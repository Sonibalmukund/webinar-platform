@extends('layouts.portal')
@section('title', 'Q&A')
@section('content')
<div class="page-heading"><div><span class="eyebrow">AUDIENCE QUESTIONS</span><h1>Q&A</h1><p>Questions from webinars where Q&A is enabled.</p></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<section class="panel-card table-responsive">
    <table class="premium-table">
        <thead><tr><th>Attendee</th><th>Question / Answer</th><th>Webinar</th><th>Received</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($questions as $question)
            <tr>
                <td><strong>{{ $question->is_anonymous ? 'Anonymous' : ($question->user_name ?: 'Guest') }}</strong><br><small>{{ $question->is_anonymous ? '' : $question->user_email }}</small></td>
                <td style="min-width:340px">
                    <p class="mb-2">{{ $question->question }}</p>
                    <form class="d-flex gap-2" method="POST" action="{{ route('admin.questions.answer', $question->id) }}">
                        @csrf
                        <textarea class="form-control" name="answer" rows="3" maxlength="5000" required placeholder="Type official answer...">{{ $question->official_answer }}</textarea>
                        <button class="btn btn-sm btn-gradient align-self-end"><i class="bi bi-send"></i> {{ $question->official_answer ? 'Update' : 'Answer' }}</button>
                    </form>
                </td>
                <td>{{ $question->webinar_title }}</td>
                <td>{{ Carbon\Carbon::parse($question->created_at)->diffForHumans() }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.questions.update', $question->id) }}">
                        @csrf @method('PATCH')
                        <select class="form-select form-select-sm" name="status" onchange="this.form.submit()">
                            @foreach(['open','answered','closed'] as $status)
                                <option value="{{ $status }}" @selected($question->status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-muted py-5">No questions received.</td></tr>
        @endforelse
        </tbody>
    </table>
</section>
<div class="mt-3">{{ $questions->links() }}</div>
@endsection
