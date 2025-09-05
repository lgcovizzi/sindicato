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
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('terms_and_conditions')->nullable();
            $table->foreignId('category_id')->constrained('convenio_categories')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'pending_approval', 'active', 'inactive', 'expired', 'cancelled'])->default('draft');
            $table->enum('type', ['discount', 'service', 'product', 'partnership'])->default('discount');
            $table->enum('availability', ['online', 'physical', 'both'])->default('both');
            $table->string('company_name');
            $table->string('company_cnpj', 14)->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('website_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->string('discount_code')->nullable();
            $table->text('how_to_use')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('requires_membership')->default(true);
            $table->json('eligible_users')->nullable(); // User criteria
            $table->json('business_hours')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('rating_count')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'valid_until']);
            $table->index(['category_id', 'status']);
            $table->index(['city', 'state']);
            $table->index(['latitude', 'longitude']);
            $table->index(['is_featured', 'status']);
            $table->index(['valid_from', 'valid_until']);
            $table->index('slug');
            $table->index('rating');
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