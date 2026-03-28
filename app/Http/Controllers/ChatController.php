<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function fetchMessages($receiverId)
    {
        $userId = auth()->id();

        $messages = Chat::where(function($q) use ($userId, $receiverId) {
            $q->where('sender_id', $userId)
              ->where('receiver_id', $receiverId);
        })->orWhere(function($q) use ($userId, $receiverId) {
            $q->where('sender_id', $receiverId)
              ->where('receiver_id', $userId);
        })
        ->with('sender')
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $chat = Chat::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        return response()->json($chat);
    }

    // 👇 ADD IT RIGHT HERE
    public function getConversations()
    {
        $userId = auth()->id();

        $chats = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->latest()
            ->get();

        $conversations = [];

        foreach ($chats as $chat) {
            $otherUserId = $chat->sender_id == $userId
                ? $chat->receiver_id
                : $chat->sender_id;

            if (!isset($conversations[$otherUserId])) {
                $conversations[$otherUserId] = $chat;
            }
        }

        return response()->json($conversations);
    }
}