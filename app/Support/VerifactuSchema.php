<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
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

    public static function ensureDevCertColumn(): void
    {
        if (! self::hasSifConfigTable() || Schema::hasColumn('user_sif_config', 'is_dev_cert')) {
            return;
        }

        Schema::table('user_sif_config', function (Blueprint $table) {
            $table->boolean('is_dev_cert')->default(false);
        });
    }
}
