@extends('layouts.portal')
@section('title','Poll results')
@section('content')
<div class="page-heading"><div><span class="eyebrow">POLL ANALYTICS</span><h1>Poll results</h1><p>{{ $poll->webinar->title }}</p></div><a class="btn btn-light" href="{{ route('admin.polls.index',['webinar_id'=>$poll->webinar_id]) }}"><i class="bi bi-arrow-left"></i> Back to polls</a></div>
<section class="panel-card poll-results-card">
    <div class="poll-results-heading"><div><span class="status-badge {{ $poll->status==='active'?'live':'completed' }}">{{ strtoupper($poll->status) }}</span><h2>{{ $poll->question }}</h2></div><div class="poll-result-summary"><span><strong>{{ $totalUsers }}</strong> users</span><span><strong>{{ $totalVotes }}</strong> answers</span></div></div>
    <div class="poll-result-chart">
        @forelse($poll->options as $option)
            @php $percentage = $totalVotes > 0 ? round(($option->responses_count / $totalVotes) * 100, 1) : 0; @endphp
            <div class="poll-result-row"><div class="poll-result-label"><strong>{{ $option->label }}</strong><span>{{ $option->responses_count }} users · {{ $percentage }}%</span></div><div class="poll-result-track"><span style="width: {{ $percentage }}%"></span></div></div>
        @empty
            <div class="text-muted text-center py-5">No answer options are available.</div>
        @endforelse
    </div>
</section>
@endsection
