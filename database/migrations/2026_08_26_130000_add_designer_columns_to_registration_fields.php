<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_fields', function (Blueprint $table) {
            $table->string('icon', 60)->default('input-cursor-text')->after('field_type');
            $table->string('width', 20)->default('full')->after('icon');
            $table->boolean('login_enabled')->default(false)->after('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('registration_fields', fn (Blueprint $table) => $table->dropColumn(['icon', 'width', 'login_enabled']));
    }
};
