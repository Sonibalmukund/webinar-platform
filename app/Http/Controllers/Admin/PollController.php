<?php

namespace App\Http\Controllers\Admin;

use App\Events\WebinarRoomUpdated;
use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollResponse;
use App\Models\Webinar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PollController extends Controller
{
    public function index(Request $request): View
    {
        $webinarId = $request->integer('webinar_id');
        $search = trim((string) $request->input('search'));
        $polls = Poll::with(['webinar', 'options'])->withCount(['options', 'options as votes_count' => fn ($query) => $query->join('poll_responses', 'poll_responses.poll_option_id', '=', 'poll_options.id')])
            ->when($request->user()->hasRole('sub-admin'), fn ($query) => $query->whereIn('webinar_id', $request->user()->assignedWebinars()->pluck('webinars.id')))
            ->when($webinarId, fn ($query) => $query->where('webinar_id', $webinarId))
            ->when($search !== '', fn ($query) => $query->where('question', 'like', "%{$search}%"))
            ->latest()->paginate(15)->withQueryString();

        return view('pages.admin.polls.index', [
            'polls' => $polls,
            'webinars' => $this->webinars($request),
            'selectedWebinarId' => $webinarId,
            'search' => $search,
        ]);
    }

    public function create(Request $request): View
    {
        return view('pages.admin.polls.form', [
            'poll' => new Poll(['webinar_id' => $request->integer('webinar_id')]),
            'webinars' => $this->webinars($request),
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
            'webinars' => $this->webinars(request()),
        ]);
    }

    public function show(Poll $poll): View
    {
        $poll->load(['webinar', 'options' => fn ($query) => $query->withCount('responses')]);
        $totalVotes = $poll->options->sum('responses_count');
        $totalUsers = PollResponse::where('poll_id', $poll->id)->distinct('user_id')->count('user_id');
        $correctVotes = $poll->options->where('is_correct', true)->sum('responses_count');

        return view('pages.admin.polls.show', compact('poll', 'totalVotes', 'totalUsers', 'correctVotes'));
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
        if ($data['status'] === 'ended') {
            $values['ended_at'] = now();
        }
        $poll->update($values);
        $this->broadcastPoll($poll);

        return back()->with('status', $poll->type.($poll->status === 'active' ? ' started.' : ($poll->status === 'ended' ? ' stopped.' : ' status updated.')));
    }

    public function destroy(Poll $poll): RedirectResponse
    {
        $webinarId = $poll->webinar_id;
        $poll->delete();
        try {
            broadcast(new WebinarRoomUpdated($webinarId, 'poll'));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'Poll deleted.');
    }

    public function duplicate(Request $request, Poll $poll): RedirectResponse
    {
        $copy = $poll->replicate(['started_at', 'ended_at']);
        $copy->question .= ' (Copy)';
        $copy->status = 'draft';
        $copy->created_by = $request->user()->id;
        $copy->save();
        foreach ($poll->options as $option) {
            $copy->options()->create($option->only(['label', 'is_correct', 'display_order']));
        }

        return redirect()->route('admin.polls.index', ['webinar_id' => $copy->webinar_id])->with('status', 'Poll duplicated as a draft.');
    }

    private function save(Request $request, Poll $poll): Poll
    {
        if (! $request->has('answers') && $request->filled('options')) {
            $request->merge(['answers' => preg_split('/\r\n|\r|\n/', $request->input('options'))]);
        }
        $data = $request->validate([
            'webinar_id' => ['required', 'exists:webinars,id'],
            'question' => ['required', 'string', 'max:1000'],
            'answers' => ['required', 'array', 'min:2'],
            'answers.*' => ['nullable', 'string', 'max:255'],
            'allow_multiple' => ['nullable', 'boolean'],
            'correct_index' => ['nullable', 'integer', 'min:0'],
            'started_at' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'status' => ['nullable', 'in:draft,active,ended,hidden'],
        ]);
        $options = collect($data['answers'])->map(fn ($value) => trim($value ?? ''))->filter()->unique()->values();
        if ($options->count() < 2) {
            throw ValidationException::withMessages(['options' => 'Please enter at least two different answers.']);
        }

        $correctIndex = $request->filled('correct_index') ? $request->integer('correct_index') : null;
        if ($correctIndex !== null && ! $options->has($correctIndex)) {
            throw ValidationException::withMessages(['correct_index' => 'Select a valid correct answer.']);
        }
        if ($poll->exists && $poll->options()->whereHas('responses')->exists()) {
            $existing = $poll->options->pluck('label')->values();
            $existingCorrect = $poll->options->search(fn ($option) => $option->is_correct);
            if ($existing->all() !== $options->all() || (($existingCorrect === false ? null : $existingCorrect) !== $correctIndex)) {
                throw ValidationException::withMessages(['answers' => 'Options and the correct answer cannot be changed after voting has started.']);
            }
        }
        $poll->fill([
            'webinar_id' => $data['webinar_id'],
            'created_by' => $poll->exists ? $poll->created_by : $request->user()->id,
            'question' => $data['question'],
            'allow_multiple' => $request->boolean('allow_multiple'),
            'status' => $data['status'] ?? ($poll->exists ? $poll->status : 'draft'),
            'started_at' => $data['started_at'] ?? null,
            'ended_at' => $data['ended_at'] ?? null,
        ])->save();
        if (! $poll->options()->whereHas('responses')->exists()) {
            $poll->options()->delete();
            $options->each(fn ($label, $order) => $poll->options()->create(['label' => $label, 'is_correct' => $correctIndex === $order, 'display_order' => $order]));
        }
        $this->broadcastPoll($poll);

        return $poll;
    }

    private function webinars(Request $request)
    {
        return Webinar::query()->when($request->user()->hasRole('sub-admin'), fn ($query) => $query->whereIn('id', $request->user()->assignedWebinars()->pluck('webinars.id')))->orderBy('title')->get(['id', 'title']);
    }

    private function broadcastPoll(Poll $poll): void
    {
        try {
            broadcast(new WebinarRoomUpdated($poll->webinar_id, 'poll', ['poll_id' => $poll->id, 'status' => $poll->status, 'type' => $poll->type]));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
