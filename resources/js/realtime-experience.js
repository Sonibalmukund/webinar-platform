const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
const post = (url, keepalive = false) => fetch(url, {method:'POST', keepalive, credentials:'same-origin', headers:{'X-CSRF-TOKEN':csrf(),'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}}).catch(()=>null);
document.addEventListener('DOMContentLoaded', () => {
    const flash=document.querySelector('[data-app-flash]');
    if(flash){const toast=document.querySelector('#appToast');if(toast&&window.bootstrap)bootstrap.Toast.getOrCreateInstance(toast,{delay:4200}).show();}
    const publicWebinar=document.querySelector('[data-public-webinar]');
    if(publicWebinar && window.Echo) window.Echo.channel(`webinar.public.${publicWebinar.dataset.publicWebinar}`).listen('.room.updated',event=>{if(event.change==='status')window.location.reload();});
    const attendee = document.querySelector('[data-attendance-tracker]');
    if (attendee) {
        let present = false;
        const join = () => { if (!present) { present = true; post(attendee.dataset.attendanceJoin); } };
        const leave = () => { if (present) { present = false; post(attendee.dataset.attendanceLeave, true); } };
        join();
        const timer = setInterval(() => { if (!document.hidden && present) post(attendee.dataset.attendanceHeartbeat); }, 30000);
        document.addEventListener('visibilitychange', () => document.hidden ? leave() : join());
        window.addEventListener('pagehide', leave);
        window.addEventListener('beforeunload', () => clearInterval(timer));
        if (window.Echo) window.Echo.private(`webinar.room.${attendee.dataset.webinarId}`)
            .listen('.room.updated', event => { if (['status','controls','poll'].includes(event.change))window.location.reload(); })
            .listen('.attendance.updated', event => document.querySelectorAll('[data-room-online]').forEach(node=>node.textContent=event.live_viewers))
            .listen('.poll.updated', event => {
                const form=document.querySelector('[data-instant-poll]'); if(!form)return;
                const total=Math.max(1,event.options.reduce((sum,option)=>sum+Number(option.count),0));
                event.options.forEach(option=>{const input=form.querySelector(`input[value="${option.id}"]`),choice=input?.closest('.poll-choice');if(!choice)return;const percent=Math.round(Number(option.count)/total*100);choice.querySelector('b').textContent=`${percent}%`;choice.querySelector('.poll-track i').style.width=`${percent}%`;});
            });
    }
    document.querySelectorAll('[data-live-viewers]').forEach(panel => {
        const render = event => panel.querySelectorAll('[data-live-viewer-count]').forEach(node => { const value=node.querySelector('[data-stat]')||node; value.textContent=event.live_viewers; const dot=node.querySelector('.live-blink-dot'); dot?.classList.toggle('active',Number(event.live_viewers)>0); dot?.classList.toggle('inactive',Number(event.live_viewers)===0); });
        (panel.dataset.webinarIds||panel.dataset.webinarId||'').split(',').filter(Boolean).forEach(id => window.Echo?.private(`webinar.manage.${id}`).listen('.attendance.updated',render));
    });
    const userId=document.body.dataset.authUserId;
    if(userId && window.Echo) window.Echo.private(`App.Models.User.${userId}`).listen('.notification.created', event => {
        document.querySelectorAll('[data-notification-badge]').forEach(badge=>{badge.hidden=false;badge.textContent=String(Number(badge.textContent||0)+1)});
    });
});
