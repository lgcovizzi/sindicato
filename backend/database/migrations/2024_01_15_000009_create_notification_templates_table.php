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
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            
            // Identificação do template
            $table->string('name')->unique(); // Nome único do template
            $table->string('slug')->unique(); // Slug para identificação
            $table->string('category'); // news, voting, convenio, system, security
            $table->enum('type', ['email', 'push', 'sms', 'toast', 'in_app']);
            
            // Conteúdo do template
            $table->string('subject')->nullable(); // Para email
            $table->text('title'); // Título da notificação
            $table->text('body'); // Corpo da mensagem
            $table->text('action_text')->nullable(); // Texto do botão de ação
            $table->string('action_url')->nullable(); // URL da ação
            
            // Templates por canal
            $table->json('email_template')->nullable(); // Template específico para email
            $table->json('push_template')->nullable(); // Template específico para push
            $table->json('sms_template')->nullable(); // Template específico para SMS
            $table->json('toast_template')->nullable(); // Template específico para toast
            $table->json('in_app_template')->nullable(); // Template específico para in-app
            
            // Configurações de estilo
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('sound')->nullable();
            $table->json('style_config')->nullable(); // Configurações de estilo específicas
            
            // Variáveis do template
            $table->json('variables')->nullable(); // Variáveis disponíveis no template
            $table->json('default_values')->nullable(); // Valores padrão para variáveis
            $table->json('validation_rules')->nullable(); // Regras de validação para variáveis
            
            // Configurações de entrega
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_persistent')->default(false);
            $table->boolean('requires_action')->default(false);
            $table->integer('auto_dismiss_seconds')->nullable();
            $table->integer('retry_attempts')->default(3);
            $table->integer('retry_delay_seconds')->default(60);
            
            // Configurações de agendamento
            $table->boolean('can_be_scheduled')->default(true);
            $table->json('schedule_rules')->nullable(); // Regras de agendamento
            $table->json('quiet_hours')->nullable(); // Horários de silêncio
            
            // Segmentação
            $table->json('target_groups')->nullable(); // Grupos alvo padrão
            $table->json('target_roles')->nullable(); // Roles alvo padrão
            $table->json('exclude_conditions')->nullable(); // Condições de exclusão
            
            // Localização
            $table->string('language', 5)->default('pt-BR');
            $table->json('translations')->nullable(); // Traduções para outros idiomas
            
            // Status e controle
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // Template do sistema (não editável)
            $table->string('version')->default('1.0');
            $table->timestamp('last_used_at')->nullable();
            $table->integer('usage_count')->default(0);
            
            // Teste e validação
            $table->json('test_data')->nullable(); // Dados para teste do template
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('test_passed')->default(false);
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->json('change_log')->nullable(); // Log de mudanças
            
            // Metadados
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->json('tags')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['category', 'type']);
            $table->index(['is_active']);
            $table->index(['is_system']);
            $table->index(['language']);
            $table->index(['priority']);
            $table->index(['last_used_at']);
            $table->index(['usage_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};