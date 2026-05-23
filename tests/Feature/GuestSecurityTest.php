<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_download_database_backup(): void
    {
        $response = $this->get(route('database.backup.download'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_facility_export(): void
    {
        $response = $this->get(route('facility.export.csv'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_create_equipment(): void
    {
        $response = $this->post(route('inventory.store'), []);

        $response->assertRedirect();
    }
}