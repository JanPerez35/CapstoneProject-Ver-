<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
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

    public function test_super_admin_can_download_database_backup_and_activity_is_logged(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $dumpBinary = $this->createFakeDumpBinary(success: true);

        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);
        app()['env'] = 'testing';
        putenv('DB_DUMP_BINARY=' . $dumpBinary);
        $_ENV['DB_DUMP_BINARY'] = $dumpBinary;

        $response = $this->actingAs($admin)
            ->get(route('database.backup.download'));

        $response->assertOk();
        $this->assertStringContainsString(
            'attachment; filename=',
            $response->headers->get('content-disposition')
        );

        $this->assertTrue(
            ActivityLog::where('user_id', $admin->id)
                ->where('action', 'Respaldo de la base de datos')
                ->exists()
        );
    }

    public function test_backup_returns_server_error_when_dump_binary_is_missing(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $missingBinary = storage_path('framework/testing/missing-dump-binary.bat');

        putenv('DB_DUMP_BINARY=' . $missingBinary);
        $_ENV['DB_DUMP_BINARY'] = $missingBinary;

        $this->actingAs($admin)
            ->get(route('database.backup.download'))
            ->assertStatus(500);

        $this->assertFalse(
            ActivityLog::where('user_id', $admin->id)
                ->where('action', 'Respaldo de la base de datos')
                ->exists()
        );
    }

    private function createFakeDumpBinary(bool $success): string
    {
        $path = storage_path('framework/testing/fake-dump.exe');

        File::ensureDirectoryExists(dirname($path));

        if ($success) {
            File::copy('C:\Windows\System32\cmd.exe', $path);
        } else {
            File::put($path, '');
        }

        return $path;
    }
}
