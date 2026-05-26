<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_update_user_status(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $target = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $response = $this->actingAs($user)
        ->put(route('users.updateStatus', $target), [
            'status' => 'Bloqueado',
        ]);

    $response->assertForbidden();
}

public function test_super_admin_can_update_user_status(): void
{
    $admin = User::factory()->create([
        'role' => 'Super Administrador',
        'status' => 'Activo',
    ]);

    $target = User::factory()->create([
        'role' => 'Usuario',
        'status' => 'Activo',
    ]);

    $response = $this->actingAs($admin)
        ->put(route('users.updateStatus', $target), [
            'status' => 'Bloqueado',
        ]);

    $response->assertOk();

    $response->assertJson([
        'success' => true,
        'status' => 'Bloqueado',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $target->id,
        'status' => 'Bloqueado',
    ]);
}

public function test_super_admin_cannot_change_own_status(): void
{
    $admin = User::factory()->create([
        'role' => 'Super Administrador',
        'status' => 'Activo',
    ]);

    $this->actingAs($admin)
        ->put(route('users.updateStatus', $admin), [
            'status' => 'Bloqueado',
        ])
        ->assertForbidden();

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'status' => 'Activo',
    ]);
}
}
