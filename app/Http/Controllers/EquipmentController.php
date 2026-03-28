<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\Equipment;

class EquipmentController extends Controller
{
    public function index()
{
    $items = Equipment::paginate(18);

    $categories = Equipment::select('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    return view('inventory_management', compact('items', 'categories'));
}

public function update(Request $request, $id)
{
    $item = Equipment::findOrFail($id);

    $validated = $request->validate([
        'description' => 'required|string|max:255',
        'category' => 'required|string',
        'quantity' => 'required|integer|min:1',
        'available_quantity' => 'required|integer|min:0|lte:quantity',
        'location' => 'required|string',
        'image' => 'nullable|image|mimes:jpg,jpeg|max:2048',
    ]);

    // If new image uploaded
    if ($request->hasFile('image')) {
        // delete old image
        if ($item->equipment_photo_url) {
            \Storage::disk('public')->delete($item->equipment_photo_url);
        }

        $imagePath = $request->file('image')->store('equipment_photos', 'public');
        $item->equipment_photo_url = $imagePath;
    }

    // update fields
    $item->update([
        'description' => $validated['description'],
        'category' => $validated['category'],
        'quantity' => $validated['quantity'],
        'available_quantity' => $validated['available_quantity'],
        'location' => $validated['location'],
    ]);

    return redirect()->route('inventory_management')
        ->with('success', 'Item actualizado correctamente');
}

public function store(Request $request)
{
    $validated = $request->validate([
        'description' => 'required|string|max:255',
        'category' => 'required|string',
        'quantity' => 'required|integer|min:1',
        'available_quantity' => 'required|integer|min:0|lte:quantity',
        'location' => 'required|string',
        'image' => 'required|image|mimes:jpg,jpeg|max:2048',
    ]);

    $imagePath = $request->file('image')->store('equipment_photos', 'public');

    Equipment::create([
        'description' => $validated['description'],
        'category' => $validated['category'],
        'quantity' => $validated['quantity'],
        'available_quantity' => $validated['available_quantity'],
        'location' => $validated['location'],
        'equipment_photo_url' => $imagePath,
    ]);

    return redirect()->route('inventory_management')
        ->with('success', 'Item agregado correctamente');
}

public function destroy($id)
{
    $item = Equipment::findOrFail($id);

    // delete image from storage
    if ($item->equipment_photo_url) {
        \Storage::disk('public')->delete($item->equipment_photo_url);
    }

    $item->delete();

    return redirect()->route('inventory_management')
        ->with('success', 'Item eliminado correctamente');
}
}