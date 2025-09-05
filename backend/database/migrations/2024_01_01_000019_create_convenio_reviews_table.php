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
        Schema::create('convenio_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating')->unsigned(); // 1-5 stars
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_verified_purchase')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable(); // Additional review data
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint to prevent duplicate reviews
            $table->unique(['convenio_id', 'user_id']);
            
            // Indexes
            $table->index(['convenio_id', 'is_approved', 'rating']);
            $table->index(['user_id', 'created_at']);
            $table->index(['rating', 'is_approved']);
            $table->index(['is_approved', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_reviews');
    }
};