<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sif_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('mode', 20)->default('verifactu');
            $table->text('cert_path')->nullable();
            $table->timestamp('cert_expires_at')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });

        Schema::create('billing_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('record_type', 20);
            $table->string('xml_path')->nullable();
            $table->string('hash_current', 64);
            $table->string('hash_previous', 64)->nullable();
            $table->string('aeat_status', 20)->default('pending');
            $table->json('aeat_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('sif_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 50);
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('rectifies_document_id')->nullable()->after('converted_from_id')
                ->constrained('documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rectifies_document_id');
        });

        Schema::dropIfExists('sif_events');
        Schema::dropIfExists('billing_records');
        Schema::dropIfExists('user_sif_config');
    }
};
