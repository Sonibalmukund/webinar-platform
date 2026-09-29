<?php

namespace App\Support;

use App\Mail\CommunicationMail;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NotificationCampaignService
{
    public function sendEmail(int $campaignId): int
    {
        $campaign = DB::transaction(function () use ($campaignId) {
            $row = DB::table('notification_campaigns')->where('id', $campaignId)->lockForUpdate()->first();
            if (! $row || ! in_array($row->status, ['draft', 'scheduled'], true)) {
                return null;
            }
            DB::table('notification_campaigns')->where('id', $campaignId)->update(['status' => 'processing', 'updated_at' => now()]);

            return $row;
        });
        if (! $campaign) {
            return 0;
        }

        $users = User::query()->whereHas('registrations')
            ->when($campaign->audience === 'webinar', fn ($q) => $q->whereHas('registrations', fn ($registration) => $registration->where('webinar_id', $campaign->webinar_id)))
            ->get();

        $webinar = $campaign->webinar_id ? Webinar::find($campaign->webinar_id) : null;
        $sent = 0;
        foreach ($users as $user) {
            if (! $user->email || str_ends_with((string) $user->email, '@internal.local')) {
                continue;
            }
            try {
                Mail::to($user->email)->send(new CommunicationMail(
                    $user,
                    $webinar,
                    $this->render($campaign->subject, $user, $webinar),
                    $this->render($campaign->message, $user, $webinar),
                    $campaign->attachment_path,
                    $campaign->attachment_name,
                    $campaign->attachment_mime,
                ));
                $sent++;
                $this->logDelivery($campaign, $user, 'sent');
            } catch (\Throwable $e) {
                $this->logDelivery($campaign, $user, 'failed', $e->getMessage());
                report($e);
            }
        }

        DB::table('notification_campaigns')->where('id', $campaignId)->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => $sent, 'updated_at' => now()]);

        return $sent;
    }

    public function sendAfterRegistration(Webinar $webinar, User $user): void
    {
        if (str_ends_with((string) $user->email, '@internal.local')) {
            return;
        }
        $campaigns = DB::table('notification_campaigns')->where('webinar_id', $webinar->id)->where('delivery_mode', 'after_registration_email')->where('status', 'active')->get();
        foreach ($campaigns as $campaign) {
            try {
                Mail::to($user->email)->send(new CommunicationMail(
                    $user,
                    $webinar,
                    $this->render($campaign->subject, $user, $webinar),
                    $this->render($campaign->message, $user, $webinar),
                    $campaign->attachment_path,
                    $campaign->attachment_name,
                    $campaign->attachment_mime,
                ));
                DB::table('notification_campaigns')->where('id', $campaign->id)->increment('sent_count');
                $this->logDelivery($campaign, $user, 'sent');
            } catch (\Throwable $e) {
                $this->logDelivery($campaign, $user, 'failed', $e->getMessage());
                report($e);
            }
        }
    }

    private function logDelivery(object $campaign, User $user, string $status, ?string $error = null): void
    {
        DB::table('notification_delivery_logs')->insert([
            'campaign_id' => $campaign->id,
            'user_id' => $user->id,
            'channel' => 'email',
            'recipient' => $user->email,
            'status' => $status,
            'error_message' => $error ? Str::limit($error, 2000, '') : null,
            'sent_at' => $status === 'sent' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function render(string $text, User $user, ?Webinar $webinar): string
    {
        return strtr($text, [
            '{name}' => $user->name,
            '{webinar}' => $webinar?->title ?? 'the webinar',
            '{date}' => $webinar?->starts_at?->timezone($webinar->timezone)->format('M d, Y · g:i A') ?? 'To be announced',
        ]);
    }
}
