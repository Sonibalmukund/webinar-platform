<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use App\Models\Webinar;
use App\Support\DynamicFieldsHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $admin = $request->user();
        $accessibleWebinarIds = $admin->accessibleWebinarIds();
        $webinars = Webinar::when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $accessibleWebinarIds))
            ->orderBy('title')
            ->get();
        $search = trim((string) $request->input('search'));
        $webinarId = $request->integer('webinar_id');
        $status = $request->input('status');

        $query = Registration::with(['user', 'webinar'])
            ->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('webinar_id', $accessibleWebinarIds));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%");
                    })
                    ->orWhereHas('webinar', function ($wq) use ($search) {
                        $wq->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($webinarId) {
            $query->where('webinar_id', $webinarId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $databaseRows = $query->latest('registered_at')->latest('id')->paginate(15)->withQueryString();
        $dynamicColumns = DynamicFieldsHelper::attach($databaseRows, $webinarId ? [$webinarId] : null);

        return view('pages.admin.resource', [
            'title' => 'Registrations',
            'type' => 'registrations',
            'databaseRows' => $databaseRows,
            'webinars' => $webinars,
            'dynamicColumns' => $dynamicColumns,
        ]);
    }

    public function users(Request $request): View
    {
        $admin = $request->user();
        $accessibleWebinarIds = $admin->accessibleWebinarIds();
        $webinars = Webinar::when($admin->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $accessibleWebinarIds))
            ->orderBy('title')
            ->get();
        $search = trim((string) $request->input('search'));
        $webinarId = $request->integer('webinar_id');
        $status = $request->input('status');

        $query = User::with([
                'roles',
                'registrations' => fn ($rq) => $admin->hasRole('sub-admin') ? $rq->whereIn('webinar_id', $accessibleWebinarIds) : $rq,
                'registrations.webinar'
            ])
            ->where(function ($q) {
                $q->whereHas('roles', fn ($rq) => $rq->where('slug', 'learner'))
                  ->orWhereHas('registrations')
                  ->orWhereDoesntHave('roles', fn ($rq) => $rq->whereIn('slug', ['super-admin', 'sub-admin']));
            })
            ->when($admin->hasRole('sub-admin'), fn ($query) => $query->whereHas('registrations', fn ($registrations) => $registrations->whereIn('webinar_id', $accessibleWebinarIds)));

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

        $databaseRows = $query->latest('updated_at')->latest('id')->paginate(15)->withQueryString();
        $dynamicColumns = DynamicFieldsHelper::attach($databaseRows, $webinarId ? [$webinarId] : null);

        return view('pages.admin.resource', [
            'title' => 'Users',
            'type' => 'users',
            'databaseRows' => $databaseRows,
            'webinars' => $webinars,
            'dynamicColumns' => $dynamicColumns,
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
        $registration->delete();

        return back()->with('status', 'Registration deleted successfully.');
    }
}
