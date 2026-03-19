<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Cost Result</title>
</head>
<body>
    <h1>Facility Cost Result</h1>

    <p>Water Cost: ${{ number_format($waterCost, 2) }}</p>
    <p>Electric Cost: ${{ number_format($electricCost, 2) }}</p>
    <p>Utilities Cost: ${{ number_format($utilitiesCost, 2) }}</p>
    <p>Classroom Daily Price: ${{ number_format($classroomDailyPrice, 2) }}</p>
    <p>Days: {{ $days }}</p>

    <h2>Total Facility Cost: ${{ number_format($facilityCost, 2) }}</h2>

    <form action="{{ route('facility.export.csv') }}" method="POST">
        @csrf
        <input type="hidden" name="water_cost" value="{{ $waterCost }}">
        <input type="hidden" name="electric_cost" value="{{ $electricCost }}">
        <input type="hidden" name="utilities_cost" value="{{ $utilitiesCost }}">
        <input type="hidden" name="classroom_price" value="{{ $classroomDailyPrice }}">
        <input type="hidden" name="days" value="{{ $days }}">

        <button type="submit">Export CSV</button>
    </form>
</body>
</html>