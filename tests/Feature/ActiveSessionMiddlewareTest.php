<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActiveSessionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_blocked_user_is_logged_out_when_accessing_authenticated_route(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Bloqueado',
        ]);

        $this->actingAs($user)
            ->withSession(['authenticated_role' => 'Usuario'])
            ->get(route('kinventory'))
            ->assertRedirect('/');

        $this->assertGuest();
    }

    public function test_user_is_logged_out_when_role_changed_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'Administrador de Inventario',
            'status' => 'Activo',
        ]);

        $user->update(['role' => 'Usuario']);

        $this->actingAs($user)
            ->withSession(['authenticated_role' => 'Administrador de Inventario'])
            ->get(route('kinventory'))
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
