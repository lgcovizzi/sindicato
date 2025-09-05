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
        Schema::create('convenio_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convenio_id')->constrained('convenios')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Dados do uso
            $table->decimal('original_amount', 10, 2)->nullable(); // Valor original
            $table->decimal('discount_amount', 10, 2)->nullable(); // Valor do desconto
            $table->decimal('final_amount', 10, 2)->nullable(); // Valor final
            $table->text('description')->nullable(); // Descrição do que foi usado
            
            // Método de verificação
            $table->enum('verification_method', ['qr_code', 'card', 'app', 'manual']);
            $table->string('verification_code')->nullable();
            $table->boolean('is_verified')->default(false);
            
            // Localização do uso
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('location_name')->nullable();
            
            // Dados de auditoria
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_id')->nullable();
            $table->string('device_type')->nullable();
            
            // Avaliação
            $table->integer('rating')->nullable(); // 1-5 estrelas
            $table->text('review')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'disputed'])->default('pending');
            $table->text('notes')->nullable();
            
            $table->timestamp('used_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index(['convenio_id', 'used_at']);
            $table->index(['user_id', 'used_at']);
            $table->index(['status']);
            $table->index(['is_verified']);
            $table->index(['rating']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('convenio_usages');
    }
};