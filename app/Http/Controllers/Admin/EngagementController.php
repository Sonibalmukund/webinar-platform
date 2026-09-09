<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function comments(): View
    {
        return $this->indexView('comments', 'comments_enabled');
    }

    public function feedback(): View
    {
        $ids = $this->webinars('feedback_enabled')->pluck('id');
        $items = DB::table('feedback')->join('webinars', 'webinars.id', '=', 'feedback.webinar_id')->leftJoin('users', 'users.id', '=', 'feedback.user_id')->whereIn('feedback.webinar_id', $ids)->select('feedback.*', 'webinars.title as webinar_title', 'users.name as user_name', 'users.email as user_email')->latest('feedback.created_at')->paginate(20);

        return view('pages.admin.engagement.feedback-list', compact('items'));
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
        $items = DB::table($type)->leftJoin('users', 'users.id', '=', $type.'.user_id')->where($type.'.webinar_id', $webinar->id)->select($type.'.*', 'users.name as user_name', 'users.email as user_email')->latest($type.'.created_at')->get();

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
