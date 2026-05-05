<?php
namespace App\Events;


use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Message;

/**
 * Class MessageSent
 *
 * Event responsible for broadcasting a new message in real-time.
 *
 * Responsibilities:
 * - broadcasting newly created messages to private chat channels
 * - defining the broadcast channel based on chat ID
 * - formatting the payload sent to the frontend
 * - naming the broadcast event
 */
class MessageSent implements ShouldBroadcastNow
{
    /**
     * The message instance being broadcasted.
     *
     * Contains all relevant message data such as:
     * - content
     * - sender
     * - chat reference
     */
    public Message $message;

    /**
     * Create a new event instance.
     *
     * @param Message $message The message to broadcast
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Defines the private channel where the event will be broadcast.
     *
     * Channel format:
     * - chat.{chat_id}
     *
     * Ensures that only authorized users subscribed to the chat
     * can receive the event.
     */
    public function broadcastOn(): PrivateChannel
    {
            return new PrivateChannel('chat.' . $this->message->chat_id);
    }

    /**
     * Defines the data payload sent with the broadcast event.
     *
     * Includes:
     * - message ID
     * - content
     * - sender ID
     * - chat ID
     * - formatted timestamp
     */
    public function broadcastWith()
    {
        return [
        'id' => $this->message->id,
        'content' => $this->message->content,
        'sender_id' => $this->message->user_id,
        'chat_id' => $this->message->chat_id,
        'created_at' => $this->message->created_at->toDateTimeString(),
        ];
    }
    
    /**
     * Defines the custom broadcast event name.
     *
     * This is the name used on the frontend listener.
     */
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}