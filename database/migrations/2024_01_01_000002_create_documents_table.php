<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['quote', 'invoice']);
            $table->string('number', 50);
            $table->enum('status', ['draft', 'sent', 'accepted', 'expired', 'paid', 'payment_pending'])->default('draft');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->date('valid_until')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->char('currency', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->string('public_token', 64)->unique();
            $table->foreignId('converted_from_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'number']);
            $table->index(['user_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
