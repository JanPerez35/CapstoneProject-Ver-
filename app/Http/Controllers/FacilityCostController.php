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
        $reportType = $request->input('report_type', 'monthly');
        $reportMonth = (int) $request->input('report_month', now()->month);
        $reportYear = (int) $request->input('report_year', now()->year);
        $filterClassroom = $request->input('filter_classroom', 'all');

        $facilityCosts = FacilityCost::orderBy('classroom_name')->get();

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

        return view('facility_management', compact(
            'facilityCosts',
            'items',
            'grandTotal',
            'reportType',
            'reportMonth',
            'reportYear',
            'filterClassroom'
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

                    'lending_certificate_1' => $validated['daily_cost_1'],
                    'lending_certificate_2' => $validated['daily_cost_2'],
                    'lending_certificate_3' => $validated['daily_cost_3'],
                ]
            );
        }

        return redirect()->route('facility_management')
    ->with('rates_saved', 'Tarifas guardadas correctamente.');
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'salon' => ['required', 'string'],
            'fecha' => ['required', 'date'],
            'responsable' => ['required', 'string', 'min:10', 'max:60'],
            'hora_inicio' => ['required'],
            'hora_fin' => ['required'],
            'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
            'tipo_periodo' => ['required', 'string'],
            'servicios' => ['required', 'array', 'min:1'],
        ]);

        $payload = [
            'classroom' => $validated['salon'],
            'event_date' => $validated['fecha'],
            'start_time' => $validated['hora_inicio'],
            'end_time' => $validated['hora_fin'],
            'description' => $validated['descripcion'],
            'responsable' => $validated['responsable'],
            'period_type' => $validated['tipo_periodo'],
            'services' => $validated['servicios'],
        ];

        $this->createFacilityReportItemFromPayload($payload);

        return redirect()->route('facility_management')
    ->with('rental_saved', 'Evento guardado correctamente.');
    }

    // public function storeEvent(Request $request)
    // {
    //     $validated = $request->validate([
    //         'salon' => ['required', 'string'],
    //         'fecha' => ['required', 'date'],
    //         'responsable' => ['required', 'string', 'min:10', 'max:60'],
    //         'hora_inicio' => ['required'],
    //         'hora_fin' => ['required'],
    //         'descripcion' => ['required', 'string', 'min:10', 'max:1000'],
    //         'tipo_periodo' => ['required', 'string'],
    //         'servicios' => ['required', 'array', 'min:1'],
    //     ]);

    //     $facilityCost = FacilityCost::where('classroom_name', $validated['salon'])->firstOrFail();

    //     $start = Carbon::parse($validated['fecha'] . ' ' . $validated['hora_inicio']);
    //     $end = Carbon::parse($validated['fecha'] . ' ' . $validated['hora_fin']);
    //     $hoursUsed = $start->diffInMinutes($end) / 60;

    //     $rate = 0;
    //     if ($validated['tipo_periodo'] === 'laborable') {
    //         $rate = $facilityCost->daily_cost_1;
    //     } elseif ($validated['tipo_periodo'] === 'no_laborable_sabado') {
    //         $rate = $facilityCost->daily_cost_2;
    //     } elseif ($validated['tipo_periodo'] === 'no_laborable_domingo_festivo') {
    //         $rate = $facilityCost->daily_cost_3;
    //     }

    //     $total = $facilityCost->classroom_space * $rate;

    //     if (in_array('utilidades', $validated['servicios'])) {
    //         $total += $facilityCost->supply_cost * $hoursUsed;
    //     }
    //     if (in_array('electricidad', $validated['servicios'])) {
    //         $total += $facilityCost->electricity_cost * $hoursUsed;
    //     }
    //     if (in_array('agua', $validated['servicios'])) {
    //         $total += $facilityCost->water_cost * $hoursUsed;
    //     }

    //     $report = FacilityCostReport::firstOrCreate([
    //         'user_id' => 1,
    //     ]);

    //     FacilityCostReportItem::create([
    //         'facility_cost_report_id' => $report->id,
    //         'facility_cost_id' => $facilityCost->id,
    //         'responsable' => $validated['responsable'],
    //         'period_type' => $validated['tipo_periodo'],
    //         'services' => $validated['servicios'],
    //         'start_time' => $start,
    //         'end_time' => $end,
    //         'event_date' => $validated['fecha'],
    //         'event_description' => $validated['descripcion'],
    //         'hours_used' => $hoursUsed,
    //         'calculated_cost' => round($total, 2),
    //     ]);

    //     return redirect()->route('facility_management')->with('success', 'Evento guardado correctamente.');
    // }

    private function createFacilityReportItemFromPayload(array $data)
    {
        $facilityCost = FacilityCost::where('classroom_name', $data['classroom'])->firstOrFail();

        $start = Carbon::parse($data['event_date'] . ' ' . $data['start_time']);
        $end = Carbon::parse($data['event_date'] . ' ' . $data['end_time']);
        $hoursUsed = $start->diffInMinutes($end) / 60;

        $rate = 0;

        if ($data['period_type'] === 'laborable') {
            $rate = $facilityCost->daily_cost_1;
        } elseif ($data['period_type'] === 'no_laborable_sabado') {
            $rate = $facilityCost->daily_cost_2;
        } elseif ($data['period_type'] === 'no_laborable_domingo_festivo') {
            $rate = $facilityCost->daily_cost_3;
        }

        $total = $facilityCost->classroom_space * $rate;

        if (in_array('utilidades', $data['services'])) {
            $total += $facilityCost->supply_cost * $hoursUsed;
        }

        if (in_array('electricidad', $data['services'])) {
            $total += $facilityCost->electricity_cost * $hoursUsed;
        }

        if (in_array('agua', $data['services'])) {
            $total += $facilityCost->water_cost * $hoursUsed;
        }

        $report = FacilityCostReport::firstOrCreate([
            'user_id' => 1,
        ]);

        return FacilityCostReportItem::create([
            'facility_cost_report_id' => $report->id,
            'facility_cost_id' => $facilityCost->id,
            'responsable' => $data['responsable'],
            'period_type' => $data['period_type'],
            'services' => $data['services'],
            'start_time' => $start,
            'end_time' => $end,
            'event_date' => $data['event_date'],
            'event_description' => $data['description'],
            'hours_used' => $hoursUsed,
            'calculated_cost' => round($total, 2),
        ]);
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
        ));

        return $pdf->download('facility_costs_' . now()->format('Ymd_His') . '.pdf');
    }

}
