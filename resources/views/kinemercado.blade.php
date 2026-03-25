<x-layout>
    <x-navbar>
    </x-navbar>

    <div class= "container py-4">

        <div class="mb-4">
            <h1 class="fw-bold" >Bienvenido al Kinemercado</h1>
            <p> Aqui podras buscar equipamiento deportivo y contactar con posibles vendedores.
            </p>
        </div>

    <div class="mb-3">
        <button
            type="button"
            class="btn btn-success d-flex align-items-center gap-2 open-create-post"
            data-bs-toggle="modal"
            data-bs-target="#createPostModal"
        >

            <i class="bi bi-plus-lg"></i>
            Crear Publicación
        </button>

        <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow">
                   <!--Modal Header-->
                    <div class="modal-header border-0 pb-0 align-items-start">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-2" id="createPostLabel">Crear Nueva Publicación</h4>
                            <p class="text-muted mb-1">
                                Publica tu equipo deportivo a la venta para otros usuarios
                            </p>
                            <small class="text-muted">
                                Los campos marcados con <span class="text-danger">*</span> son obligatorios.
                            </small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <!--Modal Body-->
                    <div class="modal-body pt-3">
                        <form>
                            @csrf
                            <div class="mb-3">
                                <label for="posTitle" class="form-label fw-semibold">Título<span class="text-danger">*</span></label>
                                <input
                                type="text"
                                class="form-control form-control-lg"
                                id="postTitle"
                                placeholder="ej. Baloncesto - Spalding"
                                >
                            </div>

                            <div class="mb-3">
                                <label for="postDescription" class="form-label fw-semibold">Description</label>
                                <textarea
                                class="form-control form-control-lg"
                                id="postDescription"
                                rows="4"
                                placeholder="Describe el estado y detalles"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postPrice" class="form-label fw-semibold">Precio ($)<span class="text-danger">*</span></label>
                                    <input
                                    type="number"
                                    class="form-control form-control-lg"
                                    id="postPrice"
                                    placeholder="0.00"
                                    step="0.01"
                                    min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="postCategory" class="form-label fw-semibold">Categoría<span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg" id="postCategory">
                                        <option selected disabled>Seleccionar</option>
                                        <option>Baloncesto</option>
                                        <option>Tenis</option>
                                        <option>Fútbol</option>
                                        <option>Deporte Recreativo</option>
                                        <option>Volibol</option>
                                        <option>Levantamiento de Pesas</option>
                                        <option>Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="postCondition" class="form-label fw-semibold">Condición<span class="text-danger">*</span></label>
                                <select class="form-select form-select-lg" id="postCondition">
                                    <option selected disabled>Seleccionar</option>
                                    <option>Nuevo</option>
                                    <option>Como Nuevo</option>
                                    <option>Buen Estado</option>
                                    <option>Justo</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label for="postImage" class="form-label fw-semibold">Fotos<span class="text-danger">*</span></label>
                                <input
                                type="file"
                                class="d-none"
                                id="postImage"
                                accept="image/jpeg"
                                multiple>
                            </div>
                            <label for="postImage" class="form-control form-control-lg text-center py-3" style="cursor:pointer;">
                                <i class="bi bi-upload me-2"></i>
                                Subir imágenes
                            </label>
                            <small class="text-muted d-block" id="photoCounter">
                                Máximo 3 fotos permitidos (0/3)
                            </small>
                        </form>
                    </div>
                   <!--Modal Footer-->
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success px-4" id="publishBtn" disabled>
                            Publicar
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>
        <div class="row mb-4 g-3">
            <div class="col-md-6">
                <div class="input-group search-group">
        <span class="input-group-text bg-white border-0">
            <i class="bi bi-search"></i>
        </span>

                    <input
                        type="text"
                        class="form-control border-0"
                        placeholder="Buscar publicaciones..."
                    >
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select border-2 border-dark ">
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
            <div class="col-md-3">
                <select class="form-select border-2 border-dark">
                    <option>Todas los Estados</option>
                    <option>Solo Disponible</option>
                    <option>Solo Vendidos</option>
                </select>
            </div>
        </div>

        <!--Card grid style-->

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm rounded-4 overflow-hidden item-card border-0">
                    <img
                        src="{{ asset('images/marketplace_images/Baloncesto.jpg') }}"
                        class="card-img-top"
                        alt="Baloncesto - Spalding"
                        style="height: 300px; object-fit: cover; object-position: center;"
                    >

                    <div class="card-body d-flex flex-column p-4">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0 fw-bold">Baloncesto - Spalding</h5>
                            <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                        Disponible
                    </span>
                        </div>

                        <p class="text-muted mb-3">
                            Balón de baloncesto tamaño oficial, uso interior/exterior. Excelente agarre y rebote.
                        </p>

                        <h3 class="fw-bold text-success mb-3">$25</h3>

                        <div class="d-flex gap-2 mb-3 flex-wrap">
                            <span class="badge text-dark border rounded-0 px-3 py-2">Muy Bueno</span>
                            <span class="badge text-dark px-3 py-2 rounded-0" style="background-color:#E8F5E9;">
                        Baloncesto
                    </span>
                        </div>

                        <div class="small text-muted mb-3">
                            <div class="mb-2">
                                <i class="bi bi-person me-2"></i> John Davis
                            </div>
                            <div class="mb-2">
                                <i class="bi bi-star-fill text-warning me-2"></i> 4.3 (8 calificaciones)
                            </div>
                            <div>
                                <i class="bi bi-clock me-2"></i> hace 2 días
                            </div>
                        </div>

                        <div class="mt-auto d-grid">
                            <button
                                type="button"
                                class="btn btn-outline-secondary rounded-3"
                                data-bs-toggle="modal"
                                data-bs-target="#postDetailsModal"
                            >
                                Ver Detalles
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">

                    <div class="modal-header border-0 pb-0 align-items-start">
                        <div class="pe-4">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Baloncesto - Spalding</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <div id="postImagesCarousel" class="carousel slide mb-4">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            </div>

                            <div class="carousel-inner rounded-4 overflow-hidden">
                                <div class="carousel-item active">
                                    <img
                                        src="{{ asset('images/marketplace_images/Baloncesto.jpg') }}"
                                        class="d-block w-100"
                                        alt="Imagen 1"
                                        style="height: 320px; object-fit: cover; object-position: center;"
                                    >
                                </div>

                                <div class="carousel-item">
                                    <img
                                        src="{{ asset('images/marketplace_images/ball2.jpg') }}"
                                        class="d-block w-100"
                                        alt="Imagen 2"
                                        style="height: 320px; object-fit: cover; object-position: center;"
                                    >
                                </div>

                                <div class="carousel-item">
                                    <img
                                        src="{{ asset('images/marketplace_images/ball3.jpg') }}"
                                        class="d-block w-100"
                                        alt="Imagen 3"
                                        style="height: 320px; object-fit: cover; object-position: center;"
                                    >
                                </div>
                            </div>

                            <button class="carousel-control-prev" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>

                            <button class="carousel-control-next" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>

                        <p class="mb-3 text-muted">
                            Balón de baloncesto tamaño oficial, uso interior/exterior. Excelente agarre y rebote.
                        </p>

                        <div class="mb-3">
                            <span class="text-muted">Calificación de Publicación:</span>
                            <span class="ms-2 text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                    </span>
                            <strong class="ms-2">4.3</strong>
                            <span class="text-muted">(8)</span>
                        </div>

                        <hr>

                        <div class="row gy-3">
                            <div class="col-6 text-muted">Precio:</div>
                            <div class="col-6 text-end fw-bold text-success">$25</div>

                            <div class="col-6 text-muted">Estado:</div>
                            <div class="col-6 text-end">
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            Disponible
                        </span>
                            </div>

                            <div class="col-6 text-muted">Condición:</div>
                            <div class="col-6 text-end">Muy Bueno</div>

                            <div class="col-6 text-muted">Vendedor:</div>
                            <div class="col-6 text-end fw-bold">John Davis</div>

                            <div class="col-6 text-muted">Calificación del Vendedor:</div>
                            <div class="col-6 text-end">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                4.5 <span class="text-muted">(12 reseñas)</span>
                            </div>

                            <div class="col-6 text-muted">Categoría:</div>
                            <div class="col-6 text-end">
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#E8F5E9; color:#212529;">
                            Baloncesto
                        </span>
                            </div>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <h5 class="fw-bold mb-3">Calificar Esta Publicación</h5>

                            <label class="form-label fw-semibold">Tu Calificación</label>
                            <div class="mb-2 fs-4 text-warning">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                                <i class="bi bi-star"></i>
                            </div>
                            <p class="text-muted small mb-3">Malo</p>

                            <label for="postComment" class="form-label fw-semibold">Comentario (Opcional)</label>
                            <textarea
                                id="postComment"
                                class="form-control form-control-lg"
                                rows="3"
                                placeholder="Comparte detalles sobre tu experiencia..."
                            ></textarea>

                            <div class="d-grid mt-3">
                                <button type="button" class="btn btn-success btn-lg rounded-3">
                                    Enviar Calificación
                                </button>
                            </div>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <a href="{{ url('/kinemercado/reportar_usuario') }}" class="btn btn-outline-secondary w-100 rounded-3">
                                    <i class="bi bi-flag me-2"></i> Reportar Usuario
                                </a>
                            </div>

                            <div class="col-6">
                                <a href="{{ url('/kinemercado/mensaje') }}" class="btn btn-success w-100 rounded-3">
                                    <i class="bi bi-chat me-2"></i> Enviar Mensaje
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   </div>
</x-layout>
