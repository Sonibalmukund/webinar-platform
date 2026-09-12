<?php

namespace App\Console\Commands;

use App\Models\Webinar;
use Illuminate\Console\Command;

class RunWebinarAutomation extends Command
{
    protected $signature = 'webinars:automate';

    protected $description = 'Advance webinar lifecycle and promote waitlisted registrations';

    public function handle(): int
    {
        // Auto-turn ON (live) 30 minutes before scheduled start
        Webinar::where('status', 'scheduled')
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', now()->addMinutes(30))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()->subMinutes(30)))
            ->each(fn ($w) => $w->update(['status' => 'live']));

        // Auto-turn OFF (completed) 30 minutes after scheduled end
        Webinar::whereIn('status', ['scheduled', 'live'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now()->subMinutes(30))
            ->each(fn ($w) => $w->update(['status' => 'completed']));
        Webinar::whereNotNull('max_attendees')->each(function ($w) {
            $used = $w->registrations()->admitted()->count();
            $available = max(0, $w->max_attendees - $used);
            if ($available) {
                $w->registrations()->where('status', 'waitlisted')->oldest('registered_at')->limit($available)->update(['status' => 'approved', 'approved_at' => now()]);
            }
        });
        $this->info('Webinar automation completed.');

        return self::SUCCESS;
    }
}
