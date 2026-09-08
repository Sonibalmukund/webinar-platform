import './bootstrap';

// Fetch a fresh snapshot after events and reconnects so missed/deleted messages reconcile.
document.addEventListener('DOMContentLoaded', () => {
    const admin = document.querySelector('[data-admin-live-chat]');
    const learner = document.querySelector('[data-learner-live-chat]');
    const root = admin || learner;
    if (!root || !window.Echo) return;
    const status = document.querySelector('[data-chat-connection]');
    const error = document.querySelector('[data-chat-error]');
    const stream = document.querySelector(admin ? '#chatMessageStream' : '[data-realtime-chat]');
    const form = document.querySelector(admin ? '#adminChatForm' : '[data-realtime-chat-form]');
    const setError = message => { if (error) { error.textContent = message; error.hidden = !message; } };
    const textNode = (tag, text, className) => {
        const node = document.createElement(tag);
        node.textContent = text;
        if (className) node.className = className;
        return node;
    };
    const renderLearner = messages => {
        const fragment = document.createDocumentFragment();
        messages.forEach(message => {
            const row = document.createElement('div');
            row.className = 'chat-message'; row.dataset.messageId = message.id;
            row.append(textNode('span', String(message.user_name || 'User').slice(0, 2).toUpperCase()));
            const copy = document.createElement('div'); copy.className='chat-message-copy';
            const head=document.createElement('div');head.className='chat-message-head';head.append(textNode('small', message.user_name || 'Attendee'));
            const sent=new Date(message.sent_at);const time=textNode('time',Number.isNaN(sent.getTime())?'':sent.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}));time.dateTime=message.sent_at||'';head.append(time);copy.append(head);
            if (message.message) copy.append(textNode('p', message.message));
            if (message.attachment_path) {
                const url = new URL(message.attachment_path, location.origin);
                if (url.origin === location.origin && url.pathname.startsWith('/uploads/chat/')) {
                    const attachment = textNode('a', message.attachment_name || 'Download attachment', 'chat-attachment');
                    attachment.href = url.href; attachment.target = '_blank'; attachment.rel = 'noopener'; attachment.download = '';
                    copy.append(attachment);
                }
            }
            row.append(copy); fragment.append(row);
        });
        if (!messages.length) fragment.append(textNode('p', 'No chat messages yet. Start the conversation.', 'empty-module'));
        stream.replaceChildren(fragment);
    };
    let refreshing = false, queued = false, forceBottom = false;
    const refresh = async () => {
        if (refreshing) { queued = true; return; }
        refreshing = true;
        try {
            do {
                queued = false;
                const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 90;
                const scrollTop = stream.scrollTop;
                const response = await fetch(root.dataset.chatHistory, {headers: {'Accept': admin ? 'text/html' : 'application/json'}, cache:'no-store'});
                if (!response.ok || response.redirected) throw new Error('Unable to refresh chat. Please reload or sign in again.');
                if (admin) {
                    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                    for (const selector of ['#chatMessageStream', '#chatPeopleList', '[data-chat-summary]', '.chat-people-head']) {
                        const current = document.querySelector(selector), updated = page.querySelector(selector);
                        if (current && updated) current.replaceChildren(...updated.childNodes);
                    }
                    document.querySelector('#chatPersonSearch')?.dispatchEvent(new Event('input'));
                } else renderLearner((await response.json()).messages);
                stream.scrollTop = nearBottom || forceBottom ? stream.scrollHeight : scrollTop;
                forceBottom = false;
            } while (queued);
        } catch (exception) { setError(exception.message); }
        finally { refreshing = false; }
    };
    document.addEventListener('submit', async event => {
        const submitted = event.target;
        const deleting = admin && submitted.matches('[data-live-chat-delete]');
        if (submitted !== form && !deleting) return;
        if (event.defaultPrevented) return;
        event.preventDefault();
        if (deleting && !window.confirm('Remove this message from the webinar chat?')) return;
        if (submitted.dataset.sending) return;
        submitted.dataset.sending = '1';
        const button = submitted.querySelector('button[type=submit],button:not([type])');
        if (button) button.disabled = true;
        setError('');
        try {
            const response = await fetch(submitted.action, {method:'POST',body:new FormData(submitted),headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
            const data = await response.json();
            if (!response.ok) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Unable to update chat.');
            if (!deleting) {
                submitted.reset(); submitted.querySelector('.chat-pending-file')?.remove();
                forceBottom = true;
            }
            await refresh();
        } catch (exception) { setError(exception.message || 'Unable to update chat. Please try again.'); }
        finally { delete submitted.dataset.sending; if (button) button.disabled = false; }
    });
    const channel = window.Echo.private(`webinar.chat.${root.dataset.webinarId}`);
    channel.listen('.chat.message', refresh).listen('.chat.deleted', refresh)
        .subscribed(() => { if (status) status.textContent = 'Live chat connected'; refresh(); })
        .error(() => { if (status) status.textContent = 'Live chat unavailable — reload to retry'; });
    const connection = window.Echo.connector?.pusher?.connection;
    connection?.bind('state_change', ({current}) => {
        if (status && current !== 'connected') status.textContent = 'Reconnecting to live chat…';
    });
    stream.scrollTop = stream.scrollHeight;
});
