<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_rate_own_post(): void
    {
        $user = User::factory()->create();

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'description' => 'Test description',
            'category' => 'General',
            'condition' => 'Used',
            'cost' => 10,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(
            route('marketplace.review.store', $post->id),
            [
                'rating' => 5,
                'comment' => 'Test review',
            ]
        );

        $response->assertStatus(422);

        $this->assertDatabaseMissing('reviews', [
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_rate_another_users_post(): void
    {
        $owner = User::factory()->create();
        $reviewer = User::factory()->create();

        $post = Post::create([
            'user_id' => $owner->id,
            'title' => 'Another Test Post',
            'description' => 'Another test description',
            'category' => 'General',
            'condition' => 'Used',
            'cost' => 20,
            'status' => 'active',
        ]);

        $response = $this->actingAs($reviewer)->post(
            route('marketplace.review.store', $post->id),
            [
                'rating' => 4,
                'comment' => 'Good item',
            ]
        );

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', [
            'post_id' => $post->id,
            'user_id' => $reviewer->id,
            'rating' => 4,
        ]);
    }

    public function test_second_review_updates_existing_review_instead_of_creating_duplicate(): void
{
    $owner = User::factory()->create();
    $reviewer = User::factory()->create();

    $post = Post::create([
        'user_id' => $owner->id,
        'title' => 'Update Review Test',
        'description' => 'Testing review update behavior',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 15,
        'status' => 'active',
    ]);

    $firstResponse = $this->actingAs($reviewer)->post(
        route('marketplace.review.store', $post->id),
        [
            'rating' => 3,
            'comment' => 'First review',
        ]
    );

    $firstResponse->assertStatus(200);

    $this->assertDatabaseHas('reviews', [
        'post_id' => $post->id,
        'user_id' => $reviewer->id,
        'rating' => 3,
        'comment' => 'First review',
    ]);

    $secondResponse = $this->actingAs($reviewer)->post(
        route('marketplace.review.store', $post->id),
        [
            'rating' => 5,
            'comment' => 'Updated review',
        ]
    );

    $secondResponse->assertStatus(200);

    $this->assertDatabaseHas('reviews', [
        'post_id' => $post->id,
        'user_id' => $reviewer->id,
        'rating' => 5,
        'comment' => 'Updated review',
    ]);

    $this->assertEquals(
        1,
        \App\Models\Review::where('post_id', $post->id)
            ->where('user_id', $reviewer->id)
            ->count()
    );
}
}