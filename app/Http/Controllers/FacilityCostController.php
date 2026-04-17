<?php

namespace App\Http\Controllers;

use App\Models\FacilityCost;
use App\Models\FacilityCostReport;
use App\Models\FacilityCostReportItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class FacilityCostController extends Controller
{
    public function index(Request $request)
    {
        $reportType = $request->input('report_type', '');
        $reportMonth = $request->input('report_month', '');
        $reportYear = $request->input('report_year', '');
        $filterClassroom = $request->input('filter_classroom', '');

        $facilityCosts = FacilityCost::orderBy('classroom_name')->get();

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

        return redirect()->route('facility_management')
    ->with('rates_saved', 'Tarifas guardadas correctamente.');
    }

    public function storeClassroom(Request $request)
    {
        $validated = $request->validate([
            'classroom_name' => ['required', 'string', 'min:6', 'max:40', 'unique:facility_costs,classroom_name'],
        ]);

        FacilityCost::create([
            'classroom_name' => $validated['classroom_name'],
            'classroom_space' => 0,
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

        return redirect()
            ->route('facility_management')
            ->with('success', 'Salón agregado correctamente.');
    }

    public function destroyClassrooms(Request $request)
    {
        $validated = $request->validate([
            'classrooms' => ['required', 'array', 'min:1'],
            'classrooms.*' => ['string'],
        ]);

        FacilityCost::whereIn('classroom_name', $validated['classrooms'])->delete();

        return redirect()
            ->route('facility_management')
            ->with('success', 'Salón(es) eliminado(s) correctamente.');
    }

    // public function storeEvent(Request $request)
    // {
    //     $validated = $request->validate([
    //     'classroom' => ['required', 'string'],
    //     'event_date' => ['required', 'date'],
    //     'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
    //     'start_time' => ['required'],
    //     'end_time' => ['required'],
    //     'description' => ['required', 'string', 'min:10', 'max:1000'],
    //     'responsable' => ['required', 'string', 'min:5', 'max:60'],
    //     'period_type' => ['required', 'string'],
    //     'rate_mode' => ['required', 'in:daily,weekly,monthly'],
    //     'services' => ['required', 'array', 'min:1'],
    // ]);

    //     $payload = [
    //     'classroom' => $validated['classroom'],
    //     'event_date' => $validated['event_date'],
    //     'event_end_date' => $validated['event_end_date'] ?? $validated['event_date'],
    //     'start_time' => $validated['start_time'],
    //     'end_time' => $validated['end_time'],
    //     'description' => $validated['description'],
    //     'responsable' => $validated['responsable'],
    //     'period_type' => $validated['period_type'],
    //     'rate_mode' => $validated['rate_mode'],
    //     'services' => $validated['services'],
    // ];

    //     $this->createFacilityReportItemFromPayload($payload);

    //     return redirect()->route('facility_management')
    // ->with('rental_saved', 'Evento guardado correctamente.');
    // }

    public function storeEvent(Request $request)
    {
    $validated = $request->validate([
        'classroom' => ['required', 'string'],
        'event_date' => ['required', 'date'],
        'event_end_date' => ['nullable', 'date', 'after_or_equal:event_date'],
        'start_time' => ['required'],
        'end_time' => ['required'],
        'description' => ['required', 'string', 'min:10', 'max:1000'],
        'responsable' => ['required', 'string', 'min:5', 'max:60'],
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
        'responsable' => $validated['responsable'],
        'period_type' => $validated['period_type'],
        'rate_mode' => $validated['rate_mode'],
        'services' => $validated['services'],
    ];

    $item = $this->createFacilityReportItemFromPayload($payload);

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

private function createFacilityReportItemFromPayload(array $data)
{
    $facilityCost = FacilityCost::where('classroom_name', $data['classroom'])->firstOrFail();

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

    if (in_array('utilidades', $data['services'])) {
        $servicesCost += $facilityCost->supply_cost * $hoursUsed;
    }

    if (in_array('electricidad', $data['services'])) {
        $servicesCost += $facilityCost->electricity_cost * $hoursUsed;
    }

    if (in_array('agua', $data['services'])) {
        $servicesCost += $facilityCost->water_cost * $hoursUsed;
    }

    $total = $baseCost + $servicesCost;

    $report = FacilityCostReport::firstOrCreate([
        'user_id' => auth()->id() ?? 1,
    ]);

    return FacilityCostReportItem::create([
        'facility_cost_report_id' => $report->id,
        'facility_cost_id' => $facilityCost->id,
        'responsable' => $data['responsable'],
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

private function calculateMonthsCrossed(Carbon $startDate, Carbon $endDate): int
{
    $startMonth = $startDate->copy()->startOfMonth();
    $endMonth = $endDate->copy()->startOfMonth();

    return $startMonth->diffInMonths($endMonth) + 1;
}

    private function getRateByPeriodAndMode($facilityCost, $periodType, $rateMode)
    {
        return match ($periodType) {
            'laborable' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_1,
                'weekly' => (float) $facilityCost->weekly_cost_1,
                'monthly' => (float) $facilityCost->monthly_cost_1,
                default => 0,
            },
            'no_laborable_sabado' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_2,
                'weekly' => (float) $facilityCost->weekly_cost_2,
                'monthly' => (float) $facilityCost->monthly_cost_2,
                default => 0,
            },
            'no_laborable_domingo_festivo' => match ($rateMode) {
                'daily' => (float) $facilityCost->daily_cost_3,
                'weekly' => (float) $facilityCost->weekly_cost_3,
                'monthly' => (float) $facilityCost->monthly_cost_3,
                default => 0,
            },
            default => 0,
        };
    }

    public function mockExternalEvents()
    {
        $events = $this->getMockExternalEvents();

        return response()->json($events);
    }

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

    public function importMockEvents()
    {
        $events = $this->getMockExternalEvents();

        $imported = 0;

        foreach ($events as $event) {
            $facilityExists = FacilityCost::where('classroom_name', $event['classroom'])->exists();

            if (!$facilityExists) {
                continue;
            }

            $this->createFacilityReportItemFromPayload($event);
            $imported++;
        }

        return redirect()->route('facility_management')
    ->with('mock_imported', "{$imported} evento(s) simulados importados correctamente.");
    }

    public function destroy(FacilityCostReportItem $item)
    {
        $item->delete();

        return redirect()->route('facility_management')->with('success', 'Registro eliminado correctamente.');
    }

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
                    $item->responsable,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

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
