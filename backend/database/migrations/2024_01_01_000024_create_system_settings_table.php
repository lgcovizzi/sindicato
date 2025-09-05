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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, array
            $table->string('group')->default('general'); // general, voting, convenio, news, notification
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false); // Can be accessed by frontend
            $table->boolean('is_editable')->default(true); // Can be edited through admin panel
            $table->json('validation_rules')->nullable(); // Validation rules for the setting
            $table->json('options')->nullable(); // Available options for select/radio inputs
            $table->integer('sort_order')->default(0);
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Indexes
            $table->index(['group', 'sort_order']);
            $table->index(['is_public', 'group']);
            $table->index(['is_editable', 'group']);
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};