<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    \Log::info('CHANNEL HIT', [
        'user' => $user,
        'auth_user' => auth()->user(),
    ]);

    return true;
});
