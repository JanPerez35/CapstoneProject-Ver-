@props([
    'title' => 'MAIKINE'
])
    <!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <div class="d-flex align-items-center text-end">
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
                        Mi Chats
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
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ empty($cart) ? 'd-none' : '' }}" id="cartCount">
                            {{ collect($cart)->sum('quantity') }}
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

            <form method="POST" action="{{ route('cart.checkout') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger mx-3 mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="modal-header border-0 pb-0">
                    <div>
                        <h3 class="modal-title fw-bold mb-1" id="cartModalLabel">Carrito de Préstamos</h3>
                        <p class="text-muted mb-0">Revisa tu selección y completa los detalles del préstamo</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="mb-4">
                        <h4 class="fw-bold mb-3">
                            Equipos Seleccionados ({{ count($cart) }} ítems)
                        </h4>

                        <div class="border rounded-4 overflow-hidden">
                            <div class="row g-0 px-3 py-3 fw-semibold border-bottom bg-light">
                                <div class="col-6">Equipo</div>
                                <div class="col-3 text-center">Cantidad</div>
                                <div class="col-3 text-center">Acciones</div>
                            </div>

                            <div id="cartItemsContainer">
                                @if(empty($cart))
                                    <div class="p-4 text-muted">Tu carrito está vacío.</div>
                                @else
                                    @foreach($cart as $item)
                                        <div class="row align-items-center px-3 py-3 border-bottom">
                                            <div class="col-6 d-flex align-items-center gap-3">
                                                <img
                                                    src="{{ !empty($item['equipment_photo_url']) ? asset('storage/' . $item['equipment_photo_url']) : asset('images/kinventory_images/default.jpg') }}"
                                                    style="width: 60px; height: 60px; object-fit: contain;"
                                                    class="rounded"
                                                    alt="{{ $item['description'] ?? 'Equipo' }}"
                                                >
                                                <span class="fw-semibold">{{ $item['description'] ?? 'Sin descripción' }}</span>
                                            </div>

                                            <div class="col-3 text-center">
                                                {{ $item['quantity'] ?? 0 }}
                                            </div>

                                            <div class="col-3 text-center">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="document.getElementById('remove-cart-item-{{ $item['equipment_id'] }}').submit();"
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
                            <h3 class="fw-bold mb-4">Detalles del Préstamo</h3>
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
                                <label for="pickup_date" class="form-label fw-semibold">
                                    Fecha de Recogida <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg" id="pickup_date" name="pickup_date" required>
                                <div class="invalid-feedback d-block" id="pickup_date_error"></div>
                            </div>

                            <div class="mb-3">
                                <label for="pickup_time" class="form-label fw-semibold">
                                    Hora de Recogida <span class="text-danger">*</span>
                                </label>
                                <select id="pickup_time" name="pickup_time" class="form-select form-select-lg" required>
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
                                <div class="invalid-feedback d-block" id="pickup_time_error"></div>
                            </div>

                            <div class="mb-3">
                                <label for="commentary" class="form-label fw-semibold">Comentario general</label>
                                <textarea
                                    class="form-control form-control-lg"
                                    id="commentary"
                                    name="commentary"
                                    rows="3"
                                    maxlength="1000"
                                    placeholder="Comentario opcional sobre el préstamo"
                                ></textarea>
                                <div class="invalid-feedback d-block" id="commentary_error"></div>
                            </div>

                            <hr class="my-4">

                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="special_case" name="special_case" value="1">
                                <label class="form-check-label fw-semibold" for="special_case">
                                    Caso Especial (Necesito el equipo fuera del horario regular)
                                </label>
                            </div>

                            <p class="text-muted ms-4 mb-3">
                                Los casos especiales requieren aprobación manual del administrador
                            </p>

                            <div id="specialCaseFields" class="d-none">
                                <div class="mb-3">
                                    <label for="return_date" class="form-label fw-semibold">
                                        Fecha de Devolución Propuesta <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control form-control-lg" id="return_date" name="return_date">
                                    <div class="invalid-feedback d-block" id="return_date_error"></div>
                                </div>

                    <div class="mb-3">
                        <label for="special_reason" class="form-label fw-semibold">
                            Razón del Caso Especial <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    class="form-control form-control-lg"
                                    id="special_reason"
                                    name="special_reason"
                                    rows="4"
                                    maxlength="1000"
                                    placeholder="Explica por qué necesitas el equipo por más tiempo"
                                ></textarea>
                                <div class="invalid-feedback d-block" id="special_reason_error"></div>
                            </div>

                                <div class="alert alert-warning border-warning-subtle rounded-4">
                                    <strong><i class="bi bi-exclamation-circle me-2"></i>Caso Especial:</strong>
                                    Tu solicitud requerirá aprobación manual del administrador de inventario.
                                </div>
                            </div>

                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="accept_terms" name="accept_terms" value="1" required>
                                <label class="form-check-label" for="accept_terms">
                                    Acepto los términos y condiciones del préstamo <span class="text-danger">*</span>
                                </label>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    @if(!empty($cart))
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-1"></i>
                            Enviar Solicitud ({{ count($cart) }} ítems)
                        </button>
                    @endif
                </div>
            </form>

        </div>
    </div>
</div>

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
                    <a href="mailto:superadmin@uprm.edu" class="text-success text-decoration-none">
                        superadmin@uprm.edu
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
                    <a href="mailto:inventario@kinesiologia.edu" class="text-success text-decoration-none">
                        inventario@uprm.edu
                    </a>
                </p>

                <p>
                    <b>Kinemercado</b><br>
                    <a href="mailto:mercado@kinesiologia.edu" class="text-success text-decoration-none">
                        mercado@uprm.edu
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
            <a href="{{route('kinemarket')}}" class="text-success d-block mb-1">
                Términos y Condiciones
            </a>
            <small class="text-muted">
                Sistema exclusivo para la comunidad de UPRM
            </small>
        </div>
    </div>
</footer>
</body>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#cartModal form');
    if (!form) return;

    const pickupDate = document.getElementById('pickup_date');
    const pickupTime = document.getElementById('pickup_time');
    const commentary = document.getElementById('commentary');
    const specialCase = document.getElementById('special_case');
    const specialCaseFields = document.getElementById('specialCaseFields');
    const returnDate = document.getElementById('return_date');
    const specialReason = document.getElementById('special_reason');
    const acceptTerms = document.getElementById('accept_terms');
    const submitBtn = document.getElementById('submitLoanRequest');

    const pickupDateError = document.getElementById('pickup_date_error');
    const pickupTimeError = document.getElementById('pickup_time_error');
    const commentaryError = document.getElementById('commentary_error');
    const returnDateError = document.getElementById('return_date_error');
    const specialReasonError = document.getElementById('special_reason_error');

    function setError(field, errorEl, message) {
        if (field) field.classList.add('is-invalid');
        if (errorEl) errorEl.textContent = message;
    }

    function clearError(field, errorEl) {
        if (field) field.classList.remove('is-invalid');
        if (errorEl) errorEl.textContent = '';
    }

    function isBlockedPickupDay(dateString) {
        const date = new Date(dateString + 'T00:00:00');
        const day = date.getDay();
        return day === 5 || day === 6 || day === 0;
    }

    function todayString() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d.toISOString().split('T')[0];
    }

    function toggleSpecialCaseFields() {
        if (!specialCase || !specialCaseFields) return;

        if (specialCase.checked) {
            specialCaseFields.classList.remove('d-none');
            if (returnDate) returnDate.required = true;
            if (specialReason) specialReason.required = true;
        } else {
            specialCaseFields.classList.add('d-none');
            if (returnDate) {
                returnDate.required = false;
                returnDate.value = '';
                clearError(returnDate, returnDateError);
            }
            if (specialReason) {
                specialReason.required = false;
                specialReason.value = '';
                clearError(specialReason, specialReasonError);
            }
        }
    }

    function validateForm(showErrors = true) {
        let valid = true;
        const today = todayString();

        if (showErrors) {
            clearError(pickupDate, pickupDateError);
            clearError(pickupTime, pickupTimeError);
            clearError(commentary, commentaryError);
            clearError(returnDate, returnDateError);
            clearError(specialReason, specialReasonError);
        }

        if (!pickupDate || !pickupDate.value) {
            if (showErrors) setError(pickupDate, pickupDateError, 'La fecha de recogida es obligatoria.');
            valid = false;
        } else if (pickupDate.value < today) {
            if (showErrors) setError(pickupDate, pickupDateError, 'La fecha no puede ser en el pasado.');
            valid = false;
        } else if (isBlockedPickupDay(pickupDate.value)) {
            if (showErrors) setError(pickupDate, pickupDateError, 'No se permiten viernes, sábados ni domingos.');
            valid = false;
        }

        if (!pickupTime || !pickupTime.value) {
            if (showErrors) setError(pickupTime, pickupTimeError, 'La hora de recogida es obligatoria.');
            valid = false;
        }

        if (commentary && commentary.value.trim().length > 1000) {
            if (showErrors) setError(commentary, commentaryError, 'El comentario no puede exceder 1000 caracteres.');
            valid = false;
        }

        if (specialCase && specialCase.checked) {
            if (!returnDate || !returnDate.value) {
                if (showErrors) setError(returnDate, returnDateError, 'La fecha de devolución es obligatoria.');
                valid = false;
            } else if (pickupDate && pickupDate.value && returnDate.value < pickupDate.value) {
                if (showErrors) setError(returnDate, returnDateError, 'La devolución no puede ser antes de la recogida.');
                valid = false;
            }

            if (!specialReason || !specialReason.value.trim()) {
                if (showErrors) setError(specialReason, specialReasonError, 'La razón del caso especial es obligatoria.');
                valid = false;
            } else if (specialReason.value.trim().length < 10) {
                if (showErrors) setError(specialReason, specialReasonError, 'La razón debe tener al menos 10 caracteres.');
                valid = false;
            } else if (specialReason.value.trim().length > 1000) {
                if (showErrors) setError(specialReason, specialReasonError, 'La razón no puede exceder 1000 caracteres.');
                valid = false;
            }
        }

        if (!acceptTerms || !acceptTerms.checked) {
            valid = false;
        }

        if (submitBtn) {
            submitBtn.disabled = !valid;
        }

        return valid;
    }

    [pickupDate, pickupTime, commentary, returnDate, specialReason, acceptTerms].forEach(el => {
        if (!el) return;
        el.addEventListener('input', () => validateForm(false));
        el.addEventListener('change', () => validateForm(false));
    });

    if (specialCase) {
        specialCase.addEventListener('change', () => {
            toggleSpecialCaseFields();
            validateForm(false);
        });
    }

    form.addEventListener('submit', function (e) {
        toggleSpecialCaseFields();
        if (!validateForm(true)) {
            e.preventDefault();
        }
    });

    toggleSpecialCaseFields();
    validateForm(false);
});
</script>

</html>
