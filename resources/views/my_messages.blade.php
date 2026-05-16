<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    {{--Javascript modules that handle profanity word filtering logic, frontend validation, chat UI interactions,
         modal behavior, dynamic messsage rendering, search filtering, and toast notifications--}}
    @vite(['resources/js/pages/messages_profanity.js', 'resources/js/messages_validation.js'])

    {{--Variables that preserve navigation flow between marketplace posts and messaging system--}}
    @php
    /**
    *  The function of the variables are as follows:
    * -Keeps track of the marketplace post currently associated with the chat.
    * -Determines where the user should return after leaving the messaging page.
    * Default is the marketplace if no return route is provided.
    * -Rebuilds the URL while preserving the related post_id so the user may return to the exact
    * marketplace publication/post context they originated from.
     * */
        $chatPostId = request('post_id');
        $volverBaseUrl = request('return_to', route('kinemarket'));

        $volverUrl = $chatPostId
             ? $volverBaseUrl . (str_contains($volverBaseUrl, '?') ? '&' : '?') . 'post_id=' . $chatPostId
             : $volverBaseUrl;

    @endphp

    {{--Container that exposes important configuration and session data to the front-end Javascript
        system using the data-* attributes--}}
    <div
        class="container-fluid py-4"
        id="messagesView"
        style="min-height: calc(100vh - 90px);"
        {{--Used to load and highlight the selected chat--}}
        data-chat-id="{{ request('chat_id', '') }}"
        {{--Used to determines message owenership for message aligment and styling--}}
        data-current-user-id="{{ auth()->id() }}"
        {{--Used to connect the active conversation with a marketplace post--}}
        data-post-id="{{ $selectedChat?->post_id ?? '' }}"
    >
        {{--Main messagin card layout
            Splits into left sidebar - chat list and search bar &
            right sidebar - active chat converation--}}
        <div class="card border border-dark border-2 shadow-sm rounded-4 overflow-hidden messages-card flex-grow-1">
            <div class="row g-0 h-100">

                {{--Left sidebar--}}
                <div class="col-md-4 border-end border-dark border-2 messages-sidebar">
                    {{--Sidebar header and return navigation.
                        Allows the user to return to the page they originally came from.
                        (Usually the marketplace post details)--}}
                    <div class="px-4 pt-4 pb-2">
                        <a href="{{ request('return_to', route('kinemarket')) }}" class="btn btn-outline-secondary rounded-3 px-4"  id="messagesVolverBtn"
                           data-return-post-id="{{ request('post_id', '') }}">

                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>

                        {{--Messaging view title--}}
                        <h1 class="fw-bold mt-5 mb-1">Mensajes</h1>

                        {{--Page description--}}
                        <p class="text-muted mb-0">Chats relacionados con tus publicaciones</p>
                    </div>

                    {{--Chat search section, allows the user filter conversations dynamically,
                        searches by particiapnt's name and post title--}}
                    <div class="px-4 pt-2 pb-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="input-group messages-search-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input
                                        type="text"
                                        id="messagesSearchInput"
                                        class="form-control border-start-0"
                                        placeholder="Buscar por nombre o por título..."
                                        autocomplete="off"
                                    >
                                </div>
                            </div>

                            {{--Search trigger button, initially disabled until user enters text--}}
                            <div class="col-6 d-grid">
                                <button type="button" class="btn btn-success" id="searchMessagesBtn" disabled>
                                    Buscar
                                </button>
                            </div>

                            {{--Clears all search filters--}}
                            <div class="col-6 d-grid">
                                <button type="button" class="btn btn-outline-secondary" id="clearMessagesFiltersBtn">
                                    Limpiar Busqueda
                                </button>
                            </div>
                        </div>
                    </div>

                    {{--Chat list section title--}}
                    <div class="px-4 pt-3 pb-2">
                        <h5 class="fw-bold mb-0">Conversaciones:</h5>
                    </div>

                   {{--Dynamic chat list container
                       Each chat iteam sotres metadata in data-* attributes used
                        by the JS filtering and chat loading logic--}}
                   <div id="chatListContainer">
                       @foreach($chats as $chat)
                        <a
                            href="{{ route('my_messages', [
                                'chat_id' => $chat->id,
                                'post_id' => $chat->post_id,
                                 'return_to' => request('return_to', route('kinemarket'))
                            ]) }}"
                            class="text-decoration-none text-dark"
                        >
                            {{--Individual chat card. It is highlighted when selected using Bootstrap utility class--}}
                            <div
                                class="p-4 border border-3 chat-list-item {{ request('chat_id') == $chat->id ? 'bg-success-subtle border-success shadow-sm' : 'bg-white border-success-subtle' }}"
                                data-chat-id="{{ $chat->id }}"
                                data-post-id="{{ $chat->post_id }}"
                                data-user-name="{{ $chat->otherUser()->name ?? 'Usuario' }}"
                                data-post-title="{{ $chat->post->title ?? 'Sin título' }}"
                            >
                                <div class="d-flex align-items-start">
                                    {{--User initial circle avatar--}}
                                    <div
                                        class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 chat-user-initial"
                                        style="width: 48px; height: 48px;"
                                    >
                                        {{ strtoupper(substr($chat->otherUser()->name ?? 'U', 0, 1)) }}
                                    </div>

                                    <div class="flex-grow-1">

                                        {{--User name & unread messages counter--}}
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="mb-1 fw-bold">
                                                {{ $chat->otherUser()->name ?? 'Usuario' }}
                                            </h5>

                                            {{--Unread message badge--}}
                                            @if($chat->unread_count > 0)
                                                <span class="badge bg-danger rounded-pill">
                                                    {{ $chat->unread_count }}
                                                </span>
                                            @endif
                                        </div>
                                        {{--Marketplace post preview--}}
                                        <div class="text-muted mb-2">
                                            {{ Str::limit($chat->post->title ?? 'Sin título', 20) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    {{--Empty search state
                        Displayed when no chat/conversation matches the search query--}}
                    <div
                        id="chatSearchEmptyState"
                        class="d-none px-4 pb-4 text-center text-muted"
                    >
                        <i class="bi bi-search fs-3 d-block mb-2"></i>
                        <p class="mb-0">No se encontraron chats que coincidan con la busqueda.</p>
                    </div>
                </div>

                {{--Right side bar
                    Represents the active messaging/chatting area--}}
                <div class="col-12 col-md-8 d-flex flex-column messages-chat-column">

                    {{--Chat header--}}
                    <div class="p-3 p-md-4 border-bottom border-dark border-2">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-nowrap chat-header-row">

                            {{--Mobile-only button to return to chat list
                                Used when the screen is small enough--}}
                            <button
                                type="button"
                                class="btn btn-outline-secondary d-md-none"
                                id="backToChatsBtn"
                            >
                                <i class="bi bi-arrow-left"></i> Volver
                            </button>

                            {{--Chat user information--}}
                            <div class="d-flex align-items-center min-w-0 flex-grow-1 chat-header-main">

                                {{--Participant initial avatar--}}
                                <div
                                    class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0 chat-user-initial {{ $selectedChat ? '' : 'd-none' }}"
                                    style="width: 48px; height: 48px;"
                                    id="chatHeaderParticipantInitial"
                                >
                                    {{ strtoupper(substr($selectedChat?->otherUser()->name ?? 'U', 0, 1)) }}
                                </div>

                                {{--Participant name--}}
                                <div class="min-w-0 chat-header-text-wrap">
                                    <h4 class="mb-1 fw-bold text-truncate w-100" id="chatHeaderParticipantName">
                                        {{ $selectedChat?->otherUser()->name ?? 'Selecciona un chat' }}
                                    </h4>

                                    {{--Marketplace post summary--}}
                                    <div class="text-muted chat-header-post-summary" id="chatHeaderPostSummary" title="{{ $selectedChat?->post->title ?? '' }}">
                                        {{ $selectedChat?->post->title ?? '' }}
                                    </div>
                                </div>
                            </div>

                            {{--Button that open the marketplace publication details modal--}}
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

                    {{--Chat message container--}}
                    <div id="chatMessagesContainer" class="flex-grow-1 p-4 overflow-auto messages-container">

                        {{--Empty sate that is displayed when no chat is selected or selected chat has no messages--}}
                        <div id="chatEmptyState" class="d-none">
                            <div class="row g-4">
                                <div class="col-12 mb-2">
                                    <div class="card border-0 rounded-0">
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

                    {{--Chat text message input section--}}
                    <div class="p-3 p-md-4 border-top border-dark border-2 chat-input-area d-flex flex-column justify-content-center" style="min-height: 150px;">
                        {{--Message input group that is initially disabled until a chat is selected--}}
                        <div class="input-group chat-message-group border border-dark border-2 rounded-3 overflow-hidden">
                            <input
                                id="chatMessageInput"
                                type="text"
                                class="form-control form-control-lg"
                                placeholder="Selecciona un chat para escribir..."
                                disabled
                            >

                            {{--Send Button that submits the message through the JS file--}}
                            <button id="sendChatMessageBtn" class="btn btn-success" disabled>
                                <i class="bi bi-send"></i>
                            </button>
                        </div>

                        {{--Validation errors that appear when the input does not comply with the correct format and
                             character counter for chat messages--}}
                        <div class="position-relative mt-2" style="min-height: 1.5rem;">

                            {{--Dynamic validation error--}}
                            <div class="invalid-feedback d-block m-0 pe-5" id="chatMessageError"></div>

                            {{--Live character counter--}}
                            <small id="chatMessageCounter" class="text-muted position-absolute end-0 top-0 text-end">
                                0 / 255
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{--Post details modal.
        Resuses the marketplace-style post preview inside the messaging page so the user can
        inspect the related post without having to leave the conversation--}}
    <div class="modal fade" id="postDetailsModal" tabindex="-1" aria-labelledby="postDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow overflow-hidden">

                {{--Modal header where the title is replaced dynamically with the selected post title--}}
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

                {{--Modal body populated by JS after fetching the selected post data--}}
                <div class="modal-body px-4 pt-2 pb-4 post-details-body">

                    {{--Post image carousel.
                         JS injects indicators and image slides.
                         Carousel controls are hidden when only one image exists.--}}
                    <div id="postImagesCarousel" class="carousel slide mb-4">
                        <div class="carousel-indicators" id="postImagesCarouselIndicators"></div>
                        <div
                            class="carousel-inner rounded-4 overflow-hidden border border-dark border-2 post-carousel-inner"
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

                    {{--Optional post description.
                        Hidden by JS when the post has no description--}}
                    <p class="mb-3 text-muted d-none" id="postDetailsDescription"></p>

                    <hr>

                    {{--Post information card, default values are replaced by selected post data--}}
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

                    {{--Restricted owner section. The JS hides this section when the current user is the owner of thepost,
                        preventing a user from rating or reporting themselves--}}
                    <div id="postOwnerRestrictedSection">

                        {{--Seller rating section. The selected star value is stored in hidden input and sent by the JS--}}
                        <div class="mt-4 pb-2">
                        <h5 class="fw-bold mb-3">Calificar Este Vendedor</h5>

                        <label class="form-label fw-semibold">Tu Calificación</label>
                        <div class="d-flex align-items-center gap-3 mb-2">

                            {{--Interactive star icons. JS handles hover, click, keyboard selection, and visual fill state--}}
                            <div class="marketplace-rating-stars text-warning fs-3" id="sellerRatingStars">
                                <i class="bi bi-star rating-star" data-value="1" role="button" tabindex="0" aria-label="1 estrella"></i>
                                <i class="bi bi-star rating-star" data-value="2" role="button" tabindex="0" aria-label="2 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="3" role="button" tabindex="0" aria-label="3 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="4" role="button" tabindex="0" aria-label="4 estrellas"></i>
                                <i class="bi bi-star rating-star" data-value="5" role="button" tabindex="0" aria-label="5 estrellas"></i>
                            </div>

                            {{--Clears the selected rating before submission--}}
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSellerRating">
                                Quitar
                            </button>
                        </div>

                            {{--Hidden rating value used by JS when submitting the seller review--}}
                            <input type="hidden" id="sellerRatingValue" value="0" step="0.5">

                            {{--Text label that changes based on selected rating--}}
                            <p class="text-muted small mb-3" id="sellerRatingText">Selecciona una calificación</p>

                        <div class="d-grid mt-3">
                            {{--Submits seller rating to the backend--}}
                            <button type="button" class="btn btn-lg rounded-3 btn-outline-success" id="submitSellerRatingBtn">
                                Enviar Calificación
                            </button>
                        </div>
                    </div>

                        {{--Opens the report user modal from the post details modal--}}
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
    </div>

    {{--Report user modal. Allows the current user to submit a marketplace report/querella against
        the seller connected to the selected marketplace post--}}
    <div class="modal fade" id="reportUserModal" tabindex="-1" aria-labelledby="reportUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">

                   {{--Report modal header. The report text is updated to include the seller's name--}}
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

                    {{--Custom close buttom. JS uses this buttom to show the cancel confirmation modal
                        if the form has data--}}
                    <button
                        type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        aria-label="Cerrar"
                        id="closeReportModalBtn"
                    ></button>
                </div>

                {{--Report form--}}
                <div class="modal-body px-4 pt-2 pb-4">
                    <form id="reportUserForm" novalidate>

                        {{--Report reason dropdown--}}
                        <div class="mb-3">
                            <label for="reportReason" class="form-label fw-semibold">
                                Razón <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg" id="reportReason" required>
                                <option value="" selected disabled>Seleccionar una razón</option>
                                <option value="Fraude o estafa">Fraude o estafa</option>
                                <option value="Información falsa">Información falsa</option>
                                <option value="Lenguaje ofensivo">Lenguaje ofensivo</option>
                                <option value="Contenido inapropiado">Contenido inapropiado</option>
                                <option value="Otros">Otros</option>
                            </select>
                            <div class="invalid-feedback" id="reportReasonError">Seleciona una razón.</div>
                        </div>

                        {{--Report description, validation of required text, minimum length, and allowed characters
                            is handles in the JS--}}
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

                        {{--Administrative warning explaining what happens after a report is submitted--}}
                        <div class="alert alert-warning rounded-4 mb-0">
                            <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong>
                            Los querellas son revisados por los administradores de mercado.
                            Las querellas válidas pueden resultar en restricciones de cuenta.
                        </div>
                    </form>
                </div>

                {{--Report modal action buttons--}}
                <div class="modal-footer border-0 px-4 pb-4 pt-2">

                    {{--Triggers JS cancellation logic instead of instantly closing the modal--}}
                    <button
                        type="button"
                        class="btn btn-outline-secondary px-4"
                        id="cancelReportBtn"
                    >
                        Cancelar
                    </button>

                    {{--Disabled until the reason and description pass frontend validation--}}
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

    {{--Cancel report confirmation modal, Prevents accidental loss of
        report form data when the user tries to close/cancel after typing a
         reason or description--}}
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

                        {{--Closes only the confirmation modal and lets the user continue editing the report--}}
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Seguir editando
                        </button>

                        {{--Confirms report cancellation, resets its data,a dn returns to post details modal--}}
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

    {{--Marketplace-style success toast container, shown on the bottom left of the screen
        when an action has been confirmed--}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">

        {{--Shown after a seller rating is successfully submitted--}}
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

        {{--Shown after a report/querella is successfully submitted--}}
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
