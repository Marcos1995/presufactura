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

class InvoiceController extends Controller
{
    public function __construct(
        private DocumentCalculatorService $calculator,
        private PdfGeneratorService $pdfGenerator,
        private DocumentNumberService $numberService,
        private EmailService $emailService,
    ) {}

    public function index(): View
    {
        $invoices = auth()->user()->documents()
            ->where('type', Document::TYPE_INVOICE)
            ->with('client')
            ->orderByDesc('created_at')
            ->get();

        return view('invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $clients = auth()->user()->clients()->orderBy('name')->get();
        $user = auth()->user();

        return view('invoices.form', [
            'invoice' => null,
            'clients' => $clients,
            'defaultVatRate' => $user->default_vat_rate,
            'lineItems' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $lines = $this->parseLines($request);
        $totals = $this->calculator->calculateDocument($lines);

        $invoice = DB::transaction(function () use ($data, $lines, $totals) {
            $user = auth()->user();

            $invoice = $user->documents()->create([
                'client_id' => $data['client_id'],
                'type' => Document::TYPE_INVOICE,
                'number' => $this->numberService->nextInvoiceNumber($user),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'public_token' => Str::random(32),
            ]);

            $this->syncLineItems($invoice, $lines, $totals['lines']);

            $invoice->events()->create([
                'event_type' => DocumentEvent::CREATED,
            ]);

            return $invoice;
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Factura creada.');
    }

    public function show(Document $invoice): View
    {
        $this->authorizeInvoice($invoice);
        $invoice->load(['client', 'lineItems']);

        $clients = auth()->user()->clients()->orderBy('name')->get();

        return view('invoices.show', compact('invoice', 'clients'));
    }

    public function update(Request $request, Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->status === Document::STATUS_DRAFT, 403);

        $data = $this->validated($request);
        $lines = $this->parseLines($request);
        $totals = $this->calculator->calculateDocument($lines);

        DB::transaction(function () use ($invoice, $data, $lines, $totals) {
            $invoice->update([
                'client_id' => $data['client_id'],
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $totals['subtotal'],
                'vat_amount' => $totals['vat_amount'],
                'total' => $totals['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->lineItems()->delete();
            $this->syncLineItems($invoice, $lines, $totals['lines']);
        });

        return redirect()->route('invoices.show', $invoice)->with('status', 'Factura actualizada.');
    }

    public function destroy(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->status === Document::STATUS_DRAFT, 403);

        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Factura eliminada.');
    }

    public function send(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->status === Document::STATUS_DRAFT, 403);

        $invoice->load(['user', 'client', 'lineItems']);

        $this->emailService->sendInvoice($invoice);

        $invoice->update([
            'status' => Document::STATUS_SENT,
            'sent_at' => now(),
        ]);

        $invoice->events()->create([
            'event_type' => DocumentEvent::SENT,
        ]);

        return back()->with('status', 'Factura enviada por email al cliente.');
    }

    public function markPaid(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->canMarkPaid(), 403);

        $invoice->update([
            'status' => Document::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $invoice->events()->create([
            'event_type' => DocumentEvent::MARKED_PAID,
            'meta' => ['source' => 'panel'],
        ]);

        return back()->with('status', 'Factura marcada como pagada.');
    }

    public function pdf(Document $invoice): Response
    {
        $this->authorizeInvoice($invoice);

        $pdf = $this->pdfGenerator->generateInvoicePdf($invoice);
        $filename = 'factura-'.$invoice->number.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'client_id.required' => 'Selecciona un cliente.',
            'lines.required' => 'Añade al menos una línea.',
            'lines.*.description.required' => 'La descripción es obligatoria.',
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
    private function syncLineItems(Document $invoice, array $lines, array $calculated): void
    {
        foreach ($lines as $i => $line) {
            $invoice->lineItems()->create([
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

    private function authorizeInvoice(Document $invoice): void
    {
        abort_unless(
            $invoice->user_id === auth()->id() && $invoice->type === Document::TYPE_INVOICE,
            403
        );
    }
}
