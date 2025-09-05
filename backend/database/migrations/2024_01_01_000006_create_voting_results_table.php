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
        Schema::create('voting_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_id')->constrained('votings')->onDelete('cascade');
            $table->foreignId('option_id')->nullable()->constrained('voting_options')->onDelete('cascade');
            $table->integer('vote_count')->default(0);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->integer('ranking')->nullable(); // For ranked voting
            $table->boolean('is_winner')->default(false);
            $table->json('detailed_results')->nullable(); // Detailed breakdown
            $table->json('statistics')->nullable(); // Statistical analysis
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['voting_id', 'option_id']);
            
            // Indexes
            $table->index(['voting_id', 'vote_count']);
            $table->index(['voting_id', 'percentage']);
            $table->index(['voting_id', 'ranking']);
            $table->index(['voting_id', 'is_winner']);
            $table->index('calculated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_results');
    }
};