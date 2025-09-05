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
        Schema::create('convenio_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable(); // User notes about the favorite
            $table->json('tags')->nullable(); // User-defined tags for organization
            $table->timestamps();
            
            // Unique constraint to prevent duplicate favorites
            $table->unique(['convenio_id', 'user_id']);
            
            // Indexes
            $table->index(['convenio_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_favorites');
    }
};