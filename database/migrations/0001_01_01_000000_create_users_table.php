<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('tax_id', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country', 2)->default('ES');
            $table->string('phone', 20)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('logo_path')->nullable();
            $table->decimal('default_vat_rate', 5, 2)->default(21.00);
            $table->string('invoice_prefix', 20)->default('FAC');
            $table->string('quote_prefix', 20)->default('PRE');
            $table->unsignedInteger('invoice_counter')->default(0);
            $table->unsignedInteger('quote_counter')->default(0);
            $table->unsignedInteger('default_due_days')->default(30);
            $table->unsignedInteger('reminder_day_1')->default(3);
            $table->unsignedInteger('reminder_day_2')->default(7);
            $table->unsignedInteger('reminder_day_3')->default(14);
            $table->unsignedInteger('owner_reminder_day')->default(10);
            $table->enum('plan', ['free', 'pro'])->default('free');
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
