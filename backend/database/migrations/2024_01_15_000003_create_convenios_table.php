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
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->text('short_description')->nullable();
            
            // Informações da empresa/estabelecimento
            $table->string('company_name');
            $table->string('company_document')->nullable(); // CNPJ
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('website')->nullable();
            
            // Categoria e tipo
            $table->string('category'); // saúde, educação, lazer, etc.
            $table->string('subcategory')->nullable();
            $table->enum('type', ['discount', 'service', 'product', 'benefit']);
            
            // Desconto e benefícios
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->text('benefits')->nullable(); // Descrição dos benefícios
            $table->json('terms_conditions')->nullable(); // Termos e condições
            
            // Localização
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Horário de funcionamento
            $table->json('business_hours')->nullable();
            
            // Status e validade
            $table->enum('status', ['active', 'inactive', 'pending', 'expired', 'suspended'])->default('pending');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            
            // Configurações de acesso
            $table->boolean('requires_card')->default(false); // Requer carteirinha
            $table->boolean('requires_qr_code')->default(false);
            $table->json('eligible_groups')->nullable(); // Grupos elegíveis
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('usage_limit_total')->nullable();
            
            // Mídia
            $table->string('logo_url')->nullable();
            $table->json('images')->nullable(); // URLs das imagens
            $table->string('qr_code_url')->nullable();
            
            // Estatísticas
            $table->integer('total_uses')->default(0);
            $table->integer('total_users')->default(0);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            
            // SEO
            $table->string('slug')->unique();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('tags')->nullable();
            
            // Auditoria
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['status', 'category']);
            $table->index(['city', 'state']);
            $table->index(['valid_from', 'valid_until']);
            $table->index(['rating', 'rating_count']);
            $table->index(['created_by']);
            $table->fullText(['name', 'description', 'company_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};