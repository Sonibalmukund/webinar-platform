<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Support\WebinarExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    private function query(int $webinarId = 0, string $search = '')
    {
        return DB::table('webinar_attendees')
            ->join('users', 'users.id', '=', 'webinar_attendees.user_id')
            ->join('webinars', 'webinars.id', '=', 'webinar_attendees.webinar_id')
            ->when($webinarId, fn ($query) => $query->where('webinar_attendees.webinar_id', $webinarId))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.mobile', 'like', "%{$search}%");
            }))
            ->select('webinar_attendees.*', 'users.name as user_name', 'users.email', 'users.mobile', 'webinars.title as webinar_title')
            ->orderByDesc('joined_at');
    }

    private function assignedIds(Request $request)
    {
        return $request->user()->hasRole('sub-admin') ? $request->user()->assignedWebinars()->pluck('webinars.id') : null;
    }

    public function index(Request $request): View
    {
        $assignedIds = $this->assignedIds($request);
        $webinarId = $request->integer('webinar_id');
        $search = trim((string) $request->input('search'));
        $rows = $this->query($webinarId, $search)->when($assignedIds, fn ($query) => $query->whereIn('webinar_attendees.webinar_id', $assignedIds))->paginate(20)->withQueryString();
        $webinarMap = Webinar::whereIn('id', $rows->pluck('webinar_id'))->get()->keyBy('id');
        $rows->getCollection()->transform(function ($row) use ($webinarMap) {
            $row->metrics = WebinarExperience::metrics($webinarMap[$row->webinar_id], $row->user_id);

            return $row;
        });
        $webinars = Webinar::when($assignedIds, fn ($query) => $query->whereIn('id', $assignedIds))->orderBy('title')->get();

        return view('pages.admin.attendance', compact('rows', 'webinars', 'webinarId', 'search'));
    }

    public function export(Request $request)
    {
        $assignedIds = $this->assignedIds($request);
        $search = trim((string) $request->input('search'));
        $rows = $this->query($request->integer('webinar_id'), $search)->when($assignedIds, fn ($query) => $query->whereIn('webinar_attendees.webinar_id', $assignedIds))->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['User', 'Email', 'Mobile', 'Webinar', 'Joined', 'Left', 'Watch seconds', 'Last seen']);
            foreach ($rows as $row) {
                fputcsv($out, [$row->user_name, $row->email, $row->mobile, $row->webinar_title, $row->joined_at, $row->left_at, $row->watch_seconds, $row->last_seen_at]);
            }fclose($out);
        }, 'attendance-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
