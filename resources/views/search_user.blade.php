<x-layout title="Buscar Usuarios">
    <x-navbar></x-navbar>

    @vite('resources/js/search_user_validation.js')

    <style>
        .status-active-badge {
            background-color: #7FC61B !important;
            color: white !important;
        }

        .users-pagination .page-link {
            color: #198754;
            border-color: #198754;
            background-color: #fff;
            box-shadow: none;
        }

        .users-pagination .page-link:hover {
            color: #fff;
            background-color: #198754;
            border-color: #198754;
        }

        .users-pagination .page-item.active .page-link {
            color: #fff;
            background-color: #198754;
            border-color: #198754;
        }

        .users-pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
        }
    </style>

    <div class="container py-4">
        <div class="mb-4">
            <h1 class="fw-bold rounded-2">Buscar Usuarios</h1>
            <p class="text-muted mb-0">
                Buscar usuarios y ver sus perfiles, administrar roles y banear/desbanear cuentas
            </p>
        </div>

        {{-- Filters --}}
        <div class="mb-4">
            <div class="row g-3 align-items-stretch mb-3">
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                        <span class="input-group-text bg-white border-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            id="userSearchInput"
                            class="form-control border-0"
                            placeholder="Buscar por nombre o correo..."
                        >
                    </div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="button" class="btn btn-success h-100 fw-semibold" id="searchUsersBtn">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <select id="roleFilterSelect" class="form-select border-2 border-dark">
                        <option value="all" selected>Todos los Roles</option>
                        <option value="Usuario">Usuario</option>
                        <option value="Admin Super">Admin Super</option>
                        <option value="Admin Inventario">Admin Inventario</option>
                        <option value="Admin Facilidades">Admin Facilidades</option>
                        <option value="Admin Mercado">Admin Mercado</option>
                    </select>
                </div>

                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" id="clearUserFilters">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="usersEmptyState" class="card border-0 shadow-sm rounded-0 d-none">
            <div class="card-body py-5 text-center">
                <i class="bi bi-people fs-1 text-muted"></i>
                <h4 class="fw-bold mt-3">No se encontraron usuarios</h4>
                <p class="text-muted mb-0">Intenta con otro nombre, correo o filtro de rol.</p>
            </div>
        </div>

        {{-- Users list --}}
        <div id="usersList" class="d-grid gap-3">

            {{-- User 1 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="John Davis"
                 data-email="john.davis@university.edu"
                 data-role="Usuario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            John Davis
                                        </a>

                                        <span class="badge user-role-badge bg-primary-subtle text-primary-emphasis px-2 py-1 small">
                                            Usuario
                                        </span>
                                    </div>

                                    <div class="text-muted small">john.davis@university.edu</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-0 role-select">
                                <option selected>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge user-status-badge status-active-badge align-self-start px-2 py-1 rounded-0">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 2 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Sarah Chen"
                 data-email="sarah.chen@university.edu"
                 data-role="Usuario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Sarah Chen
                                        </a>

                                        <span class="badge user-role-badge bg-primary-subtle text-primary-emphasis px-2 py-1 small">
                                            Usuario
                                        </span>
                                    </div>

                                    <div class="text-muted small">sarah.chen@university.edu</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-0 role-select">
                                <option selected>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge user-status-badge status-active-badge align-self-start px-2 py-1 rounded-0">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 3 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Mike Johnson"
                 data-email="mike.johnson@university.edu"
                 data-role="Admin Inventario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Mike Johnson
                                        </a>

                                        <span class="badge user-role-badge bg-success-subtle text-success-emphasis px-2 py-1 small">
                                            Admin Inventario
                                        </span>
                                    </div>

                                    <div class="text-muted small">mike.johnson@university.edu</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-0 role-select">
                                <option>Usuario</option>
                                <option>Admin Super</option>
                                <option selected>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge user-status-badge status-active-badge align-self-start px-2 py-1 rounded-0">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 4 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Laura Gómez"
                 data-email="laura.gomez@university.edu"
                 data-role="Admin Mercado"
                 data-status="Baneado">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-fill"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Laura Gómez
                                        </a>

                                        <span class="badge user-role-badge bg-warning-subtle text-warning-emphasis px-2 py-1 small">
                                            Admin Mercado
                                        </span>
                                    </div>

                                    <div class="text-muted small">laura.gomez@university.edu</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-0 role-select">
                                <option>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option selected>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge bg-danger user-status-badge align-self-start px-2 py-1 rounded-0">Baneado</span>

                                <button type="button" class="btn btn-outline-success rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-arrow-counterclockwise rounded-3 me-1"></i>
                                    Desbanear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            <ul class="pagination users-pagination mb-0" id="usersPagination"></ul>
        </div>
    </div>

    {{-- Empty state --}}
    <div id="usersEmptyState" class="card border-0 shadow-sm rounded-0 d-none container mb-4">
        <div class="card-body py-5 text-center">
            <i class="bi bi-people fs-1 text-muted"></i>
            <h4 class="fw-bold mt-3">No se encontraron usuarios</h4>
            <p class="text-muted mb-0">Intenta con otro nombre, correo o filtro de rol.</p>
        </div>
    </div>

    {{-- Change role modal --}}
    <div class="modal fade" id="confirmRoleModal" tabindex="-1" aria-labelledby="confirmRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="confirmRoleModalLabel">
                                Confirmar cambio de rol
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0" id="confirmRoleText">
                            ¿Deseas cambiar el rol de este usuario?
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmRoleBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Ban / unban modal --}}
    <div class="modal fade" id="confirmBanModal" tabindex="-1" aria-labelledby="confirmBanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="confirmBanModalLabel">
                                Confirmar acción
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0" id="confirmBanText">
                            ¿Estás seguro de realizar esta acción?
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmBanBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="roleToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Rol actualizado correctamente.
                </div>
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="banToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido baneada.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="unbanToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido desbaneada.
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

</x-layout>
