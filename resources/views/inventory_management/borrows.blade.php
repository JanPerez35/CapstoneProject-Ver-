<x-layout title="Gestión de Inventario - Préstamos">
    <x-navbar></x-navbar>

    @vite(['resources/js/borrows_validation.js'])

    <div class="container py-4">
        {{-- Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí podrás administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal nav --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('inventory_management') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-box"></i>
                Inventario Administrativo
            </a>

            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-success px-4 fw-semibold">
                <i class="bi bi-card-checklist"></i>
                Préstamos
            </a>

            <a href="{{ route('inventory_management.inventory_statistics') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
            </a>
        </div>

        {{-- Title --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Préstamos</h2>
                <p class="text-muted mb-0">
                    Revisa solicitudes, aprueba casos especiales y marca equipos devueltos.
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('inventory_management.borrows') }}" class="mb-4">
                <div class="row g-3 align-items-stretch mb-3">
                    <div class="col-lg-10">
                        <div class="input-group search-group h-100">
                            <span class="input-group-text bg-white border-0">
                                <i class="bi bi-search"></i>
                            </span>

                            <input
                                type="text"
                                id="borrowSearch"
                                name="search"
                                class="form-control border-0"
                                placeholder="Buscar equipo deportivo..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div class="col-lg-2 d-grid">
                        <button type="submit" class="btn btn-success h-100 fw-semibold">
                            Buscar
                        </button>
                    </div>
                </div>

                <div class="d-flex flex-column flex-md-row gap-3 align-items-stretch">
                    <div style="max-width: 540px; width: 100%;">
                        <input
                            type="date"
                            id="borrowDateFilter"
                            name="date"
                            class="form-control py-2"
                            value="{{ request('date') }}"
                        >
                    </div>

                    <div>
                        <a href="{{ route('inventory_management.borrows') }}" class="btn btn-outline-secondary py-2 px-4">
                            Limpiar filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Two columns --}}
        <div class="row g-4">
            {{-- LEFT: Requests to review --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom">
                            <h4 class="fw-bold mb-1">Solicitudes por Revisar</h4>
                            <p class="text-muted mb-0">
                                Aquí aparecen los casos especiales pendientes de aprobación.
                            </p>
                        </div>

                        <div id="pendingEmptyState" class="d-none text-center py-5">
                            <i class="bi bi-clipboard-check fs-1 text-muted"></i>
                            <h5 class="fw-bold mt-3">No hay solicitudes pendientes</h5>
                            <p class="text-muted mb-0">No hay casos especiales por revisar con esos filtros.</p>
                        </div>

                        <div id="pendingRequestsList">
                            @forelse($pending as $lending)
                                <div
                                    class="borrow-request pending-request card border rounded-4 shadow-sm m-3"
                                    data-search="{{ strtolower(($lending->items->first()->equipment->description ?? 'equipo') . ' ' . ($lending->user->first_name ?? '') . ' ' . ($lending->user->last_name ?? '') . ' ' . ($lending->special_reason ?? '')) }}"
                                    data-date="{{ \Carbon\Carbon::parse($lending->start_time)->format('Y-m-d') }}"
                                >
                                    <div class="card-body p-4">
                                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                            <div class="flex-grow-1">
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    <h5 class="fw-bold mb-0">
                                                        {{ $lending->items->first()->equipment->description ?? 'Equipo' }}
                                                    </h5>

                                                    @if($lending->flag)
                                                        <span class="badge text-bg-warning rounded-0">Caso Especial</span>
                                                    @endif

                                                    <span class="badge text-bg-light border text-dark rounded-0 special-status-label">
                                                        Pendiente de aprobación
                                                    </span>
                                                </div>

                                                <div class="row g-3 small mb-3">
                                                    <div class="col-md-4">
                                                        <div>
                                                            <span class="text-muted">Usuario:</span>
                                                            <strong>{{ $lending->user->first_name ?? 'N/A' }} {{ $lending->user->last_name ?? '' }}</strong>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">Cantidad:</span>
                                                            <strong>{{ $lending->items->sum('quantity') }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div>
                                                            <span class="text-muted">Recogida:</span>
                                                            <strong>{{ \Carbon\Carbon::parse($lending->start_time)->format('m/d/Y h:i A') }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div>
                                                            <span class="text-muted">Devolución:</span>
                                                            <strong>{{ \Carbon\Carbon::parse($lending->end_time)->format('m/d/Y h:i A') }}</strong>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if($lending->special_reason)
                                                    <div class="alert alert-warning rounded-4 mb-0 py-2">
                                                        <strong>Razón:</strong> {{ $lending->special_reason }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-flex flex-column justify-content-center gap-2 request-actions" style="min-width: 190px;">
                                                <form method="POST"
                                                      action="{{ route('inventory_management.requests.approve', $lending->id) }}"
                                                      class="approve-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-success approve-special-btn w-100">
                                                        Aprobar
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="{{ route('inventory_management.requests.reject', $lending->id) }}"
                                                      class="deny-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-outline-danger deny-special-btn w-100">
                                                        Denegar
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    No hay solicitudes pendientes.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Active approved requests --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-0">
                        <div class="p-4 border-bottom">
                            <h4 class="fw-bold mb-1">Solicitudes Aprobadas / Activas</h4>
                            <p class="text-muted mb-0">
                                Aquí están los préstamos normales y los casos especiales aprobados.
                            </p>
                        </div>

                        <div id="activeEmptyState" class="d-none text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <h5 class="fw-bold mt-3">No hay solicitudes activas</h5>
                            <p class="text-muted mb-0">No hay solicitudes aprobadas para mostrar con esos filtros.</p>
                        </div>

                        <div id="activeRequestsList">
                            @forelse($approved as $lending)
                                <div
                                    class="borrow-request active-request card border rounded-4 shadow-sm m-3"
                                    data-search="{{ strtolower(($lending->items->first()->equipment->description ?? 'equipo') . ' ' . ($lending->user->first_name ?? '') . ' ' . ($lending->user->last_name ?? '')) }}"
                                    data-date="{{ \Carbon\Carbon::parse($lending->start_time)->format('Y-m-d') }}"
                                >
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold mb-2">
                                            {{ $lending->items->first()->equipment->description ?? 'Equipo' }}
                                        </h5>

                                        <p class="mb-1">
                                            <strong>Cantidad:</strong> {{ $lending->items->sum('quantity') }}
                                        </p>

                                        <p class="mb-3">
                                            <strong>Usuario:</strong>
                                            {{ $lending->user
                                                ? $lending->user->first_name . ' ' . $lending->user->last_name
                                                : 'Usuario desconocido' }}
                                        </p>

                                        <form method="POST"
                                              action="{{ route('inventory_management.requests.return', $lending->id) }}"
                                              class="return-form">
                                            @csrf
                                            <button type="button" class="btn btn-outline-success mark-returned-btn w-100">
                                                Marcar como devuelto
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    No hay solicitudes activas.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Approve confirmation modal --}}
    <div class="modal fade" id="approveConfirmModal" tabindex="-1" aria-labelledby="approveConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="approveConfirmModalLabel">
                                Aprobar caso especial
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0" id="approveConfirmText">
                            ¿Seguro que quieres aprobar este caso especial?
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">
                        Sí, aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Deny confirmation modal --}}
    <div class="modal fade" id="denyConfirmModal" tabindex="-1" aria-labelledby="denyConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="denyConfirmModalLabel">
                                Denegar caso especial
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0" id="denyConfirmText">
                            ¿Seguro que quieres denegar este caso especial?
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDenyBtn">
                        Sí, denegar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Return confirmation modal --}}
    <div class="modal fade" id="returnConfirmModal" tabindex="-1" aria-labelledby="returnConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="returnConfirmModalLabel">
                                Confirmar devolución
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0" id="returnConfirmText">
                            ¿Estás seguro de que el equipo fue devuelto?
                        </p>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmReturnBtn">
                        Sí, confirmar devolución
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="approveToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item aprobado correctamente.
                </div>
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="denyToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item denegado correctamente.
                </div>
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="returnedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Equipo fue devuelto correctamente.
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
