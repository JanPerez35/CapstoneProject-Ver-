<x-layout title="Gestión de Inventario">
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
               class="btn btn-success px-4 fw-semibold">
                Inventario Administrativo
            </a>

            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                Préstamos
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
        <div class="row mb-4 g-3">
            <div class="col-md-8">
                <div class="input-group search-group">
                    <span class="input-group-text bg-white border-0">
                        <i class="bi bi-search"></i>
                    </span>

                    <input
                        type="text"
                        id="inventorySearch"
                        class="form-control border-0"
                        placeholder="Buscar equipo deportivo..."
                    >
                </div>
            </div>

            <div class="col-md-4">
                <select
                    id="inventoryCategoryFilter"
                    class="form-select border-2 border-dark"
                >
                    <option value="">Todos los Deportes</option>
                    <option value="Baloncesto">Baloncesto</option>
                    <option value="Tenis">Tenis</option>
                    <option value="Fútbol">Fútbol</option>
                    <option value="Deporte Recreativo">Deporte Recreativo</option>
                    <option value="Volibol">Volibol</option>
                    <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
        </div>

        {{-- Cards --}}
        <div class="row g-4" id="inventoryCards">

            <div class="col-md-6 col-lg-4 inventory-card-wrapper">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                    <img
                        src="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                        class="card-img-top"
                        alt="Balón de Baloncesto"
                        style="height: 220px; object-fit: cover; object-position: center;"
                    >

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold inventory-item-name">Balón de Baloncesto</h5>
                            <span class="badge rounded-0 inventory-status-badge" style="background-color:#6FC21F; color:white;">
                                Disponible
                            </span>
                        </div>

                        <p class="text-muted small mb-3 inventory-description">
                            Bola de baloncesto de tamaño oficial para uso interior/exterior.
                        </p>

                        <div class="small mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Cantidad Total:</span>
                                <strong class="inventory-total">25</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Cantidad Disponible:</span>
                                <strong class="text-success inventory-available">18</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ubicación:</span>
                                <strong class="inventory-location">Almacén A</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Categoría:</span>
                                <strong class="inventory-category">Baloncesto</strong>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-3">
                            <div>
                                <div class="small text-muted mb-1">Editar Cantidad Total</div>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-decrease">-</button>
                                    <span class="fw-bold inventory-total-control">25</span>
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-increase">+</button>
                                </div>
                            </div>

                            <div>
                                <div class="small text-muted mb-1">Editar Cantidad Disponible</div>
                                <div class="d-flex align-items-center justify-content-between gap-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-decrease">-</button>
                                    <span class="fw-bold inventory-available-control">18</span>
                                    <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-increase">+</button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-danger open-delete-item-modal"
                                data-item-name="Balón de Baloncesto"
                            >
                                <i class="bi bi-trash me-1"></i> Eliminar Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>

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
                    <form id="addItemForm" novalidate>
                        <div class="mb-3">
                            <label for="nombre_item" class="form-label fw-semibold">
                                Nombre del Item<span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="nombre_item"
                                name="nombre_item"
                                class="form-control form-control-lg"
                                placeholder="Ejemplo. Bola de Volibol"
                                maxlength="100"
                                required
                            >
                            <div class="form-text">
                                Entre 5 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </div>
                            <div class="invalid-feedback d-block" id="nombreItemError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label fw-semibold">
                                Categoría<span class="text-danger">*</span>
                            </label>
                            <select
                                id="categoria"
                                name="categoria"
                                class="form-select form-select-lg"
                                required
                            >
                                <option value="" selected disabled>Selecciona una categoría</option>
                                <option value="Baloncesto">Baloncesto</option>
                                <option value="Tenis">Tenis</option>
                                <option value="Fútbol">Fútbol</option>
                                <option value="Deporte Recreativo">Deporte Recreativo</option>
                                <option value="Volibol">Volibol</option>
                                <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                                <option value="Otros">Otros</option>
                            </select>
                            <div class="invalid-feedback d-block" id="categoriaError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_total" class="form-label fw-semibold">
                                Cantidad Total<span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                id="cantidad_total"
                                name="cantidad_total"
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
                                name="cantidad_disponible"
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

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label fw-semibold">
                                Ubicación<span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="ubicacion"
                                name="ubicacion"
                                class="form-control form-control-lg"
                                placeholder="Ejemplo. Almacén A"
                                maxlength="100"
                                required
                            >
                            <div class="form-text">
                                Entre 5 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </div>
                            <div class="invalid-feedback d-block" id="ubicacionError"></div>
                        </div>

                        <div class="mb-2">
                            <label for="imagen" class="form-label fw-semibold">
                                Imagen del Item<span class="text-danger">*</span>
                            </label>
                            <input
                                type="file"
                                class="d-none"
                                id="imagen"
                                name="imagen"
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

    {{-- Modal Eliminar Item --}}
    <div class="modal fade" id="deleteInventoryItemModal" tabindex="-1" aria-labelledby="deleteInventoryItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="deleteInventoryItemModalLabel">Eliminar Item</h4>
                        <p class="text-muted mb-0" id="deleteInventoryItemText">
                            ¿Seguro que deseas eliminar este item?
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteInventoryItem">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="inventoryToast"
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
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Item eliminado del inventario
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
            const addItemForm = document.getElementById('addItemForm');

            const nombreInput = document.getElementById('nombre_item');
            const categoriaInput = document.getElementById('categoria');
            const cantidadTotalInput = document.getElementById('cantidad_total');
            const cantidadDisponibleInput = document.getElementById('cantidad_disponible');
            const ubicacionInput = document.getElementById('ubicacion');
            const imageInput = document.getElementById('imagen');

            const imageError = document.getElementById('imageError');
            const previewWrapper = document.getElementById('previewWrapper');
            const imagePreview = document.getElementById('imagePreview');
            const addItemModal = document.getElementById('addItemModal');

            const inventoryToast = document.getElementById('inventoryToast');
            const inventoryDeleteToast = document.getElementById('inventoryDeleteToast');

            const nombreItemError = document.getElementById('nombreItemError');
            const categoriaError = document.getElementById('categoriaError');
            const cantidadTotalError = document.getElementById('cantidadTotalError');
            const cantidadDisponibleError = document.getElementById('cantidadDisponibleError');
            const ubicacionError = document.getElementById('ubicacionError');
            const submitAddItemBtn = document.getElementById('submitAddItemBtn');

            const deleteInventoryItemModal = document.getElementById('deleteInventoryItemModal');
            const deleteInventoryItemText = document.getElementById('deleteInventoryItemText');
            const confirmDeleteInventoryItem = document.getElementById('confirmDeleteInventoryItem');

            const inventoryCards = document.getElementById('inventoryCards');
            const inventorySearch = document.getElementById('inventorySearch');
            const inventoryCategoryFilter = document.getElementById('inventoryCategoryFilter');
            const inventoryEmptyState = document.getElementById('inventoryEmptyState');

            let inventoryCardToDelete = null;

            const inventoryAllowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
            const maxImageSize = 2 * 1024 * 1024;

            function setFieldError(field, errorElement, message) {
                field.classList.add('is-invalid');
                errorElement.textContent = message;
            }

            function clearFieldError(field, errorElement) {
                field.classList.remove('is-invalid');
                errorElement.textContent = '';
            }

            function clearAllInvalid() {
                clearFieldError(nombreInput, nombreItemError);
                clearFieldError(categoriaInput, categoriaError);
                clearFieldError(cantidadTotalInput, cantidadTotalError);
                clearFieldError(cantidadDisponibleInput, cantidadDisponibleError);
                clearFieldError(ubicacionInput, ubicacionError);
                clearFieldError(imageInput, imageError);
            }

            function resetImageState() {
                imageError.textContent = '';
                imageInput.classList.remove('is-invalid');
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
            }

            function validateTextField(field, errorElement, fieldLabel, showError = true) {
                const value = field.value.trim();

                if (showError) {
                    clearFieldError(field, errorElement);
                }

                if (!value) {
                    if (showError) setFieldError(field, errorElement, `La ${fieldLabel} es obligatoria.`);
                    return false;
                }

                if (value.length < 5) {
                    if (showError) setFieldError(field, errorElement, `La ${fieldLabel} debe tener al menos 5 caracteres.`);
                    return false;
                }

                if (value.length > 100) {
                    if (showError) setFieldError(field, errorElement, `La ${fieldLabel} no puede exceder 100 caracteres.`);
                    return false;
                }

                if (!inventoryAllowedTextRegex.test(value)) {
                    if (showError) setFieldError(field, errorElement, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
                    return false;
                }

                return true;
            }

            function validateNombreItem(showError = true) {
                return validateTextField(nombreInput, nombreItemError, 'nombre del item', showError);
            }

            function validateUbicacion(showError = true) {
                return validateTextField(ubicacionInput, ubicacionError, 'ubicación', showError);
            }

            function validateCategoria(showError = true) {
                const isValid = !!categoriaInput.value;

                if (showError) {
                    if (!isValid) {
                        setFieldError(categoriaInput, categoriaError, 'Debes seleccionar una categoría.');
                    } else {
                        clearFieldError(categoriaInput, categoriaError);
                    }
                }

                return isValid;
            }

            function validateCantidadTotal(showError = true) {
                const value = cantidadTotalInput.value.trim();

                if (showError) {
                    clearFieldError(cantidadTotalInput, cantidadTotalError);
                }

                if (!value) {
                    if (showError) setFieldError(cantidadTotalInput, cantidadTotalError, 'La cantidad total es obligatoria.');
                    return false;
                }

                if (!Number.isInteger(Number(value)) || Number(value) < 1) {
                    if (showError) setFieldError(cantidadTotalInput, cantidadTotalError, 'Debe ser un número entero mayor o igual a 1.');
                    return false;
                }

                return true;
            }

            function validateCantidadDisponible(showError = true) {
                const value = cantidadDisponibleInput.value.trim();
                const totalValue = cantidadTotalInput.value.trim();

                if (showError) {
                    clearFieldError(cantidadDisponibleInput, cantidadDisponibleError);
                }

                if (value === '') {
                    if (showError) setFieldError(cantidadDisponibleInput, cantidadDisponibleError, 'La cantidad disponible es obligatoria.');
                    return false;
                }

                if (!Number.isInteger(Number(value)) || Number(value) < 0) {
                    if (showError) setFieldError(cantidadDisponibleInput, cantidadDisponibleError, 'Debe ser un número entero igual o mayor a 0.');
                    return false;
                }

                if (totalValue !== '' && Number(value) > Number(totalValue)) {
                    if (showError) setFieldError(cantidadDisponibleInput, cantidadDisponibleError, 'No puede exceder la cantidad total.');
                    return false;
                }

                return true;
            }

            function validateImage(showError = true) {
                const file = imageInput.files[0];

                if (showError) {
                    clearFieldError(imageInput, imageError);
                }

                if (!file) {
                    if (showError) setFieldError(imageInput, imageError, 'Debes subir una imagen.');
                    return false;
                }

                if (file.type !== 'image/jpeg') {
                    if (showError) setFieldError(imageInput, imageError, 'Solo se permiten archivos JPG o JPEG.');
                    return false;
                }

                if (file.size > maxImageSize) {
                    if (showError) setFieldError(imageInput, imageError, 'La imagen no puede exceder 2 MB.');
                    return false;
                }

                return true;
            }

            function updateAddItemButtonState() {
                const isReady =
                    validateNombreItem(false) &&
                    validateCategoria(false) &&
                    validateCantidadTotal(false) &&
                    validateCantidadDisponible(false) &&
                    validateUbicacion(false) &&
                    validateImage(false);

                submitAddItemBtn.disabled = !isReady;
            }

            function updateStatusBadge(cardWrapper) {
                const availableText = cardWrapper.querySelector('.inventory-available');
                const badge = cardWrapper.querySelector('.inventory-status-badge');

                if (!availableText || !badge) return;

                const available = parseInt(availableText.textContent, 10);

                if (available > 0) {
                    badge.textContent = 'Disponible';
                    badge.style.backgroundColor = '#6FC21F';
                    badge.style.color = 'white';
                } else {
                    badge.textContent = 'No disponible';
                    badge.style.backgroundColor = '#dc3545';
                    badge.style.color = 'white';
                }
            }

            function filterInventoryCards() {
                const searchValue = inventorySearch.value.trim().toLowerCase();
                const categoryValue = inventoryCategoryFilter.value.trim().toLowerCase();

                const cards = inventoryCards.querySelectorAll('.inventory-card-wrapper');
                let visibleCount = 0;

                cards.forEach((card) => {
                    const itemName = card.querySelector('.inventory-item-name')?.textContent.toLowerCase() || '';
                    const itemCategory = card.querySelector('.inventory-category')?.textContent.toLowerCase() || '';

                    const matchesSearch = !searchValue || itemName.includes(searchValue);
                    const matchesCategory = !categoryValue || itemCategory === categoryValue;

                    if (matchesSearch && matchesCategory) {
                        card.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        card.classList.add('d-none');
                    }
                });

                inventoryEmptyState.classList.toggle('d-none', visibleCount > 0);
            }

            function attachInventoryCardEvents() {
                document.querySelectorAll('.inventory-card-wrapper').forEach((cardWrapper) => {
                    const totalDecreaseBtn = cardWrapper.querySelector('.inventory-total-decrease');
                    const totalIncreaseBtn = cardWrapper.querySelector('.inventory-total-increase');
                    const availableDecreaseBtn = cardWrapper.querySelector('.inventory-available-decrease');
                    const availableIncreaseBtn = cardWrapper.querySelector('.inventory-available-increase');

                    const totalText = cardWrapper.querySelector('.inventory-total');
                    const availableText = cardWrapper.querySelector('.inventory-available');
                    const totalControlText = cardWrapper.querySelector('.inventory-total-control');
                    const availableControlText = cardWrapper.querySelector('.inventory-available-control');

                    const deleteBtn = cardWrapper.querySelector('.open-delete-item-modal');

                    if (totalDecreaseBtn && totalText && totalControlText && availableText && availableControlText) {
                        totalDecreaseBtn.onclick = function () {
                            let currentTotal = parseInt(totalText.textContent, 10);
                            let currentAvailable = parseInt(availableText.textContent, 10);

                            if (currentTotal > 1) {
                                currentTotal -= 1;

                                if (currentAvailable > currentTotal) {
                                    currentAvailable = currentTotal;
                                }

                                totalText.textContent = currentTotal;
                                totalControlText.textContent = currentTotal;
                                availableText.textContent = currentAvailable;
                                availableControlText.textContent = currentAvailable;

                                updateStatusBadge(cardWrapper);
                            }
                        };
                    }

                    if (totalIncreaseBtn && totalText && totalControlText) {
                        totalIncreaseBtn.onclick = function () {
                            let currentTotal = parseInt(totalText.textContent, 10);
                            currentTotal += 1;

                            totalText.textContent = currentTotal;
                            totalControlText.textContent = currentTotal;

                            updateStatusBadge(cardWrapper);
                        };
                    }

                    if (availableDecreaseBtn && availableText && availableControlText) {
                        availableDecreaseBtn.onclick = function () {
                            let currentAvailable = parseInt(availableText.textContent, 10);

                            if (currentAvailable > 0) {
                                currentAvailable -= 1;
                                availableText.textContent = currentAvailable;
                                availableControlText.textContent = currentAvailable;

                                updateStatusBadge(cardWrapper);
                            }
                        };
                    }

                    if (availableIncreaseBtn && availableText && availableControlText && totalText) {
                        availableIncreaseBtn.onclick = function () {
                            let currentAvailable = parseInt(availableText.textContent, 10);
                            const currentTotal = parseInt(totalText.textContent, 10);

                            if (currentAvailable < currentTotal) {
                                currentAvailable += 1;
                                availableText.textContent = currentAvailable;
                                availableControlText.textContent = currentAvailable;

                                updateStatusBadge(cardWrapper);
                            }
                        };
                    }

                    if (deleteBtn) {
                        deleteBtn.onclick = function () {
                            inventoryCardToDelete = cardWrapper;

                            const itemName = deleteBtn.dataset.itemName || 'este item';
                            deleteInventoryItemText.textContent = `¿Seguro que deseas eliminar "${itemName}" del inventario?`;

                            const modalInstance = window.bootstrap.Modal.getOrCreateInstance(deleteInventoryItemModal);
                            modalInstance.show();
                        };
                    }

                    updateStatusBadge(cardWrapper);
                });
            }

            imageInput.addEventListener('change', function () {
                const file = imageInput.files[0];

                resetImageState();

                if (!file) {
                    updateAddItemButtonState();
                    return;
                }

                if (file.type !== 'image/jpeg') {
                    setFieldError(imageInput, imageError, 'Solo se permiten archivos JPG o JPEG.');
                    updateAddItemButtonState();
                    return;
                }

                if (file.size > maxImageSize) {
                    setFieldError(imageInput, imageError, 'La imagen no puede exceder 2 MB.');
                    updateAddItemButtonState();
                    return;
                }

                const reader = new FileReader();

                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewWrapper.classList.remove('d-none');
                };

                reader.readAsDataURL(file);

                clearFieldError(imageInput, imageError);
                updateAddItemButtonState();
            });

            nombreInput.addEventListener('input', function () {
                nombreInput.value = nombreInput.value.slice(0, 100);
                validateNombreItem(true);
                updateAddItemButtonState();
            });

            categoriaInput.addEventListener('change', function () {
                validateCategoria(true);
                updateAddItemButtonState();
            });

            cantidadTotalInput.addEventListener('input', function () {
                validateCantidadTotal(true);
                validateCantidadDisponible(true);
                updateAddItemButtonState();
            });

            cantidadDisponibleInput.addEventListener('input', function () {
                validateCantidadDisponible(true);
                updateAddItemButtonState();
            });

            ubicacionInput.addEventListener('input', function () {
                ubicacionInput.value = ubicacionInput.value.slice(0, 100);
                validateUbicacion(true);
                updateAddItemButtonState();
            });

            inventorySearch.addEventListener('input', filterInventoryCards);
            inventoryCategoryFilter.addEventListener('change', filterInventoryCards);

            addItemForm.addEventListener('submit', function (e) {
                e.preventDefault();

                const isNombreValid = validateNombreItem(true);
                const isCategoriaValid = validateCategoria(true);
                const isCantidadTotalValid = validateCantidadTotal(true);
                const isCantidadDisponibleValid = validateCantidadDisponible(true);
                const isUbicacionValid = validateUbicacion(true);
                const isImageValid = validateImage(true);

                if (
                    !isNombreValid ||
                    !isCategoriaValid ||
                    !isCantidadTotalValid ||
                    !isCantidadDisponibleValid ||
                    !isUbicacionValid ||
                    !isImageValid
                ) {
                    updateAddItemButtonState();
                    return;
                }

                const nombre = nombreInput.value.trim();
                const categoria = categoriaInput.value;
                const cantidadTotal = cantidadTotalInput.value;
                const cantidadDisponible = cantidadDisponibleInput.value;
                const ubicacion = ubicacionInput.value.trim();
                const file = imageInput.files[0];

                const imageUrl = URL.createObjectURL(file);
                const statusText = Number(cantidadDisponible) > 0 ? 'Disponible' : 'No disponible';
                const statusColor = Number(cantidadDisponible) > 0 ? '#6FC21F' : '#dc3545';

                const cardHtml = `
                    <div class="col-md-6 col-lg-4 inventory-card-wrapper">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                            <img
                                src="${imageUrl}"
                                class="card-img-top"
                                alt="${nombre}"
                                style="height: 220px; object-fit: cover; object-position: center;"
                            >

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 fw-bold inventory-item-name">${nombre}</h5>
                                    <span class="badge rounded-0 inventory-status-badge" style="background-color:${statusColor}; color:white;">
                                        ${statusText}
                                    </span>
                                </div>

                                <p class="text-muted small mb-3 inventory-description">
                                    Item agregado manualmente al inventario.
                                </p>

                                <div class="small mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Cantidad Total:</span>
                                        <strong class="inventory-total">${cantidadTotal}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Cantidad Disponible:</span>
                                        <strong class="text-success inventory-available">${cantidadDisponible}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Ubicación:</span>
                                        <strong class="inventory-location">${ubicacion}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Categoría:</span>
                                        <strong class="inventory-category">${categoria}</strong>
                                    </div>
                                </div>

                                <div class="mt-auto d-grid gap-3">
                                    <div>
                                        <div class="small text-muted mb-1">Editar Cantidad Total</div>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-decrease">-</button>
                                            <span class="fw-bold inventory-total-control">${cantidadTotal}</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-increase">+</button>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="small text-muted mb-1">Editar Cantidad Disponible</div>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-decrease">-</button>
                                            <span class="fw-bold inventory-available-control">${cantidadDisponible}</span>
                                            <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-increase">+</button>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="btn btn-danger open-delete-item-modal"
                                        data-item-name="${nombre}"
                                    >
                                        <i class="bi bi-trash me-1"></i> Eliminar Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                inventoryCards.insertAdjacentHTML('beforeend', cardHtml);

                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(addItemModal);
                modalInstance.hide();

                addItemForm.reset();
                clearAllInvalid();
                resetImageState();
                updateAddItemButtonState();

                attachInventoryCardEvents();
                filterInventoryCards();

                setTimeout(() => {
                    const toastInstance = window.bootstrap.Toast.getOrCreateInstance(inventoryToast);
                    toastInstance.show();
                }, 250);
            });

            if (confirmDeleteInventoryItem) {
                confirmDeleteInventoryItem.addEventListener('click', function () {
                    if (inventoryCardToDelete) {
                        inventoryCardToDelete.remove();
                        inventoryCardToDelete = null;
                    }

                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(deleteInventoryItemModal);
                    modalInstance.hide();

                    filterInventoryCards();

                    setTimeout(() => {
                        const deleteToastInstance = window.bootstrap.Toast.getOrCreateInstance(inventoryDeleteToast);
                        deleteToastInstance.show();
                    }, 250);
                });
            }

            attachInventoryCardEvents();
            updateAddItemButtonState();
            filterInventoryCards();
        });
    </script>
</x-layout>
