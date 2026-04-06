<x-layout title="Mi Perfil">
    <x-navbar></x-navbar>

    @vite('resources/js/my_profile_validation.js')

    <style>
        .profile-pagination .page-item .page-link {
            border: none;
            color: #198754;
            background-color: #e9ecef;
            min-width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: none;
        }

        .profile-pagination .page-item.active .page-link {
            background-color: #198754;
            color: white;
        }

        .profile-pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.75rem;
            border-bottom-left-radius: 0.75rem;
        }

        .profile-pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }

        .profile-pagination .page-item.disabled .page-link {
            color: #198754;
            background-color: #e9ecef;
            opacity: 0.65;
        }

        .profile-pagination .page-link:focus {
            box-shadow: none;
        }
    </style>

    <div class="container py-4">

        {{-- Profile summary card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                            <h1 class="fw-bold mb-0">Usuario Actual (Aquí dirá el nombre)</h1>
                            <span class="bg-primary-subtle text-primary-emphasis fw-semibold rounded-0 px-2 py-1">
                                Usuario
                            </span>
                        </div>
                        <p class="text-muted fs-4 mb-0">Miembro de MAIKINE</p>

                        @php
                            $userRating = 4.3;
                            $reviewCount = 8;
                        @endphp

                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <span class="text-muted fw-medium">Calificación:</span>

                            <div class="rating-stars" style="--rating: {{ $userRating }};">
                                <div class="rating-stars-base">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>

                                <div class="rating-stars-fill">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>

                            <span class="fw-bold">{{ number_format($userRating, 1) }}</span>
                            <span class="text-muted">({{ $reviewCount }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section tabs --}}
        <ul class="nav w-100 flex-wrap gap-2 mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="btn btn-success rounded-3 px-4 py-2 active"
                    id="posts-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#posts-pane"
                    type="button"
                    role="tab"
                    aria-controls="posts-pane"
                    aria-selected="true"
                >
                    <i class="bi bi-bag me-2"></i> Publicaciones (<span id="postsTabCount">3</span>)
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button
                    class="btn btn-outline-success rounded-3 px-4 py-2"
                    id="requests-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#requests-pane"
                    type="button"
                    role="tab"
                    aria-controls="requests-pane"
                    aria-selected="false"
                >
                    <i class="bi bi-clipboard-check me-2"></i> Solicitudes de Artículos
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- POSTS TAB --}}
            <div class="tab-pane fade show active" id="posts-pane" role="tabpanel" aria-labelledby="posts-tab">

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-1">Mis Publicaciones</h2>
                        <p class="text-muted mb-4">
                            Busca y filtra tus publicaciones por deporte o rango de precio.
                        </p>

                        <form id="postsFilterForm" class="mb-0">
                            <div class="row g-3 align-items-stretch mb-3">
                                <div class="col-lg-10">
                                    <div class="input-group search-group h-100">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="bi bi-search"></i>
                                        </span>

                                        <input
                                            type="text"
                                            id="postSearch"
                                            class="form-control border-0"
                                            placeholder="Buscar publicaciones..."
                                        >
                                    </div>
                                </div>

                                <div class="col-lg-2 d-grid">
                                    <button type="submit" class="btn btn-success h-100 fw-semibold">
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-lg-4">
                                    <select id="sportFilter" class="form-select border-2 border-dark">
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

                                <div class="col-md-6 col-lg-4">
                                    <select id="priceFilter" class="form-select border-2 border-dark">
                                        <option value="">Todos los precios</option>
                                        <option value="0-25">$0 - $25</option>
                                        <option value="26-50">$26 - $50</option>
                                        <option value="51-100">$51 - $100</option>
                                        <option value="101+">$101 o más</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button type="button" id="clearPostsFilters" class="btn btn-outline-secondary">
                                        Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row g-4" id="postsGrid">
                    <div class="col-md-6 col-lg-4 post-card-wrapper"
                         data-title="Baloncesto - Spalding"
                         data-description="Balón de baloncesto tamaño oficial, uso interior/exterior."
                         data-sport="Baloncesto"
                         data-price="25">
                        <div class="card h-100 shadow-sm rounded-4 overflow-hidden border-0">
                            <img
                                src="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                                class="card-img-top"
                                alt="Baloncesto - Spalding"
                                style="height: 300px; object-fit: cover;"
                            >

                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">Baloncesto - Spalding</h5>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Disponible
                                    </span>
                                </div>

                                <p class="text-muted mb-3">
                                    Balón de baloncesto tamaño oficial, uso interior/exterior.
                                </p>

                                <h3 class="fw-bold text-success mb-3">$25</h3>

                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Muy Bueno
                                    </span>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Baloncesto
                                    </span>
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-person me-2"></i> John Davis</div>
                                    <div><i class="bi bi-star-fill text-warning me-2"></i> 4.3 (8)</div>
                                    <div><i class="bi bi-clock me-2"></i> hace 2 días</div>
                                </div>

                                <div class="mt-auto d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-danger rounded-3 open-delete-post-modal"
                                        data-post-title="Baloncesto - Spalding"
                                    >
                                        Borrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 post-card-wrapper"
                         data-title="Raqueta Wilson Pro"
                         data-description="Raqueta liviana ideal para entrenamiento y partidos recreativos."
                         data-sport="Tenis"
                         data-price="45">
                        <div class="card h-100 shadow-sm rounded-4 overflow-hidden border-0">
                            <img
                                src="{{ asset('images/kinventory_images/default.jpg') }}"
                                class="card-img-top"
                                alt="Raqueta Wilson Pro"
                                style="height: 300px; object-fit: cover;"
                            >

                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">Raqueta Wilson Pro</h5>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Disponible
                                    </span>
                                </div>

                                <p class="text-muted mb-3">
                                    Raqueta liviana ideal para entrenamiento y partidos recreativos.
                                </p>

                                <h3 class="fw-bold text-success mb-3">$45</h3>

                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Bueno
                                    </span>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Tenis
                                    </span>
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-person me-2"></i> John Davis</div>
                                    <div><i class="bi bi-star-fill text-warning me-2"></i> 4.3 (8)</div>
                                    <div><i class="bi bi-clock me-2"></i> hace 4 días</div>
                                </div>

                                <div class="mt-auto d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-danger rounded-3 open-delete-post-modal"
                                        data-post-title="Raqueta Wilson Pro"
                                    >
                                        Borrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 post-card-wrapper"
                         data-title="Mancuernas Ajustables"
                         data-description="Set ajustable para rutinas de fuerza y levantamiento."
                         data-sport="Levantamiento de Pesas"
                         data-price="120">
                        <div class="card h-100 shadow-sm rounded-4 overflow-hidden border-0">
                            <img
                                src="{{ asset('images/kinventory_images/default.jpg') }}"
                                class="card-img-top"
                                alt="Mancuernas Ajustables"
                                style="height: 300px; object-fit: cover;"
                            >

                            <div class="card-body d-flex flex-column p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">Mancuernas Ajustables</h5>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Disponible
                                    </span>
                                </div>

                                <p class="text-muted mb-3">
                                    Set ajustable para rutinas de fuerza y levantamiento.
                                </p>

                                <h3 class="fw-bold text-success mb-3">$120</h3>

                                <div class="d-flex gap-2 mb-3 flex-wrap">
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Excelente
                                    </span>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Levantamiento de Pesas
                                    </span>
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-person me-2"></i> John Davis</div>
                                    <div><i class="bi bi-star-fill text-warning me-2"></i> 4.3 (8)</div>
                                    <div><i class="bi bi-clock me-2"></i> hace 1 semana</div>
                                </div>

                                <div class="mt-auto d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-danger rounded-3 open-delete-post-modal"
                                        data-post-title="Mancuernas Ajustables"
                                    >
                                        Borrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="postsEmptyState" class="col-12 d-none">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body py-5 text-center">
                                <i class="bi bi-search fs-1 text-muted"></i>
                                <h4 class="fw-bold mt-3">No se encontraron publicaciones</h4>
                                <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro artículo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Paginación de publicaciones">
                        <ul class="pagination profile-pagination mb-0" id="postsPagination"></ul>
                    </nav>
                </div>
            </div>

            {{-- REQUESTS TAB --}}
            <div class="tab-pane fade" id="requests-pane" role="tabpanel" aria-labelledby="requests-tab">

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-1">Solicitudes de Artículos</h2>
                        <p class="text-muted mb-4">
                            Busca tus solicitudes y filtra por estado.
                        </p>

                        <form id="requestsFilterForm" class="mb-0">
                            <div class="row g-3 align-items-stretch mb-3">
                                <div class="col-lg-10">
                                    <div class="input-group search-group h-100">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="bi bi-search"></i>
                                        </span>

                                        <input
                                            type="text"
                                            id="requestSearch"
                                            class="form-control border-0"
                                            placeholder="Buscar solicitudes..."
                                        >
                                    </div>
                                </div>

                                <div class="col-lg-2 d-grid">
                                    <button type="submit" class="btn btn-success h-100 fw-semibold">
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-lg-4">
                                    <select id="statusFilter" class="form-select border-2 border-dark">
                                        <option value="">Todos los estados</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Aprobada">Aprobada</option>
                                        <option value="Rechazada">Rechazada</option>
                                        <option value="Finalizado">Finalizado</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button type="button" id="clearRequestsFilters" class="btn btn-outline-secondary">
                                        Limpiar filtros
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="requestsList" class="d-grid gap-3">
                    <div class="border rounded-4 p-4 request-card"
                         data-title="Nike Zapatos de Baloncesto"
                         data-status="Pendiente">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1">Nike Zapatos de Baloncesto</h5>
                                <p class="text-muted mb-0">Solicitado: 03/10/2026</p>
                            </div>
                            <span class="badge bg-warning text-dark rounded-0 px-3 py-2">Pendiente</span>
                        </div>
                    </div>

                    <div class="border rounded-4 p-4 request-card"
                         data-title="Cuica para saltar"
                         data-status="Aprobada">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1">Cuica para saltar</h5>
                                <p class="text-muted mb-0">Solicitado: 02/25/2026</p>
                            </div>
                            <span class="badge bg-success rounded-0 px-3 py-2">Aprobada</span>
                        </div>
                    </div>

                    <div class="border rounded-4 p-4 request-card"
                         data-title="Camisa deportiva"
                         data-status="Rechazada">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1">Camisa deportiva</h5>
                                <p class="text-muted mb-0">Solicitado: 02/20/2026</p>
                            </div>
                            <span class="badge bg-danger rounded-0 px-3 py-2">Rechazada</span>
                        </div>
                    </div>

                    <div class="border rounded-4 p-4 request-card"
                         data-title="Mancuernas Ajustables"
                         data-status="Finalizado">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1">Mancuernas Ajustables</h5>
                                <p class="text-muted mb-0">Solicitado: 01/15/2026</p>
                                <p class="text-muted mb-0">Devuelto: 01/22/2026</p>
                            </div>
                            <span class="badge rounded-0 px-3 py-2"
                                  style="background-color:#e5e7eb; color:#374151;">
                                Finalizado
                            </span>
                        </div>
                    </div>

                    <div id="requestsEmptyState" class="d-none">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body py-5 text-center">
                                <i class="bi bi-clipboard-x fs-1 text-muted"></i>
                                <h4 class="fw-bold mt-3">No se encontraron solicitudes</h4>
                                <p class="text-muted mb-0">Intenta cambiar el filtro o buscar otro artículo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    <nav aria-label="Paginación de solicitudes">
                        <ul class="pagination profile-pagination mb-0" id="requestsPagination"></ul>
                    </nav>
                </div>
            </div>

        </div>
    </div>

    {{-- Delete confirmation modal --}}
    <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="deletePostModalLabel">¿Seguro que quieres borrar?</h4>
                        <p class="text-muted mb-0" id="deletePostModalText">
                            Esta publicación será eliminada de la vista.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="alert alert-warning rounded-4 mb-0">
                        Esta acción no se puede deshacer en esta vista.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeletePost">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="deletePostToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item borrado correctamente.
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
