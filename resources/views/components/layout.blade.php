@props([
    'title' => 'MAIKINE'
])
    <!--Potential backend bridge-->
@php
    $currentUser = auth()->user();

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$title}}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid px-0">
    <!--Webpage Header-->
    <header class="d-flex flex-wrap align-items-center justify-content-between py-2 px-2 border-bottom bg-light">

        <!-- Left-side of the Header -->
        <a href="/kinemarket"
           class="d-flex align-items-center mb-2 mb-md-0 text-decoration-none">

            <img src="/images/kines_logo.png"
                 alt="Logo"
                 style="height: 75px; width:auto;"
                 class="me-2">

            <span class="fs-3 fw-bold text-success m-0">MAIKINE</span>
        </a>

        <div class="d-flex align-items-center gap-3 text-end">
            <div clas="text-end me-2">
                <div class="fw-sembold">{{ $currentUserName }}</div>
                <small class="text-muted">{{ $currentUserRole }}</small>
            </div>
            <ul class="nav nav-pills align-items-center d-flex gap-3">
                <li class="nav-item">
                    <a href="{{ route('my_profile') }}"
                       class="btn btn-outline {{ request()->routeIs('my_profile') ? 'btn-success' : 'btn-outline-success' }} btn-md">
                        <i class="bi bi-person-fill"></i>
                        Mi Perfil
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{route('my_messages')}}"
                       class="btn btn-outline {{request()->routeIs('my_messages') ? 'btn-success' : 'btn-outline-success'}} btn-md">
                        <i class="bi bi-chat-left-text"></i>
                        Mis Chats
                    </a>
                </li>

                <li class="nav-item">
                    <button
                        type="button"
                        class="btn btn-outline-success position-relative"
                        data-bs-toggle="modal"
                        data-bs-target="#cartModal"
                    >
                        <i class="bi bi-cart3 me-1"></i>
                        Carrito
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="cartCount">
                            0
                        </span>
                    </button>
                </li>

                <li class="nav-item">
                    <a href="/"
                       class="btn btn-success">
                        <i class="bi bi-box-arrow-right"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </header>
</div>

<main>{{$slot}}</main>

{{-- Global Cart Modal --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="modal-title fw-bold mb-0" id="cartModalLabel">
                            Carrito de Préstamos
                        </h3>

                        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <p class="text-muted mt-2 mb-0">
                        Revisa tu selección y completa los detalles del préstamo
                    </p>
                </div>
            </div>


            <div class="modal-body pt-3">
                <div class="mb-4">
                    <h4 class="fw-bold mb-3">Equipos Seleccionados (<span id="cartItemCountLabel">0</span> ítems)</h4>

                    <div class="border rounded-4 overflow-hidden">
                        <div class="row g-0 px-3 py-3 fw-semibold border-bottom bg-light">
                            <div class="col-6">Equipo</div>
                            <div class="col-3 text-center">Cantidad</div>
                            <div class="col-3 text-center">Acciones</div>
                        </div>

                        <div id="cartItemsContainer">
                            <div class="p-4 text-muted">Tu carrito está vacío.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div id="emptyCartSection" class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>

                    <h5 class="fw-bold">Tu carrito está vacío</h5>
                    <p class="text-muted">Agrega equipos antes de continuar.</p>

                    <button
                        type="button"
                        class="btn btn-success mt-3"
                        data-bs-dismiss="modal"
                    >
                        Continuar explorando
                    </button>
                </div>

                <div id="loanDetailsSection" class="mb-4 d-none">
                    <h3 class="fw-bold mb-2">Detalles del Préstamo</h3>
                    <p class="text-muted mb-4">
                        <span class="text-danger">*</span> Campos requeridos
                    </p>

                    <div class="border rounded-4 p-4 mb-4 bg-light-subtle">
                        <h5 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-exclamation-circle me-2"></i>
                            Política de Préstamo:
                        </h5>

                        <p class="mb-2 text-muted">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                            Solicitud enviada fuera de horas laborables (8am-1pm) - préstamo disponible desde el siguiente día laborable
                        </p>

                        <p class="mb-2 fw-bold text-danger">
                            El equipo debe ser devuelto el mismo día antes de las 3 PM.
                        </p>

                        <p class="mb-0 fw-bold text-warning-emphasis">
                            Viernes solo se pueden devolver equipos. Sábados y Domingos no hay servicio.
                        </p>
                    </div>


                    <div class="mb-3">
                        <label for="loanPickupDate" class="form-label fw-semibold">
                            Fecha de Recogida <span class="text-danger">*</span>
                        </label>
                        <input
                            type="date"
                            class="form-control form-control-lg"
                            id="loanPickupDate"
                            required
                        >
                        <div class="form-text">Solo días futuros. No se permiten viernes, sábados ni domingos.</div>
                        <div class="invalid-feedback d-block" id="loanPickupDateError"></div>
                    </div>

                    <div class="mb-3">
                        <label for="pickupTimeBlock" class="form-label fw-semibold">
                            Hora de Recogida <span class="text-danger">*</span>
                        </label>
                        <select id="pickupTimeBlock" class="form-select form-select-lg" required>
                            <option value="">Selecciona una hora</option>
                            <option value="08:00">8:00 AM</option>
                            <option value="08:30">8:30 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="09:30">9:30 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="10:30">10:30 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="11:30">11:30 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="12:30">12:30 PM</option>
                            <option value="13:00">1:00 PM</option>
                        </select>
                        <div class="form-text">Horario disponible entre 8:00 AM y 1:00 PM.</div>
                        <div class="invalid-feedback d-block" id="pickupTimeBlockError"></div>
                    </div>

                    <hr class="my-4">

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="specialCaseCheck">
                        <label class="form-check-label fw-semibold" for="specialCaseCheck">
                            Caso Especial (Necesito el equipo fuera del horario regular)
                        </label>
                    </div>

                    <p class="text-muted ms-4 mb-3">
                        Los casos especiales requieren aprobación manual del administrador
                    </p>

                    <div id="specialCaseFields" class="d-none">
                        <div class="mb-3">
                            <label for="returnDate" class="form-label fw-semibold">
                                Fecha de Devolución Propuesta <span class="text-danger">*</span>
                            </label>
                            <input
                                type="date"
                                class="form-control form-control-lg"
                                id="returnDate"
                            >
                            <div class="form-text">Debe ser una fecha futura.</div>
                            <div class="invalid-feedback d-block" id="returnDateError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="specialReason" class="form-label fw-semibold">
                                Razón del Caso Especial <span class="text-danger">*</span>
                            </label>
                            <textarea
                                class="form-control form-control-lg"
                                id="specialReason"
                                rows="4"
                                maxlength="500"
                                placeholder="Explica por qué necesitas el equipo por más tiempo"
                            ></textarea>
                            <div class="form-text">Máximo 500 caracteres.</div>
                            <div class="invalid-feedback d-block" id="specialReasonError"></div>
                        </div>

                        <div class="alert alert-warning border-warning-subtle rounded-4">
                            <strong><i class="bi bi-exclamation-circle me-2"></i>Caso Especial:</strong>
                            Tus solicitudes requerirán aprobación manual del administrador de inventario.
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Términos y condiciones obligatorios --}}
                    <div class="border rounded-4 p-4 bg-light-subtle">
                        <h5 class="fw-bold text-secondary mb-3">
                            <i class="bi bi-file-earmark-text me-2"></i>
                            Términos y Condiciones
                        </h5>

                        <p class="text-muted mb-3">
                            Antes de enviar la solicitud, debes aceptar los términos y condiciones del préstamo.
                        </p>

                        <div class="form-check mb-2">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                id="loanTermsCheck"
                                required
                            >
                            <label class="form-check-label fw-semibold" for="loanTermsCheck">
                                He leído y acepto los términos y condiciones del préstamo.
                            </label>
                        </div>

                        <div class="invalid-feedback d-block" id="loanTermsError"></div>

                        <p class="text-danger fw-semibold mb-2 mt-3">
                            No puedes cancelar el pedido una vez lo hagas.
                        </p>

                        <p class="text-muted mb-2">
                            De tener algún inconveniente, contacta al administrador del inventario
                            <a href="mailto:orlando.cruz@upr.edu" class="text-success text-decoration-none fw-semibold">
                                orlando.cruz@upr.edu
                            </a>.
                        </p>

                        <p class="text-muted mb-0">
                            Esta información también está disponible en el footer de la página.
                        </p>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0" id="cartFooterActions">
                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success btn-lg" id="submitLoanRequest" disabled>
                    <i class="bi bi-check-circle me-1"></i>
                    Enviar Solicitud (<span id="submitItemCount">0</span> ítem)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Global Toast --}}
<div class="toast-container position-fixed bottom-0 start-0 p-3">
    <div id="cartToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         style="width: auto; max-width: fit-content;">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0 pe-1"
                 id="cartToastMessage"
                 style="padding-right: 0;">
                Producto agregado al carrito
            </div>

            <button type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; transform: scale(0.8);">
            </button>
        </div>
    </div>

    {{--Inventory Request Submitted Toast--}}
    <div id="submitToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
         role="alert"
         aria-live="assertive"
         aria-atomic="true">

        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold rounded-0">
                Tu solicitud ha sido enviada! Pronto recibirás un email con el estado de tu solicitud.
            </div>

            <button type="button"
                    class="btn-close me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; filter: none;">
            </button>
        </div>
    </div>
</div>

<!--Webpage Footer-->
<footer class="bg-light text-dark mt-5 pt-4 border-top">
    <div class="container">
        <div class="row text-start align-items-start">

            <!--The first column of the footer-->
            <div class="col-md-4 mb-3">
                <h5 class="d-flex align-items-center mb-2">
                    <i class="bi bi-question-circle me-2 text-success"></i>
                    Ayuda y Soporte
                </h5>
                <p class="text-muted">
                    Soporte técnico y administración general del sistema
                </p>

                <p class="mb-1">
                    <i class="bi bi-envelope text-muted"></i>
                    <a href="mailto:{{ $superAdminEmail }}" class="text-success text-decoration-none">
                        {{ $superAdminEmail }}
                    </a>
                </p>

                <p>
                    <i class="bi bi-telephone text-muted"></i>
                    <span class="text-success">+1 (787)-832-4040 Ext. 3841, 2008</span>
                </p>
            </div>

            <!--The second column of the footer-->
            <div class="col-md-4 mb-3">
                <h5>Departamento de Kinesiología</h5>
                <p class="text-muted mb-0 d-flex">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span>
                        259 Norte Blvd. Alfonso Valdés Cobián<br>
                        Oficina A-2 Coliseo Rafael A. Mangual<br>
                        Mayagüez, Puerto Rico
                    </span>
                </p>
            </div>

            <!--The third column of the footer-->
            <div class="col-md-4 mb-3">
                <h5 class="mb-2">Contactos Adicionales</h5>

                <p class="mb-1">
                    <b>Kinventario</b><br>
                    <a href="mailto:{{ $inventoryAdminEmail }}" class="text-success text-decoration-none">
                        {{ $inventoryAdminEmail }}
                    </a>
                </p>

                <p>
                    <b>Kinemercado</b><br>
                    <a href="mailto:{{ $marketAdminEmail }}" class="text-success text-decoration-none">
                        {{ $marketAdminEmail }}
                    </a>
                </p>
            </div>
        </div>

        <hr>

        <!--All rights reserved & terms and condition information-->
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

{{-- Modal Términos y Condiciones --}}
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">

            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="modal-title fw-bold mb-0" id="termsModalLabel">
                            Términos y Condiciones
                        </h4>

                        <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <p class="text-muted mt-2 mb-0">
                        Lee los términos y condiciones que aceptaste al usar el sistema.
                    </p>
                </div>
            </div>

            <div class="modal-body pt-3">
                <div class="border rounded-4 overflow-hidden" style="height: 75vh;">
                    <iframe
                        src="{{ asset('documents/terms_conditions.pdf') }}"
                        width="100%"
                        height="100%"
                        style="border: 0;"
                        title="Términos y Condiciones PDF">
                    </iframe>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <a href="{{ asset('documents/terms_conditions.pdf') }}"
                   target="_blank"
                   class="btn btn-outline-success">
                    <i class="bi bi-box-arrow-up-right me-1"></i>
                    Abrir en otra pestaña
                </a>

                <button type="button" class="btn btn-success" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
