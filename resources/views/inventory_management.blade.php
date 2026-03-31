<!-- <!-- <x-layout title="Gestión de Inventario">
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
            <a href="#"
               class="btn btn-outline-success px-4 fw-semibold">
                Préstamos
            </a>

            <a href="#"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
            </a>

            <a href="#"
               class="btn btn-success px-4 fw-semibold">
                Inventario Administrativo
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

            <!-- <div class="col-md-6 col-lg-4 inventory-card-wrapper">
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
            </div> -->
                <!-- @forelse($items as $item)
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
                                                placeholder="Selecciona o escribe una categoría"
                                                required
                                            >

                                            <datalist id="categoryOptions">
                                                @foreach($categories as $category)
                                                    <option value="{{ $category }}">
                                                @endforeach
                                            </datalist>
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
    </div>

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
                            <label for="nombre_item" class="form-label fw-semibold">Nombre del Item*</label>
                            <input
                                type="text"
                                id="nombre_item"
                                name="description"
                                class="form-control form-control-lg"
                                placeholder="Ejemplo. Bola de Volibol"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="categoria" class="form-label fw-semibold">Categoría*</label>
                            <input 
                                type="text"
                                name="category"
                                class="form-control form-control-lg"
                                list="categoryOptions"
                                placeholder="Selecciona o escribe una categoría"
                                required
                            >

                            <datalist id="categoryOptions">
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad_total" class="form-label fw-semibold">Cantidad Total*</label>
                            <input
                                type="number"
                                id="cantidad_total"
                                name="quantity"
                                class="form-control form-control-lg"
                                min="1"
                                placeholder="Ej. 10"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="cantidad_disponible" class="form-label fw-semibold">Cantidad Disponible*</label>
                            <input
                                type="number"
                                id="cantidad_disponible"
                                name="available_quantity"
                                class="form-control form-control-lg"
                                min="0"
                                placeholder="Ej. 8"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="ubicacion" class="form-label fw-semibold">Ubicación*</label>
                            <input
                                type="text"
                                id="ubicacion"
                                name="location"
                                class="form-control form-control-lg"
                                placeholder="Ej. Almacén A"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label for="imagen" class="form-label fw-semibold">Imagen del Item*</label>
                            <input
                                type="file"
                                id="imagen"
                                name="image"
                                class="form-control"
                                accept=".jpg,.jpeg,image/jpeg"
                                required
                            >
                            <div class="form-text">
                                Solo se permiten archivos JPG/JPEG de hasta 2 MB.
                            </div>
                            <div id="imageError" class="text-danger small mt-2 d-none"></div>
                        </div>

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
                            <button type="submit" class="btn btn-success">
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
        <div id="inventoryToast" class="toast text-bg-success border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Item añadido correctamente
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>

        <div id="inventoryDeleteToast" class="toast text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Item eliminado del inventario
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
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

            const deleteInventoryItemModal = document.getElementById('deleteInventoryItemModal');
            const deleteInventoryItemText = document.getElementById('deleteInventoryItemText');
            const confirmDeleteInventoryItem = document.getElementById('confirmDeleteInventoryItem');

            const inventoryCards = document.getElementById('inventoryCards');

            let inventoryCardToDelete = null;

            const requiredFields = [
                nombreInput,
                categoriaInput,
                cantidadTotalInput,
                cantidadDisponibleInput,
                ubicacionInput,
                imageInput
            ];

            function markInvalid(field) {
                field.classList.add('is-invalid');
            }

            function clearInvalid(field) {
                field.classList.remove('is-invalid');
            }

            function clearAllInvalid() {
                requiredFields.forEach(field => clearInvalid(field));
            }

            function resetImageState() {
                imageError.classList.add('d-none');
                imageError.textContent = '';
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
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

            requiredFields.forEach(field => {
                field.addEventListener('input', function () {
                    clearInvalid(field);
                    if (field === imageInput) {
                        imageError.classList.add('d-none');
                        imageError.textContent = '';
                    }
                });

                field.addEventListener('change', function () {
                    clearInvalid(field);
                    if (field === imageInput) {
                        imageError.classList.add('d-none');
                        imageError.textContent = '';
                    }
                });
            });

            imageInput.addEventListener('change', function () {
                resetImageState();
                clearInvalid(imageInput);

                const file = this.files[0];
                if (!file) return;

                const allowedTypes = ['image/jpeg'];
                const maxSize = 2 * 1024 * 1024;

                if (!allowedTypes.includes(file.type)) {
                    markInvalid(imageInput);
                    imageError.textContent = 'Solo se permiten archivos JPG o JPEG.';
                    imageError.classList.remove('d-none');
                    this.value = '';
                    return;
                }

                if (file.size > maxSize) {
                    markInvalid(imageInput);
                    imageError.textContent = 'La imagen no puede exceder 2 MB.';
                    imageError.classList.remove('d-none');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
                    imagePreview.src = e.target.result;
                    previewWrapper.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            }); --> -->

            <!-- // addItemForm.addEventListener('submit', function (e) {
            //     e.preventDefault();

            //     clearAllInvalid();

            //     const nombre = nombreInput.value.trim();
            //     const categoria = categoriaInput.value;
            //     const cantidadTotal = cantidadTotalInput.value;
            //     const cantidadDisponible = cantidadDisponibleInput.value;
            //     const ubicacion = ubicacionInput.value.trim();
            //     const file = imageInput.files[0];

            //     let hasError = false;

            //     if (!nombre) {
            //         markInvalid(nombreInput);
            //         hasError = true;
            //     }

            //     if (!categoria) {
            //         markInvalid(categoriaInput);
            //         hasError = true;
            //     }

            //     if (!cantidadTotal || Number(cantidadTotal) < 1) {
            //         markInvalid(cantidadTotalInput);
            //         hasError = true;
            //     }

            //     if (cantidadDisponible === '' || Number(cantidadDisponible) < 0) {
            //         markInvalid(cantidadDisponibleInput);
            //         hasError = true;
            //     }

            //     if (
            //         cantidadTotal &&
            //         cantidadDisponible !== '' &&
            //         Number(cantidadDisponible) > Number(cantidadTotal)
            //     ) {
            //         markInvalid(cantidadDisponibleInput);
            //         markInvalid(cantidadTotalInput);
            //         hasError = true;
            //     }

            //     if (!ubicacion) {
            //         markInvalid(ubicacionInput);
            //         hasError = true;
            //     }

            //     if (!file) {
            //         markInvalid(imageInput);
            //         imageError.textContent = 'Debes subir una imagen.';
            //         imageError.classList.remove('d-none');
            //         hasError = true;
            //     }

            //     if (hasError) {
            //         return;
            //     }

            //     const allowedTypes = ['image/jpeg'];
            //     const maxSize = 2 * 1024 * 1024;

            //     if (!allowedTypes.includes(file.type)) {
            //         markInvalid(imageInput);
            //         imageError.textContent = 'Solo se permiten archivos JPG o JPEG.';
            //         imageError.classList.remove('d-none');
            //         return;
            //     }

            //     if (file.size > maxSize) {
            //         markInvalid(imageInput);
            //         imageError.textContent = 'La imagen no puede exceder 2 MB.';
            //         imageError.classList.remove('d-none');
            //         return;
            //     }

            //     const imageUrl = URL.createObjectURL(file);
            //     const statusText = Number(cantidadDisponible) > 0 ? 'Disponible' : 'No disponible';
            //     const statusColor = Number(cantidadDisponible) > 0 ? '#6FC21F' : '#dc3545';

            //     const cardHtml = `
            //         <div class="col-md-6 col-lg-4 inventory-card-wrapper">
            //             <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
            //                 <img
            //                     src="${imageUrl}"
            //                     class="card-img-top"
            //                     alt="${nombre}"
            //                     style="height: 220px; object-fit: cover; object-position: center;"
            //                 >

            //                 <div class="card-body d-flex flex-column">
            //                     <div class="d-flex justify-content-between align-items-start mb-2">
            //                         <h5 class="card-title mb-0 fw-bold inventory-item-name">${nombre}</h5>
            //                         <span class="badge rounded-0 inventory-status-badge" style="background-color:${statusColor}; color:white;">
            //                             ${statusText}
            //                         </span>
            //                     </div>

            //                     <p class="text-muted small mb-3 inventory-description">
            //                         Item agregado manualmente al inventario.
            //                     </p>

            //                     <div class="small mb-3">
            //                         <div class="d-flex justify-content-between">
            //                             <span class="text-muted">Cantidad Total:</span>
            //                             <strong class="inventory-total">${cantidadTotal}</strong>
            //                         </div>
            //                         <div class="d-flex justify-content-between">
            //                             <span class="text-muted">Cantidad Disponible:</span>
            //                             <strong class="text-success inventory-available">${cantidadDisponible}</strong>
            //                         </div>
            //                         <div class="d-flex justify-content-between">
            //                             <span class="text-muted">Ubicación:</span>
            //                             <strong class="inventory-location">${ubicacion}</strong>
            //                         </div>
            //                         <div class="d-flex justify-content-between">
            //                             <span class="text-muted">Categoría:</span>
            //                             <strong class="inventory-category">${categoria}</strong>
            //                         </div>
            //                     </div>

            //                     <div class="mt-auto d-grid gap-3">
            //                         <div>
            //                             <div class="small text-muted mb-1">Editar Cantidad Total</div>
            //                             <div class="d-flex align-items-center justify-content-between gap-2">
            //                                 <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-decrease">-</button>
            //                                 <span class="fw-bold inventory-total-control">${cantidadTotal}</span>
            //                                 <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-total-increase">+</button>
            //                             </div>
            //                         </div>

            //                         <div>
            //                             <div class="small text-muted mb-1">Editar Cantidad Disponible</div>
            //                             <div class="d-flex align-items-center justify-content-between gap-2">
            //                                 <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-decrease">-</button>
            //                                 <span class="fw-bold inventory-available-control">${cantidadDisponible}</span>
            //                                 <button type="button" class="btn btn-outline-secondary btn-sm px-3 inventory-available-increase">+</button>
            //                             </div>
            //                         </div>

            //                         <button
            //                             type="button"
            //                             class="btn btn-danger open-delete-item-modal"
            //                             data-item-name="${nombre}"
            //                         >
            //                             <i class="bi bi-trash me-1"></i> Eliminar Item
            //                         </button>
            //                     </div>
            //                 </div>
            //             </div>
            //         </div>
            //     `;

            //     inventoryCards.insertAdjacentHTML('beforeend', cardHtml);

            //     const modalInstance = window.bootstrap.Modal.getOrCreateInstance(addItemModal);
            //     modalInstance.hide();

            //     addItemForm.reset();
            //     clearAllInvalid();
            //     resetImageState();

            //     attachInventoryCardEvents();

            //     setTimeout(() => {
            //         const toastInstance = window.bootstrap.Toast.getOrCreateInstance(inventoryToast);
            //         toastInstance.show();
            //     }, 250);
            // }); -->

            <!-- if (confirmDeleteInventoryItem) {
                confirmDeleteInventoryItem.addEventListener('click', function () {
                    if (inventoryCardToDelete) {
                        inventoryCardToDelete.remove();
                        inventoryCardToDelete = null;
                    }

                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(deleteInventoryItemModal);
                    modalInstance.hide();

                    setTimeout(() => {
                        const deleteToastInstance = window.bootstrap.Toast.getOrCreateInstance(inventoryDeleteToast);
                        deleteToastInstance.show();
                    }, 250);
                });
            }

            attachInventoryCardEvents();
        });
    </script>
</x-layout> --> -->
