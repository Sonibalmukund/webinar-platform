<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $webinars = Webinar::when($request->user()->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $request->user()->assignedWebinars()->pluck('webinars.id')))->orderBy('title')->get();
        $selected = $request->filled('webinar_id') ? Webinar::findOrFail($request->integer('webinar_id')) : null;
        $webinarIds = $selected ? collect([$selected->id]) : $webinars->pluck('id');
        $registrations = Registration::with(['user', 'webinar'])->whereIn('webinar_id', $webinarIds)->get();
        $attendees = DB::table('webinar_attendees')->whereIn('webinar_id', $webinarIds)->get();
        $polls = DB::table('polls')->whereIn('webinar_id', $webinarIds)->get();
        $votes = DB::table('poll_responses')->whereIn('poll_id', $polls->pluck('id'))->get();
        $days = collect(range(13, 0))->map(fn ($offset) => now()->subDays($offset)->startOfDay());
        $trend = $days->map(fn ($day) => ['label' => $day->format('d M'), 'registrations' => $registrations->filter(fn ($row) => ($row->registered_at ?? $row->created_at)?->isSameDay($day))->count(), 'attendees' => $attendees->filter(fn ($row) => $row->joined_at && Carbon::parse($row->joined_at)->isSameDay($day))->count()]);
        $rawTrendMax = (int) $trend->flatMap(fn ($row) => [$row['registrations'], $row['attendees']])->max();
        $maxTrend = max(8, (int) (ceil($rawTrendMax / 2) * 2));
        $performance = $webinars->whereIn('id', $webinarIds)->map(function ($webinar) use ($registrations, $attendees, $polls, $votes) {
            $eventPolls = $polls->where('webinar_id', $webinar->id);
            $registered = $registrations->where('webinar_id', $webinar->id)->count();
            $attended = $attendees->where('webinar_id', $webinar->id)->count();

            return ['webinar' => $webinar, 'registered' => $registered, 'attended' => $attended, 'rate' => $registered ? round($attended / $registered * 100) : 0, 'polls' => $eventPolls->count(), 'votes' => $votes->whereIn('poll_id', $eventPolls->pluck('id'))->count(), 'watch' => (int) $attendees->where('webinar_id', $webinar->id)->sum('watch_seconds')];
        });

        return view('pages.admin.reports.index', ['webinars' => $webinars, 'selected' => $selected, 'registrations' => $registrations, 'attendees' => $attendees, 'polls' => $polls, 'votes' => $votes, 'trend' => $trend, 'maxTrend' => $maxTrend, 'performance' => $performance, 'watchSeconds' => (int) $attendees->sum('watch_seconds')]);
    }
}
