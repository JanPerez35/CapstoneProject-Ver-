<?php
namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent implements ShouldBroadcast
{
    public string $user;
    public string $content;
    public int $chatId;

    public function __construct($user, $content, $chatId)
    {
        $this->user = $user;
        $this->content = $content;
        $this->chatId = $chatId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->chatId)
        ];
    }

    public function broadcastWith()
    {
        return [
            'user' => $this->user,
            'content' => $this->content,
            'chat_id' => $this->chatId,
        ];
    }
}