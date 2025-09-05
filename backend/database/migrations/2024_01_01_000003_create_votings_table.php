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
        Schema::create('votings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('instructions')->nullable();
            $table->foreignId('category_id')->constrained('voting_categories')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['simple', 'multiple', 'ranked', 'approval'])->default('simple');
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'ended', 'cancelled'])->default('draft');
            $table->enum('visibility', ['public', 'members_only', 'board_only', 'custom'])->default('members_only');
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->datetime('actual_start_date')->nullable();
            $table->datetime('actual_end_date')->nullable();
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->nullable();
            $table->decimal('quorum_percentage', 5, 2)->default(50.00);
            $table->boolean('requires_quorum')->default(true);
            $table->boolean('anonymous_voting')->default(true);
            $table->boolean('allow_abstention')->default(true);
            $table->boolean('allow_comments')->default(false);
            $table->boolean('show_results_during_voting')->default(false);
            $table->boolean('show_results_after_voting')->default(true);
            $table->boolean('requires_biometric')->default(false);
            $table->boolean('send_notifications')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('eligible_users')->nullable(); // User IDs or criteria
            $table->json('settings')->nullable(); // Additional settings
            $table->json('metadata')->nullable(); // Additional metadata
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'start_date']);
            $table->index(['category_id', 'status']);
            $table->index(['created_by', 'status']);
            $table->index(['visibility', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index('slug');
            $table->index('is_featured');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votings');
    }
};