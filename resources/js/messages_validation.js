/**
 * Bootstrap import and profanity checker import
 *
 * Responsible for modals tooltips and toast configurations
 * and behaviors. Additionally, verifies that the input field
 * does not contain any profanity words.
 * */


import * as bootstrap from 'bootstrap';
import { findProfanity } from './utils/profanity_checker.js';


/**
 * Messages page front-end page initialization behavior controller
 *
 * This file controls the client-side behavior for the MAIKINE messaging page.
 * Responsible:
 * - rendering chat messages and date separators
 * - keeping user-generated message HTML safe through escaping
 * - enabling/disabling the message input depending on selected chat state
 * - validating chat message text, allowed characters, and maximum length
 * - searching/filtering the chat list
 * - loading messages for the selected conversation
 * - managing mobile chat/sidebar behavior
 * - opening and populating marketplace post details from inside a chat
 * - managing seller rating UI and rating submission
 * - validating and submitting user reports/querellas
 * - preventing accidental report form loss through a confirmation modal
 * - displaying Bootstrap success toasts for rating and report actions
 */


/**
 * Escapes user-controlled text before inserting it into the DOM (Document Object Model).
 *
 * This prevents message content from being interpreted as HTML.
 * It is especially important because chat messages are later injected using
 * insertAdjacentHTML inside renderMessage().
 *
 * @param {string} text - Raw text that may contain unsafe characters.
 * @returns {string} Safe HTML-escaped text.
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}


/**
 * Converts a message timestamp into the label shown between message groups.
 *
 * Behavior is as follows:
 *  Returns "Hoy" when the message date is today.
 *  Returns "Ayer" when the message date is yesterday.
 *  Otherwise returns a Spanish Puerto Rico formatted date for when the message was sent.
 *
 * @param {string|Date} dateValue - Date value from the backend or frontend message object.
 * @returns {string} Human-readable date separator text.
 */
function formatDateSeparator(dateValue) {
    const date = new Date(dateValue);
    const today = new Date();
    const yesterday = new Date();


    yesterday.setDate(today.getDate() - 1);


    if (date.toDateString() === today.toDateString()) {
        return 'Hoy';
    }


    if (date.toDateString() === yesterday.toDateString()) {
        return 'Ayer';
    }


    const formattedDate = date.toLocaleDateString('es-PR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });


    return formattedDate
        .replaceAll(' de ', ' ')
        .split(' ')
        .map((part, index) => {
            if (index === 1) {
                return part.charAt(0).toUpperCase() + part.slice(1);
            }


            return part;
        })
        .join(' ');
}


/**
 * Inserts a visual date separator before a message only when the day changes.
 *
 * The last inserted date label is stored in messagesContainer.dataset.lastMessageDate.
 * This avoids repeating "Hoy", "Ayer", or the same full date above every message.
 *
 * @param {HTMLElement} messagesContainer - The message list container.
 * @param {string|Date} dateValue - Date used to decide which separator should be displayed.
 */
function insertDateSeparatorIfNeeded(messagesContainer, dateValue) {
    const separatorText = formatDateSeparator(dateValue);


    if (messagesContainer.dataset.lastMessageDate === separatorText) {
        return;
    }


    messagesContainer.insertAdjacentHTML('beforeend', `
       <div class="text-center text-muted small my-3">
          <div class="d-flex align-items-center my-3">
               <hr class="flex-grow-1">
               <span class="px-3 text-muted fs-6 fw-semibold">${separatorText}</span>
               <hr class="flex-grow-1">
          </div>
       </div>
   `);


    messagesContainer.dataset.lastMessageDate = separatorText;
}


/**
 * Renders one chat message bubble inside the messages container.
 *
 * This function is exported so other scripts, such as the real-time Echo listener,
 * can reuse the same message rendering behavior.
 * The message object supports both backend and frontend naming styles.
 * It expects message content, sender ownership, timestamp, and optional metadata IDs.
 *
 * @param {Object} messageObj - Message data to render.
 * @param {number|string} [messageObj.id] - Message ID from the backend or temporary frontend ID.
 * @param {string} messageObj.message - Message text shown in the bubble.
 * @param {string|Date} [messageObj.createdAt] - Message creation date.
 * @param {string|Date} [messageObj.created_at] - Backend-style creation date fallback.
 * @param {string} messageObj.time - Formatted time shown below the message.
 * @param {number|string} [messageObj.conversationId] - Related chat/conversation ID.
 * @param {number|string} [messageObj.senderId] - User ID of the message sender.
 * @param {boolean} messageObj.isMine - Determines whether the bubble aligns right or left.
 */
export function renderMessage(messageObj) {
    const messagesContainer = document.getElementById('chatMessagesContainer');
    const emptyState = document.getElementById('chatEmptyState');


    if (!messagesContainer) {
        console.error('No se encontró el contenedor de mensajes');
        return;
    }
    if (emptyState) {
        emptyState.classList.add('d-none');
    }


    const isMine = messageObj.isMine === true;


    const alignment = isMine ? 'justify-content-end' : 'justify-content-start';
    const bubbleClass = isMine
        ? 'bg-success-subtle text-dark border border-success-subtle'
        : 'receiver-bubble';
    const timeClass = isMine
        ? 'small mt-1 text-end text-muted'
        : 'small mt-1 text-end text-muted';

    let statusHtml = '';

    if (isMine) {
        if (messageObj.status === 'read') {
            statusHtml = '<i class="bi bi-check-all text-info ms-1"></i>';
        } else {
            statusHtml = '<i class="bi bi-check-all text-muted ms-1"></i>';
        }
    }


    insertDateSeparatorIfNeeded(
        messagesContainer,
        messageObj.createdAt || messageObj.created_at || new Date()
    );


    messagesContainer.insertAdjacentHTML('beforeend', `
           <div class="d-flex ${alignment} mb-3">
               <div
                   class="${bubbleClass} px-3 py-2 rounded-4 shadow-sm"
                   style="max-width: 75%; word-break: break-word;"
                   data-message-id="${messageObj.id ?? ''}"
                   data-conversation-id="${messageObj.conversationId ?? ''}"
                   data-sender-id="${messageObj.senderId ?? ''}"
               >
                   <div>${escapeHtml(messageObj.message)}</div>
                   <div class="${timeClass}">
                    ${messageObj.time}
                    ${statusHtml}
                </div>
               </div>
           </div>
       `);


    messagesContainer.scrollTo({
        top: messagesContainer.scrollHeight,
        behavior: 'smooth'
    });
}




/**
 * Main page initializer.
 *
 * Runs after the DOM is ready so all Blade-rendered elements exist before
 * references and event listeners are registered.
 */
document.addEventListener('DOMContentLoaded', () => {


    /**
     * Chat message input references.
     *
     * These elements control the write-message area: text input, validation error,
     * send button, message display container, empty state, and live counter.
     */
    const input = document.getElementById('chatMessageInput');
    const errorEl = document.getElementById('chatMessageError');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const messagesContainer = document.getElementById('chatMessagesContainer');
    const emptyState = document.getElementById('chatEmptyState');
    const counterEl = document.getElementById('chatMessageCounter');
    const chatMessageGroup = document.getElementById('chatMessageGroup');




    /**
     * Chat context and header references.
     *
     * messagesView contains data-* attributes from Blade such as chat ID,
     * current user ID, and related post ID. The header elements are updated
     * whenever the user selects a different conversation.
     */
    const messagesView = document.getElementById('messagesView');
    const chatHeaderParticipantName = document.getElementById('chatHeaderParticipantName');
    const chatHeaderPostSummary = document.getElementById('chatHeaderPostSummary');
    const chatHeaderParticipantInitial = document.getElementById('chatHeaderParticipantInitial');


    /**
     * Chat search and list references.
     *
     * These elements support filtering the conversation sidebar and showing
     * an empty state when no chat matches the search query.
     */
    const messagesSearchInput = document.getElementById('messagesSearchInput');
    const chatListContainer = document.getElementById('chatListContainer');
    const chatSearchEmptyState = document.getElementById('chatSearchEmptyState');
    const searchMessagesBtn = document.getElementById('searchMessagesBtn');
    const clearMessagesFiltersBtn = document.getElementById('clearMessagesFiltersBtn');


    /**
     * Marketplace post details modal references.
     *
     * These elements are populated when the user opens "Ver Publicación"
     * from inside an active conversation. The data is fetched from /posts/{id}.
     */
    const messagesVolverBtn = document.getElementById('messagesVolverBtn');
    const postDetailsModal = document.getElementById('postDetailsModal');
    const postDetailsModalLabel = document.getElementById('postDetailsModalLabel');
    const postDetailsDescription = document.getElementById('postDetailsDescription');
    const postDetailsRatingStars = document.getElementById('postDetailsRatingStars');
    const postDetailsRatingValue = document.getElementById('postDetailsRatingValue');
    const postDetailsReviewCount = document.getElementById('postDetailsReviewCount');
    const postDetailsPrice = document.getElementById('postDetailsPrice');
    const postDetailsStatus = document.getElementById('postDetailsStatus');
    const postDetailsCondition = document.getElementById('postDetailsCondition');
    const postDetailsSeller = document.getElementById('postDetailsSeller');
    const postDetailsSellerRating = document.getElementById('postDetailsSellerRating');
    const postDetailsCategory = document.getElementById('postDetailsCategory');


    /**
     * Post image carousel references.
     *
     * The carousel is rebuilt dynamically for each marketplace post. Controls
     * are hidden when the post has only one image.
     */
    const postImagesCarouselIndicators = document.getElementById('postImagesCarouselIndicators');
    const postImagesCarouselInner = document.getElementById('postImagesCarouselInner');
    const postImagesCarouselPrev = document.getElementById('postImagesCarouselPrev');
    const postImagesCarouselNext = document.getElementById('postImagesCarouselNext');


    const openChatPostDetailsBtn = document.getElementById('openChatPostDetailsBtn');


    /**
     * Seller rating references.
     *
     * These elements power the interactive 1-5 star rating UI inside the post
     * details modal and submit the selected rating to the backend.
     */
    const submitSellerRatingBtn = document.getElementById('submitSellerRatingBtn');
    const ratingContainer = document.getElementById('sellerRatingStars');
    const ratingInput = document.getElementById('sellerRatingValue');
    const ratingText = document.getElementById('sellerRatingText');
    const clearSellerRating = document.getElementById('clearSellerRating');


    /**
     * Toast references.
     *
     * These toasts give the user confirmation after successful seller rating
     * and report/querella submission.
     */
    const ratingSentToastEl = document.getElementById('ratingSentToast');
    const reportSentToastEl = document.getElementById('reportSentToast');

    function showRequiredFieldsToast(message) {
        const errorToastEl = document.getElementById('errorToast');
        const errorToastMessage = document.getElementById('errorToastMessage');

        if (errorToastEl && errorToastMessage) {
            errorToastMessage.textContent = message;

            bootstrap.Toast.getOrCreateInstance(errorToastEl, {
                delay: 5000
            }).show();
        }
    }



    /**
     * Bootstrap toast instances.
     *
     * Created only when the toast elements exist so the script can safely run
     * even if one of the toast containers is missing from the Blade view.
     */
    const ratingSentToast = ratingSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(ratingSentToastEl)
        : null;


    const reportSentToast = reportSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(reportSentToastEl)
        : null;


    /**
     * Report/querella modal references.
     *
     * These elements validate the report reason and description, track whether
     * the form has unsaved data, handle cancel confirmation, and submit reports
     * to the backend for the marketplace management page.
     */
    const submitReportBtn = document.getElementById('submitReportBtn');
    const submitReportBtnWrapper = document.getElementById('submitReportBtnWrapper');
    const reportUserForm = document.getElementById('reportUserForm');
    const reportReason = document.getElementById('reportReason');
    const reportReasonError = document.getElementById('reportReasonError');
    const reportDescription = document.getElementById('reportDescription');
    const reportDescriptionError = document.getElementById('reportDescriptionError');
    const reportUserModal = document.getElementById('reportUserModal');
    const cancelReportBtn = document.getElementById('cancelReportBtn');
    const closeReportModalBtn = document.getElementById('closeReportModalBtn');
    const cancelReportConfirmModal = document.getElementById('cancelReportConfirmModal');
    const confirmCancelReport = document.getElementById('confirmCancelReport');
    const reportUserText = document.getElementById('reportUserText');
    const reportMissingFieldsAlert = document.getElementById('reportMissingFieldsAlert');
    const reportMissingFieldsList = document.getElementById('reportMissingFieldsList');



    /**
     * Safety guard.
     *
     * If this script is loaded on a page without the core messaging elements,
     * the script exits early to prevent null reference errors.
     */
    if (!input || !errorEl || !sendBtn || !messagesContainer) return;


    /**
     * Prevents the message input from receiving focus when no chat is selected.
     *
     * This keeps the disabled/no-chat state consistent even if the user tries
     * to focus the input manually.
     */
    input.addEventListener('focus', () => {
        if (!messagesView?.dataset.chatId) {
            input.blur();
        }
    });


    /**
     * Validation constants.
     *
     * MAX_LENGTH controls the input chat message length.
     * MAX_REPORT_LENGTH controls report descriptions maximum length.
     * allowedTextRegex controls chat message characters, only allowing
     * letters, numbers, spaces, periods, commas, hyphens, at signs,
     * question marks, exclamation marks, dollar signs, and pound signs.
     * allowedReportRegex controls report description characters, only allowing
     * letters, numbers, commas, periods, hyphens, and spaces.
     */
    const MAX_LENGTH = 255;
    const MAX_REPORT_LENGTH = 500;
    const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s@.,\-¿?¡!#$]+$/;
    const allowedReportRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
    const chatId = messagesView?.dataset.chatId;


    /**
     * Validates the chat message against the profanity list.
     *
     * Empty values are treated as valid because the required-message validation
     * handles empty input separately. When a profanity word is detected, a blocking
     * validation error is applied so the message cannot be sent by button or Enter.
     *
     * @param {boolean} showError - Whether to display the profanity error message.
     * @returns {boolean} True when the message does not contain prohibited words.
     */
    function validateMessageProfanity(showError = true) {
        const value = input.value.trim();


        if (!value) {
            clearValidationError('profanity');
            return true;
        }


        const matchedWord = findProfanity(value);


        if (matchedWord) {
            if (showError) {
                setValidationError('El mensaje contiene lenguaje inapropiado.', 'profanity');
            }
            return false;
        }


        clearValidationError('profanity');
        return true;
    }


    /**
     * Enables or disables the chat input depending on whether a chat is selected.
     *
     * Without a chat ID, the input remains disabled and the placeholder instructs
     * the user to select a conversation first. Once a chat exists, the input is
     * enabled and the send button is recalculated using validation state.
     */
    function updateChatInputState() {
        const hasChat = !!messagesView?.dataset.chatId;


        if (!hasChat) {
            input.disabled = true;
            input.placeholder = 'Selecciona un chat para escribir...';
            sendBtn.disabled = true;
        } else {
            input.disabled = false;
            input.placeholder = 'Escriba un mensaje...';
            updateSendButtonState();
        }
    }


    /**
     * Report modal state flags.
     *
     * isReportDirty tracks whether the user typed or selected anything in the report form.
     * allowReportClose temporarily bypasses the dirty-form warning during intentional closes.
     * reportedUserId stores the seller/user ID currently being reported.
     */
    let isReportDirty = false;
    let allowReportClose = false;
    let isSubmittingReport = false;
    let reportedUserId = null;


    /**
     * Preserves marketplace return context when the user leaves the messages page.
     *
     * The marketplace page can read marketplaceReturnPostId from sessionStorage
     * and reopen or scroll back to the post the user came from.
     */
    if (messagesVolverBtn) {
        messagesVolverBtn.addEventListener('click', () => {
            const returnPostId = messagesVolverBtn.dataset.returnPostId;


            if (returnPostId) {
                sessionStorage.setItem('marketplaceReturnPostId', returnPostId);
            } else {
                sessionStorage.removeItem('marketplaceReturnPostId');
            }
        });
    }


    if (!messagesView?.dataset.chatId) {
        chatHeaderParticipantInitial?.classList.add('d-none');
    }


    if (!messagesView?.dataset.chatId && openChatPostDetailsBtn) {
        openChatPostDetailsBtn.disabled = true;
    }


    /**
     * Truncates long display text and appends an (...).
     *
     * Used mainly for post titles in the chat header so long publication names
     * do not break the layout.
     *
     * @param {string} text - Text to shorten.
     * @param {number} maxLength - Maximum visible characters before truncation.
     * @returns {string} Original or shortened text.
     */
    function truncateText(text, maxLength = 40) {
        if (!text) return '';
        const trimmed = String(text).trim();
        if (trimmed.length <= maxLength) return trimmed;
        return trimmed.slice(0, maxLength).trimEnd() + '...';
    }


    /**
     * Applies a blocking validation error to the chat input.
     *
     * The error type is stored in errorEl.dataset.errorType so other validation
     * methods know whether the send button should remain disabled.
     *
     * @param {string} message - Message shown below the input.
     * @param {string} type - Internal error category, such as required or characters.
     */
    function setValidationError(message, type) {
        input.classList.add('is-invalid');


        chatMessageGroup.classList.remove('border-dark');
        chatMessageGroup.classList.add('border-danger');


        errorEl.textContent = message;
        errorEl.dataset.errorType = type;


        sendBtn.disabled = true;
    }


    /**
     * Clears a validation error only if it matches the provided error type.
     *
     * This prevents one validator from accidentally clearing an error created
     * by another validator.
     *
     * @param {string} type - Error type that should be cleared.
     */
    function clearValidationError(type) {
        if (errorEl.dataset.errorType === type) {
            input.classList.remove('is-invalid');


            chatMessageGroup.classList.remove('border-danger');
            chatMessageGroup.classList.add('border-dark');


            errorEl.textContent = '';
            delete errorEl.dataset.errorType;
        }
    }


    /**
     * Clears every chat input validation state.
     *
     * Used after sending a message or resetting the input so the next message
     * starts with a clean validation state.
     */
    function clearAllValidationErrors() {
        input.classList.remove('is-invalid');


        chatMessageGroup.classList.remove('border-danger');
        chatMessageGroup.classList.add('border-dark');


        errorEl.textContent = '';
        delete errorEl.dataset.errorType;
    }


    /**
     * Validates chat message characters against allowedTextRegex.
     *
     * @param {boolean} showError - Whether to show the user-facing error.
     * @returns {boolean} True when the message only contains accepted characters.
     */
    function validateAllowedCharacters(showError = true) {
        const value = input.value;


        if (!value) {
            clearValidationError('characters');
            return true;
        }


        if (!allowedTextRegex.test(value)) {
            if (showError) {
                setValidationError(
                    'Solo se permiten letras, números, espacios, puntos, signos de exclamación e interrogativo, signos de dólar, signos numerales, comas, arrobas y guiones.',
                    'characters'
                );
            }
            return false;
        }


        clearValidationError('characters');
        return true;
    }




    /**
     * Validates and limits the chat message length.
     *
     * If the value exceeds MAX_LENGTH, the input is sliced back to the allowed
     * length and a maximum-length message is displayed. Reaching exactly
     * MAX_LENGTH shows feedback but still allows the message to be submitted.
     *
     * @param {boolean} showError - Whether to display maximum-length feedback.
     * @returns {boolean} Always true because the input is normalized instead of blocked.
     */
    function validateMaxLength(showError = true) {
        const exceededLimit = input.value.length > MAX_LENGTH;


        if (exceededLimit) {
            input.value = input.value.slice(0, MAX_LENGTH);
        }


        const currentLength = input.value.length;


        if (currentLength === MAX_LENGTH) {
            if (showError) {
                input.classList.add('is-invalid');


                if (exceededLimit) {
                    errorEl.textContent =
                        `Has alcanzado el máximo de ${MAX_LENGTH} caracteres. No puedes escribir más.`;
                } else {
                    errorEl.textContent =
                        `Has alcanzado el máximo de ${MAX_LENGTH} caracteres, puedes aún someter esa cantidad.`;
                }


                errorEl.dataset.errorType = 'maxlength-limit';
            }


            return true;
        }


        clearValidationError('maxlength-limit');
        return true;
    }


    /**
     * Enables or disables the send button based on the current input state.
     *
     * The button is disabled when the message is empty, when a blocking validation
     * error exists, or while a message is already being sent. The maxlength-limit
     * state is treated as non-blocking because a 255-character message can still
     * be submitted.
     */
    function updateSendButtonState() {
        const trimmedValue = input.value.trim();
        const hasBlockingError =
            errorEl.dataset.errorType === 'required' ||
            errorEl.dataset.errorType === 'characters' ||
            errorEl.dataset.errorType === 'profanity';


        sendBtn.disabled = !trimmedValue || hasBlockingError || isSending;
    }


    /**
     * Updates the live chat message character counter.
     * The counter turns red when the user reaches the maximum message length.
     */
    function updateCounter() {
        if (!counterEl) return;


        const currentLength = input.value.length;
        counterEl.textContent = `${currentLength} / ${MAX_LENGTH}`;


        if (currentLength >= MAX_LENGTH) {
            counterEl.classList.remove('text-muted');
            counterEl.classList.add('text-danger');
        } else {
            counterEl.classList.remove('text-danger');
            counterEl.classList.add('text-muted');
        }
    }






    /**
     * Filters the chat sidebar using the current search query.
     *
     * The function checks each chat item's text or dataset search text and hides
     * items that do not match. If none match, the empty search state is displayed.
     */
    function filterChats() {
        if (!messagesSearchInput || !chatListContainer) return;


        const query = messagesSearchInput.value.trim().toLowerCase();
        const items = chatListContainer.querySelectorAll('.chat-list-item');




        let visibleCount = 0;


        items.forEach((item) => {
            const haystack = (item.dataset.searchText || item.textContent || '').toLowerCase();
            const matches = !query || haystack.includes(query);


            item.classList.toggle('d-none', !matches);


            if (matches) {
                visibleCount += 1;
            }
        });


        if (chatSearchEmptyState) {
            chatSearchEmptyState.classList.toggle('d-none', visibleCount !== 0);
        }
    }


    /**
     * Moves the active conversation card to the top of the conversations sidebar.
     *
     * This is used when the page loads from a marketplace "Enviar Mensaje" redirect
     * and after sending a message, so the most recently active conversation appears
     * first without requiring a full page refresh.
     *
     * @param {number|string} chatId - Chat/conversation ID that should be moved to the top.
     */
    function moveActiveChatToTop(chatId = messagesView?.dataset.chatId) {
        if (!chatListContainer || !chatId) return;


        const activeChatItem = chatListContainer.querySelector(
            `.chat-list-item[data-chat-id="${chatId}"]`
        );


        const activeChatLink = activeChatItem?.closest('a');


        if (!activeChatLink) return;


        chatListContainer.prepend(activeChatLink);
    }


    moveActiveChatToTop();


    /**
     * Builds Bootstrap star icon HTML from a numeric seller rating.
     *
     * @param {number|string} value - Rating value to convert into star icons.
     * @returns {string} HTML containing full, half, and empty star icons.
     */
    function buildStarsHTML(value) {
        const rating = Number(value) || 0;
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);


        let starsHTML = '';


        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<i class="bi bi-star-fill"></i>';
        }


        if (hasHalfStar) {
            starsHTML += '<i class="bi bi-star-half"></i>';
        }


        for (let i = 0; i < emptyStars; i++) {
            starsHTML += '<i class="bi bi-star"></i>';
        }


        return starsHTML;
    }


    /**
     * Renders the post details image carousel.
     *
     * It clears any previous post images, inserts carousel indicators and slides,
     * activates the first image, and hides navigation arrows when there is only
     * one image.
     *
     * @param {string[]} images - Array of image URLs for the selected post.
     */
    function renderPostDetailsCarousel(images = []) {
        if (!postImagesCarouselIndicators || !postImagesCarouselInner) return;


        postImagesCarouselIndicators.innerHTML = '';
        postImagesCarouselInner.innerHTML = '';


        const validImages = Array.isArray(images) && images.length ? images : [];


        validImages.forEach((image, index) => {
            postImagesCarouselIndicators.insertAdjacentHTML(
                'beforeend',
                `
               <button
                   type="button"
                   data-bs-target="#postImagesCarousel"
                   data-bs-slide-to="${index}"
                   class="${index === 0 ? 'active' : ''}"
                   ${index === 0 ? 'aria-current="true"' : ''}
                   aria-label="Slide ${index + 1}"
               ></button>
               `
            );


            postImagesCarouselInner.insertAdjacentHTML(
                'beforeend',
                `
               <div class="carousel-item ${index === 0 ? 'active' : ''} post-carousel-item">
                   <div class="carousel-image-box">
                       <img
                           src="${image}"
                           alt="Imagen ${index + 1}"
                           class="post-carousel-img"
                       >
                   </div>
               </div>
               `
            );
        });


        const shouldHideControls = validImages.length <= 1;


        if (postImagesCarouselPrev) {
            postImagesCarouselPrev.classList.toggle('d-none', shouldHideControls);
        }


        if (postImagesCarouselNext) {
            postImagesCarouselNext.classList.toggle('d-none', shouldHideControls);
        }
    }


    /**
     * Populates the marketplace post details modal from a fetched post object.
     *
     * Updates the modal title, description, price, status, condition,
     * seller name, seller rating, category, report text, and image carousel. It also
     * hides the rating/report section when the current user owns the post.
     *
     * @param {Object} post - Marketplace post returned from /posts/{id}.
     */
    function populatePostDetailsModal(post) {
        if (!post) return;
        if (post.user?.id) {
            reportedUserId = post.user.id;
        }


        const currentUserId = Number(messagesView?.dataset.currentUserId || 0);
        const sellerId = Number(post.user?.id || 0);
        const isOwner = currentUserId === sellerId;


        const sellerName =
            post.user?.name ||
            `${post.user?.first_name ?? ''} ${post.user?.last_name ?? ''}`.trim() ||
            'Usuario';


        const sellerRating = Number(post.rating ?? post.user?.average_rating ?? 0);
        const sellerReviews = Number(post.reviews ?? post.user?.reviews_count ?? 0);


        const restrictedSection = document.getElementById('postOwnerRestrictedSection');


        if (restrictedSection) {
            restrictedSection.classList.toggle('d-none', isOwner);
        }


        if (postDetailsModalLabel) {
            postDetailsModalLabel.textContent = post.title || 'Detalle de la publicación';
        }


        if (postDetailsDescription) {
            const description = (post.description || '').trim();


            if (description) {
                postDetailsDescription.textContent = description;
                postDetailsDescription.classList.remove('d-none');
            } else {
                postDetailsDescription.textContent = '';
                postDetailsDescription.classList.add('d-none');
            }
        }


        if (postDetailsRatingStars) {
            postDetailsRatingStars.innerHTML = buildStarsHTML(sellerRating);
        }


        if (postDetailsRatingValue) {
            postDetailsRatingValue.textContent = sellerRating.toFixed(1);
        }


        if (postDetailsReviewCount) {
            postDetailsReviewCount.textContent = `(${sellerReviews})`;
        }


        if (postDetailsPrice) {
            postDetailsPrice.textContent = `$${post.cost || '0.00'}`;
        }


        if (postDetailsSeller) {
            postDetailsSeller.textContent = sellerName;
        }


        if (reportUserText) {
            reportUserText.textContent = `Reportar a ${sellerName} por comportamiento sospechoso`;
        }


        if (postDetailsSellerRating) {
            postDetailsSellerRating.innerHTML =
                `<i class="bi bi-star-fill text-warning me-1"></i> ${sellerRating.toFixed(1)} <span class="text-muted">(${sellerReviews} reseñas)</span>`;
        }


        if (postDetailsStatus) {
            postDetailsStatus.textContent = post.status || 'Disponible';
            postDetailsStatus.className = 'label-badge badge-available';
        }


        if (postDetailsCondition) {
            postDetailsCondition.textContent = post.condition || 'Sin especificar';
            postDetailsCondition.className = 'label-badge badge-available';
        }


        if (postDetailsCategory) {
            postDetailsCategory.textContent = post.category || 'Sin categoría';
            postDetailsCategory.className = 'label-badge badge-available';
        }


        const images = [
            post.photo_1_url,
            post.photo_2_url,
            post.photo_3_url
        ].filter(Boolean).map(img => '/storage/' + img);


        renderPostDetailsCarousel(images);
    }


    /**
     * Initializes the interactive seller rating stars.
     *
     * Supports mouse hover, click selection, keyboard selection with Enter/Space,
     * clearing the rating, and restoring the selected visual state on mouse leave.
     */
    function initializeSellerRating() {
        if (!ratingContainer || !ratingInput || !ratingText) return;


        const stars = ratingContainer.querySelectorAll('.rating-star');


        const ratingLabels = {
            0: 'Selecciona una calificación',
            1: 'Malo',
            2: 'Regular',
            3: 'Bueno',
            4: 'Muy Bueno',
            5: 'Excelente'
        };


        /**
         * Updates star icons style and rating label for a given value.
         *
         * @param {number} value - Selected or hovered rating value.
         */
        function paintStars(value) {
            stars.forEach((star) => {
                const starValue = Number(star.dataset.value);


                star.classList.remove('bi-star', 'bi-star-fill');


                if (starValue <= value) {
                    star.classList.add('bi-star-fill');
                } else {
                    star.classList.add('bi-star');
                }
            });


            ratingText.textContent = ratingLabels[value] || ratingLabels[0];
        }


        stars.forEach((star) => {
            star.addEventListener('mouseenter', function () {
                paintStars(Number(this.dataset.value));
            });


            star.addEventListener('click', function () {
                const value = Number(this.dataset.value);
                ratingInput.value = value;
                paintStars(value);
            });


            star.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    const value = Number(this.dataset.value);
                    ratingInput.value = value;
                    paintStars(value);
                }
            });
        });


        ratingContainer.addEventListener('mouseleave', () => {
            paintStars(Number(ratingInput.value || 0));
        });


        if (clearSellerRating) {
            clearSellerRating.addEventListener('click', () => {
                ratingInput.value = 0;
                paintStars(0);
            });
        }


        paintStars(Number(ratingInput.value || 0));
    }


    /**
     * Validates the required report reason dropdown.
     * The reason is required.
     *
     * @param {boolean} showError - Whether to apply visible validation feedback.
     * @returns {boolean} True when a reason is selected.
     */
    function validateReportReason(showError = true) {
        if (!reportReason || !reportReasonError) return true;


        const isValid = !!reportReason.value;


        if (showError) {
            if (!isValid) {
                reportReason.classList.add('is-invalid');
                reportReasonError.textContent = 'Selecciona una razón para reportar al usuario.';
            } else {
                reportReason.classList.remove('is-invalid');
                reportReasonError.textContent = '';
            }
        }


        return isValid;
    }

    function updateSelectPlaceholderColor(select, placeholderValue = '') {
        if (!select) return;

        if (select.value === placeholderValue) {
            select.classList.add('text-muted');
        } else {
            select.classList.remove('text-muted');
        }
    }

    updateSelectPlaceholderColor(reportReason, '');

    reportReason?.addEventListener('change', () => {
        updateSelectPlaceholderColor(reportReason, '');
    });


    /**
     * Validates the report description field.
     *
     * The description is a required field that only uses allowedReportRegex characters,
     * has a minimum of 10 characters, and a maximum of MAX_REPORT_LENGTH characters.
     *
     * @param {boolean} showError - Whether to display validation feedback.
     * @returns {boolean} True when the description is valid.
     */
    function validateReportDescription(showError = true) {
        if (!reportDescription || !reportDescriptionError) return true;


        const value = reportDescription.value.trim();


        if (showError) {
            reportDescription.classList.remove('is-invalid');
            reportDescriptionError.textContent = '';
        }


        if (!value) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción es obligatoria.';
            }
            return false;
        }


        if (!allowedReportRegex.test(value)) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    'Solo se permiten letras, números, espacios, puntos, comas y guiones.';
            }
            return false;
        }


        if (value.length < 10) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    'La descripción debe tener al menos 10 caracteres.';
            }
            return false;
        }


        if (value.length > MAX_REPORT_LENGTH) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    'La descripción no puede exceder 500 caracteres.';
            }
            return false;
        }


        return true;
    }


    /**
     * Enables the report submit button only when all report fields are valid.
     *
     * Validation is checked silently so the button state can update while typing
     * without forcing errors before the user finishes.
     */
    function updateReportButtonState() {
        if (!submitReportBtn) return;


        const isReady =
            validateReportReason(false) &&
            validateReportDescription(false);


        submitReportBtn.disabled = !isReady;
    }

    function validateReportForm(showErrors = true) {
        let valid = true;

        if (!validateReportReason(showErrors)) valid = false;
        if (!validateReportDescription(showErrors)) valid = false;

        if (submitReportBtn) submitReportBtn.disabled = !valid;

        return valid;
    }

    function getReportMissingFields() {
        const missing = [];

        if (!reportReason || !reportReason.value) {
            missing.push('Razón de la querella');
        }

        if (!reportDescription || !reportDescription.value.trim()) {
            missing.push('Descripción de la querella');
        }

        return missing;
    }

    function showReportMissingFieldsIndicator() {
        const valid = validateReportForm(true);

        if (valid) {
            reportMissingFieldsAlert?.classList.add('d-none');
            submitReportBtn?.click();
            return;
        }

        const missing = getReportMissingFields();

        if (reportMissingFieldsAlert && reportMissingFieldsList) {
            reportMissingFieldsList.innerHTML = '';

            missing.forEach((fieldName) => {
                const item = document.createElement('li');
                item.textContent = fieldName;
                reportMissingFieldsList.appendChild(item);
            });

            reportMissingFieldsAlert.classList.remove('d-none');
        }

        const firstInvalidField = reportUserForm?.querySelector('.is-invalid');

        if (firstInvalidField) {
            firstInvalidField.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
        }

        showRequiredFieldsToast(
            'Completa los campos requeridos antes de enviar la querella.'
        );
    }

    submitReportBtnWrapper?.addEventListener('click', (event) => {
        if (submitReportBtn?.disabled) {
            event.preventDefault();

            showReportMissingFieldsIndicator();
        }
    });


    /**
     * Clears all report modal validation styles and messages.
     */
    function resetReportValidation() {
        if (reportReason) {
            reportReason.classList.remove('is-invalid');
        }


        if (reportDescription) {
            reportDescription.classList.remove('is-invalid');
        }


        if (reportDescriptionError) {
            reportDescriptionError.textContent = '';
        }


        if (reportReasonError) {
            reportReasonError.textContent = '';
        }
    }


    /**
     * Resets the report form, dirty-state flags, validation messages, and submit button.
     */
    function resetReportForm() {
        if (reportUserForm) {
            reportUserForm.reset();
            updateSelectPlaceholderColor(reportReason, '');
        }


        isReportDirty = false;
        allowReportClose = false;


        resetReportValidation();
        updateReportButtonState();
    }


    /**
     * Updates whether the report form has unsaved user input.
     *
     * This is used to decide whether the confirmation modal should appear when
     * the user attempts to cancel/close the report modal.
     */
    function updateReportDirtyState() {
        const hasReason = !!(reportReason && reportReason.value);
        const hasDescription = !!(reportDescription && reportDescription.value.trim() !== '');


        isReportDirty = hasReason || hasDescription;
    }


    /**
     * Loads all messages for a selected chat from the backend.
     *
     * It clears previously rendered messages, restores the empty state element,
     * resets date-separator tracking, renders each fetched message, and customizes
     * the empty-state text depending on whether a chat is selected but empty.
     *
     * @param {number|string} chatId - Conversation ID to load.
     */
    async function loadMessages(chatId) {
        try {
            const response = await fetch(`/messages/${chatId}`);
            const messages = await response.json();
            messagesContainer.innerHTML = '';


            if (emptyState) {
                messagesContainer.appendChild(emptyState);
            }


            delete messagesContainer.dataset.lastMessageDate;


            if (!messages.length) {
                if (emptyState) {
                    emptyState.classList.remove('d-none');


                    const emptyTitle = emptyState.querySelector('h4');
                    const emptyText = emptyState.querySelector('p');


                    if (chatId) {
                        if (emptyTitle) {
                            emptyTitle.textContent = 'Aquí aparecerán tus mensajes.';
                        }


                        if (emptyText) {
                            emptyText.textContent =
                                'Para comenzar la conversación, escribe un mensaje abajo.';
                        }
                    } else {
                        if (emptyTitle) {
                            emptyTitle.textContent = 'No hay mensajes aún.';
                        }


                        if (emptyText) {
                            emptyText.textContent =
                                'Selecciona un chat para comenzar la conversación.';
                        }
                    }
                }


                return;
            }


            if (emptyState) {
                emptyState.classList.add('d-none');
            }




            messages.forEach(msg => {
                renderMessage({
                    id: msg.id,
                    message: msg.content,
                    createdAt: msg.created_at,
                    time: new Date(msg.created_at).toLocaleTimeString([], {
                        hour: 'numeric',
                        minute: '2-digit'
                    }),
                    senderId: msg.sender_id,
                    conversationId: chatId,
                    isMine: msg.isMine,
                    status: msg.status
                });
            });


        } catch (error) {
            console.error('ERROR:', error);
        }
    }


    /**
     * Handles attempts to close/cancel the report modal.
     *
     * If the form has no unsaved input, it closes immediately and returns to the
     * post details modal. If the form has data, it opens the confirmation modal
     * to prevent accidental loss.
     */
    function tryCloseReportModal() {
        if (!reportUserModal) return;


        updateReportDirtyState();


        const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);


        if (!isReportDirty) {
            allowReportClose = true;
            reportModalInstance.hide();


            if (postDetailsModal) {
                setTimeout(() => {
                    const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show();
                }, 150);
            }
            return;
        }


        if (cancelReportConfirmModal) {
            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
            confirmModal.show();
        }
    }


    /**
     * Opens the post details modal for the post connected to the active chat.
     *
     * The post ID comes from messagesView.dataset.postId, which is set by Blade
     * on initial load and updated when the user selects a different conversation.
     */
    if (openChatPostDetailsBtn) {
        openChatPostDetailsBtn.addEventListener('click', async () => {
            const postId = Number(messagesView.dataset.postId);
            if (!postId) return;
            console.log('FINAL USER ID:', reportedUserId);
            try {
                const response = await fetch(`/posts/${postId}`);
                const post = await response.json();


                populatePostDetailsModal(post);


                if (postDetailsModal) {
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    modalInstance.show();
                }


            } catch (error) {
                console.error('Error cargando post:', error);
            }
        });
    }


    /**
     * Chat search and chat selection event binding.
     *
     * Controls:
     * - enabling/disabling the search button
     * - applying chat filters
     * - clearing chat filters
     * - selecting a chat from the sidebar
     * - updating unread counters
     * - updating mobile layout
     * - loading selected chat messages
     * - subscribing to the selected real-time chat channel
     */
    if (messagesSearchInput) {
        messagesSearchInput.addEventListener('input', () => {
            if (searchMessagesBtn) {
                searchMessagesBtn.disabled = messagesSearchInput.value.trim() === '';
            }
        });


        if (searchMessagesBtn) {
            searchMessagesBtn.addEventListener('click', filterChats);
        }


        if (clearMessagesFiltersBtn) {
            clearMessagesFiltersBtn.addEventListener('click', () => {
                messagesSearchInput.value = '';
                searchMessagesBtn.disabled = true;
                filterChats();
            });
        }




        /**
         * Tracks the current Echo channel so the previous channel can be left
         * before subscribing to a newly selected chat.
         */
        let currentChannel = null;


        document.querySelectorAll('.chat-list-item').forEach(item => {


            if (item.dataset.bound) return;
            item.dataset.bound = true;


            item.addEventListener('click', async (e) => {
                e.preventDefault();


                document.querySelectorAll('.chat-list-item').forEach(chat => {
                    chat.classList.remove('bg-success-subtle', 'border-success', 'shadow-sm');
                    chat.classList.add('bg-white', 'border-success-subtle');
                });


                item.classList.remove('bg-white', 'border-success-subtle');
                item.classList.add('bg-success-subtle', 'border-success', 'shadow-sm');
                const unreadBadge = item.querySelector('.badge.bg-danger');
                const itemUnreadCount = Number(unreadBadge?.textContent?.trim() || 0);


                /**
                 * Accumulates the number of unread messages cleared while opening chats.
                 *
                 * Some browsers, such as Opera, may restore a cached version of the previous
                 * page after using the browser back button. When this happens, the navbar
                 * unread badge can temporarily display outdated values even though the backend
                 * already marked those messages as read.
                 *
                 * The accumulated count is stored in sessionStorage so the global layout header
                 * can synchronize and correct the unread badge after the cached page is restored.
                 */
                if (itemUnreadCount > 0) {
                    const previousReadCount = Number(sessionStorage.getItem('maikineReadMessagesCount') || 0);
                    sessionStorage.setItem('maikineReadMessagesCount', String(previousReadCount + itemUnreadCount));
                }

                if (unreadBadge) {
                    unreadBadge.remove();
                }


                const navbarChatBadge = document.getElementById('miChatsUnreadBadge');


                if (navbarChatBadge) {
                    const currentCount = Number(navbarChatBadge.textContent.trim() || 0);
                    const newCount = Math.max(currentCount - itemUnreadCount, 0);


                    if (newCount > 0) {
                        navbarChatBadge.textContent = newCount;
                    } else {
                        navbarChatBadge.remove();
                    }
                }


                const isMobile = window.innerWidth < 768;


                document.querySelectorAll('.chat-list-item').forEach(chat => {
                    chat.classList.remove('d-none-selected-mobile');
                });


                if (isMobile) {
                    item.classList.add('d-none-selected-mobile');


                    const sidebar = document.querySelector('.messages-sidebar');
                    const chatColumn = document.querySelector('.messages-chat-column');


                    if (sidebar) {
                        sidebar.classList.add('d-none');
                    }


                    if (chatColumn) {
                        chatColumn.classList.remove('mobile-hidden');
                    }
                }


                const chatId = item.dataset.chatId;
                const postId = item.dataset.postId;
                const userName = item.dataset.userName;
                const postTitle = item.dataset.postTitle;


                chatHeaderParticipantName.textContent = userName;
                chatHeaderPostSummary.textContent = truncateText(postTitle, 60);
                chatHeaderPostSummary.title = postTitle;
                chatHeaderParticipantInitial.textContent = userName.charAt(0).toUpperCase();
                chatHeaderParticipantInitial.classList.remove('d-none');


                if (!chatId) {
                    console.error('No hay chat_id');
                    return;
                }


                if (currentChannel) {
                    window.Echo.leave(currentChannel);
                }


                messagesView.dataset.chatId = chatId;
                updateChatInputState();
                messagesView.dataset.postId = postId;


                if (openChatPostDetailsBtn) {
                    openChatPostDetailsBtn.disabled = false;
                    openChatPostDetailsBtn.dataset.postId = postId || '';
                }


                messagesView.dataset.chatId = chatId;


                await loadMessages(chatId);


                if (window.subscribeToChat) {
                    window.subscribeToChat(chatId);
                }
                currentChannel = `chat.${chatId}`;


                console.log('Chat seleccionado:', chatId);
                console.log('Post relacionado:', postId);
            });
        });
    }


    /**
     * Triggers chat filtering when the user presses the Enter key
     * inside the conversations search input.
     *
     * Prevents the default Enter behavior and applies the same
     * filtering logic used by the search button.
     */
    messagesSearchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();


            if (searchMessagesBtn) {
                searchMessagesBtn.disabled = messagesSearchInput.value.trim() === '';
            }


            filterChats();
        }
    });




    /**
     * Mobile back button reference.
     *
     * Used to return from the active chat column back to the chat sidebar on
     * smaller screens.
     */
    const backBtn = document.getElementById('backToChatsBtn');


    if (backBtn) {
        backBtn.addEventListener('click', () => {
            const sidebar = document.querySelector('.messages-sidebar');
            const chatColumn = document.querySelector('.messages-chat-column');


            // Shows the sidebar once again
            if (sidebar) {
                sidebar.classList.remove('d-none');
            }


            // Hides the chat column on mobile
            if (window.innerWidth < 768 && chatColumn) {
                chatColumn.classList.add('mobile-hidden');
            }


            // Restore all chat items
            document.querySelectorAll('.chat-list-item').forEach(chat => {
                chat.classList.remove('d-none-selected-mobile');
            });
        });
    }


    /**
     * Initial mobile state.
     *
     * If no chat is selected on a small screen, the chat column starts hidden
     * so the user begins on the conversation list.
     */
    if (!chatId && window.innerWidth < 768) {
        const chatColumn = document.querySelector('.messages-chat-column');
        if (chatColumn) {
            chatColumn.classList.add('mobile-hidden');
        }
    }


    /**
     * Prevents typing beyond the chat message maximum length.
     *
     * This catches direct typing before the browser applies the new character.
     * When the next input would exceed MAX_LENGTH, the change is blocked, the
     * current value is kept within the limit, and the non-blocking maxlength
     * feedback is shown.
     */
    input.addEventListener('beforeinput', (event) => {
        const selectionLength = input.selectionEnd - input.selectionStart;
        const incomingText = event.data || '';


        const nextLength = input.value.length - selectionLength + incomingText.length;


        if (nextLength > MAX_LENGTH) {
            event.preventDefault();


            input.value = input.value.slice(0, MAX_LENGTH);


            input.classList.add('is-invalid');
            chatMessageGroup.classList.remove('border-dark');
            chatMessageGroup.classList.add('border-danger');


            errorEl.textContent =
                `Has alcanzado el máximo de ${MAX_LENGTH} caracteres. No puedes escribir más.`;
            errorEl.dataset.errorType = 'maxlength-limit';


            updateCounter();
            updateSendButtonState();
        }
    });


    /**
     * Revalidates the chat input on every change.
     *
     * This keeps the error message, send button, and character counter in sync
     * while the user types.
     */
    input.addEventListener('input', () => {
        if (input.value.trim()) {
            clearValidationError('required');
        }




        validateMaxLength(true);
        validateAllowedCharacters(true);
        validateMessageProfanity(true);
        updateSendButtonState();
        updateCounter();
    });


    /**
     * Cleans up non-blocking chat validation feedback when the input loses focus.
     *
     * If the current text is valid, the visible invalid style is removed so the
     * form does not look broken after the user leaves the field.
     */
    input.addEventListener('blur', () => {
        if (
            input.value.trim() &&
            input.value.length <= MAX_LENGTH &&
            validateAllowedCharacters(false)
        ) {


            if (errorEl.dataset.errorType !== 'profanity') {
                input.classList.remove('is-invalid');
                errorEl.textContent = '';
                delete errorEl.dataset.errorType;
            }
        }


        updateSendButtonState();
    });


    /**
     * Binds Enter-to-send only once.
     *
     * dataset.bound prevents duplicate keydown listeners if the script is re-run
     * by navigation or partial reload behavior.
     */
    if (!input.dataset.bound) {
        input.dataset.bound = 'true';


        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                event.stopPropagation();


                handleSendMessage();
            }
        });
    }




    /**
     * Prevents duplicate message submissions.
     *
     * While a fetch('/messages') request is in progress, additional sends are ignored.
     */
    let isSending = false;




    /**
     * Validates the full chat message before submission.
     *
     * This final validation is shared by the Enter key and send button paths.
     * It blocks empty messages, invalid characters, and profanity. The maximum
     * length check normalizes the input but does not block submission when the
     * message is exactly MAX_LENGTH.
     *
     * @param {boolean} showError - Whether to display validation feedback.
     * @returns {boolean} True only when the message can be submitted.
     */
    function isChatMessageValid(showError = true) {
        const message = input.value.trim();


        if (!message) {
            if (showError) {
                setValidationError('El mensaje es obligatorio.', 'required');
            }
            updateSendButtonState();
            return false;
        }


        validateMaxLength(false);


        const isCharactersValid = validateAllowedCharacters(showError);
        const isProfanityValid = validateMessageProfanity(showError);


        if (errorEl.dataset.errorType === 'maxlength-limit') {
            delete errorEl.dataset.errorType;
        }


        updateSendButtonState();


        return isCharactersValid && isProfanityValid;
    }


    /**
     * Sends the current chat message to the backend.
     *
     * Prevents duplicate submissions with isSending, validates the message,
     * verifies that a chat is selected, posts the message to /messages, and
     * clears the input after a successful response.
     */
    async function handleSendMessage() {
        if (isSending) return;


        if (!isChatMessageValid(true)) {
            return;
        }


        const message = input.value.trim();


        const activeChatId = messagesView?.dataset.chatId;


        if (!activeChatId) {
            setValidationError('Selecciona un chat antes de enviar un mensaje.', 'required');
            return;
        }


        isSending = true;
        sendBtn.disabled = true;


        try {
            const response = await fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    chat_id: activeChatId,
                    content: message
                })
            });


            if (!response.ok) {
                throw new Error('Error enviando mensaje');
            }




            input.value = '';
            clearAllValidationErrors();
            updateCounter();
            updateSendButtonState();
            moveActiveChatToTop(activeChatId);


        } catch (error) {
            console.error('Error enviando mensaje:', error);
            setValidationError('No se pudo enviar el mensaje. Inténtalo nuevamente.', 'required');
        } finally {
            isSending = false;
            updateSendButtonState();
        }
    }


    /**
     * Binds the send button click only once.
     * This uses the same handleSendMessage() function as the Enter key path.
     */
    if (!sendBtn.dataset.bound) {
        sendBtn.dataset.bound = 'true';


        sendBtn.addEventListener('click', (e) => {
            e.preventDefault();


            handleSendMessage();
        });
    }


    initializeSellerRating();


    /**
     * Submits the selected seller rating.
     *
     * Sends the rating to the backend, reloads the updated post details,
     * refreshes the modal content, and shows a success toast when complete.
     */
    if (submitSellerRatingBtn) {
        submitSellerRatingBtn.addEventListener('click', async () => {
            const ratingValue = Number(ratingInput?.value || 0);
            const postId = Number(messagesView?.dataset.postId || 0);


            if (!ratingValue || !postId) return;


            submitSellerRatingBtn.disabled = true;


            try {
                const response = await fetch(`/marketplace/${postId}/review`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rating: ratingValue
                    })
                });


                if (!response.ok) {
                    throw new Error('Error guardando rating');
                }


                const updatedResponse = await fetch(`/posts/${postId}`);
                const updatedPost = await updatedResponse.json();


                populatePostDetailsModal(updatedPost);


                ratingSentToast?.show();


            } catch (error) {
                console.error(error);
            } finally {
                submitSellerRatingBtn.disabled = false;
            }
        });
    }


    /**
     * Revalidates the report reason whenever the dropdown changes.
     *
     * Also updates the dirty-form state and enables/disables the submit button.
     */
    if (reportReason) {
        reportReason.addEventListener('change', () => {
            validateReportReason(true);
            updateReportDirtyState();
            updateReportButtonState();
        });
    }


    /**
     * Report description validation listeners.
     *
     * These handlers prevent typing beyond the maximum length, show the correct
     * limit message, validate allowed characters/minimum length, and keep the
     * submit button state updated while the user types.
     */
    if (reportDescription) {
        reportDescription.addEventListener('beforeinput', (event) => {
            const selectionLength = reportDescription.selectionEnd - reportDescription.selectionStart;
            const incomingText = event.data || '';


            if (
                reportDescription.value.length - selectionLength + incomingText.length > MAX_REPORT_LENGTH
            ) {
                event.preventDefault();


                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres. No puedes escribir más.`;


                updateReportButtonState();
            }
        });


        reportDescription.addEventListener('input', () => {
            const value = reportDescription.value;
            const currentLength = value.length;


            if (currentLength > MAX_REPORT_LENGTH) {
                reportDescription.value = value.slice(0, MAX_REPORT_LENGTH);
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres. No puedes escribir más.`;
            } else if (currentLength === MAX_REPORT_LENGTH) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent =
                    `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres. Puedes someter el texto tal como está.`;
            } else {
                validateReportDescription(true);
            }


            updateReportDirtyState();
            updateReportButtonState();
        });


        reportDescription.addEventListener('change', updateReportDirtyState);


        reportDescription.addEventListener('blur', () => {
            if (validateReportDescription(false)) {
                reportDescription.classList.remove('is-invalid');
                reportDescriptionError.textContent = '';
            } else {
                validateReportDescription(true);
            }


            updateReportButtonState();
        });
    }


    /**
     * Submits a report/querella against the selected seller.
     *
     * Validates the report fields, sends the report to the backend,
     * closes the report modal, shows the success toast, and returns the user
     * to the post details modal.
     */
    if (submitReportBtn) {
        submitReportBtn.addEventListener('click', async (e) => {
            e.preventDefault();


            if (submitReportBtn.dataset.submitting === 'true' || isSubmittingReport) {
                return;
            }


            const isReasonValid = validateReportReason(true);
            const isDescriptionValid = validateReportDescription(true);


            if (!isReasonValid || !isDescriptionValid) {
                updateReportButtonState();
                return;
            }


            submitReportBtn.dataset.submitting = 'true';
            isSubmittingReport = true;
            allowReportClose = true;
            isReportDirty = false;
            submitReportBtn.disabled = true;


            try {
                const response = await fetch('/reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        reported_user_id: reportedUserId,
                        post_id: Number(messagesView?.dataset.postId || 0),
                        report_reason: reportReason.value,
                        description: reportDescription.value
                    })
                });


                if (!response.ok) {
                    throw new Error('Error enviando reporte');
                }


                bootstrap.Modal.getOrCreateInstance(reportUserModal).hide();


                setTimeout(() => {
                    bootstrap.Toast.getInstance(document.getElementById('errorToast'))?.hide();
                    reportSentToast?.show();
                }, 250);


                if (postDetailsModal) {
                    setTimeout(() => {
                        bootstrap.Modal.getOrCreateInstance(postDetailsModal).show();
                    }, 300);
                }
            } catch (error) {
                console.error('Error enviando reporte:', error);
                isSubmittingReport = false;
                allowReportClose = false;
                submitReportBtn.disabled = false;
                delete submitReportBtn.dataset.submitting;
            }
        });
    }


    /**
     * Report modal lifecycle handlers.
     *
     * Resets validation when the report modal opens, intercepts accidental
     * modal closes when the form has unsaved data, and resets the form only
     * after an allowed close or successful submission.
     */
    if (reportUserModal) {
        reportUserModal.addEventListener('show.bs.modal', () => {
            resetReportValidation();
            updateReportButtonState();
        });


        reportUserModal.addEventListener('hide.bs.modal', (event) => {
            if (
                allowReportClose ||
                isSubmittingReport ||
                submitReportBtn?.dataset.submitting === 'true'
            ) {
                return;
            }


            updateReportDirtyState();


            if (!isReportDirty) {
                resetReportForm();


                if (postDetailsModal) {
                    setTimeout(() => {
                        const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                        postModalInstance.show();
                    }, 150);
                }


                return;
            }


            event.preventDefault();


            if (cancelReportConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
                confirmModal.show();
            }
        });


        reportUserModal.addEventListener('hidden.bs.modal', () => {
            if (allowReportClose || isSubmittingReport) {
                resetReportForm();
                allowReportClose = false;
                isSubmittingReport = false;
                delete submitReportBtn?.dataset.submitting;
            }
        });
    }


    /**
     * Report cancel button handler.
     *
     * Uses the shared close logic so cancellation respects the dirty-form
     * confirmation behavior.
     */
    if (cancelReportBtn) {
        cancelReportBtn.addEventListener('click', tryCloseReportModal);
    }


    /**
     * Report close icon handler.
     *
     * Prevents the default close behavior and routes the action through
     * the dirty-form confirmation flow.
     */
    if (closeReportModalBtn) {
        closeReportModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            tryCloseReportModal();
        });
    }


    /**
     * Confirms cancellation of the report form after the user chooses to discard
     * unsaved report data.
     *
     * This closes the confirmation modal, closes the report modal, allows the form
     * reset flow to run, and returns the user to the post details modal.
     */
    if (confirmCancelReport && reportUserModal && cancelReportConfirmModal) {
        confirmCancelReport.addEventListener('click', () => {
            allowReportClose = true;


            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
            confirmModal.hide();


            const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);
            reportModalInstance.hide();


            if (postDetailsModal) {
                setTimeout(() => {
                    const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show();
                }, 200);
            }
        });
    }


    /**
     * Initial page state setup.
     *
     * Shows the empty state when no chat is selected, loads the selected chat
     * when one exists, and connects to the real-time chat channel when available.
     */
    if (!chatId && emptyState) {
        emptyState.classList.remove('d-none');
    }


    if (chatId) {
        loadMessages(chatId);
        if (window.subscribeToChat) {
            window.subscribeToChat(chatId);
        }
    }


    /**
     * Desktop resize recovery.
     *
     * When returning from mobile width to desktop width, this restores both
     * chat columns and keeps the messages container scrolled to the bottom.
     */
    window.addEventListener('resize', () => {
        const isMobile = window.innerWidth < 768;
        const chatColumn = document.querySelector('.messages-chat-column');
        const sidebar = document.querySelector('.messages-sidebar');


        if (!isMobile) {
            sidebar?.classList.remove('d-none');
            chatColumn?.classList.remove('mobile-hidden');


            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });


    /**
     * Final UI synchronization.
     *
     * Runs once after all listeners are registered so filters, counters,
     * buttons, input state, and report submit state match the initial page data.
     */
    filterChats();
    updateSendButtonState();
    updateCounter();
    updateChatInputState();
    updateReportButtonState();
});

