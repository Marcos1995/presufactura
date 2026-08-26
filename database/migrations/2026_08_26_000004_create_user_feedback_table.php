<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('dismissed')->default(false);
            $table->text('expected')->nullable();
            $table->text('difficult')->nullable();
            $table->text('used_before')->nullable();
            $table->text('missing_weekly')->nullable();
            $table->unsignedTinyInteger('would_recommend')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
