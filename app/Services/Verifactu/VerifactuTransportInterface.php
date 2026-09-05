<?php

namespace App\Services\Verifactu;

use App\Models\BillingRecord;

interface VerifactuTransportInterface
{
    /**
     * @return array{success: bool, message: string, permanent?: bool, sandbox?: bool, csv?: string|null, code?: string|null, response?: mixed}
     */
    public function submit(BillingRecord $record, string $certPassword): array;
}
