<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Equipment Photo</title>
</head>
<body>
    <h1>Upload Photo for Equipment #{{ $equipment->id }}</h1>

    <p><strong>Category:</strong> {{ $equipment->category }}</p>
    <p><strong>Description:</strong> {{ $equipment->description }}</p>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if($equipment->equipment_photo_url)
        <p>Current photo:</p>
        <img src="{{ asset('storage/' . $equipment->equipment_photo_url) }}" alt="Equipment Photo" width="200">
    @else
        <p>No photo uploaded yet.</p>
    @endif

    <form action="{{ route('equipment.uploadPhoto', $equipment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="equipment_photo" required>
        <button type="submit">Upload Photo</button>
    </form>
</body>
</html>