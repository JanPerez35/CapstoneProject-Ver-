<?php
namespace App\Events;


use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use App\Models\Message;


class MessageSent implements ShouldBroadcastNow
{
    public Message $message;




    public function __construct(Message $message)
    {
        $this->message = $message;
    }


    public function broadcastOn(): PrivateChannel
    {
            return new PrivateChannel('chat.' . $this->message->chat_id);
    }


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
    public function broadcastAs()
    {
        return 'MessageSent';
    }
}