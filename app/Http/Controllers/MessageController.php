<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Chat;
use App\Events\MessageSent;
use App\Jobs\SendUnreadMessageReminder;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class MessageController
 *
 * Handles messaging functionality within chats.
 *
 * Responsibilities:
 * - sending messages
 * - retrieving chat messages
 * - marking messages as read
 * - triggering events and background jobs
 */
class MessageController extends Controller
{
    use LogsActivity;

    /**
     * Stores a new message in a chat.
     *
     * Validates:
     * - chat existence
     * - message content
     *
     * Actions:
     * - creates message
     * - broadcasts event (real-time)
     * - logs activity
     * - dispatches reminder job if unread
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'content' => 'required|string|max:255',
        ]);

        $message = Message::create([
            'chat_id' => $validated['chat_id'],
            'user_id' => auth()->id(),
            'content' => $validated['content'],
            'status' => 'sent',
            'sent_at' => now(),
            'read_at' => null,
        ]);

        broadcast(new MessageSent($message))->toOthers();

        $this->logActivity(
            'Enviar mensaje',
            "Se envió un mensaje en el chat ID: {$validated['chat_id']}"
        );

        /**
         * Dispatch job that will send an email reminder if the message
         * remains unread after a delay.
         *
         * This prevents immediate emails or email spam and only notifies users
         * when messages are ignored for some time.
         */
        SendUnreadMessageReminder::dispatch($message->id)
            ->delay(now()->addMinutes(15));
//            ->delay(now()->addSeconds(15));

        return response()->json($message);
    }

    /**
     * Retrieves all messages for a given chat.
     *
     * Actions:
     * - marks unread messages as read (except own messages)
     * - resets unread reminder flag
     * - returns formatted message collection
     *
     * Includes:
     * - sender info
     * - read status
     * - ownership flag (isMine)
     */
    public function getMessages($chatId)
    {
        $userId = auth()->id();

        $readCount = Message::where('chat_id', $chatId)
            ->where('user_id', '!=', $userId)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'status' => 'read',
            ]);

        /**
         * Reset unread reminder flag when messages are read.
         * This allows future email reminders to be sent again
         * if new unread messages appear later.
         */
        if ($readCount > 0) {
            Chat::where('id', $chatId)->update([
                'unread_reminder_sent_at' => null,
            ]);
        }

        $messages = Message::where('chat_id', $chatId)
            ->with('user')
            ->orderBy('created_at')
            ->get()
            ->map(function ($msg) use ($userId) {
                return [
                    'id' => $msg->id,
                    'content' => $msg->content,
                    'sender_id' => $msg->user_id,
                    'isMine' => $msg->user_id == $userId,
                    'created_at' => $msg->created_at,
                    'read_at' => $msg->read_at,
                    'status' => $msg->status,
                ];
            });

        return response()->json($messages);
    }
}
