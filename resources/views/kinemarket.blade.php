<x-layout title="Kinemercado">
    <x-navbar></x-navbar>

    {{--Loads JS responsible for the validation in kinemercado, includes modal bahavior, scrolling, and profanity filtering behavior--}}
    @vite('resources/js/pages/marketplace_profanity.js')
    @vite('resources/js/marketplace_validation.js')

    {{--Main marketplace container used to expose configuration values,
    authenticated user information, and backend routes to marketplace JS--}}
    <div
        id="marketplaceHome"
        class="container py-4"
        data-store-mode="frontend"
        data-create-url="{{ '/marketplace' }}"
        data-delete-url-base="{{ url('/marketplace') }}"
        data-details-url-base="{{ url('/marketplace') }}"
        data-current-user-id="{{ auth()->id() ?? '' }}"
        data-current-user-role="{{ auth()->user() ? auth()->user()->role : '' }}"
    >
        {{--Page header introducing the Kinemarket/Kinemercado page--}}
        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Kinemercado</h1>
            <p>
                Aquí puedes crear y buscar equipamiento deportivo. También puedes comunicarte con vendedores.
            </p>
        </div>


        {{--Button used to open the create-post modal.
        Marketplace JS dynamically blocks this action and opens the
        post-limit modal instead when the user reaches the 15-post limit.--}}
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

        {{--Marketplace publication limit notice for the 15-day posting period--}}
        <div class="d-inline-flex align-items-center alert alert-warning rounded-4 border-0 shadow-sm mb-4 px-4 py-3">
            <strong><i class="bi bi-info-circle me-2"></i>Límite de Publicaciones:</strong>
            <span class="ms-1">
        Puedes crear un máximo de <strong>15 publicaciones</strong> dentro de un período de <strong>15 días</strong>.
            </span>
        </div>


        {{--Modal used to create a new marketplace post--}}
        <div class="modal fade" id="createPostModal" tabindex="-1" aria-labelledby="createPostLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    {{--Create post modal header with subtext included--}}
                    <div class="modal-header border-0 pb-0 pt-4 px-4 pb-2 align-items-start">
                        <div class="pe-3">
                            <h4 class="modal-title fw-bold mb-2" id="createPostLabel">Crear una Nueva Publicación</h4>
                            <p class="text-muted mb-1">
                                Publica tu equipo deportivo para la venta a otros usuarios
                            </p>
                            <small class="text-muted">
                                <span class="text-danger">*</span> Campos requeridos
                            </small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>

                    {{--Create post modal body--}}
                    <div class="modal-body px-4 pt-2 pb-4">
                        {{--Form used for post creation with validation handled in the marketplace JS--}}
                        <form id="createPostForm" novalidate>
                            @csrf
                            <div class="mb-3">
                                <div class="alert alert-warning rounded-4 border-0 shadow-sm mt-3 mb-3 px-4 py-3" id="postExpirationNotice">
                                    <div class="d-flex align-items-start gap-3">
                                        <div>

                                            {{--Notification explaining that marketplace posts automatically expire
                                            and are permanently removed after 15 days--}}
                                            <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong>
                                            Las publicaciones en Kinemercado solo permanecen activas por un máximo de
                                            <strong>15 días</strong>. Luego de ese período, la publicación será
                                            <strong>eliminada automáticamente</strong>.
                                        </div>
                                    </div>
                                </div>

                                {{--Post title input with validation and profanity filtering--}}
                                <label for="postTitle" class="form-label fw-semibold">
                                    Título<span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg"
                                    id="postTitle"
                                    placeholder="ej. Baloncesto - Spalding"
                                    minlength="5"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Entre 5 y 100 caracteres. Solo letras, números, espacios, punto, coma y guion.
                                </small>
                                {{--Displays error if there is invalid inputs in title format or an inappropriate word was detected--}}
                                <div class="invalid-feedback" id="postTitleError"></div>
                                <div class="invalid-feedback d-block" id="postTitleProfanityError"></div>
                            </div>

                            {{--Optional description with validation and profanity filtering--}}
                            <div class="mb-3">
                                <label for="postDescription" class="form-label fw-semibold">
                                    Descripción (Opcional)
                                </label>
                                <textarea
                                    class="form-control form-control-lg"
                                    id="postDescription"
                                    rows="4"
                                    placeholder="Describe el estado y detalles del equipo"
                                ></textarea>
                                <small class="text-muted d-block fst-italic">
                                    Si escribes una descripción, debe tener como máximo 500 caracteres.
                                    Solo letras, números espacios, punto, coma y guion.
                                </small>
                                {{--Displays error if there is invalid inputs in description format or an inappropriate word was detected--}}
                                <div class="invalid-feedback" id="postDescriptionError"></div>
                                <div class="invalid-feedback d-block" id="postDescriptionProfanityError"></div>
                            </div>


                            {{--Price input with numeric and decimal validation--}}
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

                                    {{--Price helper text instructions for price input--}}
                                    <small class="text-muted d-block fst-italic">
                                        Escribe solo números. Los decimales son opcionales; si los usas, puedes ingresar hasta 2 dígitos después del punto decimal. El valor máximo permitido es $10,000.00.
                                    </small>
                                    {{--Displays error if there is invalid inputs in price format--}}
                                    <div class="invalid-feedback d-block" id="postPriceError"></div>
                                </div>


                                {{--Category selection dropdown filter connects with backend to create a post with selected sport category on the post table--}}
                                <div class="col-md-6 mb-3">
                                    <label for="postCategory" class="form-label fw-semibold">
                                        Categoría<span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-lg" id="postCategory" required>
                                        <option value="" selected>Selecciona un deporte</option>
                                        <option value="Baloncesto">Baloncesto</option>
                                        <option value="Tenis">Tenis</option>
                                        <option value="Fútbol">Fútbol</option>
                                        <option value="Deporte Recreativo">Deporte Recreativo</option>
                                        <option value="Voleibol">Voleibol</option>
                                        <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                                        <option  value="Otros">Otros</option>
                                    </select>
                                    {{--Category selection error, if not selection was made before posting a product--}}
                                    <div class="invalid-feedback">Selecciona la categoría deportiva a la que pertenece el equipo.</div>
                                </div>
                            </div>

                            {{--Condition selection dropdown filter connects with backend to create a post with selected equipment conditon on the post table--}}
                            <div class="mb-3">
                                <label for="postCondition" class="form-label fw-semibold">
                                    Condición<span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="postCondition" required>
                                    <option value="" selected>Selecciona una condición</option>
                                    <option>Nuevo</option>
                                    <option>Como Nuevo</option>
                                    <option>Buen Estado</option>
                                    <option>Justo</option>
                                </select>
                                {{--Condition selection error, if not selection was made before posting a product--}}
                                <div class="invalid-feedback">Selecciona la condición a la que pertenece el equipo.</div>
                            </div>

                            {{--Image upload input with restrictions (1–3 images, JPG/JPEG only)--}}
                            <div class="mb-2">
                                <label for="postImage" class="form-label fw-semibold">
                                    Fotos del equipo<span class="text-danger">*</span>
                                </label>
                                {{--Hidden file input triggered by custom made upload button--}}
                                <input
                                    type="file"
                                    class="d-none"
                                    id="postImage"
                                    accept=".jpg,.jpeg,image/jpeg"
                                    multiple
                                    required
                                >
                            </div>

                            {{--Image upload button and helper text--}}
                            <label for="postImage" class="form-control form-control-lg text-center py-3" style="cursor:pointer;">
                                <i class="bi bi-upload me-2"></i>
                                Subir imágenes
                            </label>


                            <small class="text-muted d-block fst-italic mt-2">
                                Mínimo 1 imagen y máximo 3 imágenes permitidas.
                                Solo JPEG/JPG.
                                Máximo 2MB por imagen.
                            </small>
                            {{--Displays image upload validation errors related to format, size, or quantity restrictions--}}
                            <div id="imageError" class="invalid-feedback d-block d-none"></div>
                            {{--Image preview container for picture that are uploaded--}}
                            <div id="imagePreviewContainer" class="row g-3 mt-1"></div>
                        </form>
                    </div>

                    {{--Cancellation and publication of post creation form buttons--}}
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


        {{--Modal to confirm cancellation of post creation--}}
        <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-label="cancelConfirmLabel" aria-hidden="true">
            {{--Prevents accidental loss of form data--}}
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    {{--Cancel post creation confirmation modal header--}}
                    <div class="modal-header border-0 position-relative">
                        <h5 class="modal-title fw-bold" id="cancelConfirmLabel">
                            ¿Seguro que deseas cancelar?
                        </h5>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"
                        ></button>
                    </div>


                    <div class="modal-body">
                        Se perderán los datos e imágenes que hayas escrito en este formulario.
                    </div>


                    {{--Cancel post creation confirmation actions--}}
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Seguir Editando
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmCancelCreatePost">
                            Sí, Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{--Search bar and filters for marketplace posts--}}
        <div class="row mb-4 g-3">
            <div class="col-md-10">
                {{--Search input for filtering posts by text--}}
                <div class="input-group search-group">
                   <span class="input-group-text bg-white border-0">
                       <i class="bi bi-search"></i>
                   </span>

                    {{--Search bar ID for backend connection and text placeholder--}}
                    <input
                        type="text"
                        class="form-control border-0"
                        id="marketplaceSearch"
                        placeholder="Buscar publicaciones por título, descripción, categoría, condición o vendedor..."
                    >
                </div>
            </div>

            {{--Search button that appears disabled until the user inputs text--}}
            <div class="col-md-2 d-grid">
                <button type="button" class="btn btn-success" id="searchMarketplaceBtn" disabled>
                    Buscar
                </button>
            </div>

            {{--Sport category filter connects with backend to request posts with matching sport categories--}}
            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceCategoryFilter">
                    <option value="all">Todos los Deportes</option>
                    <option value="Baloncesto">Baloncesto</option>
                    <option value="Tenis">Tenis</option>
                    <option value="Fútbol">Fútbol</option>
                    <option value="Deporte Recreativo">Deporte Recreativo</option>
                    <option value="Voleibol">Voleibol</option>
                    <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            {{--Seller star rating filter connects with backend to request posts with matching seller ratings--}}
            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceRatingFilter">
                    <option value="all">Calificaciones del Vendedor</option>
                    <option value="0">0 estrellas</option>
                    <option value="1">Entre 0 a 1 estrella</option>
                    <option value="2">Entre 1 a  2 estrellas</option>
                    <option value="3">Entre 2 a 3 estrellas</option>
                    <option value="4">Entre 3 a 4 estrellas</option>
                    <option value="5">Entre 4 a 5 estrellas</option>
                </select>
            </div>

            {{--Post price range filter connects with backend to request posts with matching price ranges--}}
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

            {{--Post equipment condition filter connects with backend to request posts with matching equipment condition--}}
            <div class="col-md-3">
                <select class="form-select border-2 border-dark" id="marketplaceConditionFilter">
                    <option value="all">Condición del Equipo</option>
                    <option value="Nuevo">Nuevo</option>
                    <option value="Como Nuevo">Como Nuevo</option>
                    <option value="Buen Estado">Buen Estado</option>
                    <option value="Justo">Justo</option>
                </select>
            </div>

            {{--Reset for all searches and filters--}}
            <div class="col-12 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary" id="clearMarketplaceFilters">
                    Limpiar Filtros
                </button>
            </div>
        </div>


        {{--Container where marketplace post cards are dynamically rendered--}}
        <div class="row g-4" id="marketplaceCardsContainer">
            {{--Empty state shown when no posts match filters--}}
            <div class="col-12 d-none" id="marketplaceEmptyState">
                <div class="card border-0 shadow-sm rounded-0">
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-shop fs-1 text-muted"></i>
                        <h4 class="fw-bold mt-3">No se encontraron publicaciones.</h4>
                    </div>
                </div>
            </div>
        </div>


        {{--Pagination controls for navigating posts, controlled via the marketplace JS file--}}
        <nav aria-label="Paginación de publicaciones" class="mt-4">
            {{--Pagination list dynamically filled by marketplace JS--}}
            <ul class="pagination justify-content-center mb-0" id="marketplacePagination"></ul>
        </nav>



        {{--Modal showing full details of a selected post such as: title, description, cost, sport category, equipment condition, status, seller's name, and more...--}}
        <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    {{--Post details modal header--}}
                    <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                        <div class="pe-5">
                            <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Detalle de la publicación</h4>
                            <p class="text-muted mb-0">Detalles de la Publicación</p>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>


                    {{--Carousel structure that displays specific uploaded post images, it includes the controls for moving from one image to the next--}}
                    <div class="modal-body px-4 pt-2 pb-4 post-details-body">
                        <div id="postImagesCarousel" class="carousel slide mb-4">
                            <div class="carousel-indicators" id="postImagesCarouselIndicators"></div>

                            <div class="carousel-inner rounded-4 overflow-hidden border border-dark border-2 post-carousel-inner" id="postImagesCarouselInner"></div>

                            {{--Carousel navigation controls used to move between uploaded post images.
                             They remain hidden when the post only contains one image.--}}
                            <button class="carousel-control-prev" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="prev" id="postImagesCarouselPrev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Anterior</span>
                            </button>


                            <button class="carousel-control-next" type="button" data-bs-target="#postImagesCarousel" data-bs-slide="next" id="postImagesCarouselNext">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Siguiente</span>
                            </button>
                        </div>

                        {{--Displays optional post description--}}
                        <p class="mb-3 text-muted d-none" id="postDetailsDescription"></p>
                        <hr>

                        {{--Displays post info: price, item condition, item staus, item sport category, seller, and rating system--}}
                        <div class="row gy-3 pb-2">
                            <div class="col-6 text-muted">Precio:</div>
                            <div class="col-6 text-end fw-bold text-success" id="postDetailsPrice">$0.00</div>


                            <div class="col-6 text-muted">Estado:</div>
                            <div class="col-6 text-end">
                               <span class="label-badge badge-available" id="postDetailsStatus">
                                     Disponible
                               </span>
                            </div>


                            <div class="col-6 text-muted">Condición:</div>
                            <div class="col-6 text-end">
                               <span class="label-badge badge-available" id="postDetailsCondition">
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
                              <span class="label-badge badge-available" id="postDetailsCategory">
                                Sin categoría
                              </span>
                            </div>
                        </div>

                        <div id="postOwnerRestrictedSection">
                        <hr>

                        {{--Allows the user to rate the seller--}}
                        <div class="mt-4 pb-2">
                            <h5 class="fw-bold mb-3">Calificar Este Vendedor</h5>


                            <label class="form-label fw-semibold">Tu Calificación</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                {{--Star icons for the rating selection and animation fill--}}
                                <div class="marketplace-rating-stars text-warning fs-3" id="sellerRatingStars">
                                    <i class="bi bi-star rating-star" data-value="1" role="button" tabindex="0" aria-label="1 estrella"></i>
                                    <i class="bi bi-star rating-star" data-value="2" role="button" tabindex="0" aria-label="2 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="3" role="button" tabindex="0" aria-label="3 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="4" role="button" tabindex="0" aria-label="4 estrellas"></i>
                                    <i class="bi bi-star rating-star" data-value="5" role="button" tabindex="0" aria-label="5 estrellas"></i>
                                </div>

                                {{--Button to erase star rating selection before submitting--}}
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSellerRating">
                                    Quitar
                                </button>
                            </div>

                            {{--Hidden input used to store the selected seller rating--}}
                            <input type="hidden" id="sellerRatingValue" value="0" step="0.5">
                            <p class="text-muted small mb-3" id="sellerRatingText">Selecciona una calificación</p>


                            <div class="d-grid mt-3">
                                {{--Button to send seller rating to the backend for calculations--}}
                                <button type="button" class="btn btn-lg rounded-3 btn-outline-success" id="submitSellerRatingBtn">
                                    Enviar Calificación
                                </button>
                            </div>
                        </div>

                        {{--Button to report a specific post seller--}}
                        <div class="row g-2 mt-3">
                            <div class="col-6">
                                <button
                                    type="button"
                                    class="btn btn-danger w-100 rounded-3"
                                    data-bs-dismiss="modal"
                                    data-bs-toggle="modal"
                                    data-bs-target="#reportUserModal"
                                >
                                    <i class="bi bi-flag me-2"></i> Reportar Usuario
                                </button>
                            </div>



                            {{--Link/button to messaging system (Mis Chats) route from a specific post--}}
                            <div class="col-6">
                                <a href="{{ url('/my_messages') }}" id="postDetailsChatLink" class="btn btn-success w-100 rounded-3">
                                    <i class="bi bi-chat me-2"></i> Enviar Mensaje
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{--Modal shown when the user reaches the maximum amount of marketplace posts allowed within a 15-day period--}}
        <div class="modal fade" id="maxPostLimitModal" tabindex="-1" aria-labelledby="maxPostLimitModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-0 position-relative">
                        <h5 class="modal-title fw-bold pe-5" id="maxPostLimitModalLabel">
                            Límite de publicaciones alcanzado
                        </h5>

                        <button
                            type="button"
                            class="btn-close position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    {{--Modal Body--}}
                    <div class="modal-body">
                        <p class="mb-2">
                            Has alcanzado el máximo de <strong>15 publicaciones</strong> permitidas dentro de un período de <strong>15 días</strong>.
                        </p>

                        <p class="mb-0">
                            Para volver a publicar, elimina una publicación existente o espera a que una de tus publicaciones cumpla 15 días.
                        </p>
                    </div>

                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            Entendido
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{--Modal to report a seller for an inappropriate or misleading post--}}
        <div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                    {{--Report modal header--}}
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


                            <p class="text-muted mb-1" id="reportUserText">
                                Reportar a Usuario por comportamiento sospechoso
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

                    {{--Form to submit report/querella with the reason of the report/querella and description of the incident--}}
                    <div class="modal-body px-4 pt-2 pb-4">
                        @csrf
                        <form id="reportUserForm" novalidate>

                            {{--Notice message explaining report review process--}}
                            <div class="alert alert-warning rounded-4 mb-3">
                                <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong> Los querellas son revisados por los administradores de mercado.
                                Las querellas válidas pueden resultar en restricciones de cuenta.
                            </div>

                            {{--Report reason dropdown selector--}}
                            <div class="mb-3">
                                <label for="reportReason" class="form-label fw-semibold">
                                    Razón de la Querella <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg" id="reportReason" required>
                                    <option value="" selected>Selecciona una razón</option>
                                    <option value="Fraude o estafa">Fraude o estafa</option>
                                    <option value="Información falsa">Información falsa</option>
                                    <option value="Lenguaje ofensivo">Lenguaje ofensivo</option>
                                    <option value="Contenido inapropiado">Contenido inapropiado</option>
                                    <option value="Otros">Otros</option>
                                </select>

                                {{-- Displays error if the report/querella is attempted to be sent without selectiing a reason--}}
                                <div class="invalid-feedback" id="reportReasonError">Selecciona una razón.</div>
                            </div>

                            {{--Report/querella description input field with connection to marketplace JS validation and formatting rules--}}
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
                                    required
                                ></textarea>
                                <small class="text-muted d-block fst-italic">
                                    Entre 10 y 500 caracteres. Solo letras, números, espacios, punto, coma y guion.
                                </small>
                                {{--Displays error if the report/querella is attempted to be sent an incorrect description format--}}
                                <div class="invalid-feedback d-block" id="reportDescriptionError"></div>
                            </div>
                        </form>
                    </div>

                    {{--Report modal action buttons--}}
                    <div class="modal-footer border-0 px-4 pb-4 pt-2">
                        <button type="button" class="btn btn-outline-secondary px-4" id="cancelReportBtn">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger px-4" id="submitReportBtn" disabled>
                            Enviar Querella
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{--Modal confirming cancellation of report/querella send--}}
        <div class="modal fade" id="cancelReportConfirmModal" tabindex="-1" aria-labelledby="cancelReportConfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    {{--Cancel report confirmation modal header--}}
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold" id="cancelReportConfirmLabel">¿Seguro que deseas cancelar?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>


                    <div class="modal-body">
                        Se perderán los datos que hayas escrito en este formulario.
                    </div>

                    {{--Cancel report confirmation action buttons--}}
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Seguir Editando
                        </button>
                        <button type="button" class="btn btn-danger" id="confirmCancelReport">
                            Sí, Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>


        {{--Modal confirming deletion of a post--}}
        <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">

                    {{--Delete confirmation modal header--}}
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
                    {{--Delete confirmation modal body--}}
                    <div class="modal-body">
                        <p class="mb-0" id="deletePostModalText">
                            Esta acción no se puede deshacer.
                        </p>
                    </div>

                    {{--Delete confirmation action buttons--}}
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

        {{--Toast notifications for user feedback (success)--}}
        <div class="toast-container position-fixed bottom-0 start-0 p-3">
            <div id="ratingSentToast"
                 class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                 role="alert"
                 aria-live="assertive"
                 aria-atomic="true"
                 style="width: auto; max-width: fit-content;"
            >
                {{--Toast notification shown only when the seller rating has been submitted successfully--}}
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                        Calificación enviada correctamente.
                    </div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>

            {{--Toast notification shown only when the report/querella has been submitted successfully--}}
            <div id="reportSentToast"
                 class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                 role="alert"
                 aria-live="assertive"
                 aria-atomic="true"
                 style="width: auto; max-width: fit-content;"
            >
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                        Querella fue enviado exitosamente.
                    </div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
                </div>
            </div>


            {{--Toast notification shown when inappropriate language is detected during post submission--}}
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

        {{--Toast notification shown only when a post has been created successfully--}}
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
                        Publicación fue creada exitosamente.
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>

            {{--Toast notification shown only when a post has been deleted/removed successfully--}}
            <div id="postDeletedToast"
                 class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                 role="alert"
                 aria-live="assertive"
                 aria-atomic="true"
                 style="width: auto; max-width: 300px;"
            >
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        Publicación fue eliminada exitosamente.
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
    </div>
</x-layout>

