<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['client_day_3', 'client_day_7', 'client_day_14', 'owner_day_10', 'client_custom']);
            $table->string('recipient_email');
            $table->timestamp('sent_at')->useCurrent();

            $table->unique(['document_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
