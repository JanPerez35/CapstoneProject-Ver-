<x-layout title="Mi Perfil">

    <div class="container py-4">

        {{-- Back button --}}
        <div class="mb-4">
            <a href="{{ route('kinemercado') }}" class="btn btn-outline-secondary rounded-3 px-4">
                <i class="bi bi-arrow-left me-2"></i> Volver al Kinemercado
            </a>
        </div>

        {{-- Profile summary card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                            <h1 class="fw-bold mb-0">Usuario Actual (Aquí dirá el nombre)</h1>
                            <span class="badge bg-success-subtle text-success border rounded-0 px-3 py-2">Tu Perfil</span>
                            <span class="badge bg-primary rounded-0 px-3 py-2">Usuario</span>
                        </div>

                        <p class="text-muted fs-4 mb-0">Miembro de MAIKINE</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section tabs --}}
        <ul class="nav nav-pills gap-3 mb-4 flex-wrap" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="btn btn-light rounded-pill px-4 py-2 active" id="posts-tab" data-bs-toggle="tab" data-bs-target="#posts-pane" type="button" role="tab">
                    <i class="bi bi-bag me-2"></i> Publicaciones (3)
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="btn btn-light rounded-pill px-4 py-2" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-pane" type="button" role="tab">
                    <i class="bi bi-star me-2"></i> Reseñas (4)
                </button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="btn btn-light rounded-pill px-4 py-2" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests-pane" type="button" role="tab">
                    <i class="bi bi-clipboard-check me-2"></i> Solicitudes de Artículos
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Posts tab --}}
            <div class="tab-pane fade show active" id="posts-pane" role="tabpanel" aria-labelledby="posts-tab">

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4 post-card-wrapper">
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

                                <div class="d-flex gap-2 mb-3">
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
                                        class="btn btn-outline-danger rounded-3 open-delete-post-modal"
                                        data-post-title="Baloncesto - Spalding"
                                    >
                                        Borrar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Reviews tab --}}
            <div class="tab-pane fade" id="reviews-pane" role="tabpanel" aria-labelledby="reviews-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4">Reseñas recibidas</h2>

                        <div class="border rounded-4 p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0">María López</h5>
                                <span class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            </div>
                            <p class="text-muted mb-2">“Muy responsable y amable. La entrega fue rápida.”</p>
                            <small class="text-muted">Hace 3 días</small>
                        </div>

                        <div class="border rounded-4 p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0">Carlos Rivera</h5>
                                <span class="text-warning">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-half"></i>
                                </span>
                            </div>
                            <p class="text-muted mb-2">“Producto tal y como se describía en la publicación.”</p>
                            <small class="text-muted">Hace 1 semana</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Requests tab --}}
            <div class="tab-pane fade" id="requests-pane" role="tabpanel" aria-labelledby="requests-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4">Solicitudes de Artículos</h2>

                        <div class="border rounded-4 p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1">Nike Zapatos de Baloncesto</h5>
                                    <p class="text-muted mb-0">Solicitado por: Ana Pérez</p>
                                </div>
                                <span class="badge bg-warning text-dark rounded-0 px-3 py-2">Pendiente</span>
                            </div>
                        </div>

                        <div class="border rounded-4 p-4 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1">Cuica para saltar</h5>
                                    <p class="text-muted mb-0">Solicitado por: José Morales</p>
                                </div>
                                <span class="badge bg-success rounded-0 px-3 py-2">Aprobada</span>
                            </div>
                        </div>

                        <div class="border rounded-4 p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="fw-bold mb-1">Camisa deportiva</h5>
                                    <p class="text-muted mb-0">Solicitado por: Laura Sánchez</p>
                                </div>
                                <span class="badge bg-danger rounded-0 px-3 py-2">Rechazada</span>
                            </div>
                        </div>
                    </div>
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

</x-layout>
