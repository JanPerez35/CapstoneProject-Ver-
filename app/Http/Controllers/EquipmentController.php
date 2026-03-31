<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Models\ActivityLog;

class EquipmentController extends Controller
{

private function logActivity($action, $comment = null)
{
    ActivityLog::create([
        'user_id' => 1, // temporary
        'role' => 'admin', // adjust if needed
        'action' => $action,
        'ip_address' => request()->ip(),
        'comment' => $comment,
        'created_at' => now(),
    ]);
}

public function index()
{
    
    $items = Equipment::paginate(18);

    $categories = Equipment::
    select('category')
    ->distinct()
    ->pluck('category');

    return view('inventory_management.admin_inventory', compact('items', 'categories'));

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

public function kinventory(Request $request)
{
    $search = $request->input('search');
    $category = $request->input('category');

    $query = Equipment::query();

    if ($search) {
        $query->where('description', 'like', '%' . $search . '%');
    }

    if ($category) {
        $query->where('category', $category);
    }

    $items = $query->paginate(18)->withQueryString();

    $categories = Equipment::select('category')
        ->whereNotNull('category')
        ->distinct()
        ->orderBy('category')
        ->pluck('category');

    return view('kinventory', compact('items', 'categories', 'search', 'category'));
}

public function borrow(Request $request)
{
    $validated = $request->validate([
        'equipment_id' => 'required|integer|exists:equipment,id',
        'quantity' => 'required|integer|min:1',
    ]);

    DB::transaction(function () use ($validated) {
        $equipment = Equipment::lockForUpdate()->findOrFail($validated['equipment_id']);

        if ($validated['quantity'] > $equipment->available_quantity) {
            abort(422, 'La cantidad solicitada excede la cantidad disponible.');
        }

        $lending = Lending::create([
            'user_id' => 1, // temporary until auth is connected
            'commentary' => 'Solicitud realizada desde Kinventory',
            'start_time' => now(),
            'end_time' => now()->addDays(7), // temporary
            'flag' => false,
            'status' => 'active',
            'created_at' => now(),
        ]);

        LendingItem::create([
            'lending_id' => $lending->id,
            'equipment_id' => $equipment->id,
            'quantity' => $validated['quantity'],
            'item_status' => 'borrowed',
        ]);

        $equipment->available_quantity -= $validated['quantity'];
        $equipment->save();
    });

    return redirect()->route('kinventory')->with('success', 'Equipo solicitado correctamente.');

    $this->logActivity(
    'Solicitud de Préstamo',
    'Solicitud creada para equipo ID ' . $validated['equipment_id']
    );

}

public function addToCart(Request $request)
{
    $validated = $request->validate([
        'equipment_id' => 'required|integer|exists:equipment,id',
        'quantity' => 'required|integer|min:1',
    ]);

    $equipment = Equipment::findOrFail($validated['equipment_id']);

    if ($validated['quantity'] > $equipment->available_quantity) {
        return redirect()->route('kinventory')
            ->with('error', 'La cantidad solicitada excede la cantidad disponible.');
    }

    $cart = session()->get('cart', []);

    if (isset($cart[$equipment->id])) {
        $newQuantity = $cart[$equipment->id]['quantity'] + $validated['quantity'];

        if ($newQuantity > $equipment->available_quantity) {
            return redirect()->route('kinventory')
                ->with('error', 'La cantidad total en el carrito excede la cantidad disponible.');
        }

        $cart[$equipment->id]['quantity'] = $newQuantity;
    } else {
        $cart[$equipment->id] = [
            'equipment_id' => $equipment->id,
            'description' => $equipment->description,
            'category' => $equipment->category,
            'location' => $equipment->location,
            'equipment_photo_url' => $equipment->equipment_photo_url,
            'quantity' => $validated['quantity'],
        ];
    }

    session()->put('cart', $cart);

    return redirect()->route('kinventory')
        ->with('success', 'Equipo añadido al carrito.');
}

public function cart()
{
    $cart = session()->get('cart', []);

    return view('cart.index', compact('cart'));
}

public function removeFromCart($id)
{
    $cart = session()->get('cart', []);

    if (isset($cart[$id])) {
        unset($cart[$id]);
        session()->put('cart', $cart);
    }
    return redirect()->back()->with('success', 'Item eliminado del carrito..');
}

public function checkoutCart(Request $request)
{
    $isSpecialCase = $request->has('special_case');

    $rules = [
        'pickup_date' => 'required|date|after_or_equal:today',
        'pickup_time' => 'required|date_format:H:i:s',
        'commentary' => 'nullable|string|max:1000',
        'accept_terms' => 'required|accepted',
    ];

    if ($isSpecialCase) {
        $rules['return_date'] = 'required|date|after_or_equal:pickup_date';
        $rules['special_reason'] = 'required|string|max:1000';
    } else {
        $rules['return_date'] = 'nullable|date';
        $rules['special_reason'] = 'nullable|string|max:1000';
    }

    $validated = $request->validate($rules);

    $cart = session()->get('cart', []);

    if (empty($cart)) {
        return redirect()->back()->with('error', 'El carrito está vacío.');
    }

    $startDateTime = $validated['pickup_date'] . ' ' . $validated['pickup_time'];

    $endDateTime = $isSpecialCase
        ? $validated['return_date'] . ' 15:00:00'
        : $validated['pickup_date'] . ' 15:00:00';

    $status = $isSpecialCase ? 'pending' : 'approved';
    $itemStatus = $isSpecialCase ? 'pending' : 'approved';

    DB::transaction(function () use ($cart, $startDateTime, $endDateTime, $isSpecialCase, $validated, $status, $itemStatus) {
        $lending = Lending::create([
            'user_id' => 1,
            'commentary' => $validated['commentary'] ?? null,
            'special_reason' => $isSpecialCase ? $validated['special_reason'] : null,
            'start_time' => $startDateTime,
            'end_time' => $endDateTime,
            'flag' => $isSpecialCase,
            'status' => $status,
        ]);

        foreach ($cart as $cartItem) {
            $equipment = Equipment::lockForUpdate()->findOrFail($cartItem['equipment_id']);

            if ($cartItem['quantity'] > $equipment->available_quantity) {
                throw new \Exception('La cantidad solicitada excede la cantidad disponible para ' . $equipment->description);
            }

            LendingItem::create([
                'lending_id' => $lending->id,
                'equipment_id' => $equipment->id,
                'quantity' => $cartItem['quantity'],
                'item_status' => $itemStatus,
            ]);

            if (!$isSpecialCase) {
                $equipment->available_quantity -= $cartItem['quantity'];
                $equipment->save();
            }
        }
    });

    $this->logActivity(
    'Creó solicitud',
    'Solicitud de préstamo creada desde carrito'
    );

    session()->forget('cart');

    return redirect()->route('kinventory')
        ->with('success', 'Solicitud de préstamo realizada correctamente.');
}

public function borrows()
{
    $pending = Lending::with('items.equipment', 'user')
        ->where('flag', true)
        ->where('status', 'pending')
        ->get();

    $approved = Lending::with('items.equipment', 'user')
        ->where('status', 'approved')
        ->get();

    return view('inventory_management.borrows', compact('pending', 'approved'));
}

public function approveRequest($id)
{
    $lending = Lending::with('items')->findOrFail($id);

    DB::transaction(function () use ($lending) {
        foreach ($lending->items as $item) {
            $equipment = Equipment::lockForUpdate()->findOrFail($item->equipment_id);

            if ($item->quantity > $equipment->available_quantity) {
                throw new \Exception('No hay suficiente inventario para aprobar esta solicitud.');
            }

            $equipment->available_quantity -= $item->quantity;
            $equipment->save();

            $item->item_status = 'approved';
            $item->save();
        }

        $lending->status = 'approved';
        $lending->save();
    });

    $this->logActivity(
    'Aprobó solicitud',
    'Solicitud ID ' . $lending->id . ' aprobada'
    );

    return redirect()->route('inventory_management.borrows')
        ->with('success', 'Solicitud aprobada correctamente.');
}

public function rejectRequest($id)
{
    $lending = Lending::with('items')->findOrFail($id);

    DB::transaction(function () use ($lending) {
        foreach ($lending->items as $item) {
            $item->item_status = 'rejected';
            $item->save();
        }

        $lending->status = 'rejected';
        $lending->save();
    });

    $this->logActivity(
    'Rechazó solicitud',
    'Solicitud ID ' . $lending->id . ' rechazada'
    );

    return redirect()->route('inventory_management.borrows')
        ->with('success', 'Solicitud denegada correctamente.');
}

public function markReturned($id)
{
    $lending = Lending::with('items')->findOrFail($id);

    DB::transaction(function () use ($lending) {
        foreach ($lending->items as $item) {
            $equipment = Equipment::lockForUpdate()->findOrFail($item->equipment_id);

            $equipment->available_quantity += $item->quantity;
            $equipment->save();

            $item->item_status = 'returned';
            $item->save();
        }

        $lending->status = 'returned';
        $lending->save();
    });

    $this->logActivity(
    'Devolución de equipo',
    'Solicitud ID ' . $lending->id . ' marcada como devuelta'
    );

    return redirect()->route('inventory_management.borrows')
        ->with('success', 'Equipo marcado como devuelto.');
}

public function accessLogs()
{
    $logs = ActivityLog::with('user')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('access_logs', compact('logs'));
}

}