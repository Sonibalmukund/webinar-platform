<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('polls')->where('answer_reveal', 'host_control')->update(['answer_reveal' => 'after_webinar']);
        Schema::table('polls', fn (Blueprint $table) => $table->string('answer_reveal', 30)->default('after_webinar')->change());
    }

    public function down(): void
    {
        DB::table('polls')->where('answer_reveal', 'after_webinar')->update(['answer_reveal' => 'host_control']);
        Schema::table('polls', fn (Blueprint $table) => $table->string('answer_reveal', 30)->default('host_control')->change());
    }
};
