<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('webinars', fn (Blueprint $table) => $table->string('icon', 60)->default('camera-video')->after('title'));
    }

    public function down(): void
    {
        Schema::table('webinars', fn (Blueprint $table) => $table->dropColumn('icon'));
    }
};
