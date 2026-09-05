<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Support\Money;
use Carbon\Carbon;

class HashChainService
{
    /**
     * Calcula la huella SHA-256 de un registro de alta según OM AEAT Veri*Factu.
     *
     * @param  array{nif: string, number: string, issue_date: string, invoice_type: string, vat_amount: string, total: string, previous_hash: string|null, timestamp: string}  $data
     */
    public function computeAltaHash(array $data): string
    {
        $previousHash = $data['previous_hash'] ?? '';

        $payload = implode('&', [
            'IDEmisorFactura='.$data['nif'],
            'NumSerieFactura='.$data['number'],
            'FechaExpedicionFactura='.$data['issue_date'],
            'TipoFactura='.$data['invoice_type'],
            'CuotaTotal='.$data['vat_amount'],
            'ImporteTotal='.$data['total'],
            'Huella='.$previousHash,
            'FechaHoraHusoGenRegistro='.$data['timestamp'],
        ]);

        return strtoupper(hash('sha256', $payload));
    }

    /** @deprecated Use computeAltaHash() */
    public function computeHash(array $data): string
    {
        return $this->computeAltaHash($data);
    }

    /**
     * Calcula la huella SHA-256 de un registro de anulación según OM AEAT Veri*Factu.
     *
     * @param  array{nif: string, number: string, issue_date: string, previous_hash: string|null, timestamp: string}  $data
     */
    public function computeAnulacionHash(array $data): string
    {
        $previousHash = $data['previous_hash'] ?? '';

        $payload = implode('&', [
            'IDEmisorFacturaAnulada='.$data['nif'],
            'NumSerieFacturaAnulada='.$data['number'],
            'FechaExpedicionFacturaAnulada='.$data['issue_date'],
            'Huella='.$previousHash,
            'FechaHoraHusoGenRegistro='.$data['timestamp'],
        ]);

        return strtoupper(hash('sha256', $payload));
    }

    public function getPreviousRecord(?int $companyId, ?int $userId = null): ?BillingRecord
    {
        $query = BillingRecord::query()->orderByDesc('id')->lockForUpdate();

        if ($companyId) {
            $query->where('company_id', $companyId);
        } else {
            $query->where('user_id', $userId);
        }

        return $query->first();
    }

    public function getPreviousHash(int $companyOrUserId): ?string
    {
        return $this->getPreviousRecord($companyOrUserId)?->hash_current;
    }

    public function formatIssueDate(Carbon $date): string
    {
        return $date->format('d-m-Y');
    }

    public function formatTimestamp(?Carbon $timestamp = null): string
    {
        return ($timestamp ?? now())->timezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
    }

    public function formatAmount(int|float|string $amount): string
    {
        return Money::of($amount);
    }
}
