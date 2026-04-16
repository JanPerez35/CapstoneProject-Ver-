<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    @vite(['resources/js/pages/messages_profanity.js', 'resources/js/messages_validation.js', 'resources/js/echo.js'])

    @php
        $chatPostId = request('post_id');
         $volverBaseUrl = request('return_to', route('kinemarket'));

          $volverUrl = $chatPostId
                 ? $volverBaseUrl . (str_contains($volverBaseUrl, '?') ? '&' : '?') . 'post_id=' . $chatPostId
                : $volverBaseUrl;

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

        .messages-card {
            height: calc(100vh - 140px);
            min-height: 600px;
        }

        .messages-sidebar,
        .messages-chat-column {
            height: 100%;
        }

        .messages-container {
            min-height: 0;
        }

        .chat-input-area {
            background: #fff;
            flex-shrink: 0;
        }

        .min-w-0 {
            min-width: 0;
        }

        @media (max-width: 767.98px) {
            .messages-card {
                height: auto;
                min-height: 0;
                border-radius: 1rem;
            }

            .messages-sidebar,
            .messages-chat-column {
                height: auto;
                max-height: none;
                min-height: 0;
            }

            .messages-sidebar {
                overflow: visible;
                border-right: 0 !important;
                border-bottom: 1px solid var(--bs-border-color);
            }

            .messages-chat-column {
                min-height: 70vh;
            }

            #chatListContainer {
                max-height: 280px;
                overflow-y: auto;
            }

            #chatMessagesContainer {
                max-height: 38vh;
                min-height: 220px;
            }

            .chat-input-area {
                position: sticky;
                bottom: 0;
                z-index: 2;
                background: #fff;
            }

            .chat-header-row {
                flex-wrap: wrap !important;
                align-items: flex-start !important;
            }

            .chat-header-main {
                flex: 0 0 100%;
                width: 100%;
                min-width: 0;
                order: 2;
            }

            #backToChatsBtn {
                order: 1;
                flex-shrink: 0;
            }

            #openChatPostDetailsBtn {
                width: 100%;
                margin-top: 0.25rem;
                order: 3;
            }

            .messages-sidebar .p-4.border-bottom {
                padding-bottom: 1rem !important;
            }

            .messages-chat-column > .border-bottom {
                padding-top: 1rem !important;
                padding-bottom: 1rem !important;
            }
        }

        @media (max-width: 767.98px) {
            .chat-input-grid {
                grid-template-columns: 1fr;
            }

            .chat-counter-wrap {
                justify-self: end;
            }
        }

        .messages-person-item {
            border-bottom: 1px solid #d6dde5;
            background-color: #fff;
            transition: background-color 0.15s ease, box-shadow 0.15s ease;
        }

        .messages-person-item:hover {
            background-color: #f8fafc;
        }

        #chatListContainer .text-decoration-none:last-child .messages-person-item {
            border-bottom: 0;
        }

        @media (max-width: 767.98px) {
            .d-none-selected-mobile {
                display: none !important;
            }
        }

        @media (max-width: 767.98px) {
            .mobile-hidden {
                display: none !important;
            }
        }

        #chatListContainer {
            overflow-y: auto;
            max-height: calc(100vh - 260px);
        }

        .messages-sidebar {
            display: flex;
            flex-direction: column;
        }

        .chat-header-text-wrap {
            min-width: 0;
            flex: 1 1 auto;
        }

        .chat-header-post-summary {
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            white-space: normal;
            text-overflow: ellipsis;
            word-break: break-word;
            line-height: 1.35;
        }

        #openChatPostDetailsBtn {
            flex-shrink: 0;
        }

    </style>

    <div
        class="container-fluid py-4"
        id="messagesView"
        data-chat-id="{{ request('chat_id', '') }}"
        data-current-user-id="{{ auth()->id() }}"
        data-post-id="{{ $selectedChat?->post_id ?? '' }}"
    >
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden messages-card">
            <div class="row g-0 h-100">

                <div class="col-md-4 border-end messages-sidebar">
                    <div class="p-4 border-bottom">
                        <a href="{{ request('return_to', route('kinemarket')) }}" class="btn btn-outline-secondary rounded-3 px-4"  id="messagesVolverBtn"
                           data-return-post-id="{{ request('post_id', '') }}">

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
                                placeholder="Buscar conversación..."
                                autocomplete="off"
                            >
                        </div>
                    </div>

                   <div id="chatListContainer" class="flex-grow-1">

                       @foreach($chats as $chat)
                        <a
                            href="{{ route('my_messages', [
                                'chat_id' => $chat->id,
                                'post_id' => $chat->post_id,
                                 'return_to' => request('return_to', route('kinemarket'))
                            ]) }}"
                            class="text-decoration-none text-dark"
                        >
                            <div
                                class="p-4 border border-3 chat-list-item {{ request('chat_id') == $chat->id ? 'bg-success-subtle border-success shadow-sm' : 'bg-white border-success-subtle' }}"
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
                                            @if($chat->unread_count > 0)
                                                <span class="badge bg-danger rounded-pill">
                                                    {{ $chat->unread_count }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-muted mb-2">
                                            {{ Str::limit($chat->post->title ?? 'Sin título', 20) }}
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

                <div class="col-12 col-md-8 d-flex flex-column messages-chat-column">

                    <!--Header-->
                    <div class="p-3 p-md-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-nowrap chat-header-row">
                            <button
                                type="button"
                                class="btn btn-outline-secondary d-md-none"
                                id="backToChatsBtn"
                            >
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>

                            <div class="d-flex align-items-center min-w-0 flex-grow-1 chat-header-main">

                                <div
                                    class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0 chat-user-initial {{ $selectedChat ? '' : 'd-none' }}"
                                    style="width: 48px; height: 48px;"
                                    id="chatHeaderParticipantInitial"
                                >
                                    {{ strtoupper(substr($selectedChat?->otherUser()->name ?? 'U', 0, 1)) }}
                                </div>

                                <div class="min-w-0 chat-header-text-wrap">
                                    <h4 class="mb-1 fw-bold text-truncate w-100" id="chatHeaderParticipantName">
                                        {{ $selectedChat?->otherUser()->name ?? 'Selecciona un chat' }}
                                    </h4>

                                    <div class="text-muted chat-header-post-summary" id="chatHeaderPostSummary" title="{{ $selectedChat?->post->title ?? '' }}">
                                        {{ $selectedChat?->post->title ?? '' }}
                                    </div>
                                </div>

                            </div>

                            <button
                                type="button"
                                class="btn btn-success rounded-3 px-4 flex-shrink-0 chat-header-post-btn"
                                id="openChatPostDetailsBtn"
                                data-bs-toggle="modal"
                                data-bs-target="#postDetailsModal"
                                data-post-id="{{ request('post_id', '') }}"
                                {{ $selectedChat ? '' : 'disabled' }}
                            >
                                <i class="bi bi-eye me-2"></i> Ver Publicación
                            </button>

                        </div>
                    </div>

                    <!--Messages-->
                    <div id="chatMessagesContainer" class="flex-grow-1 p-4 overflow-auto messages-container">

                        <div id="chatEmptyState" class="d-none">
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="card border-0 shadow-sm rounded-0">
                                        <div class="card-body py-5 text-center">
                                            <i class="bi bi-chat-dots fs-1 text-muted"></i>
                                            <h4 class="fw-bold mt-3">No hay mensajes aún.</h4>
                                            <p class="text-muted mb-0">Selecciona un chat para comenzar la conversación.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!--Input-->
                    <div class="p-3 p-md-4 border-top chat-input-area">

                                <div class="input-group chat-message-group">
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

                                <div class="position-relative mt-2" style="min-height: 1.5rem;">
                                    <div class="invalid-feedback d-block m-0 pe-5" id="chatMessageError"></div>
                                    <small id="chatMessageCounter" class="text-muted position-absolute end-0 top-0 text-end">
                                        0 / 255
                                    </small>
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
                                class="label-badge badge-available"
                                id="postDetailsStatus"
                            >
                                Disponible
                            </span>
                        </div>

                        <div class="col-6 text-muted">Condición:</div>
                        <div class="col-6 text-end">
                            <span
                                class="label-badge badge-available"
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
                                class="label-badge badge-available"
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
                <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                    <div class="pe-3">
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
                        aria-label="Cerrar"
                        id="closeReportModalBtn"
                    ></button>
                </div>

                <div class="modal-body px-4 pt-2 pb-4">
                    <form id="reportUserForm" novalidate>
                        <div class="mb-3">
                            <label for="reportReason" class="form-label fw-semibold">
                                Razón <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="reportReason" required>
                                <option value="" selected disabled>Seleccionar una razón</option>
                                <option value="Fraude">Fraude o estafa</option>
                                <option value="Información falsa">Información falsa</option>
                                <option value="Lengiaje ofensivo">Lenguaje ofensivo</option>
                                <option value="Contenido inapropiado">Contenido inapropiado</option>
                                <option value="Otros">Otros</option>
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
                                placeholder="Proporciona detalles sobre el comportamiento sospechoso."
                                minlength="10"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 500 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </small>
                            <div class="invalid-feedback d-block" id="reportDescriptionError"></div>
                        </div>

                        <div class="alert alert-warning rounded-4 mb-0">
                            <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong>
                            Los querellas son revisados por los administradores de mercado.
                            Las querellas válidas pueden resultar en restricciones de cuenta.
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-2">
                    <button
                        type="button"
                        class="btn btn-outline-secondary px-4"
                        id="cancelReportBtn"
                    >
                        Cancelar
                    </button>

                    <button
                        type="button"
                        class="btn btn-danger px-4"
                        id="submitReportBtn"
                        disabled
                    >
                        Enviar Querella
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Report Confirmation Modal -->
    <div class="modal fade" id="cancelReportConfirmModal" tabindex="-1" aria-labelledby="cancelReportConfirmLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold" id="cancelReportConfirmLabel">
                        ¿Seguro que deseas cancelar?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>


                    <div class="modal-body">
                        Se perderán los datos que hayas escrito en este formulario.
                    </div>

                    <div class="modal-footer border-0">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Seguir editando
                        </button>

                        <button
                            type="button"
                            class="btn btn-danger"
                            id="confirmCancelReport"
                        >
                            Sí, cancelar
                        </button>
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
