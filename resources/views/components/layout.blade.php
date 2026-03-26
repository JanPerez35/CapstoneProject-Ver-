@props([
    'title' => 'MAIKINE'
])
    <!doctype html>
<html lang="en">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$title}}</title>
</head>
<body>
<div class="container">
    <header class="d-flex flex-wrap justify-content-center py-3">
        <a
            href="/"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none"
        >
            <svg class="bi me-2" width="40" height="40" aria-hidden="true">
                <use xlink:href="#bootstrap"></use>
            </svg>
            <span class="fs-4 fw-bold text-success">MAIKINE</span>
        </a>

        <ul class="nav nav-pills align-items-center">
            <li class="nav-item">
                <a href="{{ route('my_profile') }}"
                   class="btn btn-outline {{ request()->routeIs('my_profile') ? 'btn-success' : 'btn-outline-success' }} btn-md mx-3">
                    <i class="bi bi-person-fill"></i>
                    Mi Perfil
                </a>
            </li>

            <li class="nav-item">
                <button
                    type="button"
                    class="btn btn-outline-success position-relative mx-3"
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
                <a href="#" class="btn btn-outline-success btn-md">
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar Sesión
                </a>
            </li>
        </ul>
    </header>
</div>

<main>{{$slot}}</main>

{{-- Global Cart Modal --}}
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h3 class="modal-title fw-bold mb-1" id="cartModalLabel">Carrito de Préstamos</h3>
                    <p class="text-muted mb-0">Revisa tu selección y completa los detalles del préstamo</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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


                <div id="loanDetailsSection" class="mb-4 d-none">                    <h3 class="fw-bold mb-4">Detalles del Préstamo</h3>

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
                        <label for="loanFullName" class="form-label fw-semibold">Nombre Completo *</label>
                        <input
                            type="text"
                            class="form-control form-control-lg"
                            id="loanFullName"
                            placeholder="Tu nombre"
                            minlength="5"
                            maxlength="80"
                            required
                        >
                        <div class="form-text">Entre 5 y 80 caracteres.</div>
                    </div>

                    <div class="mb-3">
                        <label for="loanPickupDate" class="form-label fw-semibold">Fecha de Recogida *</label>
                        <input
                            type="date"
                            class="form-control form-control-lg"
                            id="loanPickupDate"
                            required
                        >
                        <div class="form-text">Solo días futuros. No se permiten viernes, sábados ni domingos.</div>
                    </div>

{{--                    Yeah im not letting the user write pick up at 12 am -Jan--}}
                    <div class="mb-3">
                        <label for="pickupTimeBlock" class="form-label fw-semibold">Hora de Recogida *</label>
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
                            <label for="returnDate" class="form-label fw-semibold">Fecha de Devolución Propuesta *</label>
                            <input
                                type="date"
                                class="form-control form-control-lg"
                                id="returnDate"
                            >
                            <div class="form-text">Debe ser una fecha futura.</div>
                        </div>

                        <div class="mb-3">
                            <label for="specialReason" class="form-label fw-semibold">Razón del Caso Especial *</label>
                            <textarea
                                class="form-control form-control-lg"
                                id="specialReason"
                                rows="4"
                                maxlength="500"
                                placeholder="Explica por qué necesitas el equipo por más tiempo"
                            ></textarea>
                            <div class="form-text">Máximo 500 caracteres.</div>
                        </div>

                        <div class="alert alert-warning border-warning-subtle rounded-4">
                            <strong><i class="bi bi-exclamation-circle me-2"></i>Caso Especial:</strong>
                            Tus solicitudes requerirán aprobación manual del administrador de inventario.
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0" id="cartFooterActions">                <button type="button" class="btn btn-outline-secondary btn-lg" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-success btn-lg" id="submitLoanRequest">
                    <i class="bi bi-check-circle me-1"></i>
                    Enviar Solicitud (<span id="submitItemCount">0</span> ítem)
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Global Toast --}}
<div class="toast-container position-fixed bottom-0 start-0 p-3">
    <div id="cartToast" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body fw-semibold" id="cartToastMessage">
                Producto agregado al carrito
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>

    {{--Inventory Request Submitted Toast--}}
    <div id="submitToast" class="toast align-items-center border-0 shadow">
        <div class="d-flex">
            <div class="toast-body fw-semibold text-success">
                Tu solicitud ha sido enviada! Pronto recibirás un email con el estado de tu solicitud.
            </div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>

</div>

<footer class="bg-light text-dark mt-5 pt-4 border-top">
    <div class="container">
        <div class="row text-start align-items-start">
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

        <div class="text-center pb-3">
            <p class="mb-1">
                © 2026 MAIKINE - Portal del Departamento de Kinesiología | Colegio de Artes y Ciencias | Recinto Universitario de Mayagüez |<br>
                Universidad de Puerto Rico. Todos los derechos reservados.
                <br>
                Terminos y Condiciones (Reemplazar por link)
            </p>
            <small class="text-muted">
                Sistema exclusivo para comunidad de UPRM
            </small>
        </div>
    </div>
</footer>
</body>
</html>
