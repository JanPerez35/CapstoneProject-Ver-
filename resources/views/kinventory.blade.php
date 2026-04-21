<x-layout title="Kinventario">
    <x-navbar></x-navbar>

    {{--Load JS responsible for the validation in kinventory, includes modal behaviour and scroll persistence--}}
    @vite('resources/js/kinventory_validation.js')

    <div class= "container py-4">

    {{--Page header introducing the Kinventory/Kinventario page--}}
        <div class="mb-4">
            <h1 class="fw-bold" >Bienvenido al Kinventario</h1>
            <p>Aquí puedes pedir prestado equipo deportivo
                directamente del departamento de Kinesiología.
            </p>
        </div>

        {{--Search and filter form for browsing equipment, connects to the backend to do the requests--}}
        <form method="GET" action="{{ route('kinventory') }}" class="mb-5">

            {{--This is the Search bar--}}
            <div class="row g-3 mb-3 align-items-stretch">
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                        <span class="input-group-text bg-white border-0">
                            <i class="bi bi-search"></i>
                        </span>

                        <input
                            type="text"
                            name="search"
                            id="kinventorySearchInput"
                            class="form-control border-0"
                            placeholder="Buscar equipo deportivo..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>
                {{--Search button that appears disabled until the user inputs text--}}
                <div class="col-lg-2 d-grid">
                    <button type="submit" id="kinventorySearchBtn" class="btn btn-success h-100" disabled>                        Buscar
                    </button>
                </div>
            </div>

            {{--Category filter and clean filters connects with backend to request categories available--}}
            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <select name="category" class="form-select border-2 border-dark" onchange="this.form.submit()">
                        <option value="">Todas las Categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{--Reset for all filters and reloads the page--}}
                <div class="col-md-auto">
                    <a href="{{ route('kinventory') }}" class="btn btn-outline-secondary">
                        Limpiar Filtros
                    </a>
                </div>
            </div>
        </form>

        {{--Equipment grid display, where all the inventory items are displayed--}}
        <div class="row g-4 mt-2">
            @forelse($items as $item)

                {{--How the card looks individually--}}
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">

                        {{--Equipment image with a fall back if none exists--}}
                        <img
                            src="{{ $item->equipment_photo_url ? asset('storage/' . $item->equipment_photo_url) : asset('images/kinventory_images/default.jpg') }}"
                            class="card-img-top"
                            alt="{{ $item->description }}"
                            style="height: 300px; object-fit: contain; object-position: center; background-color:#f8f9fa;"
                        >
                        {{--Title and badge of current availability for item--}}
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold">
                                    {{ $item->description }}
                                </h5>

                                {{--Badge changes based on stock availability--}}
                                <span class="label-badge {{ $item->available_quantity > 0 ? 'badge-available' : 'badge-unavailable' }}">
                                {{ $item->available_quantity > 0 ? 'Disponible' : 'No disponible' }}
                                </span>

                            </div>

                            {{--Display for currently available stock--}}
                            <div class="small mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Cantidad Disponible:</span>
                                    <strong class="text-success">{{ $item->available_quantity }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Categoría:</span>
                                    <strong class="inventory-category">{{ $item->category }}</strong>
                                </div>

                            </div>

                            {{--Borrow button (disabled if no stock is currently available)--}}
                            <div class="mt-auto d-grid gap-2">
                                <button
                                    type="button"
                                    class="btn btn-success open-borrow-modal"
                                    data-item-id="{{ $item->id }}"
                                    data-item-name="{{ $item->description }}"
                                    data-item-stock="{{ $item->available_quantity }}"
                                    data-item-image="{{ $item->equipment_photo_url ? asset('storage/' . $item->equipment_photo_url) : asset('images/kinventory_images/default.jpg') }}"
                                    data-item-location="{{ $item->location }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#borrowModal"
                                    {{ $item->available_quantity == 0 ? 'disabled' : '' }}
                                >
                                    Pedir Prestado
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            {{--Displays an empty state when no items are matching the filters--}}
            @empty
                <div class="col-12">
                    <div id="itemsEmptyState" class="card border-0 shadow-sm rounded-4">
                        <div class="card-body py-5 text-center">
                            <i class="bi bi-box-seam fs-1 text-muted"></i>
                            <h4 class="fw-bold mt-3">No se encontraron equipos</h4>
                            <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro equipo.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{--Control for pagination--}}
        @if ($items->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        @endif

    </div>

    {{--Borrow modal to add items to the cart--}}
    <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">

                {{--Modal header--}}
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="borrowModalLabel">Agregar al carrito</h4>
                        <p class="text-muted mb-0" id="borrowModalText">
                            Selecciona la cantidad que deseas
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                {{--Form that submits the selected quantity to the cart--}}
                <form method="POST" action="{{ route('cart.add') }}" id="borrowForm" novalidate>
                    @csrf
                    {{--Used to keep the user in the same filtered tab after submission, for usability purposes--}}
                    <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
                    <div class="modal-body">
                    {{--Equipment ID is sent to backend for tracking--}}
                        <input type="hidden" name="equipment_id" id="borrowEquipmentId">

                        {{--Equipment preview image--}}
                        <img
                            id="borrowModalImage"
                            src=""
                            alt="Equipo"
                            class="img-fluid rounded-4 mb-3"
                            style="height: 280px; width: 100%; object-fit: contain; object-position: center; background-color:#f8f9fa;"
                        >

                        {{--Shows available stock inside modal of item--}}
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Cantidad Disponible:</span>
                            <strong class="text-success">
                                <span id="borrowModalStock">0</span> unidades
                            </strong>
                        </div>

                        {{--Validates quantity input with JS--}}
                        <div class="mb-3">
                            <label for="borrowQuantity" class="form-label fw-semibold">Cantidad</label>
                            <input
                                type="number"
                                id="borrowQuantity"
                                name="quantity"
                                class="form-control form-control-lg"
                                min="1"
                                value="1"
                                required
                            >
                            {{--Displays error if there is invalid inputs in the quantity input--}}
                            <div class="invalid-feedback" id="borrowQuantityError"></div>
                        </div>
                    </div>

                    {{--Modal actions to cancel or add to cart--}}
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        {{--Triggers validation before submitting form--}}
                        <button type="button" class="btn btn-success" id="confirmAddToCart">
                            <i class="bi bi-cart-plus me-1"></i> Añadir al Carrito
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout>
