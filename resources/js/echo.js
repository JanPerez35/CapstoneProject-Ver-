import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const chatId = document.getElementById('messagesView')?.dataset.chatId;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
});

if (chatId) {
    window.Echo.private(`chat.${chatId}`)
        .listen('MessageSent', (e) => {
            console.log('Mensaje recibido:', e);

            renderMessage({
                message: e.content,
                time: new Date().toLocaleTimeString()
            });
        });
}