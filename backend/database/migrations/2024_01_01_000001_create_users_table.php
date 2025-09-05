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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('cpf', 11)->unique();
            $table->string('phone', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->string('avatar')->nullable();
            $table->enum('role', ['admin', 'moderator', 'member', 'guest'])->default('member');
            $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])->default('pending');
            $table->string('registration_number')->unique()->nullable();
            $table->date('admission_date')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->decimal('salary', 10, 2)->nullable();
            $table->boolean('is_board_member')->default(false);
            $table->boolean('can_vote')->default(true);
            $table->boolean('can_create_votings')->default(false);
            $table->boolean('receives_notifications')->default(true);
            $table->json('preferences')->nullable();
            $table->json('biometric_data')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'role']);
            $table->index(['city', 'state']);
            $table->index(['department', 'position']);
            $table->index('last_login_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};