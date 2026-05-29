<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\FacilityCost;
use App\Models\FacilityCostReportItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FacilityCostTest extends TestCase
{
    use RefreshDatabase;

    public function test_facility_pdf_export_route_loads(): void
    {
        $admin = User::factory()->create([
            'role' => 'Administrador de Instalaciones',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('facility.export.pdf'));

        $response->assertOk();
    }

    public function test_normal_user_cannot_export_facility_csv(): void
{
    $user = User::factory()->create([
        'role' => 'Usuario',
    ]);

    $response = $this->actingAs($user)->get('/facility_management/export/csv');

    $response->assertForbidden();
}

public function test_super_admin_can_export_facility_csv(): void
{
    $admin = User::factory()->create([
        'role' => 'Administrador de Instalaciones',
    ]);

    $response = $this->actingAs($admin)->get('/facility_management/export/csv');

    $response->assertOk();
}

    public function test_custom_day_modification_uses_its_own_duration_instead_of_parent_rate_mode(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
        ]);

        FacilityCost::create([
            'classroom_name' => 'Cancha Test',
            'pending_deletion' => false,
            'supply_cost' => 1,
            'electricity_cost' => 0,
            'water_cost' => 0,
            'classroom_space' => 100,
            'daily_cost_1' => 1,
            'daily_cost_2' => 2,
            'daily_cost_3' => 3,
            'weekly_cost_1' => 5,
            'weekly_cost_2' => 6,
            'weekly_cost_3' => 7,
            'monthly_cost_1' => 10,
            'monthly_cost_2' => 20,
            'monthly_cost_3' => 30,
        ]);

        $parentResponse = $this->actingAs($admin)->postJson(route('facility.events.store'), [
            'classroom' => 'Cancha Test',
            'event_date' => '2026-06-01',
            'event_end_date' => '2026-06-30',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'description' => 'Evento mensual de prueba',
            'responsible' => 'Persona Prueba',
            'period_type' => 'workday',
            'rate_mode' => 'monthly',
            'services' => ['utilities'],
        ]);

        $parentResponse->assertCreated();

        $parent = FacilityCostReportItem::findOrFail($parentResponse->json('item_id'));

        $this->assertSame('monthly', $parent->rate_mode);
        $this->assertEquals(1060.00, (float) $parent->calculated_cost);

        $this->actingAs($admin)->postJson(route('facility.events.customize-days', $parent), [
            'scope' => 'single_day',
            'date' => '2026-06-08',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'period_type' => 'non_workday_saturday',
        ])->assertCreated();

        $customDay = FacilityCostReportItem::where('custom_parent_item_id', $parent->id)->firstOrFail();
        $parent->refresh();

        $this->assertSame('daily', $customDay->rate_mode);
        $this->assertEquals(202.00, (float) $customDay->calculated_cost);
        $this->assertEquals(35.33, (float) $customDay->parent_deducted_cost);
        $this->assertEquals(1024.67, (float) $parent->calculated_cost);

        $this->actingAs($admin)->putJson(route('facility.events.sub-event.update', $customDay), [
            'classroom' => 'Cancha Test',
            'event_date' => '2026-06-08',
            'event_end_date' => '2026-06-08',
            'start_time' => '08:00',
            'end_time' => '11:00',
            'description' => 'Evento mensual de prueba',
            'responsible' => 'Persona Prueba',
            'period_type' => 'non_workday_saturday',
            'rate_mode' => 'monthly',
            'services' => ['utilities'],
        ])->assertOk();

        $customDay->refresh();
        $parent->refresh();

        $this->assertSame('daily', $customDay->rate_mode);
        $this->assertEquals(203.00, (float) $customDay->calculated_cost);
        $this->assertEquals(35.33, (float) $customDay->parent_deducted_cost);
        $this->assertEquals(1024.67, (float) $parent->calculated_cost);
    }

    public function test_parent_edit_reapplies_existing_custom_day_deductions(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
        ]);

        FacilityCost::create([
            'classroom_name' => 'CM 204',
            'pending_deletion' => false,
            'supply_cost' => 1,
            'electricity_cost' => 0,
            'water_cost' => 0,
            'classroom_space' => 100,
            'daily_cost_1' => 1,
            'daily_cost_2' => 2,
            'daily_cost_3' => 3,
            'weekly_cost_1' => 5,
            'weekly_cost_2' => 6,
            'weekly_cost_3' => 7,
            'monthly_cost_1' => 10,
            'monthly_cost_2' => 20,
            'monthly_cost_3' => 30,
        ]);

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $parentResponse = $this->actingAs($admin)->postJson(route('facility.events.store'), [
            'classroom' => 'CM 204',
            'event_date' => $today,
            'event_end_date' => $tomorrow,
            'start_time' => '08:30',
            'end_time' => '10:00',
            'description' => 'prueba costo pocos dias',
            'responsible' => 'Kevin Pratts Garcia',
            'period_type' => 'workday',
            'rate_mode' => 'daily',
            'services' => ['utilities'],
        ]);

        $parentResponse->assertCreated();

        $parent = FacilityCostReportItem::findOrFail($parentResponse->json('item_id'));

        $this->actingAs($admin)->postJson(route('facility.events.customize-days', $parent), [
            'scope' => 'single_day',
            'date' => $tomorrow,
            'start_time' => '09:00',
            'end_time' => '10:00',
            'period_type' => 'workday',
        ])->assertCreated();

        $customDay = FacilityCostReportItem::where('custom_parent_item_id', $parent->id)->firstOrFail();

        $this->actingAs($admin)->putJson(route('facility.events.update', $parent), [
            'classroom' => 'CM 204',
            'event_date' => $today,
            'event_end_date' => $tomorrow,
            'start_time' => '08:30',
            'end_time' => '08:45',
            'description' => 'prueba costo pocos dias',
            'responsible' => 'Kevin Pratts Garcia',
            'period_type' => 'workday',
            'rate_mode' => 'daily',
            'services' => ['utilities'],
        ])->assertOk();

        $customDay->refresh();
        $parent->refresh();

        $this->assertEquals(101.00, (float) $customDay->calculated_cost);
        $this->assertEquals(100.25, (float) $customDay->parent_deducted_cost);
        $this->assertEquals(100.25, (float) $parent->calculated_cost);
        $this->assertGreaterThan((float) $parent->calculated_cost, (float) $customDay->calculated_cost);
    }

    public function test_related_area_custom_day_deducts_only_the_modified_day(): void
    {
        $admin = User::factory()->create([
            'role' => 'Super Administrador',
        ]);

        FacilityCost::create([
            'classroom_name' => 'Main Area',
            'pending_deletion' => false,
            'supply_cost' => 1,
            'electricity_cost' => 1,
            'water_cost' => 1,
            'classroom_space' => 100,
            'daily_cost_1' => 1,
            'daily_cost_2' => 2,
            'daily_cost_3' => 3,
            'weekly_cost_1' => 5,
            'weekly_cost_2' => 6,
            'weekly_cost_3' => 7,
            'monthly_cost_1' => 10,
            'monthly_cost_2' => 20,
            'monthly_cost_3' => 30,
        ]);

        FacilityCost::create([
            'classroom_name' => 'Lateral 1',
            'pending_deletion' => false,
            'supply_cost' => 30,
            'electricity_cost' => 75,
            'water_cost' => 37.5,
            'classroom_space' => 4706,
            'daily_cost_1' => 0.21,
            'daily_cost_2' => 0.26,
            'daily_cost_3' => 0.31,
            'weekly_cost_1' => 0.86,
            'weekly_cost_2' => 1.03,
            'weekly_cost_3' => 1.03,
            'monthly_cost_1' => 2.74,
            'monthly_cost_2' => 3.29,
            'monthly_cost_3' => 3.29,
        ]);

        $parentResponse = $this->actingAs($admin)->postJson(route('facility.events.store'), [
            'classroom' => 'Main Area',
            'event_date' => '2027-03-04',
            'event_end_date' => '2027-03-05',
            'start_time' => '07:30',
            'end_time' => '16:30',
            'description' => 'Job Fair 2027 main area',
            'responsible' => 'Ramon Alvarez',
            'period_type' => 'workday',
            'rate_mode' => 'daily',
            'services' => ['utilities', 'electricity', 'water'],
        ]);

        $parentResponse->assertCreated();

        $parent = FacilityCostReportItem::findOrFail($parentResponse->json('item_id'));

        $relatedResponse = $this->actingAs($admin)->postJson(route('facility.events.related', $parent), [
            'classroom' => 'Lateral 1',
            'event_date' => '2027-03-04',
            'event_end_date' => '2027-03-05',
            'start_time' => '07:30',
            'end_time' => '16:30',
            'description' => 'Job Fair 2027 arriba izquierda',
            'responsible' => 'Ramon Alvarez',
            'period_type' => 'workday',
            'rate_mode' => 'daily',
            'services' => ['utilities', 'electricity', 'water'],
        ]);

        $relatedResponse->assertCreated();

        $related = FacilityCostReportItem::findOrFail($relatedResponse->json('item_id'));

        $this->assertEquals(18.0, (float) $related->hours_used);
        $this->assertEquals(4541.52, (float) $related->calculated_cost);

        $this->actingAs($admin)->postJson(route('facility.events.customize-days', $related), [
            'scope' => 'single_day',
            'date' => '2027-03-05',
            'start_time' => '09:15',
            'end_time' => '10:30',
            'period_type' => 'workday',
        ])->assertCreated();

        $customDay = FacilityCostReportItem::where('custom_parent_item_id', $related->id)->firstOrFail();
        $related->refresh();

        $this->assertEquals(1166.39, (float) $customDay->calculated_cost);
        $this->assertEquals(2270.76, (float) $customDay->parent_deducted_cost);
        $this->assertEquals(2270.76, (float) $related->calculated_cost);
    }
}
