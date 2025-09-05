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
        Schema::create('voting_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_id')->constrained('votings')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('color', 7)->nullable(); // Hex color
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
            
            // Indexes
            $table->index(['voting_id', 'sort_order']);
            $table->index(['voting_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voting_options');
    }
};