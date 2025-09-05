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
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // Additional images
            $table->foreignId('category_id')->constrained('news_categories')->onDelete('cascade');
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('editor_id')->nullable()->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'pending_review', 'published', 'archived', 'rejected'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_evergreen')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->boolean('send_notifications')->default(true);
            $table->integer('reading_time')->nullable(); // In minutes
            $table->integer('view_count')->default(0);
            $table->integer('like_count')->default(0);
            $table->integer('share_count')->default(0);
            $table->json('seo_meta')->nullable(); // SEO metadata
            $table->json('social_meta')->nullable(); // Social media metadata
            $table->json('metadata')->nullable(); // Additional metadata
            $table->timestamp('published_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['is_breaking', 'published_at']);
            $table->index(['is_pinned', 'published_at']);
            $table->index(['priority', 'published_at']);
            $table->index('slug');
            $table->index('view_count');
            $table->index('like_count');
            $table->index('share_count');
            $table->fullText(['title', 'excerpt', 'content']); // Full-text search
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