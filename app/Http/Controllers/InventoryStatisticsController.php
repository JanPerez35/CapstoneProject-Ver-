<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Class InventoryStatisticsController
 *
 * Handles all inventory statistics-related actions within the application.
 *
 * Responsibilities:
 * - generating inventory usage statistics
 * - filtering statistics by month or year
 * - retrieving available years for reports
 * - exporting statistics in CSV and PDF formats
 */
class InventoryStatisticsController extends Controller
{
    /**
     * Builds the base query for inventory statistics.
     *
     * Retrieves lending item quantities grouped by equipment description
     * and filtered by year. If the selected type is monthly, it also
     * filters the results by month.
     */
    private function buildStatisticsQuery(string $type, int $year, int $month)
    {
        $query = DB::table('lending_items')
            ->join('lendings', 'lending_items.lending_id', '=', 'lendings.id')
            ->join('equipment', 'lending_items.equipment_id', '=', 'equipment.id')
            ->selectRaw('equipment.description, CAST(SUM(lending_items.quantity) AS UNSIGNED) as total')
            ->whereYear('lendings.created_at', $year)
            ->groupBy('equipment.description')
            ->orderByDesc('total');

        // Filter by month if the selected type is monthly
        if ($type === 'monthly') {
            $query->whereMonth('lendings.created_at', $month);
        }

        return $query;
    }

    /**
     * Displays the inventory statistics view.
     *
     * Retrieves inventory statistics based on the selected period,
     * calculates summary values, and returns the statistics view.
     *
     * Also retrieves the available years for filtering and limits
     * the displayed items to the top 5 most requested.
     */
    public function statistics(Request $request)
    {
        $type  = $request->input('type', 'monthly');
        $year  = (int) $request->input('year', date('Y'));
        $month = (int) $request->input('month', date('n'));

        // Get all statistics and keep only the top 5 for display
        $allItems = $this->buildStatisticsQuery($type, $year, $month)->get();
        $items = $allItems->take(5);

        // Get all available years from lending records
        $availableYears = DB::table('lendings')
            ->selectRaw('DISTINCT YEAR(created_at) as yr')
            ->whereNotNull('created_at')
            ->pluck('yr')
            ->filter()
            ->sort()
            ->values()
            ->toArray();

        // Add the current year if it is not already included
        if (!in_array((int) date('Y'), $availableYears)) {
            $availableYears[] = (int) date('Y');
            sort($availableYears);
        }

        // Calculate summary statistics
        $topItem    = $allItems->first();
        $totalReqs  = $allItems->sum('total');
        $totalItems = $allItems->count();

        // Return statistics data to the view
        return view('inventory_management.inventory_statistics', compact(
            'items', 'availableYears', 'type', 'year', 'month',
            'topItem', 'totalReqs', 'totalItems'
        ));
    }

    /**
     * Exports the inventory statistics report.
     *
     * Retrieves inventory statistics based on the selected period
     * and exports the report in CSV or PDF format.
     *
     * If the selected format is not valid, the request is aborted.
     */
    public function exportStatistics(Request $request)
    {
        $type   = $request->input('type', 'monthly');
        $year   = (int) $request->input('year', date('Y'));
        $month  = (int) $request->input('month', date('n'));
        $format = $request->input('format', 'csv');

        // Get statistics data for export
        $items = $this->buildStatisticsQuery($type, $year, $month)->get();

        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];

        // Build the label for the selected period
        $periodLabel = $type === 'annual'
            ? "Anual - {$year}"
            : "{$monthNames[$month]} {$year}";

        // Export as CSV
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

        // Export as PDF
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

        // Abort if the format is invalid
        abort(404);
    }
}