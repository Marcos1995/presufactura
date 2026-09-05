<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Company;
use App\Models\Document;
use App\Models\DocumentEvent;
use App\Services\AnalyticsService;
use App\Services\DocumentCalculatorService;
use App\Services\DocumentNumberService;
use App\Services\EmailService;
use App\Services\PdfGeneratorService;
use App\Services\Verifactu\BillingRecordService;
use App\Services\Verifactu\QrService;
use App\Support\VerifactuSchema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private DocumentCalculatorService $calculator,
        private PdfGeneratorService $pdfGenerator,
        private DocumentNumberService $numberService,
        private EmailService $emailService,
        private BillingRecordService $billingRecordService,
        private AnalyticsService $analytics,
        private QrService $qrService,
    ) {}

    public function index(): View
    {
        $with = ['client'];
        if (VerifactuSchema::hasBillingRecordsTable()) {
            $with[] = 'billingRecord';
        }

        $invoices = $this->company()->documents()
            ->where('type', Document::TYPE_INVOICE)
            ->with($with)
            ->orderByDesc('created_at')
            ->get();

        return view('invoices.index', [
            'invoices' => $invoices,
            'verifactuAvailable' => VerifactuSchema::hasBillingRecordsTable(),
        ]);
    }

    public function create(): View
    {
        $company = $this->company();

        return view('invoices.form', [
            'invoice' => null,
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

        $invoice = DB::transaction(function () use ($data, $lines, $totals) {
            $user = auth()->user();
            $company = $this->company();

            $invoice = $user->documents()->create([
                'company_id' => $company->id,
                'client_id' => $data['client_id'],
                'type' => Document::TYPE_INVOICE,
                'invoice_kind' => $data['invoice_kind'] ?? Document::KIND_F1,
                'number' => $this->numberService->draftNumber(Document::TYPE_INVOICE),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => $data['issue_date'],
                'operation_date' => $data['operation_date'] ?? null,
                'due_date' => $data['due_date'],
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

            $this->syncLineItems($invoice, $lines, $totals['lines']);
            $invoice->events()->create([
                'event_type' => DocumentEvent::CREATED,
                'meta' => ['user_id' => $user->id],
            ]);

            return $invoice;
        });

        $this->analytics->record(AnalyticsEvent::FIRST_INVOICE_CREATED);

        return redirect()->route('invoices.show', $invoice)->with('status', 'Factura creada.');
    }

    public function show(Document $invoice): View
    {
        $this->authorizeInvoice($invoice);
        $load = ['client', 'lineItems', 'company', 'rectifiesDocument', 'payments'];
        if (VerifactuSchema::hasBillingRecordsTable()) {
            $load[] = 'billingRecord';
        }
        $invoice->load($load);
        $qr = $this->qrService->payloadForDocument($invoice);

        return view('invoices.show', [
            'invoice' => $invoice,
            'clients' => $this->company()->clients()->orderBy('name')->get(),
            'verifactuAvailable' => VerifactuSchema::hasBillingRecordsTable(),
            'qrDataUri' => $qr['dataUri'] ?? null,
            'qrUrl' => $qr['url'] ?? null,
        ]);
    }

    public function update(Request $request, Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        $this->ensureMutable($invoice);

        $data = $this->validated($request);
        $lines = $this->parseLines($request);
        $totals = $this->calculator->calculateDocument($lines);

        DB::transaction(function () use ($invoice, $data, $lines, $totals) {
            $invoice->update([
                'client_id' => $data['client_id'],
                'invoice_kind' => $data['invoice_kind'] ?? $invoice->invoice_kind,
                'issue_date' => $data['issue_date'],
                'operation_date' => $data['operation_date'] ?? null,
                'due_date' => $data['due_date'],
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'vat_amount' => $totals['vat_amount'],
                'irpf_amount' => $totals['irpf_amount'],
                'recargo_amount' => $totals['recargo_amount'],
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
        $this->ensureMutable($invoice);
        abort_if($invoice->hasSifRecord(), 403, 'No se puede eliminar una factura con registro fiscal.');

        $invoice->delete();

        return redirect()->route('invoices.index')->with('status', 'Factura eliminada.');
    }

    public function send(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->status === Document::STATUS_DRAFT, 403);

        DB::transaction(function () use ($invoice) {
            $invoice = Document::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            $this->numberService->assignFiscalNumber($invoice);
            $invoice->update([
                'status' => Document::STATUS_SENT,
                'sent_at' => now(),
            ]);
            $invoice->events()->create([
                'event_type' => DocumentEvent::SENT,
                'meta' => ['user_id' => auth()->id()],
            ]);
            $this->billingRecordService->createAltaRecord($invoice->fresh(['user', 'company', 'client', 'lineItems']));
        });

        $invoice = $invoice->fresh(['user', 'company', 'client', 'lineItems']);

        try {
            $this->emailService->sendInvoice($invoice);
        } catch (\Throwable $e) {
            Log::error('Error enviando factura', [
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('status', 'Factura emitida. No se pudo enviar el email. Revisa MAIL_* en .env.');
        }

        $this->analytics->record(AnalyticsEvent::INVOICE_EMAIL_SENT);

        return back()->with('status', 'Factura emitida y enviada por email al cliente.');
    }

    public function markPaid(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->canMarkPaid(), 403);

        $invoice->update([
            'status' => Document::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $invoice->payments()->create([
            'amount' => $invoice->total,
            'method' => 'manual',
            'paid_on' => now()->toDateString(),
            'created_by' => auth()->id(),
        ]);

        $invoice->events()->create([
            'event_type' => DocumentEvent::MARKED_PAID,
            'meta' => ['source' => 'panel', 'user_id' => auth()->id()],
        ]);

        return back()->with('status', 'Factura marcada como pagada.');
    }

    public function storePayment(Request $request, Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->canMarkPaid() || $invoice->status === Document::STATUS_PAID, 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_on' => ['required', 'date'],
            'method' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $paid = (float) $invoice->payments()->sum('amount') + (float) $data['amount'];
        abort_if($paid - (float) $invoice->total > 0.009, 422, 'El cobro supera el total de la factura.');

        $invoice->payments()->create([
            'amount' => $data['amount'],
            'method' => $data['method'] ?? 'transferencia',
            'paid_on' => $data['paid_on'],
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        if ($paid + 0.009 >= (float) $invoice->total) {
            $invoice->update([
                'status' => Document::STATUS_PAID,
                'paid_at' => now(),
            ]);
        }

        return back()->with('status', 'Cobro registrado.');
    }

    public function retryVerifactu(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        $record = $invoice->billingRecord;
        abort_unless($record?->canRetry(), 403, 'Este registro no se puede reintentar.');

        $record->update(['aeat_status' => \App\Models\BillingRecord::STATUS_PENDING]);
        \App\Jobs\SubmitBillingRecordJob::dispatch($record->id);

        return back()->with('status', 'Reintento de envío AEAT encolado.');
    }

    public function cancel(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->canCancel(), 403);

        $this->billingRecordService->createAnulacionRecord($invoice);
        $invoice->update(['status' => Document::STATUS_CANCELLED]);
        $invoice->events()->create([
            'event_type' => DocumentEvent::CANCELLED,
            'meta' => ['user_id' => auth()->id()],
        ]);

        return back()->with('status', 'Factura anulada. Registro SIF de anulación generado.');
    }

    public function createRectificativa(Document $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice);
        abort_unless($invoice->canCreateRectificativa(), 403, 'No se puede crear una rectificativa para esta factura.');

        $invoice->load(['client', 'lineItems']);

        $rectificativa = DB::transaction(function () use ($invoice) {
            $user = auth()->user();

            $rectificativa = $user->documents()->create([
                'company_id' => $invoice->company_id,
                'client_id' => $invoice->client_id,
                'type' => Document::TYPE_INVOICE,
                'invoice_kind' => Document::KIND_R1,
                'number' => $this->numberService->draftNumber(Document::TYPE_INVOICE),
                'status' => Document::STATUS_DRAFT,
                'issue_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'subtotal' => $invoice->subtotal,
                'discount_amount' => $invoice->discount_amount,
                'vat_amount' => $invoice->vat_amount,
                'irpf_amount' => $invoice->irpf_amount,
                'recargo_amount' => $invoice->recargo_amount,
                'total' => $invoice->total,
                'notes' => 'Rectificativa de factura '.$invoice->number,
                'public_token' => Str::random(32),
                'rectifies_document_id' => $invoice->id,
                'created_by' => $user->id,
            ]);

            foreach ($invoice->lineItems as $item) {
                $rectificativa->lineItems()->create($item->only([
                    'description', 'quantity', 'unit_price', 'discount_rate', 'vat_rate',
                    'irpf_rate', 'recargo_rate', 'line_subtotal', 'line_discount',
                    'line_vat', 'line_irpf', 'line_recargo', 'line_total', 'sort_order',
                ]));
            }

            $rectificativa->events()->create([
                'event_type' => DocumentEvent::CREATED,
                'meta' => ['user_id' => $user->id],
            ]);

            return $rectificativa;
        });

        return redirect()->route('invoices.show', $rectificativa)
            ->with('status', 'Rectificativa creada. Revisa los importes y envía cuando esté lista.');
    }

    public function pdf(Document $invoice): Response
    {
        $this->authorizeInvoice($invoice);

        $pdf = $this->pdfGenerator->generateInvoicePdf($invoice);
        $this->analytics->record(AnalyticsEvent::PDF_GENERATED);
        $filename = 'factura-'.$invoice->number.'.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function validated(Request $request): array
    {
        $companyId = $this->company()->id;

        return $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where(fn ($q) => $q->where('company_id', $companyId)->where('user_id', auth()->id()))],
            'invoice_kind' => ['nullable', Rule::in([Document::KIND_F1, Document::KIND_F2])],
            'issue_date' => ['required', 'date'],
            'operation_date' => ['nullable', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.description' => ['required', 'string', 'max:500'],
            'lines.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'lines.*.discount_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.vat_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'lines.*.irpf_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.recargo_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'client_id.required' => 'Selecciona un cliente.',
            'lines.required' => 'Añade al menos una línea.',
            'lines.*.description.required' => 'La descripción es obligatoria.',
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function parseLines(Request $request): array
    {
        $clientIds = $this->company()->clients()->pluck('id');
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

    /** @param  array<int, array<string, mixed>>  $lines
     * @param  array<int, array<string, float>>  $calculated
     */
    private function syncLineItems(Document $invoice, array $lines, array $calculated): void
    {
        foreach ($lines as $i => $line) {
            $invoice->lineItems()->create([
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

    private function company(): Company
    {
        return auth()->user()->currentCompany();
    }

    private function authorizeInvoice(Document $invoice): void
    {
        abort_unless($invoice->type === Document::TYPE_INVOICE, 403);
        $this->authorize('view', $invoice);
    }

    private function ensureMutable(Document $invoice): void
    {
        if ($invoice->isImmutable() || $invoice->status !== Document::STATUS_DRAFT) {
            abort(403, 'Las facturas emitidas, pagadas o anuladas no se pueden modificar ni eliminar.');
        }
    }
}
