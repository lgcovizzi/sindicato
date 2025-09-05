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
        Schema::create('news_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable(); // User notes about the bookmark
            $table->json('tags')->nullable(); // User-defined tags for organization
            $table->timestamps();
            
            // Unique constraint to prevent duplicate bookmarks
            $table->unique(['news_id', 'user_id']);
            
            // Indexes
            $table->index(['news_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_bookmarks');
    }
};