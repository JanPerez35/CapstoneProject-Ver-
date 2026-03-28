<!DOCTYPE html>
<html>
<head>
    <title>Inbox</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        body { font-family: Arial; margin: 0; }

        .container {
            display: flex;
            height: 100vh;
        }

        .conversations {
            width: 30%;
            border-right: 1px solid #ccc;
            overflow-y: auto;
            background: #f9f9f9;
        }

        .chat {
            width: 70%;
            display: flex;
            flex-direction: column;
        }

        .conversation-item {
            padding: 12px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
        }

        .conversation-item:hover {
            background: #eee;
        }

        .active {
            background: #ddd;
        }

        .chat-box {
            flex: 1;
            padding: 10px;
            overflow-y: auto;
        }

        .message {
            margin: 5px 0;
        }

        .sent {
            text-align: right;
        }

        .bubble {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 12px;
            background: #e4e6eb;
        }

        .sent .bubble {
            background: #0084ff;
            color: white;
        }

        .input-box {
            display: flex;
            border-top: 1px solid #ccc;
        }

        .input-box input {
            flex: 1;
            padding: 10px;
            border: none;
        }

        .input-box button {
            padding: 10px 15px;
            border: none;
            background: #0084ff;
            color: white;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- LEFT SIDE -->
    <div class="conversations" id="conversations"></div>

    <!-- RIGHT SIDE -->
    <div class="chat">

        <div class="chat-box" id="chat-box">
            <p>Select a conversation</p>
        </div>

        <div class="input-box">
            <input type="text" id="message" placeholder="Type a message...">
            <button onclick="sendMessage()">Send</button>
        </div>

    </div>

</div>

<script>
let currentReceiverId = null;
const USER_ID = {{ auth()->id() }};
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Load conversations
function loadConversations() {
    fetch('/conversations')
        .then(res => res.json())
        .then(data => {
            let container = document.getElementById('conversations');
            container.innerHTML = '';

            Object.keys(data).forEach(userId => {
                let chat = data[userId];

                container.innerHTML += `
                    <div class="conversation-item"
                         onclick="selectUser(${userId}, this)">
                        <b>User ${userId}</b><br>
                        <small>${chat.message}</small>
                    </div>
                `;
            });
        });
}

// Select user
function selectUser(userId, element) {
    currentReceiverId = userId;

    // Highlight active
    document.querySelectorAll('.conversation-item')
        .forEach(el => el.classList.remove('active'));

    element.classList.add('active');

    loadMessages(userId);
}

// Load messages
function loadMessages(receiverId) {
    fetch(`/chat/${receiverId}`)
        .then(res => res.json())
        .then(messages => {
            let box = document.getElementById('chat-box');
            box.innerHTML = '';

            messages.forEach(msg => {
                let isMine = msg.sender_id == USER_ID;

                box.innerHTML += `
                    <div class="message ${isMine ? 'sent' : ''}">
                        <div class="bubble">${msg.message}</div>
                    </div>
                `;
            });

            box.scrollTop = box.scrollHeight;
        });
}

// Send message
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
        loadMessages(currentReceiverId);
        loadConversations();
    });
}

// Auto refresh (like inbox)
setInterval(() => {
    loadConversations();
    if (currentReceiverId) {
        loadMessages(currentReceiverId);
    }
}, 3000);

// Initial load
loadConversations();
</script>

</body>
</html>