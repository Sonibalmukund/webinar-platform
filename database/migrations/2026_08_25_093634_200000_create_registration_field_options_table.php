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
        Schema::create('registration_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_field_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('value');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_field_options');
    }
};
