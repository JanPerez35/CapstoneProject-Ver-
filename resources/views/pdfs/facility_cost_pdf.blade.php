<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Costo de Facilidades</title>


    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #000;
        }


        h1 {
            margin: 0 0 10px 0;
            font-size: 22px;
        }


        p {
            margin: 0 0 8px 0;
            line-height: 1.4;
        }


        .report-meta {
            margin-bottom: 15px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }


        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }


        /*Changes the background and text color within the table*/
        th {
            background: #6FC21F;
            color: white;
            font-weight: bold;
        }


        .text-left {
            text-align: left;
        }


        .text-right {
            text-align: right;
        }


        .total-row td {
            font-weight: bold;
        }
    </style>
</head>
<body>

@php
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


    $periodLabel = $reportType === 'annual'
        ? $reportYear
        : (($months[$reportMonth] ?? 'Mes no definido') . ' ' . $reportYear);


    $classroomLabel = $filterClassroom === 'all' ? 'Todos los salones' : $filterClassroom;


    function translatePeriodType($value) {
        return match($value) {
            'laborable' => 'Laborable',
            'no_laborable_sabado' => 'No laborable sábado',
            'no_laborable_domingo_festivo' => 'No laborable domingo o festivo',
            default => $value,
        };
    }


    function translateServices($services) {
        if (!$services || !is_array($services) || count($services) === 0) {
            return 'Ninguno';
        }


        $translated = array_map(function ($service) {
            return match($service) {
                'utilidades' => 'Utilidades',
                'electricidad' => 'Electricidad',
                'agua' => 'Agua',
                default => ucfirst($service),
            };
        }, $services);


        return implode(', ', $translated);
    }
@endphp


<h1>Reporte de Costos de Facilidades</h1>


<p>
    Este reporte fue generado a través de la página de MAIKINE. Representa una lista de los eventos
    registrados y sus costos estimados según el período seleccionado, el salón y los servicios aplicados.
</p>


<div class="report-meta">
    <p><strong>Tipo:</strong> {{ $reportType === 'annual' ? 'Anual' : 'Mensual' }}</p>
    <p><strong>Período:</strong> {{ $periodLabel }}</p>
    <p><strong>Salón:</strong> {{ $classroomLabel }}</p>
    <p><strong>Total estimado:</strong> ${{ number_format($grandTotal, 2) }}</p>
</div>


<table>
    <thead>
    <tr>
        <th>Fecha</th>
        <th>Salón</th>
        <th>Hora Inicio</th>
        <th>Hora Fin</th>
        <th>Período</th>
        <th>Servicios</th>
        <th>Horas</th>
        <th>Costo</th>
        <th>Descripción</th>
        <th>Responsable</th>
    </tr>
    </thead>
    <tbody>
    @forelse($items as $item)
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->event_date)->format('m/d/Y') }}</td>
            <td>{{ $item->facilityCost->classroom_name ?? 'N/A' }}</td>
            <td>{{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}</td>
            <td>{{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}</td>
            <td>{{ translatePeriodType($item->period_type) }}</td>
            <td class="text-left">{{ translateServices($item->services) }}</td>
            <td>{{ number_format($item->hours_used, 2) }}</td>
            <td class="text-right">${{ number_format($item->calculated_cost, 2) }}</td>
            <td class="text-left">{{ $item->event_description }}</td>
            <td class="text-left">{{ $item->responsable }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="10">Sin datos</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>
    <tr class="total-row">
        <td colspan="9" class="text-right">Total estimado del período</td>
        <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
    </tr>
    </tfoot>
</table>
</body>
</html>

