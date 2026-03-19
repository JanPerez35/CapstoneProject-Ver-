<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Cost Calculator</title>
</head>
<body>
    <h1>Facility Cost Calculator</h1>

    <button type="button" onclick="enableEdit()">Edit Values</button>

    <form action="{{ route('facility.calculate') }}" method="POST">
        @csrf

        <label>Water Cost:</label>
        <input type="number" step="0.01" name="water_cost" value="{{ $defaultValues['water_cost'] }}" readonly><br><br>

        <label>Electric Cost:</label>
        <input type="number" step="0.01" name="electric_cost" value="{{ $defaultValues['electric_cost'] }}" readonly><br><br>

        <label>Utilities Cost:</label>
        <input type="number" step="0.01" name="utilities_cost" value="{{ $defaultValues['utilities_cost'] }}" readonly><br><br>

        <label>Classroom Daily Price:</label>
        <input type="number" step="0.01" name="classroom_price" value="{{ $defaultValues['classroom_price'] }}" readonly><br><br>

        <label>Number of Days:</label>
        <input type="number" name="days" min="1" required><br><br>

        <button type="submit">Calculate Facility Cost</button>
    </form>

    <script>
        function enableEdit() {
            const inputs = document.querySelectorAll('input[name="water_cost"], input[name="electric_cost"], input[name="utilities_cost"], input[name="classroom_price"]');
            inputs.forEach(input => input.removeAttribute('readonly'));
        }
    </script>
</body>
</html>