<x-layout title="Gestión de Inventario">
    <x-navbar></x-navbar>

    @vite('resources/js/inv_management_validate.js')

    <div class="container py-4">

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí puedes administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal nav --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('inventory_management') }}"
               class="btn btn-success px-4 fw-semibold">
                <i class="bi bi-box"></i>
                Inventario Administrativo
            </a>

            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-card-checklist"></i>                Préstamos
            </a>

            <a href="{{ route('inventory_management.inventory_statistics') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
            </a>
        </div>

        {{-- Section heading + button --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Inventario Administrativo</h2>
                <p class="text-muted mb-0">
                    Agregar, eliminar o actualizar cantidades de equipo.
                </p>
            </div>

            <button
                type="button"
                class="btn btn-success d-flex align-items-center gap-2"
                data-bs-toggle="modal"
                data-bs-target="#addItemModal"
            >
                <i class="bi bi-plus-lg"></i>
                Agregar Item
            </button>
        </div>

        {{-- Buscar + filtrar --}}
        <form method="GET" action="{{ route('inventory_management') }}" class="mb-4">
            <div class="row g-3 align-items-stretch mb-3">
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                        <span class="input-group-text bg-white border-0">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            name="search"
                            id="inventorySearchInput"
                            class="form-control border-0"
                            placeholder="Buscar equipo deportivo..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="submit" id="inventorySearchBtn" class="btn btn-success h-100 fw-semibold" disabled>                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <select
                        name="category"
                        class="form-select border-2 border-dark"
                        onchange="this.form.submit()"
                    >
                        <option value="">Todas las Categorías</option>

                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto">
                    <a href="{{ route('inventory_management') }}" class="btn btn-outline-secondary">
                        Limpiar Filtros
                    </a>
                </div>
            </div>
        </form>

        {{-- Cards --}}
        <div class="row g-4" id="inventoryCards">
            @forelse($items as $item)
                <div class="col-md-6 col-lg-4 inventory-card-wrapper">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                        <img
                            src="{{ $item->equipment_photo_url ? asset('storage/' . $item->equipment_photo_url) : asset('images/kinventory_images/default.jpg') }}"
                            class="card-img-top"
                            alt="{{ $item->description ?? $item->category }}"
                            style="height: 220px; object-fit: contain; object-position: center;"
                        >

                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold inventory-item-name">
                                    {{ $item->description }}
                                </h5>

                                <span class="label-badge inventory-status-badge {{ $item->available_quantity > 0 ? 'badge-available' : 'badge-unavailable' }}">
        {{ $item->available_quantity > 0 ? 'Disponible' : 'No disponible' }}
    </span>
                            </div>

                            <div class="small mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cantidad Total:</span>
                                    <strong class="inventory-total">{{ $item->quantity }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cantidad Disponible:</span>
                                    <strong class="text-success inventory-available">{{ $item->available_quantity }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Ubicación:</span>
                                    <strong class="inventory-location">{{ $item->location }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Categoría:</span>
                                    <strong class="inventory-category">{{ $item->category }}</strong>
                                </div>
                            </div>

                            <div class="mt-auto d-grid gap-3">
                                <button
                                    type="button"
                                    class="btn btn-warning w-100 fw-bold"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editItemModal{{ $item->id }}"
                                >
                                    Editar
                                </button>

                                <form
                                    action="{{ route('equipment.destroy', $item->id) }}"
                                    method="POST"
                                    class="d-grid deleteItemForm"
                                    data-has-active-orders="{{ $item->open_lendings_count > 0 ? '1' : '0' }}"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        Eliminar Item
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit Modal --}}
                <div class="modal fade" id="editItemModal{{ $item->id }}" tabindex="-1" aria-labelledby="editItemModalLabel{{ $item->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content rounded-4 border-0 shadow">
                            <div class="modal-header border-0 pb-0">
                                <div>
                                    <h4 class="modal-title fw-bold" id="editItemModalLabel{{ $item->id }}">Editar Item</h4>
                                    <p class="text-muted mb-0">Actualiza la información del equipo</p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <div class="modal-body">
                                <form class="editItemForm" method="POST" action="{{ route('equipment.update', $item->id) }}" enctype="multipart/form-data" novalidate>
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Nombre / Descripción<span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            name="description"
                                            class="form-control form-control-lg"
                                            value="{{ $item->description }}"
                                            placeholder="Ejemplo: Bola de Volibol"
                                            required
                                        >
                                        <div class="form-text">
                                            Entre 5 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                                        </div>
                                        <div class="invalid-feedback d-block error-description"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Categoría<span class="text-danger">*</span>
                                        </label>

                                        <select class="form-select form-select-lg mb-2 edit-category-existing">
                                            <option value="">Selecciona una categoría existente</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}" {{ $item->category === $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input
                                            type="text"
                                            class="form-control form-control-lg edit-category-new"
                                            placeholder="O escribe una categoría nueva"
                                        >

                                        <input
                                            type="hidden"
                                            name="category"
                                            class="edit-category-final"
                                            value="{{ $item->category }}"
                                        >

                                        <div class="form-text">
                                            Entre 3 y 100 caracteres. Puedes seleccionar una categoría existente o escribir una nueva.
                                        </div>
                                        <div class="invalid-feedback d-block error-category"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Ubicación<span class="text-danger">*</span>
                                        </label>

                                        <select class="form-select form-select-lg mb-2 edit-location-existing">
                                            <option value="">Selecciona una ubicación existente</option>
                                            @foreach($locations as $location)
                                                <option value="{{ $location }}" {{ $item->location === $location ? 'selected' : '' }}>
                                                    {{ $location }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <input
                                            type="text"
                                            class="form-control form-control-lg edit-location-new"
                                            placeholder="O escribe una ubicación nueva"
                                        >

                                        <input
                                            type="hidden"
                                            name="location"
                                            class="edit-location-final"
                                            value="{{ $item->location }}"
                                        >

                                        <div class="form-text">
                                            Entre 3 y 100 caracteres. Puedes seleccionar una ubicación existente o escribir una nueva.
                                        </div>
                                        <div class="invalid-feedback d-block error-location"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Cantidad Total<span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            name="quantity"
                                            class="form-control form-control-lg"
                                            min="1"
                                            value="{{ $item->quantity }}"
                                            placeholder="Ej. 10"
                                            required
                                        >
                                        <div class="form-text">
                                            Debe ser un número entero mayor o igual a 1.
                                        </div>
                                        <div class="invalid-feedback d-block error-quantity"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            Cantidad Disponible<span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            name="available_quantity"
                                            class="form-control form-control-lg"
                                            min="0"
                                            value="{{ $item->available_quantity }}"
                                            placeholder="Ej. 8"
                                            required
                                        >
                                        <div class="form-text">
                                            Debe ser un número entero igual o mayor a 0 y no puede exceder la cantidad total.
                                        </div>
                                        <div class="invalid-feedback d-block error-available"></div>
                                    </div>

                                    <div class="mb-2">
                                        <label for="edit_image_{{ $item->id }}" class="form-label fw-semibold">
                                            Nueva Imagen (opcional)
                                        </label>
                                        <input
                                            type="file"
                                            class="d-none"
                                            id="edit_image_{{ $item->id }}"
                                            name="image"
                                            accept=".jpg,.jpeg,image/jpeg"
                                        >
                                    </div>

                                    <label for="edit_image_{{ $item->id }}" class="form-control form-control-lg text-center py-3" style="cursor:pointer;">
                                        <i class="bi bi-upload me-2"></i>
                                        Subir nueva imagen
                                    </label>

                                    <small class="text-muted d-block fst-italic mt-2">
                                        Solo 1 imagen permitida. Formato JPEG/JPG. Máximo 2MB.
                                    </small>
                                    <div class="invalid-feedback d-block error-image"></div>

                                    <div class="modal-footer border-0 px-0 pb-0">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-12">
                    <div id="itemsEmptyState" class="card border-0 shadow-sm rounded-4">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-boxes fs-1 text-muted"></i>

                            <h4 class="fw-bold mt-3">No se encontraron items</h4>
                            <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro equipo.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>

        <div id="inventoryEmptyState" class="text-center text-muted py-5 d-none">
            No se encontraron items con ese filtro.
        </div>
    </div>

    {{-- Modal Agregar Item --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="w-100">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="modal-title fw-bold mb-0" id="addItemModalLabel">
                                Agregar Nuevo Item
                            </h4>

                            <button type="button" class="btn-close ms-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>

                        <p class="text-muted mt-2 mb-0">
                            Completa la información del nuevo equipo
                        </p>

                        <p class="mt-2 mb-0 text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </p>
                    </div>
                </div>

                <div class="modal-body">
                    <form id="addItemForm"
                          method="POST"
                          action="{{ route('inventory.store') }}"
                          enctype="multipart/form-data"
                          novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="nombre_item" class="form-label fw-semibold">
                                Nombre del Item<span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="nombre_item"
                                name="description"
                                class="form-control form-control-lg"
                                placeholder="Ejemplo: Bola de Volibol"
                                required
                            >
                            <div class="form-text">
                                Entre 3 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </div>
                            <div class="invalid-feedback d-block" id="nombreItemError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Categoría<span class="text-danger">*</span>
                            </label>

                            <select id="categoria_existente" class="form-select form-select-lg mb-2">
                                <option value="">Selecciona una categoría existente</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>

                            <input
                                type="text"
                                id="categoria_nueva"
                                class="form-control form-control-lg"
                                placeholder="O escribe una categoría nueva"
                            >

                            <input type="hidden" id="categoria" name="category">

                            <div class="form-text">
                                Entre 3 y 100 caracteres. Puedes seleccionar una categoría existente o escribir una nueva.
                            </div>
                            <div class="invalid-feedback d-block" id="categoriaError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Ubicación<span class="text-danger">*</span>
                            </label>

                            <select id="ubicacion_existente" class="form-select form-select-lg mb-2">
                                <option value="">Selecciona una ubicación existente</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location }}">{{ $location }}</option>
                                    document.addEventListener('DOMContentLoaded', function () {
                                    const borrowDateFilter = document.getElementById('borrowDateFilter');
                                    const borrowSearch = document.getElementById('borrowSearch');

                                    const pendingEmptyState = document.getElementById('pendingEmptyState');
                                    const activeEmptyState = document.getElementById('activeEmptyState');

                                    const pendingRequestsList = document.getElementById('pendingRequestsList');
                                    const activeRequestsList = document.getElementById('activeRequestsList');

                                    const approveToastEl = document.getElementById('approveToast');
                                    const denyToastEl = document.getElementById('denyToast');
                                    const returnedToastEl = document.getElementById('returnedToast');

                                    const approveConfirmModalEl = document.getElementById('approveConfirmModal');
                                    const approveConfirmText = document.getElementById('approveConfirmText');
                                    const confirmApproveBtn = document.getElementById('confirmApproveBtn');

                                    const denyConfirmModalEl = document.getElementById('denyConfirmModal');
                                    const denyConfirmText = document.getElementById('denyConfirmText');
                                    const confirmDenyBtn = document.getElementById('confirmDenyBtn');

                                    const returnConfirmModalEl = document.getElementById('returnConfirmModal');
                                    const returnConfirmText = document.getElementById('returnConfirmText');
                                    const confirmReturnBtn = document.getElementById('confirmReturnBtn');

                                    let approveFormToSubmit = null;
                                    let denyFormToSubmit = null;
                                    let returnFormToSubmit = null;

                                    function getAllRequests() {
                                    return document.querySelectorAll('.borrow-request');
                                    }

                                    function updateEmptyStates() {
                                    const pendingVisible = [...document.querySelectorAll('.pending-request')]
                                    .filter(card => !card.classList.contains('d-none'));

                                    const activeVisible = [...document.querySelectorAll('.active-request')]
                                    .filter(card => !card.classList.contains('d-none'));

                                    if (pendingEmptyState) {
                                    pendingEmptyState.classList.toggle('d-none', pendingVisible.length !== 0);
                                    }

                                    if (activeEmptyState) {
                                    activeEmptyState.classList.toggle('d-none', activeVisible.length !== 0);
                                    }
                                    }

                                    function filterRequests() {
                                    const selectedDate = borrowDateFilter ? borrowDateFilter.value : '';
                                    const searchValue = borrowSearch ? borrowSearch.value.trim().toLowerCase() : '';

                                    getAllRequests().forEach(card => {
                                    const cardDate = card.dataset.date || '';
                                    const cardSearch = (card.dataset.search || '').toLowerCase();

                                    const matchesDate = !selectedDate || cardDate === selectedDate;
                                    const matchesSearch = !searchValue || cardSearch.includes(searchValue);

                                    card.classList.toggle('d-none', !(matchesDate && matchesSearch));
                                    });

                                    updateEmptyStates();
                                    }

                                    function showToast(toastElement) {
                                    if (!toastElement || !window.bootstrap) return;
                                    const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
                                    toast.show();
                                    }

                                    function attachApproveEvents() {
                                    document.querySelectorAll('.approve-special-btn').forEach(button => {
                                    if (button.dataset.bound === 'true') return;
                                    button.dataset.bound = 'true';

                                    button.addEventListener('click', function () {
                                    const form = button.closest('form');
                                    const card = button.closest('.borrow-request');
                                    const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                                    approveFormToSubmit = form;
                                    approveConfirmText.textContent = `¿Seguro que quieres aprobar "${itemName}"?`;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
                                    modal.show();
                                    });
                                    });
                                    }

                                    function attachDenyEvents() {
                                    document.querySelectorAll('.deny-special-btn').forEach(button => {
                                    if (button.dataset.bound === 'true') return;
                                    button.dataset.bound = 'true';

                                    button.addEventListener('click', function () {
                                    const form = button.closest('form');
                                    const card = button.closest('.borrow-request');
                                    const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                                    denyFormToSubmit = form;
                                    denyConfirmText.textContent = `¿Seguro que quieres denegar "${itemName}"?`;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
                                    modal.show();
                                    });
                                    });
                                    }

                                    function attachReturnEvents() {
                                    document.querySelectorAll('.mark-returned-btn').forEach(button => {
                                    if (button.dataset.bound === 'true') return;
                                    button.dataset.bound = 'true';

                                    button.addEventListener('click', function () {
                                    const form = button.closest('form');
                                    const card = button.closest('.borrow-request');
                                    const itemName = card?.querySelector('h5')?.textContent?.trim() || 'el equipo';

                                    returnFormToSubmit = form;
                                    returnConfirmText.textContent = `¿Estás seguro de que "${itemName}" fue devuelto?`;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
                                    modal.show();
                                    });
                                    });
                                    }

                                    if (confirmApproveBtn) {
                                    confirmApproveBtn.addEventListener('click', function () {
                                    if (!approveFormToSubmit) return;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
                                    modal.hide();

                                    showToast(approveToastEl);

                                    setTimeout(() => {
                                    approveFormToSubmit.submit();
                                    }, 500);
                                    });
                                    }

                                    if (confirmDenyBtn) {
                                    confirmDenyBtn.addEventListener('click', function () {
                                    if (!denyFormToSubmit) return;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
                                    modal.hide();

                                    showToast(denyToastEl);

                                    setTimeout(() => {
                                    denyFormToSubmit.submit();
                                    }, 500);
                                    });
                                    }

                                    if (confirmReturnBtn) {
                                    confirmReturnBtn.addEventListener('click', function () {
                                    if (!returnFormToSubmit) return;

                                    const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
                                    modal.hide();

                                    showToast(returnedToastEl);

                                    setTimeout(() => {
                                    returnFormToSubmit.submit();
                                    }, 500);
                                    });
                                    }

                                    if (borrowSearch) {
                                    borrowSearch.addEventListener('input', filterRequests);
                                    }

                                    if (borrowDateFilter) {
                                    borrowDateFilter.addEventListener('change', filterRequests);
                                    }

                                    attachApproveEvents();
                                    attachDenyEvents();
                                    attachReturnEvents();
                                    updateEmptyStates();
                                    });@endforeach
                            </select>

                            <input
                                type="text"
                                id="ubicacion_nueva"
                                class="form-control form-control-lg"
                                placeholder="O escribe una ubicación nueva"
                            >

                            <input type="hidden" id="ubicacion" name="location">

                            <div class="form-text">
                                Entre 3 y 100 caracteres. Puedes seleccionar una ubicación existente o escribir una nueva.
                            </div>
                            <div class="invalid-feedback d-block" id="ubicacionError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_total" class="form-label fw-semibold">
                                Cantidad Total<span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                id="cantidad_total"
                                name="quantity"
                                class="form-control form-control-lg"
                                min="1"
                                placeholder="Ej. 10"
                                required
                            >
                            <div class="form-text">
                                Debe ser un número entero mayor o igual a 1.
                            </div>
                            <div class="invalid-feedback d-block" id="cantidadTotalError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_disponible" class="form-label fw-semibold">
                                Cantidad Disponible<span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                id="cantidad_disponible"
                                name="available_quantity"
                                class="form-control form-control-lg"
                                min="0"
                                placeholder="Ej. 8"
                                required
                            >
                            <div class="form-text">
                                Debe ser un número entero igual o mayor a 0 y no puede exceder la cantidad total.
                            </div>
                            <div class="invalid-feedback d-block" id="cantidadDisponibleError"></div>
                        </div>

                        <div class="mb-2">
                            <label for="imagen" class="form-label fw-semibold">
                                Imagen del Item<span class="text-danger">*</span>
                            </label>
                            <input
                                type="file"
                                class="d-none"
                                id="imagen"
                                name="image"
                                accept=".jpg,.jpeg,image/jpeg"
                                required
                            >
                        </div>

                        <label for="imagen" class="form-control form-control-lg text-center py-3" style="cursor:pointer;">
                            <i class="bi bi-upload me-2"></i>
                            Subir imagen
                        </label>

                        <small class="text-muted d-block fst-italic mt-2">
                            Solo 1 imagen permitida. Formato JPEG/JPG. Máximo 2MB.
                        </small>

                        <div class="invalid-feedback d-block" id="imageError"></div>

                        <div class="mb-3 d-none" id="previewWrapper">
                            <label class="form-label fw-semibold">Vista previa</label>
                            <img
                                id="imagePreview"
                                src=""
                                alt="Vista previa"
                                class="img-fluid rounded-4 w-100"
                                style="height: 220px; object-fit: cover; object-position: center;"
                            >
                        </div>

                        <div class="modal-footer border-0 px-0 pb-0">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-success" id="submitAddItemBtn" disabled>
                                <i class="bi bi-plus-lg me-1"></i> Agregar Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmar borrar --}}
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="confirmDeleteModalLabel">Eliminar Item</h4>
                        <p class="text-muted mb-0" id="confirmDeleteText">
                            ¿Seguro que quieres borrar este item?

                        <p class="text-danger fw-semibold mb-0 d-none" id="confirmDeleteWarningText">
                            *Este item tiene un pedido atado.
                        </p>
                        </p>
                    </div>

                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        Sí, Borrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal confirmar editar --}}
    <div class="modal fade" id="confirmEditModal" tabindex="-1" aria-labelledby="confirmEditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="confirmEditModalLabel">Confirmar edición</h4>
                        <p class="text-muted mb-0" id="confirmEditText">
                            ¿Seguro que quieres editar este item?
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="confirmEditBtn">
                        Sí, Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="inventoryAddToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Item añadido correctamente
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="inventoryDeleteToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Item borrado correctamente
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="inventoryEditToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Item editado correctamente
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
