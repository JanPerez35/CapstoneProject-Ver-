<?php

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Chat;
use Livewire\Volt\Component;

new class extends Component
{
    public array $messages = [];
    public string $message = '';
    public int $postId;
    public int $sellerId;

    public function mount($postId, $sellerId)
    {
        $this->postId = $postId;
        $this->sellerId = $sellerId;

        $this->loadMessages();
    }

    public function loadMessages()
    {
        $chat = Chat::where([
            'post_id' => $this->postId,
            'buyer_user_id' => auth()->id(),
            'seller_user_id' => $this->sellerId,
        ])->first();

        if (!$chat) {
            $this->messages = [];
            return;
        }

        $this->messages = $chat->messages()
            ->with('user')
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->map(function ($msg) {
                return [
                    'user_id' => $msg->user_id,
                    'name' => $msg->user->name,
                    'message' => $msg->content,
                ];
            })
            ->toArray();
    }

    public function addMessage()
    {
        $user = auth()->user();

        if (!$user || !$this->message) return;

        $chat = Chat::firstOrCreate([
            'post_id' => $this->postId,
            'buyer_user_id' => $user->id,
            'seller_user_id' => $this->sellerId,
        ], [
            'status' => 'active',
        ]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'content' => $this->message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->messages[] = [
            'user_id' => $message->user_id,
            'name' => $user->name,
            'message' => $message->content,
        ];

        MessageSent::dispatch($user->name, $message->content);

        $this->reset('message');
    }

    #[On('echo:messages,MessageSent')]
    public function onMessageSent($event)
    {
        $this->messages[] = [
            'user_id' => auth()->id(),
            'name' => $event['name'],
            'message' => $event['message'],
        ];
    }
}
?>

<div class="d-flex flex-column h-100">

    <!-- MESSAGES AREA -->
    <div class="flex-grow-1 overflow-auto px-4 py-3 d-flex flex-column w-100"
          id="messagesContainer">

        @if(count($messages) === 0)
            <!-- EMPTY STATE -->
            <div>
                <i class="bi bi-chat fs-1 mb-3"></i>
                <h3 class="fw-normal">No hay mensajes aún</h3>
                <p>Envía el primer mensaje para comenzar la conversación</p>
            </div>
        @else

            <!-- MESSAGE LIST -->
            <div class="d-flex flex-column gap-2 w-100">

                @foreach($messages as $msg)
                    <div class="d-flex w-100 mb-1
                        {{ $msg['user_id'] === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">

                        <div class="px-3 py-2 rounded-3 
                            {{ $msg['user_id'] === auth()->id() ? 'bg-success text-white ms-auto' : 'bg-light me-auto' }}"
                            style="max-width: 60%;">

                            <div class="small fw-semibold mb-1">
                                {{ $msg['name'] }}
                            </div>

                            <div>
                                {{ $msg['message'] }}
                            </div>
                        </div>

                    </div>
                @endforeach

            </div>
        @endif

    </div>

    <!-- INPUT -->
    <div class="p-3 border-top bg-white">
        <form wire:submit.prevent="addMessage">
            <div class="input-group">
                <input
                    type="text"
                    wire:model="message"
                    class="form-control form-control-lg border-end-0"
                    placeholder="Escribe un mensaje..."
                >
                <button class="btn btn-success px-4" type="submit">
                    <i class="bi bi-send"></i>
                </button>
            </div>
        </form>
    </div>

</div>

