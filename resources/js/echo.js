import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { renderMessage } from './messages_validation';

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher;


const appData = document.getElementById('messagesView')?.dataset || {};
const currentUserId = appData.currentUserId;


console.log('USER ID:', currentUserId);

/**
 * Echo Configuration
 *
 * Responsibilities:
 * - Establishes a WebSocket connection using Laravel Echo and Pusher
 * - Configures authentication for private channels
 * - Listens for real-time events on chat channels
 * - Handles incoming messages and updates the UI accordingly
 *
 * Features:
 * - Dynamic channel subscription based on active chat
 * - Prevents duplicate subscriptions to the same channel
 * - Graceful handling of connection states and errors
 */
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
let subscribedChannel = null;

/** * Subscribes to a chat channel for real-time messaging
 *
 * @param {string} chatId - The ID of the chat to subscribe to
 *
 * Responsibilities:
 * - Manages dynamic subscription to private chat channels based on the active conversation
 * - Prevents redundant subscriptions to the same channel
 * - Handles incoming message events and updates the UI in real-time
 * - Ensures proper cleanup of previous channel subscriptions when switching chats
 *
 * Features:
 * - Listens for '.MessageSent' events and renders new messages in the UI
 * - Marks messages as read when they arrive in the active chat
 * - Logs connection status and errors for debugging purposes
 */
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

                const activeChatId = String(document.getElementById('messagesView')?.dataset.chatId || '');
                const incomingChatId = String(data.chat_id || '');

                if (activeChatId === incomingChatId) {
                    fetch(`/messages/${incomingChatId}`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json'
                        }
                    }).catch(error => {
                        console.error('Error marcando mensajes como leídos:', error);
                    });
                }

                if (document.querySelector(`[data-message-id="${data.id}"]`)) {
                    return;
                }

                renderMessage({
                    id: data.id,
                    message: data.content,
                    createdAt: data.created_at,
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
// Expose the subscribeToChat function globally for use in other modules
window.subscribeToChat = subscribeToChat;
