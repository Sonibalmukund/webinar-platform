<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_campaigns', function (Blueprint $table) {
            $table->string('delivery_mode', 40)->default('instant')->after('audience')->index();
            $table->unsignedInteger('sent_count')->default(0)->after('sent_at');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('utm_source')->nullable()->after('source')->index();
            $table->string('utm_medium')->nullable()->after('utm_source');
            $table->string('utm_campaign')->nullable()->after('utm_medium')->index();
            $table->string('utm_content')->nullable()->after('utm_campaign');
            $table->string('utm_term')->nullable()->after('utm_content');
            $table->string('referral_code')->nullable()->after('utm_term')->index();
            $table->text('referrer_url')->nullable()->after('referral_code');
            $table->text('landing_url')->nullable()->after('referrer_url');
        });
    }

    public function down(): void
    {
        Schema::table('notification_campaigns', function (Blueprint $table) {
            $table->dropIndex(['delivery_mode']);
            $table->dropColumn(['delivery_mode', 'sent_count']);
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['utm_source']);
            $table->dropIndex(['utm_campaign']);
            $table->dropIndex(['referral_code']);
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referral_code', 'referrer_url', 'landing_url']);
        });
    }
};
