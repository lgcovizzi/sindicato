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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('excerpt')->nullable(); // Resumo da notícia
            $table->longText('content');
            
            // Categoria e classificação
            $table->string('category'); // sindical, geral, urgente, etc.
            $table->string('subcategory')->nullable();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->json('tags')->nullable();
            
            // Status e publicação
            $table->enum('status', ['draft', 'review', 'scheduled', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Configurações de visibilidade
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false); // Notícia urgente
            $table->boolean('is_public')->default(true); // Visível para não-membros
            $table->boolean('allow_comments')->default(true);
            $table->boolean('send_notification')->default(false);
            
            // Grupos de acesso
            $table->json('target_groups')->nullable(); // Grupos específicos
            $table->json('exclude_groups')->nullable(); // Grupos excluídos
            
            // Mídia
            $table->string('featured_image')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->json('gallery')->nullable(); // Array de imagens
            $table->json('attachments')->nullable(); // Documentos anexos
            $table->string('video_url')->nullable();
            
            // SEO
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('meta_keywords')->nullable();
            
            // Estatísticas
            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);
            $table->integer('shares_count')->default(0);
            $table->integer('comments_count')->default(0);
            $table->decimal('reading_time', 5, 2)->nullable(); // Tempo estimado de leitura
            
            // Configurações de notificação
            $table->boolean('notification_sent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            $table->integer('notification_recipients')->default(0);
            
            // Auditoria
            $table->foreignId('author_id')->constrained('users');
            $table->foreignId('editor_id')->nullable()->constrained('users');
            $table->foreignId('published_by')->nullable()->constrained('users');
            $table->json('revision_history')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['status', 'published_at']);
            $table->index(['category', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['is_breaking', 'published_at']);
            $table->index(['priority', 'published_at']);
            $table->index(['author_id']);
            $table->index(['views_count']);
            $table->fullText(['title', 'subtitle', 'excerpt', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};