<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class EquipmentController
 *
 * Handles all equipment-related actions within the application.
 *
 * Responsibilities:
 * - displaying equipment inventory
 * - filtering equipment by search and category
 * - creating new equipment records
 * - updating equipment information
 * - deleting or marking equipment for deletion
 * - displaying available equipment in Kinventory
 */
class EquipmentController extends Controller
{
    /**
     * Displays the inventory management view.
     *
     * Retrieves equipment records, applies search and category filters,
     * loads the number of active lendings for each item, and returns
     * the inventory management view with pagination.
     */

    use LogsActivity;

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

        // Filter equipment by search term
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        // Filter equipment by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Paginate filtered equipment
        $items = $query->paginate(18)->withQueryString();

        // Get all available categories
        $categories = Equipment::where('pending_deletion', false)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Get all available locations
        $locations = Equipment::where('pending_deletion', false)
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view('inventory_management.admin_inventory', compact('items', 'categories', 'locations'));
    }

    /**
     * Updates an existing equipment record.
     *
     * Validates the submitted data, updates the equipment information,
     * and replaces the image if a new one is uploaded.
     */
    public function update(Request $request, $id)
    {
        $item = Equipment::findOrFail($id);

        $validated = $request->validate([
            'description' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]+$/'],
            'category' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]+$/'],
            'quantity' => 'required|integer|min:1',
            'available_quantity' => 'required|integer|min:0|lte:quantity',
            'location' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-\/]+$/'],
            'image' => 'nullable|image|mimes:jpg,jpeg|max:2048',
        ]);

        $imagePath = $item->equipment_photo_url;

        if ($request->hasFile('image')) {
            if ($item->equipment_photo_url) {
                \Storage::disk('public')->delete($item->equipment_photo_url);
            }

            $imagePath = $request->file('image')->store('equipment_photos', 'public');
        }

        $item->update([
            'description' => $validated['description'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'available_quantity' => $validated['available_quantity'],
            'location' => $validated['location'],
            'equipment_photo_url' => $imagePath,
        ]);

        $this->logActivity(
            'Actualizar equipo',
            "Se actualizó el equipo: {$item->description} (ID: {$item->id})"
        );

        return redirect()->back()
            ->with('success', 'Item actualizado correctamente');
    }

    /**
     * Stores a new equipment record.
     *
     * Validates the submitted data, uploads the image,
     * and creates a new equipment entry.
     */
    public function store(Request $request)
    {

        //dd($request->all());

        $validated = $request->validate([
            'description' => [
                'required',
                'string',
                'min:3',
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
                'min:3',
                'max:100',
                'regex:/^[A-Za-z0-9\s\.,\-\/]+$/'
            ],
            'quantity' => 'required|integer|min:1',
            'available_quantity' => 'required|integer|min:0|lte:quantity',
            'image' => 'required|image|mimes:jpg,jpeg|max:2048',
        ]);

        // Store equipment image
        $imagePath = $request->file('image')->store('equipment_photos', 'public');

        // Create equipment record
        $item = Equipment::create([
            'description' => $validated['description'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'available_quantity' => $validated['available_quantity'],
            'location' => $validated['location'],
            'equipment_photo_url' => $imagePath,
        ]);

        $this->logActivity(
            'Agregar equipo',
            "Se agregó el equipo: {$item->description} (ID: {$item->id})"
        );

        return redirect()->back()
            ->with('success', 'Item agregado correctamente');
    }

    /**
     * Deletes an equipment record.
     *
     * If the equipment has active or pending lendings,
     * it is marked as unavailable and pending deletion.
     * Otherwise, it is removed completely.
     */
    public function destroy($id)
    {
        $equipment = Equipment::findOrFail($id);

        $hasOpenLendings = $equipment->lendingItems()
            ->whereHas('lending', function ($query) {
                $query->whereIn('status', ['pending', 'approved', 'active']);
            })
            ->exists();

        // Mark equipment as pending deletion if it is linked to active lendings
        if ($hasOpenLendings) {
            $equipment->available_quantity = 0;
            $equipment->pending_deletion = true;
            $equipment->save();

        $this->logActivity(
            'Marcar equipo para eliminación',
            "Se marcó como pendiente de eliminación el equipo: {$equipment->description} (ID: {$equipment->id})"
        );

        return redirect()->route('inventory_management')
            ->with('warning', 'Este equipo está vinculado a préstamos pendientes o activos. Se marcó como no disponible y pendiente de eliminación.');
        }

        // Delete equipment if no active lendings exist
        $equipmentDescription = $equipment->description;
        $equipmentId = $equipment->id;

        $equipment->delete();

        $this->logActivity(
            'Eliminar equipo',
            "Se eliminó el equipo: {$equipmentDescription} (ID: {$equipmentId})"
        );

        return redirect()->route('inventory_management')
            ->with('success', 'Equipo eliminado correctamente.');
    }

    /**
     * Displays the Kinventory view.
     *
     * Retrieves available equipment, applies optional filters,
     * and returns the Kinventory view with pagination.
     */
    public function kinventory(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');

        // Get available equipment with optional filters
        $items = Equipment::where('available_quantity', '>', 0)
            ->where('pending_deletion', false)
            ->when($search, function ($query) use ($search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($category, function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->paginate(18);

        // Get available categories
        $categories = Equipment::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('kinventory', compact('items', 'categories', 'search', 'category'));
    }
}
