@props([
    'title' => 'MAIKINE'
])

@php

/**
 * These variables prepare the user-facing header and footer data.
 *
 * If a specific admin user is not available, default contact emails are used as
 * safe fallbacks.  Essentially they serve as defaults values when that data is not yet
 * available.
 *
 * */
    $currentUser = auth()->user();

    use App\Models\Lending;

        $dailyRequestLimit = 50;
        $todayRequests = Lending::whereDate('created_at', today())->count();
        $remainingDailyRequests = max($dailyRequestLimit - $todayRequests, 0);


    $currentUserName = $currentUser?->name ?? 'Usuario';
    $currentUserRole = $currentUser?->role ?? 'rol';
    $superAdminUser = $superAdminUser ?? null;
    $inventoryAdminUser = $inventoryAdminUser ?? null;
    $marketAdminUser = $marketAdminUser ?? null;

    $superAdminEmail = $superAdminUser?->email ?? 'superadmin@uprm.edu';
    $inventoryAdminEmail = $inventoryAdminUser?->email ?? 'inventario@uprm.edu';
    $marketAdminEmail = $marketAdminUser?->email ?? 'mercado@uprm.edu';
@endphp

    <!doctype html>
<html lang="es-PR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>

    {{--Flag used by layout_validation.js to activate layout behaviours like toasts and pop ups--}}
    <script>
        window.useDedicatedLayoutValidation = true;
    </script>


    <script>

        /**
         * Synchronizes the global unread chat badge after navigating back from
         * the messages page.
         *
         * Opera and some browsers may restore a cached version of the previous page
         * using the back/forward cache (bfcache). When this happens, the unread
         * badge in the navbar can appear outdated even though the backend already
         * marked the messages as read.
         *
         * This function restores the correct unread counter using the value stored
         * in sessionStorage by messages_validation.js when a conversation is opened.
         */
        function syncUnreadChatBadgeFromSession() {
            const readCount = Number(sessionStorage.getItem('maikineReadMessagesCount') || 0);

            if (readCount <= 0) {
                return;
            }

            const badge = document.getElementById('miChatsUnreadBadge');

            if (badge) {
                const currentCount = Number(badge.textContent.trim() || 0);
                const newCount = Math.max(currentCount - readCount, 0);

                if (newCount > 0) {
                    badge.textContent = newCount;
                } else {
                    badge.remove();
                }
            }

            sessionStorage.removeItem('maikineReadMessagesCount');
        }

        window.addEventListener('pageshow', syncUnreadChatBadgeFromSession);
        document.addEventListener('DOMContentLoaded', syncUnreadChatBadgeFromSession);
    </script>

    {{-- Global app styles and scripts for validation logic, handle things like badge styles and toasts--}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/layout_validation.js'
    ])
</head>
<body
    {{-- Success and UI state data passed to JavaScript through body dataset. Details the cart, user, and terms & conditions states --}}
    data-cart-success="{{ session('cart_success') }}"
    data-request-success="{{ session('request_success') }}"
    data-cart-removed-success="{{ session('cart_removed_success') }}"
    data-terms-updated-success="{{ session('terms_updated_success') }}"
    data-reopen-cart-modal="{{ session('reopen_cart_modal') ? '1' : '0' }}"
    data-current-user-id="{{ auth()->id() ?? '' }}"


    data-unread-count="{{ $totalUnread ?? 0 }}"
    data-error-message="{{ session('error') }}"
>
{{-- Top navigation bar with branding, user info, quick actions, and logout --}}
<div class="container-fluid px-0">
    <header class="app-header d-flex flex-wrap align-items-center justify-content-between py-2 px-3 border-bottom">
        {{-- Application logo and home link --}}
        <a href="/kinemarket" class="d-flex align-items-center mb-2 mb-md-0 text-decoration-none">
            <img src="/images/kine_logo.png"
                 alt="Logo"
                 style="height: 75px; width:auto;"
                 class="me-2">

            <span class="fs-3 fw-bold text-white m-0">MAIKINE</span>
        </a>

        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2 gap-lg-3">

            {{-- Logged-in user identity summary: Shows the name and role of the current user logged in --}}
            <div class="text-start text-lg-end">
                <div class="fw-semibold">{{ $currentUserName }}</div>
                <small class="text-muted">{{ $currentUserRole }}</small>
            </div>

            {{-- Navigation/action buttons  for profile chats and cart--}}
            <div class="d-flex flex-wrap gap-2 justify-content-start justify-content-lg-end">
                <a href="{{ route('my_profile') }}"
                   class="btn {{ request()->routeIs('my_profile') ? 'btn-success' : 'btn-outline-success' }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Ve tus publicaciones y estado de préstamos!">
                    <i class="bi bi-person-fill"></i> Mi Perfil
                </a>

                {{-- Messages button with unread badge updated by JS --}}
                <a href="{{ route('my_messages', ['return_to' => url()->full()]) }}"
                   class="btn position-relative {{ request()->routeIs('my_messages') ? 'btn-success' : 'btn-outline-success' }}"
                   data-bs-toggle="tooltip"
                   data-bs-placement="bottom"
                   data-bs-custom-class="custom-tooltip"
                   data-bs-title="Comunícate con otros usuarios sobre publicaciones del mercado!">
                    <i class="bi bi-chat-left-text"></i> Mis Chats
                    @if($totalUnread > 0)
                        <span
                            id="miChatsUnreadBadge"
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                            >
                            {{ $totalUnread }}
                        </span>
                    @endif
                </a>

                {{-- Opens the borrowing cart modal: badge shows total selected quantity dynamically --}}
                <button
                    type="button"
                    class="btn btn-outline-success position-relative"
                    data-bs-toggle="modal"
                    data-bs-target="#cartModal"
                    data-bs-placement="top"
                    data-bs-custom-class="custom-tooltip"
                    data-bs-title="Verifica equipo deportivo en tu carrito!"
                    data-bs-trigger="hover"
                >
                    <i class="bi bi-cart3 me-1"></i>
                    Carrito
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ empty($cart) ? 'd-none' : '' }}"
                          id="cartCount">
                        {{ collect($cart)->sum('quantity') }}
                    </span>
                </button>

                {{-- Logout form --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </header>
</div>

{{-- Main page content injected by each individual view: For example for Kinventory it displays the inventory view before the footer --}}
<main>{{ $slot }}</main>

{{-- Floating button to scroll back to the top of the page --}}
<button
    type="button"
    id="scrollToTopBtn"
    class="btn btn-success scroll-to-top-btn d-none"
    data-bs-toggle="tooltip"
    data-bs-placement="right"
    data-bs-custom-class="custom-tooltip"
    data-bs-title="Subir al inicio de la página"
    aria-label="Subir al inicio de la página"
>
    <i class="bi bi-arrow-up"></i>
</button>

{{-- Borrowing cart modal used to review selected items and submit a loan request --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">

            {{-- Main checkout form for submitting the borrowing request of the user --}}
            <form method="POST"
                  action="{{ route('cart.checkout') }}"
                  class="d-flex flex-column flex-grow-1 overflow-hidden"
                  id="checkoutCartForm">
                @csrf

                {{-- Server-side validation errors shown at the top of the modal --}}
                @if ($errors->any())
                    <div class="alert alert-danger mx-3 mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Cart Modal title and short description for user --}}
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="modal-title fw-bold mb-0" id="cartModalLabel">
                                Carrito de Préstamos
                            </h3>

                            <button type="button"
                                    class="btn-close ms-3"
                                    data-bs-dismiss="modal"
                                    aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0">
                            Revisa tu selección y completa los detalles del préstamo
                        </p>
                    </div>
                </div>

                <div class="modal-body pt-3">
                    <div class="mb-4">
                        {{-- Cart contents summary --}}
                        @if(!empty($cart))
                            <div class="fw-bold  fs-5 text-success mb-3 d-flex align-items-center gap-2">
                                <span>Por favor, desplácese hacia abajo para ver más</span>
                                <i class="bi bi-arrow-down-circle"></i>
                            </div>

                            <div class="alert alert-warning rounded-0 shadow-sm py-2 px-3 mb-3">
                                <strong>Importante:</strong>
                                Los equipos agregados al carrito se mantienen disponibles solo mientras la sesión esté activa.
                            </div>
                        @endif

                        <h4 class="fw-bold mb-3">
                            Equipos Seleccionados ({{ count($cart) }} ítems)
                        </h4>

                        <div class="border border-dark border-1 rounded-4 overflow-hidden">

                            {{-- Cart table header --}}
                            <div class="row g-0 px-3 py-3 fw-semibold border-bottom bg-light">
                                <div class="col-6">Equipo</div>
                                <div class="col-3 text-center">Cantidad</div>
                                <div class="col-3 text-center">Remover</div>
                            </div>

                            <div id="cartItemsContainer">
                                @if(empty($cart))
                                    {{-- Empty state shown when the cart has no selected items currently--}}
                                    <div class="text-center py-5">
                                        <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                                        <h5 class="fw-bold">Tu carrito está vacío</h5>
                                        <p class="text-muted mb-3">Agrega equipos antes de continuar.</p>

                                        <button type="button"
                                                class="btn btn-success"
                                                data-bs-dismiss="modal">
                                            Continuar Explorando
                                        </button>
                                    </div>
                                @else
                                    @foreach($cart as $index => $item)
                                        @php

                                        /*
                                         * This is a safeguard for the current quantity and available stock.
                                         * This is so the cart always has a valid minimum quantity range for
                                         * frontend.
                                         *
                                         * */

                                                $itemQuantity = (int) ($item['quantity'] ?? 1);
                                                $itemStock = (int) ($item['available_quantity'] ?? $itemQuantity);
                                                if ($itemStock < 1) {
                                                    $itemStock = 1;
                                                }
                                        @endphp

                                        {{-- Single cart item row with image, quantity controls, and remove action --}}
                                        <div class="row g-0 align-items-center px-3 py-2 border-bottom cart-item-row"
                                             data-index="{{ $index }}"
                                             data-max-quantity="{{ $itemStock }}">
                                            <div class="col-6 d-flex align-items-center gap-3">
                                                <img
                                                    src="{{ !empty($item['equipment_photo_url']) ? asset('storage/' . $item['equipment_photo_url']) : asset('images/kinventory_images/default.jpg') }}"
                                                    style="width: 60px; height: 60px; object-fit: contain;"
                                                    class="rounded border border-1 border-dark"
                                                    alt="{{ $item['description'] ?? 'Equipo' }}"
                                                >
                                                <span class="fw-semibold">{{ $item['description'] ?? 'Sin descripción' }}</span>
                                            </div>

                                            <div class="col-3 cart-quantity-col">
                                                {{-- Quantity controls adjusted in JS --}}
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="cart-quantity-controls">
                                                        {{--Minus button--}}
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm decrease-cart-item"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-trigger="hover"
                                                                data-bs-title="Disminuir cantidad">
                                                            -
                                                        </button>

                                                        <span class="fw-bold fs-6 cart-quantity-display">{{ $itemQuantity }}</span>
                                                        {{--Plus button--}}
                                                        <button type="button"
                                                                class="btn btn-outline-secondary btn-sm increase-cart-item"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                data-bs-custom-class="custom-tooltip"
                                                                data-bs-trigger="hover"
                                                                data-bs-title="Aumentar cantidad">
                                                            +
                                                        </button>
                                                    </div>

                                                    {{-- Hidden input that submits the final quantity to the backend as an update --}}
                                                    <input type="hidden"
                                                           name="cart_quantities[{{ $item['equipment_id'] }}]"
                                                           value="{{ $itemQuantity }}"
                                                           class="cart-quantity-input">

                                                    {{-- Per-item validation message populated dynamically --}}
                                                    <div class="text-danger small mt-2 cart-item-error text-center"></div>
                                                </div>
                                            </div>

                                            <div class="col-3 cart-remove-col">                                                {{-- Opens a confirmation modal before removing the cart item --}}
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger open-remove-cart-confirm"
                                                    data-form-id="remove-cart-item-{{ $item['equipment_id'] }}"
                                                    data-item-name="{{ $item['description'] ?? 'este artículo' }}"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    data-bs-custom-class="custom-tooltip"
                                                    data-bs-title="Remover equipo del carrito"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>

                    @if(!empty($cart))
                        <hr class="my-4">

                        <div class="mb-4">
                            {{-- Loan request details section --}}
                            <h3 class="fw-bold mb-2">Detalles del Préstamo</h3>



                            {{-- Service rules and operating hours --}}
                            <div class="border border-dark border-2 rounded-4 p-4 mb-4 bg-light-subtle">
                                <h5 class="fw-bold text-secondary mb-3">
                                    Horario y Servicio:
                                </h5>

                                <p class="mb-2 text-subtle ">
                                    Solicitud enviada fuera de horas laborables (8am-1pm) permitira un préstamo disponible desde el siguiente día laborable
                                </p>

                                <p class="mb-2 fw-bold text">
                                    El equipo debe ser recogido en COLI 109, ubicado en el Coliseo Rafael A. Mangual.
                                </p>

                                <p class="mb-2 fw-bold text-danger">
                                    El equipo debe ser devuelto entre las 8:00 AM y 3:00 PM del día de devolución asignado.
                                </p>

                                <p class="mb-2 fw-bold text-danger">
                                    En una solicitud normal, el día de devolución es el mismo día de recogida.
                                </p>

                                <p class="mb-2 fw-bold text-danger">
                                    En un caso especial, el usuario propone una fecha de devolución y el administrador aprueba o rechaza la solicitud.
                                </p>

                                <p class="mb-0 fw-bold text-warning-emphasis">
                                    Viernes se pueden devolver equipos solamente por caso especial.
                                </p>

                                <p class="mb-0 fw-bold text-warning-emphasis">
                                    Sábados y Domingos no hay servicio.
                                </p>
                            </div>

                            {{-- Required fields note --}}
                            <p class="text-bold mb-4">
                                <span class="text-danger">*</span> Campos requeridos
                            </p>

                            {{-- Pickup date --}}
                            <div class="mb-3">
                                <label for="pickup_date" class="form-label fw-semibold">
                                    Fecha de Recogida <span class="text-danger">*</span>
                                </label>
                                <div class="date-picker-wrapper">
                                    <input type="text"
                                           class="form-control form-control-lg date-picker-input border border-dark border-1"
                                           id="pickup_date"
                                           name="pickup_date"
                                           placeholder="Día Mes Año"
                                           autocomplete="off"
                                           inputmode="none"
                                           required>

                                    <button type="button"
                                            class="date-picker-icon"
                                            id="pickupDateIcon"
                                            aria-label="Abrir calendario de recogida">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    Solo días laborables permitidos. Si la solicitud se realiza después de la 1:00 PM,
                                    no se permitirá seleccionar el próximo día laborable inmediato.
                                </div>                                <div class="invalid-feedback d-block" id="pickup_date_error"></div>
                            </div>

                            {{-- Pickup time --}}
                            <div class="mb-3">
                                <label for="pickup_time" class="form-label fw-semibold">
                                    Hora de Recogida <span class="text-danger">*</span>
                                </label>
                                <select id="pickup_time"
                                        name="pickup_time"
                                        class="form-select form-select-lg border border-dark border-1"
                                        required>
                                    <option value="">Selecciona una hora</option>
                                    <option value="08:00:00">8:00 AM</option>
                                    <option value="08:30:00">8:30 AM</option>
                                    <option value="09:00:00">9:00 AM</option>
                                    <option value="09:30:00">9:30 AM</option>
                                    <option value="10:00:00">10:00 AM</option>
                                    <option value="10:30:00">10:30 AM</option>
                                    <option value="11:00:00">11:00 AM</option>
                                    <option value="11:30:00">11:30 AM</option>
                                    <option value="12:00:00">12:00 PM</option>
                                    <option value="12:30:00">12:30 PM</option>
                                    <option value="13:00:00">1:00 PM</option>
                                </select>
                                <div class="form-text">Horario disponible entre 8:00 AM y 1:00 PM.</div>
                                <div class="invalid-feedback d-block" id="pickup_time_error"></div>
                            </div>

                            <hr class="my-4">

                            {{-- Optional special case toggle for requests outside regular rules --}}
                            <div class="form-check mb-2">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="special_case"
                                       name="special_case"
                                       value="1">
                                <label class="form-check-label fw-semibold" for="special_case">
                                    Caso Especial (Necesito el equipo fuera del horario regular)
                                </label>
                            </div>

                            <p class="text-muted ms-4 mb-3">
                                Los casos especiales requieren aprobación manual del administrador
                            </p>

                            {{-- Extra fields shown only when special case is checked --}}
                            <div id="specialCaseFields" class="d-none">
                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-semibold">
                                        Fecha de Devolución Propuesta <span class="text-danger">*</span>
                                    </label>
                                    <div class="date-picker-wrapper">
                                        <input type="text"
                                               class="form-control form-control-lg date-picker-input border border-dark border-1"
                                               id="return_date"
                                               name="return_date"
                                               placeholder="Día Mes Año"
                                               autocomplete="off"
                                               inputmode="none">

                                        <button type="button"
                                                class="date-picker-icon"
                                                id="returnDateIcon"
                                                aria-label="Abrir calendario de devolución">
                                            <i class="bi bi-calendar3"></i>
                                        </button>
                                    </div>

                                    <div class="form-text">
                                        Debe ser al menos un día después de la fecha de recogida. No se permiten sábados ni domingos.
                                    </div>
                                    <div class="invalid-feedback d-block" id="return_date_error"></div>
                                </div>

                                <div class="mb-3">
                                    <label for="special_reason" class="form-label fw-semibold">
                                        Razón del Caso Especial <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        class="form-control form-control-lg border border-dark border-1"
                                        id="special_reason"
                                        name="special_reason"
                                        rows="4"
                                        placeholder="Explica por qué necesitas el equipo por más tiempo"
                                    ></textarea>
                                    <div class="form-text">Mínimo 10 caracteres. Máximo 500 caracteres. Solo letras, números, espacios, punto, coma y guion. </div>
                                    <div class="invalid-feedback d-block" id="special_reason_error"></div>
                                </div>

                                {{-- Warning reminding the user that the request needs manual approval --}}
                                <div class="alert alert-warning border-warning-subtle rounded-0 shadow-sm">
                                    <strong>Caso Especial:</strong>
                                    Tu solicitud requerirá aprobación manual del administrador de inventario.
                                </div>
                            </div>

                            <hr class="my-4">

                            {{-- Loan conditions and acceptance checkbox --}}
                            <div class="border border-dark border-1 rounded-4 p-4 bg-light-subtle">
                                <h5 class="fw-bold text-secondary mb-3">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    Condiciones de préstamo
                                </h5>

                                <p class="text-muted mb-3">
                                    Antes de enviar la solicitud debes aceptar las siguientes condiciones del préstamo:
                                </p>



                                <p class="text-danger fw-semibold mb-2 mt-3">
                                    Debes traer tu ID de estudiante o empleado para poder recoger tu pedido.
                                </p>
                                <p class="text-danger fw-semibold mb-2 mt-3">
                                    No puedes cancelar una solicitud en ningún momento.
                                </p>

                                <p class="text-muted mb-2 mt-3">
                                    En el caso de una solicitud normal, recibirás un email de confirmación automáticamente
                                    si no se alcanzó el límite de pedidos por día.

                                    <span class=" text-bg-warning ms-2">
                                    {{ $todayRequests }} / {{ $dailyRequestLimit }} pedidos usados hoy
                                </span>

                                </p>

                                <p class="text-muted mb-2 mt-3">
                                    En el caso de una solicitud especial, recibirás un email de confirmación o negación una vez el administrador de inventario maneje la solicitud.
                                </p>

                                <p class="text-muted mb-2 mt-3">
                                    De tener algún inconveniente, contacta al administrador del inventario.

                                    @foreach($inventoryAdmin as $admin)
                                        <a href="mailto:{{ $admin->email }}" class="text-success text-decoration-none d-block">
                                            {{ $admin->email }}
                                        </a>
                                    @endforeach

                                </p>

                                <p class="text-muted mb-2 mt-3">
                                    Esta información también está disponible en el pie de la página.
                                </p>

                                <div class="form-check mb-2">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="accept_terms"
                                           name="accept_terms"
                                           value="1"
                                           required>
                                    <label class="form-check-label fw-semibold" for="accept_terms">
                                        He leído y acepto las condiciones del préstamo<span class="text-danger">*</span>
                                    </label>
                                </div>
                                <div class="invalid-feedback d-block" id="accept_terms_error"></div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Cart modal action buttons --}}
                <div class="modal-footer border-0 pt-0">
                    {{-- Cancel button which simply closes modal --}}
                    <button type="button"
                            class="btn btn-outline-secondary btn-lg"
                            data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    @if(!empty($cart))
                        {{-- Confirmation button to submit request--}}
                        <button type="submit"
                                class="btn btn-success btn-lg"
                                id="submitLoanRequest">
                            <i class="bi bi-check-circle me-1"></i>
                            Enviar Solicitud
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden delete forms used when removing a cart item after confirmation --}}
@foreach($cart as $item)
    <form
        id="remove-cart-item-{{ $item['equipment_id'] }}"
        method="POST"
        action="{{ route('cart.remove', $item['equipment_id']) }}"
        style="display: none;"
    >
        @csrf
        @method('DELETE')
    </form>
@endforeach

{{-- Confirmation modal shown before removing an item from the cart --}}
<div class="modal fade" id="removeCartConfirmModal" tabindex="-1" aria-labelledby="removeCartConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header border-0 pb-0 d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="modal-title fw-bold mb-0" id="removeCartConfirmModalLabel">
                        Remover del carrito
                    </h4>
                    <p class="text-muted mb-0" id="removeCartConfirmText">
                        ¿Estás seguro que quieres remover este item del carrito?
                    </p>
                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
            </div>

            <div class="modal-footer border-0 pt-0">
                {{-- Button to cancel removal of item--}}
                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                {{-- Button to accept removal of item--}}
                <button type="button"
                        class="btn btn-danger"
                        id="confirmRemoveCartItem">
                    Sí, Remover
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Toast notifications for success feedback across cart, requests, and terms updates --}}
<div class="toast-container position-fixed bottom-0 start-0 p-3">

    <div id="cartToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         style="width: auto; max-width: fit-content;">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0 pe-1"
                 id="cartToastMessage">
                Equipo añadido al carrito
            </div>

            <button type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; transform: scale(0.8);">
            </button>
        </div>
    </div>

    <div id="errorToast"
         class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0" id="errorToastMessage">
                Error.
            </div>

            <button type="button"
                    class="btn-close me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none;">
            </button>
        </div>
    </div>

    <div id="submitToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0" id="submitToastMessage">
                Solicitud enviada correctamente. Pronto recibirás un email con el estado de tu solicitud.
            </div>

            <button type="button"
                    class="btn-close me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none;">
            </button>
        </div>
    </div>

    <div id="cartRemovedToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         style="width: auto; max-width: fit-content;">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0 pe-1"
                 id="cartRemovedToastMessage">
                Equipo removido del carrito correctamente.
            </div>

            <button type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; transform: scale(0.8);">
            </button>
        </div>
    </div>

    <div id="termsUpdatedToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         style="width: auto; max-width: fit-content;">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0 pe-1"
                 id="termsUpdatedToastMessage">
                Términos y condiciones actualizados correctamente.
            </div>

            <button type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; transform: scale(0.8);">
            </button>
        </div>
    </div>

</div>

{{-- Footer with institutional support, department location, and additional contact information --}}
<footer class="bg-light text-dark mt-5 pt-4 border-top">
    <div class="container">
        <div class="row text-start align-items-start">

            {{-- General support contact information --}}
            <div class="col-md-4 mb-3">
                <h5 class="d-flex align-items-center mb-2">
                    <i class="bi bi-question-circle me-2 text-success"></i>
                    Ayuda y Soporte
                </h5>
                <p class="text-muted">
                    Soporte técnico y administración general del sistema
                </p>

                <div class="d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-envelope text-muted"></i>

                    <div>
                        @foreach($superAdmin as $admin)
                            <div>
                                <a href="mailto:{{ $admin->email }}" class="text-success text-decoration-none">
                                    {{ $admin->email }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-telephone text-muted"></i>
                    <span class="text-success">+1 (787)-832-4040 Ext. 3841, 2008</span>
                </div>
            </div>

            {{-- Department physical address --}}
            <div class="col-md-4 mb-3">
                <h5>Departamento de Kinesiología</h5>
                <div class="d-flex align-items-start gap-2 text-muted">
                    <i class="bi bi-geo-alt mt-1 flex-shrink-0"></i>

                    <div>
                        259 Norte Blvd. Alfonso Valdés Cobián<br>
                        Oficina A-2 Coliseo Rafael A. Mangual<br>
                        Mayagüez, Puerto Rico
                    </div>
            </div>
            </div>

            {{-- Additional section-specific contact emails --}}
            <div class="col-md-4 mb-3">
                <h5 class="mb-2">Contactos Adicionales</h5>

                <p class="mb-3">
                    <b>Kinventario</b><br>

                    @foreach($inventoryAdmin as $admin)
                        <a href="mailto:{{ $admin->email }}" class="text-success text-decoration-none d-block">
                            {{ $admin->email }}
                        </a>
                    @endforeach
                </p>

                <p>
                    <b>Kinemercado</b><br>

                    @foreach($marketAdmin as $admin)
                        <a href="mailto:{{ $admin->email }}" class="text-success text-decoration-none d-block">
                            {{ $admin->email }}
                        </a>
                    @endforeach
                </p>
            </div>
        </div>

        <hr>

        {{-- Footer bottom section with institutional note and terms link --}}
        <div class="text-center pb-3">
            <p class="mb-1">
                © 2026 MAIKINE - Portal del Departamento de Kinesiología | Colegio de Artes y Ciencias | Recinto Universitario de Mayagüez |<br> Universidad de Puerto Rico.
                Todos los derechos reservados.
            </p>
            <a href="#"
               class="text-success d-block mb-1"
               data-bs-toggle="modal"
               data-bs-target="#termsModal">
                Términos y Condiciones
            </a>
            <small class="text-muted">
                Sistema exclusivo para la comunidad de UPRM
            </small>
        </div>
    </div>
</footer>

{{-- Terms and conditions modal with embedded PDF preview and update form --}}
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="modal-title fw-bold mb-0" id="termsModalLabel">
                            Términos y Condiciones
                        </h4>

                        <button type="button"
                                class="btn-close ms-3"
                                data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mt-2 mb-3">                        <p class="text-muted mb-0">
                            Lee los términos y condiciones que aceptaste al usar el sistema.
                        </p>

                        @auth
                            @if(trim(auth()->user()->role) === 'Super Administrador')
                                <form id="updateTermsForm"
                                      method="POST"
                                      action="{{ route('terms.update') }}"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <input
                                        type="file"
                                        id="termsPdfInput"
                                        name="terms_pdf"
                                        accept="application/pdf,.pdf"
                                        class="d-none"
                                    >

                                    <div class="d-flex flex-column align-items-start align-items-md-end gap-2">
                                        <button type="button"
                                                class="btn btn-success"
                                                id="openTermsPdfPicker">
                                            <i class="bi bi-upload me-1"></i>
                                            Actualizar términos y condiciones
                                        </button>

                                        <div id="termsPdfSelectedName" class="text-muted small d-none"></div>

                                        <div id="termsPdfError" class="text-danger small d-none"></div>

                                        @error('terms_pdf')
                                        <div class="text-danger small mt-1">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </form>
                            @endif
                        @endauth
                    </div>

                </div>
            </div>

            <div class="modal-body pt-4">
                <div class="border rounded-4 overflow-hidden mb-3" style="height: 75vh;">

                    <iframe
                        src="{{ asset('documents/terms_conditions.pdf') }}?v={{ file_exists(public_path('documents/terms_conditions.pdf')) ? filemtime(public_path('documents/terms_conditions.pdf')) : time() }}"
                        width="100%"
                        height="100%"
                        style="border: 0;"
                        title="Términos y Condiciones PDF">
                    </iframe>

                </div>

            </div>

            <div class="modal-footer border-0 pt-3">
                <a href="{{ asset('documents/terms_conditions.pdf') }}?v={{ file_exists(public_path('documents/terms_conditions.pdf')) ? filemtime(public_path('documents/terms_conditions.pdf')) : time() }}"
                   target="_blank"
                   class="btn btn-outline-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    Abrir en otra pestaña
                </a>

                <button type="button"
                        class="btn btn-outline-success"
                        data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Final confirmation modal before replacing the terms PDF --}}
<div class="modal fade" id="confirmTermsUpdateModal" tabindex="-1" aria-labelledby="confirmTermsUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="modal-title fw-bold mb-0" id="confirmTermsUpdateModalLabel">
                            Confirmar Actualización
                        </h4>

                        <button type="button"
                                class="btn-close ms-3"
                                data-bs-dismiss="modal"
                                aria-label="Cerrar"></button>
                    </div>

                    <p class="text-muted mt-2 mb-0">
                        ¿Estás seguro que quieres cambiar los términos y condiciones?
                    </p>
                </div>
            </div>

            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button" class="btn btn-success" id="confirmTermsUpdateBtn">
                    Sí, actualizar
                </button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
