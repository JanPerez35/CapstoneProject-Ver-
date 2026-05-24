<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

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

    public function test_only_super_admin_can_view_access_logs(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('access_logs'))
            ->assertOk();

        foreach (['Usuario', 'Administrador de Inventario', 'Administrador de Instalaciones', 'Administrador de Mercado'] as $role) {
            $user = User::factory()->create([
                'role' => $role,
                'status' => 'Activo',
            ]);

            $this->actingAs($user)
                ->get(route('access_logs'))
                ->assertForbidden();
        }
    }

    public function test_access_logs_are_filtered_by_role_event_and_date(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'role' => 'Super Administrador',
            'action' => 'Agregar equipo',
            'ip_address' => '2001:0db8:85a3:0000:0000:8a2e:0370:7334',
            'comment' => 'Se agrego el equipo: Balon de prueba (ID: 1)',
            'created_at' => '2026-05-24 10:00:00',
        ]);

        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'role' => 'Administrador de Inventario',
            'action' => 'Actualizar equipo',
            'ip_address' => '127.0.0.1',
            'comment' => 'Se actualizo el equipo: Raqueta de prueba (ID: 2)',
            'created_at' => '2026-05-23 10:00:00',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('access_logs', [
            'role' => 'Super Administrador',
            'event' => 'Agregar equipo',
            'date' => '2026-05-24',
        ]));

        $response->assertOk();

        $logs = $response->viewData('logs');

        $this->assertCount(1, $logs);
        $this->assertSame('Agregar equipo', $logs->first()->action);
        $this->assertSame('Super Administrador', $logs->first()->role);
        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $logs->first()->ip_address);
    }

    public function test_access_logs_csv_export_respects_active_filters(): void
    {
        $superAdmin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'role' => 'Super Administrador',
            'action' => 'Agregar equipo',
            'ip_address' => '10.0.0.1',
            'comment' => 'Visible filtered log',
            'created_at' => now(),
        ]);

        ActivityLog::create([
            'user_id' => $superAdmin->id,
            'role' => 'Administrador de Mercado',
            'action' => 'Resolver reporte',
            'ip_address' => '10.0.0.2',
            'comment' => 'Hidden unfiltered log',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($superAdmin)->get(route('access_logs.export-csv', [
            'role' => 'Super Administrador',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Visible filtered log', $csv);
        $this->assertStringNotContainsString('Hidden unfiltered log', $csv);
    }
}
