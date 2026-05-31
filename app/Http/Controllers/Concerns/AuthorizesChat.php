<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Chat;

trait AuthorizesChat
{
    /**
     * Ensure unauthorized users cannot access the chat.
     */
    private function authorizeChat(Chat $chat)
    {
        $userId = auth()->id();

        if (
            $chat->buyer_user_id != $userId &&
            $chat->seller_user_id != $userId
        ) {
            abort(404);        }
    }
}
