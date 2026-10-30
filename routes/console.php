<?php

use App\Support\NotificationCampaignService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:send-scheduled', function () {
    $service = app(NotificationCampaignService::class);
    $campaignIds = DB::table('notification_campaigns')
        ->where('status', 'scheduled')
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', now())
        ->orderBy('scheduled_at')
        ->pluck('id');

    foreach ($campaignIds as $campaignId) {
        $service->sendEmail((int) $campaignId);
    }

    $this->info($campaignIds->count().' scheduled reminder(s) processed.');
})->purpose('Send notification campaigns whose scheduled time has arrived.');
Schedule::command('webinars:automate')->everyMinute()->withoutOverlapping();
Schedule::command('notifications:send-scheduled')->everyMinute()->withoutOverlapping();
