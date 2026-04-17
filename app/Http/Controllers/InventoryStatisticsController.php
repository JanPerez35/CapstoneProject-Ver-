<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class InventoryStatisticsController extends Controller
{
    private function buildStatisticsQuery(string $type, int $year, int $month)
    {
        $query = DB::table('lending_items')
            ->join('lendings', 'lending_items.lending_id', '=', 'lendings.id')
            ->join('equipment', 'lending_items.equipment_id', '=', 'equipment.id')
            ->selectRaw('equipment.description, CAST(SUM(lending_items.quantity) AS UNSIGNED) as total')
            ->whereYear('lendings.created_at', $year)
            ->groupBy('equipment.description')
            ->orderByDesc('total');

        if ($type === 'monthly') {
            $query->whereMonth('lendings.created_at', $month);
        }

        return $query;
    }

    public function statistics(Request $request)
    {
        $type  = $request->input('type', 'monthly');
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        $allItems = $this->buildStatisticsQuery($type, $year, $month)->get();
        $items = $allItems->take(5);

        $availableYears = DB::table('lendings')
            ->selectRaw('DISTINCT YEAR(created_at) as yr')
            ->whereNotNull('created_at')
            ->pluck('yr')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        if (!in_array((int) date('Y'), $availableYears)) {
            $availableYears[] = (int) date('Y');
            sort($availableYears);
        }

        $topItem    = $allItems->first();
        $totalReqs  = $allItems->sum('total');
        $totalItems = $allItems->count();

        return view('inventory_management.inventory_statistics', compact(
            'items', 'availableYears', 'type', 'year', 'month',
            'topItem', 'totalReqs', 'totalItems'
        ));
    }

    public function exportStatistics(Request $request)
    {
        $type   = $request->input('type', 'monthly');
        $year   = (int) $request->input('year', date('Y'));
        $month  = (int) $request->input('month', date('n'));
        $format = $request->input('format', 'csv');

        $items = $this->buildStatisticsQuery($type, $year, $month)->get();

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        $periodLabel = $type === 'annual'
            ? "Anual - {$year}"
            : "{$monthNames[$month]} {$year}";

        if ($format === 'csv') {
            $lines   = ["Objeto,Pedidos"];
            foreach ($items as $item) {
                $lines[] = "\"{$item->description}\",{$item->total}";
            }
            $content  = implode("\n", $lines);
            $filename = "reporte_inventario_{$type}_{$year}.csv";
            $mime     = 'text/csv';

            return response($content, 200)
                ->header('Content-Type', $mime . '; charset=utf-8')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
        }

        elseif ($format === 'pdf') {

            $pdf = Pdf::loadView('pdfs.statistics_pdf', [
                'items' => $items,
                'type' => $type,
                'year' => $year,
                'month' => $month,
                'periodLabel' => $periodLabel,
            ]);

            return $pdf->download("reporte_inventario_{$type}_{$year}.pdf");
        }

    // fallback (por si acaso)
        abort(404);

        return response($content, 200)
            ->header('Content-Type', $mime . '; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}