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
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webinar_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('title')->default('Registration Form');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('require_login')->default(true);
            $table->text('success_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_forms');
    }
};
