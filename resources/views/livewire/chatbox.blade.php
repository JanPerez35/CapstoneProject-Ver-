<?php

use App\Events\MessageSent;
use Livewire\Volt\Component;
use App\Models\Message;

/**
 * Livewire Volt chat component.
 *
 * This component handles the lightweight real-time chat behavior for a selected chat.
 * Responsible:
 * - Reading the active chat_id from the URL
 * - Storing the temporary message input value
 * - Creating a new Message record in the database
 * - Broadcasting the message to other connected users
 * - Immediately adding the sent message to the current user's local message list
 */
new class extends Component
{
    /**
     * Stores the active chat/conversation id.
     *
     * The value is nullable because the component may load before a chat is selected.
     * It is filled during mount() shown below, using the chat_id query parameter from the request.
     */
    public ?string $chatId = null;

    /**
     * Stores messages displayed by this component.
     *
     * This array is updated locally after the current user sends a message.
     * Each message item contains: name: sender name & message: message content
     */
    public array $messages = [];

    /**
     * Stores the current text typed in the message input.
     * The input field is connected to this property through wire:model="message".
     */
    public string $message = '';

    /**
     * Initializes the component when it loads.
     *
     * The component reads chat_id from the current request so new messages can be
     * connected to the correct chat conversation in the database.
     */
    public function mount()
    {
        $this->chatId = request('chat_id');
    }

    /**
     * Creates and sends a new chat message.
     *
     * This method runs when the user submits the message form
     * from the front-end.
     * Process:
     * 1. Creates a new Message record for the active chat.
     * 2. Assigns the authenticated user as the sender.
     * 3. Saves the message content, status, and sent timestamp.
     * 4. Broadcasts the MessageSent event to other connected user.
     * 5. Adds the message to the local messages array so the sender sees it immediately.
     * 6. Clears the input field after successful submission.
     */
    public function addMessage()
    {
        $message = Message::create([
            'chat_id' => $this->chatId,
            'user_id' => auth()->id(),
            'content' => $this->message,
            'status' => 'sent',
            'sent_at' => now(),
        ]);


        /**
         * Broadcasts the message to other user listening to this chat.
         *
         * toOthers() prevents the sender from receiving a duplicate broadcast
         * because the sender already sees the message through the local messages array.
         */
        broadcast(new MessageSent(
            auth()->user()->name,
            $message->content,
            $this->chatId
        ))->toOthers();

        /**
         * Adds the newly sent message to the current user's local message list.
         * This gives immediate visual feedback without waiting for a broadcast response.
         */
        $this->messages[] = [
            'name' => auth()->user()->name,
            'message' => $message->content
        ];


        /**
         * Clears the text input after the message is sent.
         * Because the input uses wire:model="message", resetting this property
         * also clears the visible input field.
         */
        $this->reset('message');
    }
}
?>

<div>

    {{--Main message display container.
       This area shows all messages currently stored in the component's messages array.
       The overflow-auto class allows the message list to scroll when the content grows--}}
    <div class="flex-grow-1 p-4 overflow-auto" id="chatMessagesContainer">

        {{--Loops through locally available messages.
           Each message is rendered as a right-aligned bubble, which represents
           messages sent by the current user in this component--}}
        @forelse($messages as $msg)
            <div class="d-flex justify-content-end mb-3">

                {{--Message bubble. Uses success styling to match the chat theme
                 and visually separate the message content from the background--}}
                <div class="bg-success text-white px-3 py-2 rounded-4 shadow-sm">
                    <div>{{ $msg['message'] }}</div>
                </div>
            </div>

        {{--Empty state shown when the coponent has no messages to display yet--}}
        @empty
            <div class="text-center text-muted">
                No hay mensajes aún
            </div>
        @endforelse

    </div>

    {{--Message input area--}}
    <div class="p-4 border-top">

        {{--Livewire form submission.
          wire:submit.prevent="addMessage" stops the normal page refresh and calls
          the addMessage() method directly through Livewire--}}
        <form wire:submit.prevent="addMessage" class="input-group">
            @csrf

            {{--Message input field.
               wire:model="message" keeps this input synchronized with the public
               $message property in the Volt component--}}
            <input
                type="text"
                wire:model="message"
                class="form-control form-control-lg border-end-0"
                placeholder="Escribe un mensaje..."
            >

            {{--Submit button. Sends the current message value to addMessage()--}}
            <button class="btn btn-success px-4" type="submit">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </div>
</div>
