<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Lending;
use App\Models\LendingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LendingController extends Controller
{
    /**
     * Show borrows management page.
     * Left column: pending special requests
     * Right column: active approved requests
     */
    public function index(Request $request)
    {
        $pendingLendings = Lending::with(['user', 'items.equipment'])
            ->where('flag', 1)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $activeLendings = Lending::with(['user', 'items.equipment'])
            ->where('status', 'active')
            ->latest()
            ->get();

        return view('inventory_management.borrows', compact(
            'pendingLendings',
            'activeLendings'
        ));
    }

    /**
     * Store a lending request from cart checkout.
     * Normal request => active immediately + reduce stock
     * Special request => pending + do not reduce stock yet
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pickup_date' => ['required', 'date'],
            'pickup_time' => ['required'],
            'commentary' => ['nullable', 'string', 'max:1000'],
            'special_case' => ['nullable', 'boolean'],
            'return_date' => ['nullable', 'date'],
            'special_reason' => ['nullable', 'string', 'max:1000'],
            'accept_terms' => ['required', 'accepted'],
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()
                ->back()
                ->with('error', 'Tu carrito está vacío.');
        }

        $isSpecialCase = (bool) ($request->boolean('special_case'));
        $status = $isSpecialCase ? 'pending' : 'active';

        DB::transaction(function () use ($request, $cart, $isSpecialCase, $status) {
            $lending = Lending::create([
                'user_id' => auth()->id() ?? 1, // adjust if auth is not ready yet
                'commentary' => $request->input('commentary'),
                'start_time' => $request->input('pickup_date') . ' ' . $request->input('pickup_time'),
                'end_time' => $isSpecialCase && $request->filled('return_date')
                    ? $request->input('return_date') . ' 15:00:00'
                    : null,
                'flag' => $isSpecialCase ? 1 : 0,
                'status' => $status,
                'special_reason' => $request->input('special_reason'),
            ]);

            foreach ($cart as $cartItem) {
                $equipment = Equipment::lockForUpdate()->findOrFail($cartItem['equipment_id']);
                $requestedQty = (int) $cartItem['quantity'];

                LendingItem::create([
                    'loan_id' => $lending->id,
                    'equipment_id' => $equipment->id,
                    'quantity' => $requestedQty,
                    'item_status' => $status,
                ]);

                // Only reduce stock immediately for normal requests
                if (!$isSpecialCase) {
                    if ($equipment->available_quantity < $requestedQty) {
                        abort(422, "No hay suficiente cantidad disponible para {$equipment->description}.");
                    }

                    $equipment->available_quantity -= $requestedQty;
                    $equipment->save();
                }
            }
        });

        session()->forget('cart');

        return redirect()
            ->route('kinventory')
            ->with('success', $isSpecialCase
                ? 'Solicitud especial enviada. Queda pendiente de aprobación.'
                : 'Solicitud enviada correctamente.');
    }

    /**
     * Approve a special pending lending.
     * Reduce stock only at approval time.
     */
    public function approve(Lending $lending)
    {
        if ((int) $lending->flag !== 1 || $lending->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden aprobar solicitudes especiales pendientes.');
        }

        DB::transaction(function () use ($lending) {
            $lending->load('items.equipment');

            foreach ($lending->items as $item) {
                $equipment = Equipment::lockForUpdate()->findOrFail($item->equipment_id);

                if ($equipment->available_quantity < $item->quantity) {
                    abort(422, "No hay suficiente cantidad disponible para {$equipment->description}.");
                }

                $equipment->available_quantity -= $item->quantity;
                $equipment->save();

                $item->item_status = 'active';
                $item->save();
            }

            $lending->status = 'active';
            $lending->save();
        });

        return redirect()
            ->back()
            ->with('success', 'Caso especial aprobado correctamente.');
    }

    /**
     * Reject a special pending lending.
     * Do not reduce stock.
     */
    public function reject(Lending $lending)
    {
        if ((int) $lending->flag !== 1 || $lending->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden rechazar solicitudes especiales pendientes.');
        }

        DB::transaction(function () use ($lending) {
            $lending->load('items');

            foreach ($lending->items as $item) {
                $item->item_status = 'rejected';
                $item->save();
            }

            $lending->status = 'rejected';
            $lending->save();
        });

        return redirect()
            ->back()
            ->with('success', 'Caso especial rechazado.');
    }

    /**
     * Mark an active lending as returned.
     * Restore available quantity.
     */
    public function markReturned(Lending $lending)
    {
        if ($lending->status !== 'active') {
            return redirect()
                ->back()
                ->with('error', 'Solo se pueden marcar como devueltos los préstamos activos.');
        }

        DB::transaction(function () use ($lending) {
            $lending->load('items.equipment');

            foreach ($lending->items as $item) {
                $equipment = Equipment::lockForUpdate()->findOrFail($item->equipment_id);

                $equipment->available_quantity += $item->quantity;

                if ($equipment->available_quantity > $equipment->quantity) {
                    $equipment->available_quantity = $equipment->quantity;
                }

                $equipment->save();

                $item->item_status = 'finished';
                $item->save();
            }

            $lending->status = 'finished';
            $lending->save();
        });

        return redirect()
            ->back()
            ->with('success', 'Equipo marcado como devuelto.');
    }
}