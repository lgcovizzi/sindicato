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
        Schema::create('biometric_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['fingerprint', 'face', 'voice', 'iris', 'palm'])->default('fingerprint');
            $table->text('template_hash'); // Hashed biometric template
            $table->string('device_id')->nullable(); // Device that captured the biometric
            $table->string('device_type')->nullable(); // Type of device
            $table->decimal('quality_score', 5, 2)->nullable(); // Quality of the biometric sample
            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')->default(false); // Primary biometric for the user
            $table->json('metadata')->nullable(); // Additional biometric metadata
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'type', 'is_active']);
            $table->index(['user_id', 'is_primary']);
            $table->index(['type', 'is_active']);
            $table->index(['device_id', 'created_at']);
            $table->index('last_used_at');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_data');
    }
};