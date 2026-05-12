<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Post;
use App\Models\Chat;
use App\Models\UserReport;

class KineMercadoTest extends TestCase
{
    use RefreshDatabase;

    private function postData()
    {
        return [
            'title' => 'Test Post',
            'description' => 'Descripción',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
        ];
    }

    public function test_user_can_create_and_delete_post()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', $this->postData())
            ->assertStatus(200);

        $post = Post::first();

        $this->actingAs($user)
            ->delete("/posts/{$post->id}")
            ->assertStatus(200);
    }

    public function test_user_cannot_exceed_post_limit()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 15; $i++) {
            Post::create([
                'user_id' => $user->id,
                'title' => 'Test',
                'description' => 'Test',
                'cost' => 10,
                'category' => 'Test',
                'condition' => 'Nuevo',
                'status' => 'Disponible',
            ]);
        }

        $this->actingAs($user)
            ->post('/posts', $this->postData())
            ->assertStatus(403);
    }

    public function test_user_can_review_other_user()
    {
        $user = User::factory()->create();
        $seller = User::factory()->create();

        $post = Post::create([
            'user_id' => $seller->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user)
            ->post("/marketplace/{$post->id}/review", [
                'rating' => 5
            ])
            ->assertStatus(200);
    }

    public function test_user_can_report_other_user()
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $post = Post::create([
            'user_id' => $target->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user)
            ->post('/reports', [
                'reported_user_id' => $target->id,
                'post_id' => $post->id,
                'report_reason' => 'Spam',
                'description' => 'Este usuario está haciendo spam',
            ])
            ->assertStatus(200);
    }

    public function test_user_can_create_chat_and_send_message()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user2->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $chatResponse = $this->actingAs($user1)
            ->post('/chats/open', [
                'seller_id' => $user2->id,
                'post_id' => $post->id,
            ]);

        $chatResponse->assertStatus(302);

        $chat = Chat::first();

        $this->actingAs($user1)
            ->post('/messages', [
                'chat_id' => $chat->id,
                'content' => 'Hola'
            ])
            ->assertStatus(200);
    }

    public function test_admin_can_delete_any_post()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($admin)
            ->delete("/posts/{$post->id}")
            ->assertStatus(200);
    }

    public function test_admin_can_resolve_reports()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $report = UserReport::create([
            'user_id' => $admin->id,
            'reported_user_id' => $user->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Test report',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post("/reports/{$report->id}/resolve")
            ->assertStatus(200);
    }

    public function test_admin_can_block_user()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $report = UserReport::create([
            'user_id' => $admin->id,
            'reported_user_id' => $user->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Test report',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post("/reports/{$report->id}/ban")
            ->assertStatus(200);
    }

    public function test_blocked_user_cannot_create_post()
    {
        $user = User::factory()->create([
            'status' => 'Bloqueado'
        ]);

        $this->actingAs($user)
            ->post('/posts', $this->postData())
            ->assertStatus(302);
    }

    public function test_user_cannot_delete_other_user_post()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user2->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user1)
            ->delete("/posts/{$post->id}")
            ->assertStatus(403);
    }

    public function test_user_cannot_review_own_post()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user)
            ->post("/marketplace/{$post->id}/review", [
                'rating' => 5
            ])
            ->assertStatus(422);
    }

    public function test_user_cannot_chat_with_self()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user)
            ->post('/chats/open', [
                'seller_id' => $user->id,
                'post_id' => $post->id,
            ])
            ->assertStatus(403);
    }

    public function test_user_cannot_report_self()
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user)
            ->post('/reports', [
                'reported_user_id' => $user->id,
                'post_id' => $post->id,
                'report_reason' => 'Spam',
                'description' => 'Test para reporte a uno mismo',
            ])
            ->assertStatus(403);
    }

    public function test_normal_user_cannot_resolve_reports()
    {
        $user = User::factory()->create();
        $target = User::factory()->create();

        $post = Post::create([
            'user_id' => $target->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $report = UserReport::create([
            'user_id' => $user->id,
            'reported_user_id' => $target->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Descripción válida de prueba',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->post("/reports/{$report->id}/resolve")
            ->assertStatus(403);
    }

    public function test_user_can_only_see_own_chats()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user2->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $user1->id,
            'seller_user_id' => $user2->id,
            'status' => 'active',
        ]);

        Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $user3->id,
            'seller_user_id' => $user2->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user1)
            ->get(route('my_messages'));

        $response->assertStatus(200);

        $response->assertViewHas('chats', function ($chats) {
            return count($chats) === 1;
        });
    }

    public function test_deleting_post_deletes_related_chats()
    {
        $user = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $chat_id = Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $user2->id,
            'seller_user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('chats', [
            'id' => $chat_id->id
        ]);

        $this->actingAs($user)
            ->delete("/posts/{$post->id}");

        $this->assertDatabaseMissing('chats', [
            'id' => $chat_id->id
        ]);
    }

    public function test_report_status_changes_on_resolve()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $report = UserReport::create([
            'user_id' => $admin->id,
            'reported_user_id' => $user->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Test',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post("/reports/{$report->id}/resolve");

        $this->assertDatabaseHas('user_reports', [
            'id' => $report->id,
            'status' => 'resolved'
        ]);
    }

    public function test_user_cannot_view_reports_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/reports')
            ->assertStatus(403);
    }

    public function test_admin_can_view_reports()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);

        $this->actingAs($admin)
            ->get('/reports')
            ->assertStatus(200);
    }

    public function test_only_super_admin_can_change_roles()
    {
        $super = User::factory()->create(['role' => 'Super Administrador']);
        $user = User::factory()->create();

        $this->actingAs($super)
            ->put("/users/{$user->id}/role", [
                'role' => 'Administrador de Mercado'
            ])
            ->assertStatus(200);
    }

    public function test_market_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);
    }

    public function test_facility_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Instalaciones']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);
    }

    public function test_inventory_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Inventario']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);
    }

    public function test_blocked_user_cannot_send_message()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/messages', [
                'chat_id' => 1,
                'content' => 'Hola'
            ])
            ->assertStatus(302);
    }

    public function test_blocked_user_cannot_open_chat()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/chats/open', [])
            ->assertStatus(302);
    }

    public function test_blocked_user_cannot_review()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

            $post = Post::create([
                'user_id' => User::factory()->create()->id,
                'title' => 'Post',
                'description' => 'Desc',
                'cost' => 10,
                'category' => 'Test',
                'condition' => 'Nuevo',
                'status' => 'Disponible',
            ]);

        $this->actingAs($user)
            ->post("/marketplace/{$post->id}/review", [
                'rating' => 5
            ])
            ->assertStatus(302);
    }

    public function test_blocked_user_cannot_report()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/reports', [])
            ->assertStatus(302);
    }

    public function test_user_cannot_access_other_users_chat()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user2->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $chat = Chat::create([
            'post_id' => $post->id,
            'buyer_user_id' => $user2->id,
            'seller_user_id' => $user2->id,
            'status' => 'active',
        ]);

        $this->actingAs($user1)
            ->get("/chat/{$chat->id}")
            ->assertStatus(404);
    }

    public function test_chat_is_not_duplicated()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $post = Post::create([
            'user_id' => $user2->id,
            'title' => 'Post',
            'description' => 'Desc',
            'cost' => 10,
            'category' => 'Test',
            'condition' => 'Nuevo',
            'status' => 'Disponible',
        ]);

        $this->actingAs($user1)->post('/chats/open', [
            'seller_id' => $user2->id,
            'post_id' => $post->id,
        ]);

        $this->actingAs($user1)->post('/chats/open', [
            'seller_id' => $user2->id,
            'post_id' => $post->id,
        ]);

        $this->assertEquals(1, Chat::count());
    }

    public function test_post_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', [])
            ->assertStatus(302);
    }

    public function test_message_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/messages', [])
            ->assertStatus(302);
    }

    public function test_report_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/reports', [])
            ->assertStatus(302);
    }

    public function test_guest_cannot_create_post()
    {
        $this->post('/posts', [])
            ->assertStatus(302);
    }

    public function test_guest_cannot_open_chat()
    {
        $this->post('/chats/open', [])
            ->assertStatus(302);
    }

    public function test_guest_cannot_access_marketplace()
    {
        $this->get('/kinemarket')
            ->assertStatus(302);
    }
}
        