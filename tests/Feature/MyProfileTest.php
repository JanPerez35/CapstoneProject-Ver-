<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_profile_page_loads_for_authenticated_user_with_summary_data(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Kevin',
            'last_name' => 'Pratts',
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $seller = User::factory()->create();

        Review::create([
            'user_id' => $seller->id,
            'seller_id' => $user->id,
            'rating' => 5,
            'status' => 'confident',
            'comment' => null,
        ]);

        $response = $this->actingAs($user)->get(route('my_profile'));

        $response->assertOk();
        $response->assertViewHas('user', fn (User $viewUser) => $viewUser->is($user));
        $response->assertViewHas('sellerAverageRating', 5.0);
        $response->assertViewHas('sellerReviewsCount', 1);
    }

    public function test_profile_blocks_unauthenticated_access(): void
    {
        $this->get(route('my_profile'))
            ->assertRedirect();
    }

    public function test_profile_only_loads_authenticated_users_posts(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $otherUser = User::factory()->create();

        $ownPost = Post::create([
            'user_id' => $user->id,
            'title' => 'My Calculator',
            'description' => 'Owned profile post',
            'cost' => 25,
            'category' => 'Supplies',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        Post::create([
            'user_id' => $otherUser->id,
            'title' => 'Other Calculator',
            'description' => 'Other user post',
            'cost' => 30,
            'category' => 'Supplies',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        $response = $this->actingAs($user)->get(route('my_profile'));

        $posts = $response->viewData('posts');

        $response->assertOk();
        $this->assertCount(1, $posts);
        $this->assertTrue($posts->first()->is($ownPost));
    }

    public function test_profile_request_search_filters_only_authenticated_users_lending_records(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $otherUser = User::factory()->create();

        $basketball = Equipment::create([
            'description' => 'Balon Basketball Oficial',
            'category' => 'Deportes',
            'location' => 'Almacen A',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $soccer = Equipment::create([
            'description' => 'Balon Soccer Oficial',
            'category' => 'Deportes',
            'location' => 'Almacen B',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $matchingLending = $this->createLendingWithItem($user, $basketball, 'approved');
        $this->createLendingWithItem($user, $soccer, 'approved');
        $this->createLendingWithItem($otherUser, $basketball, 'approved');

        $response = $this->actingAs($user)->get(route('my_profile', [
            'request_search' => 'Basketball',
            'tab' => 'requests',
        ]));

        $requests = $response->viewData('requests');

        $response->assertOk();
        $this->assertCount(1, $requests);
        $this->assertTrue($requests->first()->is($matchingLending));
    }

    public function test_profile_finished_request_filter_includes_finished_and_returned_requests(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $equipment = Equipment::create([
            'description' => 'Cronometro Digital',
            'category' => 'Deportes',
            'location' => 'Almacen A',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $finished = $this->createLendingWithItem($user, $equipment, 'finished');
        $returned = $this->createLendingWithItem($user, $equipment, 'returned');
        $this->createLendingWithItem($user, $equipment, 'pending');

        $response = $this->actingAs($user)->get(route('my_profile', [
            'request_status' => 'finished',
            'tab' => 'requests',
        ]));

        $requestIds = $response->viewData('requests')->pluck('id')->all();

        $response->assertOk();
        $this->assertContains($finished->id, $requestIds);
        $this->assertContains($returned->id, $requestIds);
        $this->assertCount(2, $requestIds);
    }

    private function createLendingWithItem(User $user, Equipment $equipment, string $status): Lending
    {
        $lending = Lending::create([
            'user_id' => $user->id,
            'commentary' => null,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->setTime(15, 0),
            'flag' => false,
            'status' => $status,
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => 1,
            'item_status' => $status,
        ]);

        return $lending;
    }
}
