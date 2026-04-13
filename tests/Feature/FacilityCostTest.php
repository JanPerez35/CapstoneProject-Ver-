<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FacilityCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_pdf_export_route_loads(): void
    {
        $admin = User::factory()->create([
            'role' => 'Admin',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('facility.export.pdf'));

        $response->assertOk();
    }
}