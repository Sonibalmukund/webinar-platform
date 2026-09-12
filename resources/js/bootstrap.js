import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
const isHttps = window.location.protocol === 'https:' || (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https';
const port = isHttps ? 443 : (Number(import.meta.env.VITE_REVERB_PORT) || 8080);

const envHost = import.meta.env.VITE_REVERB_HOST;
const host = (envHost && envHost !== '127.0.0.1' && envHost !== 'localhost') ? envHost : window.location.hostname;
const key = document.querySelector('meta[name="reverb-key"]')?.content || import.meta.env.VITE_REVERB_APP_KEY || 'xdp8ocwlczmqoe4bgark';

window.Echo = new Echo({
    broadcaster: 'reverb',
    authEndpoint: document.querySelector('meta[name="broadcast-auth-url"]')?.content || '/broadcasting/auth',
    key: key,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
});
