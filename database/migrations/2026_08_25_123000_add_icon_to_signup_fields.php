<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('signup_fields',fn(Blueprint $table)=>$table->string('icon',60)->default('input-cursor-text')->after('label')); } public function down(): void { Schema::table('signup_fields',fn(Blueprint $table)=>$table->dropColumn('icon')); } };
