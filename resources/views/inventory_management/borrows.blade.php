<x-layout title="Gestión de Inventario - Préstamos">
    <x-navbar></x-navbar>

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
                Inventario Administrativo
            </a>

            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-success px-4 fw-semibold">
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
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label for="borrowSearch" class="form-label fw-semibold">Buscar solicitud</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-2 border-dark border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                id="borrowSearch"
                                class="form-control form-control-md border-2 border-dark border-start-0"
                                placeholder="Buscar por equipo, usuario, ubicación o fecha (dd/mm/yyyy)"
                            >
                        </div>
                    </div>

                    <div class="col-md-5">
                        <label for="borrowDateFilter" class="form-label fw-semibold">Ver pedidos por día</label>
                        <input
                            type="date"
                            id="borrowDateFilter"
                            class="form-control form-control-md border-2 border-dark"
                        >
                    </div>
                </div>
            </div>
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
                            @forelse($pending as $lending)
                                <div class="borrow-request pending-request card border rounded-4 shadow-sm">
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

                                                    <span class="badge text-bg-light border text-dark rounded-0">
                                                        Pendiente de aprobación
                                                    </span>
                                                </div>

                                                <div class="row g-3 small mb-3">
                                                    <div class="col-md-4">
                                                        <div><span class="text-muted">Usuario:</span> <strong>{{ $lending->user->first_name ?? 'N/A' }} {{ $lending->user->last_name ?? '' }}</strong></div>
                                                        <div><span class="text-muted">Cantidad:</span> 
                                                            <strong>{{ $lending->items->sum('quantity') }}</strong>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div><span class="text-muted">Recogida:</span> <strong>{{ $lending->start_time }}</strong></div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div><span class="text-muted">Devolución:</span> <strong>{{ $lending->end_time }}</strong></div>
                                                    </div>
                                                </div>

                                                @if($lending->special_reason)
                                                    <div class="alert alert-warning rounded-4 mb-0 py-2">
                                                        <strong>Razón:</strong> {{ $lending->special_reason }}
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="d-flex flex-column justify-content-center gap-2" style="min-width: 190px;">
                                                <form method="POST" action="{{ route('inventory_management.requests.approve', $lending->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">
                                                        Aprobar
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('inventory_management.requests.reject', $lending->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger">
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
                            @forelse($approved as $lending)
                                <div class="borrow-request active-request card border rounded-4 shadow-sm">
                                    <div class="card-body p-4">

                                        <h5 class="fw-bold mb-2">
                                            {{ $lending->items->first()->equipment->description ?? 'Equipo' }}
                                        </h5>

                                        <p><strong>Cantidad:</strong> {{ $lending->items->sum('quantity') }}</p>
                                        <strong>
                                            {{ $lending->user 
                                                ? $lending->user->first_name . ' ' . $lending->user->last_name 
                                                : 'Usuario desconocido' }}
                                        </strong>

                                        <form method="POST" action="{{ route('inventory_management.requests.return', $lending->id) }}">
                                            @csrf
                                            <button class="btn btn-outline-success">
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

    {{-- Return confirmation modal --}}
    <div class="modal fade" id="returnConfirmModal" tabindex="-1" aria-labelledby="returnConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="returnConfirmModalLabel">Confirmar devolución</h4>
                        <p class="text-muted mb-0" id="returnConfirmText">
                            ¿Confirmas que se devolvió el equipo?
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Caso especial aprobado correctamente.
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
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Caso especial denegado.
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
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Equipo ha sido devuelto.
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const borrowDateFilter = document.getElementById('borrowDateFilter');
            const borrowSearch = document.getElementById('borrowSearch');

            const pendingRequestsCount = document.getElementById('pendingRequestsCount');
            const activeRequestsCount = document.getElementById('activeRequestsCount');
            const selectedDateLabel = document.getElementById('selectedDateLabel');

            const pendingEmptyState = document.getElementById('pendingEmptyState');
            const activeEmptyState = document.getElementById('activeEmptyState');

            const pendingRequestsList = document.getElementById('pendingRequestsList');
            const activeRequestsList = document.getElementById('activeRequestsList');

            const approveToastEl = document.getElementById('approveToast');
            const denyToastEl = document.getElementById('denyToast');
            const returnedToastEl = document.getElementById('returnedToast');

            const returnConfirmModal = document.getElementById('returnConfirmModal');
            const returnConfirmText = document.getElementById('returnConfirmText');
            const confirmReturnBtn = document.getElementById('confirmReturnBtn');

            let requestToRemove = null;

            function formatDateToDMY(dateString) {
                if (!dateString) return '';
                const [year, month, day] = dateString.split('-');
                return `${day}/${month}/${year}`;
            }

            function getAllRequests() {
                return document.querySelectorAll('.borrow-request');
            }

            function updateCounters() {
                const pendingVisible = [...document.querySelectorAll('.pending-request')]
                    .filter(card => !card.classList.contains('d-none'));

                const activeVisible = [...document.querySelectorAll('.active-request')]
                    .filter(card => !card.classList.contains('d-none'));

                if (pendingRequestsCount) pendingRequestsCount.textContent = pendingVisible.length;
                if (activeRequestsCount) activeRequestsCount.textContent = activeVisible.length;

                pendingEmptyState.classList.toggle('d-none', pendingVisible.length !== 0);
                activeEmptyState.classList.toggle('d-none', activeVisible.length !== 0);

                if (selectedDateLabel) {
                    if (borrowDateFilter.value) {
                        selectedDateLabel.textContent = formatDateToDMY(borrowDateFilter.value);
                    } else {
                        selectedDateLabel.textContent = 'Todos los días';
                    }
                }
            }

            function filterRequests() {
                const selectedDate = borrowDateFilter.value;
                const searchValue = borrowSearch.value.trim().toLowerCase();

                getAllRequests().forEach(card => {
                    const cardDate = card.dataset.date;
                    const cardSearch = card.dataset.search.toLowerCase();

                    const matchesDate = !selectedDate || cardDate === selectedDate;
                    const matchesSearch = !searchValue || cardSearch.includes(searchValue);

                    card.classList.toggle('d-none', !(matchesDate && matchesSearch));
                });

                updateCounters();
            }

            function attachApproveDenyEvents() {
                document.querySelectorAll('.approve-special-btn').forEach(button => {
                    if (button.dataset.bound === 'true') return;

                    button.dataset.bound = 'true';

                    button.addEventListener('click', function () {
                        const card = button.closest('.borrow-request');
                        const statusLabel = card.querySelector('.special-status-label');

                        if (statusLabel) {
                            statusLabel.textContent = 'Aprobado';
                            statusLabel.className = 'badge text-bg-light border text-success rounded-0 special-status-label';
                        }

                        const actions = button.closest('.d-flex.flex-column');
                        if (actions) {
                            actions.innerHTML = `
                                <button type="button" class="btn btn-outline-success mark-returned-btn">
                                    <i class="bi bi-box-arrow-in-left me-1"></i>
                                    Marcar como devuelto
                                </button>
                            `;
                        }

                        card.classList.remove('pending-request');
                        card.classList.add('active-request');
                        card.dataset.status = 'active';

                        activeRequestsList.appendChild(card);

                        const toast = window.bootstrap.Toast.getOrCreateInstance(approveToastEl);
                        toast.show();

                        attachReturnEvents();
                        filterRequests();
                    });
                });

                document.querySelectorAll('.deny-special-btn').forEach(button => {
                    if (button.dataset.bound === 'true') return;

                    button.dataset.bound = 'true';

                    button.addEventListener('click', function () {
                        const card = button.closest('.borrow-request');
                        card.remove();

                        const toast = window.bootstrap.Toast.getOrCreateInstance(denyToastEl);
                        toast.show();

                        filterRequests();
                    });
                });
            }

            function attachReturnEvents() {
                document.querySelectorAll('.mark-returned-btn').forEach(button => {
                    if (button.dataset.bound === 'true') return;

                    button.dataset.bound = 'true';

                    button.addEventListener('click', function () {
                        const card = button.closest('.borrow-request');
                        const itemName = card.querySelector('h5')?.textContent?.trim() || 'el equipo';

                        requestToRemove = card;
                        returnConfirmText.textContent = `¿Confirmas que se devolvió el equipo "${itemName}"?`;

                        const modalInstance = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModal);
                        modalInstance.show();
                    });
                });
            }

            if (confirmReturnBtn) {
                confirmReturnBtn.addEventListener('click', function () {
                    if (requestToRemove) {
                        requestToRemove.remove();
                        requestToRemove = null;
                    }

                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModal);
                    modalInstance.hide();

                    const toast = window.bootstrap.Toast.getOrCreateInstance(returnedToastEl);
                    toast.show();

                    filterRequests();
                });
            }

            borrowDateFilter.addEventListener('change', filterRequests);
            borrowSearch.addEventListener('input', filterRequests);

            attachApproveDenyEvents();
            attachReturnEvents();
            updateCounters();
        });
    </script>
</x-layout>
