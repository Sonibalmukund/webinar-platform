html, body.event-dashboard-body{overflow-x:hidden!important;max-width:100vw}
.event-dashboard{overflow-x:hidden!important;max-width:100vw}
.event-dashboard .event-layout{overflow-x:hidden!important;max-width:100vw}
.event-dashboard .event-side{overflow-x:hidden!important}
.event-dashboard .event-main{scrollbar-width:none;-ms-overflow-style:none;scrollbar-gutter:auto;overflow-x:hidden!important}
.event-dashboard .event-main::-webkit-scrollbar{display:none;width:0}
.event-user .event-profile-menu button.dropdown-item{width:100%;height:auto;min-height:44px;display:flex;justify-content:flex-start;border:0;text-align:left;border-radius:8px;background:transparent}
.event-user .event-profile-menu button.dropdown-item:hover{background:#ffffff0c;transform:none}
.raised-hand-icon{width:22px;height:22px;display:inline-block;flex-shrink:0}
.quick-grid .raised-hand-icon{color:var(--event-primary)}
.quick-grid .active .raised-hand-icon{color:#fbbf24}
.poll-choice:focus-within{outline:2px solid #a78bfa;outline-offset:2px}
[data-instant-poll][data-answered="true"] .poll-radio{display:none}
[data-poll-result][hidden]{display:none!important}
@media(min-width:992px){
 body.event-dashboard-body{height:100dvh;overflow-y:hidden;overflow-x:hidden!important}
 .event-dashboard{height:100dvh;min-height:0;overflow-y:hidden;overflow-x:hidden!important}
 .event-dashboard .event-layout{height:calc(100dvh - 80px);min-height:0;grid-template-columns:minmax(0,2fr) minmax(380px,1fr)}
 .event-dashboard .event-main{height:100%;overflow-y:auto;overflow-x:hidden!important;overscroll-behavior:contain;scrollbar-gutter:auto}
 .event-dashboard .event-side{height:100%;min-height:0;overflow-y:auto;overflow-x:hidden!important;overscroll-behavior:contain;scrollbar-width:thin;scrollbar-color:#34415b transparent}
 .event-dashboard .event-side-heading,.event-dashboard .module-tabs,.event-dashboard .side-summary{flex-shrink:0}
 .event-dashboard .module-panel.active{display:block;flex:none;min-height:360px;height:auto;overflow-y:visible;overflow-x:hidden!important;padding:22px 20px}
 .event-dashboard .module-panel[data-module-panel=chat].active{display:flex;height:460px;min-height:360px}
 .event-dashboard .module-scroll{flex:1;min-height:0;max-height:none;overflow-y:auto;overflow-x:hidden!important}
 .event-dashboard .event-footer{display:none}
}
.event-dashboard .poll-heading{flex-wrap:wrap;gap:8px}
.event-dashboard .poll-question{font-size:1rem;line-height:1.55;margin-bottom:8px}
.event-dashboard .poll-help{font-size:.76rem;line-height:1.5;color:#a4b1c7}
.event-dashboard .poll-choice.selectable{margin:12px 0;padding:15px;border-radius:12px;font-size:.85rem;line-height:1.5}
.event-dashboard .poll-choice .d-flex{align-items:flex-start;gap:12px}
.event-dashboard .poll-choice span{min-width:0;flex-wrap:wrap;gap:8px}
.event-dashboard .poll-radio{flex:0 0 18px;margin-top:2px}
.event-dashboard .poll-choice [data-answer-state]{font-size:.65rem;margin-left:0;color:#c4b5fd}
.event-dashboard .poll-choice b[data-poll-result]{flex-shrink:0;font-size:.8rem}
.event-dashboard .poll-choice.locked{opacity:1}
.event-dashboard .poll-choice.selected{border-color:#a78bfa;background:#292044;box-shadow:inset 3px 0 #a78bfa}
.event-dashboard .poll-choice.quiz-correct [data-answer-state]{color:#166534}
.event-dashboard .poll-choice.quiz-incorrect [data-answer-state]{color:#991b1b}
.event-dashboard .poll-track{height:7px;margin-top:12px}
.event-dashboard .side-summary{padding:22px 20px;overflow:visible}
.event-dashboard .side-summary h3{font-size:.9rem;color:#f1f5f9}
.event-dashboard .quick-grid{gap:12px;overflow:visible}
.event-dashboard .quick-grid>a,.event-dashboard .quick-grid>button,.event-dashboard .quick-grid>.quick-disabled{min-height:86px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:7px;padding:14px 10px;font-size:.78rem;border-radius:12px}
.event-dashboard .participant-list{max-height:240px;overflow-y:auto;overflow-x:hidden!important;scrollbar-width:thin;padding-right:4px}
.event-dashboard .participant-list .d-flex{min-width:0;width:100%;overflow:hidden}
.event-dashboard .participant-list strong{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.event-dashboard .participant-list .avatar{flex:0 0 34px;width:34px;height:34px;font-size:.7rem;background:#148d9d;color:#fff;position:relative;border-radius:50%;display:inline-grid;place-items:center}
.event-dashboard .participant-list .avatar::after{content:'';position:absolute;bottom:0;right:0;width:9px;height:9px;border-radius:50%;background:#22c55e;border:2px solid #111a2e}
.event-dashboard .participant-list [data-participant-hand]{flex-shrink:0;transform-origin:center}
.event-dashboard .participant-list [data-participant-hand]:not([hidden]){display:inline-flex;align-items:center;color:#fbbf24!important;animation:pulseHand 1.5s infinite}
@keyframes pulseHand{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}
@media(max-width:991px){
 body.event-dashboard-body{height:auto;overflow-y:auto;overflow-x:hidden!important}
 .event-dashboard .event-layout{height:auto;min-height:0;overflow-x:hidden!important}
 .event-dashboard .event-main,.event-dashboard .event-side{height:auto;overflow-y:visible;overflow-x:hidden!important}
 .event-dashboard .module-panel.active{height:auto;min-height:340px;overflow-y:visible;overflow-x:hidden!important;padding:20px 16px}
 .event-dashboard .module-scroll{max-height:380px;overflow-y:auto;overflow-x:hidden!important}
 .session-resources-grid{grid-template-columns:1fr}
}
