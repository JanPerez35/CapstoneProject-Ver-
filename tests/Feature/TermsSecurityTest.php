<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TermsSecurityTest extends TestCase
{
    use RefreshDatabase;

    private ?string $originalTermsPdf = null;
    private string $termsPdfPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->termsPdfPath = public_path('documents/terms_conditions.pdf');
        $this->originalTermsPdf = File::exists($this->termsPdfPath)
            ? File::get($this->termsPdfPath)
            : null;
    }

    protected function tearDown(): void
    {
        if ($this->originalTermsPdf !== null) {
            File::ensureDirectoryExists(dirname($this->termsPdfPath));
            File::put($this->termsPdfPath, $this->originalTermsPdf);
        } elseif (File::exists($this->termsPdfPath)) {
            File::delete($this->termsPdfPath);
        }

        parent::tearDown();
    }

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

    public function test_user_accepting_terms_updates_database_and_logs_activity(): void
    {
        $user = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
            'terms_accepted' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('terms.accept'));

        $response->assertRedirect(route('kinemarket'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'terms_accepted' => true,
        ]);

        $this->assertTrue(
            ActivityLog::where('user_id', $user->id)
                ->where('action', 'like', 'Aceptar%')
                ->exists()
        );
    }

    public function test_super_admin_can_upload_terms_pdf_and_reset_acceptances(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'Super Administrador',
            'status' => 'Activo',
            'terms_accepted' => true,
        ]);

        $acceptedUser = User::factory()->create([
            'role' => 'Usuario',
            'status' => 'Activo',
            'terms_accepted' => true,
        ]);

        $pdf = UploadedFile::fake()->create('new-terms.pdf', 100, 'application/pdf');

        $response = $this->actingAs($admin)
            ->post(route('terms.update'), [
                'terms_pdf' => $pdf,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('terms_updated_success');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'terms_accepted' => false,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $acceptedUser->id,
            'terms_accepted' => false,
        ]);

        $this->assertTrue(File::exists($this->termsPdfPath));
        $this->assertTrue(
            ActivityLog::where('user_id', $admin->id)
                ->where('action', 'like', 'Actualizar%')
                ->exists()
        );
    }
}
