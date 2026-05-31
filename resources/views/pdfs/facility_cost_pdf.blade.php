<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Costo de Facilidades</title>

    {{--Styling for the PDF utilizing DomPDF to render the document--}}
    <style>
        /* Base font and text styling for the document */
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            color: #000;
        }

        /* Main report title styling and spacing */
        h1 {
            margin: 0 0 10px 0;
            font-size: 22px;
        }

        /* Paragraph spacing and readability */
        p {
            margin: 0 0 8px 0;
            line-height: 1.4;
        }

        /* Metadata block spacing */
        .report-meta {
            margin-bottom: 15px;
        }

        /* Table layout styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        /* Table cell styling */
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }


        /* Header styling for the report table */
        /*Changes the background and text color within the table*/
        th {
            background: #828282;
            color: white;
            font-weight: bold;
        }

        /* Left-aligned text utility */
        .text-left {
            text-align: left;
        }

        /* Right-aligned text utility */
        .text-right {
            text-align: right;
        }

        /* Emphasizes the final total row */
        .total-row td {
            font-weight: bold;
        }

        /* Allows long text content to wrap properly */
        .text-left {
            text-align: left;
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
        }

        /* Limits width for long descriptions */
        td.text-left {
            max-width: 200px;
        }
    </style>
</head>
<body>

{{--Month names used to format the selected report period--}}
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

    /*Builds the label for the selected report period*/
    $periodLabel = $reportType === 'annual'
        ? $reportYear
        : (($months[$reportMonth] ?? 'Mes no definido') . ' ' . $reportYear);

   /*Builds the area label for the selected filter*/
    $classroomLabel = $filterClassroom === 'all' ? 'Todos las áreas' : $filterClassroom;


    /*Translates internal working period type values into labels*/
    function translatePeriodType($value) {
        return match($value) {
            'workday' => 'Laborable',
            'non_workday_saturday' => 'No laborable sábado',
            'non_workday_sunday_holiday' => 'No laborable domingo o festivo',
            default => $value,
        };
    }

    /*Translates selected service values into labels*/
    function translateServices($services) {
        if (!$services || !is_array($services) || count($services) === 0) {
            return 'Ninguno';
        }


        $translated = array_map(function ($service) {
            return match($service) {
                'utilities' => 'Material de Limpieza',
                'electricity' => 'Electricidad',
                'water' => 'Agua',
                default => ucfirst($service),
            };
        }, $services);


        return implode(', ', $translated);
    }

        /*Translates internal duration rate mode values into labels*/
        function translateRateMode($value) {
        return match($value) {
            'daily' => 'Diario',
            'weekly' => 'Semanal',
            'monthly' => 'Mensual',
            default => $value,
        };
    }
@endphp

{{--Report title--}}
<h1>Reporte de Costos de Instalaciones</h1>

{{--Report description--}}
<p>
    Este reporte fue generado a través de la página de MAIKINE. Representa una lista de los eventos
    registrados y sus costos estimados según el período seleccionado, el áreas y los servicios aplicados.
</p>

{{--Report metadata that connects to backend information--}}
<div class="report-meta">
    <p><strong>Tipo:</strong> {{ $reportType === 'annual' ? 'Anual' : 'Mensual' }}</p>
    <p><strong>Período:</strong> {{ $periodLabel }}</p>
    <p><strong>Área:</strong> {{ $classroomLabel }}</p>
    <p><strong>Total estimado:</strong> ${{ number_format($grandTotal, 2) }}</p>
</div>

{{--Main data table--}}
<table>
    <thead>
    <tr>
        <th>Fecha Inicial del Evento</th>
        <th>Fecha Final del Evento</th>
        <th>Responsable</th>
        <th>Área</th>
        <th>Descripción</th>
        <th>Horario Inicial del Evento</th>
        <th>Horario Final del Evento</th>
        <th>Horas Totales</th>
        <th>Período</th>
        <th>Tipo de tarifa</th>
        <th>Servicios</th>
        <th>Costo</th>
    </tr>
    </thead>
    <tbody>

    {{--Loop through report items and display each event row, utilizies backend objects--}}
    @forelse($items as $item)
        <tr>
            <td>{{ \Carbon\Carbon::parse($item->event_date)->format('m/d/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($item->end_date ?? $item->event_date)->format('m/d/Y') }}</td>
            <td>{{ $item->responsible }}</td>
            <td>{{ $item->facilityCost->classroom_name ?? 'N/A' }}</td>
            <td class="text-left">{{ $item->event_description }}</td>
            <td>{{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}</td>
            <td>{{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}</td>
            <td>{{ number_format($item->hours_used, 2) }}</td>
            <td>{{ translatePeriodType($item->period_type) }}</td>
            <td>{{ translateRateMode($item->rate_mode) }}</td>
            <td>{{ translateServices($item->services) }}</td>
            <td class="text-right">${{ number_format($item->calculated_cost, 2) }}</td>

        </tr>
    @empty
        {{--Fallback when no report data exists--}}
        <tr>
            <td colspan="12">Sin datos</td>
        </tr>
    @endforelse
    </tbody>
    <tfoot>

    {{--Summary row showing the total estimated cost--}}
    <tr class="total-row">
        <td colspan="11" class="text-right">Total estimado del período</td>
        <td class="text-right">${{ number_format($grandTotal, 2) }}</td>
    </tr>
    </tfoot>
</table>
</body>
</html>

