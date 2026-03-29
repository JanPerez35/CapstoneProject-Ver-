<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    <!-- 🔥 Load Echo -->
    <script src="http://localhost:5173/resources/js/app.js"></script>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0" style="min-height: 650px;">

                <!-- LEFT -->
                <div class="col-md-4 border-end">
                    <div class="p-4 border-bottom">
                        <h1 class="fw-bold">Mensajes</h1>
                    </div>

                    <!-- 🔥 Conversations -->
                    <div id="conversations"></div>
                </div>

                <!-- RIGHT -->
                <div class="col-md-8 d-flex flex-column">

                    <!-- Header -->
                    <div class="p-4 border-bottom">
                        <h4 id="chat-user">Selecciona un chat</h4>
                    </div>

                    <!-- 🔥 Messages -->
                    <div id="chat-box" class="flex-grow-1 p-4 overflow-auto"></div>

                    <!-- Input -->
                    <div class="p-4 border-top">
                        <div class="input-group">
                            <input id="message"
                                   class="form-control form-control-lg"
                                   placeholder="Escribe un mensaje...">
                            <button onclick="sendMessage()" class="btn btn-success">
                                Enviar
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

<script>
let currentReceiverId = null;
const USER_ID = {{ auth()->id() }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// 🔥 Load conversations
function loadConversations() {
    fetch('/conversations')
        .then(res => res.json())
        .then(data => {
            let container = document.getElementById('conversations');
            container.innerHTML = '';

            Object.keys(data).forEach(userId => {
                let chat = data[userId];

                container.innerHTML += `
                    <div class="p-3 border-bottom cursor-pointer"
                         onclick="selectUser(${userId})">
                        <b>User ${userId}</b><br>
                        <small>${chat.message}</small>
                    </div>
                `;
            });
        });
}

// 🔥 Select user
function selectUser(userId) {
    currentReceiverId = userId;
    document.getElementById('chat-user').innerText = "User " + userId;
    loadMessages(userId);
}

// 🔥 Load messages
function loadMessages(receiverId) {
    fetch(`/chat/${receiverId}`)
        .then(res => res.json())
        .then(messages => {
            let box = document.getElementById('chat-box');
            box.innerHTML = '';

            messages.forEach(msg => {
                let isMine = msg.sender_id == USER_ID;

                box.innerHTML += `
                    <div class="mb-2 ${isMine ? 'text-end' : ''}">
                        <span class="badge bg-${isMine ? 'success' : 'secondary'}">
                            ${msg.message}
                        </span>
                    </div>
                `;
            });

            box.scrollTop = box.scrollHeight;
        });
}

// 🔥 Send message (NO polling)
function sendMessage() {
    let message = document.getElementById('message').value;

    if (!message || !currentReceiverId) return;

    fetch('/send-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            receiver_id: currentReceiverId,
            message: message
        })
    })
    .then(() => {
        document.getElementById('message').value = '';
    });
}

// 🔥 REALTIME LISTENER
window.Echo.private(`chat.${USER_ID}`)
    .listen('.MessageSent', (e) => {

        console.log('🔥 REALTIME EVENT', e);

        if (e.chat.sender_id == currentReceiverId) {
            let box = document.getElementById('chat-box');

            box.innerHTML += `
                <div class="mb-2">
                    <span class="badge bg-secondary">
                        ${e.chat.message}
                    </span>
                </div>
            `;

            box.scrollTop = box.scrollHeight;
        }

        loadConversations();
    });

// Init
loadConversations();
</script>

</x-layout>