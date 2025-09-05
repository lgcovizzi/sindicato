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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_id')->constrained('votings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('voting_options')->onDelete('cascade');
            $table->json('selected_options')->nullable(); // For multiple choice or ranked voting
            $table->boolean('is_abstention')->default(false);
            $table->text('comment')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('biometric_verification')->nullable(); // Biometric verification data
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable(); // Additional vote data
            $table->timestamps();
            
            // Unique constraint to prevent duplicate votes
            $table->unique(['voting_id', 'user_id']);
            
            // Indexes
            $table->index(['voting_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['option_id', 'created_at']);
            $table->index(['is_verified', 'verified_at']);
            $table->index('is_abstention');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};