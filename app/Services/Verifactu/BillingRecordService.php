<?php

namespace App\Services\Verifactu;

use App\Jobs\SubmitBillingRecordJob;
use App\Models\BillingRecord;
use App\Models\Document;
use App\Models\SifEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BillingRecordService
{
    public function __construct(
        private HashChainService $hashChain,
        private XmlBuilderService $xmlBuilder,
        private VerifactuTransportFactory $transports,
    ) {}

    public function createAltaRecord(Document $document): ?BillingRecord
    {
        if (! $document->isInvoice()) {
            return null;
        }

        $document->loadMissing(['user.sifConfig', 'company.sifConfig', 'client', 'lineItems']);
        $company = $document->company;
        $user = $document->user;

        if (! $company?->canEmitFiscalInvoices() && ! $user->canEmitFiscalInvoices()) {
            return null;
        }

        if ($document->billingRecords()->where('record_type', BillingRecord::TYPE_ALTA)->exists()) {
            return $document->billingRecord;
        }

        return DB::transaction(function () use ($document, $company, $user) {
            $existing = $document->billingRecords()
                ->where('record_type', BillingRecord::TYPE_ALTA)
                ->lockForUpdate()
                ->first();
            if ($existing) {
                return $existing;
            }

            $previous = $this->hashChain->getPreviousRecord($company?->id, $user->id);
            $timestamp = $this->hashChain->formatTimestamp();
            $invoiceType = $document->fiscalInvoiceType();
            $nif = strtoupper(preg_replace('/[\s-]+/', '', (string) ($company?->tax_id ?: $user->tax_id)));

            $hash = $this->hashChain->computeAltaHash([
                'nif' => $nif,
                'number' => $document->number,
                'issue_date' => $this->hashChain->formatIssueDate($document->issue_date),
                'invoice_type' => $invoiceType,
                'vat_amount' => $this->hashChain->formatAmount((float) $document->vat_amount + (float) $document->recargo_amount),
                'total' => $this->hashChain->formatAmount((float) $document->total),
                'previous_hash' => $previous?->hash_current ?? '',
                'timestamp' => $timestamp,
            ]);

            $xml = $this->xmlBuilder->buildAltaXml($document, $hash, $timestamp, $previous);
            $xmlPath = $this->storeXml($company?->id ?: $user->id, $document->number, 'alta', $xml);

            $record = BillingRecord::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'company_id' => $company?->id,
                'record_type' => BillingRecord::TYPE_ALTA,
                'invoice_type' => $invoiceType,
                'schema_version' => '1.0',
                'xml_path' => $xmlPath,
                'hash_current' => $hash,
                'hash_previous' => $previous?->hash_current,
                'aeat_status' => BillingRecord::STATUS_PENDING,
                'generated_at' => now(),
            ]);

            SifEvent::create([
                'user_id' => $user->id,
                'company_id' => $company?->id,
                'event_type' => SifEvent::TYPE_ALTA,
                'payload' => [
                    'document_id' => $document->id,
                    'number' => $document->number,
                    'hash' => $hash,
                ],
            ]);

            $this->queueOrAccept($record);

            return $record;
        });
    }

    public function createAnulacionRecord(Document $document): ?BillingRecord
    {
        if (! $document->isInvoice()) {
            return null;
        }

        $hasVerifactu = $document->company?->hasVerifactuEnabled() || $document->user->hasVerifactuEnabled();
        if (! $hasVerifactu) {
            return null;
        }

        $altaRecord = $document->billingRecord;
        if (! $altaRecord) {
            return null;
        }

        $existing = $document->billingRecords()
            ->where('record_type', BillingRecord::TYPE_ANULACION)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($document, $altaRecord) {
            $document->loadMissing(['user', 'company']);
            $user = $document->user;
            $company = $document->company;

            $previous = $this->hashChain->getPreviousRecord($company?->id, $user->id);
            $timestamp = $this->hashChain->formatTimestamp();
            $nif = strtoupper(preg_replace('/[\s-]+/', '', (string) ($company?->tax_id ?: $user->tax_id)));

            $hash = $this->hashChain->computeAnulacionHash([
                'nif' => $nif,
                'number' => $document->number,
                'issue_date' => $this->hashChain->formatIssueDate($document->issue_date),
                'previous_hash' => $previous?->hash_current ?? '',
                'timestamp' => $timestamp,
            ]);

            $xml = $this->xmlBuilder->buildAnulacionXml($document, $hash, $timestamp, $previous?->hash_current, $previous);
            $xmlPath = $this->storeXml($company?->id ?: $user->id, $document->number, 'anulacion', $xml);

            $record = BillingRecord::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'company_id' => $company?->id,
                'record_type' => BillingRecord::TYPE_ANULACION,
                'invoice_type' => $altaRecord->invoice_type,
                'schema_version' => '1.0',
                'xml_path' => $xmlPath,
                'hash_current' => $hash,
                'hash_previous' => $previous?->hash_current,
                'aeat_status' => BillingRecord::STATUS_PENDING,
                'generated_at' => now(),
            ]);

            SifEvent::create([
                'user_id' => $user->id,
                'company_id' => $company?->id,
                'event_type' => SifEvent::TYPE_ANULACION,
                'payload' => [
                    'document_id' => $document->id,
                    'number' => $document->number,
                    'hash' => $hash,
                ],
            ]);

            $this->queueOrAccept($record);

            return $record;
        });
    }

    private function queueOrAccept(BillingRecord $record): void
    {
        $transport = $this->transports->for($record);

        if ($transport instanceof SandboxVerifactuTransport) {
            $result = $transport->submit($record, '');
            $record->update([
                'aeat_status' => BillingRecord::STATUS_ACCEPTED,
                'aeat_response' => $result,
                'sent_at' => now(),
            ]);

            return;
        }

        if ($transport instanceof DisabledVerifactuTransport) {
            $record->update([
                'aeat_status' => BillingRecord::STATUS_PENDING,
                'aeat_response' => $transport->submit($record, ''),
            ]);

            return;
        }

        SubmitBillingRecordJob::dispatch($record->id);
    }

    private function storeXml(int $ownerId, string $number, string $type, string $xml): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', $number);
        $path = "sif/{$ownerId}/{$safeNumber}_{$type}_".now()->format('YmdHis').'.xml';
        Storage::disk('local')->put($path, $xml);

        return $path;
    }
}
