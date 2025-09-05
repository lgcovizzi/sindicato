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
        Schema::create('news_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('platform', ['facebook', 'twitter', 'linkedin', 'whatsapp', 'telegram', 'email', 'copy_link', 'other'])->default('other');
            $table->string('ip_address')->nullable();
            $table->json('metadata')->nullable(); // Additional sharing data
            $table->timestamps();
            
            // Indexes
            $table->index(['news_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['platform', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_shares');
    }
};