<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LendingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_checkout_when_stock_is_zero(): void
    {
        $user = User::factory()->create([
            'role' => 'Student',
        ]);

        $equipment = Equipment::create([
            'description' => 'Test Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 0,
            'pending_deletion' => false,
        ]);

        $cart = [
            [
                'equipment_id' => $equipment->id,
                'quantity' => 1,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('cart.checkout'), [
                'pickup_date' => now()->addDay()->format('Y-m-d'),
                'pickup_time' => '09:00:00',
                'accept_terms' => '1',
                'cart_quantities' => [
                    $equipment->id => 1,
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_cannot_checkout_with_empty_cart(): void
    {
        $user = User::factory()->create([
            'role' => 'Student',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => []])
            ->post(route('cart.checkout'), [
                'pickup_date' => now()->addDay()->format('Y-m-d'),
                'pickup_time' => '09:00:00',
                'accept_terms' => '1',
                'cart_quantities' => [],
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['cart_quantities']);
    }

    public function test_successful_checkout_creates_lending_item(): void
    {
        $user = User::factory()->create([
            'role' => 'Student',
        ]);

        $equipment = Equipment::create([
            'description' => 'Available Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $cart = [
            [
                'equipment_id' => $equipment->id,
                'quantity' => 2,
            ],
        ];

        $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('cart.checkout'), [
                'pickup_date' => now()->addDay()->format('Y-m-d'),
                'pickup_time' => '09:00:00',
                'accept_terms' => '1',
                'cart_quantities' => [
                    $equipment->id => 2,
                ],
            ]);

        $lending = \App\Models\Lending::where('user_id', $user->id)->latest()->first();

        $this->assertNotNull($lending);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => 2,
        ]);
    }

    public function test_successful_checkout_creates_lending(): void
    {
        $user = User::factory()->create([
            'role' => 'Student',
        ]);

        $equipment = Equipment::create([
            'description' => 'Available Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $cart = [
            [
                'equipment_id' => $equipment->id,
                'quantity' => 1,
            ],
        ];

        $response = $this->actingAs($user)
            ->withSession(['cart' => $cart])
            ->post(route('cart.checkout'), [
                'pickup_date' => now()->addDay()->format('Y-m-d'),
                'pickup_time' => '09:00:00',
                'accept_terms' => '1',
                'cart_quantities' => [
                    $equipment->id => 1,
                ],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('lendings', [
            'user_id' => $user->id,
        ]);
    }
}