<div class="qa-shell" data-qa-container data-qa-url="{{ route('webinars.questions.index', $webinar) }}">
    <div class="private-comment-head mb-3">
        <span><i class="bi bi-patch-question-fill"></i></span>
        <div>
            <small>LIVE Q&amp;A</small>
            <h3>Ask the speakers</h3>
            <p>Submit questions and upvote your favorites so the host can address them live.</p>
        </div>
    </div>

    <form class="qa-composer mb-4" method="POST" action="{{ route('webinars.questions.store', $webinar) }}" data-qa-form>
        @csrf
        <div class="comment-input-shell mb-2">
            <textarea name="question" required minlength="3" maxlength="500" placeholder="Ask a question for the speaker..." rows="2" class="form-control" data-qa-input></textarea>
        </div>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <label class="form-check form-switch mb-0 small text-white-50" style="cursor: pointer;">
                <input class="form-check-input" type="checkbox" name="is_anonymous" value="1">
                <span>Ask anonymously</span>
            </label>
            <button class="btn btn-gradient btn-sm px-3" type="submit" data-qa-submit-btn>
                <span>Ask Question</span>
                <i class="bi bi-send-fill ms-1"></i>
            </button>
        </div>
    </form>

    <div class="qa-list-heading d-flex align-items-center justify-content-between mb-3">
        <span class="text-white-50 small fw-bold text-uppercase" style="letter-spacing: .06em;">Questions (<b data-qa-total-count>{{ $questions->count() }}</b>)</span>
        <span class="text-white-50 small"><i class="bi bi-arrow-down-up me-1"></i>Sorted by votes</span>
    </div>

    <div class="qa-questions-stream" data-qa-stream>
        @forelse($questions as $q)
        <div class="qa-card mb-3 @if($q->status==='answered') is-answered @endif" data-qa-item-id="{{ $q->id }}">
            <div class="qa-card-body">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <p class="qa-text mb-0 flex-grow-1">{{ $q->question }}</p>
                    @php($hasVoted = in_array($q->id, $userVotes))
                    <button type="button" class="qa-vote-btn {{ $hasVoted ? 'voted' : '' }}" data-qa-vote-btn data-vote-url="{{ route('webinars.questions.vote', [$webinar, $q->id]) }}" title="{{ $hasVoted ? 'Remove upvote' : 'Upvote question' }}">
                        <i class="bi {{ $hasVoted ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i>
                        <span data-qa-vote-count>{{ $q->votes_count }}</span>
                    </button>
                </div>
                <div class="qa-meta d-flex align-items-center gap-2 flex-wrap">
                    <span class="qa-author">{{ $q->is_anonymous ? 'Anonymous' : ($q->user_name ?: 'Attendee') }}</span>
                    <span class="qa-dot">·</span>
                    <time class="qa-time">{{ Carbon\Carbon::parse($q->created_at)->diffForHumans() }}</time>
                    @if($q->status === 'answered')
                        <span class="qa-badge-answered ms-auto"><i class="bi bi-check-circle-fill me-1"></i>Answered</span>
                    @endif
                </div>
            </div>
            @if(!empty($q->official_answer))
            <div class="qa-official-answer mt-2 pt-2 border-top">
                <div class="d-flex align-items-center gap-1 text-success small fw-bold mb-1">
                    <i class="bi bi-patch-check-fill"></i> Answer from host
                </div>
                <p class="small text-white-50 mb-0">{{ $q->official_answer }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="empty-module qa-empty" data-qa-empty>
            <div>
                <i class="bi bi-question-circle display-5"></i>
                <p class="mt-2">No questions asked yet.<br><small class="text-white-50">Be the first one to ask a question!</small></p>
            </div>
        </div>
        @endforelse
    </div>
</div>
