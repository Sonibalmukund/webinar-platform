<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Webinar;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\User;
use App\Models\Poll;
use Illuminate\Support\Facades\DB;
use App\Support\WebinarExperience;

class DashboardController extends Controller
{
    public function learner(Request $request): View
    {
        $registrations=Registration::with('webinar')->where('user_id',$request->user()->id)->latest('registered_at')->get();
        $scheduledWebinars=$registrations
            ->filter(fn($registration)=>$registration->webinar?->starts_at && ($registration->webinar->status==='live' || ($registration->webinar->status==='scheduled' && $registration->webinar->starts_at->isFuture())))
            ->sortBy(fn($registration)=>$registration->webinar->starts_at)
            ->values();

        return view('pages.user.dashboard',[
            'registrations'=>$registrations,
            'scheduledWebinars'=>$scheduledWebinars,
            'upcoming'=>$scheduledWebinars->first()?->webinar,
        ]);
    }

    public function myWebinars(Request $request): View
    {
        $registrations=Registration::with(['webinar.creator'])
            ->where('user_id',$request->user()->id)
            ->latest('registered_at')
            ->get()
            ->filter(fn($registration)=>$registration->webinar)
            ->values();

        $attendance=DB::table('webinar_attendees')
            ->where('user_id',$request->user()->id)
            ->whereIn('webinar_id',$registrations->pluck('webinar_id'))
            ->get()
            ->keyBy('webinar_id');

        $rows=$registrations->map(function($registration) use($request,$attendance){
            $metrics=WebinarExperience::metrics($registration->webinar,$request->user()->id);
            return ['registration'=>$registration,'webinar'=>$registration->webinar,'attendance'=>$attendance->get($registration->webinar_id),'metrics'=>$metrics];
        });

        return view('pages.user.my-webinars',compact('rows'));
    }

    public function recordings(Request $request): View
    {
        $recordings=DB::table('webinar_recordings')
            ->join('webinars','webinars.id','=','webinar_recordings.webinar_id')
            ->join('registrations',fn($join)=>$join->on('registrations.webinar_id','=','webinars.id')->where('registrations.user_id',$request->user()->id))
            ->where('webinars.status','completed')->where('webinar_recordings.status','published')->whereNotNull('webinar_recordings.published_at')
            ->select('webinar_recordings.*','webinars.title as webinar_title','webinars.slug as webinar_slug')->latest('webinar_recordings.published_at')->get();
        return view('pages.user.library',['type'=>'recordings','items'=>$recordings]);
    }

    public function bookmarks(Request $request): View
    {
        $bookmarks=DB::table('webinar_bookmarks')->join('webinars','webinars.id','=','webinar_bookmarks.webinar_id')
            ->where('webinar_bookmarks.user_id',$request->user()->id)->select('webinars.*','webinar_bookmarks.created_at as bookmarked_at')->latest('webinar_bookmarks.created_at')->get();
        return view('pages.user.library',['type'=>'bookmarks','items'=>$bookmarks]);
    }

    public function certificates(Request $request): View
    {
        $certificates=DB::table('certificates')->join('webinars','webinars.id','=','certificates.webinar_id')
            ->where('certificates.user_id',$request->user()->id)->where('certificates.status','approved')->whereNull('certificates.revoked_at')
            ->select('certificates.*','webinars.title as webinar_title','webinars.slug as webinar_slug')->latest('certificates.issued_at')->get();
        return view('pages.user.library',['type'=>'certificates','items'=>$certificates]);
    }

    public function admin(Request $request): View
    {
        $subadmin=$request->user()->hasRole('sub-admin');
        $webinarIds=$subadmin ? $request->user()->assignedWebinars()->pluck('webinars.id') : null;
        $webinars=Webinar::withCount(['registrations','polls'])->when($subadmin,fn($query)=>$query->whereIn('id',$webinarIds))->latest()->get();
        $registrations=Registration::with(['user','webinar'])->when($subadmin,fn($query)=>$query->whereIn('webinar_id',$webinarIds))->latest('registered_at')->get();
        $attendance=DB::table('webinar_attendees')->when($subadmin,fn($query)=>$query->whereIn('webinar_id',$webinarIds))->get();
        $pollResponses=DB::table('poll_responses')->join('polls','polls.id','=','poll_responses.poll_id')->when($subadmin,fn($query)=>$query->whereIn('polls.webinar_id',$webinarIds))->select('poll_responses.*')->get();
        $days=collect(range(6,0))->map(fn($offset)=>now()->subDays($offset)->startOfDay());
        $chart=$days->map(function($day) use($registrations,$attendance){
            return ['label'=>$day->format('D'),'registrations'=>$registrations->filter(fn($row)=>($row->registered_at??$row->created_at)?->isSameDay($day))->count(),'attendees'=>$attendance->filter(fn($row)=>$row->joined_at && \Illuminate\Support\Carbon::parse($row->joined_at)->isSameDay($day))->count()];
        });
        $rawChartMax=(int)$chart->flatMap(fn($row)=>[$row['registrations'],$row['attendees']])->max();
        $maxChart=max(8,(int)(ceil($rawChartMax/2)*2));
        $eventPerformance=$webinars->map(function($webinar) use($attendance,$pollResponses){
            $pollIds=$webinar->polls()->pluck('id');
            return ['webinar'=>$webinar,'attendees'=>$attendance->where('webinar_id',$webinar->id)->count(),'votes'=>$pollResponses->whereIn('poll_id',$pollIds)->count()];
        });
        return view('pages.admin.dashboard',[
            'webinars'=>$webinars,'subadmin'=>$subadmin,'totalRegistrations'=>$registrations->count(),'registeredUsers'=>$subadmin?$registrations->pluck('user_id')->unique()->count():User::whereHas('roles',fn($q)=>$q->where('slug','learner'))->count(),
            'todayRegistrations'=>$registrations->filter(fn($row)=>($row->registered_at??$row->created_at)?->isToday())->count(),
            'totalAttendees'=>$attendance->count(),'liveNow'=>$attendance->filter(fn($row)=>$row->last_seen_at && \Illuminate\Support\Carbon::parse($row->last_seen_at)->greaterThan(now()->subMinutes(5)))->count(),
            'pollsCount'=>$subadmin?Poll::whereIn('webinar_id',$webinarIds)->count():Poll::count(),'pollVoters'=>$pollResponses->pluck('user_id')->unique()->count(),'votesCount'=>$pollResponses->count(),
            'chatMessages'=>DB::table('chat_messages')->when($subadmin,fn($query)=>$query->whereIn('webinar_id',$webinarIds))->count(),'questionsCount'=>DB::table('questions')->when($subadmin,fn($query)=>$query->whereIn('webinar_id',$webinarIds))->count(),
            'watchSeconds'=>(int)$attendance->sum('watch_seconds'),'recentRegistrations'=>$registrations->take(6),'chart'=>$chart,'maxChart'=>$maxChart,'eventPerformance'=>$eventPerformance,
            'waitlistCount'=>$registrations->where('status','waitlisted')->count(),'pendingCertificates'=>DB::table('certificates')->where('status','pending')->when($subadmin,fn($query)=>$query->whereIn('webinar_id',$webinarIds))->count(),'missingVideo'=>$webinars->whereNull('live_url')->count(),'upcomingSoon'=>$webinars->filter(fn($w)=>$w->starts_at?->between(now(),now()->addDay()))->count(),
        ]);
    }
}
