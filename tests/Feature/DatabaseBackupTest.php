<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_download_database_backup(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('database.backup.download'));

        $response->assertForbidden();
    }

    public function test_inventory_admin_cannot_download_database_backup(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('database.backup.download'));

        $response->assertForbidden();
    }

    public function test_facility_admin_cannot_download_database_backup(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Instalaciones',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('database.backup.download'));

        $response->assertForbidden();
    }
}