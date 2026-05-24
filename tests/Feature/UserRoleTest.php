<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_another_users_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Administrador de Inventario',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Rol actualizado correctamente',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'Administrador de Inventario',
        ]);
    }

    public function test_super_admin_cannot_change_own_role(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $this->actingAs($admin)
            ->put("/users/{$admin->id}/role", [
                'role' => 'Usuario',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'Super Administrador',
        ]);
    }

    public function test_invalid_role_value_is_rejected(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $this->actingAs($admin)
            ->put("/users/{$user->id}/role", [
                'role' => 'Root',
            ])
            ->assertSessionHasErrors(['role']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'Usuario',
        ]);
    }
}
