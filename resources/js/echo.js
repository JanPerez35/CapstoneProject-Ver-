import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { renderMessage } from './messages_validation';


window.Pusher = Pusher;


const appData = document.getElementById('messagesView')?.dataset || {};
const currentUserId = appData.currentUserId;


console.log('USER ID:', currentUserId);


window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,


    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,


    forceTLS: true,
    enabledTransports: ['wss' , 'ws'],


    authEndpoint: '/broadcasting/auth',
    withCredentials: true,
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
    },
});


let currentChannel = null;
let lastMessageId = null;
let subscribedChannel = null;

function subscribeToChat(chatId) {
    if (!chatId) return;

    const newChannel = `chat.${chatId}`;
    console.log('Cambiando a chat:', newChannel);

    if (subscribedChannel === newChannel) {
        console.log('YA SUSCRITO, NO REPITE:', newChannel);
        return;
    }

    if (currentChannel) {
        console.log('LEAVING:', currentChannel);
        window.Echo.leave(currentChannel);
    }

    currentChannel = newChannel;

    const subscribe = () => {
        console.log('Suscribiendo a:', currentChannel);

        window.Echo.private(currentChannel)
            .subscribed(() => {
                console.log('SUSCRITO A:', currentChannel);
                subscribedChannel = currentChannel;
            })
            .error((err) => {
                console.error('ERROR DE CANAL:', err);
            })
            .listen('.MessageSent', (data) => {
                console.log('EVENTO:', data);

                if (document.querySelector(`[data-message-id="${data.id}"]`)) {
                    return;
                } 

                renderMessage({
                    id: data.id,
                    message: data.content,
                    time: new Date(data.created_at).toLocaleTimeString([], {
                        hour: '2-digit', 
                        minute: '2-digit' 
                    }),
                    senderId: data.sender_id,
                    conversationId: data.chat_id,
                    isMine: String(data.sender_id) === String(currentUserId)
                });
            });
    };

    const state = window.Echo.connector.pusher.connection.state;

    if (state === 'connected') {
        subscribe();
    } else {
        window.Echo.connector.pusher.connection.bind('connected', () => {
            subscribe();
            window.Echo.connector.pusher.connection.unbind('connected');
        });
    }
}

window.subscribeToChat = subscribeToChat;