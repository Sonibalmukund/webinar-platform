<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        $admin = request()->user();

        return view('pages.admin.resource', [
            'title' => 'Registrations',
            'type' => 'registrations',
            'databaseRows' => Registration::with(['user', 'webinar'])->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('webinar_id', $admin->assignedWebinars()->pluck('webinars.id')))->latest('registered_at')->paginate(10),
        ]);
    }

    public function users(): View
    {
        $admin = request()->user();

        return view('pages.admin.resource', [
            'title' => 'Users',
            'type' => 'users',
            'databaseRows' => User::with('roles')->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereHas('registrations', fn ($registrations) => $registrations->whereIn('webinar_id', $admin->assignedWebinars()->pluck('webinars.id'))))->latest()->paginate(10),
        ]);
    }

    public function show(Registration $registration): View
    {
        $registration->load(['user', 'webinar']);
        $events = DB::table('webinar_attendance_events')->where(['webinar_id' => $registration->webinar_id, 'user_id' => $registration->user_id])->orderBy('occurred_at')->get();
        $pollAnswers = DB::table('poll_responses')->join('polls', 'polls.id', '=', 'poll_responses.poll_id')->where('polls.webinar_id', $registration->webinar_id)->where('poll_responses.user_id', $registration->user_id)->count();
        $certificate = DB::table('certificates')->where(['webinar_id' => $registration->webinar_id, 'user_id' => $registration->user_id])->first();

        return view('pages.admin.registration-journey', compact('registration', 'events', 'pollAnswers', 'certificate'));
    }
}
