<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;

class DisabledVerifactuTransport implements VerifactuTransportInterface
{
    public function submit(BillingRecord $record, string $certPassword): array
    {
        return [
            'success' => false,
            'permanent' => false,
            'message' => 'Envío AEAT desactivado. El registro se conserva en local.',
        ];
    }
}
