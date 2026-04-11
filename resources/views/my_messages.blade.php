<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    @vite(['resources/js/pages/messages_profanity.js', 'resources/js/messages_validation.js', 'resources/js/echo.js'])

    @php
        $volverUrl = request('return_to', route('kinemarket'));
        $chatPostId = request('post_id');
    @endphp
    <style>
        .messages-search-group {
            border: 1px solid var(--bs-border-color);
            border-radius: 0.75rem;
            overflow: hidden;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            background-color: #fff;
        }

        .messages-search-group .input-group-text,
        .messages-search-group .form-control {
            background-color: #fff;
        }

        .messages-search-group .input-group-text {
            border-right: 0 !important;
        }

        .messages-search-group .form-control {
            border-left: 0 !important;
            box-shadow: none !important;
        }

        .messages-search-group:focus-within {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>

    <div
        class="container-fluid py-4"
        id="messagesView"
        data-chat-id="{{ request('chat_id', '') }}"
        data-current-user-id="{{ auth()->id() }}"
        data-post-id="{{ $selectedChat?->post_id ?? '' }}"
        data-current-user-id="{{ auth()->id() ?? '' }}"
    >
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="height: 650px;">
            <div class="row g-0" style="height: 100%;">

                <div class="col-md-4 border-end">
                    <div class="p-4 border-bottom">
                        <a href="{{ $volverUrl }}" class="btn btn-outline-secondary rounded-3 px-4">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>

                        <h1 class="fw-bold mt-3 mb-1">Mensajes</h1>
                        <p class="text-muted mb-0">Chats relacionados con tus publicaciones</p>
                    </div>

                    <div class="p-3 border-bottom">
                        <div class="input-group messages-search-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                id="messagesSearchInput"
                                class="form-control border-start-0"
                                placeholder="Buscar chats..."
                                autocomplete="off"
                            >
                        </div>
                    </div>

                   <div id="chatListContainer">

                       @foreach($chats as $chat)
                        <a 
                            href="{{ route('my_messages', [
                                'chat_id' => $chat->id,
                                'post_id' => $chat->post_id
                            ]) }}"
                            class="text-decoration-none text-dark"
                        >
                            <div
                                class="p-4 border-start border-4 border-success chat-list-item"
                                data-chat-id="{{ $chat->id }}"
                                data-post-id="{{ $chat->post_id }}"
                                data-user-name="{{ $chat->otherUser()->name ?? 'Usuario' }}"
                                data-post-title="{{ $chat->post->title ?? 'Sin título' }}"
                            >
                                <div class="d-flex align-items-start">
                                    <div
                                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 chat-user-initial"
                                        style="width: 48px; height: 48px;"
                                    >
                                        {{ strtoupper(substr($chat->otherUser()->name ?? 'U', 0, 1)) }}
                                    </div>

                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-1 fw-bold">
                                                {{ $chat->otherUser()->name ?? 'Usuario' }}
                                            </h5>
                                        </div>

                                        <div class="text-muted mb-2">
                                            {{ Str::limit($chat->post->title ?? 'Sin título', 35) }}
                                        </div>
                                    </div>
                                </div>
                            </div>   
                        </a>
                        @endforeach

                    </div>

                    <div
                        id="chatSearchEmptyState"
                        class="d-none p-4 text-center text-muted"
                    >
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        <p class="mb-0">No se encontraron chats.</p>
                    </div>
                </div>

                <div class="col-md-8 d-flex flex-column" style="height: 100%;">
                    <div class="p-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div class="d-flex align-items-center">
                                <div
                                    class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 chat-user-initial"
                                    style="width: 48px; height: 48px;"
                                    id="chatHeaderParticipantInitial"
                                >
                                    {{ strtoupper(substr($selectedChat?->otherUser()->name ?? 'U', 0, 1)) }}
                                </div>

                                <div>
                                    <h4 class="mb-1 fw-bold" id="chatHeaderParticipantName">
                                        {{ $selectedChat?->otherUser()->name ?? 'Selecciona un chat' }}
                                    </h4>
                                    <div class="text-muted" id="chatHeaderPostSummary">
                                        {{ $selectedChat?->post->title ?? '' }}
                                    </div>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-success rounded-3 px-4"
                                id="openChatPostDetailsBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#postDetailsModal"
                                data-post-id="{{ request('post_id', '') }}"
                            >
                                <i class="bi bi-eye me-2"></i> Ver Publicación
                            </button>   
                        </div>
                    </div>
                <div id="chatMessagesContainer" class="flex-grow-1 p-4 overflow-auto">
                    <div id="chatEmptyState" class="text-center text-muted">
                        No hay mensajes aún
                    </div>
                </div>

                <div class="p-4 border-top">
                    <div class="input-group">
                        <input
                            id="chatMessageInput"
                            type="text"
                            class="form-control form-control-lg"
                            placeholder="Escribe un mensaje..."
                        >
                        <button id="sendChatMessageBtn" class="btn btn-success">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </div>
                    </div>

                    <div class="p-4 border-top">
                        <div class="d-flex justify-content-between mt-1">
                            <div class="invalid-feedback d-block" id="chatMessageError"></div>
                            <small id="chatMessageCounter" class="text-muted d-block text-end">0 / 255</small>
                        </div>
                    </div>
                </div>

                <div class="toast-container position-fixed bottom-0 start-0 p-3">
                    <div
                        id="chatProfanityToast"
                        class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
                        role="alert"
                        aria-live="assertive"
                        aria-atomic="true"
                        style="width: auto; max-width: 360px;"
                    >
                        <div class="d-flex">
                            <div class="toast-body fw-semibold">
                                Se detectó lenguaje inapropiado. Revisa el mensaje.
                            </div>
                            <button
                                type="button"
                                class="btn-close me-2 m-auto"
                                data-bs-dismiss="toast"
                                aria-label="Cerrar"
                            ></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Post Details Modal -->
    <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                    <div class="pe-5">
                        <h4 class="modal-title fw-bold mb-1" id="postDetailsModalLabel">Detalle de la publicación</h4>
                        <p class="text-muted mb-0">Detalles de la Publicación</p>
                    </div>
                    <button
                        type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body px-4 pt-2 pb-4 post-details-body">
                    <div id="postImagesCarousel" class="carousel slide mb-4">
                        <div class="carousel-indicators" id="postImagesCarouselIndicators"></div>

                        <div
                            class="carousel-inner rounded-4 overflow-hidden post-carousel-inner"
                            id="postImagesCarouselInner"
                        ></div>

                        <button
                            class="carousel-control-prev"
                            type="button"
                            data-bs-target="#postImagesCarousel"
                            data-bs-slide="prev"
                            id="postImagesCarouselPrev"
                        >
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>

                        <button
                            class="carousel-control-next"
                            type="button"
                            data-bs-target="#postImagesCarousel"
                            data-bs-slide="next"
                            id="postImagesCarouselNext"
                        >
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
                            <span
                                class="badge rounded-0 px-3 py-2"
                                style="background-color:#6FC21F; color:white;"
                                id="postDetailsStatus"
                            >
                                Disponible
                            </span>
                        </div>

                        <div class="col-6 text-muted">Condición:</div>
                        <div class="col-6 text-end">
                            <span
                                class="badge rounded-0 px-3 py-2"
                                style="background-color:#6FC21F; color:white;"
                                id="postDetailsCondition"
                            >
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
                            <span
                                class="badge rounded-0 px-3 py-2"
                                style="background-color:#6FC21F; color:white;"
                                id="postDetailsCategory"
                            >
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
                        <div class="col-12">
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
                    </div>

                    <button
                        type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        aria-label="Cerrar"
                        id="closeReportModalBtn"
                    ></button>
                </div>

                <div class="modal-body px-4 pt-2 pb-4">
                    <form id="reportUserForm" novalidate>
                        <div class="mb-3">
                            <label for="reportReason" class="form-label fw-semibold">Razón</label>
                            <select id="reportReason" class="form-select form-select-lg" required>
                                <option value="" selected>Selecciona una razón</option>
                                <option value="fraude">Fraude</option>
                                <option value="contenido">Contenido inapropiado</option>
                                <option value="spam">Spam</option>
                                <option value="acoso">Acoso</option>
                                <option value="otro">Otro</option>
                            </select>
                            <div class="invalid-feedback d-block" id="reportReasonError">Selecciona una razón.</div>
                        </div>

                        <div class="mb-3">
                            <label for="reportDescription" class="form-label fw-semibold">Descripción</label>
                            <textarea
                                id="reportDescription"
                                class="form-control form-control-lg"
                                rows="4"
                                placeholder="Describe el problema..."
                            ></textarea>
                            <div class="invalid-feedback d-block" id="reportDescriptionError"></div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex flex-column gap-3">
                    <button
                        type="button"
                        class="btn btn-success w-100 rounded-3"
                        id="submitReportBtn"
                        disabled
                    >
                        Enviar Reporte
                    </button>

                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100 rounded-3"
                        id="cancelReportBtn"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Report Confirmation Modal -->
    <div class="modal fade" id="cancelReportConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-body text-center py-4">
                    <h5 class="fw-bold mb-3">¿Cancelar reporte?</h5>

                    <p class="text-muted">
                        Se perderá la información ingresada.
                    </p>

                    <div class="d-flex gap-2 mt-4">
                        <button
                            type="button"
                            class="btn btn-outline-secondary w-50"
                            data-bs-dismiss="modal"
                        >
                            Volver
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger w-50"
                            id="confirmCancelReport"
                        >
                            Sí, cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketplace-style toast container -->
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="ratingSentToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;"
        >
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Calificación enviada correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
            </div>
        </div>

        <div id="reportSentToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;"
        >
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Reporte fue enviado exitosamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="background-color: transparent; border: none; transform: scale(0.8);"></button>
            </div>
        </div>
    </div>
</x-layout>
