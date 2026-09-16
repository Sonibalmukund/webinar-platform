<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('polls', fn (Blueprint $table) => $table->string('answer_reveal', 30)->default('host_control')->after('allow_multiple'));
    }

    public function down(): void
    {
        Schema::table('polls', fn (Blueprint $table) => $table->dropColumn('answer_reveal'));
    }
};
