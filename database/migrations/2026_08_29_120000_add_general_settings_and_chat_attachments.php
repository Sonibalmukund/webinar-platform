<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('banners',function(Blueprint $table){$table->id();$table->foreignId('webinar_id')->constrained()->cascadeOnDelete();$table->string('title');$table->enum('media_type',['image','video']);$table->string('media_path')->nullable();$table->text('media_url')->nullable();$table->boolean('is_active')->default(true);$table->unsignedSmallInteger('display_order')->default(0);$table->timestamps();});
        Schema::create('brands',function(Blueprint $table){$table->id();$table->foreignId('webinar_id')->constrained()->cascadeOnDelete();$table->string('name');$table->string('logo_path')->nullable();$table->text('website_url')->nullable();$table->boolean('is_active')->default(true);$table->timestamps();});
        Schema::table('chat_messages',function(Blueprint $table){$table->string('attachment_path')->nullable()->after('message');$table->string('attachment_name')->nullable()->after('attachment_path');$table->string('attachment_mime',100)->nullable()->after('attachment_name');});
    }
    public function down(): void {Schema::table('chat_messages',fn(Blueprint $table)=>$table->dropColumn(['attachment_path','attachment_name','attachment_mime']));Schema::dropIfExists('brands');Schema::dropIfExists('banners');}
};
