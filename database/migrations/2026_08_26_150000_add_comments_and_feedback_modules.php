<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('webinars',function(Blueprint $table){$table->boolean('comments_enabled')->default(false);$table->boolean('feedback_enabled')->default(false);}); Schema::create('comments',function(Blueprint $table){$table->id();$table->foreignId('webinar_id')->constrained()->cascadeOnDelete();$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();$table->text('comment');$table->string('status',30)->default('visible')->index();$table->timestamps();}); } public function down(): void { Schema::dropIfExists('comments'); Schema::table('webinars',fn(Blueprint $table)=>$table->dropColumn(['comments_enabled','feedback_enabled'])); } };
