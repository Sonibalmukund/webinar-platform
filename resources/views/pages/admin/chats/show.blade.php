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
                    <div class="chat-bubble-row {{ $mine?'mine':'' }}" data-message-id="{{ $message->id }}"><span class="chat-avatar">{{ Str::of($message->user_name ?: 'U')->substr(0,1)->upper() }}</span><div><div class="chat-message-meta"><strong>{{ $message->user_name ?: 'Deleted user' }}</strong><small>{{ Carbon\Carbon::parse($message->sent_at)->format('h:i A') }}</small>@if(!empty($message->votes_count))<span class="badge rounded-pill bg-light text-primary border ms-1" style="font-size:.65rem;" title="Upvotes"><i class="bi bi-hand-thumbs-up-fill me-1"></i>{{ $message->votes_count }}</span>@endif</div><div class="chat-bubble">@if(!empty($message->reply_to_user_name))<div class="chat-reply-quote" style="display:flex;align-items:center;gap:5px;padding:3px 8px;margin-bottom:6px;border-left:2px solid #6366f1;background:rgba(99,102,241,0.08);border-radius:4px;font-size:.7rem;color:#475569;"><i class="bi bi-reply-fill text-primary"></i><span>Replying to <strong>{{ $message->reply_to_user_name }}</strong>: {{ Str::limit($message->reply_to_message, 45) }}</span></div>@endif{{ $message->message }}@if($message->attachment_path)<a class="chat-attachment" href="{{ $message->attachment_path }}" target="_blank" download><i class="bi bi-paperclip"></i><span>{{ $message->attachment_name }}</span></a>@endif<div class="d-flex align-items-center gap-2 mt-1"><button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" data-admin-reply-btn data-message-id="{{ $message->id }}" data-user-name="{{ $message->user_name ?: 'Attendee' }}" data-message-text="{{ Str::limit($message->message, 80) }}" style="font-size: .8rem;" title="Reply"><i class="bi bi-reply-fill"></i></button></div></div><form method="POST" action="{{ route($routePrefix.'.chats.messages.destroy',[$webinar,$message->id]) }}" data-live-chat-delete data-no-validation>@csrf @method('DELETE')<button title="Remove message"><i class="bi bi-trash3"></i></button></form></div></div>
                @endforeach
            @empty
                <div class="chat-stream-empty"><i class="bi bi-chat-heart"></i><strong>Conversation starts here</strong><span>Send the first admin message using the box below.</span></div>
            @endforelse
        </div>
        <form class="chat-composer" method="POST" enctype="multipart/form-data" action="{{ route($routePrefix.'.chats.store',$webinar) }}" id="adminChatForm" data-no-validation style="flex-wrap:wrap;">@csrf<input type="hidden" name="reply_to_id" value="" data-admin-reply-input><div class="admin-reply-preview mb-2 p-2 rounded border bg-light align-items-center justify-content-between w-100" data-admin-reply-preview style="display:none;font-size:.78rem;"><div class="text-truncate"><i class="bi bi-reply-fill text-primary me-1"></i><span>Replying to <strong data-admin-reply-user>...</strong>: <span class="text-muted" data-admin-reply-snippet>...</span></span></div><button type="button" class="btn-close btn-close-sm ms-2" data-admin-reply-cancel aria-label="Cancel reply" style="font-size:.65rem;"></button></div><label class="chat-file-button" title="Attach file"><i class="bi bi-paperclip"></i><input type="file" name="attachment" hidden></label><div><textarea name="message" rows="2" maxlength="2000" placeholder="Type a message...">{{ old('message') }}</textarea><small>Send a message or attach an image, video, document, audio file or ZIP (maximum 20 MB).</small></div><button class="btn btn-gradient"><i class="bi bi-send-fill"></i><span>Send</span></button></form>
    </section>
</div>
@endsection

