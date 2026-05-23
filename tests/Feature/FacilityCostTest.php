<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FacilityCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_pdf_export_route_loads(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Instalaciones',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('facility.export.pdf'));

        $response->assertOk();
    }

    public function test_normal_user_cannot_export_facility_csv(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
    ]);

    $response = $this->actingAs($user)->get('/facility_management/export/csv');

    $response->assertForbidden();
}

public function test_super_admin_can_export_facility_csv(): void
{
    $admin = User::factory()->create([
        'role' => 'Administrador de Instalaciones',
    ]);

    $response = $this->actingAs($admin)->get('/facility_management/export/csv');

    $response->assertOk();
}
}