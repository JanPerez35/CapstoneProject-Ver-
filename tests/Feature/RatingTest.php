<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\UserReport;
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

public function test_low_rating_creates_pending_user_report_for_seller(): void
{
    $owner = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $reviewer = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $post = Post::create([
        'user_id' => $owner->id,
        'title' => 'Low Rating Test',
        'description' => 'Testing low rating report behavior',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 15,
        'status' => 'Disponible',
    ]);

    $this->actingAs($reviewer)->post(
        route('marketplace.review.store', $post->id),
        [
            'rating' => 1,
        ]
    )->assertOk();

    $this->assertDatabaseHas('reviews', [
        'post_id' => $post->id,
        'user_id' => $reviewer->id,
        'seller_id' => $owner->id,
        'rating' => 1,
        'status' => 'negative',
    ]);

    $this->assertTrue(
        UserReport::where('reported_user_id', $owner->id)
            ->where('status', 'pending')
            ->exists()
    );
}

public function test_positive_rating_does_not_create_low_rating_report(): void
{
    $owner = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $reviewer = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $post = Post::create([
        'user_id' => $owner->id,
        'title' => 'Positive Rating Test',
        'description' => 'Testing positive rating behavior',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 15,
        'status' => 'Disponible',
    ]);

    $this->actingAs($reviewer)->post(
        route('marketplace.review.store', $post->id),
        [
            'rating' => 5,
        ]
    )->assertOk();

    $this->assertDatabaseMissing('user_reports', [
        'reported_user_id' => $owner->id,
        'report_reason' => 'CalificaciÃ³n baja',
    ]);
}

public function test_multiple_low_ratings_from_different_buyers_create_only_one_pending_auto_report(): void
{
    $seller = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);
    $buyerA = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);
    $buyerB = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);

    $postA = Post::create([
        'user_id' => $seller->id,
        'title' => 'Post A',
        'description' => 'Description A',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 10,
        'status' => 'Disponible',
    ]);

    $postB = Post::create([
        'user_id' => $seller->id,
        'title' => 'Post B',
        'description' => 'Description B',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 10,
        'status' => 'Disponible',
    ]);

    $this->actingAs($buyerA)->post(route('marketplace.review.store', $postA->id), ['rating' => 1])->assertOk();
    $this->actingAs($buyerB)->post(route('marketplace.review.store', $postB->id), ['rating' => 1])->assertOk();

    // updateOrCreate in ReviewController ensures only one pending "Calificación baja" report per seller
    $this->assertEquals(
        1,
        \App\Models\UserReport::where('reported_user_id', $seller->id)
            ->where('report_reason', 'Calificación baja')
            ->where('status', 'pending')
            ->count()
    );
}

public function test_auto_report_is_not_triggered_when_seller_average_stays_above_two(): void
{
    $seller = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);
    $buyerA = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);
    $buyerB = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);

    $postA = Post::create([
        'user_id' => $seller->id,
        'title' => 'Post A',
        'description' => 'Description',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 10,
        'status' => 'Disponible',
    ]);

    $postB = Post::create([
        'user_id' => $seller->id,
        'title' => 'Post B',
        'description' => 'Description',
        'category' => 'General',
        'condition' => 'Used',
        'cost' => 10,
        'status' => 'Disponible',
    ]);

    // ratings 4 and 1 → average 2.5, which is above the ≤2 threshold — no auto-report
    $this->actingAs($buyerA)->post(route('marketplace.review.store', $postA->id), ['rating' => 4])->assertOk();
    $this->actingAs($buyerB)->post(route('marketplace.review.store', $postB->id), ['rating' => 1])->assertOk();

    $this->assertDatabaseMissing('user_reports', [
        'reported_user_id' => $seller->id,
        'report_reason' => 'Calificación baja',
        'status' => 'pending',
    ]);
}
}
