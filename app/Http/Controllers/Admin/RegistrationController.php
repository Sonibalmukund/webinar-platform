<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('admin.users', $request->query());
    }

    public function users(Request $request): View
    {
        $admin = $request->user();
        $webinars = Webinar::when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $admin->assignedWebinars()->pluck('webinars.id')))
            ->orderBy('title')
            ->get();
        $search = trim((string) $request->input('search'));
        $webinarId = $request->integer('webinar_id');
        $status = $request->input('status');

        $query = User::with(['roles', 'registrations.webinar'])
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'sub-admin']))
            ->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereHas('registrations', fn ($registrations) => $registrations->whereIn('webinar_id', $admin->assignedWebinars()->pluck('webinars.id'))));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($webinarId) {
            $query->whereHas('registrations', fn ($rq) => $rq->where('webinar_id', $webinarId));
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $databaseRows = $query->latest()->paginate(15)->withQueryString();

        return view('pages.admin.resource', [
            'title' => 'Users',
            'type' => 'users',
            'databaseRows' => $databaseRows,
            'webinars' => $webinars,
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

    public function destroy(Registration $registration): RedirectResponse
    {
        $webinarId = $registration->webinar_id;
        $registration->delete();

        return redirect()->route('admin.users', array_filter(['webinar_id' => $webinarId]))->with('status', 'Registration deleted successfully.');
    }
}
