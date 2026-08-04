<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use Carbon\Carbon;

class HashChainService
{
    /**
     * Calcula la huella SHA-256 encadenada según especificación AEAT Veri*Factu.
     *
     * @param  array{nif: string, number: string, issue_date: string, invoice_type: string, vat_amount: string, total: string, previous_hash: string|null, timestamp: string}  $data
     */
    public function computeHash(array $data): string
    {
        $previousHash = $data['previous_hash'] ?? '';

        $payload = implode('&', [
            'NIF='.$data['nif'],
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

    public function getPreviousHash(int $userId): ?string
    {
        $record = BillingRecord::query()
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->first();

        return $record?->hash_current;
    }

    public function formatIssueDate(Carbon $date): string
    {
        return $date->format('d-m-Y');
    }

    public function formatTimestamp(?Carbon $timestamp = null): string
    {
        return ($timestamp ?? now())->timezone('Europe/Madrid')->format('Y-m-d\TH:i:sP');
    }

    public function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
