<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use App\Models\Lending;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_equipment_with_zero_available_quantity_is_hidden_from_normal_user(): void
    {
        $user = User::factory()->create([
            'role' => 'Student',
        ]);

        Equipment::create([
            'description' => 'Hidden Equipment',
            'category' => 'Test',
            'location' => 'Storage',
            'quantity' => 5,
            'available_quantity' => 0,
            'pending_deletion' => false,
        ]);

        $response = $this->actingAs($user)->get(route('kinventory'));

        $response->assertOk();
        $response->assertDontSee('Hidden Equipment');
        $response->assertSee('No se encontraron equipos');
    }

    public function test_inventory_admin_cannot_upload_php_file_as_equipment_image(): void
{
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'Administrador de Inventario',
        'status' => 'Activo',
    ]);

    $file = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');

    $response = $this->actingAs($admin)
        ->post(route('inventory.store'), [
            'description' => 'Test Equipment',
            'category' => 'Test Category',
            'quantity' => 5,
            'available_quantity' => 5,
            'location' => 'Storage Room',
            'image' => $file,
        ]);

    $response->assertSessionHasErrors(['image']);

    $this->assertDatabaseMissing('equipment', [
        'description' => 'Test Equipment',
    ]);
}

public function test_inventory_admin_cannot_upload_png_equipment_image(): void
{
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'Administrador de Inventario',
        'status' => 'Activo',
    ]);

    $file = UploadedFile::fake()->image('equipment.png');

    $response = $this->actingAs($admin)
        ->post(route('inventory.store'), [
            'description' => 'PNG Equipment',
            'category' => 'Test Category',
            'quantity' => 5,
            'available_quantity' => 5,
            'location' => 'Storage Room',
            'image' => $file,
        ]);

    $response->assertSessionHasErrors(['image']);

    $this->assertDatabaseMissing('equipment', [
        'description' => 'PNG Equipment',
    ]);
}

public function test_inventory_admin_can_upload_valid_jpg_equipment_image(): void
{
    Storage::fake('public');

    $admin = User::factory()->create([
        'role' => 'Administrador de Inventario',
        'status' => 'Activo',
    ]);

    $file = UploadedFile::fake()->image('equipment.jpg');

    $response = $this->actingAs($admin)
        ->post(route('inventory.store'), [
            'description' => 'Valid Equipment',
            'category' => 'Test Category',
            'quantity' => 5,
            'available_quantity' => 5,
            'location' => 'Storage Room',
            'image' => $file,
        ]);

    // Use assertRedirect if your controller redirects after storing.
    // Use assertOk if it returns JSON 200.
    $response->assertRedirect();

    $this->assertDatabaseHas('equipment', [
        'description' => 'Valid Equipment',
        'category' => 'Test Category',
        'quantity' => 5,
        'available_quantity' => 5,
        'location' => 'Storage Room',
    ]);
}

public function test_normal_user_cannot_create_equipment_even_with_valid_image(): void
{
    Storage::fake('public');

    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $file = UploadedFile::fake()->image('equipment.jpg');

    $response = $this->actingAs($user)
        ->post(route('inventory.store'), [
            'description' => 'Unauthorized Equipment',
            'category' => 'Test Category',
            'quantity' => 5,
            'available_quantity' => 5,
            'location' => 'Storage Room',
            'image' => $file,
        ]);

    $response->assertForbidden();

    $this->assertDatabaseMissing('equipment', [
        'description' => 'Unauthorized Equipment',
    ]);
}

public function test_add_to_cart_allows_internal_redirect_url(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Test Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $internalUrl = url('/kinventory');

    $response = $this->actingAs($user)
        ->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'quantity' => 1,
            'redirect_back' => $internalUrl,
        ]);

    $response->assertRedirect($internalUrl);
}

public function test_add_to_cart_does_not_redirect_to_external_url(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Test Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $response = $this->actingAs($user)
        ->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'quantity' => 1,
            'redirect_back' => 'https://evil.com/fake-login',
        ]);

    $response->assertRedirect(route('kinventory'));
}

public function test_add_to_cart_merges_existing_item_without_exceeding_stock(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Merged Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $this->actingAs($user)
        ->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'quantity' => 2,
        ]);

    $response = $this->actingAs($user)
        ->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'quantity' => 3,
        ]);

    $response->assertSessionHas('cart.' . $equipment->id . '.quantity', 5);

    $response = $this->actingAs($user)
        ->post(route('cart.add'), [
            'equipment_id' => $equipment->id,
            'quantity' => 1,
        ]);

    $response->assertSessionHas('error');
    $response->assertSessionHas('cart.' . $equipment->id . '.quantity', 5);
}

public function test_normal_checkout_decrements_stock_and_clears_cart(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Stock Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'cart' => [
                $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'description' => $equipment->description,
                    'category' => $equipment->category,
                    'location' => $equipment->location,
                    'available_quantity' => 5,
                    'quantity' => 2,
                ],
            ],
        ])
        ->post(route('cart.checkout'), [
            'pickup_date' => now()->addDay()->format('Y-m-d'),
            'pickup_time' => '09:00:00',
            'accept_terms' => '1',
            'cart_quantities' => [
                $equipment->id => 2,
            ],
        ]);

    $response->assertRedirect(route('kinventory'));
    $response->assertSessionMissing('cart');

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'available_quantity' => 3,
    ]);

    $this->assertDatabaseHas('lendings', [
        'user_id' => $user->id,
        'status' => 'approved',
        'flag' => false,
    ]);
}

public function test_special_case_checkout_stays_pending_and_does_not_decrement_stock(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Special Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'cart' => [
                $equipment->id => [
                    'equipment_id' => $equipment->id,
                    'description' => $equipment->description,
                    'category' => $equipment->category,
                    'location' => $equipment->location,
                    'available_quantity' => 5,
                    'quantity' => 2,
                ],
            ],
        ])
        ->post(route('cart.checkout'), [
            'pickup_date' => now()->addDay()->format('Y-m-d'),
            'pickup_time' => '09:00:00',
            'accept_terms' => '1',
            'special_case' => '1',
            'return_date' => now()->addDays(3)->format('Y-m-d'),
            'special_reason' => 'Necesito el equipo para una actividad oficial.',
            'cart_quantities' => [
                $equipment->id => 2,
            ],
        ]);

    $response->assertRedirect(route('kinventory'));

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'available_quantity' => 5,
    ]);

    $this->assertDatabaseHas('lendings', [
        'user_id' => $user->id,
        'status' => 'pending',
        'flag' => true,
        'special_reason' => 'Necesito el equipo para una actividad oficial.',
    ]);
}

public function test_cart_blocks_adding_equipment_beyond_ten_different_items(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

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

public function test_deleting_equipment_with_active_lending_marks_pending_deletion(): void
{
    $admin = User::factory()->create([
        'role' => 'Administrador de Inventario',
        'status' => 'Activo',
    ]);

    $borrower = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $equipment = Equipment::create([
        'description' => 'Historical Equipment',
        'category' => 'Test',
        'location' => 'Storage',
        'quantity' => 5,
        'available_quantity' => 5,
        'pending_deletion' => false,
    ]);

    $lending = Lending::create([
        'user_id' => $borrower->id,
        'start_time' => now(),
        'end_time' => now()->addHour(),
        'flag' => false,
        'status' => 'active',
    ]);

    \App\Models\LendingItem::create([
        'lending_id' => $lending->id,
        'equipment_id' => $equipment->id,
        'quantity' => 1,
        'item_status' => 'active',
    ]);

    $response = $this->actingAs($admin)
        ->delete(route('equipment.destroy', $equipment->id));

    $response->assertRedirect(route('inventory_management'));

    $this->assertDatabaseHas('equipment', [
        'id' => $equipment->id,
        'available_quantity' => 0,
        'pending_deletion' => true,
    ]);

    $this->assertDatabaseHas('lending_items', [
        'lending_id' => $lending->id,
        'equipment_id' => $equipment->id,
    ]);
}
}
