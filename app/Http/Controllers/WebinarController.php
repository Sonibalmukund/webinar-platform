<?php

namespace App\Http\Controllers;

use App\Events\WebinarChatMessageSent;
use App\Events\WebinarPollUpdated;
use App\Events\WebinarQuestionUpdated;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\City;
use App\Models\Country;
use App\Models\Poll;
use App\Models\SignupField;
use App\Models\State;
use App\Models\Webinar;
use App\Support\AuditTrail;
use App\Support\WebinarExperience;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebinarController extends Controller
{
    public function index(): View
    {
        return view('pages.user.webinars', ['webinars' => Webinar::with('creator')->whereNot('status', 'draft')->whereNotNull('published_at')->orderBy('starts_at')->paginate(12)]);
    }

    public function show(Webinar $webinar): View
    {
        $canPreview = auth()->check() && (auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('sub-admin'));
        abort_if($webinar->status === 'draft' && ! $canPreview, 404);
        if ($webinar->status !== 'draft') {
            request()->session()->put('frontend_event_slug', $webinar->slug);
        }
        $webinar = $webinar->load(['registrationForm.fields.options', 'speakers', 'creator'])->loadCount('registrations');
        $loginField = $webinar->registrationForm?->fields->first(fn ($field) => $field->is_enabled && $field->login_enabled);
        $banners = Banner::where('webinar_id', $webinar->id)->where('is_active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))->orderBy('display_order')->get();
        $brands = Brand::where('webinar_id', $webinar->id)->where('is_active', true)->get();
        $agenda = DB::table('webinar_agenda_items')->where('webinar_id', $webinar->id)->orderBy('display_order')->get();
        $resources = DB::table('webinar_resources')->where('webinar_id', $webinar->id)->where('is_public', true)->orderBy('display_order')->get();
        $agenda->each(function ($item) use ($webinar) {
            $item->starts_at_iso = $item->starts_at && $webinar->starts_at ? $webinar->starts_at->copy()->timezone($webinar->timezone)->setTimeFromTimeString($item->starts_at)->utc()->toIso8601String() : null;
        });
        $authSettings = DB::table('settings')->where('group', 'registration')->pluck('value', 'key');
        $defaultCountryId = (int) ($authSettings['registration_default_country_id'] ?? 0);
        $defaultStateId = (int) ($authSettings['registration_default_state_id'] ?? 0);

        return view('pages.user.webinar-show', [
            'webinar' => $webinar,
            'banners' => $banners,
            'brands' => $brands,
            'agenda' => $agenda,
            'resources' => $resources,
            'isRegistered' => $webinar->registrations()->where('user_id', auth()->id())->where('status', 'approved')->exists(),
            'loginField' => $loginField,
            'authSettings' => $authSettings,
            'countries' => Country::where('is_active', true)->orderBy('name')->get(),
            'states' => State::where('country_id', $defaultCountryId)->where('is_active', true)->orderBy('name')->get(),
            'cities' => City::where('state_id', $defaultStateId)->where('is_active', true)->orderBy('name')->get(),
            'signupFields' => SignupField::with(['options' => fn ($q) => $q->where('is_enabled', true)])->where('is_enabled', true)->orderBy('display_order')->get(),
        ]);
    }

    public function dashboard(Request $request, Webinar $webinar): View
    {
        abort_unless($webinar->registrations()->where('user_id', $request->user()->id)->exists(), 403, 'Register for this webinar before opening its dashboard.');
        $request->session()->put('frontend_event_slug', $webinar->slug);
        $webinar->load(['speakers'])->loadCount('registrations');
        $banners = Banner::where('webinar_id', $webinar->id)->where('is_active', true)->orderBy('display_order')->get();
        $brands = Brand::where('webinar_id', $webinar->id)->where('is_active', true)->get();
        $agenda = DB::table('webinar_agenda_items')->where('webinar_id', $webinar->id)->orderBy('display_order')->get();
        $resources = DB::table('webinar_resources')->where('webinar_id', $webinar->id)->where('is_public', true)->orderBy('display_order')->get();
        $agenda->each(function ($item) use ($webinar) {
            $item->starts_at_iso = $item->starts_at && $webinar->starts_at ? $webinar->starts_at->copy()->timezone($webinar->timezone)->setTimeFromTimeString($item->starts_at)->utc()->toIso8601String() : null;
        });
        $activePoll = $webinar->polls()->with(['options' => fn ($query) => $query->withCount('responses')])->where('status', 'active')->latest()->first();
        $chatMessages = DB::table('chat_messages')
            ->leftJoin('users', 'users.id', '=', 'chat_messages.user_id')
            ->leftJoin('chat_messages as parent_msg', 'parent_msg.id', '=', 'chat_messages.reply_to_id')
            ->leftJoin('users as parent_user', 'parent_user.id', '=', 'parent_msg.user_id')
            ->where('chat_messages.webinar_id', $webinar->id)->whereNull('chat_messages.deleted_at')
            ->select([
                'chat_messages.*',
                'users.name as user_name',
                'parent_user.name as reply_to_user_name',
                'parent_msg.message as reply_to_message',
            ])
            ->selectSub(fn ($q) => $q->from('chat_message_votes')->selectRaw('count(*)')->whereColumn('chat_message_votes.chat_message_id', 'chat_messages.id'), 'votes_count')
            ->selectSub(fn ($q) => $q->from('chat_message_votes')->selectRaw('count(*)')->whereColumn('chat_message_votes.chat_message_id', 'chat_messages.id')->where('chat_message_votes.user_id', $request->user()->id), 'has_voted')
            ->orderByDesc('votes_count')
            ->latest('chat_messages.sent_at')->limit(50)->get();
        $feedback = $webinar->feedback_enabled ? DB::table('feedback')->where(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id])->latest()->first() : null;
        $now = now();
        $existingAttendee = DB::table('webinar_attendees')->where(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id])->first();
        $shouldResetHand = ! $existingAttendee || $existingAttendee->left_at !== null || ! $existingAttendee->last_seen_at || Carbon::parse($existingAttendee->last_seen_at)->lt($now->copy()->subMinutes(10));

        DB::table('webinar_attendees')->updateOrInsert(
            ['webinar_id' => $webinar->id, 'user_id' => $request->user()->id],
            [
                'last_seen_at' => $now,
                'left_at' => null,
                'raised_hand' => $shouldResetHand ? false : (bool) ($existingAttendee->raised_hand ?? false),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
        $liveViewers = DB::table('webinar_attendees')->where('webinar_id', $webinar->id)->whereNull('left_at')->where('last_seen_at', '>=', now()->subSeconds(75))->count();
        $participants = DB::table('webinar_attendees')->join('users', 'users.id', '=', 'webinar_attendees.user_id')->where('webinar_id', $webinar->id)->whereNull('left_at')->where('last_seen_at', '>=', now()->subSeconds(75))->select('users.id', 'users.name', 'raised_hand')->orderByDesc('raised_hand')->get();
        $raisedHand = (bool) DB::table('webinar_attendees')->where(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id])->value('raised_hand');

        if (! $participants->contains('id', $request->user()->id)) {
            $participants->push((object) [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'raised_hand' => $raisedHand,
            ]);
        }
        $self = $participants->firstWhere('id', $request->user()->id);
        if ($self) {
            $participants = $participants->reject(fn ($p) => (int) $p->id === (int) $request->user()->id)->prepend($self);
        }
        $liveViewers = max(1, $liveViewers, $participants->count());
        $pollResponse = $activePoll ? DB::table('poll_responses')->where('poll_id', $activePoll->id)->where('user_id', $request->user()->id)->pluck('poll_option_id') : collect();
        $metrics = WebinarExperience::metrics($webinar, $request->user()->id);
        $certificate = DB::table('certificates')->where(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id])->first();

        $questions = DB::table('questions')
            ->leftJoin('users', 'users.id', '=', 'questions.user_id')
            ->where('questions.webinar_id', $webinar->id)
            ->select([
                'questions.id', 'questions.webinar_id', 'questions.user_id',
                'questions.question', 'questions.status', 'questions.is_anonymous',
                'questions.is_pinned', 'questions.answered_at', 'questions.created_at',
                'users.name as user_name',
            ])
            ->selectSub(
                fn ($q) => $q->from('question_answers')->select('answer')->whereColumn('question_answers.question_id', 'questions.id')->where('is_official', true)->latest('question_answers.created_at')->limit(1),
                'official_answer'
            )
            ->selectSub(
                fn ($q) => $q->from('question_votes')->selectRaw('count(*)')->whereColumn('question_votes.question_id', 'questions.id'),
                'votes_count'
            )
            ->orderByDesc('questions.is_pinned')
            ->orderByDesc('votes_count')
            ->orderByDesc('questions.id')
            ->limit(100)
            ->get();

        $userVotes = DB::table('question_votes')
            ->whereIn('question_id', $questions->pluck('id'))
            ->where('user_id', $request->user()->id)
            ->pluck('question_id')
            ->toArray();

        return view('pages.user.webinar-dashboard', compact('webinar', 'banners', 'brands', 'agenda', 'resources', 'activePoll', 'chatMessages', 'feedback', 'pollResponse', 'metrics', 'certificate', 'liveViewers', 'raisedHand', 'participants', 'questions', 'userVotes'));
    }

    public function chatMessages(Request $request, Webinar $webinar): JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        if (! $webinar->chat_enabled) {
            return response()->json(['messages' => [], 'chat_enabled' => false]);
        }
        $userId = $request->user()->id;
        $messages = DB::table('chat_messages')
            ->leftJoin('users', 'users.id', '=', 'chat_messages.user_id')
            ->leftJoin('chat_messages as parent_msg', 'parent_msg.id', '=', 'chat_messages.reply_to_id')
            ->leftJoin('users as parent_user', 'parent_user.id', '=', 'parent_msg.user_id')
            ->where('chat_messages.webinar_id', $webinar->id)->whereNull('chat_messages.deleted_at')
            ->select([
                'chat_messages.id', 'chat_messages.user_id', 'users.name as user_name',
                'chat_messages.message', 'chat_messages.sent_at',
                'chat_messages.reply_to_id',
                'parent_user.name as reply_to_user_name',
                'parent_msg.message as reply_to_message',
                'chat_messages.attachment_path', 'chat_messages.attachment_name', 'chat_messages.attachment_mime',
            ])
            ->selectSub(fn ($q) => $q->from('chat_message_votes')->selectRaw('count(*)')->whereColumn('chat_message_votes.chat_message_id', 'chat_messages.id'), 'votes_count')
            ->selectSub(fn ($q) => $q->from('chat_message_votes')->selectRaw('count(*)')->whereColumn('chat_message_votes.chat_message_id', 'chat_messages.id')->where('chat_message_votes.user_id', $userId), 'has_voted')
            ->orderByDesc('votes_count')
            ->latest('chat_messages.sent_at')->limit(50)
            ->get();

        return response()->json(['messages' => $messages->values(), 'chat_enabled' => true]);
    }

    public function voteChat(Request $request, Webinar $webinar, int $messageId): JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        $message = DB::table('chat_messages')->where('id', $messageId)->where('webinar_id', $webinar->id)->whereNull('deleted_at')->first();
        abort_unless($message, 404, 'Message not found.');

        $userId = $request->user()->id;
        $existing = DB::table('chat_message_votes')->where(['chat_message_id' => $messageId, 'user_id' => $userId])->first();

        if ($existing) {
            DB::table('chat_message_votes')->where(['chat_message_id' => $messageId, 'user_id' => $userId])->delete();
            $voted = false;
        } else {
            DB::table('chat_message_votes')->insert([
                'chat_message_id' => $messageId,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voted = true;
        }

        $votesCount = (int) DB::table('chat_message_votes')->where('chat_message_id', $messageId)->count();

        try {
            broadcast(new \App\Events\WebinarChatMessageVoted($webinar->id, $messageId, $votesCount, $userId));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message_id' => $messageId,
            'voted' => $voted,
            'votes_count' => $votesCount,
        ]);
    }

    public function sendChat(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($webinar->chat_enabled, 404);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'reply_to_id' => ['nullable', 'integer', 'exists:chat_messages,id'],
        ]);
        $sentAt = now();
        $replyToId = !empty($data['reply_to_id']) ? (int) $data['reply_to_id'] : null;
        $parentMessage = null;
        if ($replyToId) {
            $parentMessage = DB::table('chat_messages')
                ->leftJoin('users', 'users.id', '=', 'chat_messages.user_id')
                ->where('chat_messages.id', $replyToId)
                ->where('chat_messages.webinar_id', $webinar->id)
                ->whereNull('chat_messages.deleted_at')
                ->select(['chat_messages.message', 'users.name as user_name'])
                ->first();
            if (!$parentMessage) {
                $replyToId = null;
            }
        }

        $id = DB::table('chat_messages')->insertGetId([
            'webinar_id' => $webinar->id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'reply_to_id' => $replyToId,
            'sent_at' => $sentAt,
            'created_at' => $sentAt,
            'updated_at' => $sentAt,
        ]);
        $message = [
            'id' => $id,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
            'message' => $data['message'],
            'reply_to_id' => $replyToId,
            'reply_to_user_name' => $parentMessage?->user_name,
            'reply_to_message' => $parentMessage?->message,
            'votes_count' => 0,
            'has_voted' => false,
            'sent_at' => $sentAt->toIso8601String(),
        ];
        try {
            broadcast(new WebinarChatMessageSent($webinar->id, $message));
        } catch (\Throwable $exception) {
            report($exception);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return back()->with('dashboard_status', 'Message sent.');
    }

    public function storeComment(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        $data = $request->validate(['comment' => ['required', 'string', 'max:2000']]);
        DB::table('comments')->insert(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id, 'comment' => $data['comment'], 'status' => 'visible', 'created_at' => now(), 'updated_at' => now()]);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your private comment was sent to the host.'], 201);
        }

        return back()->with('dashboard_status', 'Your private comment was sent to the host.');
    }

    public function storeFeedback(Request $request, Webinar $webinar): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($webinar->feedback_enabled, 404);
        $data = $request->validate([
            'rating' => ['nullable', 'required_without:message', 'integer', 'between:1,5'],
            'message' => ['nullable', 'required_without:rating', 'string', 'max:3000'],
        ], [
            'rating.required_without' => 'Please select a rating or write a feedback message.',
            'message.required_without' => 'Please write a message or select a rating.',
        ]);
        $data['message'] = $data['message'] ?? '';
        DB::table('feedback')->updateOrInsert(['webinar_id' => $webinar->id, 'user_id' => $request->user()->id], $data + ['status' => 'new', 'created_at' => now(), 'updated_at' => now()]);
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Thank you. Your feedback was saved.']);
        }

        return back()->with('dashboard_status', 'Thank you for your feedback.');
    }

    public function vote(Request $request, Webinar $webinar, Poll $poll): RedirectResponse|JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($webinar->polls_enabled && $poll->webinar_id === $webinar->id && $poll->status === 'active', 404);
        $data = $request->validate(['option_id' => ['required', 'integer']]);
        abort_unless($poll->options()->whereKey($data['option_id'])->exists(), 422);
        if (DB::table('poll_responses')->where(['poll_id' => $poll->id, 'user_id' => $request->user()->id])->exists()) {
            if ($request->expectsJson()) {
                return $this->pollResults($request, $webinar, $poll);
            }
            return back()->with('dashboard_status', 'You have already answered this poll.')->with('dashboard_toast_tone', 'warning');
        }
        $selected = $poll->options()->findOrFail($data['option_id']);
        $isQuiz = $poll->options()->where('is_correct', true)->exists();
        $isCorrect = $isQuiz ? $selected->is_correct : null;
        DB::table('poll_responses')->insertOrIgnore(['poll_id' => $poll->id, 'poll_option_id' => $data['option_id'], 'user_id' => $request->user()->id, 'is_correct' => $isCorrect, 'voted_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
        $options = $poll->options()->withCount('responses')->get()->map(fn ($option) => ['id' => $option->id, 'count' => $option->responses_count])->all();
        try {
            event(new WebinarPollUpdated($webinar->id, $poll->id, $options));
        } catch (\Throwable $exception) {
            report($exception);
        }
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your answer was recorded.', 'is_quiz' => $isQuiz, 'is_correct' => $isCorrect, 'selected_option_id' => $selected->id, 'correct_option_id' => $isQuiz ? $poll->options()->where('is_correct', true)->value('id') : null, 'options' => $options]);
        }

        return back()->with('dashboard_status', $isQuiz ? ($isCorrect ? 'Correct Answer' : 'Answer submitted. The correct answer is highlighted.') : 'Your vote was recorded.');
    }

    public function pollResults(Request $request, Webinar $webinar, Poll $poll): JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($poll->webinar_id === $webinar->id && $webinar->polls_enabled, 404);
        $answer = DB::table('poll_responses')->where(['poll_id' => $poll->id, 'user_id' => $request->user()->id])->first();
        abort_unless($answer, 403, 'Answer this poll to see results.');
        $correct = $poll->options()->where('is_correct', true)->value('id');

        return response()->json([
            'message' => 'Your answer was recorded.',
            'poll_id' => $poll->id,
            'is_quiz' => $correct !== null,
            'is_correct' => $correct !== null ? (int) $answer->poll_option_id === (int) $correct : null,
            'selected_option_id' => $answer->poll_option_id,
            'correct_option_id' => $correct,
            'options' => $poll->options()->withCount('responses')->get()->map(fn ($option) => ['id' => $option->id, 'count' => $option->responses_count])->all(),
        ])->header('Cache-Control', 'no-store');
    }

    public function activePoll(Request $request, Webinar $webinar): View
    {
        $this->authorizeRegistration($request, $webinar);
        $activePoll = $webinar->polls_enabled
            ? $webinar->polls()->with(['options' => fn ($query) => $query->withCount('responses')])->where('status', 'active')->latest()->first()
            : null;
        $pollResponse = $activePoll
            ? DB::table('poll_responses')->where('poll_id', $activePoll->id)->where('user_id', $request->user()->id)->pluck('poll_option_id')
            : collect();

        return view('pages.user.partials.dashboard-poll', compact('webinar', 'activePoll', 'pollResponse'));
    }

    public function questions(Request $request, Webinar $webinar): JsonResponse
    {
        $this->authorizeRegistration($request, $webinar);
        $questions = DB::table('questions')
            ->leftJoin('users', 'users.id', '=', 'questions.user_id')
            ->where('questions.webinar_id', $webinar->id)
            ->select([
                'questions.id', 'questions.webinar_id', 'questions.user_id',
                'questions.question', 'questions.status', 'questions.is_anonymous',
                'questions.is_pinned', 'questions.answered_at', 'questions.created_at',
                'users.name as user_name',
            ])
            ->selectSub(
                fn ($q) => $q->from('question_answers')->select('answer')->whereColumn('question_answers.question_id', 'questions.id')->where('is_official', true)->latest('question_answers.created_at')->limit(1),
                'official_answer'
            )
            ->selectSub(
                fn ($q) => $q->from('question_votes')->selectRaw('count(*)')->whereColumn('question_votes.question_id', 'questions.id'),
                'votes_count'
            )
            ->orderByDesc('questions.is_pinned')
            ->orderByDesc('votes_count')
            ->orderByDesc('questions.id')
            ->limit(100)
            ->get();

        $userVotes = DB::table('question_votes')
            ->whereIn('question_id', $questions->pluck('id'))
            ->where('user_id', $request->user()->id)
            ->pluck('question_id')
            ->toArray();

        $data = $questions->map(fn ($q) => [
            'id' => $q->id,
            'question' => $q->question,
            'user_name' => $q->is_anonymous ? 'Anonymous' : ($q->user_name ?: 'Attendee'),
            'is_anonymous' => (bool) $q->is_anonymous,
            'is_own' => (int) $q->user_id === (int) $request->user()->id,
            'status' => $q->status,
            'official_answer' => $q->official_answer,
            'votes_count' => (int) $q->votes_count,
            'has_voted' => in_array($q->id, $userVotes),
            'created_at_human' => Carbon::parse($q->created_at)->diffForHumans(),
        ]);

        return response()->json(['questions' => $data, 'qa_enabled' => (bool) $webinar->qa_enabled]);
    }

    public function askQuestion(Request $request, Webinar $webinar): JsonResponse|RedirectResponse
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($webinar->qa_enabled, 403, 'Q&A is currently paused.');

        $data = $request->validate([
            'question' => ['required', 'string', 'min:3', 'max:500'],
            'is_anonymous' => ['nullable'],
        ]);

        $isAnonymous = $request->boolean('is_anonymous');
        $id = DB::table('questions')->insertGetId([
            'webinar_id' => $webinar->id,
            'user_id' => $request->user()->id,
            'question' => $data['question'],
            'status' => 'open',
            'is_anonymous' => $isAnonymous,
            'is_pinned' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'id' => $id,
            'question' => $data['question'],
            'user_name' => $isAnonymous ? 'Anonymous' : $request->user()->name,
            'user_id' => $request->user()->id,
            'is_anonymous' => $isAnonymous,
            'status' => 'open',
            'votes_count' => 0,
            'official_answer' => null,
            'created_at_human' => 'Just now',
        ];

        try {
            broadcast(new WebinarQuestionUpdated($webinar->id, 'created', $payload));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Question submitted.', 'question' => $payload], 201);
        }

        return back()->with('dashboard_status', 'Question submitted.');
    }

    public function voteQuestion(Request $request, Webinar $webinar, int $question): JsonResponse|RedirectResponse
    {
        $this->authorizeRegistration($request, $webinar);
        $q = DB::table('questions')->where(['id' => $question, 'webinar_id' => $webinar->id])->first();
        abort_unless($q, 404);

        $exists = DB::table('question_votes')->where(['question_id' => $question, 'user_id' => $request->user()->id])->exists();
        if ($exists) {
            DB::table('question_votes')->where(['question_id' => $question, 'user_id' => $request->user()->id])->delete();
            $voted = false;
        } else {
            DB::table('question_votes')->insert([
                'question_id' => $question,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $voted = true;
        }

        $votesCount = DB::table('question_votes')->where('question_id', $question)->count();

        try {
            broadcast(new WebinarQuestionUpdated($webinar->id, 'voted', [
                'id' => $question,
                'votes_count' => $votesCount,
            ]));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['voted' => $voted, 'votes_count' => $votesCount]);
        }

        return back();
    }

    public function downloadCertificate(Request $request, Webinar $webinar): Response
    {
        $this->authorizeRegistration($request, $webinar);
        abort_unless($webinar->certificate_enabled === 'yes', 404);
        // The webinar's backend toggle authorizes all registered attendees.
        $identity = ['webinar_id' => $webinar->id, 'user_id' => $request->user()->id];
        DB::table('certificates')->insertOrIgnore($identity + [
            'template_id' => data_get($webinar->settings, 'certificate_template_id'),
            'credential_id' => (string) Str::uuid(), 'status' => 'approved',
            'issued_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
        $certificate = DB::table('certificates')->where($identity)->first();
        DB::table('certificates')->where($identity)->update([
            'status' => 'approved', 'revoked_at' => null,
            'issued_at' => $certificate->issued_at ?: now(), 'updated_at' => now(),
        ]);
        DB::table('certificate_downloads')->insert([
            'certificate_id' => $certificate->id,
            'webinar_id' => $webinar->id,
            'user_id' => $request->user()->id,
            'credential_id' => $certificate->credential_id,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 500),
            'downloaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        AuditTrail::record('certificate.downloaded', $webinar, 'Certificate downloaded by '.$request->user()->name.'.', [
            'credential_id' => $certificate->credential_id,
            'user_id' => $request->user()->id,
        ]);
        $pdf = $this->certificatePdf($request->user()->name, $webinar->title, $certificate->credential_id, $certificate->issued_at ?: now());

        return response($pdf, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="'.Str::slug($webinar->title).'-certificate.pdf"']);
    }

    public function downloadResource(Request $request, Webinar $webinar, int $resource): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorizeRegistration($request, $webinar);
        $item = DB::table('webinar_resources')->where('webinar_id', $webinar->id)->where('id', $resource)->where('is_public', true)->where('type', 'file')->first();
        abort_unless($item, 404);
        $root = realpath(public_path('uploads/resources'));
        $path = realpath(public_path(ltrim($item->path_or_url, '/')));
        abort_unless($root && $path && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path), 404);

        return response()->download($path, (Str::slug($item->title) ?: 'resource').'.'.pathinfo($path, PATHINFO_EXTENSION));
    }

    private function authorizeRegistration(Request $request, Webinar $webinar): void
    {
        abort_unless($webinar->registrations()->where('user_id', $request->user()->id)->exists(), 403);
    }

    private function certificatePdf(string $name, string $title, string $credential, mixed $issuedAt): string
    {
        $escape = fn (string $text) => str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $lines = [['/F2', 18, 230, 520, 'CERTIFICATE OF COMPLETION'], ['/F1', 11, 290, 475, 'THIS CERTIFIES THAT'], ['/F2', 27, 220, 420, $name], ['/F1', 12, 150, 370, 'has successfully completed the webinar'], ['/F2', 18, 120, 325, $title], ['/F1', 10, 190, 255, 'Issued: '.Carbon::parse($issuedAt)->format('F j, Y')], ['/F1', 9, 150, 220, 'Credential ID: '.$credential]];
        $stream = "0.25 0.12 0.55 rg 0 0 842 595 re f\n0.98 0.97 1 rg 25 25 792 545 re f\n0.45 0.25 0.78 RG 3 w 42 42 758 511 re S\n0.12 0.08 0.22 rg\n";
        foreach ($lines as [$font,$size,$x,$y,$text]) {
            $stream .= "BT {$font} {$size} Tf {$x} {$y} Td (".$escape($text).") Tj ET\n";
        }
        $objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>', '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>', '<< /Length '.strlen($stream)." >>\nstream\n{$stream}endstream", '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>', '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>'];
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($index + 1)." 0 obj\n{$object}\nendobj\n";
        }$xref = strlen($pdf);
        $pdf .= "xref\n0 7\n0000000000 65535 f \n";
        for ($i = 1; $i <= 6; $i++) {
            $pdf .= sprintf('%010d 00000 n ', $offsets[$i])."\n";
        }$pdf .= "trailer << /Size 7 /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
