<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Broadcast channels
 * Define the channels that your application supports broadcasting on. The given
 * callback must return a boolean indicating whether the authenticated user
 * is authorized to listen on the channel. The callback receives the authenticated * user as an argument.
 */
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {

    $chat = \App\Models\Chat::find($chatId);
    
    if (!$chat) {
        return false;
    }
    
    return $user->id === $chat->seller_user_id || $user->id === $chat->buyer_user_id;
});
