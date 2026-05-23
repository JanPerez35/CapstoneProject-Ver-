<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermsSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_user_cannot_update_terms_pdf(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
        ]);

        $file = UploadedFile::fake()->create('terms.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('terms.update'), [
                'terms_pdf' => $file,
            ]);

        $response->assertForbidden();
    }

    public function test_super_admin_cannot_upload_non_pdf_as_terms(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
        ]);

        $file = UploadedFile::fake()->create('bad-file.txt', 10, 'text/plain');

        $response = $this->actingAs($admin)
            ->post(route('terms.update'), [
                'terms_pdf' => $file,
            ]);

        $response->assertSessionHasErrors();
    }
}