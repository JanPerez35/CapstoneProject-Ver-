<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;

class EquipmentController extends Controller
{

    public function index(Request $request)
    {

        $query = Equipment::where('pending_deletion', false)
        ->withCount([
            'lendingItems as open_lendings_count' => function ($q) {
                $q->whereHas('lending', function ($lendingQuery) {
                    $lendingQuery->whereIn('status', ['pending', 'approved', 'active']);
                });
            }
        ]);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // NOW paginate (after filters)
        $items = $query->paginate(18)->withQueryString();

        // Categories list
        $categories = Equipment::where('pending_deletion', false)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Locations list
        $locations = Equipment::where('pending_deletion', false)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view('inventory_management.admin_inventory', compact('items', 'categories', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $item = Equipment::findOrFail($id);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:5', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]+$/'],
            'category' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]+$/'],
            'quantity' => 'required|integer|min:1',
            'available_quantity' => 'required|integer|min:0|lte:quantity',
            'location' => ['required', 'string', 'min:5', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-\/]+$/'],
            'image' => 'nullable|image|mimes:jpg,jpeg|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->equipment_photo_url) {
                \Storage::disk('public')->delete($item->equipment_photo_url);
            }

            $imagePath = $request->file('image')->store('equipment_photos', 'public');
            $item->equipment_photo_url = $imagePath;
        }

        $item->update([
            'description' => $validated['description'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'available_quantity' => $validated['available_quantity'],
            'location' => $validated['location'],
        ]);

        return redirect()->back()
            ->with('success', 'Item actualizado correctamente');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[A-Za-z0-9\s\.,\-]+$/'
            ],
            'category' => [
                'required',
                'string',
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9\s\.,\-]+$/'
            ],
            'location' => [
                'required',
                'string',
                'min:5',
                'max:100',
                'regex:/^[A-Za-z0-9\s\.,\-\/]+$/'
            ],
            'quantity' => 'required|integer|min:1',
            'available_quantity' => 'required|integer|min:0|lte:quantity',
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

        return redirect()->back()
            ->with('success', 'Item agregado correctamente');
    }

    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);

        $hasOpenLendings = $equipment->lendingItems()
            ->whereHas('lending', function ($query) {
                $query->whereIn('status', ['pending', 'approved', 'active']);
            })
            ->exists();

        if ($hasOpenLendings) {
            $equipment->available_quantity = 0;
            $equipment->pending_deletion = true;
            $equipment->save();

            return redirect()->route('inventory_management')
                ->with('warning', 'Este equipo está vinculado a préstamos pendientes o activos. Se marcó como no disponible y pendiente de eliminación.');
        }

        $equipment->delete();

        return redirect()->route('inventory_management')
            ->with('success', 'Equipo eliminado correctamente.');
    }

public function kinventory(Request $request)
{
    $search = $request->input('search');
    $category = $request->input('category');

    $items = Equipment::where('available_quantity', '>', 0)
    ->where('pending_deletion', false)
    ->when($search, function ($query) use ($search) {
        $query->where('description', 'like', "%{$search}%");
    })
    ->when($category, function ($query) use ($category) {
        $query->where('category', $category);
    })
    ->paginate(18);

    $categories = Equipment::select('category')
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    return view('kinventory', compact('items', 'categories', 'search', 'category'));
}
}