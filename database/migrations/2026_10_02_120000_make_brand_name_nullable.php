<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('brands')->whereNull('name')->update(['name' => '']);

        Schema::table('brands', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }
};
