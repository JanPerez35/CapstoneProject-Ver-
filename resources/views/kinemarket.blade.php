<x-layout>
    <x-navbar>
    </x-navbar>

    <div class= "container py-4">

        <div class="mb-4">
            <h1 class="fw-bold" >Bienvenido al Kinemercado</h1>
            <p> Aqui podras buscar equipamiento deportivo y contactar con posibles vendedores.
            </p>
        </div>

        <!--Post creation button-->
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
                                <span class="text-danger">*</span> Campos requeridos
                            </small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <!--Modal Body-->
                    <div class="modal-body pt-3">
                        <form id="createPostForm" novalidate>
                            @csrf
                            <div class="mb-3">
                                <label for="postTitle" class="form-label fw-semibold">
                                    Título<span class="text-danger">*</span>
                                </label>
                                <input
                                type="text"
                                class="form-control form-control-lg"
                                id="postTitle"
                                placeholder="ej. Baloncesto - Spalding"
                                minlength="5"
                                maxlength="100"
                                required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Entre 5 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                                </small>
                                <div class="invalid-feedback" id="postTitleError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="postDescription" class="form-label fw-semibold">
                                    Description (Opcional)
                                </label>
                                <textarea
                                class="form-control form-control-lg"
                                id="postDescription"
                                rows="4"
                                placeholder="Describe el estado y detalles"
                                minlength="10"
                                maxlength="1000"
                                ></textarea>
                                <small class="text-muted d-block fst-italic">
                                    Si escribes una descripción, debe tener entre 10 y 1000 caracteres.
                                    Solo letras, números espacios, punto, coma y guion.
                                </small>
                                <div class="invalid-feedback" id="postDescriptionError"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postPrice" class="form-label fw-semibold">
                                        Precio ($)<span class="text-danger">*</span>
                                    </label>
                                    <input
                                    type="text"
                                    inputmode="decimal"
                                    class="form-control form-control-lg"
                                    id="postPrice"
                                    placeholder="0.00"
                                    required>
                                    <small class="text-muted d-block fst-italic">
                                        Solo números decimales validos.
                                    </small>
                                    <div class="invalid-feedback" id="postPriceError"></div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="postCategory" class="form-label fw-semibold">
                                        Categoría<span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="postCategory" required>
                                        <option value="" selected disabled>Seleccionar</option>
                                        <option>Baloncesto</option>
                                        <option>Tenis</option>
                                        <option>Fútbol</option>
                                        <option>Deporte Recreativo</option>
                                        <option>Volibol</option>
                                        <option>Levantamiento de Pesas</option>
                                        <option>Otro</option>
                                    </select>
                                    <div class="invalid-feedback">Selecciona una categoría.</div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="postCondition" class="form-label fw-semibold">
                                    Condición<span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="postCondition" required>
                                    <option value="" selected disabled>Seleccionar</option>
                                    <option>Nuevo</option>
                                    <option>Como Nuevo</option>
                                    <option>Buen Estado</option>
                                    <option>Justo</option>
                                </select>
                                <div class="invalid-feedback">Seleciona una condición.</div>
                            </div>

                            <div class="mb-2">
                                <label for="postImage" class="form-label fw-semibold">
                                    Fotos<span class="text-danger">*</span>
                                </label>
                                <input
                                type="file"
                                class="d-none"
                                id="postImage"
                                accept=".jpg,.jpeg,image/jpeg"
                                multiple
                                required>
                            </div>
                            <label for="postImage" class="form-control form-control-lg text-center py-3" style="cursor:pointer;">
                                <i class="bi bi-upload me-2"></i>
                                Subir imágenes
                            </label>

                            <small class="text-muted d-block fst-italic mt-2">
                                Mínimo 1 imagen y máximo 3 imágenes permitidas.
                                Solo JPEG/JPG.
                                Máximo 2MB por imagen.
                            </small>
                            <div id="imageError" class="invalid-feedback d-block d-none"></div>
                            <div id="imagePreviewContainer" class="row g-3 mt-1"></div>
                        </form>
                    </div>
                   <!--Modal Footer-->
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary px-4" id="cancelCreatePostBtn">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success px-4" id="publishBtn" disabled >
                            Publicar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-label="cancelConfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0">
                     <h5 class="modal-title fw-bold" id="cancelConfirmLabel">
                        ¿Seguro que deseas cancelar?
                     </h5>
                    </div>

                    <div class="modal-body">
                        Se perderán los datos e imágenes que hayas escrito en este formularion.
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Seguir editando
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmCancelCreatePost">
                            Sí, cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <!--Post template-->
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
                        style="height: 400px; object-fit: cover; object-position: center;"
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
                            <span class="badge  border rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">Muy Bueno</span>
                            <span class="badge px-3 py-2 rounded-0" style="background-color:#6FC21F; color:white;">
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
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <div class="modal-content rounded-4 border-0 shadow">

                    <div class="modal-header border-0 pb-0 align-items-start">
                        <div class="pe-4">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Baloncesto - Spalding</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>
                        <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body pt-3 post-details-body">
                        <div id="postImagesCarousel" class="carousel slide mb-4">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#postImagesCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            </div>

                            <div class="carousel-inner rounded-4 overflow-hidden post-carousel-inner">
                                <div class="carousel-item active post-carousel-item">
                                    <div class="carousel-image-box">
                                    <img
                                        src="{{ asset('images/marketplace_images/Baloncesto.jpg') }}"
                                        alt="Imagen 1"
                                        class="post-carousel-img"
                                    >
                                    </div>
                                </div>

                                <div class="carousel-item">
                                    <div class="carousel-image-box">
                                    <img
                                        src="{{ asset('images/marketplace_images/ball2.jpg') }}"
                                        alt="Imagen 2"
                                        class="post-carousel-img"
                                    >
                                    </div>
                                </div>

                                <div class="carousel-item">
                                    <div class="carousel-image-box">
                                    <img
                                        src="{{ asset('images/marketplace_images/ball3.jpg') }}"
                                        alt="Imagen 3"
                                        class="post-carousel-img"
                                    >
                                    </div>
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
                            <div class="col-6 text-end">
                              <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                Muy Bueno
                              </span>
                            </div>

                            <div class="col-6 text-muted">Vendedor:</div>
                            <div class="col-6 text-end fw-bold">John Davis</div>

                            <div class="col-6 text-muted">Calificación del Vendedor:</div>
                            <div class="col-6 text-end">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                4.5 <span class="text-muted">(12 reseñas)</span>
                            </div>

                            <div class="col-6 text-muted">Categoría:</div>
                            <div class="col-6 text-end">
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            Baloncesto
                        </span>
                            </div>
                        </div>

                        <hr>

                        <div class="mt-4">
                            <h5 class="fw-bold mb-3">Calificar Esta Publicación</h5>

                            <label class="form-label fw-semibold">Tu Calificación</label>

                            <div class="mb-2 rating-stars text-warning fs-3" id="sellerRatingStars">
                                <i class="bi bi-star rating-star" data-value="1" role="button" tabindex="0" aria-label="1 estrella"></i>
                                <i class="bi bi-star rating-star" data-value="2" role="button" tabindex="0" aria-label="2 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="3" role="button" tabindex="0" aria-label="3 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="4" role="button" tabindex="0" aria-label="4 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="5" role="button" tabindex="0" aria-label="5 estrellas"></i>
                            </div>

                            <input type="hidden" id="sellerRatingValue" value="0">
                            <p class="text-muted small mb-3" id="sellerRatingText">Selecciona una calificación</p>

                            <label for="postComment" class="form-label fw-semibold">Comentario (Opcional)</label>
                            <textarea
                                id="postComment"
                                class="form-control form-control-lg"
                                rows="3"
                                placeholder="Comparte detalles sobre tu experiencia..."
                            ></textarea>

                            <div class="d-grid mt-3">
                                <button type="button" class="btn btn-lg rounded-3 btn-outline-success">
                                    Enviar Calificación
                                </button>
                            </div>
                        </div>

                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <button
                                type="button"
                                class="btn btn-outline-secondary w-100 rounded-3 report-btn"
                                data-bs-dismiss="modal"
                                data-bs-toggle="modal"
                                data-bs-target="#reportUserModal">

                                    <i class="bi bi-flag me-2"></i> Reportar Usuario
                                </button>
                            </div>

                            <div class="col-6">
                                <a href="{{ url('/my_messages') }}" class="btn btn-outline-success w-100 rounded-3">
                                    <i class="bi bi-chat me-2"></i> Enviar Mensaje
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">

                    <div class="modal-header border-0 pb-0">
                        <div>
                            <h4 class="modal-title fw-bold mb-2" id="reportUserModalLabel">
                                <i class="bi bi-exclamation-triangle text-danger me-2"></i>
                                Reportar Usuario
                            </h4>
                            <p class="text-muted mb-0">
                                Reportar a John Davis por comportamiento sospechoso
                            </p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body pt-3">
                        <form>
                            <div class="mb-3">
                                <label for="reportReason" class="form-label fw-semibold">
                                    Razón <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="reportReason" required>
                                    <option value="" selected disabled>Seleccionar una razón</option>
                                    <option>Fraude o estafa</option>
                                    <option>Información falsa</option>
                                    <option>Lenguaje ofensivo</option>
                                    <option>Contenido inapropiado</option>
                                    <option>Otro</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="reportDescription" class="form-label fw-semibold">
                                    Descripción <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    id="reportDescription"
                                    class="form-control form-control-lg"
                                    rows="4"
                                    placeholder="Proporciona detalles sobre el comportamiento sospechoso..."
                                    maxlength="500"
                                    required
                                ></textarea>
                            </div>

                            <div class="alert alert-warning rounded-4 mb-0">
                                <strong>Nota:</strong> Los reportes son revisados por administradores del mercado.
                                Los reportes falsos pueden resultar en restricciones de cuenta.
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger px-4">
                            Enviar Reporte
                        </button>
                    </div>

                </div>
            </div>
        </div>
   </div>
</x-layout>
