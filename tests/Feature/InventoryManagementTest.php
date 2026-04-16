<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Equipment;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
}