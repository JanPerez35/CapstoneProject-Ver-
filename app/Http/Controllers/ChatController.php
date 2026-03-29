<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Events\MessageSent;

public function sendMessage(Request $request)
{
    $chat = Chat::create([
        'sender_id' => auth()->id(),
        'receiver_id' => $request->receiver_id,
        'message' => $request->message
    ]);

    broadcast(new MessageSent($chat))->toOthers();

    return response()->json($chat);
}