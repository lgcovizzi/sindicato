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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Informações da atividade
            $table->string('action'); // login, logout, vote, view_convenio, etc.
            $table->string('description');
            $table->enum('type', ['auth', 'voting', 'convenio', 'news', 'system', 'security', 'preference']);
            $table->enum('level', ['info', 'warning', 'error', 'critical'])->default('info');
            
            // Dados do objeto afetado
            $table->string('subject_type')->nullable(); // Tipo do modelo (User, Voting, etc.)
            $table->unsignedBigInteger('subject_id')->nullable(); // ID do modelo
            $table->json('subject_data')->nullable(); // Dados do objeto antes da ação
            $table->json('changes')->nullable(); // Mudanças realizadas
            
            // Dados da requisição
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type')->nullable(); // web, mobile, api
            $table->string('device_id')->nullable();
            $table->string('session_id')->nullable();
            
            // Dados de localização
            $table->string('country')->nullable();
            $table->string('region')->nullable();
            $table->string('city')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Dados de segurança
            $table->boolean('is_suspicious')->default(false);
            $table->string('risk_level')->default('low'); // low, medium, high, critical
            $table->json('security_flags')->nullable(); // Flags de segurança
            $table->string('authentication_method')->nullable(); // password, biometric, 2fa
            
            // Dados de performance
            $table->integer('response_time_ms')->nullable();
            $table->integer('memory_usage_mb')->nullable();
            $table->integer('query_count')->nullable();
            
            // Dados contextuais
            $table->string('route')->nullable(); // Rota acessada
            $table->string('method')->nullable(); // GET, POST, PUT, DELETE
            $table->json('request_data')->nullable(); // Dados da requisição (sanitizados)
            $table->json('response_data')->nullable(); // Dados da resposta (sanitizados)
            $table->integer('status_code')->nullable();
            
            // Dados de auditoria
            $table->string('correlation_id')->nullable(); // ID para correlacionar ações relacionadas
            $table->string('batch_id')->nullable(); // ID para ações em lote
            $table->json('tags')->nullable(); // Tags para categorização
            
            // Metadados
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            
            $table->timestamps();
            
            // Índices
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['type', 'created_at']);
            $table->index(['level', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['ip_address', 'created_at']);
            $table->index(['is_suspicious', 'created_at']);
            $table->index(['risk_level', 'created_at']);
            $table->index(['correlation_id']);
            $table->index(['batch_id']);
            $table->index(['device_id', 'created_at']);
            $table->index(['session_id']);
            $table->index(['is_archived']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};