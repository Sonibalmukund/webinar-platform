@if($activePoll)
    @php($isQuiz = $activePoll->options->contains('is_correct', true))
    @php($showCorrectAnswer = $activePoll->shouldRevealAnswer($webinar))
    @php($allowMultiple = (bool) $activePoll->allow_multiple)
    @php($answerReveal = $activePoll->answer_reveal ?: 'after_webinar')
    <div class="poll-heading">
        <span class="poll-live-dot"></span>
        <small>{{ $isQuiz ? 'LIVE QUIZ' : 'LIVE POLL' }}</small>
        @if($pollResponse->isNotEmpty())
            <b><i class="bi bi-check2-circle"></i> Answer submitted</b>
        @endif
    </div>
    <h3 class="poll-question">{{ $activePoll->question }}</h3>
    <p class="poll-help">{{ $pollResponse->isNotEmpty() ? ($isQuiz ? ($showCorrectAnswer ? 'Answer submitted · The correct answer is highlighted.' : ($answerReveal === 'after_webinar' ? 'Answer submitted · Correct answer will appear after the webinar finishes.' : 'Answer submitted · Correct answer is hidden.')) : 'Live results · Your selected option is highlighted.') : ($allowMultiple ? 'Select one or more answers, then submit.' : 'Choose one answer. It submits instantly.') }}</p>
    <form method="POST" action="{{ route('webinars.polls.vote', [$webinar, $activePoll]) }}" data-instant-poll data-is-quiz="{{ $isQuiz ? 'true' : 'false' }}" data-answer-reveal="{{ $answerReveal }}" data-multiple="{{ $allowMultiple ? 'true' : 'false' }}" data-results-url="{{ route('webinars.polls.results', [$webinar, $activePoll]) }}" data-poll-id="{{ $activePoll->id }}" data-answered="{{ $pollResponse->isNotEmpty() ? 'true' : 'false' }}" data-show-correct-answer="{{ $showCorrectAnswer ? 'true' : 'false' }}">
        @csrf
        @php($total = max(1, $activePoll->options->sum('responses_count')))
        @foreach($activePoll->options as $option)
            @php($percent = round($option->responses_count / $total * 100))
            @php($selected = $pollResponse->contains($option->id))
            <label class="poll-choice selectable {{ $selected ? 'selected' : '' }} {{ $pollResponse->isNotEmpty() ? 'locked' : '' }} {{ $pollResponse->isNotEmpty() && $showCorrectAnswer && $option->is_correct ? 'quiz-correct' : '' }} {{ $pollResponse->isNotEmpty() && $showCorrectAnswer && $selected && !$option->is_correct ? 'quiz-incorrect' : '' }}" data-option-id="{{ $option->id }}">
                <div class="d-flex justify-content-between">
                    <span>
                        <input type="{{ $allowMultiple ? 'checkbox' : 'radio' }}" name="{{ $allowMultiple ? 'option_ids[]' : 'option_id' }}" value="{{ $option->id }}" @if(!$allowMultiple) required @endif @checked($selected) @disabled($pollResponse->isNotEmpty())>
                        <i class="poll-radio {{ $allowMultiple ? 'poll-checkbox' : '' }}"></i>{{ $option->label }}
                    </span>
                    @unless($isQuiz)<b data-poll-result @if($pollResponse->isEmpty()) hidden @endif>{{ $pollResponse->isNotEmpty() ? $percent : 0 }}%</b>@endunless
                </div>
                @unless($isQuiz)<div class="poll-track" data-poll-result @if($pollResponse->isEmpty()) hidden @endif>
                    <i style="width:{{ $pollResponse->isNotEmpty() ? $percent : 0 }}%"></i>
                </div>@endunless
            </label>
        @endforeach
        @if($allowMultiple && $pollResponse->isEmpty())
            <button class="btn btn-gradient w-100 mt-3" type="submit" data-poll-submit><i class="bi bi-send-check me-1"></i> Submit selected answers</button>
        @endif
        @unless($isQuiz)<p class="poll-help text-center mt-3" data-poll-result data-poll-total @if($pollResponse->isEmpty()) hidden @endif>{{ $pollResponse->isNotEmpty() ? $activePoll->options->sum('responses_count') : 0 }} total votes</p>@endunless
    </form>
@else
    <div class="empty-module">
        <div>
            <i class="bi bi-bar-chart display-5"></i>
            <p class="mt-2">No active poll.</p>
        </div>
    </div>
@endif
