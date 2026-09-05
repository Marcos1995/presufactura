<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Services\AnalyticsService;
use App\Services\DocumentCalculatorService;
use App\Services\DocumentNumberService;
use App\Services\EmailService;
use App\Services\PdfGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private DocumentCalculatorService $calculator,
        private DocumentNumberService $numberService,
        private EmailService $emailService,
        private PdfGeneratorService $pdfGenerator,
        private AnalyticsService $analytics,
    ) {}

    public function index(): View
    {
        $quotes = auth()->user()->currentCompany()->documents()
            ->where('type', Document::TYPE_QUOTE)
            ->with('client')
            ->orderByDesc('created_at')
            ->get();

        return view('quotes.index', compact('quotes'));
    }

    public function create(): View
    {
        $company = auth()->user()->currentCompany();

        return view('quotes.form', [
            'quote' => null,
            'clients' => $company->clients()->where('is_active', true)->orderBy('name')->get(),
            'defaultVatRate' => $company->default_vat_rate,
            'defaultIrpfRate' => $company->default_irpf_rate,
            'defaultRecargoRate' => $company->default_recargo_rate,
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
            $company = $user->currentCompany();

            $quote = $user->documents()->create([
                'company_id' => $company->id,
                'client_id' => $data['client_id'],
                'type' => Document::TYPE_QUOTE,
                'number' => $this->numberService->draftNumber(Document::TYPE_QUOTE),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => $data['issue_date'],
                'valid_until' => $data['valid_until'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'vat_amount' => $totals['vat_amount'],
                'irpf_amount' => $totals['irpf_amount'],
                'recargo_amount' => $totals['recargo_amount'],
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'public_token' => Str::random(32),
                'created_by' => $user->id,
            ]);

            $this->syncLineItems($quote, $lines, $totals['lines']);

            $quote->events()->create(['event_type' => DocumentEvent::CREATED]);

            return $quote;
        });

        $this->analytics->record(AnalyticsEvent::FIRST_QUOTE_CREATED);

        return redirect()->route('quotes.show', $quote)->with('status', 'Presupuesto creado.');
    }

    public function show(Document $quote): View
    {
        $this->authorizeQuote($quote);
        $quote->load(['client', 'lineItems', 'company']);
        $clients = auth()->user()->currentCompany()->clients()->orderBy('name')->get();

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
                'discount_amount' => $totals['discount_amount'],
                'vat_amount' => $totals['vat_amount'],
                'irpf_amount' => $totals['irpf_amount'],
                'recargo_amount' => $totals['recargo_amount'],
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

        DB::transaction(function () use ($quote) {
            $locked = Document::query()->whereKey($quote->id)->lockForUpdate()->firstOrFail();
            $this->numberService->assignFiscalNumber($locked);
            $locked->update([
                'status' => Document::STATUS_SENT,
                'sent_at' => now(),
            ]);
            $locked->events()->create(['event_type' => DocumentEvent::SENT]);
        });

        $quote = $quote->fresh(['user', 'company', 'client', 'lineItems']);

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
        $this->analytics->record(AnalyticsEvent::PDF_GENERATED);
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
            return back()->with('error', 'No se puede crear el documento en este momento.');
        }

        $invoice = DB::transaction(function () use ($quote) {
            $user = auth()->user();
            $quote->load('lineItems');

            $invoice = $user->documents()->create([
                'company_id' => $quote->company_id,
                'client_id' => $quote->client_id,
                'type' => Document::TYPE_INVOICE,
                'number' => $this->numberService->draftNumber(Document::TYPE_INVOICE),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays($user->currentCompany()->default_due_days)->toDateString(),
                'subtotal' => $quote->subtotal,
                'discount_amount' => $quote->discount_amount,
                'vat_amount' => $quote->vat_amount,
                'irpf_amount' => $quote->irpf_amount,
                'recargo_amount' => $quote->recargo_amount,
                'total' => $quote->total,
                'notes' => $quote->notes,
                'public_token' => Str::random(32),
                'converted_from_id' => $quote->id,
                'created_by' => $user->id,
            ]);

            foreach ($quote->lineItems as $item) {
                $invoice->lineItems()->create($item->only([
                    'description', 'quantity', 'unit_price', 'discount_rate', 'vat_rate',
                    'irpf_rate', 'recargo_rate', 'line_subtotal', 'line_discount',
                    'line_vat', 'line_irpf', 'line_recargo', 'line_total', 'sort_order',
                ]));
            }

            $invoice->events()->create(['event_type' => DocumentEvent::CREATED]);

            return $invoice;
        });

        $this->analytics->record(AnalyticsEvent::QUOTE_TO_INVOICE);
        $this->analytics->record(AnalyticsEvent::FIRST_INVOICE_CREATED);

        return redirect()->route('invoices.show', $invoice)
            ->with('status', 'Factura creada desde presupuesto '.$quote->number.'.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where(fn ($q) => $q->where('company_id', auth()->user()->currentCompany()->id))],
            'issue_date' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.recargo_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'client_id.required' => 'Selecciona un cliente.',
            'lines.required' => 'Añade al menos una línea.',
        ]);
    }

    /** @return array<int, array{description: string, quantity: float, unit_price: float, vat_rate: float}> */
    private function parseLines(Request $request): array
    {
        $clientIds = auth()->user()->currentCompany()->clients()->pluck('id');
        abort_unless($clientIds->contains((int) $request->input('client_id')), 403);

        return collect($request->input('lines', []))
            ->values()
            ->map(fn ($line) => [
                'description' => $line['description'],
                'quantity' => $line['quantity'],
                'unit_price' => $line['unit_price'],
                'discount_rate' => $line['discount_rate'] ?? 0,
                'vat_rate' => $line['vat_rate'],
                'irpf_rate' => $line['irpf_rate'] ?? 0,
                'recargo_rate' => $line['recargo_rate'] ?? 0,
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
                'discount_rate' => $line['discount_rate'] ?? 0,
                'vat_rate' => $line['vat_rate'],
                'irpf_rate' => $line['irpf_rate'] ?? 0,
                'recargo_rate' => $line['recargo_rate'] ?? 0,
                'line_subtotal' => $calculated[$i]['line_subtotal'],
                'line_discount' => $calculated[$i]['line_discount'] ?? 0,
                'line_vat' => $calculated[$i]['line_vat'],
                'line_irpf' => $calculated[$i]['line_irpf'] ?? 0,
                'line_recargo' => $calculated[$i]['line_recargo'] ?? 0,
                'line_total' => $calculated[$i]['line_total'],
                'sort_order' => $i,
            ]);
        }
    }

    private function authorizeQuote(Document $quote): void
    {
        abort_unless($quote->type === Document::TYPE_QUOTE, 403);
        $this->authorize('view', $quote);
    }
}
