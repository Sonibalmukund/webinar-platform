<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_campaigns', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('message');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime', 120)->nullable()->after('attachment_name');
        });

        DB::table('notification_campaigns')->where('delivery_mode', 'scheduled_notification')->update([
            'delivery_mode' => 'reminder_now',
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
        DB::table('notification_campaigns')->whereIn('delivery_mode', ['instant', 'instant_notification'])->update(['delivery_mode' => 'reminder_now']);
    }

    public function down(): void
    {
        Schema::table('notification_campaigns', fn (Blueprint $table) => $table->dropColumn(['attachment_path', 'attachment_name', 'attachment_mime']));
    }
};
