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
        Webinar::where('status', 'scheduled')->whereNotNull('starts_at')->where('starts_at', '<=', now())->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))->each(fn ($w) => $w->update(['status' => 'live']));
        Webinar::whereIn('status', ['scheduled', 'live'])->whereNotNull('ends_at')->where('ends_at', '<=', now())->each(fn ($w) => $w->update(['status' => 'completed']));
        Webinar::whereNotNull('max_attendees')->each(function ($w) {
            $used = $w->registrations()->where('status', 'approved')->count();
            $available = max(0, $w->max_attendees - $used);
            if ($available) {
                $w->registrations()->where('status', 'waitlisted')->oldest('registered_at')->limit($available)->update(['status' => 'approved', 'approved_at' => now()]);
            }
        });
        $this->info('Webinar automation completed.');

        return self::SUCCESS;
    }
}
