<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;
use App\Models\UserSifConfig;

class VerifactuTransportFactory
{
    public function for(BillingRecord $record): VerifactuTransportInterface
    {
        $record->loadMissing(['company.sifConfig', 'user.sifConfig']);
        $sif = $record->company?->sifConfig ?? $record->user?->sifConfig;

        $transport = (string) config('verifactu.transport', 'aeat');
        if ($transport === 'disabled' || ! $sif?->enabled) {
            return new DisabledVerifactuTransport;
        }

        if ($transport === 'sandbox' || $sif?->is_dev_cert) {
            return new SandboxVerifactuTransport;
        }

        return app(AeatSoapClient::class);
    }
}
