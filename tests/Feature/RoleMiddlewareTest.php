<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // =========================================================================
    // Inventory Management Routes
    // Required roles: Super Administrador, Administrador de Inventario
    // =========================================================================

    public function test_market_admin_cannot_access_inventory_management(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('inventory_management'))->assertForbidden();
    }

    public function test_facility_admin_cannot_access_inventory_management(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('inventory_management'))->assertForbidden();
    }

    public function test_super_admin_can_access_inventory_management(): void
    {
        $user = User::factory()->create(['role' => 'Super Administrador', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('inventory_management'))->assertOk();
    }

    public function test_market_admin_cannot_access_borrows_admin_page(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('inventory_management.borrows'))->assertForbidden();
    }

    public function test_facility_admin_cannot_access_borrows_admin_page(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('inventory_management.borrows'))->assertForbidden();
    }

    public function test_market_admin_cannot_approve_borrow_requests(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)
            ->post(route('inventory_management.requests.approve', 999))
            ->assertForbidden();
    }

    public function test_facility_admin_cannot_reject_borrow_requests(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)
            ->post(route('inventory_management.requests.reject', 999))
            ->assertForbidden();
    }

    public function test_market_admin_cannot_mark_equipment_returned(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)
            ->post(route('inventory_management.requests.return', 999))
            ->assertForbidden();
    }

    // =========================================================================
    // Facility Management Routes
    // Required roles: Super Administrador, Administrador de Instalaciones
    // =========================================================================

    public function test_usuario_cannot_access_facility_management(): void
    {
        $user = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility_management'))->assertForbidden();
    }

    public function test_inventory_admin_cannot_access_facility_management(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility_management'))->assertForbidden();
    }

    public function test_market_admin_cannot_access_facility_management(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility_management'))->assertForbidden();
    }

    public function test_facility_admin_can_access_facility_management(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility_management'))->assertOk();
    }

    public function test_inventory_admin_cannot_export_facility_csv(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility.export.csv'))->assertForbidden();
    }

    public function test_market_admin_cannot_export_facility_csv(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('facility.export.csv'))->assertForbidden();
    }

    // =========================================================================
    // Database Backup
    // Required role: Super Administrador only
    // =========================================================================

    public function test_inventory_admin_cannot_download_database_backup(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('database.backup.download'))->assertForbidden();
    }

    public function test_market_admin_cannot_download_database_backup(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Mercado', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('database.backup.download'))->assertForbidden();
    }

    public function test_facility_admin_cannot_download_database_backup(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('database.backup.download'))->assertForbidden();
    }

    // =========================================================================
    // Marketplace Admin Routes (Reports)
    // Required roles: Super Administrador, Administrador de Mercado
    // =========================================================================

    public function test_inventory_admin_cannot_view_reports(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
    }

    public function test_facility_admin_cannot_view_reports(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('reports.index'))->assertForbidden();
    }

    public function test_inventory_admin_cannot_access_reports_data(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);

        $this->actingAs($user)->get(route('reports.data'))->assertForbidden();
    }

    public function test_facility_admin_cannot_resolve_reports(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Instalaciones', 'status' => 'Activo']);
        $report = $this->createPendingReport();

        $this->actingAs($user)
            ->post(route('reports.resolve', $report))
            ->assertForbidden();
    }

    public function test_inventory_admin_cannot_ban_users_via_report(): void
    {
        $user = User::factory()->create(['role' => 'Administrador de Inventario', 'status' => 'Activo']);
        $report = $this->createPendingReport();

        $this->actingAs($user)
            ->post(route('reports.ban', $report))
            ->assertForbidden();
    }

    private function createPendingReport(): UserReport
    {
        $reporter = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);
        $reported = User::factory()->create(['role' => 'Usuario', 'status' => 'Activo']);

        $post = Post::create([
            'user_id' => $reported->id,
            'title' => 'Reported marketplace post',
            'description' => 'Post used for role middleware tests',
            'cost' => 10,
            'category' => 'General',
            'condition' => 'Usado',
            'status' => 'Disponible',
        ]);

        return UserReport::create([
            'user_id' => $reporter->id,
            'reported_user_id' => $reported->id,
            'post_id' => $post->id,
            'report_reason' => 'Spam',
            'description' => 'This report exists so the request reaches authorization.',
            'status' => 'pending',
        ]);
    }
}
