<?php

use App\Events\MessageSent;
use Livewire\Volt\Component;
use App\Models\Message;

new class extends Component
{
    public ?string $chatId = null;
    public array $messages = [];
    public string $message = '';

    public function mount()
    {
        $this->chatId = request('chat_id');
    }

    public function addMessage()
    {
        $message = Message::create([
            'chat_id' => $this->chatId,
            'user_id' => auth()->id(),
            'content' => $this->message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        broadcast(new MessageSent(
            auth()->user()->name,
            $message->content,
            $this->chatId
        ))->toOthers();

        // agregar al frontend
        $this->messages[] = [
            'name' => auth()->user()->name,
            'message' => $message->content
        ];

        $this->reset('message');
    }
}
?>

<div>

    <div class="flex-grow-1 p-4 overflow-auto" id="chatMessagesContainer">

        @forelse($messages as $msg)
            <div class="d-flex justify-content-end mb-3">
                <div class="bg-success text-white px-3 py-2 rounded-4 shadow-sm">
                    <div>{{ $msg['message'] }}</div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted">
                No hay mensajes aún
            </div>
        @endforelse

    </div>

    <div class="p-4 border-top">
        <form wire:submit.prevent="addMessage" class="input-group">
            <input
                type="text"
                wire:model="message"
                class="form-control form-control-lg border-end-0"
                placeholder="Escribe un mensaje..."
            >

            <button class="btn btn-success px-4" type="submit">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </div>

</div> 