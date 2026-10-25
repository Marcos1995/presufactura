<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('fiscal_activity')->nullable()->after('invoice_footer');
            $table->string('fiscal_income_method', 32)->nullable()->after('fiscal_activity');
            $table->boolean('fiscal_has_employees')->default(false)->after('fiscal_income_method');
            $table->boolean('fiscal_has_rented_local')->default(false)->after('fiscal_has_employees');
            $table->boolean('fiscal_reminders_opt_in')->default(true)->after('fiscal_has_rented_local');
            $table->string('fiscal_calendar_token', 64)->nullable()->unique()->after('fiscal_reminders_opt_in');
            $table->timestamp('fiscal_profile_completed_at')->nullable()->after('fiscal_calendar_token');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'fiscal_activity',
                'fiscal_income_method',
                'fiscal_has_employees',
                'fiscal_has_rented_local',
                'fiscal_reminders_opt_in',
                'fiscal_calendar_token',
                'fiscal_profile_completed_at',
            ]);
        });
    }
};
