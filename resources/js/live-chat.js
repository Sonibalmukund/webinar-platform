import './bootstrap';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const escapeHtml = str => String(str || '').replace(/[&<>'"]/g, tag => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
}[tag] || tag));

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
        const tracker = document.querySelector('[data-attendance-tracker]');
        const slug = tracker?.dataset.webinarSlug || root.dataset.webinarSlug || root.dataset.webinarId;

        // Ensure higher upvotes come first ("jitne upvote ho vo phele aaye")
        messages.sort((a, b) => (Number(b.votes_count) || 0) - (Number(a.votes_count) || 0));

        messages.forEach(message => {
            const row = document.createElement('div');
            row.className = 'chat-message'; row.dataset.messageId = message.id;
            row.append(textNode('span', String(message.user_name || 'User').slice(0, 2).toUpperCase()));
            const copy = document.createElement('div'); copy.className = 'chat-message-copy';
            const head = document.createElement('div'); head.className = 'chat-message-head';
            head.append(textNode('small', message.user_name || 'Attendee'));
            const sent = new Date(message.sent_at);
            const time = textNode('time', Number.isNaN(sent.getTime()) ? '' : sent.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
            time.dateTime = message.sent_at || '';
            head.append(time);
            copy.append(head);

            if (message.reply_to_user_name) {
                const quote = document.createElement('div');
                quote.className = 'chat-reply-quote';
                quote.innerHTML = `<i class="bi bi-reply-fill"></i><span>Replying to <strong>${escapeHtml(message.reply_to_user_name)}</strong>: ${escapeHtml((message.reply_to_message || '').slice(0, 45))}</span>`;
                copy.append(quote);
            }

            if (message.message) copy.append(textNode('p', message.message));
            if (message.attachment_path) {
                const url = new URL(message.attachment_path, location.origin);
                if (url.origin === location.origin && url.pathname.startsWith('/uploads/chat/')) {
                    const attachment = textNode('a', message.attachment_name || 'Download attachment', 'chat-attachment');
                    attachment.href = url.href; attachment.target = '_blank'; attachment.rel = 'noopener'; attachment.download = '';
                    copy.append(attachment);
                }
            }

            // Bottom-left action bar: Upvote & Reply
            const actions = document.createElement('div');
            actions.className = 'chat-message-actions';

            const voteBtn = document.createElement('button');
            voteBtn.type = 'button';
            voteBtn.className = `chat-vote-btn ${message.has_voted ? 'voted' : ''}`;
            voteBtn.dataset.chatVoteBtn = '';
            voteBtn.dataset.messageId = message.id;
            voteBtn.dataset.voteUrl = `/webinars/${slug}/chat/${message.id}/vote`;
            voteBtn.title = 'Upvote message';
            voteBtn.innerHTML = `<i class="bi ${message.has_voted ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up'}"></i><span data-chat-vote-count>${message.votes_count || 0}</span>`;
            actions.append(voteBtn);

            const replyBtn = document.createElement('button');
            replyBtn.type = 'button';
            replyBtn.className = 'chat-reply-btn';
            replyBtn.dataset.chatReplyBtn = '';
            replyBtn.dataset.messageId = message.id;
            replyBtn.dataset.userName = message.user_name || 'Attendee';
            replyBtn.dataset.messageText = (message.message || '').slice(0, 80);
            replyBtn.title = 'Reply';
            replyBtn.innerHTML = `<i class="bi bi-reply-fill"></i>`;
            actions.append(replyBtn);

            copy.append(actions);
            row.append(copy); fragment.append(row);
        });
        if (!messages.length) fragment.append(textNode('p', 'No chat messages yet. Start the conversation.', 'empty-module'));
        stream.replaceChildren(fragment);
    };
    let refreshing = false, queued = false;
    const refresh = async () => {
        if (refreshing) { queued = true; return; }
        refreshing = true;
        try {
            do {
                queued = false;
                const nearBottom = stream.scrollHeight - stream.scrollTop - stream.clientHeight < 90;
                const scrollTop = stream.scrollTop;
                const response = await fetch(root.dataset.chatHistory, {headers: {'Accept': admin ? 'text/html' : 'application/json'}, cache:'no-store'});
                if (!response.ok || response.redirected) {
                    if (response.status === 404) return;
                    throw new Error('Unable to refresh chat. Please reload or sign in again.');
                }
                if (admin) {
                    const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                    for (const selector of ['#chatMessageStream', '#chatPeopleList', '[data-chat-summary]', '.chat-people-head']) {
                        const current = document.querySelector(selector), updated = page.querySelector(selector);
                        if (current && updated) current.replaceChildren(...updated.childNodes);
                    }
                    document.querySelector('#chatPersonSearch')?.dispatchEvent(new Event('input'));
                } else renderLearner((await response.json()).messages || []);
                stream.scrollTop = scrollTop;
            } while (queued);
        } catch (exception) { setError(exception.message); }
        finally { refreshing = false; }
    };
    window.refreshLiveChat = refresh;

    // Handle Reply clicks
    document.addEventListener('click', event => {
        const replyBtn = event.target.closest('[data-chat-reply-btn]');
        if (replyBtn) {
            event.preventDefault();
            const messageId = replyBtn.dataset.messageId;
            const userName = replyBtn.dataset.userName || 'Attendee';
            const text = replyBtn.dataset.messageText || '';

            const replyInput = form?.querySelector('[data-chat-reply-input]');
            const replyPreview = form?.querySelector('[data-chat-reply-preview]');
            const replyUser = form?.querySelector('[data-reply-user]');
            const replySnippet = form?.querySelector('[data-reply-snippet]');
            const inputField = form?.querySelector('input[name="message"]');

            if (replyInput && replyPreview) {
                replyInput.value = messageId;
                if (replyUser) replyUser.textContent = userName;
                if (replySnippet) replySnippet.textContent = text;
                replyPreview.style.display = 'flex';
                if (inputField) inputField.focus();
            }
            return;
        }

        const cancelReplyBtn = event.target.closest('[data-chat-reply-cancel]');
        if (cancelReplyBtn) {
            event.preventDefault();
            const replyInput = form?.querySelector('[data-chat-reply-input]');
            const replyPreview = form?.querySelector('[data-chat-reply-preview]');
            if (replyInput) replyInput.value = '';
            if (replyPreview) replyPreview.style.display = 'none';
            return;
        }

        const adminReplyBtn = event.target.closest('[data-admin-reply-btn]');
        if (adminReplyBtn) {
            event.preventDefault();
            const messageId = adminReplyBtn.dataset.messageId;
            const userName = adminReplyBtn.dataset.userName || 'Attendee';
            const text = adminReplyBtn.dataset.messageText || '';

            const replyInput = form?.querySelector('[data-admin-reply-input]');
            const replyPreview = form?.querySelector('[data-admin-reply-preview]');
            const replyUser = form?.querySelector('[data-admin-reply-user]');
            const replySnippet = form?.querySelector('[data-admin-reply-snippet]');
            const textarea = form?.querySelector('textarea[name="message"]');

            if (replyInput && replyPreview) {
                replyInput.value = messageId;
                if (replyUser) replyUser.textContent = userName;
                if (replySnippet) replySnippet.textContent = text;
                replyPreview.style.display = 'flex';
                if (textarea) textarea.focus();
            }
            return;
        }

        const cancelAdminReplyBtn = event.target.closest('[data-admin-reply-cancel]');
        if (cancelAdminReplyBtn) {
            event.preventDefault();
            const replyInput = form?.querySelector('[data-admin-reply-input]');
            const replyPreview = form?.querySelector('[data-admin-reply-preview]');
            if (replyInput) replyInput.value = '';
            if (replyPreview) replyPreview.style.display = 'none';
            return;
        }
    });

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
                const replyInput = submitted.querySelector('[data-chat-reply-input], [data-admin-reply-input]');
                const replyPreview = submitted.querySelector('[data-chat-reply-preview], [data-admin-reply-preview]');
                if (replyInput) replyInput.value = '';
                if (replyPreview) replyPreview.style.display = 'none';
            }
            await refresh();
        } catch (exception) { setError(exception.message || 'Unable to update chat. Please try again.'); }
        finally { delete submitted.dataset.sending; if (button) button.disabled = false; }
    });

    document.addEventListener('click', async event => {
        const btn = event.target.closest('[data-chat-vote-btn]');
        if (!btn || !btn.dataset.voteUrl) return;
        event.preventDefault();
        btn.disabled = true;
        try {
            const response = await fetch(btn.dataset.voteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                btn.classList.toggle('voted', Boolean(data.voted));
                const icon = btn.querySelector('i');
                if (icon) icon.className = data.voted ? 'bi bi-hand-thumbs-up-fill' : 'bi bi-hand-thumbs-up';
                const count = btn.querySelector('[data-chat-vote-count]');
                if (count && typeof data.votes_count !== 'undefined') count.textContent = data.votes_count;
                // Re-fetch to sort highest upvotes first ("jitne upvote ho vo phele aaye")
                refresh();
            }
        } catch {
        } finally {
            btn.disabled = false;
        }
    });
    const channel = window.Echo.private(`webinar.chat.${root.dataset.webinarId}`);
    channel.listen('.chat.message', refresh).listen('.chat.deleted', refresh)
        .listen('.chat.voted', event => {
            const row = stream.querySelector(`[data-message-id="${event.message_id}"]`);
            if (row) {
                const count = row.querySelector('[data-chat-vote-count]');
                if (count && typeof event.votes_count !== 'undefined') count.textContent = event.votes_count;
            }
            // Re-order messages so higher upvoted messages move up
            refresh();
        })
        .subscribed(() => { if (status) status.textContent = 'Live chat connected'; refresh(); })
        .error(() => { if (status) status.textContent = 'Live chat unavailable — reload to retry'; });
    const connection = window.Echo.connector?.pusher?.connection;
    connection?.bind('state_change', ({current}) => {
        if (status && current !== 'connected') status.textContent = 'Reconnecting to live chat…';
    });
});
