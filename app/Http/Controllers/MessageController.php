<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\Chat;
use App\Events\MessageSent;
use App\Jobs\SendUnreadMessageReminder;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'content' => 'required|string|max:255',
        ]);

        $message = Message::create([
            'chat_id' => $request->chat_id,
            'user_id' => auth()->id(),
            'content' => $request->content,
            'status' => 'sent',
            'sent_at' => now(),
            'read_at' => null,
        ]);

        /**
         * Dispatch job that will send an email reminder if the message
         * remains unread after a delay.
         *
         * This prevents immediate emails or email spam and only notifies users
         * when messages are ignored for some time.
         */
        SendUnreadMessageReminder::dispatch($message->id)
//            ->delay(now()->addMinutes(15));
            ->delay(now()->addSeconds(15));

        return response()->json($message);
    }

    public function getMessages($chatId)
    {
        $userId = auth()->id();

        Message::where('chat_id', $chatId)
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
        Chat::where('id', $chatId)->update([
            'unread_reminder_sent_at' => null,
        ]);

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
