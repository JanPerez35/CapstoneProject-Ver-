<x-layout title="Kinemercado">
    <x-navbar></x-navbar>

    @vite('resources/js/pages/marketplace-profanity.js')
    <div
        id="marketplaceHome"
        class="container py-4"
        data-store-mode="frontend"
        data-create-url="{{ '/marketplace' }}"
        data-delete-url-base="{{ url('/marketplace') }}"
        data-details-url-base="{{ url('/marketplace') }}"
    >
        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Kinemercado</h1>
            <p>
                Aqui podras buscar equipamiento deportivo y contactar con posibles vendedores.
            </p>
        </div>


        <!-- Post creation button -->
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
        </div>


        <!-- Create Post Modal -->
        <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    <div class="modal-header border-0 pb-0 pt-4 px-4 pb-2 align-items-start">
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


                    <div class="modal-body px-4 pt-2 pb-4">
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
                                <div class="invalid-feedback d-block" id="postTitleProfanityError"></div>
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
                                    maxlength="500"
                                ></textarea>
                                <small class="text-muted d-block fst-italic">
                                    Si escribes una descripción, debe tener como máximo 500 caracteres.
                                    Solo letras, números espacios, punto, coma y guion.
                                </small>
                                <div class="invalid-feedback" id="postDescriptionError"></div>
                                <div class="invalid-feedback d-block" id="postDescriptionProfanityError"></div>
                            </div>


                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postPrice" class="form-label fw-semibold">
                                        Precio ($)<span class="text-danger">*</span>
                                    </label>


                                    <div class="input-group input-group-lg" id="postPriceGroup">
                                        <span class="input-group-text">$</span>
                                        <input
                                            type="text"
                                            inputmode="decimal"
                                            class="form-control"
                                            id="postPrice"
                                            placeholder="Ej. 25.00"
                                            required
                                        >
                                    </div>


                                    <small class="text-muted d-block fst-italic">
                                        Escribe solo números y hasta 2 decimales.
                                    </small>
                                    <div class="invalid-feedback d-block" id="postPriceError"></div>
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
                                    required
                                >
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


                    <div class="modal-footer border-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-outline-secondary px-4" id="cancelCreatePostBtn">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-success px-4" id="publishBtn" disabled>
                            Publicar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Cancel Create Post Modal -->
        <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-label="cancelConfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="cancelConfirmLabel">
                            ¿Seguro que deseas cancelar?
                        </h5>
                    </div>


                    <div class="modal-body">
                        Se perderán los datos e imágenes que hayas escrito en este formulario.
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


        <!-- Search and Filter -->
        <div class="row mb-4 g-3">
            <div class="col-md-12">
                <div class="input-group search-group">
                   <span class="input-group-text bg-white border-0">
                       <i class="bi bi-search"></i>
                   </span>


                    <input
                        type="text"
                        class="form-control border-0"
                        id="marketplaceSearch"
                        placeholder="Buscar publicaciones..."
                    >
                </div>
            </div>


            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceCategoryFilter">
                    <option value="all">Todos los Deportes</option>
                    <option value="Baloncesto">Baloncesto</option>
                    <option value="Tenis">Tenis</option>
                    <option value="Fútbol">Fútbol</option>
                    <option value="Deporte Recreativo">Deporte Recreativo</option>
                    <option value="Volibol">Volibol</option>
                    <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>


            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceRatingFilter">
                    <option value="all">Calificaciones</option>
                    <option value="0">0 estrellas</option>
                    <option value="1">Entre 0 a 1 estrella</option>
                    <option value="2">Entre 1 a  2 estrellas</option>
                    <option value="3">Entre 2 a 3 estrellas</option>
                    <option value="4">Entre 3 a 4 estrellas</option>
                    <option value="5">Entre 4 a 5 estrellas</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplacePriceFilter">
                    <option value="all">Rango de Precios</option>
                    <option value="0">Gratis</option>
                    <option value="0.01-9.99">$0.01 - $9.99</option>
                    <option value="10-29.99">$10.00 - $29.99</option>
                    <option value="30-49.99">$30.00 - $49.99</option>
                    <option value="50+">$50.00 o más</option>
                </select>


            </div>
            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceConditionFilter">
                    <option value="all">Condición de Equipo</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Como Nuevo">Como Nuevo</option>
                    <option value="Buen Estado">Buen Estado</option>
                    <option value="Justo">Justo</option>
                </select>
            </div>
                <div class="col-12 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" id="clearMarketplaceFilters">
                        Limpiar filtros
                    </button>
                </div>
        </div>

        <!-- Card Template for empty search and filter results -->
        <div class="row g-4" id="marketplaceCardsContainer">
            <div class="col-12 d-none" id="marketplaceEmptyState">
                <div class="border rounded-4 p-4 text-center bg-light">
                    <h5 class="fw-bold mb-2">Publicaciones no existentes</h5>
                </div>
            </div>
        </div>


        <!-- Pagination -->
        <nav aria-label="Paginación de publicaciones" class="mt-4">
            <ul class="pagination justify-content-center mb-0" id="marketplacePagination"></ul>
        </nav>


        <!-- Post Details Modal -->
        <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                        <div class="pe-5">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel ">Detalle de la publicación</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>


                    <div class="modal-body px-4 pt-2 pb-4 post-details-body">
                        <div id="postImagesCarousel" class="carousel slide mb-4">
                            <div class="carousel-indicators" id="postImagesCarouselIndicators"></div>


                            <div class="carousel-inner rounded-4 overflow-hidden post-carousel-inner" id="postImagesCarouselInner"></div>


                            <button class="carousel-control-prev" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="prev" id="postImagesCarouselPrev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>


                            <button class="carousel-control-next" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="next" id="postImagesCarouselNext">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>
                        <p class="mb-3 text-muted d-none" id="postDetailsDescription"></p>
                        <hr>


                        <div class="row gy-3 pb-2">
                            <div class="col-6 text-muted">Precio:</div>
                            <div class="col-6 text-end fw-bold text-success" id="postDetailsPrice">$0.00</div>


                            <div class="col-6 text-muted">Estado:</div>
                            <div class="col-6 text-end">
                               <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;" id="postDetailsStatus">
                                   Disponible
                               </span>
                            </div>


                            <div class="col-6 text-muted">Condición:</div>
                            <div class="col-6 text-end">
                               <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;" id="postDetailsCondition">
                                   Sin especificar
                               </span>
                            </div>


                            <div class="col-6 text-muted">Vendedor:</div>
                            <div class="col-6 text-end fw-bold" id="postDetailsSeller">Usuario</div>


                            <div class="col-6 text-muted">Calificación del Vendedor:</div>
                            <div class="col-6 text-end" id="postDetailsSellerRating">
                                <i class="bi bi-star-fill text-warning me-1"></i>
                                0.0 <span class="text-muted">(0 reseñas)</span>
                            </div>


                            <div class="col-6 text-muted">Categoría:</div>
                            <div class="col-6 text-end">
                               <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;" id="postDetailsCategory">
                                   Sin categoría
                               </span>
                            </div>
                        </div>


                        <hr>


                        <div class="mt-4 pb-2">
                            <h5 class="fw-bold mb-3">Calificar Este Vendedor</h5>


                            <label class="form-label fw-semibold">Tu Calificación</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="marketplace-rating-stars text-warning fs-3" id="sellerRatingStars">
                                    <i class="bi bi-star rating-star" data-value="1" role="button" tabindex="0" aria-label="1 estrella"></i>
                                    <i class="bi bi-star rating-star" data-value="2" role="button" tabindex="0" aria-label="2 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="3" role="button" tabindex="0" aria-label="3 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="4" role="button" tabindex="0" aria-label="4 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="5" role="button" tabindex="0" aria-label="5 estrellas"></i>
                                </div>


                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSellerRating">
                                    Quitar
                                </button>
                            </div>




                            <input type="hidden" id="sellerRatingValue" value="0" step="0.5">
                            <p class="text-muted small mb-3" id="sellerRatingText">Selecciona una calificación</p>


                            <div class="d-grid mt-3">
                                <button type="button" class="btn btn-lg rounded-3 btn-outline-success" id="submitSellerRatingBtn">
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
                                    data-bs-target="#reportUserModal"
                                >
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


        <!-- Report User Modal -->
        <div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    <div class="modal-header border-0 pb-0 position-relative">
                        <div class="w-100 pe-5">
                            <h4 class="modal-title fw-bold mb-2 d-flex align-items-center" id="reportUserModalLabel">
                                <img
                                    src="{{ asset('images/icons/warning-triangle.png') }}"
                                    alt="Advertencia"
                                    class="me-2 report-warning-icon"
                                >
                                Reportar Usuario
                            </h4>


                            <p class="text-muted mb-1">
                                Reportar a John Davis por comportamiento sospechoso
                            </p>


                            <small class="text-muted">
                                <span class="text-danger">*</span> Campos requeridos
                            </small>
                        </div>


                        <button
                            type="button"
                            class="btn-close position-absolute top-0 end-0 m-3"
                            id="closeReportModalBtn"
                            aria-label="Cerrar"
                        ></button>
                    </div>


                    <div class="modal-body px-4 pt-2 pb-4">
                        @csrf
                        <form id="reportUserForm" novalidate>
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
                                <div class="invalid-feedback" id="reportReasonError">Seleciona una razón.</div>
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
                                    minlength="10"
                                    maxlength="500"
                                    required
                                ></textarea>
                                <small class="text-muted d-block fst-italic">
                                    Entre 10 y 500 caracteres. Solo letras, números, espacios, punto, coma y guion.
                                </small>
                                <div class="invalid-feedback d-block" id="reportDescriptionError"></div>
                            </div>


                            <div class="alert alert-warning rounded-4 mb-0">
                                <strong>Nota:</strong> Los reportes son revisados por administradores del mercado.
                                Los reportes falsos pueden resultar en restricciones de cuenta.
                            </div>
                        </form>
                    </div>


                    <div class="modal-footer border-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-outline-secondary px-4" id="cancelReportBtn">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="submitReportBtn" disabled>
                            Enviar Reporte
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Cancel Report Modal -->
        <div class="modal fade" id="cancelReportConfirmModal" tabindex="-1" aria-labelledby="cancelReportConfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="cancelReportConfirmLabel">¿Seguro que deseas cancelar?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>


                    <div class="modal-body">
                        Se perderán los datos que hayas escrito en este formulario.
                    </div>


                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Seguir editando
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmCancelReport">
                            Sí, cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Delete post Modal -->
        <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">


                    <div class="modal-header border-0 position-relative">
                        <h5 class="modal-title fw-bold pe-5" id="deletePostModalLabel">
                            ¿Seguro que deseas eliminar esta publicación?
                        </h5>


                        <button
                            type="button"
                            class="btn-close position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0" id="deletePostModalText">
                            Esta acción no se puede deshacer.
                        </p>
                    </div>


                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmDeletePost">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="toast-container position-fixed bottom-0 start-0 p-3">
            <div id="ratingSentToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                        Calificación enviada correctamente.
                    </div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>


            <div id="reportSentToast" class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width: auto; max-width: fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                        Reporte enviado correctamente.
                    </div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>

            <div
                id="profanityDetectedToast"
                class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                style="width: auto; max-width: 360px;"
            >
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        Se detectó lenguaje inapropiado. Revisa los campos marcados.
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
            </div>
        </div>
        <div class="toast-container position-fixed bottom-0 start-0 p-3">
            <div
                id="postCreatedToast"
                class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                style="width: auto; max-width: 300px;"
            >
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        Publicación creada exitosamente.
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const clearMarketplaceFiltersBtn = document.getElementById('clearMarketplaceFilters');

                if (clearMarketplaceFiltersBtn) {
                    clearMarketplaceFiltersBtn.addEventListener('click', function () {
                        document.getElementById('marketplaceSearch').value = '';
                        document.getElementById('marketplaceCategoryFilter').value = 'all';
                        document.getElementById('marketplaceRatingFilter').value = 'all';
                        document.getElementById('marketplacePriceFilter').value = 'all';
                        document.getElementById('marketplaceConditionFilter').value = 'all';

                        if (typeof filterMarketplaceItems === 'function') {
                            filterMarketplaceItems();
                        }
                    });
                }
            });
            </script>
</x-layout>

