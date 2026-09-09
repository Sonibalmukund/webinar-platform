<?php

namespace App\Support;

use App\Models\Webinar;
use Illuminate\Support\Facades\DB;

class WebinarExperience
{
    public static function metrics(Webinar $webinar, int $userId): array
    {
        $attendance = DB::table('webinar_attendees')->where(['webinar_id' => $webinar->id, 'user_id' => $userId])->first();
        $duration = max(1, $webinar->starts_at && $webinar->ends_at ? $webinar->starts_at->diffInSeconds($webinar->ends_at) : 3600);
        $watch = (int) ($attendance->watch_seconds ?? 0);
        $attendancePercent = min(100, (int) round(($watch / $duration) * 100));
        $pollAnswers = DB::table('poll_responses')->join('polls', 'polls.id', '=', 'poll_responses.poll_id')->where('polls.webinar_id', $webinar->id)->where('poll_responses.user_id', $userId)->count();
        $engagement = min(100, (int) round(($attendancePercent * .75) + ($pollAnswers > 0 ? 20 : 0) + ($attendance ? 5 : 0)));
        $settings = data_get($webinar->settings, 'experience', []);
        $minimum = (int) ($settings['certificate_min_attendance'] ?? 80);
        $pollRequired = (bool) ($settings['certificate_require_poll'] ?? false);

        return compact('watch', 'attendancePercent', 'pollAnswers', 'engagement') + ['eligible' => $attendancePercent >= $minimum && (! $pollRequired || $pollAnswers > 0), 'minimum' => $minimum, 'pollRequired' => $pollRequired];
    }
}
