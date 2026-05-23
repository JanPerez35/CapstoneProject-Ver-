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
use Illuminate\Support\Facades\Http;

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
                  ->orWhere('event_date', 'like', "%{$search}%")
                  ->orWhere('end_date', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Displays the facility management view.
     *
     * Retrieves all classrooms and applies optional filters (report type,
     * month, year, classroom) to the cost report items query. Passes the
     * filtered results, grand total, and year range to the view.
     */

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

/**
 * Updates the main facility usage event.
 *
 * Validates the edited event data, recalculates the parent event cost,
 * and updates the main FacilityCostReportItem. If the event has custom-day
 * modifications, this method prevents invalid changes unless the request
 * explicitly confirms that affected custom days should be deleted.
 *
 * Custom-day modifications outside the new date range are removed only when
 * delete_out_of_range_custom_days is true. If the classroom/area changes,
 * existing custom-day modifications are removed only when
 * delete_custom_days_on_area_change is true.
 *
 * @param Request $request
 * @param FacilityCostReportItem $item
 * @return \Illuminate\Http\JsonResponse
 */
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
     * 3. Recalculate parent cost with the new parent values, then reapply
     * any custom-day deductions that still belong to this parent.
     */
    $costData = $this->calculateFacilityCostFromPayload($validated);
    $customDayDeductedTotal = 0;

    if ($item->event_group_id) {
        $customDays = FacilityCostReportItem::where('event_group_id', $item->event_group_id)
            ->where('sub_event_type', 'custom_day')
            ->where('custom_parent_item_id', $item->id)
            ->get();

        foreach ($customDays as $customDay) {
            $newParentDeduction = $this->calculateParentDeductionForCustomPeriod(
                $item,
                Carbon::parse($customDay->event_date)->toDateString(),
                Carbon::parse($customDay->end_date ?? $customDay->event_date)->toDateString(),
                (float) $costData['calculated_cost']
            );

            $customDay->update([
                'parent_deducted_cost' => $newParentDeduction,
            ]);

            $customDayDeductedTotal += $newParentDeduction;
        }
    }

    $calculatedCost = max(
        (float) $costData['calculated_cost'] - $customDayDeductedTotal,
        0
    );

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
        'calculated_cost' => $calculatedCost,
    ]);

    $this->logActivity(
        'Editar evento de facilidad',
        "Se editó el evento principal {$item->id}."
    );

    return response()->json([
        'message' => 'Evento actualizado correctamente.',
        'item_id' => $item->id,
        'calculated_cost' => $calculatedCost,
    ]);
}

/**
 * Updates the schedule of an entire event group.
 *
 * Recalculates the parent event using the new start and end times while
 * preserving any custom-day deductions already applied to the parent.
 * This action is intended for modifying the full event schedule from the
 * parent event level.
 *
 * @param Request $request
 * @param FacilityCostReportItem $item
 * @return \Illuminate\Http\JsonResponse
 */
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

/**
 * Calculates how much cost should be deducted from the parent event
 * when creating or updating a custom-day modification.
 *
 * Prorates the parent event's original total across the selected date range.
 * Existing custom-day deductions are added back first because the parent row
 * stores the remaining cost after prior modifications.
 *
 * @param FacilityCostReportItem $parent
 * @param string $customStartDate
 * @param string $customEndDate
 * @return float
 */
private function calculateParentDeductionForCustomPeriod(
    FacilityCostReportItem $parent,
    string $customStartDate,
    string $customEndDate,
    ?float $originalParentCost = null
): float {
    $parentStart = Carbon::parse($parent->event_date)->startOfDay();
    $parentEnd = Carbon::parse($parent->end_date ?? $parent->event_date)->startOfDay();
    $customStart = Carbon::parse($customStartDate)->startOfDay();
    $customEnd = Carbon::parse($customEndDate)->startOfDay();

    $parentDays = $parentStart->diffInDays($parentEnd) + 1;
    $customDays = $customStart->diffInDays($customEnd) + 1;

    if ($parentDays <= 0 || $customDays <= 0) {
        return 0;
    }

    if ($originalParentCost === null) {
        $existingDeductions = FacilityCostReportItem::where('custom_parent_item_id', $parent->id)
            ->where('sub_event_type', 'custom_day')
            ->sum('parent_deducted_cost');

        $originalParentCost = (float) $parent->calculated_cost + (float) $existingDeductions;
    }

    return round(($originalParentCost / $parentDays) * $customDays, 2);
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

/**
 * Updates a sub-event inside an event group.
 *
 * Validates the submitted sub-event data, recalculates its cost, and updates
 * the existing sub-event record. If the sub-event is a custom-day modification,
 * the method also recalculates the amount deducted from the parent event and
 * adjusts the parent cost accordingly.
 *
 * @param Request $request
 * @param FacilityCostReportItem $item
 * @return \Illuminate\Http\JsonResponse
 */
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

        $validated['rate_mode'] = $this->resolveRateModeForDateRange($subStart, $subEnd);
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

/**
 * Resolves the event item that can be customized.
 *
 * Prevents creating a custom-day modification from another existing
 * custom-day modification. Only a main event or valid related event can
 * be used as the customization target.
 *
 * @param FacilityCostReportItem $item
 * @return FacilityCostReportItem
 */
private function getCustomizableTarget(FacilityCostReportItem $item): FacilityCostReportItem
{
    if ($item->sub_event_type === 'custom_day') {
        abort(422, 'No puedes modificar días desde una modificación existente.');
    }

    return $item;
}

/**
 * Calculates facility usage cost from a payload without creating a database record.
 *
 * Resolves the selected classroom, calculates the total hours used across
 * the date range, determines the correct rate based on period type and rate
 * mode, applies selected service costs, and returns the calculated values
 * needed to update or create a report item.
 *
 * @param array $data
 * @return array{
 *     facility_cost_id: int,
 *     start_time: \Illuminate\Support\Carbon,
 *     end_time: \Illuminate\Support\Carbon,
 *     hours_used: float|int,
 *     calculated_cost: float
 * }
 */
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
     * Resolves the billing mode for a date range.
     *
     * Custom-day modifications should be billed by their own duration instead
     * of inheriting the parent event's weekly or monthly mode.
     */
    private function resolveRateModeForDateRange(Carbon $startDate, Carbon $endDate): string
{
    $daysUsed = $startDate->diffInDays($endDate) + 1;

    if ($daysUsed < 7) {
        return 'daily';
    }

    if ($daysUsed < 28) {
        return 'weekly';
    }

    return 'monthly';
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

        $imported = $this->importExternalFacilityEvents($events);

        $this->logActivity(
            'Importar eventos simulados',
            "Se importaron {$imported} evento(s) simulados."
        );

        return redirect()->route('facility_management')
            ->with('mock_imported', "{$imported} evento(s) simulados importados correctamente.");
        }

    /**
     * Imports facility events from EventFlow's export endpoint.
     *
     * The API key and URL are read from config/services.php so the credential
     * stays server-side and never reaches browser JavaScript.
     */
    public function importEventFlowEvents()
    {
        $apiKey = config('services.eventflow.api_key');
        $exportUrl = config('services.eventflow.export_url');

        if (!$apiKey || !$exportUrl) {
            return redirect()->route('facility_management')
                ->with('mock_imported', 'Configura EVENTFLOW_API_KEY y EVENTFLOW_EXPORT_URL antes de importar eventos.');
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                ])
                ->timeout(20)
                ->get($exportUrl);
        } catch (\Throwable $exception) {
            return redirect()->route('facility_management')
                ->with('mock_imported', 'No se pudo conectar con EventFlow. Intenta nuevamente.');
        }

        if (!$response->successful()) {
            return redirect()->route('facility_management')
                ->with('mock_imported', "EventFlow respondió con error {$response->status()}.");
        }

        $events = $this->extractEventFlowEvents($response->json());
        $imported = $this->importExternalFacilityEvents($events);

        $this->logActivity(
            'Importar eventos de EventFlow',
            "Se importaron {$imported} evento(s) desde EventFlow."
        );

        return redirect()->route('facility_management')
            ->with('mock_imported', "{$imported} evento(s) de EventFlow importados correctamente.");
    }

    /**
     * Normalizes EventFlow API response shapes into a plain event list.
     */
    private function extractEventFlowEvents($payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        if (array_is_list($payload)) {
            return $payload;
        }

        foreach (['data', 'events', 'items'] as $key) {
            if (isset($payload[$key]) && is_array($payload[$key])) {
                return $payload[$key];
            }
        }

        return [];
    }

    /**
     * Imports external facility events using the existing cost calculation path.
     */
    private function importExternalFacilityEvents(array $events): int
    {
        $imported = 0;

        foreach ($events as $event) {
            if (!is_array($event) || empty($event['classroom'])) {
                continue;
            }

            $event = $this->normalizeExternalFacilityEvent($event);

            $facilityExists = FacilityCost::where('classroom_name', $event['classroom'])
                ->where('pending_deletion', false)
                ->exists();

            if (!$facilityExists) {
                continue;
            }

            $this->createFacilityReportItemFromPayload($event);
            $imported++;
        }

        return $imported;
    }

    /**
     * Applies defaults for fields EventFlow may not send yet.
     */
    private function normalizeExternalFacilityEvent(array $event): array
    {
        $event['event_end_date'] = $event['event_end_date']
            ?? $event['end_date']
            ?? $event['event_date']
            ?? null;

        $event['description'] = $event['description']
            ?? $event['event_description']
            ?? 'Evento importado desde EventFlow';

        $event['responsible'] = $event['responsible']
            ?? $event['responsable']
            ?? 'EventFlow';

        $event['period_type'] = $event['period_type'] ?? 'workday';
        $event['rate_mode'] = $event['rate_mode'] ?? 'daily';
        $event['services'] = $event['services'] ?? ['utilities', 'electricity', 'water'];

        if (is_string($event['services'])) {
            $decodedServices = json_decode($event['services'], true);
            $event['services'] = is_array($decodedServices)
                ? $decodedServices
                : array_filter(array_map('trim', explode(',', $event['services'])));
        }

        if (empty($event['services'])) {
            $event['services'] = ['utilities', 'electricity', 'water'];
        }

        return $event;
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

    /**
 * Creates a related event under the same event group as the selected item.
 *
 * Validates the related event data, resolves the parent event group, creates
 * a new non-parent FacilityCostReportItem, and marks it as a related_area
 * sub-event. If the parent does not already have a group id, one is generated.
 *
 * @param Request $request
 * @param FacilityCostReportItem $item
 * @return \Illuminate\Http\JsonResponse
 */
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

/**
 * Creates a custom-day schedule modification for an event.
 *
 * Allows an admin to modify either one selected day or the selected day and
 * all following days within the parent event range. The method prevents
 * overlapping custom-day modifications unless force_overwrite is true.
 *
 * The cost originally assigned to the customized period is deducted from
 * the parent event, and the new custom-day item is stored as a sub-event.
 *
 * @param Request $request
 * @param FacilityCostReportItem $item
 * @return \Illuminate\Http\JsonResponse
 */
public function customizeDays(Request $request, FacilityCostReportItem $item)
{
    $validated = $request->validate([
        'scope' => ['required', 'in:single_day,this_and_following'],
        'date' => ['required', 'date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'period_type' => ['required', 'in:workday,non_workday_saturday,non_workday_sunday_holiday'],
        'force_overwrite' => ['nullable', 'boolean'],
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

    if ($validated['period_type'] === 'workday') {
        $cursor = $customStartDate->copy();

        while ($cursor->lte($customEndDate)) {
            if ($cursor->isWeekend()) {
                return response()->json([
                    'message' => 'No puedes usar el período laborable porque el rango seleccionado incluye sábado o domingo.',
                ], 422);
            }

            $cursor->addDay();
        }
    }

    $groupId = $parent->event_group_id ?? (string) Str::uuid();

    if (!$parent->event_group_id) {
        $parent->update([
            'event_group_id' => $groupId,
            'is_group_parent' => true,
        ]);
    }

    $overlappingCustomDays = FacilityCostReportItem::where('event_group_id', $parent->event_group_id)
        ->where('sub_event_type', 'custom_day')
        ->where('custom_parent_item_id', $parent->id)
        ->where(function ($query) use ($customStartDate, $customEndDate) {
            $query->whereDate('event_date', '<=', $customEndDate)
                ->whereDate('end_date', '>=', $customStartDate);
        })
        ->get();

    if ($overlappingCustomDays->isNotEmpty() && !$request->boolean('force_overwrite')) {
        return response()->json([
            'message' => 'Uno o más días seleccionados ya tienen una modificación.',
            'conflicting_count' => $overlappingCustomDays->count(),
        ], 422);
    }

    if ($overlappingCustomDays->isNotEmpty()) {
        foreach ($overlappingCustomDays as $customDay) {
            $this->restoreSubEventCostToParent($parent, $customDay);
            $customDay->delete();
        }

        $parent->refresh();
    }

    $payload = [
        'classroom' => $parent->facilityCost->classroom_name,
        'event_date' => $customStartDate->toDateString(),
        'event_end_date' => $customEndDate->toDateString(),
        'start_time' => $validated['start_time'],
        'end_time' => $validated['end_time'],
        'description' => $parent->event_description,
        'responsible' => $parent->responsible,
        'period_type' => $validated['period_type'],
        'rate_mode' => $this->resolveRateModeForDateRange($customStartDate, $customEndDate),
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

/**
 * Restores a custom-day deduction back to the parent event.
 *
 * Used when a custom-day modification is deleted or overwritten. The amount
 * stored in parent_deducted_cost is added back to the parent event's
 * calculated cost.
 *
 * @param FacilityCostReportItem $parent
 * @param FacilityCostReportItem $subEvent
 * @return void
 */
private function restoreSubEventCostToParent(
    FacilityCostReportItem $parent,
    FacilityCostReportItem $subEvent
): void {
    $amountToRestore = (float) ($subEvent->parent_deducted_cost ?? 0);

    $parent->update([
        'calculated_cost' => (float) $parent->calculated_cost + $amountToRestore,
    ]);
}

/**
 * Gets the parent event for a grouped facility event item.
 *
 * If the given item is already the parent or does not belong to a group,
 * it is returned directly. Otherwise, the method searches for the parent
 * item within the same event_group_id.
 *
 * @param FacilityCostReportItem $item
 * @return FacilityCostReportItem
 */
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
        $reportType      = $request->input('report_type', '');
        $reportMonth     = $request->input('report_month', '');
        $reportYear      = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');
        $filterPeriodType = $request->input('filter_period_type', '');
        $filterRateMode  = $request->input('filter_rate_mode', '');
        $filterServices  = $request->input('filter_services', '');
        $search          = $request->input('search', '');

        $items = $this->buildFilteredQuery($request)->get();

        $grandTotal = $items->sum('calculated_cost');

        $filename = 'facility_costs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $reportTypeLabel = match ($reportType) {
            'monthly' => 'Mensual',
            'annual' => 'Anual',
            default => 'Todos',
        };

        $periodLabel = 'Todos';

        if ($reportType === 'monthly' && $reportMonth && $reportYear) {
            $periodLabel = ($months[(int) $reportMonth] ?? 'Mes no definido') . ' ' . $reportYear;
        } elseif ($reportType === 'annual' && $reportYear) {
            $periodLabel = $reportYear;
        }

        $classroomLabel = $filterClassroom ?: 'Todas las áreas';
        $periodTypeLabel = $filterPeriodType ?: 'Todos';
        $rateModeLabel = $filterRateMode ?: 'Todos';
        $servicesLabel = $filterServices ?: 'Todos';
        $searchLabel = $search ?: 'Sin búsqueda aplicada';

        $callback = function () use (
            $items,
            $grandTotal,
            $reportTypeLabel,
            $periodLabel,
            $classroomLabel,
            $periodTypeLabel,
            $rateModeLabel,
            $servicesLabel,
            $searchLabel
        ) {
            $handle = fopen('php://output', 'w');

            /*
            * UTF-8 BOM.
            * Helps Excel open Spanish accents correctly.
            */
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            /*
            * Friendly report summary.
            */
            fputcsv($handle, ['Reporte de Costos de Instalaciones']);
            fputcsv($handle, ['Generado por', 'MAIKINE']);
            fputcsv($handle, ['Fecha de descarga', now()->format('m/d/Y h:i A')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Filtros aplicados']);
            fputcsv($handle, ['Tipo de informe', $reportTypeLabel]);
            fputcsv($handle, ['Período', $periodLabel]);
            fputcsv($handle, ['Área', $classroomLabel]);
            fputcsv($handle, ['Tipo de período', $periodTypeLabel]);
            fputcsv($handle, ['Tipo de tarifa', $rateModeLabel]);
            fputcsv($handle, ['Servicios', $servicesLabel]);
            fputcsv($handle, ['Búsqueda', $searchLabel]);
            fputcsv($handle, []);

            fputcsv($handle, ['Resumen']);
            fputcsv($handle, ['Cantidad de registros', $items->count()]);
            fputcsv($handle, ['Costo total estimado', '$' . number_format($grandTotal, 2)]);
            fputcsv($handle, []);

            /*
            * Main data table.
            */
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
                    \Carbon\Carbon::parse($item->event_date)->format('m/d/Y'),
                    \Carbon\Carbon::parse($item->end_date ?? $item->event_date)->format('m/d/Y'),
                    $item->responsible,
                    $item->facilityCost->classroom_name ?? 'N/A',
                    $item->event_description,
                    \Carbon\Carbon::parse($item->start_time)->format('h:i A'),
                    \Carbon\Carbon::parse($item->end_time)->format('h:i A'),
                    number_format($item->hours_used, 2),
                    $this->translateCsvPeriodType($item->period_type),
                    $this->translateCsvRateMode($item->rate_mode),
                    $this->translateCsvServices($item->services),
                    '$' . number_format($item->calculated_cost, 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'Total estimado',
                '$' . number_format($grandTotal, 2),
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Translates a stored period type value into a Spanish CSV display label.
     *
     * @param string|null $value
     * @return string
     */
    private function translateCsvPeriodType($value): string
    {
        return match ($value) {
            'workday' => 'Laborable',
            'non_workday_saturday' => 'No laborable sábado',
            'non_workday_sunday_holiday' => 'No laborable domingo o festivo',
            default => $value ?? 'N/A',
        };
    }

    /**
     * Translates a stored rate mode value into a Spanish CSV display label.
     *
     * @param string|null $value
     * @return string
     */
    private function translateCsvRateMode($value): string
    {
        return match ($value) {
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            default => $value ?? 'N/A',
        };
    }

    /**
     * Translates selected service values into Spanish CSV display labels.
     *
     * Accepts either an array of service keys or a JSON-encoded string.
     * Returns a comma-separated list of translated service names.
     *
     * @param array|string|null $services
     * @return string
     */
    private function translateCsvServices($services): string
    {
        if (is_string($services)) {
            $decoded = json_decode($services, true);
            $services = is_array($decoded) ? $decoded : [$services];
        }

        if (!$services || !is_array($services) || count($services) === 0) {
            return 'Ninguno';
        }

        $translated = array_map(function ($service) {
            return match ($service) {
                'utilities' => 'Utilidades',
                'electricity' => 'Electricidad',
                'water' => 'Agua',
                default => ucfirst($service),
            };
        }, $services);

        return implode(', ', $translated);
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
