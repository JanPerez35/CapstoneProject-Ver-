<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use SerializesModels;

    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('chat.' . $this->chat->receiver_id);
    }

    public function broadcastAs()
    {
        return 'MessageSent';
    }
}