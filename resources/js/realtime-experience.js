const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const post = (url, keepalive = false) => fetch(url, {method:'POST', keepalive, credentials:'same-origin', headers:{'X-CSRF-TOKEN':csrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).then(async response=>{if(response.ok){const data=await response.json();window.syncRoomParticipants?.(data);}return response;}).catch(()=>null);
document.addEventListener('DOMContentLoaded', () => {
    const notify = message => {
        if (window.showToast) {
            window.showToast(message, 'info');
            return;
        }
        const toast = document.querySelector('#appToast');
        if (!toast || !message) return;
        toast.querySelector('.toast-body span').textContent = message;
        if (window.bootstrap) bootstrap.Toast.getOrCreateInstance(toast, {delay:4200}).show();
    };
    const getMyUserId = () => String(document.querySelector('[data-attendance-tracker]')?.dataset.authUserId || document.body.dataset.authUserId || '');
    const handUpdate = event => {
        const list = document.querySelector('.participant-list');
        const myId = getMyUserId();
        let participant = list?.querySelector(`[data-participant-id="${event.user_id}"]`);
        if (event.state === 'leave') {
            if (String(event.user_id) !== myId) participant?.remove();
            return;
        }
        const isOwn = String(event.user_id) === myId;
        if (list && !participant && event.user_name) {
            participant = document.createElement('div');
            participant.className = 'd-flex align-items-center gap-2 py-2';
            participant.dataset.participantId = event.user_id;
            const avatar = document.createElement('span');
            avatar.className = 'avatar'; avatar.textContent = event.user_name.slice(0, 2).toUpperCase();
            const name = document.createElement('strong');
            name.className = 'small';
            name.innerHTML = `${event.user_name}${isOwn ? ' <small class="text-white-50 ms-1">(You)</small>' : ''}`;
            const hand = document.createElement('span');
            hand.className = 'text-warning ms-auto';
            const icon = document.querySelector('[data-raise-hand] svg');
            if (icon) hand.append(icon.cloneNode(true));
            hand.dataset.participantHand = ''; hand.hidden = true; hand.setAttribute('aria-label', 'Hand raised');
            participant.append(avatar, name, hand);
            if (isOwn) {
                list.prepend(participant);
            } else {
                list.append(participant);
            }
        } else if (participant && isOwn) {
            const strong = participant.querySelector('strong');
            if (strong && !strong.querySelector('small')) {
                strong.innerHTML = `${event.user_name || strong.textContent.trim()} <small class="text-white-50 ms-1">(You)</small>`;
            }
        }
        if (!['hand-raised', 'hand-lowered'].includes(event.state)) return;
        const raised = event.state === 'hand-raised';
        if (!isOwn) notify(`${event.user_name || 'An attendee'} ${raised ? 'raised' : 'lowered'} their hand.`);
        const row = document.querySelector(`[data-participant-id="${event.user_id}"]`);
        row?.querySelector('[data-participant-hand]')?.toggleAttribute('hidden', !raised);
        if (isOwn) document.querySelectorAll('[data-raise-hand]').forEach(button => {
            button.classList.toggle('active', raised);
            button.querySelector('span').textContent = raised ? 'Lower hand' : 'Raise hand';
        });
    };
    window.syncRoomParticipants = data => {
        if (!Array.isArray(data.participants)) return;
        const list = document.querySelector('.participant-list');
        if (!list) return;
        const myId = getMyUserId();
        const ids = new Set(data.participants.map(person => String(person.id)));
        if (myId) ids.add(myId);
        list.querySelectorAll('[data-participant-id]').forEach(row => {
            if (!ids.has(row.dataset.participantId) && row.dataset.participantId !== myId) row.remove();
        });
        data.participants.forEach(person => {
            handUpdate({user_id: person.id, user_name: person.name, state: 'snapshot'});
            const row = list.querySelector(`[data-participant-id="${person.id}"]`);
            const raised = Boolean(Number(person.raised_hand));
            row?.querySelector('[data-participant-hand]')?.toggleAttribute('hidden', !raised);
            if (String(person.id) === myId) {
                document.querySelectorAll('[data-raise-hand]').forEach(button => {
                    button.classList.toggle('active', raised);
                    button.querySelector('span').textContent = raised ? 'Lower hand' : 'Raise hand';
                });
            }
        });
        const currentRows = list.querySelectorAll('[data-participant-id]').length;
        const onlineCount = Math.max(1, currentRows, data.participants.length);
        document.querySelectorAll('[data-room-online]').forEach(node => node.textContent = onlineCount);
    };
    const publicWebinar=document.querySelector('[data-public-webinar]');
    if(publicWebinar && window.Echo) window.Echo.channel(`webinar.public.${publicWebinar.dataset.publicWebinar}`).listen('.room.updated',event=>{if(event.change==='status')window.location.reload();});
    const attendee = document.querySelector('[data-attendance-tracker]');
    if (attendee) {
        const noticeKey = `webinar-notice-${attendee.dataset.webinarId}`;
        const notice = sessionStorage.getItem(noticeKey);
        if (notice) { sessionStorage.removeItem(noticeKey); notify(notice); }

        // Keep answered poll results usable when the websocket service is unavailable.
        let refreshingResults = false;
        const refreshResults = async () => {
            const form = document.querySelector('[data-instant-poll]');
            const connection = window.Echo?.connector?.pusher?.connection;
            if (document.hidden || refreshingResults || !form || form.dataset.answered !== 'true' || connection?.state === 'connected') return;
            refreshingResults = true;
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000);
            try {
                const response = await fetch(form.dataset.resultsUrl, {headers:{'Accept':'application/json'}, signal:controller.signal});
                if (response.ok) window.renderPollResults?.(form, (await response.json()).options);
            } catch { /* Retry quietly on the next refresh. */ }
            finally { clearTimeout(timeout); refreshingResults = false; }
        };
        const resultsTimer = setInterval(refreshResults, 10000);
        window.addEventListener('pagehide', () => clearInterval(resultsTimer));

        let present = false;
        const join = () => { if (!present) { present = true; post(attendee.dataset.attendanceJoin); } };
        const leave = () => { if (present) { present = false; post(attendee.dataset.attendanceLeave, true); } };
        join();
        const timer = setInterval(() => { if (!document.hidden && present) post(attendee.dataset.attendanceHeartbeat); }, 30000);
        document.addEventListener('visibilitychange', () => document.hidden ? leave() : join());
        window.addEventListener('pagehide', leave);
        window.addEventListener('beforeunload', () => clearInterval(timer));
        if (window.Echo) window.Echo.private(`webinar.room.${attendee.dataset.webinarId}`)
            .listen('.room.updated', async event => {
                if (['controls', 'announcement'].includes(event.change) && event.state) {
                    if (typeof event.state.chat_enabled !== 'undefined') {
                        const wasChat = document.querySelector('[data-module-tab="chat"]')?.dataset.moduleEnabled === 'true';
                        window.setDashboardModuleState?.('chat', Boolean(event.state.chat_enabled));
                        if (event.state.chat_enabled && !wasChat) {
                            notify('Live chat has been enabled by the host.');
                            window.refreshLiveChat?.();
                        } else if (!event.state.chat_enabled && wasChat) {
                            notify('Live chat has been paused by the host.');
                        }
                    }

                    if (typeof event.state.polls_enabled !== 'undefined') {
                        const wasPoll = document.querySelector('[data-module-tab="poll"]')?.dataset.moduleEnabled === 'true';
                        window.setDashboardModuleState?.('poll', Boolean(event.state.polls_enabled));
                        if (event.state.polls_enabled && !wasPoll) {
                            notify('Polls have been enabled by the host.');
                            window.refreshActivePoll?.();
                        } else if (!event.state.polls_enabled && wasPoll) {
                            notify('Polls have been closed by the host.');
                        }
                    }
                    if (typeof event.state.comments_enabled !== 'undefined') {
                        window.setDashboardModuleState?.('comments', Boolean(event.state.comments_enabled));
                    }
                    if (typeof event.state.feedback_enabled !== 'undefined') {
                        window.setDashboardModuleState?.('feedback', Boolean(event.state.feedback_enabled));
                    }
                    if (typeof event.state.certificate_enabled !== 'undefined') {
                        const certLink = document.querySelector('[data-certificate-download]');
                        if (certLink) {
                            certLink.hidden = !event.state.certificate_enabled;
                            certLink.style.display = event.state.certificate_enabled ? '' : 'none';
                        }
                        notify(event.state.certificate_enabled ? 'Certificate download is now available.' : 'Certificate download has been closed.');
                    }
                    if (typeof event.state.pinned_announcement !== 'undefined') {
                        const banner = document.querySelector('#pinnedAnnouncementBanner');
                        const data = event.state.pinned_announcement || {};
                        if (banner) {
                            const isEnabled = Boolean(data.enabled && data.message);
                            banner.style.display = isEnabled ? '' : 'none';
                            banner.hidden = !isEnabled;
                            const msgNode = banner.querySelector('[data-announcement-message]');
                            if (msgNode && data.message) msgNode.textContent = data.message;
                            const btnNode = banner.querySelector('[data-announcement-btn]');
                            const btnText = banner.querySelector('[data-announcement-btn-text]');
                            if (btnNode) {
                                if (data.button_url) {
                                    btnNode.href = data.button_url;
                                    btnNode.style.display = '';
                                    btnNode.hidden = false;
                                    if (btnText) btnText.textContent = data.button_text || 'Learn more';
                                } else {
                                    btnNode.style.display = 'none';
                                    btnNode.hidden = true;
                                }
                            }
                            if (event.change === 'announcement' && isEnabled) {
                                notify('Pinned announcement updated by host.');
                            }
                        }
                    }
                    if (event.state.status) {
                        const statePill = document.querySelector('.event-state');
                        if (statePill) {
                            statePill.className = `event-state ${event.state.status}`;
                            statePill.innerHTML = `<i></i>${event.state.status.toUpperCase()}`;
                        }
                    }
                }
                if (event.change === 'status' && event.state?.status) {
                    const statePill = document.querySelector('.event-state');
                    if (statePill) {
                        statePill.className = `event-state ${event.state.status}`;
                        statePill.innerHTML = `<i></i>${event.state.status.toUpperCase()}`;
                    }
                    notify(`Webinar status changed to ${event.state.status}.`);
                }
                if (event.change === 'poll') {
                    if (event.state?.status === 'active') {
                        window.setDashboardModuleState?.('poll', true);
                    }
                    await window.refreshActivePoll?.();
                    if (event.state?.status === 'active') {
                        window.activateWebinarTab?.('poll');
                    }
                    if (['active', 'ended'].includes(event.state?.status)) {
                        notify(`${event.state.type || 'Poll'} ${event.state.status === 'active' ? 'started' : 'stopped'}.`);
                    }
                }
            })
            .listen('.attendance.updated', event => { document.querySelectorAll('[data-room-online]').forEach(node=>node.textContent=event.live_viewers); handUpdate(event); })
            .listen('.poll.updated', event => {
                const form=document.querySelector('[data-instant-poll]');
                if(!form || form.dataset.answered!=='true' || Number(form.dataset.pollId)!==Number(event.poll_id))return;
                window.renderPollResults?.(form,event.options);
            });
    }

    // Seamless Admin Live Controls submit without full page reload
    const adminControlsForm = document.querySelector('[data-admin-live-controls]');
    if (adminControlsForm) {
        adminControlsForm.addEventListener('submit', async e => {
            e.preventDefault();
            const submitBtn = adminControlsForm.querySelector('button');
            if (submitBtn) submitBtn.disabled = true;
            try {
                const formData = new FormData(adminControlsForm);
                const response = await fetch(adminControlsForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                if (response.ok) {
                    notify('Webinar controls updated.');
                } else {
                    notify('Error updating controls.');
                }
            } catch {
                notify('Failed to update controls.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // Seamless Admin Pinned Announcement submit without full page reload
    const adminAnnouncementForm = document.querySelector('[data-admin-announcement-form]');
    if (adminAnnouncementForm) {
        adminAnnouncementForm.addEventListener('submit', async e => {
            e.preventDefault();
            const submitBtn = adminAnnouncementForm.querySelector('button');
            if (submitBtn) submitBtn.disabled = true;
            try {
                const formData = new FormData(adminAnnouncementForm);
                const response = await fetch(adminAnnouncementForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                if (response.ok) {
                    notify('Pinned announcement updated.');
                } else {
                    notify('Error updating announcement.');
                }
            } catch {
                notify('Failed to update announcement.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // Seamless Admin Certificate Visibility toggle without full page reload
    document.addEventListener('submit', async e => {
        const form = e.target.closest('[data-cert-visibility-form]');
        if (!form) return;
        e.preventDefault();
        const submitBtn = form.querySelector('button');
        const input = form.querySelector('input[name="enabled"]');
        if (submitBtn) submitBtn.disabled = true;
        try {
            const formData = new FormData(form);
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf(),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });
            if (response.ok) {
                const data = await response.json();
                const nowEnabled = Boolean(data.enabled);
                if (input) input.value = nowEnabled ? 0 : 1;
                if (submitBtn) {
                    submitBtn.textContent = nowEnabled ? 'Hide' : 'Show';
                    submitBtn.className = `btn btn-sm ${nowEnabled ? 'btn-secondary' : 'btn-success'}`;
                }
                const row = form.closest('tr');
                const badge = row?.querySelector('[data-cert-badge]');
                if (badge) {
                    badge.textContent = nowEnabled ? 'VISIBLE' : 'HIDDEN';
                    badge.className = `status-badge ${nowEnabled ? 'live' : 'scheduled'}`;
                }
                notify(data.message || (nowEnabled ? 'Certificate enabled.' : 'Certificate hidden.'));
            }
        } catch {
            notify('Unable to toggle certificate visibility.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    });

    document.querySelectorAll('[data-live-viewers]').forEach(panel => {
        const render = event => panel.querySelectorAll('[data-live-viewer-count]').forEach(node => { const value=node.querySelector('[data-stat]')||node; value.textContent=event.live_viewers; const dot=node.querySelector('.live-blink-dot'); dot?.classList.toggle('active',Number(event.live_viewers)>0); dot?.classList.toggle('inactive',Number(event.live_viewers)===0); });
        (panel.dataset.webinarIds||panel.dataset.webinarId||'').split(',').filter(Boolean).forEach(id => window.Echo?.private(`webinar.manage.${id}`).listen('.attendance.updated',event => { render(event); handUpdate(event); }));
    });
    const userId=document.body.dataset.authUserId;
    if(userId && window.Echo) window.Echo.private(`App.Models.User.${userId}`).listen('.notification.created', event => {
        document.querySelectorAll('[data-notification-badge]').forEach(badge=>{badge.hidden=false;badge.textContent=String(Number(badge.textContent||0)+1)});
    });
});

