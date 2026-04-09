<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;

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
        ]);

        // broadcast(new MessageSent(
        //     $message->user->name,
        //     $message->content,
        //     $message->chat_id
        // ))->toOthers();

        return response()->json($message);
    }
}