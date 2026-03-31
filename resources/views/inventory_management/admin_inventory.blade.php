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

                                <span
                                    class="badge rounded-0 inventory-status-badge"
                                    style="background-color: {{ $item->available_quantity > 0 ? '#6FC21F' : '#dc3545' }}; color:white;"
                                >
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
                                    class="btn btn-outline-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editItemModal{{ $item->id }}"
                                >
                                    Editar
                                </button>

                                <form action="{{ route('equipment.destroy', $item->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-trash me-1"></i> Eliminar Item
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Edit modal --}}
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
                                <form method="POST" action="{{ route('equipment.update', $item->id) }}" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nombre / Descripción*</label>
                                        <input
                                            type="text"
                                            name="description"
                                            class="form-control form-control-lg"
                                            value="{{ $item->description }}"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Categoría*</label>
                                        <input
                                            type="text"
                                            name="category"
                                            class="form-control form-control-lg"
                                            list="categoryOptions"
                                            value="{{ $item->category }}"
                                            placeholder="Selecciona o escribe una categoría"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cantidad Total*</label>
                                        <input
                                            type="number"
                                            name="quantity"
                                            class="form-control form-control-lg"
                                            min="1"
                                            value="{{ $item->quantity }}"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Cantidad Disponible*</label>
                                        <input
                                            type="number"
                                            name="available_quantity"
                                            class="form-control form-control-lg"
                                            min="0"
                                            value="{{ $item->available_quantity }}"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Ubicación*</label>
                                        <input
                                            type="text"
                                            name="location"
                                            class="form-control form-control-lg"
                                            value="{{ $item->location }}"
                                            required
                                        >
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Nueva Imagen (opcional)</label>
                                        <input
                                            type="file"
                                            name="image"
                                            class="form-control"
                                            accept=".jpg,.jpeg,image/jpeg"
                                        >
                                    </div>

                                    <div class="modal-footer border-0 px-0 pb-0">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-warning">
                                            Guardar cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info rounded-4 shadow-sm">
                        No hay items en el inventario todavía.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-center">
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    </div>

    {{-- Shared datalist --}}
    <datalist id="categoryOptions">
        @foreach($categories as $category)
            <option value="{{ $category }}">
        @endforeach
    </datalist>

    {{-- Modal Agregar Item --}}
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="addItemModalLabel">Agregar Nuevo Item</h4>
                        <p class="text-muted mb-0">
                            Completa la información del nuevo equipo
                        </p>
                        <p class="mt-2 mb-0 text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <form id="addItemForm"
                          method="POST"
                          action="{{ route('inventory.store') }}"
                          enctype="multipart/form-data">
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
                            <input
                                type="text"
                                id="categoria"
                                name="category"
                                class="form-control form-control-lg"
                                list="categoryOptions"
                                placeholder="Selecciona o escribe una categoría"
                                required
                            >
                            <div class="invalid-feedback d-block" id="categoriaError"></div>
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

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label fw-semibold">
                                Ubicación<span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                id="ubicacion"
                                name="location"
                                class="form-control form-control-lg"
                                placeholder="Ej. Almacén A"
                                required
                            >
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

    {{-- Toast --}}
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
            const inventoryToast = document.getElementById('inventoryToast');
            const submitAddItemBtn = document.getElementById('submitAddItemBtn');

            const nombreItemError = document.getElementById('nombreItemError');
            const categoriaError = document.getElementById('categoriaError');
            const cantidadTotalError = document.getElementById('cantidadTotalError');
            const cantidadDisponibleError = document.getElementById('cantidadDisponibleError');
            const ubicacionError = document.getElementById('ubicacionError');

            const inventoryAllowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
            const maxImageSize = 2 * 1024 * 1024;

            function setFieldError(field, errorElement, message) {
                if (!field) return;
                field.classList.add('is-invalid');
                if (errorElement) {
                    errorElement.textContent = message;
                }
            }

            function clearFieldError(field, errorElement) {
                if (!field) return;
                field.classList.remove('is-invalid');
                if (errorElement) {
                    errorElement.textContent = '';
                }
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

            function validateNombreItem(showError = true) {
                const value = nombreInput.value.trim();

                if (showError) clearFieldError(nombreInput, nombreItemError);

                if (!value) {
                    if (showError) setFieldError(nombreInput, nombreItemError, 'El nombre del item es obligatorio.');
                    return false;
                }

                if (value.length < 5) {
                    if (showError) setFieldError(nombreInput, nombreItemError, 'El nombre debe tener al menos 5 caracteres.');
                    return false;
                }

                if (value.length > 100) {
                    if (showError) setFieldError(nombreInput, nombreItemError, 'El nombre no puede exceder 100 caracteres.');
                    return false;
                }

                if (!inventoryAllowedTextRegex.test(value)) {
                    if (showError) setFieldError(nombreInput, nombreItemError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
                    return false;
                }

                return true;
            }

            function validateCategoria(showError = true) {
                const value = categoriaInput.value.trim();
                const isValid = !!value;

                if (showError) {
                    if (!isValid) {
                        setFieldError(categoriaInput, categoriaError, 'Debes seleccionar o escribir una categoría.');
                    } else {
                        clearFieldError(categoriaInput, categoriaError);
                    }
                }

                return isValid;
            }

            function validateCantidadTotal(showError = true) {
                const value = cantidadTotalInput.value.trim();

                if (showError) clearFieldError(cantidadTotalInput, cantidadTotalError);

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

                if (showError) clearFieldError(cantidadDisponibleInput, cantidadDisponibleError);

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

            function validateUbicacion(showError = true) {
                const value = ubicacionInput.value.trim();
                const isValid = !!value;

                if (showError) {
                    if (!isValid) {
                        setFieldError(ubicacionInput, ubicacionError, 'La ubicación es obligatoria.');
                    } else {
                        clearFieldError(ubicacionInput, ubicacionError);
                    }
                }

                return isValid;
            }

            function validateImage(showError = true) {
                const file = imageInput.files[0];

                if (showError) clearFieldError(imageInput, imageError);

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

            categoriaInput.addEventListener('input', function () {
                validateCategoria(true);
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
                validateUbicacion(true);
                updateAddItemButtonState();
            });

            addItemForm.addEventListener('submit', function (e) {
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
                    e.preventDefault();
                    updateAddItemButtonState();
                }
            });

            @if(session('success'))
                const toastInstance = window.bootstrap.Toast.getOrCreateInstance(inventoryToast);
                toastInstance.show();
            @endif

            clearAllInvalid();
            updateAddItemButtonState();
        });
    </script>
</x-layout>