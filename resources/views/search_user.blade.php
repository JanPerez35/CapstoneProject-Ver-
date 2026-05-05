<x-layout title="Buscar Usuarios">
    <x-navbar></x-navbar>

    {{-- Loads the JS responsible for filtering users, pagination, role changes, and ban/unban actions --}}
    @vite('resources/js/search_user_validation.js')

    <style>
        /* Custom badge color for active users */
        .status-active-badge {
            background-color: #7FC61B !important;
            color: white !important;
        }

        /* Pagination styling consistent with system theme, success green */
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

        {{-- Page Header for Search Users tab --}}
        <div class="mb-4">
            <h1 class="fw-bold rounded-2">Buscar Usuarios</h1>
            <p class="text mb-0">
                Aquí puedes buscar usuarios, administrar roles y bloquear/desbloquear cuentas.
            </p>
        </div>

        {{-- Filters --}}
        <div class="mb-4">

            {{-- Search input for search bar --}}
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

                {{-- Search button (enabled via JS only when input is valid) standard across site --}}
                <div class="col-lg-2 d-grid">
                    <button type="button" class="btn btn-success h-100 fw-semibold" id="searchUsersBtn" disabled>
                        Buscar
                    </button>
                </div>
            </div>

            {{-- Role filter and clear filter button --}}
            <div class="row g-3 align-items-center">
                <div class="col-md-6 col-lg-4">

                    {{-- Role filter selector utilized by JS --}}
                    <select id="roleFilterSelect" class="form-select border-2 border-dark">
                        <option value="all" selected>Todos los Roles</option>
                        <option value="Usuario">Usuario</option>
                        <option value="Admin Super">Admin Super</option>
                        <option value="Admin Inventario">Admin Inventario</option>
                        <option value="Admin Facilidades">Admin Facilidades</option>
                        <option value="Admin Mercado">Admin Mercado</option>
                    </select>
                </div>

                {{-- Clear Filters button --}}
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-secondary" id="clearUserFilters">
                        Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        {{-- Empty state  when there are no users matching filters --}}
        <div id="usersEmptyState" class="card border-0 shadow-sm rounded-0 d-none">
            <div class="card-body py-5 text-center">
                <i class="bi bi-people fs-1 text-muted"></i>
                <h4 class="fw-bold mt-3">No se encontraron usuarios</h4>
                <p class="text-muted mb-0">Intenta con otro nombre, correo o filtro de rol.</p>
            </div>
        </div>

        {{-- Users list --}}
        <div id="usersList" class="d-grid gap-3">
            @foreach ($users as $user)

                @php
                    $isSelf = auth()->id() === $user->id;
                @endphp
                {{-- Backend preprocessing for status of each user. Basically verifies in the tables the state of a user --}}
                @php
                    $isBlocked = in_array($user->status, ['Bloqueado', 'Blocked']);
                    $isActive = in_array($user->status, ['Activo', 'Active']) || !$isBlocked;
                    $statusLabel = $isBlocked ? 'Bloqueado' : 'Activo';
                @endphp

                {{-- Individual user card --}}
                <div class="card border-dark border-2 rounded-4 user-card"

                     {{-- Data attributes used by JS for filtering and actions --}}
                     data-user-id="{{ $user->id }}"
                     data-name="{{ $user->name }}"
                     data-email="{{ $user->email }}"
                     data-role="{{ $user->role_label ?? 'Usuario' }}"
                     data-status="{{ $statusLabel }}">

                    <div class="card-body p-3">
                        <div class="row g-2 align-items-center">

                            {{-- User info --}}
                            <div class="col-lg-7">
                                <div class="d-flex align-items-start gap-2">

                                    {{-- User avatar placeholder --}}
                                    <div class="bg-light rounded-4 d-flex align-items-center justify-content-center"
                                         style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>

                                    {{-- Name and role display for user --}}
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                            <span class="fw-semibold fs-5 user-name-link">
                                                {{ $user->name }}
                                            </span>

                                            <span class="label-badge {{ $user->role_badge_class }}">
                                                {{ $user->role_label }}
                                            </span>
                                        </div>

                                        {{-- Email display --}}
                                        <div class="text-muted small">
                                            {{ $user->email }}
                                        </div>
                                        @if($isSelf)
                                            <small class="text-large-muted ">(Tu cuenta)</small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Role management --}}
                            @if(!$isSelf)
                                <div class="col-lg-3">
                                    <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>

                                    <select class="form-select rounded-0 role-select">
                                        <option {{ ($user->role_label ?? '') == 'Usuario' ? 'selected' : '' }}>Usuario</option>
                                        <option {{ ($user->role_label ?? '') == 'Admin Super' ? 'selected' : '' }}>Admin Super</option>
                                        <option {{ ($user->role_label ?? '') == 'Admin Inventario' ? 'selected' : '' }}>Admin Inventario</option>
                                        <option {{ ($user->role_label ?? '') == 'Admin Facilidades' ? 'selected' : '' }}>Admin Facilidades</option>
                                        <option {{ ($user->role_label ?? '') == 'Admin Mercado' ? 'selected' : '' }}>Admin Mercado</option>
                                    </select>
                                </div>
                            @endif


                            {{-- Ban status and action buttons --}}
                            @if(!$isSelf)
                                <div class="col-lg-2">
                                    <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                                    <div class="d-flex flex-column gap-2">
            <span class="label-badge {{ $isBlocked ? 'badge-blocked' : 'badge-active' }} align-self-start">
                {{ $statusLabel }}
            </span>

                                        <button
                                            type="button"
                                            class="{{ $isBlocked ? 'btn btn-success rounded-3 ban-toggle-btn btn-sm' : 'btn btn-danger rounded-3 ban-toggle-btn btn-sm' }}">
                                            @if ($isBlocked)
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                                Desbloquear
                                            @else
                                                <i class="bi bi-ban me-1"></i>
                                                Bloquear
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            <ul class="pagination users-pagination mb-0" id="usersPagination"></ul>
        </div>
    </div>

    {{-- Empty state when there are no available users --}}
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

    {{-- Role updated toast --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="roleToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            {{--Message--}}
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

        {{-- User banned toast --}}
        <div id="banToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">

            {{-- Message --}}
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido bloqueada.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        {{-- User unbanned toast --}}
        <div id="unbanToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">

            {{-- Message --}}
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido desbloqueada.
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

