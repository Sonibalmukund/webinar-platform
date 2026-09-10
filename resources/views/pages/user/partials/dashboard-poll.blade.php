@if($activePoll)
    @php($isQuiz = $activePoll->options->contains('is_correct', true))
    <div class="poll-heading">
        <span class="poll-live-dot"></span>
        <small>{{ $isQuiz ? 'LIVE QUIZ' : 'LIVE POLL' }}</small>
        @if($pollResponse->isNotEmpty())
            <b><i class="bi bi-check2-circle"></i> Answer submitted</b>
        @endif
    </div>
    <h3 class="poll-question">{{ $activePoll->question }}</h3>
    <p class="poll-help">{{ $pollResponse->isNotEmpty() ? 'Live results · Your answer is highlighted.' : 'Choose one answer. It submits instantly.' }}</p>
    <form method="POST" action="{{ route('webinars.polls.vote', [$webinar, $activePoll]) }}" data-instant-poll data-results-url="{{ route('webinars.polls.results', [$webinar, $activePoll]) }}" data-poll-id="{{ $activePoll->id }}" data-answered="{{ $pollResponse->isNotEmpty() ? 'true' : 'false' }}">
        @csrf
        @php($total = max(1, $activePoll->options->sum('responses_count')))
        @foreach($activePoll->options as $option)
            @php($percent = round($option->responses_count / $total * 100))
            @php($selected = $pollResponse->contains($option->id))
            <label class="poll-choice selectable {{ $selected ? 'selected' : '' }} {{ $pollResponse->isNotEmpty() ? 'locked' : '' }} {{ $pollResponse->isNotEmpty() && $isQuiz && $option->is_correct ? 'quiz-correct' : '' }} {{ $pollResponse->isNotEmpty() && $isQuiz && $selected && !$option->is_correct ? 'quiz-incorrect' : '' }}" data-option-id="{{ $option->id }}">
                <div class="d-flex justify-content-between">
                    <span>
                        <input type="radio" name="option_id" value="{{ $option->id }}" required @checked($selected) @disabled($pollResponse->isNotEmpty())>
                        <i class="poll-radio"></i>{{ $option->label }}
                        <small data-answer-state>@if($pollResponse->isNotEmpty() && $isQuiz && $option->is_correct) Correct Answer @elseif($pollResponse->isNotEmpty() && $selected) Your Answer @endif</small>
                    </span>
                    <b data-poll-result @if($pollResponse->isEmpty()) hidden @endif>{{ $pollResponse->isNotEmpty() ? $percent : 0 }}%</b>
                </div>
                <div class="poll-track" data-poll-result @if($pollResponse->isEmpty()) hidden @endif>
                    <i style="width:{{ $pollResponse->isNotEmpty() ? $percent : 0 }}%"></i>
                </div>
            </label>
        @endforeach
        <p class="poll-help text-center mt-3" data-poll-result data-poll-total @if($pollResponse->isEmpty()) hidden @endif>{{ $pollResponse->isNotEmpty() ? $activePoll->options->sum('responses_count') : 0 }} total votes</p>
    </form>
@else
    <div class="empty-module">
        <div>
            <i class="bi bi-bar-chart display-5"></i>
            <p class="mt-2">No active poll.</p>
        </div>
    </div>
@endif
