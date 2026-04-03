<x-layout title="Kinventario">
    <x-navbar>
    </x-navbar>
    @vite('resources/js/kinventory_validation.js')

    <div class= "container py-4">

        <div class="mb-4">
            <h1 class="fw-bold" >Bienvenido al Kinventario</h1>
            <p>Aquí podrás pedir prestado equipo deportivo
                directamente del departamento de Kinesiología.
            </p>
            {{--    <p class="fw-bolder text-danger"> *IMPORTANTE: Usted es--}}
            {{--        responsable de entregar el equipo en la misma  condición  en la que se le fue entregado. <br>--}}
            {{--        Al acceder a esta pagina usted acepta los terminos y condiciones, donde--}}
            {{--        se hace totalmente responsable de reemplazar equipo dañado durante--}}
            {{--        el tiempo de prestamo del equipo.--}}

            {{--    </p>--}}

        </div>
        <form method="GET" action="{{ route('kinventory') }}" class="mb-5">
            <div class="row g-3 mb-3 align-items-stretch">
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                <span class="input-group-text bg-white border-0">
                    <i class="bi bi-search"></i>
                </span>

                        <input
                            type="text"
                            name="search"
                            class="form-control border-0"
                            placeholder="Buscar equipo deportivo..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="submit" class="btn btn-success h-100">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <select name="category" class="form-select border-2 border-dark" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('kinventory') }}" class="btn btn-outline-secondary">
                        Limpiar filtros
                    </a>
                </div>
            </div>
        </form>

        {{--        okay this will be some sort of card grid, It will be filled when the cards are actually available--}}
        <div class="row g-4 mt-2">
                @forelse($items as $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                            <img
                                src="{{ $item->equipment_photo_url ? asset('storage/' . $item->equipment_photo_url) : asset('images/kinventory_images/default.jpg') }}"
                                class="card-img-top"
                                alt="{{ $item->description }}"
                                style="height: 300px; object-fit: contain; object-position: center; background-color:#f8f9fa;"
                            >

                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0 fw-bold">
                                        {{ $item->description }}
                                    </h5>

                                    <span class="badge rounded-0"
                                        style="background-color: {{ $item->available_quantity > 0 ? '#6FC21F' : '#dc3545' }}; color:white;">
                                        {{ $item->available_quantity > 0 ? 'Disponible' : 'No disponible' }}
                                    </span>
                                </div>

                                <div class="small mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Cantidad Disponible:</span>
                                        <strong class="text-success">{{ $item->available_quantity }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Ubicación:</span>
                                        <strong>{{ $item->location }}</strong>
                                    </div>
                                </div>

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
                                        Pedir prestado
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <p class="fw-semibold fs-3 text-secondary mb-0">
                                Item no disponible.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if ($items->hasPages())
                <div class="mt-4 d-flex justify-content-center">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            @endif

        </div>


    </div>
    {{--    here container for cards closes--}}
    {{-- Borrow Pop up when someone clicks on a card --}}
    <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="borrowModalLabel">Agregar al carrito</h4>
                        <p class="text-muted mb-0" id="borrowModalText">
                            Selecciona la cantidad que deseas
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form method="POST" action="{{ route('cart.add') }}" id="borrowForm" novalidate>
                    @csrf

                    <div class="modal-body">
                        <input type="hidden" name="equipment_id" id="borrowEquipmentId">

                        <img
                            id="borrowModalImage"
                            src=""
                            alt="Equipo"
                            class="img-fluid rounded-4 mb-3"
                            style="height: 280px; width: 100%; object-fit: contain; object-position: center; background-color:#f8f9fa;"
                        >

                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Cantidad Disponible:</span>
                            <strong class="text-success">
                                <span id="borrowModalStock">0</span> unidades
                            </strong>
                        </div>

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
                            <div class="invalid-feedback" id="borrowQuantityError"></div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success" id="confirmAddToCart">
                            <i class="bi bi-cart-plus me-1"></i> Añadir al carrito
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</x-layout>


    {{-- Ignore this, this was me testing the email service it is for me to reference later--}}
{{--<h2>Send Email</h2>--}}

{{--@if(session('success'))--}}
{{--    <p style="color: green">{{ session('success') }}</p>--}}
{{--@endif--}}

{{--<form method="POST" action="/send-email">--}}
{{--    @csrf--}}

{{--    <label>Email:</label>--}}
{{--    <input type="email" name="email" required>--}}

{{--    <br><br>--}}

{{--    <label>Subject:</label>--}}
{{--    <input type="text" name="subject" required>--}}

{{--    <br><br>--}}

{{--    <br><br>--}}

{{--    <label>Message:</label>--}}
{{--    <textarea name="message" required></textarea>--}}

{{--    <br><br>--}}

{{--    <button type="submit">Send</button>--}}
{{--</form>--}}

