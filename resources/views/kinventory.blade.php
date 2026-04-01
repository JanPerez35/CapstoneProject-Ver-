<x-layout title="Kinventario">
<x-navbar>
    </x-navbar>

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
        <div class="row mb-4 g-3">
            <div class="col-md-8">
                <div class="input-group search-group">
        <span class="input-group-text bg-white border-0">
            <i class="bi bi-search"></i>
        </span>

                    <input
                        type="text"
                        class="form-control border-0"
                        placeholder="Buscar equipo deportivo..."
                    >
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select border-2 border-dark">
                    <option>Todos los Deportes</option>
                    <option>Baloncesto</option>
                    <option>Tenis</option>
                    <option>Fútbol</option>
                    <option>Deporte Recreativo</option>
                    <option>Volibol</option>
                    <option>Levantamiento de Pesas</option>
                    <option>Otros</option>
                </select>
            </div>
        </div>

{{--        okay this will be some sort of card grid, It will be filled when the cards are actually available--}}
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                    <img
                        src="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                        class="card-img-top"
                        alt="Baloncesto"
                        style="height: 300px; object-fit: cover; object-position: center;"
                    >
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold">Balon de Baloncesto</h5>
                            <span class="badge rounded-0 " style="background-color:#6FC21F; color:white;">Disponible</span>
                        </div>

                        <p class="text-muted small mb-3">
                            Bola de baloncesto de tamaño oficial para uso interior/exterior.
                        </p>

                        <div class="small mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Cantidad Disponible:</span>
                                <strong class="text-success">18</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ubicación:</span>
                                <strong>Almacen A</strong>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <button
                                class="btn btn-success open-borrow-modal"
                                data-item-name="Balón de Baloncesto"
                                data-item-stock="18"
                                data-item-image="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                                data-item-location="Almacen A"
                                data-bs-toggle="modal"
                                data-bs-target="#borrowModal"
                            >
                                Pedir prestado
                            </button>

                        </div>
                    </div>
                </div>
            </div>


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

                <div class="modal-body">
                    <img
                        id="borrowModalImage"
                        src="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                        alt="Equipo"
                        class="img-fluid rounded-4 mb-3"
                        style="height: 280px; width: 100%; object-fit: cover; object-position: center;"
                    >

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Cantidad Disponible:</span>
                        <strong class="text-success">
                            <span id="borrowModalStock">18</span> unidades
                        </strong>
                    </div>

                    <div class="mb-3">
                        <label for="borrowQuantity" class="form-label fw-semibold">Cantidad</label>
                        <input
                            type="number"
                            id="borrowQuantity"
                            class="form-control form-control-lg"
                            min="1"
                            max="18"
                            value="1"
                        >
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmAddToCart">
                        <i class="bi bi-cart-plus me-1"></i> Agregar

                    </button>
                </div>
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

