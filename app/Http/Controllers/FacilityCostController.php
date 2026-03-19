<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FacilityCostController extends Controller
{
    public function showForm()
    {
        $defaultValues = [
            'water_cost' => 1000,
            'electric_cost' => 50000,
            'utilities_cost' => 5000,
            'classroom_price' => 220,
        ];

        return view('Facility_Form', compact('defaultValues'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'water_cost' => 'required|numeric|min:0',
            'electric_cost' => 'required|numeric|min:0',
            'utilities_cost' => 'required|numeric|min:0',
            'classroom_price' => 'required|numeric|min:0',
            'days' => 'required|integer|min:1',
        ]);

        $waterCost = (float) $request->water_cost;
        $electricCost = (float) $request->electric_cost;
        $utilitiesCost = (float) $request->utilities_cost;
        $classroomDailyPrice = (float) $request->classroom_price;
        $days = (int) $request->days;

        $facilityCost = $utilitiesCost + $waterCost + $electricCost + ($classroomDailyPrice * $days);

        return view('facility-result', compact(
            'days',
            'waterCost',
            'electricCost',
            'utilitiesCost',
            'classroomDailyPrice',
            'facilityCost'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $request->validate([
            'water_cost' => 'required|numeric|min:0',
            'electric_cost' => 'required|numeric|min:0',
            'utilities_cost' => 'required|numeric|min:0',
            'classroom_price' => 'required|numeric|min:0',
            'days' => 'required|integer|min:1',
        ]);

        $waterCost = (float) $request->water_cost;
        $electricCost = (float) $request->electric_cost;
        $utilitiesCost = (float) $request->utilities_cost;
        $classroomDailyPrice = (float) $request->classroom_price;
        $days = (int) $request->days;

        $facilityCost = $utilitiesCost + $waterCost + $electricCost + ($classroomDailyPrice * $days);

        $fileName = 'facility_report.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $callback = function () use (
            $waterCost,
            $electricCost,
            $utilitiesCost,
            $classroomDailyPrice,
            $days,
            $facilityCost
        ) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Water Cost',
                'Electric Cost',
                'Utilities Cost',
                'Classroom Daily Price',
                'Days',
                'Total Facility Cost',
            ]);

            fputcsv($file, [
                $waterCost,
                $electricCost,
                $utilitiesCost,
                $classroomDailyPrice,
                $days,
                $facilityCost,
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}