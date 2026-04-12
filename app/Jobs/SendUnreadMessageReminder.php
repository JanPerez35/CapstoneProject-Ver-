<?php

namespace App\Jobs;

use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUnreadMessageReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $messageId;

    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    public function handle(EmailService $emailService): void
    {
        $message = Message::find($this->messageId);

        if (!$message) {
            return;
        }

        if ($message->read_at !== null) {
            return;
        }

        $chat = Chat::find($message->chat_id);

        if (!$chat) {
            return;
        }

        // Si ya se mandó un recordatorio para este chat, no repetir
        if ($chat->unread_reminder_sent_at !== null) {
            return;
        }

        $recipientId = null;

        if ((int) $message->user_id === (int) $chat->buyer_user_id) {
            $recipientId = $chat->seller_user_id;
        } elseif ((int) $message->user_id === (int) $chat->seller_user_id) {
            $recipientId = $chat->buyer_user_id;
        }

        if (!$recipientId) {
            return;
        }

        $recipient = User::find($recipientId);

        if (!$recipient || empty($recipient->email)) {
            return;
        }

        // Verificar que todavía haya mensajes sin leer para ese destinatario en ese chat
        $hasUnreadMessages = Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $recipientId)
            ->whereNull('read_at')
            ->exists();

        if (!$hasUnreadMessages) {
            return;
        }

        $emailService->send(
            $recipient->email,
            'Tienes mensajes sin leer en MAIKINE',
            'Hola, tienes mensajes sin leer en MAIKINE. Entra a la plataforma para revisarlos.'
        );

        $chat->unread_reminder_sent_at = now();
        $chat->save();
    }
}


