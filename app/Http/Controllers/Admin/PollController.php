<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use App\Events\WebinarRoomUpdated;

class PollController extends Controller
{
    public function index(Request $request): View
    {
        $webinarId = $request->integer('webinar_id');
        $polls = Poll::with(['webinar', 'options'])
            ->when($webinarId, fn ($query) => $query->where('webinar_id', $webinarId))
            ->latest()->paginate(15)->withQueryString();

        return view('pages.admin.polls.index', [
            'polls' => $polls,
            'webinars' => Webinar::orderBy('title')->get(['id', 'title']),
            'selectedWebinarId' => $webinarId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('pages.admin.polls.form', [
            'poll' => new Poll(['webinar_id' => $request->integer('webinar_id')]),
            'webinars' => Webinar::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $poll = $this->save($request, new Poll);
        return redirect()->route('admin.polls.index', ['webinar_id' => $poll->webinar_id])->with('status', 'Poll added successfully.');
    }

    public function edit(Poll $poll): View
    {
        return view('pages.admin.polls.form', [
            'poll' => $poll->load('options'),
            'webinars' => Webinar::orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function show(Poll $poll): View
    {
        $poll->load(['webinar', 'options' => fn ($query) => $query->withCount('responses')]);
        $totalVotes = $poll->options->sum('responses_count');
        $totalUsers = \App\Models\PollResponse::where('poll_id', $poll->id)->distinct('user_id')->count('user_id');
        return view('pages.admin.polls.show', compact('poll', 'totalVotes', 'totalUsers'));
    }

    public function update(Request $request, Poll $poll): RedirectResponse
    {
        $poll = $this->save($request, $poll);
        return redirect()->route('admin.polls.index', ['webinar_id' => $poll->webinar_id])->with('status', 'Poll updated successfully.');
    }

    public function status(Request $request, Poll $poll): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', 'in:draft,active,ended,hidden']]);
        $values = ['status' => $data['status']];
        if ($data['status'] === 'active') {
            $values['started_at'] = now();
            $values['ended_at'] = null;
            $poll->webinar()->update(['polls_enabled' => true]);
        }
        if ($data['status'] === 'ended') $values['ended_at'] = now();
        $poll->update($values);
        $this->broadcastPoll($poll);

        return back()->with('status', 'Poll status updated.');
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        $webinarId=$poll->webinar_id;
        $poll->delete();
        try { broadcast(new WebinarRoomUpdated($webinarId,'poll')); } catch(\Throwable $e) { report($e); }
        return back()->with('status', 'Poll deleted.');
    }

    private function save(Request $request, Poll $poll): Poll
    {
        if (!$request->has('answers') && $request->filled('options')) {
            $request->merge(['answers' => preg_split('/\r\n|\r|\n/', $request->input('options'))]);
        }
        $data = $request->validate([
            'webinar_id' => ['required', 'exists:webinars,id'],
            'question' => ['required', 'string', 'max:1000'],
            'answers' => ['required', 'array', 'min:2'],
            'answers.*' => ['nullable', 'string', 'max:255'],
            'allow_multiple' => ['nullable', 'boolean'],
        ]);
        $options = collect($data['answers'])->map(fn ($value) => trim($value ?? ''))->filter()->unique()->values();
        if ($options->count() < 2) throw ValidationException::withMessages(['options' => 'Please enter at least two different answers.']);

        $poll->fill([
            'webinar_id' => $data['webinar_id'],
            'created_by' => $poll->exists ? $poll->created_by : $request->user()->id,
            'question' => $data['question'],
            'allow_multiple' => $request->boolean('allow_multiple'),
            'status' => $poll->exists ? $poll->status : 'draft',
        ])->save();
        $poll->options()->delete();
        $options->each(fn ($label, $order) => $poll->options()->create(['label' => $label, 'display_order' => $order]));
        $this->broadcastPoll($poll);
        return $poll;
    }

    private function broadcastPoll(Poll $poll): void
    {
        try { broadcast(new WebinarRoomUpdated($poll->webinar_id,'poll',['poll_id'=>$poll->id,'status'=>$poll->status])); } catch(\Throwable $e) { report($e); }
    }
}
