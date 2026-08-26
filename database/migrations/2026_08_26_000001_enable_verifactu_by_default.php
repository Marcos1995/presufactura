<?php

use App\Models\User;
use App\Models\UserSifConfig;
use App\Support\VerifactuSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! VerifactuSchema::hasSifConfigTable()) {
            return;
        }

        $now = now();
        $existing = DB::table('user_sif_config')->pluck('user_id');

        User::query()->whereNotIn('id', $existing)->each(function (User $user) use ($now) {
            DB::table('user_sif_config')->insert([
                'user_id' => $user->id,
                'mode' => UserSifConfig::MODE_VERIFACTU,
                'enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }

    public function down(): void
    {
        // No revert: no borramos configuraciones de usuarios existentes.
    }
};
