import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Pusher = Pusher;
const isHttps = (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https' || window.location.protocol === 'https:';
const defaultPort = isHttps ? 443 : 8080;
const port = Number(import.meta.env.VITE_REVERB_PORT) || defaultPort;

window.Echo = new Echo({
    broadcaster: 'reverb',
    authEndpoint: document.querySelector('meta[name="broadcast-auth-url"]')?.content || '/broadcasting/auth',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'webinar-local-key',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: port,
    wssPort: port,
    forceTLS: isHttps,
    enabledTransports: ['ws', 'wss'],
});
