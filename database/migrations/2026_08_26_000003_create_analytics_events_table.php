<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64);
            $table->string('source', 32);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('route_name', 120)->nullable();
            $table->string('visitor_hash', 64)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['name', 'created_at']);
            $table->index('source');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};
