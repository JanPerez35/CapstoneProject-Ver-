<x-layout title="Gestión de Inventario - Préstamos">
    <x-navbar></x-navbar>

    {{-- Load JS for filtering, validation, pagination, and modal confirmations --}}
    @vite(['resources/js/borrows_validation.js'])

    <div class="container py-4">
        {{-- Page Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí puedes administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal navigation between inventory sections --}}
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

        {{-- Section title and description --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Préstamos</h2>
                <p class="text-muted mb-0">
                    Revisa solicitudes, aprueba casos especiales y marca equipos devueltos.
                </p>
            </div>
        </div>

        {{-- Search and filter form --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('inventory_management.borrows') }}" class="mb-4">

                {{-- Search bar --}}
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

                    {{-- Search button (enabled via JS) if there is nothing written in search it does not work --}}
                    <div class="col-lg-2 d-grid">
                        <button type="submit" id="borrowSearchBtn" class="btn btn-success h-100 fw-semibold" disabled>
                            Buscar
                        </button>
                    </div>
                </div>

                {{-- Date filter and clear all filters --}}
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
                            Limpiar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Two columns layout for Pending requests (left) and Active requests (right) --}}
        <div class="row g-4">

            {{-- LEFT COLUMN: Pending special requests --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-0">

                        {{-- Header --}}
                        <div class="p-4 border-bottom">
                            <h4 class="fw-bold mb-1">
                                <i class="bi bi-exclamation-circle me-2 text-warning"></i>
                                Solicitudes por Revisar
                            </h4>
                            <p class="text-muted mb-0">
                                Aquí aparecen los casos especiales pendientes de aprobación.
                            </p>
                        </div>

                        {{-- Empty state for pending, shown when there are no requests available --}}
                        <div id="pendingEmptyState" class="d-none text-center py-5">
                            <i class="bi bi-clipboard-check fs-1 text-muted"></i>
                            <h5 class="fw-bold mt-3">No hay solicitudes pendientes</h5>
                            <p class="text-muted mb-0">No hay casos especiales por revisar con esos filtros.</p>
                        </div>

                        {{-- Pending requests list --}}
                        <div id="pendingRequestsList">
                            @forelse($pending as $lending)
                                {{-- Single pending request card, the structure for all cards in this area --}}
                                <div
                                    class="borrow-request pending-request card border rounded-4 shadow-sm m-3"

                                    {{-- Used for filtering with the JS by date and general search matching --}}
                                    data-search="{{ strtolower(($lending->items->first()->equipment->description ?? 'equipo') . ' ' . ($lending->user->first_name ?? '') . ' ' . ($lending->user->last_name ?? '') . ' ' . ($lending->special_reason ?? '')) }}"
                                    data-date="{{ \Carbon\Carbon::parse($lending->start_time)->format('Y-m-d') }}"
                                >
                                    <div class="card-body p-4">
                                        {{-- Request content --}}
                                        <div class="d-flex flex-column h-100">
                                            <div class="flex-grow-1">

                                                {{-- Title for each card, for standardizing --}}
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                                    <h5 class="fw-bold mb-0">Artículos solicitados</h5>
                                                </div>

                                                {{-- Status badges --}}
                                                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                    @if($lending->flag)
                                                        <span class="label-badge badge-request-special">
                                                            Caso Especial
                                                        </span>
                                                    @endif

                                                    <span class="label-badge badge-request-pending">
                                                        Pendiente de aprobación
                                                    </span>
                                                </div>

                                                {{-- Items list --}}
                                                <div class="mb-3">
                                                    @forelse($lending->items as $item)
                                                        <div class="border rounded-3 px-3 py-2 mb-2 bg-light-subtle">
                                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                                <span class="fw-semibold">
                                                                    {{ $item->equipment->description ?? 'Equipo' }}
                                                                </span>
                                                                <span>
                                                                    <span class="text-muted">Cantidad:</span>
                                                                    <strong>{{ $item->quantity }}</strong>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">No hay artículos en esta solicitud.</div>
                                                    @endforelse
                                                </div>

                                                {{-- Pulls data from request specifically name and dates) --}}
                                                <div class="row g-3 small mb-3">
                                                    <div class="col-md-4">
                                                        <div>
                                                            <span class="text-muted">Usuario:</span>
                                                            <strong>{{ $lending->user->first_name ?? 'N/A' }} {{ $lending->user->last_name ?? '' }}</strong>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">Total de artículos:</span>
                                                            <strong>{{ $lending->items->count() }}</strong>
                                                        </div>
                                                        <div>
                                                            <span class="text-muted">Cantidad total:</span>
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

                                                {{-- Special reason --}}
                                                @if($lending->special_reason)
                                                    <div class="alert alert-warning rounded-4 mb-0 py-2">
                                                        <strong>Razón:</strong> {{ $lending->special_reason }}
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Action buttons per request --}}
                                            <div class="d-flex flex-column gap-2 mt-auto pt-3">

                                                {{-- Approve button --}}
                                                <form method="POST"
                                                      action="{{ route('inventory_management.requests.approve', $lending->id) }}"
                                                      class="approve-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-success approve-special-btn w-100">
                                                        Aprobar
                                                    </button>
                                                </form>

                                                {{-- Deny button --}}
                                                <form method="POST"
                                                      action="{{ route('inventory_management.requests.reject', $lending->id) }}"
                                                      class="deny-form">
                                                    @csrf
                                                    <button type="button" class="btn btn-danger deny-special-btn w-100">
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

                        {{-- Pagination (handled via JS) most likely not shown in testing but available nonetheless --}}
                        <div id="pendingPaginationWrapper" class="px-3 pb-3 d-none">
                            <nav aria-label="Paginación de solicitudes pendientes">
                                <ul id="pendingPagination" class="pagination justify-content-center mb-0"></ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Active approved requests --}}
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-0">

                        {{-- Section header --}}
                        <div class="p-4 border-bottom">
                            <h4 class="fw-bold mb-1">
                                <i class="bi bi-check-circle me-2 text-success"></i>
                                Solicitudes Activas
                            </h4>
                            <p class="text-muted mb-0">
                                Aquí están los préstamos normales y los casos especiales aprobados.
                            </p>
                        </div>

                        {{-- Empty state shown when no active requests match filters --}}
                        <div id="activeEmptyState" class="d-none text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <h5 class="fw-bold mt-3">No hay solicitudes activas</h5>
                            <p class="text-muted mb-0">
                                No hay solicitudes aprobadas para mostrar con esos filtros.
                            </p>
                        </div>

                        {{-- Shows active requests list --}}
                        <div id="activeRequestsList">
                            @forelse($approved as $lending)
                                {{-- Individual active request card, blueprint for all of them. --}}
                                <div
                                    class="borrow-request active-request card border rounded-4 shadow-sm m-3"

                                    {{-- Used by JS for filtering by text and date --}}
                                    data-search="{{ strtolower(($lending->items->first()->equipment->description ?? 'equipo') . ' ' . ($lending->user->first_name ?? '') . ' ' . ($lending->user->last_name ?? '')) }}"
                                    data-date="{{ \Carbon\Carbon::parse($lending->start_time)->format('Y-m-d') }}"
                                >
                                    <div class="card-body p-4">

                                        {{-- Requested items section --}}
                                        <div class="mb-3">
                                            <h5 class="fw-bold mb-2">Artículos solicitados</h5>

                                            {{-- Status badges --}}
                                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">

                                                {{-- Special case badge (if applicable) --}}
                                                @if($lending->flag)
                                                    <span class="label-badge badge-request-special">
                                                        Caso Especial
                                                    </span>
                                                @endif

                                                {{-- Approved status --}}
                                                <span class="label-badge badge-request-approved">
                                                    Aprobado
                                                </span>
                                            </div>

                                            {{-- Shows the item list --}}
                                            @forelse($lending->items as $item)
                                                <div class="border rounded-3 px-3 py-2 mb-2 bg-light-subtle">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                                                        {{-- Equipment name --}}
                                                        <span class="fw-semibold">
                                                            {{ $item->equipment->description ?? 'Equipo' }}
                                                        </span>

                                                        {{-- Quantity --}}
                                                        <span>
                                                            <span class="text-muted">Cantidad:</span>
                                                            <strong>{{ $item->quantity }}</strong>
                                                        </span>
                                                    </div>
                                                </div>
                                            @empty
                                                {{-- Empty state fallback if no items exist --}}
                                                <div class="text-muted">No hay artículos en esta solicitud.</div>
                                            @endforelse
                                        </div>

                                        {{-- Pulls user data for request specifically name and dates --}}
                                        <div class="row g-3 small mb-3">

                                            {{-- User and totals for each item --}}
                                            <div class="col-md-6">
                                                <div>
                                                    <span class="text-muted">Usuario:</span>
                                                    <strong>
                                                        {{ $lending->user
                                                            ? $lending->user->first_name . ' ' . $lending->user->last_name
                                                            : 'Usuario desconocido' }}
                                                    </strong>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Total de artículos:</span>
                                                    <strong>{{ $lending->items->count() }}</strong>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Cantidad total:</span>
                                                    <strong>{{ $lending->items->sum('quantity') }}</strong>
                                                </div>
                                            </div>

                                            {{-- Dates --}}
                                            <div class="col-md-6">
                                                <div>
                                                    <span class="text-muted">Recogida:</span>
                                                    <strong>{{ \Carbon\Carbon::parse($lending->start_time)->format('m/d/Y h:i A') }}</strong>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Devolución:</span>
                                                    <strong>{{ \Carbon\Carbon::parse($lending->end_time)->format('m/d/Y h:i A') }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Return action button --}}
                                        <form method="POST"
                                              action="{{ route('inventory_management.requests.return', $lending->id) }}"
                                              class="return-form">
                                            @csrf
                                            {{-- Button triggers confirmation modal in JS --}}
                                            <button type="button" class="btn btn-success mark-returned-btn w-100">
                                                Marcar como Devuelto
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                {{-- Empty fallback in case there are no active requests --}}
                                <div class="text-center py-4 text-muted">
                                    No hay solicitudes activas.
                                </div>
                            @endforelse
                        </div>

                        {{-- Pagination (handled dynamically via JS) not shown due to amount of items normally --}}
                        <div id="activePaginationWrapper" class="px-3 pb-3 d-none">
                            <nav aria-label="Paginación de solicitudes activas">
                                <ul id="activePagination" class="pagination justify-content-center mb-0"></ul>
                            </nav>
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

                {{-- Modal header --}}
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">

                        {{-- Title and close button --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="approveConfirmModalLabel">
                                Aprobar caso especial
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        {{-- Dynamic confirmation text (can be modified via JS if needed) --}}
                        <p class="text-muted mt-2 mb-0" id="approveConfirmText">
                            ¿Seguro que quieres aprobar este caso especial?
                        </p>
                    </div>
                </div>

                {{-- Modal action buttons --}}
                <div class="modal-footer border-0 pt-2">

                    {{-- Cancel closes modal --}}
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    {{-- JS listens to this button to submit the correct approve form to backend --}}
                    <button type="button" class="btn btn-success" id="confirmApproveBtn">
                        Sí, Aprobar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Deny confirmation modal --}}
    <div class="modal fade" id="denyConfirmModal" tabindex="-1" aria-labelledby="denyConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                {{-- Modal header --}}
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">

                        {{-- Title  and close button --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="denyConfirmModalLabel">
                                Denegar caso especial
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        {{-- Confirmation message --}}
                        <p class="text-muted mt-2 mb-0" id="denyConfirmText">
                            ¿Seguro que quieres denegar este caso especial?
                        </p>
                    </div>
                </div>

                {{-- Action buttons for modal --}}
                <div class="modal-footer border-0 pt-2">

                    {{-- Cancel --}}
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    {{-- JS triggers denial form submission --}}
                    <button type="button" class="btn btn-danger" id="confirmDenyBtn">
                        Sí, Denegar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Return confirmation modal --}}
    <div class="modal fade" id="returnConfirmModal" tabindex="-1" aria-labelledby="returnConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                {{-- Modal header --}}
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">

                        {{-- Title and close button --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="returnConfirmModalLabel">
                                Confirmar Devolución
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        {{-- Confirmation message to make sure user truly wants to label the item as returned --}}
                        <p class="text-muted mt-2 mb-0" id="returnConfirmText">
                            ¿Estás seguro de que el equipo fue devuelto?
                        </p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="modal-footer border-0 pt-2">

                    {{-- Cancel button --}}
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    {{-- JS triggers return form submission --}}
                    <button type="button" class="btn btn-success" id="confirmReturnBtn">
                        Sí, Confirmar Devolución
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Here are toast notifications which act as visual feedback after an action is taken--}}

    {{-- Approve success toast --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="approveToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">

                {{-- Message in the toast pop up --}}
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item aprobado correctamente.
                </div>

                {{-- Close button --}}
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        {{-- Deny success toast --}}
        <div id="denyToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">

                {{-- Message --}}
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item denegado correctamente.
                </div>
                {{-- Close button --}}
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        {{-- Return success toast --}}
        <div id="returnedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                {{-- Message --}}
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Equipo fue devuelto correctamente.
                </div>

                {{-- Cancel Button --}}
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
