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
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Conteúdo da notificação
            $table->string('title');
            $table->text('message');
            $table->text('action_text')->nullable(); // Texto do botão de ação
            $table->string('action_url')->nullable(); // URL da ação
            $table->json('action_data')->nullable(); // Dados adicionais da ação
            
            // Tipo e categoria
            $table->enum('type', ['info', 'success', 'warning', 'error', 'system']);
            $table->string('category'); // news, voting, convenio, system, security
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Configurações de exibição
            $table->boolean('is_persistent')->default(false); // Não remove automaticamente
            $table->boolean('requires_action')->default(false); // Requer ação do usuário
            $table->boolean('is_dismissible')->default(true); // Pode ser dispensada
            $table->integer('auto_dismiss_seconds')->nullable(); // Auto-dispensar após X segundos
            
            // Canais de entrega
            $table->boolean('send_email')->default(false);
            $table->boolean('send_push')->default(true);
            $table->boolean('send_sms')->default(false);
            $table->boolean('show_toast')->default(true);
            $table->boolean('show_in_app')->default(true);
            
            // Status de entrega
            $table->boolean('email_sent')->default(false);
            $table->boolean('push_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamp('push_sent_at')->nullable();
            $table->timestamp('sms_sent_at')->nullable();
            
            // Status de leitura
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->boolean('action_taken')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('action_taken_at')->nullable();
            
            // Agendamento
            $table->timestamp('scheduled_for')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Segmentação
            $table->json('target_groups')->nullable(); // Grupos alvo
            $table->json('target_roles')->nullable(); // Roles alvo
            $table->json('exclude_users')->nullable(); // Usuários excluídos
            
            // Dados relacionados
            $table->string('related_type')->nullable(); // Tipo do modelo relacionado
            $table->unsignedBigInteger('related_id')->nullable(); // ID do modelo relacionado
            $table->json('related_data')->nullable(); // Dados adicionais
            
            // Metadados
            $table->json('metadata')->nullable();
            $table->string('tracking_id')->nullable(); // ID para rastreamento
            $table->json('delivery_attempts')->nullable(); // Tentativas de entrega
            
            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'is_read']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type', 'category']);
            $table->index(['priority', 'created_at']);
            $table->index(['scheduled_for']);
            $table->index(['expires_at']);
            $table->index(['related_type', 'related_id']);
            $table->index(['tracking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};