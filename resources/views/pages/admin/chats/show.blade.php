@extends('layouts.portal')
@section('title', $webinar->title.' Chat')
@section('content')
<div class="page-heading">
    <div><span class="eyebrow">CHAT THREAD</span><h1>{{ $webinar->title }}</h1><p data-chat-summary>{{ $participants->count() }} people chatted · {{ $messages->count() }} total messages</p></div>
    <a href="{{ route($routePrefix.'.chats.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to chats</a>
</div>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<p class="small text-muted" data-chat-connection role="status">Connecting to live chat…</p><div class="alert alert-danger" data-chat-error role="alert" hidden></div><div class="admin-chat-shell" data-admin-live-chat data-webinar-id="{{ $webinar->id }}" data-chat-history="{{ route($routePrefix.'.chats.show',$webinar) }}">
    <aside class="chat-people-panel">
        <div class="chat-people-head"><div><strong>Participants</strong><small>{{ $participants->count() }} unique chatters</small></div><span class="chat-count-badge">{{ $messages->count() }}</span></div>
        <div class="chat-person-search"><i class="bi bi-search"></i><input id="chatPersonSearch" placeholder="Search name or email"></div>
        <div class="chat-people-list" id="chatPeopleList">
            @forelse($participants as $person)
                <div data-search="{{ strtolower($person->name.' '.$person->email) }}"><span class="chat-avatar">{{ Str::of($person->name)->substr(0,1)->upper() }}</span><span><strong>{{ $person->name }}</strong><small>{{ $person->email }}</small></span><b>{{ $person->messages_count }}</b></div>
            @empty
                <p class="chat-empty-copy">No one has posted in this chat yet.</p>
            @endforelse
        </div>
    </aside>
    <section class="chat-conversation">
        <header><div><span class="chat-avatar event"><i class="bi bi-camera-video"></i></span><span><strong>{{ $webinar->title }}</strong><small><i class="chat-online-dot"></i> {{ $webinar->chat_enabled?'Chat enabled':'Chat disabled' }}</small></span></div><span class="status-badge {{ $webinar->status }}">{{ strtoupper($webinar->status) }}</span></header>
        <div class="chat-message-stream" id="chatMessageStream">
            @forelse($messages->groupBy(function ($message) { return Carbon\Carbon::parse($message->sent_at)->format('Y-m-d'); }) as $date => $dayMessages)
                <div class="chat-date-divider"><span>{{ Carbon\Carbon::parse($date)->format('d M Y') }}</span></div>
                @foreach($dayMessages as $message)
                    @php $mine = $message->user_id === auth()->id(); @endphp
                    <div class="chat-bubble-row {{ $mine?'mine':'' }}"><span class="chat-avatar">{{ Str::of($message->user_name ?: 'U')->substr(0,1)->upper() }}</span><div><div class="chat-message-meta"><strong>{{ $message->user_name ?: 'Deleted user' }}</strong><small>{{ Carbon\Carbon::parse($message->sent_at)->format('h:i A') }}</small></div><div class="chat-bubble">{{ $message->message }}@if($message->attachment_path)<a class="chat-attachment" href="{{ $message->attachment_path }}" target="_blank" download><i class="bi bi-paperclip"></i><span>{{ $message->attachment_name }}</span></a>@endif</div><form method="POST" action="{{ route($routePrefix.'.chats.messages.destroy',[$webinar,$message->id]) }}" data-live-chat-delete data-no-validation>@csrf @method('DELETE')<button title="Remove message"><i class="bi bi-trash3"></i></button></form></div></div>
                @endforeach
            @empty
                <div class="chat-stream-empty"><i class="bi bi-chat-heart"></i><strong>Conversation starts here</strong><span>Send the first admin message using the box below.</span></div>
            @endforelse
        </div>
        <form class="chat-composer" method="POST" enctype="multipart/form-data" action="{{ route($routePrefix.'.chats.store',$webinar) }}" id="adminChatForm" data-no-validation>@csrf<label class="chat-file-button" title="Attach file"><i class="bi bi-paperclip"></i><input type="file" name="attachment" hidden></label><div><textarea name="message" rows="2" maxlength="2000" placeholder="Type a message...">{{ old('message') }}</textarea><small>Send a message or attach an image, video, document, audio file or ZIP (maximum 20 MB).</small></div><button class="btn btn-gradient"><i class="bi bi-send-fill"></i><span>Send</span></button></form>
    </section>
</div>
@endsection

