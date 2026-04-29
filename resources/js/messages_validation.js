import * as bootstrap from 'bootstrap';

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

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

    return date.toLocaleDateString('es-PR', {
        month: 'long',
        day: 'numeric',
        year: 'numeric'
    });
}

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
    const bubbleClass = isMine ? 'bg-success-subtle text-dark border border-success-subtle' : 'bg-success-subtle text-dark border border-success-subtle';
    const timeClass = 'small mt-1 text-end text-muted';

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
                    <div class=" ${timeClass}">${messageObj.time}</div>
                </div>
            </div>
        `);

    messagesContainer.scrollTo({
        top: messagesContainer.scrollHeight,
        behavior: 'smooth'
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatMessageInput');
    const errorEl = document.getElementById('chatMessageError');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const messagesContainer = document.getElementById('chatMessagesContainer');
    const emptyState = document.getElementById('chatEmptyState');
    const counterEl = document.getElementById('chatMessageCounter');

    const messagesView = document.getElementById('messagesView');
    const chatHeaderParticipantName = document.getElementById('chatHeaderParticipantName');
    const chatHeaderPostSummary = document.getElementById('chatHeaderPostSummary');
    const chatHeaderParticipantInitial = document.getElementById('chatHeaderParticipantInitial');

    const messagesSearchInput = document.getElementById('messagesSearchInput');
    const chatListContainer = document.getElementById('chatListContainer');
    const chatSearchEmptyState = document.getElementById('chatSearchEmptyState');
    const searchMessagesBtn = document.getElementById('searchMessagesBtn');
    const clearMessagesFiltersBtn = document.getElementById('clearMessagesFiltersBtn');

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

    const postImagesCarouselIndicators = document.getElementById('postImagesCarouselIndicators');
    const postImagesCarouselInner = document.getElementById('postImagesCarouselInner');
    const postImagesCarouselPrev = document.getElementById('postImagesCarouselPrev');
    const postImagesCarouselNext = document.getElementById('postImagesCarouselNext');

    const openChatPostDetailsBtn = document.getElementById('openChatPostDetailsBtn');

    const submitSellerRatingBtn = document.getElementById('submitSellerRatingBtn');
    const ratingContainer = document.getElementById('sellerRatingStars');
    const ratingInput = document.getElementById('sellerRatingValue');
    const ratingText = document.getElementById('sellerRatingText');
    const clearSellerRating = document.getElementById('clearSellerRating');

    const ratingSentToastEl = document.getElementById('ratingSentToast');
    const reportSentToastEl = document.getElementById('reportSentToast');


    const ratingSentToast = ratingSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(ratingSentToastEl)
        : null;

    const reportSentToast = reportSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(reportSentToastEl)
        : null;

    const submitReportBtn = document.getElementById('submitReportBtn');
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

    if (!input || !errorEl || !sendBtn || !messagesContainer) return;

    input.addEventListener('focus', () => {
        if (!messagesView?.dataset.chatId) {
            input.blur();
        }
    });

    const MAX_LENGTH = 255;
    const MAX_REPORT_LENGTH = 500;
    const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,\-¿?¡!#$]+$/;
    const allowedReportRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
    const frontendMessages = [];
    const chatId = messagesView?.dataset.chatId;
    function updateChatInputState() {
        const hasChat = !!messagesView?.dataset.chatId;

        if (!hasChat) {
            input.disabled = true;
            input.placeholder = 'Selecciona un chat para escribir...';
            sendBtn.disabled = true;
        } else {
            input.disabled = false;
            input.placeholder = 'Escriba un mensaje...';
            updateSendButtonState(); // reuse your existing logic
        }
    }
    const currentUserId = messagesView?.dataset.currentUserId;

    let isReportDirty = false;
    let allowReportClose = false;
    let reportedUserId = null;

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

    function truncateText(text, maxLength = 40) {
        if (!text) return '';
        const trimmed = String(text).trim();
        if (trimmed.length <= maxLength) return trimmed;
        return trimmed.slice(0, maxLength).trimEnd() + '...';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function setValidationError(message, type) {
        input.classList.add('is-invalid');
        errorEl.textContent = message;
        errorEl.dataset.errorType = type;
        sendBtn.disabled = true;
    }

    function clearValidationError(type) {
        if (errorEl.dataset.errorType === type) {
            input.classList.remove('is-invalid');
            errorEl.textContent = '';
            delete errorEl.dataset.errorType;
        }
    }

    function clearAllValidationErrors() {
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
        delete errorEl.dataset.errorType;
    }


    function validateRequired() {
        const value = input.value.trim();

        if (!value) {
            setValidationError('El mensaje no puede estar vacío.', 'required');
            return false;
        }

        clearValidationError('required');
        return true;
    }

    function validateAllowedCharacters(showError = true) {
        const value = input.value;

        if (!value) {
            clearValidationError('characters');
            return true;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) {
                setValidationError(
                    'Solo se permiten letras, números, espacios, punto, coma y guion.',
                    'characters'
                );
            }
            return false;
        }

        clearValidationError('characters');
        return true;
    }

    function validateMaxLength(showError = true) {
        const value = input.value;

        if (value.length > MAX_LENGTH) {
            input.value = value.slice(0, MAX_LENGTH);

            if (showError) {
                input.classList.add('is-invalid');
                errorEl.textContent = `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres. No puedes escribir más.`;
                errorEl.dataset.errorType = 'maxlength-over';
            }

            return false;
        }

        if (value.length === MAX_LENGTH) {
            input.classList.add('is-invalid');
            errorEl.textContent = `Has alcanzado el máximo de ${MAX_LENGTH} caracteres, puedes aún someter esa cantidad.`;
            errorEl.dataset.errorType = 'maxlength-limit';
            return true;
        }

        clearValidationError('maxlength-over');
        clearValidationError('maxlength-limit');
        return true;
    }

    function updateSendButtonState() {
        const trimmedValue = input.value.trim();
        const hasBlockingError =
            errorEl.dataset.errorType === 'required' ||
            errorEl.dataset.errorType === 'maxlength-over' ||
            errorEl.dataset.errorType === 'characters';

        sendBtn.disabled = !trimmedValue || hasBlockingError;
    }

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

    function getCurrentTime() {
        return new Date().toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
    }


    function buildFrontendMessage(messageText) {
        return {
            id: Date.now(),
            conversationId: sendBtn.dataset.conversationId || '',
            senderId: sendBtn.dataset.senderId || '',
            message: messageText,
            time: getCurrentTime(),
            createdAt: new Date().toISOString(),
            isMine: true
        };
    }

    function saveMessagesForBackend() {
        const hiddenInputId = 'frontendMessagesPayload';
        let hiddenInput = document.getElementById(hiddenInputId);

        if (!hiddenInput) {
            hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.id = hiddenInputId;
            hiddenInput.name = 'frontend_messages_payload';
            sendBtn.closest('.p-4')?.appendChild(hiddenInput);
        }

        hiddenInput.value = JSON.stringify(frontendMessages);
    }

    function sendFrontendMessage() {
        const messageText = input.value.trim();
        const messageObj = buildFrontendMessage(messageText);

        frontendMessages.push(messageObj);
        renderMessage(messageObj);
        saveMessagesForBackend();

        input.value = '';
        clearAllValidationErrors();
        updateSendButtonState();
        updateCounter();
        input.focus();
    }

    function getStoredMarketplacePosts() {
        try {
            const raw = localStorage.getItem('marketplacePosts');
            return raw ? JSON.parse(raw) : [];
        } catch {
            return [];
        }
    }

    async function populateChatContextFromPost() {
        if (!messagesView) return;

        const postId = Number(messagesView.dataset.postId);
        if (!postId) return;

        try {
            const response = await fetch(`/posts/${postId}`);
            const post = await response.json();

            if (!post) return;

            const sellerName = `${post.user?.first_name ?? ''} ${post.user?.last_name ?? ''}`.trim() || 'Usuario';
            const postTitle = (post.title || 'Publicación').trim();
            const sellerInitial = sellerName.charAt(0).toUpperCase() || 'U';

            // Header

            if (chatHeaderParticipantName) {
                chatHeaderParticipantName.textContent = sellerName;
            }
            if (chatHeaderParticipantInitial && sellerName) {
                chatHeaderParticipantInitial.textContent = sellerName.charAt(0).toUpperCase();
            }


            if (chatHeaderPostSummary) {
                chatHeaderPostSummary.textContent = truncateText(postTitle, 60);
                chatHeaderPostSummary.title = postTitle;
            }

            document.querySelectorAll('.chat-user-initial').forEach((el) => {
                el.textContent = sellerInitial;
            });

        } catch (error) {
            console.error('Error cargando post:', error);
        }
    }

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

    function validateReportReason(showError = true) {
        if (!reportReason || !reportReasonError) return true;

        const isValid = !!reportReason.value;

        if (showError) {
            if (!isValid) {
                reportReason.classList.add('is-invalid');
                reportReasonError.textContent = 'Seleciona una razón.';
            } else {
                reportReason.classList.remove('is-invalid');
                reportReasonError.textContent = '';
            }
        }

        return isValid;
    }

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
                    'Solo se permiten letras, números, espacios, punto, coma y guion.';
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

    function updateReportButtonState() {
        if (!submitReportBtn) return;

        const isReady =
            validateReportReason(false) &&
            validateReportDescription(false);

        submitReportBtn.disabled = !isReady;
    }

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

    function resetReportForm() {
        if (reportUserForm) {
            reportUserForm.reset();
        }

        isReportDirty = false;
        allowReportClose = false;

        resetReportValidation();
        updateReportButtonState();
    }

    function updateReportDirtyState() {
        const hasReason = !!(reportReason && reportReason.value);
        const hasDescription = !!(reportDescription && reportDescription.value.trim() !== '');

        isReportDirty = hasReason || hasDescription;
    }

    async function loadMessages(chatId) {
        try {
            const response = await fetch(`/messages/${chatId}`);
            const messages = await response.json();
            messagesContainer.innerHTML = '';
            delete messagesContainer.dataset.lastMessageDate;

            if (!messages.length) {
                if (emptyState) {
                    emptyState.classList.remove('d-none');
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
                        hour: '2-digit',
                        minute: '2-digit'
                    }),
                    senderId: msg.sender_id,
                    conversationId: chatId,
                    isMine: msg.isMine
                });
            });

        } catch (error) {
            console.error('ERROR:', error);
        }
    }

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

    const backBtn = document.getElementById('backToChatsBtn');

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            const sidebar = document.querySelector('.messages-sidebar');
            const chatColumn = document.querySelector('.messages-chat-column');

            // SHows the sidebar once more
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

    if (!chatId && window.innerWidth < 768) {
        const chatColumn = document.querySelector('.messages-chat-column');
        if (chatColumn) {
            chatColumn.classList.add('mobile-hidden');
        }
    }

    input.addEventListener('beforeinput', (event) => {
        const selectionLength = input.selectionEnd - input.selectionStart;
        const incomingText = event.data || '';

        if (
            input.value.length - selectionLength + incomingText.length > MAX_LENGTH
        ) {
            event.preventDefault();

            input.classList.add('is-invalid');
            errorEl.textContent = `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres. No puedes escribir más.`;
            errorEl.dataset.errorType = 'maxlength-over';

            updateSendButtonState();
        }
    });

    input.addEventListener('input', () => {
        if (input.value.trim()) {
            clearValidationError('required');
        }


        validateMaxLength(true);
        validateAllowedCharacters(true);
        updateSendButtonState();
        updateCounter();
    });

    input.addEventListener('blur', () => {
        if (
            input.value.trim() &&
            input.value.length <= MAX_LENGTH &&
            validateAllowedCharacters(false)
        ) {
            input.classList.remove('is-invalid');
            errorEl.textContent = '';
            delete errorEl.dataset.errorType;
        }

        updateSendButtonState();
    });


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
    let isSending = false;

    async function handleSendMessage() {
        if (isSending) return;
        isSending = true;

        const message = input.value.trim();
        const chatId = messagesView.dataset.chatId;

        if (!message || !chatId) {
            isSending = false;
            return;
        }

        try {
            await fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    chat_id: chatId,
                    content: message
                })
            });

            sendFrontendMessage();

            input.value = '';
            clearAllValidationErrors();
            updateSendButtonState();
            updateCounter();
            input.focus();

        } catch (error) {
            console.error(error);
        } finally {
            isSending = false;
        }
    }

    if (!sendBtn.dataset.bound) {
        sendBtn.dataset.bound = 'true';

        sendBtn.addEventListener('click', (e) => {
            e.preventDefault();
            handleSendMessage();
        });
    }

    initializeSellerRating();

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

    if (reportReason) {
        reportReason.addEventListener('change', () => {
            validateReportReason(true);
            updateReportDirtyState();
            updateReportButtonState();
        });
    }

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
                    `Has alcanzado el máximo de ${MAX_REPORT_LENGTH} caracteres, puedes aún someter esa cantidad.`;
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

    if (submitReportBtn) {
        submitReportBtn.addEventListener('click', async (e) => {
            e.preventDefault();

            const isReasonValid = validateReportReason(true);
            const isDescriptionValid = validateReportDescription(true);

            if (!isReasonValid || !isDescriptionValid) {
                updateReportButtonState();
                return;
            }

            try {

                await fetch('/reports', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        reported_user_id: reportedUserId,
                        report_reason: reportReason.value,
                        description: reportDescription.value
                    })
                });

                allowReportClose = true;

                const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);
                reportModalInstance.hide();

                setTimeout(() => {
                    reportSentToast?.show();
                }, 250);

                if (postDetailsModal) {
                    setTimeout(() => {
                        const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                        postModalInstance.show();
                    }, 300);
                }
            }
            catch (error) {
                console.error('Error enviando reporte:', error);
            }
        });
    }

    if (reportUserModal) {
        reportUserModal.addEventListener('show.bs.modal', () => {
            resetReportValidation();
            updateReportButtonState();
        });

        reportUserModal.addEventListener('hide.bs.modal', (event) => {
            if (allowReportClose) {
                return;
            }

            updateReportDirtyState();

            if (!isReportDirty) {
                resetReportForm();
                return;
            }

            event.preventDefault();

            if (cancelReportConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
                confirmModal.show();
            }
        });

        reportUserModal.addEventListener('hidden.bs.modal', () => {
            if (allowReportClose) {
                resetReportForm();
                allowReportClose = false;
            }
        });
    }

    if (cancelReportBtn) {
        cancelReportBtn.addEventListener('click', tryCloseReportModal);
    }

    if (closeReportModalBtn) {
        closeReportModalBtn.addEventListener('click', (e) => {
            e.preventDefault();
            tryCloseReportModal();
        });
    }

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

    if (!chatId && emptyState) {
        emptyState.classList.remove('d-none');
    }

    if (chatId) {
        loadMessages(chatId);
        if (window.subscribeToChat) {
            window.subscribeToChat(chatId);
        }
    }

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

    filterChats();
    updateSendButtonState();
    updateCounter();
    updateChatInputState();
    updateReportButtonState();
});
