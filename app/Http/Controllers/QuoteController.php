<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentEvent;
use App\Services\DocumentCalculatorService;
use App\Services\DocumentNumberService;
use App\Services\EmailService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private DocumentCalculatorService $calculator,
        private DocumentNumberService $numberService,
        private EmailService $emailService,
        private PdfGeneratorService $pdfGenerator,
    ) {}

    public function index(): View
    {
        $quotes = auth()->user()->documents()
            ->where('type', Document::TYPE_QUOTE)
            ->with('client')
            ->orderByDesc('created_at')
            ->get();

        return view('quotes.index', compact('quotes'));
    }

    public function create(): View
    {
        $clients = auth()->user()->clients()->orderBy('name')->get();

        return view('quotes.form', [
            'quote' => null,
            'clients' => $clients,
            'defaultVatRate' => auth()->user()->default_vat_rate,
            'lineItems' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $lines = $this->parseLines($request);
        $totals = $this->calculator->calculateDocument($lines);

        $quote = DB::transaction(function () use ($data, $lines, $totals) {
            $user = auth()->user();

            $quote = $user->documents()->create([
                'client_id' => $data['client_id'],
                'type' => Document::TYPE_QUOTE,
                'number' => $this->numberService->nextQuoteNumber($user),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'public_token' => Str::random(32),
            ]);

            $this->syncLineItems($quote, $lines, $totals['lines']);

            $quote->events()->create(['event_type' => DocumentEvent::CREATED]);

            return $quote;
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Presupuesto creado.');
    }

    public function show(Document $quote): View
    {
        $this->authorizeQuote($quote);
        $quote->load(['client', 'lineItems']);
        $clients = auth()->user()->clients()->orderBy('name')->get();

        return view('quotes.show', compact('quote', 'clients'));
    }

    public function update(Request $request, Document $quote): RedirectResponse
    {
        $this->authorizeQuote($quote);
        abort_unless($quote->status === Document::STATUS_DRAFT, 403);

        $data = $this->validated($request);
        $lines = $this->parseLines($request);
        $totals = $this->calculator->calculateDocument($lines);

        DB::transaction(function () use ($quote, $data, $lines, $totals) {
            $quote->update([
                'client_id' => $data['client_id'],
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            $quote->lineItems()->delete();
            $this->syncLineItems($quote, $lines, $totals['lines']);
        });

        return redirect()->route('quotes.show', $quote)->with('status', 'Presupuesto actualizado.');
    }

    public function destroy(Document $quote): RedirectResponse
    {
        $this->authorizeQuote($quote);
        abort_unless($quote->status === Document::STATUS_DRAFT, 403);

        $quote->delete();

        return redirect()->route('quotes.index')->with('status', 'Presupuesto eliminado.');
    }

    public function send(Document $quote): RedirectResponse
    {
        $this->authorizeQuote($quote);
        abort_unless($quote->canSend(), 403);

        $quote->update([
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $quote->events()->create(['event_type' => DocumentEvent::SENT]);

        try {
            $this->emailService->sendQuote($quote);
        } catch (\Throwable) {
            return back()->with('status', 'Presupuesto publicado. No se pudo enviar el email al cliente.');
        }

        return back()->with('status', 'Presupuesto enviado al cliente por email.');
    }

    public function pdf(Document $quote): Response
    {
        $this->authorizeQuote($quote);

        $pdf = $this->pdfGenerator->generateQuotePdf($quote);
        $filename = 'presupuesto-'.$quote->number.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function convert(Document $quote): RedirectResponse
    {
        $this->authorizeQuote($quote);
        abort_unless($quote->canConvert(), 403);

        if (! auth()->user()->canCreateDocument()) {
            return back()->with('error', 'Has alcanzado el límite de 3 documentos al mes del plan Free.');
        }

        $invoice = DB::transaction(function () use ($quote) {
            $user = auth()->user();
            $quote->load('lineItems');

            $invoice = $user->documents()->create([
                'client_id' => $quote->client_id,
                'type' => Document::TYPE_INVOICE,
                'number' => $this->numberService->nextInvoiceNumber($user),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays($user->default_due_days)->toDateString(),
                'subtotal' => $quote->subtotal,
                'vat_amount' => $quote->vat_amount,
                'total' => $quote->total,
                'notes' => $quote->notes,
                'public_token' => Str::random(32),
                'converted_from_id' => $quote->id,
            ]);

            foreach ($quote->lineItems as $item) {
                $invoice->lineItems()->create([
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'vat_rate' => $item->vat_rate,
                    'line_subtotal' => $item->line_subtotal,
                    'line_vat' => $item->line_vat,
                    'line_total' => $item->line_total,
                    'sort_order' => $item->sort_order,
                ]);
            }

            $invoice->events()->create(['event_type' => DocumentEvent::CREATED]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)
            ->with('status', 'Factura creada desde presupuesto '.$quote->number.'.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'client_id.required' => 'Selecciona un cliente.',
            'lines.required' => 'Añade al menos una línea.',
        ]);
    }

    /** @return array<int, array{description: string, quantity: float, unit_price: float, vat_rate: float}> */
    private function parseLines(Request $request): array
    {
        $clientIds = auth()->user()->clients()->pluck('id');
        abort_unless($clientIds->contains($request->input('client_id')), 403);

        return collect($request->input('lines', []))
            ->values()
            ->map(fn ($line) => [
                'description' => $line['description'],
                'quantity' => (float) $line['quantity'],
                'unit_price' => (float) $line['unit_price'],
                'vat_rate' => (float) $line['vat_rate'],
            ])
            ->all();
    }

    /** @param  array<int, array{description: string, quantity: float, unit_price: float, vat_rate: float}>  $lines
     * @param  array<int, array{line_subtotal: float, line_vat: float, line_total: float}>  $calculated
     */
    private function syncLineItems(Document $quote, array $lines, array $calculated): void
    {
        foreach ($lines as $i => $line) {
            $quote->lineItems()->create([
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'vat_rate' => $line['vat_rate'],
                'line_subtotal' => $calculated[$i]['line_subtotal'],
                'line_vat' => $calculated[$i]['line_vat'],
                'line_total' => $calculated[$i]['line_total'],
                'sort_order' => $i,
            ]);
        }
    }

    private function authorizeQuote(Document $quote): void
    {
        abort_unless(
            $quote->user_id === auth()->id() && $quote->type === Document::TYPE_QUOTE,
            403
        );
    }
}
