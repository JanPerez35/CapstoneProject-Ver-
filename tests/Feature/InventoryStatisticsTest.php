<?php

namespace Tests\Feature;

use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_inventory_statistics_aggregates_lending_quantities_for_selected_month(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        $basketball = Equipment::create([
            'description' => 'Balon Basketball',
            'category' => 'Deportes',
            'location' => 'Almacen',
            'quantity' => 10,
            'available_quantity' => 10,
            'pending_deletion' => false,
        ]);

        $soccer = Equipment::create([
            'description' => 'Balon Soccer',
            'category' => 'Deportes',
            'location' => 'Almacen',
            'quantity' => 10,
            'available_quantity' => 10,
            'pending_deletion' => false,
        ]);

        $this->createLendingItem($admin, $basketball, 3, '2026-05-10 08:00:00');
        $this->createLendingItem($admin, $basketball, 2, '2026-05-11 08:00:00');
        $this->createLendingItem($admin, $soccer, 4, '2026-04-10 08:00:00');

        $response = $this->actingAs($admin)->get(route('inventory_management.inventory_statistics', [
            'type' => 'monthly',
            'year' => 2026,
            'month' => 5,
        ]));

        $items = $response->viewData('items');

        $response->assertOk();
        $this->assertCount(1, $items);
        $this->assertSame('Balon Basketball', $items->first()->description);
        $this->assertSame(5, (int) $items->first()->total);
        $this->assertSame(5, (int) $response->viewData('totalReqs'));
    }

    public function test_inventory_statistics_csv_export_respects_selected_period(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        $basketball = Equipment::create([
            'description' => 'CSV Basketball',
            'category' => 'Deportes',
            'location' => 'Almacen',
            'quantity' => 10,
            'available_quantity' => 10,
            'pending_deletion' => false,
        ]);

        $soccer = Equipment::create([
            'description' => 'CSV Soccer',
            'category' => 'Deportes',
            'location' => 'Almacen',
            'quantity' => 10,
            'available_quantity' => 10,
            'pending_deletion' => false,
        ]);

        $this->createLendingItem($admin, $basketball, 2, '2026-05-10 08:00:00');
        $this->createLendingItem($admin, $soccer, 4, '2026-04-10 08:00:00');

        $response = $this->actingAs($admin)->get(route('inventory_management.inventory_statistics.export', [
            'type' => 'monthly',
            'year' => 2026,
            'month' => 5,
            'format' => 'csv',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->assertSee('CSV Basketball');
        $response->assertDontSee('CSV Soccer');
    }

    public function test_inventory_statistics_rejects_invalid_export_format(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        $this->actingAs($admin)
            ->get(route('inventory_management.inventory_statistics.export', [
                'format' => 'exe',
            ]))
            ->assertNotFound();
    }

    private function createLendingItem(User $user, Equipment $equipment, int $quantity, string $createdAt): void
    {
        $lending = Lending::create([
            'user_id' => $user->id,
            'start_time' => $createdAt,
            'end_time' => $createdAt,
            'flag' => false,
            'status' => 'returned',
        ]);

        $lending->created_at = $createdAt;
        $lending->updated_at = $createdAt;
        $lending->save();

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => $quantity,
            'item_status' => 'returned',
        ]);
    }
}
