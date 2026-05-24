<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Services\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LendingTest extends TestCase
{
    use RefreshDatabase;

public function test_normal_user_cannot_access_inventory_management(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->get(route('inventory_management'));

        $response->assertForbidden();
    }

    public function test_normal_user_cannot_create_equipment(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->post(route('inventory.store'), [
                'description' => 'Unauthorized Equipment',
                'category' => 'Test',
                'location' => 'Storage',
                'quantity' => 5,
                'available_quantity' => 5,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('equipment', [
            'description' => 'Unauthorized Equipment',
        ]);
    }

    public function test_normal_user_cannot_update_equipment(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $equipment = Equipment::create([
            'description' => 'Original Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $response = $this->actingAs($user)
            ->put(route('equipment.update', $equipment->id), [
                'description' => 'Hacked Equipment',
                'category' => 'Test',
                'location' => 'Storage',
                'quantity' => 10,
                'available_quantity' => 10,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'description' => 'Original Equipment',
        ]);
    }

    public function test_normal_user_cannot_delete_equipment(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $equipment = Equipment::create([
            'description' => 'Protected Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $response = $this->actingAs($user)
            ->delete(route('equipment.destroy', $equipment->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
        ]);
    }

    public function test_normal_user_cannot_access_borrows_admin_page(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->get(route('inventory_management.borrows'));

        $response->assertForbidden();
    }

    public function test_normal_user_cannot_export_inventory_statistics(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($user)
            ->get(route('inventory_management.inventory_statistics.export'));

        $response->assertForbidden();
    }

    public function test_inventory_admin_can_access_inventory_management(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('inventory_management'));

        $response->assertOk();
    }

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

    public function test_inventory_admin_approves_lending_and_decrements_stock(): void
    {
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        [$lending, $equipment] = $this->createPendingLending(quantity: 2, availableQuantity: 5);

        $this->actingAs($admin)
            ->post(route('inventory_management.requests.approve', $lending->id))
            ->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'approved',
        ]);

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 3,
        ]);
    }

    public function test_inventory_admin_rejects_lending_without_decrementing_stock(): void
    {
        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('send')->once();
        });

        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        [$lending, $equipment] = $this->createPendingLending(quantity: 2, availableQuantity: 5);

        $this->actingAs($admin)
            ->post(route('inventory_management.requests.reject', $lending->id))
            ->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'rejected',
        ]);

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 5,
        ]);
    }

    public function test_inventory_admin_marks_lending_returned_and_restores_stock(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        [$lending, $equipment] = $this->createPendingLending(quantity: 2, availableQuantity: 3);

        $lending->update(['status' => 'approved']);
        $lending->items()->update(['item_status' => 'approved']);

        $this->actingAs($admin)
            ->post(route('inventory_management.requests.return', $lending->id))
            ->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'returned',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'returned',
        ]);

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 5,
        ]);
    }

    private function createPendingLending(int $quantity, int $availableQuantity): array
    {
        $borrower = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $equipment = Equipment::create([
            'description' => 'Admin Lending Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => $availableQuantity,
            'pending_deletion' => false,
        ]);

        $lending = Lending::create([
            'user_id' => $borrower->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDay()->setTime(15, 0),
            'flag' => true,
            'status' => 'pending',
            'special_reason' => 'Actividad institucional de larga duracion.',
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => $quantity,
            'item_status' => 'pending',
        ]);

        return [$lending->fresh('items'), $equipment];
    }
}
