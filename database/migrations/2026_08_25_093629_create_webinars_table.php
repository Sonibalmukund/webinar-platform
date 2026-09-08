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
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->string('language', 10)->default('en');
            $table->string('timezone')->default('UTC');
            $table->dateTime('starts_at')->nullable()->index();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('max_attendees')->nullable();
            $table->string('registration_type', 20)->default('free');
            $table->decimal('price', 10, 2)->nullable();
            $table->string('banner_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webinars');
    }
};
