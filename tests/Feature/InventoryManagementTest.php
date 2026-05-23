<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
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
}