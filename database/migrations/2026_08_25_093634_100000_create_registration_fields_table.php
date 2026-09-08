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
        Schema::create('registration_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_form_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_key');
            $table->string('field_type', 30);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->unique(['registration_form_id', 'field_key']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_fields');
    }
};
