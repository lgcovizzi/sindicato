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
            $table->text('description');
            $table->enum('type', ['simple', 'multiple', 'ranked', 'secret']);
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'ended', 'cancelled'])->default('draft');
            $table->json('options'); // Array de opções de voto
            $table->json('settings')->nullable(); // Configurações específicas da votação
            
            // Datas e horários
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Configurações de participação
            $table->integer('min_participants')->default(1);
            $table->integer('max_participants')->nullable();
            $table->decimal('quorum_percentage', 5, 2)->default(50.00); // Percentual de quórum
            $table->boolean('requires_quorum')->default(false);
            
            // Configurações de votação
            $table->boolean('allow_abstention')->default(true);
            $table->boolean('allow_change_vote')->default(false);
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_secret')->default(false);
            $table->boolean('requires_justification')->default(false);
            
            // Configurações de acesso
            $table->json('eligible_groups')->nullable(); // Grupos elegíveis para votar
            $table->json('eligible_users')->nullable(); // Usuários específicos elegíveis
            $table->boolean('is_public')->default(false);
            
            // Resultados
            $table->json('results')->nullable(); // Resultados da votação
            $table->integer('total_votes')->default(0);
            $table->integer('total_participants')->default(0);
            $table->decimal('participation_rate', 5, 2)->default(0.00);
            $table->boolean('quorum_reached')->default(false);
            
            // Auditoria
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->json('metadata')->nullable(); // Dados adicionais
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['status', 'starts_at']);
            $table->index(['status', 'ends_at']);
            $table->index(['created_by']);
            $table->index(['type', 'status']);
            $table->index(['is_public', 'status']);
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