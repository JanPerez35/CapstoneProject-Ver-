<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use App\Services\EmailService;
use App\Http\Controllers\Concerns\LogsActivity;

/**
 * Class LendingController
 *
 * Handles all equipment lending actions within the application.
 *
 * Responsibilities:
 * - processing direct borrow requests from the inventory view
 * - managing the session-based shopping cart (add, remove, view)
 * - checking out a cart as a lending request (standard or special case)
 * - displaying pending and approved lending requests for admins
 * - approving, rejecting, and marking lending requests as returned
 * - sending email notifications for approval, rejection, and checkout events
 */
class LendingController extends Controller
{
    use LogsActivity;

    /**
     * Email service used to send lending-related notifications.
     */
    protected $emailService;

    /**
     * Inject EmailService so all emails are handled
     * through a centralized service layer.
     */
    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Adds an equipment item to the session cart.
     *
     * Validates availability, merges with any existing cart entry for the
     * same equipment, and enforces that the total cart quantity does not
     * exceed the available stock. Only allows redirects to internal URLs.
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|integer|exists:equipment,id',
            'quantity' => 'required|integer|min:1',
            'redirect_back' => 'nullable|string',
        ]);

        $redirectBack = $validated['redirect_back'] ?? route('kinventory');

        $appUrl = rtrim(url('/'), '/');

        if (
            ! filter_var($redirectBack, FILTER_VALIDATE_URL) ||
            ! str_starts_with($redirectBack, $appUrl . '/')
        ) {
            $redirectBack = route('kinventory');
        }

        $equipment = Equipment::findOrFail($validated['equipment_id']);

        if ($validated['quantity'] > $equipment->available_quantity) {
            return redirect($redirectBack)
                ->with('error', 'La cantidad solicitada excede la cantidad disponible.');
        }

        $cart = session()->get('cart', []);

        $maxDifferentItems = 10;

        if (!isset($cart[$equipment->id]) && count($cart) >= $maxDifferentItems) {
            return redirect($redirectBack)
                ->with('error', 'No puedes pedir más de 10 equipos diferentes a la vez.');
        }


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
            ->with('cart_success', 'Equipo añadido al carrito.');
    }

    /**
     * Displays the cart view with live availability checks.
     *
     * Re-validates every cart item against current stock, removes items
     * that are no longer available or have fallen to zero quantity,
     * adjusts quantities that exceed current availability, and returns
     * the updated cart alongside a list of removed item names.
     */
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

    /**
     * Removes a single item from the session cart.
     *
     * Deletes the cart entry for the given equipment ID and redirects back.
     * Also signals the view to reopen the cart modal if items still remain.
     */
    public function removeFromCart($id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        $shouldReopenCart = !empty($cart);

        return redirect()->back()
            ->with('cart_removed_success', 'Equipo removido del carrito correctamente.')
            ->with('reopen_cart_modal', $shouldReopenCart);
    }

    /**
     * Formats a date using the Spanish month name.
     *
     * This method avoids depending on the server locale configuration
     * and guarantees that dates are shown consistently in Spanish.
     *
     * Example:
     * - 25 Mayo 2026
     */
    private function formatSpanishDate($dateTime): string
    {
        $date = \Carbon\Carbon::parse($dateTime);

        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $day = $date->format('d');
        $month = $months[(int) $date->format('n')];
        $year = $date->format('Y');

        return "{$day} {$month} {$year}";
    }

    /**
     * Builds the approval email message for a lending request.
     *
     * Keeps the original approval text and appends the request details
     * so the user can clearly see what was approved.
     *
     * Details included:
     * - requested equipment items with their quantities
     * - pickup date and pickup time
     * - return date and return time range
     * - pickup location
     */
    private function buildApprovalEmailMessage(Lending $lending): string
    {
        $lending->loadMissing(['items.equipment']);

        $itemsText = $lending->items->map(function ($item) {
            $equipmentName = $item->equipment?->description ?? 'Equipo no disponible';

            return "- {$equipmentName} | Cantidad: {$item->quantity}";
        })->implode("\n");

        $pickupDate = $this->formatSpanishDate($lending->start_time);

        $pickupTime = \Carbon\Carbon::parse($lending->start_time)
            ->format('g:i A');

        $returnDate = $this->formatSpanishDate($lending->end_time);

        return "Tu solicitud de equipo deportivo fue aprobada satisfactoriamente. Por favor entra a tu perfil de MAIKINE para más detalles.\n\n"
            . "Detalles de tu solicitud:\n\n"
            . "Equipos solicitados:\n"
            . $itemsText . "\n\n"
            . "Lugar de recogida: COLI 109 en el Coliseo Rafael A. Mangual\n"
            . "Día de recogida: {$pickupDate}\n"
            . "Hora de recogida: {$pickupTime}\n"
            . "Día de devolución: {$returnDate}\n"
            . "Hora de devolución: Entre 8:00 AM a 3:00 PM del día de devolución\n\n"
            . "Recuerda traer tu identificación de estudiante o empleado.d";

    }

    /**
     * Checks out the cart and creates a lending request.
     *
     * Applies stricter validation for special-case requests (extended return date
     * and reason required). Re-validates availability for all cart items, creates
     * the Lending and LendingItem records in a locked transaction, decrements
     * stock only for standard (auto-approved) requests, sends an approval email
     * when applicable, and clears the session cart on success.
     */
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

        // Lendings daily limit
        $dailyRequestLimit = 50;

        $todayRequests = Lending::whereDate('created_at', today())->count();

        if ($todayRequests >= $dailyRequestLimit) {
            return redirect()->back()
                ->with('error', 'Tu pedido ha sido rechazado debido a que se alcanzó el máximo del día de hoy.');
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

        $lending->load(['user', 'items.equipment']);


         // Send approval email automatically when request is NOT a special case.
         // Only sends if:
         // request is auto-approved
         // user has a valid email
        if (!$isSpecialCase && $lending->user && !empty($lending->user->email)) {
            $this->emailService->send(
                $lending->user->email,
                'Solicitud de equipo deportivo aprobada',
                $this->buildApprovalEmailMessage($lending)
            );
        }

        if ($isSpecialCase) {
            $this->logActivity(
                'Solicitud pendiente de revisión',
                "Solicitud ID: {$lending->id} creada en estado pendiente (caso especial)"
            );
        } else {
            $this->logActivity(
                'Creó solicitud',
                "Solicitud ID: {$lending->id} creada y aprobada automáticamente desde carrito"
            );
        }

        session()->forget('cart');

        return redirect()->route('kinventory')
            ->with('request_success', 'Solicitud enviada correctamente. Pronto recibirás un email con el estado de tu solicitud.');
    }
    /**
     * Displays the admin borrows management view.
     *
     * Loads pending and approved/active lending requests with their related
     * user and equipment data. Supports filtering by search term (name,
     * equipment, reason, commentary, date) and by exact pickup date.
     */
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

    /**
     * Approves a pending lending request.
     *
     * Validates available inventory for each item in a locked transaction,
     * decrements stock, marks all items and the lending as approved,
     * sends an approval email to the user, and logs the activity.
     */
    public function approveRequest($id)
    {
        $lending = Lending::with(['items.equipment', 'user'])->findOrFail($id);
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

        /**
         * Send email when admin manually approves a request.
         */
        if ($lending->user && !empty($lending->user->email)) {
            $this->emailService->send(
                $lending->user->email,
                'Solicitud de equipo deportivo aprobada',
                $this->buildApprovalEmailMessage($lending)
            );
        }

        $this->logActivity(
            'Aprobó solicitud',
            'Solicitud ID ' . $lending->id . ' aprobada'
        );

        return redirect()->route('inventory_management.borrows')
            ->with('success', 'Solicitud aprobada correctamente.');
    }

    /**
     * Rejects a pending lending request.
     *
     * Marks all items and the lending as rejected in a transaction, then sends
     * a rejection email to the user. The email includes the best available admin
     * contact (inventory admin → super admin → fallback phone extension).
     */
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

        /**
         * Send email when request is rejected.
         * Includes dynamic admin contact email.
         * Email or phone extension.
         */
        if ($lending->user && !empty($lending->user->email)) {
            $inventoryAdmin = \App\Models\User::where('role', 'Administrador de Inventario')
                ->where('status', 'Activo')
                ->first();

            $superAdmin = \App\Models\User::where('role', 'Super Administrador')
                ->where('status', 'Activo')
                ->first();

            $adminEmail = $inventoryAdmin?->email
                ?? $superAdmin?->email
                ?? '+1 (787)-832-4040 Ext. 3841, 2008';

            $this->emailService->send(
                $lending->user->email,
                'Solicitud de equipo deportivo denegada',
                'Tu solicitud de equipo deportivo fue denegada. Por favor entra a tu perfil de MAIKINE para más detalles. De tener alguna duda comunícate con el administrador de inventario (' . $adminEmail . ').'
            );
        }
        $this->logActivity(
            'Rechazó solicitud',
            'Solicitud ID ' . $lending->id . ' rechazada'
        );

        return redirect()->route('inventory_management.borrows')
            ->with('success', 'Solicitud denegada correctamente.');
    }

    /**
     * Marks a lending request as returned.
     *
     * Restores the available quantity for each item in a locked transaction,
     * sets all item statuses and the lending status to returned, and logs
     * the activity.
     */
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
