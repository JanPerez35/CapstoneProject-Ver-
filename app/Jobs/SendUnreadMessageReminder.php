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

/**
 * Class SendUnreadMessageReminder
 *
 * Queued job responsible for sending an email reminder when a chat
 * message remains unread for a certain period of time.
 *
 * Responsibilities:
 * - load the original message by ID
 * - verify the message still exists and is unread
 * - determine the correct recipient in the chat
 * - ensure the recipient still has unread messages in that chat
 * - avoid sending duplicate reminders for the same chat
 * - send the reminder email and mark the chat as reminded
 */
class SendUnreadMessageReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * ID of the message that triggered this reminder job.
     *
     * @var int
     */
    public int $messageId;

    /**
     * Create a new job instance.
     *
     * @param int $messageId ID of the message to check for unread reminder logic
     */
    public function __construct(int $messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     *
     * Workflow:
     * - find the original message
     * - stop if it no longer exists or was already read
     * - load the related chat
     * - stop if a reminder was already sent for this chat
     * - identify the recipient as the other participant in the chat
     * - stop if the recipient is invalid or has no email
     * - confirm unread messages still exist for that recipient
     * - send email reminder
     * - record the reminder timestamp on the chat
     *
     * @param EmailService $emailService Service used to send reminder emails
     * @return void
     */
    public function handle(EmailService $emailService): void
    {
        // Retrieve the original message that triggered the job
        $message = Message::find($this->messageId);

        // Stop if message no longer exists
        if (!$message) {
            return;
        }

        // Stop if the message has already been read
        if ($message->read_at !== null) {
            return;
        }

        // Load the chat associated with the unread message
        $chat = Chat::find($message->chat_id);

        // Stop if the related chat no longer exists
        if (!$chat) {
            return;
        }

        // Prevent duplicate reminders for the same chat
        if ($chat->unread_reminder_sent_at !== null) {
            return;
        }

        /**
         * Determine the recipient:
         * if the sender is the buyer, recipient is the seller
         * if the sender is the seller, recipient is the buyer
         */
        $recipientId = null;

        if ((int) $message->user_id === (int) $chat->buyer_user_id) {
            $recipientId = $chat->seller_user_id;
        } elseif ((int) $message->user_id === (int) $chat->seller_user_id) {
            $recipientId = $chat->buyer_user_id;
        }

        // Stop if no valid recipient could be determined
        if (!$recipientId) {
            return;
        }

        // Retrieve the recipient user record
        $recipient = User::find($recipientId);

        // Stop if recipient does not exist or does not have an email
        if (!$recipient || empty($recipient->email)) {
            return;
        }

        /**
         * Confirm that unread messages still exist for this recipient in the same chat.
         * This prevents sending reminders when the chat has already been read
         * before the queued job executes.
         */
        $hasUnreadMessages = Message::where('chat_id', $chat->id)
            ->where('user_id', '!=', $recipientId)
            ->whereNull('read_at')
            ->exists();

        // Stop if there are no unread messages left
        if (!$hasUnreadMessages) {
            return;
        }

        // Send unread message reminder email
        $emailService->send(
            $recipient->email,
            'Tienes mensajes sin leer en MAIKINE',
            'Hola, tienes mensajes sin leer en MAIKINE. Entra a la plataforma para revisarlos.'
        );

        // Record that a reminder has already been sent for this chat
        $chat->unread_reminder_sent_at = now();
        $chat->save();
    }
}


