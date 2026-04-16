<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte</title>

    {{--Styling for the PDF utilizing DomPDF to render the document--}}
    <style>
        /* Base font for the document */
        body {
            font-family:  "Times New Roman";
        }

        /* Main report title spacing */
        h1 {
            margin-bottom: 10px;
        }
        /* Table layout styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        /* Table cell styling */
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;

        }

        /* Header styling (matches system green theme) */
        th {
            background: #198754;
            color: white;
        }
    </style>
</head>
<body>

{{-- Report title --}}
<h1>Reporte de Inventario</h1>

{{-- Report description --}}
<p>
    Este reporte fue generado a través de la plataforma MAIKINE. Representa una lista del equipo deportivo más popular basado en los siguientes criterios.
</p>

{{-- Report metadata --}}
<p><strong>Tipo:</strong> {{ $type === 'annual' ? 'Anual' : 'Mensual' }}</p>
<p><strong>Período:</strong> {{ $periodLabel }}</p>

{{-- Main data table --}}
<table>
    <thead>
    <tr>
        <th>Rango</th>
        <th>Objeto</th>
        <th>Pedidos</th>
    </tr>
    </thead>
    <tbody>

    {{-- Loop through items ranked by popularity --}}
    @forelse($items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->total }}</td>
        </tr>
    @empty
        {{-- Fallback when no data exists --}}
        <tr>
            <td colspan="3">Sin datos</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>
