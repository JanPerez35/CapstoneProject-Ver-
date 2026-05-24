<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LendingAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeInventoryAdmin(): User
    {
        return User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);
    }

    private function makeUser(): User
    {
        return User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);
    }

    private function makeEquipment(int $stock): Equipment
    {
        return Equipment::create([
            'description' => 'Test Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => $stock,
            'available_quantity' => $stock,
            'pending_deletion' => false,
        ]);
    }

    private function makePendingLending(User $requester, Equipment $equipment, int $quantity): Lending
    {
        $lending = Lending::create([
            'user_id' => $requester->id,
            'start_time' => now()->addDay(),
            'end_time' => now()->addDays(2),
            'flag' => true,
            'status' => 'pending',
            'special_reason' => 'Actividad oficial universitaria',
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => $quantity,
            'item_status' => 'pending',
        ]);

        return $lending;
    }

    // =========================================================================
    // Admin: Approve Request
    // =========================================================================

    public function test_admin_approving_pending_request_decrements_stock_and_sets_status_approved(): void
    {
        $admin = $this->makeInventoryAdmin();
        $requester = $this->makeUser();
        $equipment = $this->makeEquipment(10);
        $lending = $this->makePendingLending($requester, $equipment, 3);

        $response = $this->actingAs($admin)
            ->post(route('inventory_management.requests.approve', $lending->id));

        $response->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 7,
        ]);

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'approved',
        ]);
    }

    public function test_admin_cannot_approve_request_when_stock_is_insufficient(): void
    {
        $admin = $this->makeInventoryAdmin();
        $requester = $this->makeUser();
        $equipment = $this->makeEquipment(1);
        $lending = $this->makePendingLending($requester, $equipment, 5); // requesting more than available

        $this->actingAs($admin)
            ->post(route('inventory_management.requests.approve', $lending->id));

        // DB transaction rolls back — stock and status must remain unchanged
        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 1,
        ]);

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'pending',
        ]);
    }

    // =========================================================================
    // Admin: Reject Request
    // =========================================================================

    public function test_admin_rejecting_pending_request_does_not_change_stock(): void
    {
        $admin = $this->makeInventoryAdmin();
        $requester = $this->makeUser();
        $equipment = $this->makeEquipment(10);
        $lending = $this->makePendingLending($requester, $equipment, 3);

        $response = $this->actingAs($admin)
            ->post(route('inventory_management.requests.reject', $lending->id));

        $response->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 10,
        ]);

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'rejected',
        ]);
    }

    // =========================================================================
    // Admin: Mark Returned
    // =========================================================================

    public function test_admin_marking_returned_restores_stock(): void
    {
        $admin = $this->makeInventoryAdmin();
        $requester = $this->makeUser();
        $equipment = $this->makeEquipment(10);

        // Simulate stock already decremented by a prior checkout
        $equipment->available_quantity = 7;
        $equipment->save();

        $lending = Lending::create([
            'user_id' => $requester->id,
            'start_time' => now()->subDay(),
            'end_time' => now(),
            'flag' => false,
            'status' => 'approved',
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => 3,
            'item_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('inventory_management.requests.return', $lending->id));

        $response->assertRedirect(route('inventory_management.borrows'));

        $this->assertDatabaseHas('equipment', [
            'id' => $equipment->id,
            'available_quantity' => 10,
        ]);

        $this->assertDatabaseHas('lendings', [
            'id' => $lending->id,
            'status' => 'returned',
        ]);

        $this->assertDatabaseHas('lending_items', [
            'lending_id' => $lending->id,
            'item_status' => 'returned',
        ]);
    }

    public function test_marking_multiple_items_returned_restores_all_stock(): void
    {
        $admin = $this->makeInventoryAdmin();
        $requester = $this->makeUser();

        $equipmentA = $this->makeEquipment(10);
        $equipmentB = $this->makeEquipment(5);

        $equipmentA->available_quantity = 8; // 2 checked out
        $equipmentA->save();
        $equipmentB->available_quantity = 3; // 2 checked out
        $equipmentB->save();

        $lending = Lending::create([
            'user_id' => $requester->id,
            'start_time' => now()->subDay(),
            'end_time' => now(),
            'flag' => false,
            'status' => 'approved',
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipmentA->id,
            'quantity' => 2,
            'item_status' => 'approved',
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipmentB->id,
            'quantity' => 2,
            'item_status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->post(route('inventory_management.requests.return', $lending->id));

        $this->assertDatabaseHas('equipment', ['id' => $equipmentA->id, 'available_quantity' => 10]);
        $this->assertDatabaseHas('equipment', ['id' => $equipmentB->id, 'available_quantity' => 5]);
    }

    // =========================================================================
    // System Limits
    // =========================================================================

    public function test_checkout_is_blocked_when_daily_request_limit_is_reached(): void
    {
        $user = $this->makeUser();
        $equipment = $this->makeEquipment(100);

        for ($i = 0; $i < 50; $i++) {
            Lending::create([
                'user_id' => $user->id,
                'start_time' => now()->addDay(),
                'end_time' => now()->addDays(2),
                'flag' => false,
                'status' => 'approved',
            ]);
        }

        $response = $this->actingAs($user)
            ->withSession([
                'cart' => [
                    $equipment->id => [
                        'equipment_id' => $equipment->id,
                        'description' => 'Test Equipment',
                        'category' => 'Test',
                        'location' => 'Storage',
                        'available_quantity' => 100,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->post(route('cart.checkout'), [
                'pickup_date' => now()->addDay()->format('Y-m-d'),
                'pickup_time' => '09:00:00',
                'accept_terms' => '1',
                'cart_quantities' => [$equipment->id => 1],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertEquals(50, Lending::count());
    }

    public function test_cart_rejects_adding_an_eleventh_different_item(): void
    {
        $user = $this->makeUser();

        $existingCart = [];
        for ($i = 0; $i < 10; $i++) {
            $eq = Equipment::create([
                'description' => "Equipment {$i}",
                'category' => 'Test',
                'location' => 'Storage',
                'quantity' => 5,
                'available_quantity' => 5,
                'pending_deletion' => false,
            ]);
            $existingCart[$eq->id] = [
                'equipment_id' => $eq->id,
                'description' => "Equipment {$i}",
                'category' => 'Test',
                'location' => 'Storage',
                'available_quantity' => 5,
                'quantity' => 1,
            ];
        }

        $eleventhEquipment = Equipment::create([
            'description' => 'Eleventh Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 5,
            'pending_deletion' => false,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['cart' => $existingCart])
            ->post(route('cart.add'), [
                'equipment_id' => $eleventhEquipment->id,
                'quantity' => 1,
            ]);

        $response->assertSessionHas('error');
        $response->assertSessionMissing('cart.' . $eleventhEquipment->id);
    }

    public function test_adding_more_of_an_existing_cart_item_bypasses_the_ten_item_limit(): void
    {
        $user = $this->makeUser();

        $existingCart = [];
        $firstEquipment = null;

        for ($i = 0; $i < 10; $i++) {
            $eq = Equipment::create([
                'description' => "Equipment {$i}",
                'category' => 'Test',
                'location' => 'Storage',
                'quantity' => 10,
                'available_quantity' => 10,
                'pending_deletion' => false,
            ]);
            $existingCart[$eq->id] = [
                'equipment_id' => $eq->id,
                'description' => "Equipment {$i}",
                'category' => 'Test',
                'location' => 'Storage',
                'available_quantity' => 10,
                'quantity' => 1,
            ];
            $firstEquipment = $firstEquipment ?? $eq;
        }

        // Adding more of an already-in-cart item should succeed even with 10 items in cart
        $response = $this->actingAs($user)
            ->withSession(['cart' => $existingCart])
            ->post(route('cart.add'), [
                'equipment_id' => $firstEquipment->id,
                'quantity' => 1,
            ]);

        $response->assertSessionHas('cart.' . $firstEquipment->id . '.quantity', 2);
        $response->assertSessionMissing('error');
    }
}
