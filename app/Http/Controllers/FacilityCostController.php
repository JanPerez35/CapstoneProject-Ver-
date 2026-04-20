<?php

namespace App\Http\Controllers;

use App\Models\FacilityCost;
use App\Models\FacilityCostReport;
use App\Models\FacilityCostReportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Concerns\LogsActivity;

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

    public function index(Request $request)
    {
        $reportType = $request->input('report_type', '');
        $reportMonth = $request->input('report_month', '');
        $reportYear = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');

        // Only active classrooms for admin actions
        $facilityCosts = FacilityCost::where('pending_deletion', false)
            ->orderBy('classroom_name')
            ->get();

        // All classrooms for report filters/history
        $allFacilityCosts = FacilityCost::orderBy('classroom_name')->get();

        $query = FacilityCostReportItem::with('facilityCost')
            ->orderBy('event_date', 'desc');

        if ($reportType === 'monthly' && !empty($reportYear) && !empty($reportMonth)) {
            $query->whereYear('event_date', (int) $reportYear)
                ->whereMonth('event_date', (int) $reportMonth);
        } elseif ($reportType === 'annual' && !empty($reportYear)) {
            $query->whereYear('event_date', (int) $reportYear);
        }

        if ($filterClassroom !== 'all' && !empty($filterClassroom)) {
            $query->whereHas('facilityCost', function ($q) use ($filterClassroom) {
                $q->where('classroom_name', $filterClassroom);
            });
        }

        $items = $query->get();
        $grandTotal = $items->sum('calculated_cost');

        $minYear = FacilityCostReportItem::selectRaw('MIN(YEAR(event_date)) as min_year')->value('min_year');
        $maxYear = FacilityCostReportItem::selectRaw('MAX(YEAR(event_date)) as max_year')->value('max_year');

        $minYear = $minYear ? (int) $minYear : now()->year;
        $maxYear = $maxYear ? max((int) $maxYear, now()->year + 1) : now()->year + 1;

        return view('facility_management', compact(
            'facilityCosts',
            'allFacilityCosts',
            'items',
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
            'Agregar salón',
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
            'Eliminar/procesar salones',
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
    ]);
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
        $classroomName = $item->facilityCost->classroom_name ?? 'Salón desconocido';
        $eventDate = $item->event_date;
        $endDate = $item->end_date;
        $startTime = $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : 'N/A';
        $endTime = $item->end_time ? \Carbon\Carbon::parse($item->end_time)->format('H:i') : 'N/A';
        $responsible = $item->responsible ?? 'N/A';
        $cost = $item->calculated_cost ?? 0;
        $itemId = $item->id;

        $item->delete();

        $this->logActivity(
            'Eliminar evento de facilidad',
            "Evento eliminado | ID: {$itemId} | Salón: {$classroomName} | Fecha: {$eventDate} | Fecha fin: {$endDate} | Hora: {$startTime}-{$endTime} | Responsable: {$responsible} | Costo: {$cost}"
        );

        return redirect()->route('facility_management')->with('entry_deleted', 'true');
    }

    /**
     * Exports filtered facility cost report items as a CSV file.
     *
     * Applies the same report type, month, year, and classroom filters as
     * the index view, then streams a downloadable CSV with a timestamped filename.
     */
    public function exportCsv(Request $request)
    {
        $reportType = $request->input('report_type', 'monthly');
        $reportMonth = (int) $request->input('report_month', now()->month);
        $reportYear = (int) $request->input('report_year', now()->year);
        $filterClassroom = $request->input('filter_classroom', 'all');

        $query = FacilityCostReportItem::with('facilityCost')
            ->orderBy('event_date', 'desc');

        if ($reportType === 'monthly') {
            $query->whereYear('event_date', $reportYear)
                ->whereMonth('event_date', $reportMonth);
        } else {
            $query->whereYear('event_date', $reportYear);
        }

        if ($filterClassroom !== 'all') {
            $query->whereHas('facilityCost', function ($q) use ($filterClassroom) {
                $q->where('classroom_name', $filterClassroom);
            });
        }

        $items = $query->get();

        $filename = 'facility_costs_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($items) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Fecha',
                'Salon',
                'Hora Inicio',
                'Hora Fin',
                'Periodo',
                'Servicios',
                'Horas',
                'Costo',
                'Descripcion',
                'Responsable',
            ]);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->event_date,
                    $item->facilityCost->classroom_name ?? '',
                    \Carbon\Carbon::parse($item->start_time)->format('H:i'),
                    \Carbon\Carbon::parse($item->end_time)->format('H:i'),
                    $item->period_type,
                    implode(', ', $item->services ?? []),
                    $item->hours_used,
                    $item->calculated_cost,
                    $item->event_description,
                    $item->responsible,
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
        $reportType = $request->input('report_type', 'monthly');
        $reportMonth = (int) $request->input('report_month', now()->month);
        $reportYear = (int) $request->input('report_year', now()->year);
        $filterClassroom = $request->input('filter_classroom', 'all');

        $query = FacilityCostReportItem::with('facilityCost')
            ->orderBy('event_date', 'desc');

        if ($reportType === 'monthly') {
            $query->whereYear('event_date', $reportYear)
                ->whereMonth('event_date', $reportMonth);
        } else {
            $query->whereYear('event_date', $reportYear);
        }

        if ($filterClassroom !== 'all') {
            $query->whereHas('facilityCost', function ($q) use ($filterClassroom) {
                $q->where('classroom_name', $filterClassroom);
            });
        }

        $items = $query->get();
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
