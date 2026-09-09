<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module');
            $table->timestamps();
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unique(['permission_id', 'role_id']);
        });
        Schema::create('user_webinar_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webinar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unique(['user_id', 'webinar_id']);
            $table->timestamps();
        });
        Schema::create('notification_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('webinar_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->longText('message');
            $table->json('channels');
            $table->string('audience', 40);
            $table->string('status', 30)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('orientation', 20)->default('landscape');
            $table->json('design');
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();
            $table->foreignId('webinar_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('credential_id')->unique();
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->unique(['webinar_id', 'user_id']);
            $table->timestamps();
        });
        Schema::create('cms_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page')->index();
            $table->string('section_key');
            $table->string('title')->nullable();
            $table->json('content');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->unique(['page', 'section_key']);
            $table->timestamps();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->index();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('subject');
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'settings', 'cms_sections', 'certificates', 'certificate_templates', 'user_notifications', 'notification_campaigns', 'user_webinar_assignments', 'permission_role', 'permissions'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
