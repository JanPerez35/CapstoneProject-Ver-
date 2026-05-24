<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_list_excludes_posts_from_blocked_sellers(): void
    {
        $viewer = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $activeSeller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $blockedSeller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Bloqueado',
        ]);

        Post::create([
            'user_id' => $activeSeller->id,
            'title' => 'Visible Post',
            'description' => 'Visible',
            'cost' => 10,
            'category' => 'General',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        Post::create([
            'user_id' => $blockedSeller->id,
            'title' => 'Hidden Blocked Post',
            'description' => 'Hidden',
            'cost' => 10,
            'category' => 'General',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        $response = $this->actingAs($viewer)->get(route('posts.list'));

        $response->assertOk();
        $response->assertJsonFragment(['title' => 'Visible Post']);
        $response->assertJsonMissing(['title' => 'Hidden Blocked Post']);
    }

    public function test_post_detail_returns_seller_rating_summary(): void
    {
        $viewer = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $seller = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
            'first_name' => 'Ana',
            'last_name' => 'Rivera',
        ]);

        $reviewerOne = User::factory()->create();
        $reviewerTwo = User::factory()->create();

        $post = Post::create([
            'user_id' => $seller->id,
            'title' => 'Rated Post',
            'description' => 'Rated',
            'cost' => 10,
            'category' => 'General',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        Review::create([
            'user_id' => $reviewerOne->id,
            'seller_id' => $seller->id,
            'post_id' => $post->id,
            'rating' => 4,
            'status' => 'confident',
        ]);

        Review::create([
            'user_id' => $reviewerTwo->id,
            'seller_id' => $seller->id,
            'post_id' => $post->id,
            'rating' => 5,
            'status' => 'confident',
        ]);

        $response = $this->actingAs($viewer)->get(route('posts.show', $post->id));

        $response->assertOk();
        $response->assertJsonPath('user.name', 'Ana Rivera');
        $response->assertJsonPath('user.average_rating', 4.5);
        $response->assertJsonPath('user.reviews_count', 2);
    }
}
