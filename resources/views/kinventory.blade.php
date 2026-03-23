<x-layout>
    <x-navbar>
    </x-navbar>

    <div class= "container py-4">

<div class="mb-4">
    <h1 class="fw-bold" >Bienvenido al Kinventario</h1>
    <p> Aqui podras pedir prestado equipo deportivo
    directamente del departamento de Kinesiologia.
    </p>

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
                    <option>Todas las categorías</option>
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
                    <img src="{{ asset('images/baloncesto.jpg') }}" class="card-img-top" alt="Baloncesto" style="height: 220px; object-fit: cover;">

                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold">Baloncesto</h5>
                            <span class="badge bg-success">Disponible</span>
                        </div>

                        <p class="text-muted small mb-3">
                            Bola de baloncesto de tamaño oficial para uso interior/exterior.
                        </p>

                        <div class="small mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Disponibles:</span>
                                <strong class="text-success">18</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Ubicación:</span>
                                <strong>Sala A</strong>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <button class="btn btn-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#borrowModal">
                                Pedir prestado
                            </button>
                        </div>
                    </div>
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

