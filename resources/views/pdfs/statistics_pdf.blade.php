<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte</title>
    <style>
        body {
            font-family:  "Times New Roman";
        }

        h1 {
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;

        }

        th {
            background: #198754;
            color: white;
        }
    </style>
</head>
<body>

<h1>Reporte de Inventario</h1>
<p>Este reporte fue generado a través de la página de MAIKINE. Representa una lista del equipo deportivo más popular basado en los siguientes criterios.</p>
<p><strong>Tipo:</strong> {{ $type === 'annual' ? 'Anual' : 'Mensual' }}</p>
<p><strong>Período:</strong> {{ $periodLabel }}</p>

<table>
    <thead>
    <tr>
        <th>Rango</th>
        <th>Objeto</th>
        <th>Pedidos</th>
    </tr>
    </thead>
    <tbody>
    @forelse($items as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->total }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="3">Sin datos</td>
        </tr>
    @endforelse
    </tbody>
</table>

</body>
</html>

