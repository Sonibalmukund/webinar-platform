<?php

namespace App\Http\Controllers\Admin;

use App\Events\WebinarQuestionUpdated;
use App\Http\Controllers\Controller;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function index(Request $request): View
    {
        $ids = Webinar::where('qa_enabled', true)->when($request->user()->hasRole('sub-admin'), fn ($q) => $q->whereIn('id', $request->user()->assignedWebinars()->pluck('webinars.id')))->pluck('id');
        $questions = DB::table('questions')->join('webinars', 'webinars.id', '=', 'questions.webinar_id')->leftJoin('users', 'users.id', '=', 'questions.user_id')->whereIn('questions.webinar_id', $ids)->select('questions.*', 'webinars.title as webinar_title', 'users.name as user_name', 'users.email as user_email')->selectSub(fn ($query) => $query->from('question_answers')->select('answer')->whereColumn('question_answers.question_id', 'questions.id')->where('is_official', true)->latest('question_answers.created_at')->limit(1), 'official_answer')->latest('questions.created_at')->paginate(20);

        return view('pages.admin.questions.index', compact('questions'));
    }

    public function update(Request $request, int $question): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:open,answered,closed']]);
        $query = DB::table('questions')->where('id', $question);
        if ($request->user()->hasRole('sub-admin')) {
            $query->whereIn('webinar_id', $request->user()->assignedWebinars()->pluck('webinars.id'));
        }
        $row = $query->first();
        abort_unless($row, 404);
        $query->update(['status' => $data['status'], 'answered_at' => $data['status'] === 'answered' ? now() : null, 'updated_at' => now()]);

        try {
            broadcast(new WebinarQuestionUpdated($row->webinar_id, 'status_updated', [
                'id' => $question,
                'status' => $data['status'],
            ]));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Question status updated.');
    }

    public function answer(Request $request, int $question): RedirectResponse
    {
        $data = $request->validate(['answer' => ['required', 'string', 'max:5000']]);
        $query = DB::table('questions')->where('id', $question);
        if ($request->user()->hasRole('sub-admin')) {
            $query->whereIn('webinar_id', $request->user()->assignedWebinars()->pluck('webinars.id'));
        }
        $row = $query->first();
        abort_unless($row, 404);
        DB::transaction(function () use ($request, $question, $data) {
            DB::table('question_answers')->where('question_id', $question)->where('is_official', true)->delete();
            DB::table('question_answers')->insert(['question_id' => $question, 'user_id' => $request->user()->id, 'answer' => $data['answer'], 'is_official' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('questions')->where('id', $question)->update(['status' => 'answered', 'answered_at' => now(), 'updated_at' => now()]);
        });

        try {
            broadcast(new WebinarQuestionUpdated($row->webinar_id, 'answered', [
                'id' => $question,
                'status' => 'answered',
                'official_answer' => $data['answer'],
            ]));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('success', 'Official answer saved successfully.');
    }
}

