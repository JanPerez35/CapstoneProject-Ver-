import * as bootstrap from 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatMessageInput');
    const errorEl = document.getElementById('chatMessageError');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const messagesContainer = document.getElementById('chatMessagesContainer');
    const emptyState = document.getElementById('chatEmptyState');
    const counterEl = document.getElementById('chatMessageCounter');

    const messagesView = document.getElementById('messagesView');
    const chatParticipantName = document.getElementById('chatParticipantName');
    const chatPostSummary = document.getElementById('chatPostSummary');
    const chatHeaderParticipantName = document.getElementById('chatHeaderParticipantName');
    const chatHeaderPostSummary = document.getElementById('chatHeaderPostSummary');

    const messagesSearchInput = document.getElementById('messagesSearchInput');
    const chatListContainer = document.getElementById('chatListContainer');
    const chatListItem = document.getElementById('chatListItem');
    const chatSearchEmptyState = document.getElementById('chatSearchEmptyState');

    const postDetailsModal = document.getElementById('postDetailsModal');
    const postDetailsModalLabel = document.getElementById('postDetailsModalLabel');
    const postDetailsDescription = document.getElementById('postDetailsDescription');
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
    const reportUserNameText = document.getElementById('reportUserNameText');

    if (!input || !errorEl || !sendBtn || !messagesContainer) return;

    const MAX_LENGTH = 255;
    const MAX_REPORT_LENGTH = 500;
    const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,\-¿?¡!()]+$/;
    const allowedReportRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,\-¿?¡!()]+$/;
    const frontendMessages = [];

    let isReportDirty = false;
    let allowReportClose = false;

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
                setValidationError(
                    `El mensaje no puede exceder ${MAX_LENGTH} caracteres.`,
                    'maxlength'
                );
            }

            return false;
        }

        clearValidationError('maxlength');
        return true;
    }

    function updateSendButtonState() {
        const trimmedValue = input.value.trim();
        const hasOwnError =
            errorEl.dataset.errorType === 'required' ||
            errorEl.dataset.errorType === 'maxlength' ||
            errorEl.dataset.errorType === 'characters';

        sendBtn.disabled = !trimmedValue || hasOwnError;
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

    function renderMessage(messageObj) {
        if (emptyState) {
            emptyState.classList.add('d-none');
        }

        messagesContainer.insertAdjacentHTML('beforeend', `
            <div class="d-flex justify-content-end mb-3">
                <div
                    class="bg-success text-white px-3 py-2 rounded-4 shadow-sm"
                    style="max-width: 75%; word-break: break-word;"
                    data-message-id="${messageObj.id}"
                    data-conversation-id="${messageObj.conversationId}"
                    data-sender-id="${messageObj.senderId}"
                >
                    <div>${escapeHtml(messageObj.message)}</div>
                    <div class="small text-white-50 mt-1 text-end">${messageObj.time}</div>
                </div>
            </div>
        `);

        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }

    function buildFrontendMessage(messageText) {
        return {
            id: Date.now(),
            conversationId: sendBtn.dataset.conversationId || '',
            senderId: sendBtn.dataset.senderId || '',
            message: messageText,
            time: getCurrentTime(),
            createdAt: new Date().toISOString()
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

    function populateChatContextFromPost() {
        if (!messagesView) return;

        const postId = Number(messagesView.dataset.postId);
        if (!postId) return;

        const posts = getStoredMarketplacePosts();
        const post = posts.find((p) => Number(p.id) === postId);

        if (!post) {
            if (chatParticipantName) chatParticipantName.textContent = 'Usuario';
            if (chatHeaderParticipantName) chatHeaderParticipantName.textContent = 'Usuario';

            if (chatPostSummary) {
                chatPostSummary.textContent = 'Publicación no encontrada';
                chatPostSummary.title = 'Publicación no encontrada';
            }

            if (chatHeaderPostSummary) {
                chatHeaderPostSummary.textContent = 'Publicación no encontrada';
                chatHeaderPostSummary.title = 'Publicación no encontrada';
            }

            if (chatListItem) {
                chatListItem.dataset.searchText = 'usuario publicación no encontrada';
            }

            document.querySelectorAll('.chat-user-initial').forEach((el) => {
                el.textContent = 'U';
            });

            return;
        }

        const sellerName = (post.seller || 'Usuario').trim();
        const postTitle = (post.title || 'Publicación').trim();
        const sellerInitial = sellerName.charAt(0).toUpperCase() || 'U';

        if (chatParticipantName) {
            chatParticipantName.textContent = sellerName;
        }

        if (chatHeaderParticipantName) {
            chatHeaderParticipantName.textContent = sellerName;
        }

        if (chatPostSummary) {
            chatPostSummary.textContent = truncateText(postTitle, 35);
            chatPostSummary.title = postTitle;
        }

        if (chatHeaderPostSummary) {
            chatHeaderPostSummary.textContent = truncateText(postTitle, 60);
            chatHeaderPostSummary.title = postTitle;
        }

        document.querySelectorAll('.chat-user-initial').forEach((el) => {
            el.textContent = sellerInitial;
        });

        if (chatListItem) {
            chatListItem.dataset.searchText = `${sellerName} ${postTitle}`.toLowerCase();
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

        if (postDetailsPrice) {
            postDetailsPrice.textContent = `$${post.price || '0.00'}`;
        }

        if (postDetailsStatus) {
            postDetailsStatus.textContent = post.status || 'Disponible';
        }

        if (postDetailsCondition) {
            postDetailsCondition.textContent = post.condition || 'Sin especificar';
        }

        if (postDetailsSeller) {
            postDetailsSeller.textContent = post.seller || 'Usuario';
        }

        if (postDetailsSellerRating) {
            postDetailsSellerRating.innerHTML =
                `<i class="bi bi-star-fill text-warning me-1"></i> ${post.rating || '0.0'} <span class="text-muted">(${post.reviews || 0} reseñas)</span>`;
        }

        if (postDetailsCategory) {
            postDetailsCategory.textContent = post.category || 'Sin categoría';
        }

        if (reportUserNameText) {
            reportUserNameText.textContent = post.seller || 'Usuario';
        }

        const images = Array.isArray(post.images) && post.images.length
            ? post.images
            : (post.image ? [post.image] : []);

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
                reportReasonError.textContent = 'Selecciona una razón.';
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
        openChatPostDetailsBtn.addEventListener('click', () => {
            const postId = Number(openChatPostDetailsBtn.dataset.postId);
            if (!postId) return;

            const posts = getStoredMarketplacePosts();
            const post = posts.find((p) => Number(p.id) === postId);

            if (!post) return;

            populatePostDetailsModal(post);

            if (postDetailsModal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                modalInstance.show();
            }
        });
    }

    if (messagesSearchInput) {
        messagesSearchInput.addEventListener('input', filterChats);
    }

    input.addEventListener('input', () => {
        if (input.value.trim()) {
            clearValidationError('required');
        }

        validateMaxLength(true);
        validateAllowedCharacters(true);
        updateSendButtonState();
        updateCounter();
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            sendBtn.click();
        }
    });

    sendBtn.addEventListener('click', (event) => {
        event.preventDefault();

        const isRequiredValid = validateRequired();
        const isLengthValid = validateMaxLength(true);
        const isCharactersValid = validateAllowedCharacters(true);


        if (!isRequiredValid || !isLengthValid || !isCharactersValid) {
            updateSendButtonState();
            updateCounter();
            return;
        }

        sendFrontendMessage();
    });

    initializeSellerRating();

    if (submitSellerRatingBtn) {
        submitSellerRatingBtn.addEventListener('click', () => {
            const ratingValue = Number(ratingInput?.value || 0);
            if (!ratingValue) return;
            ratingSentToast?.show();
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
        reportDescription.addEventListener('input', () => {
            const value = reportDescription.value;

            if (value.length > MAX_REPORT_LENGTH) {
                reportDescription.value = value.slice(0, MAX_REPORT_LENGTH);
                validateReportDescription(true);
            } else {
                validateReportDescription(true);
            }

            updateReportDirtyState();
            updateReportButtonState();
        });

        reportDescription.addEventListener('change', updateReportDirtyState);

        reportDescription.addEventListener('blur', () => {
            validateReportDescription(true);
            updateReportButtonState();
        });
    }

    if (submitReportBtn) {
        submitReportBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const isReasonValid = validateReportReason(true);
            const isDescriptionValid = validateReportDescription(true);

            if (!isReasonValid || !isDescriptionValid) {
                updateReportButtonState();
                return;
            }

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

    populateChatContextFromPost();
    filterChats();
    updateSendButtonState();
    updateCounter();
    updateReportButtonState();
});
