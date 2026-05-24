<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Post;
use App\Models\Chat;
use App\Models\UserReport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Class KineMercadoTest
 *
 * This test suite covers the core functionalities of the KineMercado marketplace feature, including:
 * - post creation and deletion
 * - chat initiation and messaging
 * - user reviews and reporting
 * - administrative actions such as resolving reports and blocking users
 *
 * The tests ensure that both normal users and administrators can perform their respective actions while enforcing proper access controls and validations.
 */
class KineMercadoTest extends TestCase
{
    use RefreshDatabase;

public function test_normal_user_cannot_view_reports_data(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->get(route('reports.data'));

        $response->assertForbidden();
    }

    public function test_marketplace_admin_can_view_reports_data(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Mercado',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.data'));

        $response->assertOk();
    }

    public function test_normal_user_cannot_resolve_report(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $target = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

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
        'description' => 'Test report',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->post(route('reports.resolve', $report));

    $response->assertForbidden();

    $this->assertDatabaseHas('user_reports', [
        'id' => $report->id,
        'status' => 'pending',
    ]);
}

    public function test_normal_user_cannot_ban_from_report(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $target = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

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
        'description' => 'Test report',
        'status' => 'pending',
    ]);

    $response = $this->actingAs($user)
        ->post(route('reports.ban', $report));

    $response->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'status' => 'Activo',
    ]);
}

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

    /**
     * Test that a user can create a post and then delete it, ensuring the post is removed from the database.
     */
    public function test_user_can_create_and_delete_post()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', $this->postData())
            ->assertStatus(200);

        $post = Post::first();

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete("/posts/{$post->id}")
            ->assertStatus(200);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * Test that a user cannot create more than 15 posts, ensuring the post limit is enforced.
     */
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

        $this->assertCount(15, Post::where('user_id', $user->id)->get());
    }

    /**
     * Test that a user can review another user, ensuring the review is saved in the database.
     */
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

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'rating' => 5,
        ]);
    }

    /**
     * Test that a user can report another user, ensuring the report is saved in the database.
     */
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

        $this->assertDatabaseHas('user_reports', [
            'user_id' => $user->id,
            'reported_user_id' => $target->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Este usuario está haciendo spam',
            'status' => 'pending',
        ]);
    }

    /**
     * Test that a user can create a chat and send a message, ensuring the message is saved in the database.
     */
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

        $this->assertDatabaseHas('messages', [
            'chat_id' => $chat->id,
        ]);
    }

    /**
     * Test that an admin can delete any post, ensuring the post is removed from the database.
     */
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

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * Test that an admin can resolve reports, ensuring the report status is updated in the database.
     */
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

        $this->assertDatabaseHas('user_reports', [
            'id' => $report->id,
            'status' => 'resolved'
        ]);
    }

    /**
    * Test that an admin can block a user, ensuring the user's status is updated in the database.
    */
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

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'Bloqueado'
        ]);
    }

    /**
     * Test that a blocked user cannot create a post, ensuring the action is forbidden.
     */
    public function test_blocked_user_cannot_create_post()
    {
        $user = User::factory()->create([
            'status' => 'Bloqueado'
        ]);

        $this->actingAs($user)
            ->post('/posts', $this->postData())
            ->assertStatus(302);

        $this->assertDatabaseMissing('posts', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that a user cannot delete another user's post, ensuring the action is forbidden.
     */
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

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
        ]);
    }

    /**
     * Test that a user cannot review their own post, ensuring the action is forbidden.
     */
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

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'seller_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Test that a user cannot report themselves, ensuring the action is forbidden.
     */
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

            $this->assertDatabaseMissing('chats', [
                'post_id' => $post->id,
                'buyer_user_id' => $user->id,
                'seller_user_id' => $user->id,
            ]);
    }

    /**
     * Test that a user cannot report themselves, ensuring the action is forbidden.
     */
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

        $this->assertDatabaseMissing('user_reports', [
            'user_id' => $user->id,
            'reported_user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Test that a normal user cannot resolve reports, ensuring the action is forbidden.
     */
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

        $this->assertDatabaseHas('user_reports', [
            'id' => $report->id,
            'status' => 'pending'
        ]);
    }

    /**
     * Test that a user can only see their own chats, ensuring the correct chats are returned in the view.
     */
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

    /**
     * Test that deleting a post also deletes related chats, ensuring data integrity in the database.
     */
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

    /**
     * Test that resolving a report changes its status to 'resolved', ensuring the report is updated in the database.
     */
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

    /**
     * Test that a user cannot view the reports list, ensuring the action is forbidden for non-admin users.
     */
    public function test_user_cannot_view_reports_list()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/reports')
            ->assertStatus(403);
    }

    /**
     * Test that an admin can view the reports list, ensuring the page loads successfully for admin users.
     */
    public function test_admin_can_view_reports()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);

        $this->actingAs($admin)
            ->get('/reports')
            ->assertStatus(200);
    }

    /**
     * Test that only a super admin can change user roles, ensuring the action is successful for super admins and forbidden for others.
     */
    public function test_only_super_admin_can_change_roles()
    {
        $super = User::factory()->create(['role' => 'Super Administrador']);
        $user = User::factory()->create();

        $this->actingAs($super)
            ->put("/users/{$user->id}/role", [
                'role' => 'Administrador de Mercado'
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'Administrador de Mercado'
        ]);
    }

    /**
     * Test that a market admin cannot change user roles, ensuring the action is forbidden for market admins.
     */
    public function test_market_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => $user->role
        ]);
    }

    /**
     * Test that a facility admin cannot change user roles, ensuring the action is forbidden for facility admins.
     */
    public function test_facility_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Instalaciones']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => $user->role
        ]);
    }

    /**
     * Test that an inventory admin cannot change user roles, ensuring the action is forbidden for inventory admins.
     */
    public function test_inventory_admin_cannot_change_roles()
    {
        $admin = User::factory()->create(['role' => 'Administrador de Inventario']);
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Super Administrador'
            ])
            ->assertStatus(403);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => $user->role
        ]);
    }

    /**
     * Test that a blocked user cannot send messages, ensuring the action is forbidden for blocked users.
     */
    public function test_blocked_user_cannot_send_message()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/messages', [
                'chat_id' => 1,
                'content' => 'Hola'
            ])
            ->assertStatus(302);

            $this->assertDatabaseMissing('messages', [
                'user_id' => $user->id,
                'content' => 'Hola'
            ]);
    }

    /**
     * Test that a blocked user cannot open a chat, ensuring the action is forbidden for blocked users.
     */
    public function test_blocked_user_cannot_open_chat()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/chats/open', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('chats', [
            'buyer_user_id' => $user->id,
        ]);
    }

    /**
     * Test that a blocked user cannot review other users, ensuring the action is forbidden for blocked users.
     */
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

        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    /**
     * Test that a blocked user cannot report other users, ensuring the action is forbidden for blocked users.
     */
    public function test_blocked_user_cannot_report()
    {
        $user = User::factory()->create(['status' => 'Bloqueado']);

        $this->actingAs($user)
            ->post('/reports', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('user_reports', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that a user cannot access another user's chat, ensuring the action is forbidden and returns a 404 status.
     */
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

        $this->assertDatabaseHas('chats', [
            'id' => $chat->id,
        ]);
    }

    /**
     * Test that a user cannot open multiple chats for the same post, ensuring that duplicate chats are not created.
     */
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

        $this->assertDatabaseHas('chats', [
            'post_id' => $post->id,
            'buyer_user_id' => $user1->id,
            'seller_user_id' => $user2->id,
        ]);
    }

    /**
     * Test that banning a user from a report atomically blocks the user AND resolves the report.
     */
    public function test_banning_user_from_report_resolves_report_and_blocks_user(): void
    {
        $admin = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);
        $target = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);

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
            'user_id' => $admin->id,
            'reported_user_id' => $target->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'Descripción del reporte de prueba',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('reports.ban', $report))
            ->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'status' => 'Bloqueado',
        ]);

        $this->assertDatabaseHas('user_reports', [
            'id' => $report->id,
            'status' => 'resolved',
        ]);

        $this->assertNotNull($report->fresh()->resolved_at);
    }

    /**
     * Test that post creation validation fails when required fields are missing, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_post_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('posts', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that message creation validation fails when required fields are missing, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_message_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/messages', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('messages', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that report creation validation fails when required fields are missing, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_report_validation_fails()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/reports', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('user_reports', [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that guests cannot create posts, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_guest_cannot_create_post()
    {
        $this->post('/posts', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('posts', [
            'title' => null,
        ]);
    }

    /**
     * Test that guests cannot open chats, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_guest_cannot_open_chat()
    {
        $this->post('/chats/open', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('chats', [
            'id' => null,
        ]);
    }

    /**
     * Test that guests cannot send messages, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_guest_cannot_send_message()
    {
        $this->post('/messages', [])
            ->assertStatus(302);

        $this->assertDatabaseMissing('messages', [
            'id' => null,
        ]);
    }

    /**
     * Test that guests cannot access the marketplace, ensuring the action is forbidden and returns a 302 status.
     */
    public function test_guest_cannot_access_marketplace()
    {
        $this->get('/kinemarket')
            ->assertStatus(302);

        $this->assertDatabaseMissing('posts', [
            'id' => null,
        ]);
    }

    public function test_user_cannot_upload_php_file_as_post_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $file = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');

        $response = $this->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Valid Test Title',
                'description' => 'Valid description',
                'cost' => 10,
                'category' => 'Test',
                'condition' => 'Nuevo',
                'images' => [$file],
            ]);

        $response->assertSessionHasErrors(['images.0']);

        $this->assertDatabaseMissing('posts', [
            'title' => 'Valid Test Title',
        ]);
    }

    public function test_user_cannot_upload_more_than_three_post_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Valid Test Title',
                'description' => 'Valid description',
                'cost' => 10,
                'category' => 'Test',
                'condition' => 'Nuevo',
                'images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                    UploadedFile::fake()->image('three.jpg'),
                    UploadedFile::fake()->image('four.jpg'),
                ],
            ]);

        $response->assertSessionHasErrors(['images']);

        $this->assertDatabaseMissing('posts', [
            'title' => 'Valid Test Title',
        ]);
    }

    public function test_user_can_upload_valid_post_images(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->post(route('posts.store'), [
                'title' => 'Valid Test Title',
                'description' => 'Valid description',
                'cost' => 10,
                'category' => 'Test',
                'condition' => 'Nuevo',
                'images' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.png'),
                ],
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('posts', [
            'title' => 'Valid Test Title',
            'user_id' => $user->id,
        ]);

        $post = Post::where('title', 'Valid Test Title')->first();

        $this->assertNotNull($post);
    }
}
