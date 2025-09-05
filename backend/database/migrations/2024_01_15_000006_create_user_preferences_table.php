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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Preferências de tema
            $table->enum('theme', ['light', 'dark', 'system'])->default('system');
            $table->enum('density', ['compact', 'normal', 'spacious'])->default('normal');
            $table->string('primary_color')->default('#1976d2');
            $table->string('accent_color')->default('#ff4081');
            $table->boolean('high_contrast')->default(false);
            $table->boolean('reduce_motion')->default(false);
            
            // Preferências de idioma e localização
            $table->string('language', 5)->default('pt-BR');
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->string('date_format')->default('d/m/Y');
            $table->string('time_format')->default('H:i');
            $table->string('currency')->default('BRL');
            
            // Preferências de notificação
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('browser_notifications')->default(true);
            
            // Tipos específicos de notificação
            $table->boolean('notify_news')->default(true);
            $table->boolean('notify_voting')->default(true);
            $table->boolean('notify_convenios')->default(true);
            $table->boolean('notify_events')->default(true);
            $table->boolean('notify_system')->default(true);
            $table->boolean('notify_security')->default(true);
            
            // Configurações de notificação por horário
            $table->time('quiet_hours_start')->nullable(); // Início do período silencioso
            $table->time('quiet_hours_end')->nullable(); // Fim do período silencioso
            $table->json('quiet_days')->nullable(); // Dias da semana para silenciar
            
            // Preferências de privacidade
            $table->boolean('profile_public')->default(false);
            $table->boolean('show_online_status')->default(true);
            $table->boolean('allow_contact')->default(true);
            $table->boolean('data_analytics')->default(true);
            
            // Preferências de interface
            $table->boolean('show_tooltips')->default(true);
            $table->boolean('show_animations')->default(true);
            $table->boolean('auto_save')->default(true);
            $table->integer('items_per_page')->default(20);
            $table->enum('sidebar_collapsed', ['always', 'never', 'auto'])->default('auto');
            
            // Preferências de segurança
            $table->boolean('two_factor_enabled')->default(false);
            $table->boolean('biometric_login')->default(false);
            $table->boolean('remember_device')->default(true);
            $table->integer('session_timeout')->default(120); // minutos
            
            // Preferências de conteúdo
            $table->json('favorite_categories')->nullable(); // Categorias favoritas
            $table->json('blocked_categories')->nullable(); // Categorias bloqueadas
            $table->json('favorite_tags')->nullable(); // Tags favoritas
            $table->boolean('show_mature_content')->default(false);
            
            // Configurações de dispositivo
            $table->string('device_id')->nullable();
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->json('device_settings')->nullable();
            
            // Metadados
            $table->json('custom_settings')->nullable(); // Configurações personalizadas
            $table->timestamp('last_sync_at')->nullable();
            $table->json('sync_devices')->nullable(); // Dispositivos sincronizados
            
            $table->timestamps();
            
            // Índices
            $table->unique('user_id');
            $table->index(['theme']);
            $table->index(['language']);
            $table->index(['timezone']);
            $table->index(['device_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};