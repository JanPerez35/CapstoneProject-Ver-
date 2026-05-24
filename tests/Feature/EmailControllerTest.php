<?php

namespace Tests\Feature;

use App\Services\EmailService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_email_rejects_incomplete_payload(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $this->actingAs($user)
            ->post('/send-email', [
                'email' => 'student@example.com',
                'subject' => 'Missing message',
            ])
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_send_email_delegates_valid_payload_to_email_service(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $this->mock(EmailService::class, function ($mock) {
            $mock->shouldReceive('send')
                ->once()
                ->with('student@example.com', 'Prueba', 'Mensaje de prueba');
        });

        $this->actingAs($user)
            ->post('/send-email', [
                'email' => 'student@example.com',
                'subject' => 'Prueba',
                'message' => 'Mensaje de prueba',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
