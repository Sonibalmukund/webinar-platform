<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use App\Support\DynamicFieldsHelper;
use App\Support\XlsxExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function comments(): View
    {
        return $this->listing('comments');
    }

    public function feedback(): View
    {
        return $this->listing('feedback');
    }

    private function listing(string $type): View
    {
        $webinars = $this->webinars($type.'_enabled')->get();
        $webinarId = request()->integer('webinar_id');
        $search = trim((string) request('search', ''));
        $items = $this->listingQuery($type, $webinars->pluck('id'), $webinarId, $search)->paginate(20)->withQueryString();

        $dynamicColumns = DynamicFieldsHelper::attach($items, $webinarId ? [$webinarId] : null);

        return view('pages.admin.engagement.'.$type.'-list', compact('items', 'webinars', 'webinarId', 'search', 'dynamicColumns'));
    }

    public function exportComments(Request $request)
    {
        return $this->exportListing($request, 'comments');
    }

    public function exportFeedback(Request $request)
    {
        return $this->exportListing($request, 'feedback');
    }

    private function exportListing(Request $request, string $type)
    {
        $webinarId = $request->integer('webinar_id');
        $search = trim((string) $request->input('search'));
        $webinars = $this->webinars($type.'_enabled')->get();
        $items = $this->listingQuery($type, $webinars->pluck('id'), $webinarId, $search)->get();
        $dynamicColumns = DynamicFieldsHelper::attach($items, $webinarId ? [$webinarId] : null);
        $contentHeader = $type === 'comments' ? ['Comment'] : ['Rating', 'Feedback', 'Status'];
        $rows = [array_merge(['User', 'Email', 'Mobile', 'Webinar'], $dynamicColumns, $contentHeader, ['Received'])];

        foreach ($items as $item) {
            $dynamicValues = collect($dynamicColumns)->map(fn ($column) => $item->dynamic_fields[$column] ?? '')->all();
            $content = $type === 'comments'
                ? [$item->comment]
                : [(int) ($item->rating ?? 0), $item->message, ucfirst($item->status ?? 'new')];
            $rows[] = array_merge([
                $item->user_name ?: 'Guest',
                $item->user_email,
                $item->user_mobile,
                $item->webinar_title,
            ], $dynamicValues, $content, [Carbon::parse($item->created_at)]);
        }

        $receivedColumn = 5 + count($dynamicColumns) + ($type === 'feedback' ? 2 : 0);
        $numberColumns = $type === 'feedback' ? [4 + count($dynamicColumns)] : [];
        $sheetName = $type === 'comments' ? 'Comments' : 'Feedback';
        $path = XlsxExport::create($rows, [$receivedColumn], $numberColumns, $sheetName);

        return response()->download($path, $type.'-'.now()->format('Y-m-d').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function listingQuery(string $type, $webinarIds, int $webinarId, string $search)
    {
        $column = $type === 'comments' ? 'comment' : 'message';

        return DB::table($type)
            ->join('webinars', 'webinars.id', '=', $type.'.webinar_id')
            ->leftJoin('users', 'users.id', '=', $type.'.user_id')
            ->whereIn($type.'.webinar_id', $webinarIds)
            ->when($webinarId, fn ($query) => $query->where($type.'.webinar_id', $webinarId))
            ->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested->where($type.'.'.$column, 'like', '%'.$search.'%')->orWhere('users.name', 'like', '%'.$search.'%')->orWhere('users.email', 'like', '%'.$search.'%')->orWhere('users.mobile', 'like', '%'.$search.'%')))
            ->select($type.'.*', 'webinars.title as webinar_title', 'users.name as user_name', 'users.email as user_email', 'users.mobile as user_mobile')
            ->latest($type.'.created_at');
    }

    private function webinars(string $flag)
    {
        $user = request()->user();

        return Webinar::where($flag, true)->when($user->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $user->assignedWebinars()->pluck('webinars.id')))->latest('starts_at');
    }

    private function indexView(string $type, string $flag): View
    {
        $webinars = $this->webinars($flag)->get();
        $extra = $type === 'feedback' ? ', AVG(rating) average_rating' : '';
        $stats = DB::table($type)->whereIn('webinar_id', $webinars->pluck('id'))->selectRaw('webinar_id, COUNT(*) total, COUNT(DISTINCT user_id) people, MAX(created_at) latest_at'.$extra)->groupBy('webinar_id')->get()->keyBy('webinar_id');

        return view('pages.admin.engagement.index', compact('webinars', 'stats', 'type'));
    }

    public function commentsShow(Webinar $webinar): View
    {
        return $this->showView($webinar, 'comments');
    }

    public function feedbackShow(Webinar $webinar): View
    {
        abort_unless($webinar->feedback_enabled, 404);

        return $this->showView($webinar, 'feedback');
    }

    private function showView(Webinar $webinar, string $type): View
    {
        $items = DB::table($type)->leftJoin('users', 'users.id', '=', $type.'.user_id')->where($type.'.webinar_id', $webinar->id)->select($type.'.*', 'users.name as user_name', 'users.email as user_email', 'users.mobile as user_mobile')->latest($type.'.created_at')->get();

        return view('pages.admin.engagement.show', compact('webinar', 'items', 'type'));
    }

    public function removeComment(Webinar $webinar, int $item): RedirectResponse
    {
        DB::table('comments')->where('webinar_id', $webinar->id)->where('id', $item)->delete();

        return back()->with('success', 'Comment removed.');
    }

    public function updateFeedback(Webinar $webinar, int $item): RedirectResponse
    {
        DB::table('feedback')->where('webinar_id', $webinar->id)->where('id', $item)->update(['status' => request('status', 'reviewed'), 'updated_at' => now()]);

        return back()->with('success', 'Feedback status updated.');
    }
}
