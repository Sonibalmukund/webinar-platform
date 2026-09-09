<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile', 30)->nullable()->after('email');
            $table->string('avatar_path')->nullable()->after('password');
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->text('bio')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('last_login_at')->nullable();
        });
        Schema::table('webinars', function (Blueprint $table) {
            $table->timestamp('registration_deadline')->nullable()->after('published_at');
            $table->string('live_provider', 30)->nullable();
            $table->text('live_url')->nullable();
            $table->string('promo_video_path')->nullable();
            $table->string('certificate_enabled')->default('yes');
            $table->boolean('chat_enabled')->default(true);
            $table->boolean('qa_enabled')->default(true);
            $table->boolean('polls_enabled')->default(true);
            $table->boolean('auto_approve')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('webinars', fn (Blueprint $table) => $table->dropColumn(['registration_deadline', 'live_provider', 'live_url', 'promo_video_path', 'certificate_enabled', 'chat_enabled', 'qa_enabled', 'polls_enabled', 'auto_approve']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['mobile', 'avatar_path', 'job_title', 'company', 'bio', 'timezone', 'status', 'last_login_at']));
    }
};
