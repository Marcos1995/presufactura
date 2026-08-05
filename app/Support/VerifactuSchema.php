<?php

namespace App\Support;

use Illuminate\Support\Facades\Schema;

final class VerifactuSchema
{
    public static function hasSifConfigTable(): bool
    {
        return Schema::hasTable('user_sif_config');
    }

    public static function hasBillingRecordsTable(): bool
    {
        return Schema::hasTable('billing_records');
    }
}
