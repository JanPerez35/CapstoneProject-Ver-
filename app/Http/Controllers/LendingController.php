<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Services\EmailService;
use App\Http\Controllers\Concerns\LogsActivity;

class LendingController extends Controller
{
    use LogsActivity;

    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
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
            'user_id' => auth()->id(),
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

    $this->logActivity(
        'Solicitud de Préstamo',
        'Solicitud creada para equipo ID ' . $validated['equipment_id']

    );

    return redirect()->route('kinventory')->with('success', 'Equipo solicitado correctamente.');

}

    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|integer|exists:equipment,id',
            'quantity' => 'required|integer|min:1',
            'redirect_back' => 'nullable|string',
        ]);

        $redirectBack = $validated['redirect_back'] ?? route('kinventory');

        // Seguridad básica: solo permitir redirects internos del mismo sitio
        if (!str_starts_with($redirectBack, url('/'))) {
            $redirectBack = route('kinventory');
        }

        $equipment = Equipment::findOrFail($validated['equipment_id']);

        if ($validated['quantity'] > $equipment->available_quantity) {
            return redirect($redirectBack)
                ->with('error', 'La cantidad solicitada excede la cantidad disponible.');
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$equipment->id])) {
            $newQuantity = $cart[$equipment->id]['quantity'] + $validated['quantity'];

            if ($newQuantity > $equipment->available_quantity) {
                return redirect($redirectBack)
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

        return redirect($redirectBack)
            ->with('cart_success', 'Item añadido al carrito.');
    }

public function cart()
{
    $cart = session()->get('cart', []);
    $updatedCart = [];
    $removedItems = [];

    foreach ($cart as $item) {
        $equipment = Equipment::find($item['equipment_id']);

        if (!$equipment || $equipment->available_quantity <= 0) {
            $removedItems[] = $item['description'] ?? 'equipo';
            continue;
        }

        if ($item['quantity'] > $equipment->available_quantity) {
            $item['quantity'] = $equipment->available_quantity;
        }

        if ($item['quantity'] <= 0) {
            $removedItems[] = $item['description'] ?? 'equipo';
            continue;
        }

        $item['available_quantity'] = $equipment->available_quantity;
        $updatedCart[$item['equipment_id']] = $item;
    }

    session()->put('cart', $updatedCart);

    return view('cart.index',['cart' => $updatedCart,'removedItems' => $removedItems,]);
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

        $updatedCart = [];

        foreach ($cart as $cartItem) {
            $equipment = Equipment::find($cartItem['equipment_id']);

            if (!$equipment || $equipment->available_quantity <= 0) {
                continue; // remove from cart completely
            }

            if ($cartItem['quantity'] > $equipment->available_quantity) {
                $cartItem['quantity'] = $equipment->available_quantity;
            }

            if ($cartItem['quantity'] <= 0) {
                continue; // never keep zero-quantity items
            }

            $cartItem['available_quantity'] = $equipment->available_quantity;
            $updatedCart[$cartItem['equipment_id']] = $cartItem;
        }

        session()->put('cart', $updatedCart);
        $cart = $updatedCart;

        if (empty($cart)) {
            return redirect()->route('kinventory')
                ->with('error', 'Los equipos del carrito ya no están disponibles.');
        }

        $startDateTime = $validated['pickup_date'] . ' ' . $validated['pickup_time'];

        $endDateTime = $isSpecialCase
            ? $validated['return_date'] . ' 15:00:00'
            : $validated['pickup_date'] . ' 15:00:00';

        $status = $isSpecialCase ? 'pending' : 'approved';
        $itemStatus = $isSpecialCase ? 'pending' : 'approved';

        $lending = null;

        DB::transaction(function () use ($cart, $startDateTime, $endDateTime, $isSpecialCase, $validated, $status, $itemStatus, &$lending) {
            $lending = Lending::create([
                'user_id' => auth()->id(),
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

        $lending->load('user');

        if (!$isSpecialCase && $lending->user && !empty($lending->user->email)) {
            $this->emailService->send(
                $lending->user->email,
                'Solicitud de item aprobada',
                'Tu solicitud de equipo deportivo fue aprobada satisfactoriamente. Por favor entra a tu perfil de MAIKINE para más detalles.'
            );
        }

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
        $lending = Lending::with(['items', 'user'])->findOrFail($id);

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

        if ($lending->user && !empty($lending->user->email)) {
            $this->emailService->send(
                $lending->user->email,
                'Solicitud de item aprobada',
                'Tu solicitud de equipo deportivo fue aprobada satisfactoriamente. Por favor entra a tu perfil de MAIKINE para más detalles.'
            );
        }

        $this->logActivity(
            'Aprobó solicitud',
            'Solicitud ID ' . $lending->id . ' aprobada'
        );

        return redirect()->route('inventory_management.borrows')
            ->with('success', 'Solicitud aprobada correctamente.');
    }

    public function rejectRequest($id)
    {
        $lending = Lending::with(['items', 'user'])->findOrFail($id);

        DB::transaction(function () use ($lending) {
            foreach ($lending->items as $item) {
                $item->item_status = 'rejected';
                $item->save();
            }

            $lending->status = 'rejected';
            $lending->save();
        });

        if ($lending->user && !empty($lending->user->email)) {
            $this->emailService->send(
                $lending->user->email,
                'Solicitud de item denegada',
                'Tu solicitud de equipo deportivo fue denegada. Por favor entra a tu perfil de MAIKINE para más detalles. De tener alguna duda comunícate con el administrador de inventario (inventario@upr.edu).'
            );
        }

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
}