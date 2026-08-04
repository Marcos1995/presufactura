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
    ) {}

    public function createAltaRecord(Document $document): ?BillingRecord
    {
        if (! $document->isInvoice()) {
            return null;
        }

        if (! $document->user->hasVerifactuEnabled()) {
            return null;
        }

        if ($document->billingRecords()->where('record_type', BillingRecord::TYPE_ALTA)->exists()) {
            return $document->billingRecord;
        }

        return DB::transaction(function () use ($document) {
            $document->loadMissing(['user', 'client', 'lineItems']);
            $user = $document->user;

            $previousHash = $this->hashChain->getPreviousHash($user->id);
            $timestamp = $this->hashChain->formatTimestamp();
            $invoiceType = $document->rectifies_document_id ? 'R1' : 'F1';

            $hash = $this->hashChain->computeAltaHash([
                'nif' => strtoupper(preg_replace('/\s+/', '', $user->tax_id)),
                'number' => $document->number,
                'issue_date' => $this->hashChain->formatIssueDate($document->issue_date),
                'invoice_type' => $invoiceType,
                'vat_amount' => $this->hashChain->formatAmount((float) $document->vat_amount),
                'total' => $this->hashChain->formatAmount((float) $document->total),
                'previous_hash' => $previousHash ?? '',
                'timestamp' => $timestamp,
            ]);

            $xml = $this->xmlBuilder->buildAltaXml($document, $hash, $timestamp);
            $xmlPath = $this->storeXml($user->id, $document->number, 'alta', $xml);

            $record = BillingRecord::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'record_type' => BillingRecord::TYPE_ALTA,
                'xml_path' => $xmlPath,
                'hash_current' => $hash,
                'hash_previous' => $previousHash,
                'aeat_status' => BillingRecord::STATUS_PENDING,
            ]);

            SifEvent::create([
                'user_id' => $user->id,
                'event_type' => SifEvent::TYPE_ALTA,
                'payload' => [
                    'document_id' => $document->id,
                    'number' => $document->number,
                    'hash' => $hash,
                ],
            ]);

            SubmitBillingRecordJob::dispatch($record->id);

            return $record;
        });
    }

    public function createAnulacionRecord(Document $document): ?BillingRecord
    {
        if (! $document->isInvoice() || ! $document->user->hasVerifactuEnabled()) {
            return null;
        }

        $altaRecord = $document->billingRecord;
        if (! $altaRecord) {
            return null;
        }

        return DB::transaction(function () use ($document, $altaRecord) {
            $document->loadMissing(['user']);
            $user = $document->user;

            $previousHash = $this->hashChain->getPreviousHash($user->id);
            $timestamp = $this->hashChain->formatTimestamp();

            $hash = $this->hashChain->computeAnulacionHash([
                'nif' => strtoupper(preg_replace('/\s+/', '', $user->tax_id)),
                'number' => $document->number,
                'issue_date' => $this->hashChain->formatIssueDate($document->issue_date),
                'previous_hash' => $previousHash ?? '',
                'timestamp' => $timestamp,
            ]);

            $xml = $this->xmlBuilder->buildAnulacionXml($document, $hash, $timestamp, $previousHash);
            $xmlPath = $this->storeXml($user->id, $document->number, 'anulacion', $xml);

            $record = BillingRecord::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'record_type' => BillingRecord::TYPE_ANULACION,
                'xml_path' => $xmlPath,
                'hash_current' => $hash,
                'hash_previous' => $previousHash,
                'aeat_status' => BillingRecord::STATUS_PENDING,
            ]);

            SifEvent::create([
                'user_id' => $user->id,
                'event_type' => SifEvent::TYPE_ANULACION,
                'payload' => [
                    'document_id' => $document->id,
                    'number' => $document->number,
                    'hash' => $hash,
                ],
            ]);

            SubmitBillingRecordJob::dispatch($record->id);

            return $record;
        });
    }

    private function storeXml(int $userId, string $number, string $type, string $xml): string
    {
        $safeNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', $number);
        $path = "sif/{$userId}/{$safeNumber}_{$type}_".now()->format('YmdHis').'.xml';
        Storage::disk('local')->put($path, $xml);

        return $path;
    }
}
