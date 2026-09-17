<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Support\WebinarExperience;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

use App\Support\DynamicFieldsHelper;

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
            ->select('webinar_attendees.*', 'users.name as user_name', 'users.email', 'users.mobile', 'webinars.title as webinar_title', 'webinars.timezone as webinar_timezone')
            ->orderByDesc('joined_at');
    }

    private function assignedIds(Request $request)
    {
        return $request->user()->hasRole('sub-admin') ? $request->user()->accessibleWebinarIds() : null;
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

        $dynamicColumns = DynamicFieldsHelper::attach($rows, $webinarId ? [$webinarId] : null);

        $webinars = Webinar::when($assignedIds, fn ($query) => $query->whereIn('id', $assignedIds))->orderBy('title')->get();

        return view('pages.admin.attendance', compact('rows', 'webinars', 'webinarId', 'search', 'dynamicColumns'));
    }

    public function export(Request $request)
    {
        $assignedIds = $this->assignedIds($request);
        $search = trim((string) $request->input('search'));
        $rows = $this->query($request->integer('webinar_id'), $search)->when($assignedIds, fn ($query) => $query->whereIn('webinar_attendees.webinar_id', $assignedIds))->get();

        $dynamicColumns = DynamicFieldsHelper::attach($rows, $request->integer('webinar_id') ? [$request->integer('webinar_id')] : null);

        return response()->streamDownload(function () use ($rows, $dynamicColumns) {
            $out = fopen('php://output', 'w');
            $header = array_merge(['User', 'Email', 'Mobile', 'Webinar'], $dynamicColumns, ['Joined', 'Left', 'Watch seconds', 'Last seen']);
            fputcsv($out, $header);
            foreach ($rows as $row) {
                $timezone = $row->webinar_timezone ?: config('app.timezone');
                $dynamicValues = [];
                foreach ($dynamicColumns as $col) {
                    $dynamicValues[] = $row->dynamic_fields[$col] ?? '';
                }
                fputcsv($out, array_merge([
                    $row->user_name, $row->email, $row->mobile, $row->webinar_title
                ], $dynamicValues, [
                    ($row->joined_at ?: $row->created_at) ? Carbon::parse($row->joined_at ?: $row->created_at)->timezone($timezone)->format('Y-m-d H:i:s T') : null,
                    $row->left_at ? Carbon::parse($row->left_at)->timezone($timezone)->format('Y-m-d H:i:s T') : null,
                    $row->watch_seconds,
                    $row->last_seen_at ? Carbon::parse($row->last_seen_at)->timezone($timezone)->format('Y-m-d H:i:s T') : null,
                ]));
            }
            fclose($out);
        }, 'attendance-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }
}
