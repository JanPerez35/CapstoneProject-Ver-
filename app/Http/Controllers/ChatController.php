<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Chat;
use App\Models\Post;
use App\Models\Message;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class ChatController
 *
 * Handles chat-related functionality within the application.
 *
 * Responsibilities:
 * - listing user chats
 * - opening or creating chats
 * - redirecting to specific chats
 */
class ChatController extends Controller
{
    use LogsActivity;

    /**
     * Displays the list of chats for the authenticated user.
     *
     * Includes:
     * - buyer and seller info
     * - related post
     * - last message timestamp
     * - unread message count
     *
     * Optionally selects an active chat using:
     * - chat_id (query param)
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $chats = Chat::with(['buyer', 'seller', 'post'])
            ->where('buyer_user_id', $userId)
            ->orWhere('seller_user_id', $userId)
            ->withMax('messages', 'created_at')
            ->withcount([
                'messages as unread_count' => function ($query) use ($userId) {
                    $query->whereNull('read_at')
                          ->where('user_id', '!=', $userId);
                }
            ])
            ->orderByDesc('messages_max_created_at')
            ->get();

        $selectedChat = null;

        if ($request->chat_id) {
            $selectedChat = $chats->firstWhere('id', $request->chat_id);
        }

        return view('my_messages', compact('chats', 'selectedChat'));
    }


    /**
     * Creates or retrieves an existing chat based on:
     * - post_id
     * - buyer_user_id (auth user)
     * - seller_user_id
     *
     * Logs activity if the chat is newly created.
     *
     * Redirects to messages view with:
     * - selected chat
     * - return route reference
     */
    public function openOrCreate(Request $request)
    {
        $userId = auth()->id();

        $postId = $request->post_id;
        $sellerId = $request->seller_id;

        if (auth()->id() === $request->seller_id) {
            abort(403, 'No puedes crear un chat contigo mismo');
        }

        $chat = Chat::firstOrCreate(
            [
                'post_id' => $postId,
                'buyer_user_id' => $userId,
                'seller_user_id' => $sellerId,
            ],
            [
                'status' => 'active',
            ]
        );

        if ($chat->wasRecentlyCreated) {
            $this->logActivity(
                'Crear chat',
                "Se inició un chat (ID: {$chat->id}) con el vendedor ID: {$sellerId} sobre la publicación ID: {$postId}"
            );
        }

        return redirect()->route('my_messages', [
            'chat_id' => $chat->id,
            'post_id' => $postId,
            'return_to' => route('kinemarket'),
        ]);
    }


    /**
     * Redirects to a specific chat by ID.
     *
     * Ensures the authenticated user:
     * - is either the buyer or seller of the chat
     *
     * Prevents unauthorized access.
     */
    public function show($chatId)
    {
        $userId = auth()->id();

        $chat = Chat::with(['buyer', 'seller', 'post'])
            ->where('id', $chatId)
            ->where(function ($q) use ($userId) {
                $q->where('buyer_user_id', $userId)
                  ->orWhere('seller_user_id', $userId);
            })
            ->firstOrFail();

        return redirect()->route('my_messages', [
            'chat_id' => $chat->id
        ]);
    }
}
