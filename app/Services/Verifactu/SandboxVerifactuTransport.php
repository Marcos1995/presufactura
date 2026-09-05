<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;

class SandboxVerifactuTransport implements VerifactuTransportInterface
{
    public function submit(BillingRecord $record, string $certPassword): array
    {
        return [
            'success' => true,
            'message' => 'Aceptada en sandbox de pruebas (no enviada a AEAT)',
            'csv' => 'TEST-SANDBOX-'.$record->id,
            'sandbox' => true,
        ];
    }
}
