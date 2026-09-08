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
        Schema::create('speaker_webinar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('speaker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webinar_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('speaker');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->unique(['speaker_id', 'webinar_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speaker_webinar');
    }
};
