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

public function index(Request $request)
{
    $query = Equipment::query();

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

    // Pagination + keep filters in URL
    $items = $query->paginate(18)->withQueryString();

    // Categories list
    $categories = Equipment::select('category')
        ->distinct()
        ->pluck('category');

    return view('inventory_management.admin_inventory', compact('items', 'categories'));
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
            $cart[$equipment->id]['available_quantity'] = $equipment->available_quantity;
        } else {
            $cart[$equipment->id] = [
                'equipment_id' => $equipment->id,
                'description' => $equipment->description,
                'category' => $equipment->category,
                'location' => $equipment->location,
                'equipment_photo_url' => $equipment->equipment_photo_url,
                'available_quantity' => $equipment->available_quantity,
                'quantity' => $validated['quantity'],
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('kinventory')
            ->with('cart_success', 'Item añadido al carrito.');
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

        $shouldReopenCart = !empty($cart);

        return redirect()->back()
            ->with('cart_removed_success', 'Item removido del carrito correctamente.')
            ->with('reopen_cart_modal', $shouldReopenCart);
    }

    public function checkoutCart(Request $request)
    {
        $isSpecialCase = $request->has('special_case');

        $rules = [
            'pickup_date' => 'required|date|after:today',
            'pickup_time' => 'required|date_format:H:i:s',
            'accept_terms' => 'required|accepted',
            'cart_quantities' => 'required|array',
            'cart_quantities.*' => 'required|integer|min:1',
        ];

        if ($isSpecialCase) {
            $rules['return_date'] = 'required|date|after_or_equal:pickup_date';
            $rules['special_reason'] = [
                'required',
                'string',
                'min:10',
                'max:500',
                'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]+$/',
            ];
        } else {
            $rules['return_date'] = 'nullable|date';
            $rules['special_reason'] = [
                'nullable',
                'string',
                'max:500',
                'regex:/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\.,\-]*$/',
            ];
        }

        $validated = $request->validate($rules);

        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        foreach ($cart as $equipmentId => &$cartItem) {
            if (isset($validated['cart_quantities'][$equipmentId])) {
                $cartItem['quantity'] = (int) $validated['cart_quantities'][$equipmentId];
            }
        }
        unset($cartItem);

        $startDateTime = $validated['pickup_date'] . ' ' . $validated['pickup_time'];

        $endDateTime = $isSpecialCase
            ? $validated['return_date'] . ' 15:00:00'
            : $validated['pickup_date'] . ' 15:00:00';

        $status = $isSpecialCase ? 'pending' : 'approved';
        $itemStatus = $isSpecialCase ? 'pending' : 'approved';

        DB::transaction(function () use ($cart, $startDateTime, $endDateTime, $isSpecialCase, $validated, $status, $itemStatus) {
            $lending = Lending::create([
                'user_id' => auth()->id() ?? 1,
                'commentary' => null,
                'special_reason' => $isSpecialCase ? $validated['special_reason'] : null,
                'start_time' => $startDateTime,
                'end_time' => $endDateTime,
                'flag' => $isSpecialCase,
                'status' => $status,
            ]);

            foreach ($cart as $cartItem) {
                $equipment = Equipment::lockForUpdate()->findOrFail($cartItem['equipment_id']);

                if ($cartItem['quantity'] > $equipment->available_quantity) {
                    throw new \Exception(
                        'La cantidad solicitada excede la cantidad disponible para ' . $equipment->description
                    );
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
            ->with('request_success', 'Solicitud enviada correctamente. Pronto recibirás un email con el estado.');
    }
public function borrows(Request $request)
{
    $pendingQuery = Lending::with(['user', 'items.equipment'])
        ->where('status', 'pending');

    $approvedQuery = Lending::with(['user', 'items.equipment'])
        ->whereIn('status', ['approved', 'active']);

    if ($request->filled('search')) {
        $search = $request->search;

        $pendingQuery->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
            })
            ->orWhereHas('items.equipment', function ($itemQuery) use ($search) {
                $itemQuery->where('description', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            })
            ->orWhere('special_reason', 'like', '%' . $search . '%')
            ->orWhere('commentary', 'like', '%' . $search . '%')
            ->orWhereDate('start_time', $search);
        });

        $approvedQuery->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('first_name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $search . '%']);
            })
            ->orWhereHas('items.equipment', function ($itemQuery) use ($search) {
                $itemQuery->where('description', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            })
            ->orWhere('special_reason', 'like', '%' . $search . '%')
            ->orWhere('commentary', 'like', '%' . $search . '%')
            ->orWhereDate('start_time', $search);
        });
    }

    if ($request->filled('date')) {
        $pendingQuery->whereDate('start_time', $request->date);
        $approvedQuery->whereDate('start_time', $request->date);
    }

    $pending = $pendingQuery->latest('start_time')->get();
    $approved = $approvedQuery->latest('start_time')->get();

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

private function buildStatisticsQuery(string $type, int $year, int $month)
{
    $query = DB::table('lending_items')
        ->join('lendings', 'lending_items.lending_id', '=', 'lendings.id')
        ->join('equipment', 'lending_items.equipment_id', '=', 'equipment.id')
        ->selectRaw('equipment.description, CAST(SUM(lending_items.quantity) AS UNSIGNED) as total')
        ->whereYear('lendings.created_at', $year)
        ->groupBy('equipment.description')
        ->orderByDesc('total');

    if ($type === 'monthly') {
        $query->whereMonth('lendings.created_at', $month);
    }

    return $query;
}

public function statistics(Request $request)
{
    $type  = $request->input('type', 'monthly');
    $year  = (int) $request->input('year', date('Y'));
    $month = (int) $request->input('month', date('n'));

    $items = $this->buildStatisticsQuery($type, $year, $month)->get();

    $availableYears = DB::table('lendings')
        ->selectRaw('DISTINCT YEAR(created_at) as yr')
        ->whereNotNull('created_at')
        ->pluck('yr')
        ->filter()
        ->sort()
        ->values()
        ->toArray();

    if (!in_array((int) date('Y'), $availableYears)) {
        $availableYears[] = (int) date('Y');
        sort($availableYears);
    }

    $topItem    = $items->first();
    $totalReqs  = $items->sum('total');
    $totalItems = $items->count();

    return view('inventory_management.inventory_statistics', compact(
        'items', 'availableYears', 'type', 'year', 'month',
        'topItem', 'totalReqs', 'totalItems'
    ));
}

public function exportStatistics(Request $request)
{
    $type   = $request->input('type', 'monthly');
    $year   = (int) $request->input('year', date('Y'));
    $month  = (int) $request->input('month', date('n'));
    $format = $request->input('format', 'csv');

    $items = $this->buildStatisticsQuery($type, $year, $month)->get();

    $monthNames = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    $periodLabel = $type === 'annual'
        ? "Anual - {$year}"
        : "{$monthNames[$month]} {$year}";

    if ($format === 'csv') {
        $lines   = ["Objeto,Pedidos"];
        foreach ($items as $item) {
            $lines[] = "\"{$item->description}\",{$item->total}";
        }
        $content  = implode("\n", $lines);
        $filename = "reporte_inventario_{$type}_{$year}.csv";
        $mime     = 'text/csv';
    } else {
        $lines = [
            "REPORTE DE INVENTARIO",
            "Tipo: " . ($type === 'annual' ? 'Anual' : 'Mensual'),
            "Período: {$periodLabel}",
            "",
            "TOP ARTÍCULOS:",
        ];
        if ($items->isEmpty()) {
            $lines[] = "Sin datos para el período seleccionado.";
        } else {
            foreach ($items as $i => $item) {
                $lines[] = ($i + 1) . ". {$item->description} - {$item->total} pedidos";
            }
        }
        $content  = implode("\n", $lines);
        $filename = "reporte_inventario_{$type}_{$year}.txt";
        $mime     = 'text/plain';
    }

    return response($content, 200)
        ->header('Content-Type', $mime . '; charset=utf-8')
        ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
}

public function accessLogs()
{
    $logs = ActivityLog::with('user')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('access_logs', compact('logs'));
}

}
