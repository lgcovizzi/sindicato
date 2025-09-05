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
        Schema::create('biometric_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('biometric_data_id')->nullable()->constrained('biometric_data')->onDelete('set null');
            $table->enum('type', ['fingerprint', 'face', 'voice', 'iris', 'palm'])->default('fingerprint');
            $table->enum('action', ['login', 'voting', 'transaction', 'verification', 'registration'])->default('verification');
            $table->boolean('success')->default(false);
            $table->decimal('confidence_score', 5, 2)->nullable(); // Confidence level of the match
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('failure_reason')->nullable(); // Reason for failure if applicable
            $table->json('metadata')->nullable(); // Additional verification data
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'success', 'created_at']);
            $table->index(['biometric_data_id', 'created_at']);
            $table->index(['type', 'action', 'success']);
            $table->index(['device_id', 'created_at']);
            $table->index(['success', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_verification_logs');
    }
};