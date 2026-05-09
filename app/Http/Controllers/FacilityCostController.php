<?php

namespace App\Http\Controllers;

use App\Models\FacilityCost;
use App\Models\FacilityCostReport;
use App\Models\FacilityCostReportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\LogsActivity;
use Illuminate\Support\Str;

/**
 * Class FacilityCostController
 *
 * Handles all facility cost management actions within the application.
 *
 * Responsibilities:
 * - displaying and filtering facility cost report items
 * - saving and updating classroom rental rates
 * - creating and removing classroom records
 * - storing individual facility usage events and calculating their cost
 * - importing mock external events for testing
 * - exporting cost reports as CSV or PDF
 */
class FacilityCostController extends Controller
{
    /**
     * Displays the facility management view.
     *
     * Retrieves all classrooms and applies optional filters (report type,
     * month, year, classroom) to the cost report items query. Passes the
     * filtered results, grand total, and year range to the view.
     */

    use LogsActivity;

    /**
     * Builds a filtered FacilityCostReportItem query from the given request params.
     * Used by index, exportCsv, and exportPdf so all three apply identical filters.
     *
     * Handles: report_type/month/year, filter_classroom, filter_period_type,
     * filter_rate_mode, filter_services, and search.
     *
     * filter_period_type, filter_rate_mode, and filter_services accept the Spanish
     * display labels sent by the JS (matching the select option values in the blade).
     */
    private function buildFilteredQuery(Request $request)
    {
        $reportType      = $request->input('report_type', '');
        $reportMonth     = $request->input('report_month', '');
        $reportYear      = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');
        $filterPeriodType = $request->input('filter_period_type', '');
        $filterRateMode  = $request->input('filter_rate_mode', '');
        $filterServices  = $request->input('filter_services', '');
        $search          = $request->input('search', '');

        $periodTypeMap = [
            'Laborable'                        => 'workday',
            'No laborable sábado'              => 'non_workday_saturday',
            'No laborable domingo o festivo'   => 'non_workday_sunday_holiday',
        ];

        $rateModeMap = [
            'Diario'   => 'daily',
            'Semanal'  => 'weekly',
            'Mensual'  => 'monthly',
        ];

        $servicesMap = [
            'Utilidades'   => 'utilities',
            'Electricidad' => 'electricity',
            'Agua'         => 'water',
        ];

        $query = FacilityCostReportItem::with('facilityCost')
            ->orderBy('created_at', 'desc')
            ->orderBy('start_time', 'desc');

        if ($reportType === 'monthly' && !empty($reportYear) && !empty($reportMonth)) {
            $query->whereYear('event_date', (int) $reportYear)
                  ->whereMonth('event_date', (int) $reportMonth);
        } elseif ($reportType === 'annual' && !empty($reportYear)) {
            $query->whereYear('event_date', (int) $reportYear);
        }

        if (!empty($filterClassroom) && $filterClassroom !== 'all') {
            $query->whereHas('facilityCost', function ($q) use ($filterClassroom) {
                $q->where('classroom_name', $filterClassroom);
            });
        }

        if (!empty($filterPeriodType) && isset($periodTypeMap[$filterPeriodType])) {
            $query->where('period_type', $periodTypeMap[$filterPeriodType]);
        }

        if (!empty($filterRateMode) && isset($rateModeMap[$filterRateMode])) {
            $query->where('rate_mode', $rateModeMap[$filterRateMode]);
        }

        if (!empty($filterServices) && isset($servicesMap[$filterServices])) {
            $query->whereJsonContains('services', $servicesMap[$filterServices]);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('responsible', 'like', "%{$search}%")
                  ->orWhere('event_description', 'like', "%{$search}%")
                  ->orWhereHas('facilityCost', function ($q2) use ($search) {
                      $q2->where('classroom_name', 'like', "%{$search}%");
                  });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $reportType      = $request->input('report_type', '');
        $reportMonth     = $request->input('report_month', '');
        $reportYear      = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');

        // Only active classrooms for admin actions
        $facilityCosts = FacilityCost::where('pending_deletion', false)
            ->orderBy('classroom_name')
            ->get();

        // All classrooms for report filters/history
        $allFacilityCosts = FacilityCost::orderBy('classroom_name')->get();

        $items = $this->buildFilteredQuery($request)->get();

        $eventGroups = $items
            ->groupBy(function ($item) {
                return $item->event_group_id ?: 'single-' . $item->id;
            })
            ->map(function ($group) {
                $parent = $group->firstWhere('is_group_parent', true) ?? $group->first();

                $parent->sub_items = $group->values();
                $parent->group_total = $group->sum('calculated_cost');

                return $parent;
            })
            ->values();

        $grandTotal = $items->sum('calculated_cost');

        $minYear = FacilityCostReportItem::selectRaw('MIN(YEAR(event_date)) as min_year')->value('min_year');
        $maxYear = FacilityCostReportItem::selectRaw('MAX(YEAR(event_date)) as max_year')->value('max_year');

        $minYear = $minYear ? (int) $minYear : now()->year;
        $maxYear = $maxYear ? max((int) $maxYear, now()->year + 1) : now()->year + 1;

        return view('facility_management', compact(
            'facilityCosts',
            'allFacilityCosts',
            'items',
            'eventGroups',
            'grandTotal',
            'reportType',
            'reportMonth',
            'reportYear',
            'filterClassroom',
            'minYear',
            'maxYear'
        ));
    }

    /**
     * Saves rental rates for one or more classrooms.
     *
     * Validates the submitted rates and upserts a FacilityCost record
     * for each selected classroom, covering all three period types
     * (workday, Saturday, Sunday/holiday) across daily, weekly, and monthly modes.
     */
    public function saveRates(Request $request)
    {
        $validated = $request->validate([
            'classrooms' => ['required', 'array', 'min:1'],
            'classrooms.*' => ['string'],

            'classroom_space' => ['required', 'numeric', 'min:0'],
            'supply_cost' => ['required', 'numeric', 'min:0'],
            'electricity_cost' => ['required', 'numeric', 'min:0'],
            'water_cost' => ['required', 'numeric', 'min:0'],

            'daily_cost_1' => ['required', 'numeric', 'min:0'],
            'weekly_cost_1' => ['required', 'numeric', 'min:0'],
            'monthly_cost_1' => ['required', 'numeric', 'min:0'],

            'daily_cost_2' => ['required', 'numeric', 'min:0'],
            'weekly_cost_2' => ['required', 'numeric', 'min:0'],
            'monthly_cost_2' => ['required', 'numeric', 'min:0'],

            'daily_cost_3' => ['required', 'numeric', 'min:0'],
            'weekly_cost_3' => ['required', 'numeric', 'min:0'],
            'monthly_cost_3' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($validated['classrooms'] as $classroomName) {
            FacilityCost::updateOrCreate(
                ['classroom_name' => $classroomName],
                [
                    'classroom_space' => $validated['classroom_space'],
                    'supply_cost' => $validated['supply_cost'],
                    'electricity_cost' => $validated['electricity_cost'],
                    'water_cost' => $validated['water_cost'],

                    'daily_cost_1' => $validated['daily_cost_1'],
                    'weekly_cost_1' => $validated['weekly_cost_1'],
                    'monthly_cost_1' => $validated['monthly_cost_1'],

                    'daily_cost_2' => $validated['daily_cost_2'],
                    'weekly_cost_2' => $validated['weekly_cost_2'],
                    'monthly_cost_2' => $validated['monthly_cost_2'],

                    'daily_cost_3' => $validated['daily_cost_3'],
                    'weekly_cost_3' => $validated['weekly_cost_3'],
                    'monthly_cost_3' => $validated['monthly_cost_3'],
                ]
            );
        }

        $this->logActivity(
            'Guardar tarifas de facilidades',
            'Se guardaron/actualizaron tarifas para los salones: ' . implode(', ', $validated['classrooms'])
        );

        return redirect()->route('facility_management')
    ->with('rates_saved', 'Tarifas guardadas correctamente.');
    }

    /**
     * Creates a new classroom record.
     *
     * Validates that the classroom name is unique and between 6–40 characters,
     * then creates a FacilityCost entry with all rates initialized to zero.
     */
    public function storeClassroom(Request $request)
    {
        $validated = $request->validate([
            'classroom_name' => ['required', 'string', 'min:6', 'max:40', 'unique:facility_costs,classroom_name'],
        ]);

        $classroom = FacilityCost::create([
            'classroom_name' => $validated['classroom_name'],
            'classroom_space' => 1,
            'supply_cost' => 0,
            'electricity_cost' => 0,
            'water_cost' => 0,
            'daily_cost_1' => 0,
            'weekly_cost_1' => 0,
            'monthly_cost_1' => 0,
            'daily_cost_2' => 0,
            'weekly_cost_2' => 0,
            'monthly_cost_2' => 0,
            'daily_cost_3' => 0,
            'weekly_cost_3' => 0,
            'monthly_cost_3' => 0,
        ]);

        $this->logActivity(
            'Agregar área',
            "Se agregó el salón: {$classroom->classroom_name} (ID: {$classroom->id})"
        );

        return redirect()
            ->route('facility_management')
            ->with('success', 'Salón agregado correctamente.');
    }

    /**
     * Deletes one or more classroom records.
     *
     * Accepts an array of classroom names and removes all matching
     * FacilityCost entries from the database.
     */
    public function destroyClassrooms(Request $request)
    {
        $validated = $request->validate([
            'classrooms' => ['required', 'array', 'min:1'],
            'classrooms.*' => ['string'],
        ]);

        $classrooms = FacilityCost::whereIn('classroom_name', $validated['classrooms'])->get();

        $deletedClassrooms = [];
        $pendingDeletionClassrooms = [];

        foreach ($classrooms as $classroom) {
            $hasEvents = FacilityCostReportItem::where('facility_cost_id', $classroom->id)->exists();

            if ($hasEvents) {
                $classroom->update([
                    'pending_deletion' => true,
                ]);

                $pendingDeletionClassrooms[] = $classroom->classroom_name;
            } else {
                $deletedClassrooms[] = $classroom->classroom_name;
                $classroom->delete();
            }
        }

        $comments = [];

        if (!empty($deletedClassrooms)) {
            $comments[] = 'Eliminados: ' . implode(', ', $deletedClassrooms);
        }

        if (!empty($pendingDeletionClassrooms)) {
            $comments[] = 'Marcados como pendientes de eliminación: ' . implode(', ', $pendingDeletionClassrooms);
        }

        $this->logActivity(
            'Eliminar área',
            !empty($comments) ? implode(' | ', $comments) : 'No se realizaron cambios'
        );

        return redirect()
            ->route('facility_management')
            ->with('success', 'Salón(es) procesado(s) correctamente.');
    }

    /**
     * Stores a new facility usage event.
     *
     * Validates the submitted event data, delegates cost calculation to
     * createFacilityReportItemFromPayload, and returns a JSON response
     * for API callers or a redirect for browser requests.
     */
    public function storeEvent(Request $request)
    {

    $validated = $request->validate([
        'classroom' => ['required', 'string'],
        'event_date' => ['required', 'date'],
        'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'description' => ['required', 'string', 'min:10', 'max:1000'],
        'responsible' => ['required', 'string', 'min:5', 'max:60'],
        'period_type' => ['required', 'string'],
        'rate_mode' => ['required', 'in:daily,weekly,monthly'],
        'services' => ['required', 'array', 'min:1'],
    ]);

    $payload = [
        'classroom' => $validated['classroom'],
        'event_date' => $validated['event_date'],
        'event_end_date' => $validated['event_end_date'] ?? $validated['event_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'description' => $validated['description'],
        'responsible' => $validated['responsible'],
        'period_type' => $validated['period_type'],
        'rate_mode' => $validated['rate_mode'],
        'services' => $validated['services'],
        'event_group_id' => (string) Str::uuid(),
        'is_group_parent' => true,
    ];

    $item = $this->createFacilityReportItemFromPayload($payload);

    $this->logActivity(
        'Agregar evento de facilidad',
        "Se agregó un evento para el salón {$validated['classroom']} en fecha {$validated['event_date']} con costo calculado de {$item->calculated_cost}"
    );

    if ($request->expectsJson() || $request->is('api/*')) {
        return response()->json([
            'message' => 'Evento guardado correctamente.',
            'item_id' => $item->id,
            'calculated_cost' => $item->calculated_cost,
        ], 201);
    }

    return redirect()->route('facility_management')
        ->with('rental_saved', 'Evento guardado correctamente.');
}

public function updateEvent(Request $request, FacilityCostReportItem $item)
{
    if (!$item->is_group_parent) {
        return response()->json([
            'message' => 'Solo el evento principal puede editarse con esta acción.',
        ], 422);
    }

    $validated = $request->validate([
        'classroom' => ['required', 'string'],
        'event_date' => ['required', 'date'],
        'event_end_date' => ['required', 'date', 'after_or_equal:event_date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'description' => ['required', 'string', 'min:10', 'max:1000'],
        'responsible' => ['required', 'string', 'min:5', 'max:60'],
        'period_type' => ['required', 'string'],
        'rate_mode' => ['required', 'in:daily,weekly,monthly'],
        'services' => ['required', 'array', 'min:1'],

        'delete_out_of_range_custom_days' => ['nullable', 'boolean'],
        'delete_custom_days_on_area_change' => ['nullable', 'boolean'],
    ]);

    $oldClassroom = $item->facilityCost?->classroom_name;
    $newClassroom = $validated['classroom'];
    $areaChanged = $oldClassroom !== $newClassroom;

    $newStart = Carbon::parse($validated['event_date'])->startOfDay();
    $newEnd = Carbon::parse($validated['event_end_date'])->startOfDay();

    /*
     * 1. Delete custom-day modifications outside the new parent date range.
     */
    if ($item->event_group_id) {
        $outOfRangeCustomDays = FacilityCostReportItem::where('event_group_id', $item->event_group_id)
            ->where('sub_event_type', 'custom_day')
            ->where('id', '!=', $item->id)
            ->where(function ($query) use ($newStart, $newEnd) {
                $query->whereDate('event_date', '<', $newStart)
                    ->orWhereDate('event_date', '>', $newEnd);
            })
            ->get();

        if ($outOfRangeCustomDays->isNotEmpty() && !$request->boolean('delete_out_of_range_custom_days')) {
            return response()->json([
                'message' => 'Hay modificaciones fuera del nuevo rango del evento principal.',
                'out_of_range_custom_days' => $outOfRangeCustomDays->count(),
            ], 422);
        }

        if ($outOfRangeCustomDays->isNotEmpty()) {
            foreach ($outOfRangeCustomDays as $customDay) {
                $this->restoreSubEventCostToParent($item, $customDay);
                $customDay->delete();
            }

            $item->refresh();
        }
    }

    /*
     * 2. Delete custom-day modifications when the parent area changes.
     */
    if ($areaChanged && $item->event_group_id) {
        $customDays = FacilityCostReportItem::where('event_group_id', $item->event_group_id)
            ->where('sub_event_type', 'custom_day')
            ->where('id', '!=', $item->id)
            ->get();

        if ($customDays->isNotEmpty() && !$request->boolean('delete_custom_days_on_area_change')) {
            return response()->json([
                'message' => 'Cambiar el área del evento principal eliminará las modificaciones existentes.',
                'area_change_custom_days' => $customDays->count(),
            ], 422);
        }

        if ($customDays->isNotEmpty()) {
            foreach ($customDays as $customDay) {
                $this->restoreSubEventCostToParent($item, $customDay);
                $customDay->delete();
            }

            $item->refresh();
        }
    }

    /*
     * 3. Recalculate parent cost with the new parent values.
     */
    $costData = $this->calculateFacilityCostFromPayload($validated);

    $item->update([
        'facility_cost_id' => $costData['facility_cost_id'],
        'responsible' => $validated['responsible'],
        'event_description' => $validated['description'],
        'period_type' => $validated['period_type'],
        'services' => $validated['services'],
        'rate_mode' => $validated['rate_mode'],
        'start_time' => $costData['start_time'],
        'end_time' => $costData['end_time'],
        'event_date' => $validated['event_date'],
        'end_date' => $validated['event_end_date'],
        'hours_used' => $costData['hours_used'],
        'calculated_cost' => $costData['calculated_cost'],
    ]);

    $this->logActivity(
        'Editar evento de facilidad',
        "Se editó el evento principal {$item->id}."
    );

    return response()->json([
        'message' => 'Evento actualizado correctamente.',
        'item_id' => $item->id,
        'calculated_cost' => $costData['calculated_cost'],
    ]);
}

public function updateEventSchedule(Request $request, FacilityCostReportItem $item)
{
    $validated = $request->validate([
        'start_time' => ['required'],
        'end_time' => ['required'],
    ]);

    $parent = $this->getGroupParent($item);

    if (!$parent->is_group_parent) {
        return response()->json([
            'message' => 'Solo el evento principal puede modificarse con esta acción.',
        ], 422);
    }

    $parentStart = Carbon::parse($parent->event_date)->startOfDay();
    $parentEnd = Carbon::parse($parent->end_date ?? $parent->event_date)->startOfDay();

    $payload = [
        'classroom' => $parent->facilityCost->classroom_name,
        'event_date' => $parentStart->toDateString(),
        'event_end_date' => $parentEnd->toDateString(),
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'description' => $parent->event_description,
        'responsible' => $parent->responsible,
        'period_type' => $parent->period_type,
        'rate_mode' => $parent->rate_mode,
        'services' => $parent->services ?? [],
    ];

    $newCostData = $this->calculateFacilityCostFromPayload($payload);

    $customDayDeductedTotal = FacilityCostReportItem::where('event_group_id', $parent->event_group_id)
        ->where('sub_event_type', 'custom_day')
        ->where('id', '!=', $parent->id)
        ->sum('parent_deducted_cost');

    $parent->update([
        'facility_cost_id' => $newCostData['facility_cost_id'],
        'start_time' => $newCostData['start_time'],
        'end_time' => $newCostData['end_time'],
        'hours_used' => $newCostData['hours_used'],
        'calculated_cost' => max(
            (float) $newCostData['calculated_cost'] - (float) $customDayDeductedTotal,
            0
        ),
    ]);

    $this->logActivity(
        'Modificar horario de evento completo',
        "Se modificó el horario completo del evento principal {$parent->id}."
    );

    return response()->json([
        'message' => 'Evento completo actualizado correctamente.',
        'item_id' => $parent->id,
        'calculated_cost' => $parent->fresh()->calculated_cost,
    ]);
}

private function calculateParentDeductionForCustomPeriod(
    FacilityCostReportItem $parent,
    string $customStartDate,
    string $customEndDate
): float {
    $payload = [
        'classroom' => $parent->facilityCost->classroom_name,
        'event_date' => $customStartDate,
        'event_end_date' => $customEndDate,
        'start_time' => Carbon::parse($parent->start_time)->format('H:i:s'),
        'end_time' => Carbon::parse($parent->end_time)->format('H:i:s'),
        'description' => $parent->event_description,
        'responsible' => $parent->responsible,
        'period_type' => $parent->period_type,
        'rate_mode' => $parent->rate_mode,
        'services' => $parent->services ?? [],
    ];

    $costData = $this->calculateFacilityCostFromPayload($payload);

    return (float) $costData['calculated_cost'];
}

    /**
     * Creates a FacilityCostReportItem from a validated payload.
     *
     * Resolves the classroom's rates, calculates total hours used across
     * the date range, computes the base rental cost and per-hour service costs
     * (utilities, electricity, water), then persists the result under the
     * authenticated user's cost report.
     */
    private function createFacilityReportItemFromPayload(array $data)
{
    $facilityCost = FacilityCost::where('classroom_name', $data['classroom'])
    ->where('pending_deletion', false)
    ->firstOrFail();

    $startDate = Carbon::parse($data['event_date'])->startOfDay();
    $endDate = Carbon::parse($data['event_end_date'] ?? $data['event_date'])->startOfDay();

    $startTime = Carbon::parse($data['start_time']);
    $endTime = Carbon::parse($data['end_time']);

    // If same-day times were entered backwards, treat as invalid
    if ($endTime->lessThanOrEqualTo($startTime)) {
        abort(422, 'La hora de finalización debe ser mayor que la hora de inicio.');
    }

    // Hours per day
    $hoursPerDay = $startTime->diffInMinutes($endTime) / 60;

    // Inclusive days in the range
    $daysUsed = $startDate->diffInDays($endDate) + 1;

    // Total service hours
    $hoursUsed = $hoursPerDay * $daysUsed;

    $rateMode = $data['rate_mode'] ?? 'daily';

    // Selected rate according to period_type + rate_mode
    $rate = $this->getRateByPeriodAndMode(
        $facilityCost,
        $data['period_type'],
        $data['rate_mode']
    );

    // Units according to mode
    $unitsUsed = $this->getUnitsUsed($startDate, $endDate, $data['rate_mode']);

    // Base cost
    $baseCost = $facilityCost->classroom_space * $rate * $unitsUsed;

    // Services by hour
    $servicesCost = 0;

    if (in_array('utilities', $data['services'])) {
        $servicesCost += $facilityCost->supply_cost * $hoursUsed;
    }

    if (in_array('electricity', $data['services'])) {
        $servicesCost += $facilityCost->electricity_cost * $hoursUsed;
    }

    if (in_array('water', $data['services'])) {
        $servicesCost += $facilityCost->water_cost * $hoursUsed;
    }

    $total = $baseCost + $servicesCost;

    $report = FacilityCostReport::firstOrCreate([
        'user_id' => auth()->id() ?? 1,
    ]);

    return FacilityCostReportItem::create([
        'facility_cost_report_id' => $report->id,
        'facility_cost_id' => $facilityCost->id,
        'event_group_id' => $data['event_group_id'] ?? (string) Str::uuid(),
        'is_group_parent' => $data['is_group_parent'] ?? true,
        'sub_event_type' => $data['sub_event_type'] ?? null,
        'responsible' => $data['responsible'],
        'period_type' => $data['period_type'],
        'services' => $data['services'],
        'rate_mode' => $data['rate_mode'],
        'start_time' => $startDate->copy()->setTimeFrom($startTime),
        'end_time' => $endDate->copy()->setTimeFrom($endTime),
        'event_date' => $data['event_date'],
        'end_date' => $data['event_end_date'] ?? $data['event_date'],
        'event_description' => $data['description'],
        'hours_used' => $hoursUsed,
        'calculated_cost' => round($total, 2),
        'parent_deducted_cost' => $data['parent_deducted_cost'] ?? null,
        'custom_parent_item_id' => $data['custom_parent_item_id'] ?? null,
    ]);
}

public function updateSubEvent(Request $request, FacilityCostReportItem $item)
{
    if ($item->is_group_parent) {
        return response()->json([
            'message' => 'El evento principal no puede editarse con esta acción.',
        ], 422);
    }

    $validated = $request->validate([
        'classroom' => ['required', 'string'],
        'event_date' => ['required', 'date'],
        'event_end_date' => ['required', 'date', 'after_or_equal:event_date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'description' => ['required', 'string', 'min:10', 'max:1000'],
        'responsible' => ['required', 'string', 'min:5', 'max:60'],
        'period_type' => ['required', 'string'],
        'rate_mode' => ['required', 'in:daily,weekly,monthly'],
        'services' => ['required', 'array', 'min:1'],
    ]);

    $parent = $item->sub_event_type === 'custom_day' && $item->custom_parent_item_id
        ? FacilityCostReportItem::findOrFail($item->custom_parent_item_id)
        : $this->getGroupParent($item);

    $subStart = Carbon::parse($validated['event_date'])->startOfDay();
    $subEnd = Carbon::parse($validated['event_end_date'])->startOfDay();

    if ($item->sub_event_type === 'custom_day') {
        $parentStart = Carbon::parse($parent->event_date)->startOfDay();
        $parentEnd = Carbon::parse($parent->end_date ?? $parent->event_date)->startOfDay();

        if ($subStart->lt($parentStart) || $subEnd->gt($parentEnd)) {
            return response()->json([
                'message' => 'La modificación debe estar dentro del rango de fechas del evento principal.',
            ], 422);
        }
    }

    $oldParentDeduction = (float) ($item->parent_deducted_cost ?? 0);

    $newCostData = $this->calculateFacilityCostFromPayload($validated);

    $newParentDeduction = null;

    if ($item->sub_event_type === 'custom_day') {
        $newParentDeduction = $this->calculateParentDeductionForCustomPeriod(
            $parent,
            $validated['event_date'],
            $validated['event_end_date']
        );
    }

    $item->update([
        'facility_cost_id' => $newCostData['facility_cost_id'],
        'responsible' => $validated['responsible'],
        'event_description' => $validated['description'],
        'period_type' => $validated['period_type'],
        'services' => $validated['services'],
        'rate_mode' => $validated['rate_mode'],
        'start_time' => $newCostData['start_time'],
        'end_time' => $newCostData['end_time'],
        'event_date' => $validated['event_date'],
        'end_date' => $validated['event_end_date'],
        'hours_used' => $newCostData['hours_used'],
        'calculated_cost' => $newCostData['calculated_cost'],
        'parent_deducted_cost' => $newParentDeduction,
    ]);

    $parent->refresh();

    if ($item->sub_event_type === 'custom_day') {
        $parent->update([
            'calculated_cost' => max(
                (float) $parent->calculated_cost + $oldParentDeduction - (float) $newParentDeduction,
                0
            ),
        ]);
    }

    $this->logActivity(
        'Editar sub-evento de facilidad',
        "Se editó el sub-evento {$item->id} del grupo {$item->event_group_id}."
    );

    return response()->json([
        'message' => 'Sub-evento actualizado correctamente.',
        'item_id' => $item->id,
        'calculated_cost' => $newCostData['calculated_cost'],
    ]);
}

private function getCustomizableTarget(FacilityCostReportItem $item): FacilityCostReportItem
{
    if ($item->sub_event_type === 'custom_day') {
        abort(422, 'No puedes modificar días desde una modificación existente.');
    }

    return $item;
}

private function calculateFacilityCostFromPayload(array $data): array
{
    $facilityCost = FacilityCost::where('classroom_name', $data['classroom'])
        ->where('pending_deletion', false)
        ->firstOrFail();

    $startDate = Carbon::parse($data['event_date'])->startOfDay();
    $endDate = Carbon::parse($data['event_end_date'] ?? $data['event_date'])->startOfDay();

    $startTime = Carbon::parse($data['start_time']);
    $endTime = Carbon::parse($data['end_time']);

    if ($endTime->lessThanOrEqualTo($startTime)) {
        abort(422, 'La hora de finalización debe ser mayor que la hora de inicio.');
    }

    $hoursPerDay = $startTime->diffInMinutes($endTime) / 60;
    $daysUsed = $startDate->diffInDays($endDate) + 1;
    $hoursUsed = $hoursPerDay * $daysUsed;

    $rate = $this->getRateByPeriodAndMode(
        $facilityCost,
        $data['period_type'],
        $data['rate_mode']
    );

    $unitsUsed = $this->getUnitsUsed($startDate, $endDate, $data['rate_mode']);

    $baseCost = $facilityCost->classroom_space * $rate * $unitsUsed;

    $servicesCost = 0;

    if (in_array('utilities', $data['services'])) {
        $servicesCost += $facilityCost->supply_cost * $hoursUsed;
    }

    if (in_array('electricity', $data['services'])) {
        $servicesCost += $facilityCost->electricity_cost * $hoursUsed;
    }

    if (in_array('water', $data['services'])) {
        $servicesCost += $facilityCost->water_cost * $hoursUsed;
    }

    $total = round($baseCost + $servicesCost, 2);

    return [
        'facility_cost_id' => $facilityCost->id,
        'start_time' => $startDate->copy()->setTimeFrom($startTime),
        'end_time' => $endDate->copy()->setTimeFrom($endTime),
        'hours_used' => $hoursUsed,
        'calculated_cost' => $total,
    ];
}

    /**
     * Calculates the number of billing units between two dates for a given rate mode.
     *
     * Returns the count of days, weeks (rounded up), or calendar months crossed,
     * depending on the rate mode (daily, weekly, monthly).
     */
    private function getUnitsUsed(Carbon $startDate, Carbon $endDate, string $rateMode): int
{
    $daysUsed = $startDate->diffInDays($endDate) + 1;

    return match ($rateMode) {
        'daily' => $daysUsed,
        'weekly' => (int) ceil($daysUsed / 7),
        'monthly' => $this->calculateMonthsCrossed($startDate, $endDate),
        default => 1,
    };
}

    /**
     * Counts the number of distinct calendar months spanned by a date range.
     *
     * Both the start and end months are counted, so a range within
     * the same month returns 1.
     */
    private function calculateMonthsCrossed(Carbon $startDate, Carbon $endDate): int
{
    $startMonth = $startDate->copy()->startOfMonth();
    $endMonth = $endDate->copy()->startOfMonth();

    return $startMonth->diffInMonths($endMonth) + 1;
}

    /**
     * Resolves the applicable rate for a classroom based on period type and rate mode.
     *
     * Maps the combination of period type (workday, Saturday, Sunday/holiday)
     * and rate mode (daily, weekly, monthly) to the corresponding cost column
     * on the FacilityCost model. Returns 0 for unknown combinations.
     */
    private function getRateByPeriodAndMode($facilityCost, $periodType, $rateMode)
    {
        return match ($periodType) {
            'workday' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_1,
                'weekly' => (float) $facilityCost->weekly_cost_1,
                'monthly' => (float) $facilityCost->monthly_cost_1,
                default => 0,
            },
            'non_workday_saturday' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_2,
                'weekly' => (float) $facilityCost->weekly_cost_2,
                'monthly' => (float) $facilityCost->monthly_cost_2,
                default => 0,
            },
            'non_workday_sunday_holiday' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_3,
                'weekly' => (float) $facilityCost->weekly_cost_3,
                'monthly' => (float) $facilityCost->monthly_cost_3,
                default => 0,
            },
            default => 0,
        };
    }

    /**
     * Returns mock external events as a JSON response.
     *
     * Reads simulated EventFlow events from storage and exposes them
     * as a JSON endpoint for preview or debugging purposes.
     */
    public function mockExternalEvents()
    {
        $events = $this->getMockExternalEvents();

        return response()->json($events);
    }

    /**
     * Reads and parses mock events from the local JSON file.
     *
     * Looks for storage/app/mock_eventflow_events.json and decodes it.
     * Returns an empty array if the file is missing or contains invalid JSON.
     */
    private function getMockExternalEvents(): array
    {
        $path = storage_path('app/mock_eventflow_events.json');

        if (!file_exists($path)) {
            return [];
        }

        $json = file_get_contents($path);
        $events = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return is_array($events) ? $events : [];
    }

    /**
     * Imports mock external events into the facility cost report.
     *
     * Iterates over simulated EventFlow events, skips any whose classroom
     * does not exist in the database, and creates a report item for each
     * valid event. Redirects with a count of successfully imported events.
     */
    public function importMockEvents()
    {
        $events = $this->getMockExternalEvents();

        $imported = 0;

        foreach ($events as $event) {
            $facilityExists = FacilityCost::where('classroom_name', $event['classroom'])
            ->where('pending_deletion', false)
            ->exists();

            if (!$facilityExists) {
                continue;
            }

            $this->createFacilityReportItemFromPayload($event);
            $imported++;
        }

        $this->logActivity(
            'Importar eventos simulados',
            "Se importaron {$imported} evento(s) simulados."
        );

        return redirect()->route('facility_management')
            ->with('mock_imported', "{$imported} evento(s) simulados importados correctamente.");
        }

    /**
     * Deletes a facility cost report item.
     *
     * Removes the given report item from the database and redirects
     * back to the facility management view with a success message.
     */
    public function destroy(FacilityCostReportItem $item)
    {
        if ($item->is_group_parent && $item->event_group_id) {
            $deleted = FacilityCostReportItem::where('event_group_id', $item->event_group_id)->delete();

            $this->logActivity(
                'Eliminar evento principal de facilidad',
                "Se eliminó el evento principal {$item->id} y todos sus sub-eventos del grupo {$item->event_group_id}. Total eliminado: {$deleted}."
            );

            return redirect()
                ->route('facility_management')
                ->with('entry_deleted', 'Evento principal y sub-eventos eliminados correctamente.');
        }

        $parent = null;

        if ($item->sub_event_type === 'custom_day' && $item->custom_parent_item_id) {
            $parent = FacilityCostReportItem::find($item->custom_parent_item_id);
        } elseif ($item->event_group_id) {
            $parent = FacilityCostReportItem::where('event_group_id', $item->event_group_id)
                ->where('is_group_parent', true)
                ->first();
        }

        $itemId = $item->id;
        $groupId = $item->event_group_id;

        if ($parent && $item->sub_event_type === 'custom_day') {
            $this->restoreSubEventCostToParent($parent, $item);
        }

        $item->delete();

        $this->logActivity(
            'Eliminar sub-evento de facilidad',
            $groupId
                ? "Se eliminó el sub-evento {$itemId} del grupo {$groupId}."
                : "Se eliminó el evento individual {$itemId}."
        );

        return redirect()
            ->route('facility_management')
            ->with('entry_deleted', 'Evento eliminado correctamente.');
    }

    public function storeRelatedEvent(Request $request, FacilityCostReportItem $item)
{
    $validated = $request->validate([
        'classroom' => ['required', 'string'],
        'event_date' => ['required', 'date'],
        'event_end_date' => ['required', 'date', 'after_or_equal:event_date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'description' => ['required', 'string', 'min:10', 'max:1000'],
        'responsible' => ['required', 'string', 'min:5', 'max:60'],
        'period_type' => ['required', 'string'],
        'rate_mode' => ['required', 'in:daily,weekly,monthly'],
        'services' => ['required', 'array', 'min:1'],
    ]);

    $parent = $this->getGroupParent($item);

    $groupId = $parent->event_group_id ?? (string) Str::uuid();

    if (!$parent->event_group_id) {
        $parent->update([
            'event_group_id' => $groupId,
            'is_group_parent' => true,
        ]);
    }

    $payload = [
        'classroom' => $validated['classroom'],
        'event_date' => $validated['event_date'],
        'event_end_date' => $validated['event_end_date'],
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'description' => $validated['description'],
        'responsible' => $validated['responsible'],
        'period_type' => $validated['period_type'],
        'rate_mode' => $validated['rate_mode'],
        'services' => $validated['services'],
        'event_group_id' => $groupId,
        'is_group_parent' => false,
        'sub_event_type' => 'related_area',
    ];

    $newItem = $this->createFacilityReportItemFromPayload($payload);

    $this->logActivity(
        'Crear evento relacionado',
        "Se creó un sub-evento relacionado al grupo {$groupId}."
    );

    return response()->json([
        'message' => 'Evento relacionado creado correctamente.',
        'item_id' => $newItem->id,
    ], 201);
}

public function customizeDays(Request $request, FacilityCostReportItem $item)
{
    $validated = $request->validate([
        'scope' => ['required', 'in:single_day,this_and_following'],
        'date' => ['required', 'date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
    ]);

    $parent = $this->getCustomizableTarget($item);

    $selectedDate = Carbon::parse($validated['date'])->startOfDay();
    $parentStart = Carbon::parse($parent->event_date)->startOfDay();
    $parentEnd = Carbon::parse($parent->end_date ?? $parent->event_date)->startOfDay();

    if ($selectedDate->lt($parentStart) || $selectedDate->gt($parentEnd)) {
        return response()->json([
            'message' => 'La fecha seleccionada debe estar dentro del rango del evento principal.',
        ], 422);
    }

    $customStartDate = $selectedDate;
    $customEndDate = $validated['scope'] === 'this_and_following'
        ? $parentEnd
        : $selectedDate;

    $groupId = $parent->event_group_id ?? (string) Str::uuid();

    if (!$parent->event_group_id) {
        $parent->update([
            'event_group_id' => $groupId,
            'is_group_parent' => true,
        ]);
    }

    $payload = [
        'classroom' => $parent->facilityCost->classroom_name,
        'event_date' => $customStartDate->toDateString(),
        'event_end_date' => $customEndDate->toDateString(),
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'description' => $parent->event_description,
        'responsible' => $parent->responsible,
        'period_type' => $parent->period_type,
        'rate_mode' => $parent->rate_mode,
        'services' => $parent->services ?? [],
        'event_group_id' => $groupId,
        'is_group_parent' => false,
        'sub_event_type' => 'custom_day',
        'custom_parent_item_id' => $parent->id,
    ];

    $parentDeductedCost = $this->calculateParentDeductionForCustomPeriod(
        $parent,
        $customStartDate->toDateString(),
        $customEndDate->toDateString()
    );

    if ($parentDeductedCost > (float) $parent->calculated_cost) {
        return response()->json([
            'message' => 'El costo del período a modificar no puede exceder el costo restante del evento principal.',
        ], 422);
    }

    $payload['parent_deducted_cost'] = $parentDeductedCost;

    $newItem = $this->createFacilityReportItemFromPayload($payload);

    $parent->refresh();

    $parent->update([
        'calculated_cost' => max(
            (float) $parent->calculated_cost - $parentDeductedCost,
            0
        ),
    ]);

    $this->logActivity(
        'Modificar días de evento',
        "Se creó una modificación '{$validated['scope']}' desde {$customStartDate->toDateString()} hasta {$customEndDate->toDateString()} en el grupo {$groupId}."
    );

    return response()->json([
        'message' => 'Modificación creada correctamente.',
        'item_id' => $newItem->id,
    ], 201);
}

private function restoreSubEventCostToParent(
    FacilityCostReportItem $parent,
    FacilityCostReportItem $subEvent
): void {
    $amountToRestore = (float) ($subEvent->parent_deducted_cost ?? 0);

    $parent->update([
        'calculated_cost' => (float) $parent->calculated_cost + $amountToRestore,
    ]);
}

private function getGroupParent(FacilityCostReportItem $item): FacilityCostReportItem
{
    if ($item->is_group_parent || !$item->event_group_id) {
        return $item;
    }

    return FacilityCostReportItem::where('event_group_id', $item->event_group_id)
        ->where('is_group_parent', true)
        ->firstOrFail();
}

    /**
     * Exports filtered facility cost report items as a CSV file.
     *
     * Applies the same report type, month, year, and classroom filters as
     * the index view, then streams a downloadable CSV with a timestamped filename.
     */
    public function exportCsv(Request $request)
    {
        $items = $this->buildFilteredQuery($request)->get();

        $filename = 'facility_costs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Fecha Inicio',
                'Fecha Fin',
                'Responsable',
                'Área',
                'Descripción',
                'Hora Inicio',
                'Hora Fin',
                'Horas',
                'Período',
                'Modo de tarifa',
                'Servicios',
                'Costo',
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->event_date,
                    $item->end_date,
                    $item->responsible,
                    $item->facilityCost->classroom_name ?? '',
                    $item->event_description,
                    \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                    \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                    $item->hours_used,
                    $item->period_type,
                    $item->rate_mode,
                    implode(', ', $item->services ?? []),
                    $item->calculated_cost,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exports filtered facility cost report items as a PDF file.
     *
     * Applies the same filters as the CSV export, renders the
     * facility_cost_pdf view in landscape A4 format, and returns
     * it as a downloadable PDF with a timestamped filename.
     */
    public function exportPdf(Request $request)
    {
        $reportType      = $request->input('report_type', '');
        $reportMonth     = $request->input('report_month', '');
        $reportYear      = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');

        $items = $this->buildFilteredQuery($request)->get();
        $grandTotal = $items->sum('calculated_cost');

        $pdf = Pdf::loadView('pdfs.facility_cost_pdf', compact(
            'items',
            'grandTotal',
            'reportType',
            'reportMonth',
            'reportYear',
            'filterClassroom'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('facility_costs_' . now()->format('Ymd_His') . '.pdf');
    }

}
