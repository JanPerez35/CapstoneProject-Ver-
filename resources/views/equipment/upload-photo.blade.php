{{-- 
    Equipment Photo Upload View

    Displays the photo upload page for a specific equipment record.

    Responsibilities:
    - Show basic equipment information for identification
    - Display success feedback after a photo is uploaded
    - Display validation or upload errors
    - Show the current equipment photo if one exists
    - Provide a form for uploading a new equipment photo

    Expected data:
    - $equipment: equipment record containing id, category, description,
      and optional equipment_photo_url
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    {{-- Page title shown in the browser tab --}}
    <title>Upload Equipment Photo</title>
</head>
<body>
    {{-- Page heading identifying the selected equipment record --}}
    <h1>Upload Photo for Equipment #{{ $equipment->id }}</h1>

    {{-- Equipment summary section --}}
    <p><strong>Category:</strong> {{ $equipment->category }}</p>
    <p><strong>Description:</strong> {{ $equipment->description }}</p>

    {{-- Success message displayed after a successful upload --}}
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    {{-- Validation or upload error messages --}}
    @if($errors->any())
        <div style="color: red;">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- Current equipment photo preview, if one already exists --}}
    @if($equipment->equipment_photo_url)
        <p>Current photo:</p>
        <img src="{{ asset('storage/' . $equipment->equipment_photo_url) }}" alt="Equipment Photo" width="200">
    @else
        {{-- Fallback message when no photo has been uploaded yet --}}
        <p>No photo uploaded yet.</p>
    @endif

    {{--
        Photo upload form

        Allows the user to:
        - Select an image file from their device
        - Submit the file to the equipment photo upload route

        Notes:
        - Uses POST method
        - Uses multipart/form-data because a file is being uploaded
        - Includes CSRF protection
    --}}
    <form action="{{ route('equipment.uploadPhoto', $equipment->id) }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- File input for the new equipment photo --}}
        <input type="file" name="equipment_photo" required>

        {{-- Submit button to upload the selected photo --}}
        <button type="submit">Upload Photo</button>
    </form>
</body>
</html>