<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessagingBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_fetching_messages_marks_other_users_unread_messages_as_read(): void
    {
        Event::fake();

        $buyer = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $seller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $post = $this->createPost($seller);

        $chat = Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'status' => 'active',
            'unread_reminder_sent_at' => now(),
        ]);

        $message = Message::create([
            'chat_id' => $chat->id,
            'user_id' => $seller->id,
            'content' => 'Disponible para recoger.',
            'status' => 'sent',
            'sent_at' => now(),
            'read_at' => null,
        ]);

        $response = $this->actingAs($buyer)
            ->get("/messages/{$chat->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'id' => $message->id,
            'isMine' => false,
            'status' => 'read',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'status' => 'read',
        ]);

        $this->assertNotNull($message->fresh()->read_at);
        $this->assertNull($chat->fresh()->unread_reminder_sent_at);
    }

    public function test_sending_message_creates_record_for_chat_participant(): void
    {
        Bus::fake();
        Event::fake();

        $buyer = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $seller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $post = $this->createPost($seller);

        $chat = Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($buyer)
            ->post('/messages', [
                'chat_id' => $chat->id,
                'content' => 'Me interesa el articulo.',
            ]);

        $response->assertOk();

        $message = Message::where('chat_id', $chat->id)
            ->where('user_id', $buyer->id)
            ->firstOrFail();

        $this->assertSame('Me interesa el articulo.', $message->content);
        $this->assertSame('sent', $message->status);
    }

    public function test_outsider_cannot_send_message_to_unrelated_chat(): void
    {
        Bus::fake();
        Event::fake();

        $buyer = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $seller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $outsider = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $post = $this->createPost($seller);

        $chat = Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'status' => 'active',
        ]);

        $this->actingAs($outsider)
            ->post('/messages', [
                'chat_id' => $chat->id,
                'content' => 'Mensaje no autorizado.',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('messages', [
            'chat_id' => $chat->id,
            'user_id' => $outsider->id,
        ]);
    }

    private function createPost(User $seller): Post
    {
        return Post::create([
            'user_id' => $seller->id,
            'title' => 'Articulo de prueba',
            'description' => 'Descripcion de prueba',
            'cost' => 10,
            'category' => 'General',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);
    }
}
