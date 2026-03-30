<?php

use App\Events\MessageSent;
use Livewire\Volt\Component;

new class extends Component
{
    public array $messages = [];
    public string $message = '';

    public function addMessage()
    {
        MessageSent::dispatch(auth()->user()->name, $this->message);

        $this->reset('message');
    }

    #[On('echo:messages,MessageSent')]
    public function onMessageSent($event)
    {
        $this->messages[] = [
            'name' => $event['name'],
            'message' => $event['text'],
        ];
    }
}
?>

<div x-data="{ open: true }">
    <div :class="{'-translate-y-0': open, 'translate-y-full': !open}" class="fixed transition-all duration-300 transform bottom-10 right-12 h-60 w-80">
        
        <div class="mb-2">
            <button @click="open = !open" type="button"
                class="w-full text-start py-2 px-2.5 text-sm text-white rounded-lg bg-indigo-600 hover:bg-indigo-400">
                Chat
            </button>
        </div>

        <div class="w-full h-full bg-white border rounded overflow-auto flex flex-col px-2 py-4">
            
            <!-- Messages -->
            <div x-ref="chatBox" class="flex-1 p-4 text-sm flex flex-col gap-y-1">
                @foreach($messages as $message)
                    <div>
                        <span class="text-indigo-600">{{ $message['name'] }}:</span>
                        <span>{{ $message['message'] }}</span>
                    </div>
                @endforeach
            </div>

            <!-- Input -->
            <div>
                <form wire:submit.prevent="addMessage" class="flex gap-2">
                    
                    <input 
                        type="text"
                        wire:model="message"
                        x-ref="messageInput"
                        name="message"
                        id="message"
                        class="block w-full border rounded px-2 py-1"
                        placeholder="Type a message..."
                    />

                    <button type="submit"
                        class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-400">
                        Send
                    </button>

                </form>
            </div>

        </div>
    </div>
</div>