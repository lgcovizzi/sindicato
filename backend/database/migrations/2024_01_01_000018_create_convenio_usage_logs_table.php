<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('convenio_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('action', ['view', 'use', 'share', 'favorite', 'rate', 'review'])->default('view');
            $table->decimal('amount_saved', 10, 2)->nullable(); // Amount saved by user
            $table->text('notes')->nullable();
            $table->string('location')->nullable(); // Where it was used
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable(); // Additional usage data
            $table->timestamps();
            
            // Indexes
            $table->index(['convenio_id', 'action', 'created_at']);
            $table->index(['user_id', 'action', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_usage_logs');
    }
};