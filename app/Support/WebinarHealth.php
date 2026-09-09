<?php

namespace App\Support;

use App\Models\Webinar;
use Illuminate\Support\Facades\DB;

class WebinarHealth
{
    public static function score(Webinar $webinar): array
    {
        $registrations = $webinar->registrations()->whereIn('status', ['approved', 'attended', 'completed'])->count();
        $attended = DB::table('webinar_attendees')->where('webinar_id', $webinar->id)->where('watch_seconds', '>', 0)->count();
        $pollAnswers = DB::table('poll_responses')->join('polls', 'polls.id', '=', 'poll_responses.poll_id')->where('polls.webinar_id', $webinar->id)->distinct('poll_responses.user_id')->count('poll_responses.user_id');
        $attendanceRate = $registrations ? round($attended / $registrations * 100) : 0;
        $pollRate = $attended ? round($pollAnswers / $attended * 100) : 0;
        $capacity = $webinar->max_attendees ? min(100, round($registrations / $webinar->max_attendees * 100)) : min(100, $registrations * 5);
        $score = (int) round($attendanceRate * .5 + $pollRate * .3 + $capacity * .2);

        return compact('score', 'registrations', 'attended', 'attendanceRate', 'pollRate', 'capacity');
    }
}
