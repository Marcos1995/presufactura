<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_sif_config') || Schema::hasColumn('user_sif_config', 'is_dev_cert')) {
            return;
        }

        Schema::table('user_sif_config', function (Blueprint $table) {
            $table->boolean('is_dev_cert')->default(false)->after('enabled');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('user_sif_config') || ! Schema::hasColumn('user_sif_config', 'is_dev_cert')) {
            return;
        }

        Schema::table('user_sif_config', function (Blueprint $table) {
            $table->dropColumn('is_dev_cert');
        });
    }
};
