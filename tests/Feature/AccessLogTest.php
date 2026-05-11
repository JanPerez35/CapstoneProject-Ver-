<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_store_route_redirects(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
        ]);

        $response = $this->actingAs($admin)->post(route('inventory.store'), [
            'description' => 'Test Equipment',
            'category' => 'Test',
            'location' => 'Test',
            'quantity' => 10,
        ]);

        $response->assertRedirect();
    }
}