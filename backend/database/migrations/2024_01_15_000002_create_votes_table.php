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
            
            // Dados do voto
            $table->json('vote_data'); // Opções selecionadas
            $table->text('justification')->nullable(); // Justificativa do voto
            $table->enum('vote_type', ['vote', 'abstention', 'blank']);
            
            // Metadados de segurança
            $table->string('vote_hash')->unique(); // Hash único do voto
            $table->string('verification_code')->nullable(); // Código de verificação
            $table->boolean('is_verified')->default(false);
            
            // Dados de auditoria
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable();
            $table->json('biometric_data')->nullable(); // Dados biométricos se aplicável
            
            // Timestamps
            $table->timestamp('voted_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->unique(['voting_id', 'user_id']); // Um voto por usuário por votação
            $table->index(['voting_id', 'voted_at']);
            $table->index(['user_id', 'voted_at']);
            $table->index(['vote_type']);
            $table->index(['is_verified']);
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