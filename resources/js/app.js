import './bootstrap';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    const borrowButtons = document.querySelectorAll('.open-borrow-modal');
    const borrowEquipmentId = document.getElementById('borrowEquipmentId');
    const borrowModalText = document.getElementById('borrowModalText');
    const borrowModalImage = document.getElementById('borrowModalImage');
    const borrowModalStock = document.getElementById('borrowModalStock');
    const borrowQuantity = document.getElementById('borrowQuantity');

    const specialCaseCheck = document.getElementById('special_case');
    const specialCaseFields = document.getElementById('specialCaseFields');
    const pickupDate = document.getElementById('pickup_date');
    const returnDate = document.getElementById('return_date');
    const specialReason = document.getElementById('special_reason');

    borrowButtons.forEach(button => {
        button.addEventListener('click', function () {
            const itemId = this.dataset.itemId;
            const itemName = this.dataset.itemName;
            const itemStock = this.dataset.itemStock;
            const itemImage = this.dataset.itemImage;

            if (borrowEquipmentId) borrowEquipmentId.value = itemId;
            if (borrowModalText) borrowModalText.textContent = `Selecciona la cantidad que deseas de "${itemName}"`;
            if (borrowModalImage) borrowModalImage.src = itemImage;
            if (borrowModalStock) borrowModalStock.textContent = itemStock;

            if (borrowQuantity) {
                borrowQuantity.max = itemStock;
                borrowQuantity.value = 1;
            }
        });
    });

    function toLocalDateString(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function setMinDates() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);

        const minDate = toLocalDateString(tomorrow);

        if (pickupDate) pickupDate.min = minDate;
        if (returnDate) returnDate.min = minDate;
    }

    function toggleSpecialCaseFields() {
        if (!specialCaseCheck || !specialCaseFields) return;

        if (specialCaseCheck.checked) {
            specialCaseFields.classList.remove('d-none');

            if (returnDate) returnDate.required = true;
            if (specialReason) specialReason.required = true;
        } else {
            specialCaseFields.classList.add('d-none');

            if (returnDate) {
                returnDate.required = false;
                returnDate.value = '';
            }

            if (specialReason) {
                specialReason.required = false;
                specialReason.value = '';
            }
        }
    }

    if (specialCaseCheck) {
        specialCaseCheck.addEventListener('change', toggleSpecialCaseFields);
    }

    setMinDates();
    toggleSpecialCaseFields();
    // Marketplace search and filters includes pagination
    const marketplaceSearch = document.getElementById('marketplaceSearch');
    const marketplaceCategoryFilter = document.getElementById('marketplaceCategoryFilter');
    const marketplaceStatusFilter = document.getElementById('marketplaceStatusFilter');
    const marketplaceCardsContainer = document.getElementById('marketplaceCardsContainer');
    const marketplaceEmptyState = document.getElementById('marketplaceEmptyState');
    const marketplacePagination = document.getElementById('marketplacePagination');

    const POSTS_PER_PAGE = 12;
    let currentMarketplacePage = 1;

    function getStoredMarketplacePosts() {
        try {
            const raw = localStorage.getItem('marketplacePosts');
            return raw ? JSON.parse(raw) : [];
        } catch {
            return [];
        }
    }

    function saveMarketplacePosts(posts) {
        localStorage.setItem('marketplacePosts', JSON.stringify(posts));
    }

    let allMarketplacePosts = getStoredMarketplacePosts();

    function createMarketplacePostObject() {
        const firstImage = selectedPostImages[0]
            ? URL.createObjectURL(selectedPostImages[0])
            : '';

        return {
            id: Date.now(),
            title: postTitle.value.trim(),
            description: postDescription.value.trim() || 'Sin descripción.',
            price: postPrice.value.trim(),
            category: postCategory.value,
            condition: postCondition.value,
            status: 'Disponible',
            seller: 'John Davis',
            rating: '4.3',
            reviews: '8',
            createdAt: 'hace unos segundos',
            image: firstImage
        };
    }

    function createMarketplaceCardHTML(post) {
        return `
        <div
            class="col-md-6 col-lg-4 marketplace-card"
            data-id="${post.id}"
            data-title="${post.title}"
            data-description="${post.description}"
            data-category="${post.category}"
            data-status="${post.status}"
            data-condition="${post.condition}"
            data-seller="${post.seller}"
        >
            <div class="card h-100 shadow-sm rounded-4 overflow-hidden item-card border-0">
                <img
                    src="${post.image}"
                    class="card-img-top"
                    alt="${post.title}"
                    style="height: 400px; object-fit: cover; object-position: center;"
                >

                <div class="card-body d-flex flex-column p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0 fw-bold">${post.title}</h5>
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            ${post.status}
                        </span>
                    </div>

                    <p class="text-muted mb-3">${post.description}</p>

                    <h3 class="fw-bold text-success mb-3">$${post.price}</h3>

                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <span class="badge border rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            ${post.condition}
                        </span>
                        <span class="badge px-3 py-2 rounded-0" style="background-color:#6FC21F; color:white;">
                            ${post.category}
                        </span>
                    </div>

                    <div class="small text-muted mb-3">
                        <div class="mb-2">
                            <i class="bi bi-person me-2"></i> ${post.seller}
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-star-fill text-warning me-2"></i> ${post.rating} (${post.reviews} calificaciones)
                        </div>
                        <div>
                            <i class="bi bi-clock me-2"></i> ${post.createdAt}
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
    `;
    }

    function getFilteredMarketplacePosts() {
        const searchValue = (marketplaceSearch?.value || '').trim().toLowerCase();
        const selectedCategory = marketplaceCategoryFilter?.value || 'all';
        const selectedStatus = marketplaceStatusFilter?.value || 'all';

        return allMarketplacePosts.filter((post) => {
            const matchesSearch =
                searchValue === '' ||
                post.title.toLowerCase().includes(searchValue) ||
                post.description.toLowerCase().includes(searchValue) ||
                post.category.toLowerCase().includes(searchValue) ||
                post.condition.toLowerCase().includes(searchValue) ||
                post.seller.toLowerCase().includes(searchValue);

            const matchesCategory =
                selectedCategory === 'all' || post.category === selectedCategory;

            const matchesStatus =
                selectedStatus === 'all' || post.status === selectedStatus;

            return matchesSearch && matchesCategory && matchesStatus;
        });
    }

    function renderMarketplacePagination(totalItems) {
        if (!marketplacePagination) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / POSTS_PER_PAGE));
        marketplacePagination.innerHTML = '';

        const prevDisabled = currentMarketplacePage === 1 ? 'disabled' : '';
        marketplacePagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${prevDisabled}">
            <button class="page-link" type="button" data-page="prev" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </button>
        </li>
    `);

        for (let page = 1; page <= totalPages; page++) {
            const activeClass = page === currentMarketplacePage ? 'active' : '';
            marketplacePagination.insertAdjacentHTML('beforeend', `
            <li class="page-item ${activeClass}">
                <button class="page-link" type="button" data-page="${page}">${page}</button>
            </li>
        `);
        }

        const nextDisabled = currentMarketplacePage === totalPages ? 'disabled' : '';
        marketplacePagination.insertAdjacentHTML('beforeend', `
        <li class="page-item ${nextDisabled}">
            <button class="page-link" type="button" data-page="next" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </button>
        </li>
    `);

        marketplacePagination.querySelectorAll('.page-link').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.page;
                const maxPage = totalPages;

                if (action === 'prev' && currentMarketplacePage > 1) {
                    currentMarketplacePage -= 1;
                } else if (action === 'next' && currentMarketplacePage < maxPage) {
                    currentMarketplacePage += 1;
                } else if (!isNaN(Number(action))) {
                    currentMarketplacePage = Number(action);
                }

                renderMarketplace();
            });
        });
    }

    function renderMarketplace() {
        if (!marketplaceCardsContainer) return;

        const filteredPosts = getFilteredMarketplacePosts();
        const totalPages = Math.max(1, Math.ceil(filteredPosts.length / POSTS_PER_PAGE));

        if (currentMarketplacePage > totalPages) {
            currentMarketplacePage = totalPages;
        }

        const start = (currentMarketplacePage - 1) * POSTS_PER_PAGE;
        const end = start + POSTS_PER_PAGE;
        const paginatedPosts = filteredPosts.slice(start, end);

        marketplaceCardsContainer.querySelectorAll('.marketplace-card').forEach((card) => card.remove());

        if (marketplaceEmptyState) {
            marketplaceEmptyState.classList.toggle('d-none', filteredPosts.length !== 0);
        }

        paginatedPosts.forEach((post) => {
            marketplaceCardsContainer.insertAdjacentHTML('beforeend', createMarketplaceCardHTML(post));
        });

        renderMarketplacePagination(filteredPosts.length);
    }

    function resetCreatePostLocalState() {
        if (createPostForm) {
            createPostForm.reset();
        }

        selectedPostImages = [];
        isCreatePostDirty = false;
        allowCreatePostClose = false;

        if (imagePreviewContainer) {
            imagePreviewContainer.innerHTML = '';
        }

        if (postImage) {
            postImage.value = '';
        }

        resetCreatePostValidation();
        updatePublishButtonState();
    }

    if (marketplaceSearch) {
        marketplaceSearch.addEventListener('input', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }

    if (marketplaceCategoryFilter) {
        marketplaceCategoryFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }

    if (marketplaceStatusFilter) {
        marketplaceStatusFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }

    renderMarketplace();

    // Post validation
    const createPostForm = document.getElementById('createPostForm');
    const publishBtn = document.getElementById('publishBtn');
    const postTitle = document.getElementById('postTitle');
    const postTitleError = document.getElementById('postTitleError');
    const postDescription = document.getElementById('postDescription');
    const postDescriptionError = document.getElementById('postDescriptionError');
    const postPrice = document.getElementById('postPrice');
    const postPriceError = document.getElementById('postPriceError');
    const postCategory = document.getElementById('postCategory');
    const postCondition = document.getElementById('postCondition');
    const postImage = document.getElementById('postImage');
    const imageError = document.getElementById('imageError');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
    const priceRegex = /^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/;
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
    const MAX_IMAGES = 3;
    const MIN_IMAGES = 1;

    let selectedPostImages = [];

    function setFieldError(field, errorElement, message) {
        if (!field) return;
        field.classList.add('is-invalid');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }

    function clearFieldError(field, errorElement) {
        if (!field) return;
        field.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    function showImageError(message) {
        if (!imageError) return;
        imageError.textContent = message;
        imageError.classList.remove('d-none');
    }

    function clearImageError() {
        if (!imageError) return;
        imageError.textContent = '';
        imageError.classList.add('d-none');
    }

    function renderImagePreviews(files) {
        if (!imagePreviewContainer) return;

        imagePreviewContainer.innerHTML = '';

        files.forEach((file, index) => {
            const url = URL.createObjectURL(file);

            imagePreviewContainer.insertAdjacentHTML('beforeend', `
    <div class="col-md-4">
        <div class="border rounded-4 overflow-hidden bg-light position-relative">

            <button
                type="button"
                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle d-flex align-items-center justify-content-center remove-preview-image"
                style="width:32px; height:32px;"
                data-index="${index}"
            >
                <i class="bi bi-x"></i>
            </button>

            <img
                src="${url}"
                alt="Preview"
                class="w-100"
                style="height: 180px; object-fit: cover;"
            >

            <div class="p-2 small text-muted text-truncate">
                ${file.name}
            </div>
        </div>
    </div>
`);
        });

        document.querySelectorAll('.remove-preview-image').forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.dataset.index);

                selectedPostImages.splice(index, 1);

                renderImagePreviews(selectedPostImages);
                clearImageError();
                if (postImage) {
                    postImage.value = '';
                }
                updateCreatePostDirtyState();
                updatePublishButtonState();
            });
        });
    }

    async function isRealJpeg(file) {
        if (!file || !file.type.includes('jpeg')) {
            return false;
        }

        const buffer = await file.slice(0, 3).arrayBuffer();
        const bytes = new Uint8Array(buffer);

        return bytes[0] === 0xFF && bytes[1] === 0xD8 && bytes[2] === 0xFF;
    }

    function validateTitle(showError = true) {
        if (!postTitle || !postTitleError) return true;

        const value = postTitle.value.trim();

        if (showError) {
            clearFieldError(postTitle, postTitleError);
        }

        if (!value) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título es obligatorio.');
            return false;
        }

        if (value.length < 5) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título debe tener al menos 5 caracteres.');
            return false;
        }

        if (value.length > 100) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título no puede exceder 100 caracteres.');
            return false;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) setFieldError(postTitle, postTitleError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            return false;
        }

        return true;
    }

    function validateDescription(showError = true) {
        if (!postDescription || !postDescriptionError) return true;

        const value = postDescription.value.trim();

        if (showError) {
            clearFieldError(postDescription, postDescriptionError);
        }

        if (!value) {
            return true;
        }

        if (value.length < 10) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
            return false;
        }

        if (value.length > 1000) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'La descripción no puede exceder 1000 caracteres.');
            return false;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            return false;
        }

        return true;
    }

    function validatePrice(showError = true) {
        if (!postPrice || !postPriceError) return true;

        const value = postPrice.value.trim();

        if (showError) {
            clearFieldError(postPrice, postPriceError);
        }

        if (!value) {
            if (showError) setFieldError(postPrice, postPriceError, 'El precio es obligatorio.');
            return false;
        }

        if (/[eE+\-]/.test(value)) {
            if (showError) setFieldError(postPrice, postPriceError, 'No se permite usar e, E, + ni - en el precio.');
            return false;
        }

        if (!priceRegex.test(value)) {
            if (showError) setFieldError(postPrice, postPriceError, 'Ingresa un precio válido usando solo números y hasta 2 decimales.');
            return false;
        }

        return true;
    }

    function validateSelect(field, showError = true) {
        if (!field) return true;

        const isValid = !!field.value;

        if (showError) {
            if (!isValid) {
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        }

        return isValid;
    }

    function validateImages(showError = true) {
        if (showError) {
            clearImageError();
        }

        if (selectedPostImages.length < MIN_IMAGES) {
            if (showError) showImageError('Debes subir al menos 1 imagen.');
            return false;
        }

        if (selectedPostImages.length > MAX_IMAGES) {
            if (showError) showImageError('Solo puedes subir un máximo de 3 imágenes.');
            return false;
        }

        return true;
    }

    function updatePublishButtonState() {
        if (!publishBtn) return;

        const isReady =
            validateTitle(false) &&
            validatePrice(false) &&
            validateSelect(postCategory, false) &&
            validateSelect(postCondition, false) &&
            validateImages(false) &&
            validateDescription(false);

        publishBtn.disabled = !isReady;
    }

    if (postTitle) {
        postTitle.addEventListener('input', () => {
            postTitle.value = postTitle.value.slice(0, 100);
            validateTitle(true);
            updatePublishButtonState();
        });
    }

    if (postDescription) {
        postDescription.addEventListener('input', () => {
            postDescription.value = postDescription.value.slice(0, 1000);
            validateDescription(true);
            updatePublishButtonState();
        });
    }

    if (postPrice) {
        postPrice.addEventListener('keydown', (e) => {
            if (['e', 'E', '+', '-'].includes(e.key)) {
                e.preventDefault();
            }
        });

        postPrice.addEventListener('input', () => {
            postPrice.value = postPrice.value.replace(/[^0-9.]/g, '');

            const firstDotIndex = postPrice.value.indexOf('.');
            if (firstDotIndex !== -1) {
                const integerPart = postPrice.value.slice(0, firstDotIndex + 1);
                const decimalPart = postPrice.value
                    .slice(firstDotIndex + 1)
                    .replace(/\./g, '')
                    .slice(0, 2);

                postPrice.value = integerPart + decimalPart;
            }

            validatePrice(true);
            updatePublishButtonState();
        });
    }

    if (postCategory) {
        postCategory.addEventListener('change', () => {
            validateSelect(postCategory, true);
            updatePublishButtonState();
        });
    }

    if (postCondition) {
        postCondition.addEventListener('change', () => {
            validateSelect(postCondition, true);
            updatePublishButtonState();
        });
    }

    if (postImage) {
        postImage.addEventListener('change', async () => {
            const newFiles = Array.from(postImage.files || []);
            if (newFiles.length === 0) return;

            clearImageError();

            const allowedSlots = MAX_IMAGES - selectedPostImages.length;

            if (allowedSlots <= 0) {
                showImageError('Solo puedes subir un máximo de 3 imágenes.');
                postImage.value = '';
                updatePublishButtonState();
                return;
            }

            if (newFiles.length > allowedSlots) {
                showImageError(`Solo puedes agregar ${allowedSlots} imagen${allowedSlots === 1 ? '' : 'es'} más.`);
                postImage.value = '';
                updatePublishButtonState();
                return;
            }

            for (const file of newFiles) {
                if (file.size > MAX_IMAGE_SIZE) {
                    showImageError(`"${file.name}" excede el tamaño máximo de 2MB.`);
                    postImage.value = '';
                    updatePublishButtonState();
                    return;
                }

                if (!(await isRealJpeg(file))) {
                    showImageError(`"${file.name}" no es un JPEG válido.`);
                    postImage.value = '';
                    updatePublishButtonState();
                    return;
                }
            }

            selectedPostImages = [...selectedPostImages, ...newFiles];
            renderImagePreviews(selectedPostImages);

            postImage.value = '';
            updateCreatePostDirtyState();
            updatePublishButtonState();
        });
    }

    if (publishBtn && createPostForm) {
        publishBtn.addEventListener('click', async () => {
            const isTitleValid = validateTitle(true);
            const isDescriptionValid = validateDescription(true);
            const isPriceValid = validatePrice(true);
            const isCategoryValid = validateSelect(postCategory, true);
            const isConditionValid = validateSelect(postCondition, true);
            const areImagesValid = validateImages(true);

            if (
                !isTitleValid ||
                !isDescriptionValid ||
                !isPriceValid ||
                !isCategoryValid ||
                !isConditionValid ||
                !areImagesValid
            ) {
                updatePublishButtonState();
                return;
            }

            const newPost = createMarketplacePostObject();
            allMarketplacePosts.unshift(newPost);
            saveMarketplacePosts(allMarketplacePosts);

            currentMarketplacePage = 1;
            renderMarketplace();

            const createModalInstance = bootstrap.Modal.getOrCreateInstance(createPostModal);
            allowCreatePostClose = true;
            createModalInstance.hide();

            resetCreatePostLocalState();
        });
    }

    updatePublishButtonState();

    const createPostModal = document.getElementById('createPostModal');
    const cancelCreatePostBtn = document.getElementById('cancelCreatePostBtn');
    const cancelConfirmModal = document.getElementById('cancelConfirmModal');
    const confirmCancelCreatePost = document.getElementById('confirmCancelCreatePost');

    let isCreatePostDirty = false;
    let allowCreatePostClose = false;

    function resetCreatePostValidation() {
        clearFieldError(postTitle, postTitleError);
        clearFieldError(postDescription, postDescriptionError);
        clearFieldError(postPrice, postPriceError);

        if (postCategory) postCategory.classList.remove('is-invalid');
        if (postCondition) postCondition.classList.remove('is-invalid');

        clearImageError();
    }

    function resetCreatePostForm() {
        if (createPostForm) {
            createPostForm.reset();
        }

        selectedPostImages = [];
        isCreatePostDirty = false;
        allowCreatePostClose = false;

        if (imagePreviewContainer) {
            imagePreviewContainer.innerHTML = '';
        }

        if (postImage) {
            postImage.value = '';
        }

        resetCreatePostValidation();
        updatePublishButtonState();
    }

    function updateCreatePostDirtyState() {
        const hasText =
            (postTitle?.value.trim() || '') !== '' ||
            (postDescription?.value.trim() || '') !== '' ||
            (postPrice?.value.trim() || '') !== '';

        const hasSelects =
            !!postCategory?.value ||
            !!postCondition?.value;

        const hasImages = selectedPostImages.length > 0;

        isCreatePostDirty = hasText || hasSelects || hasImages;
    }

// Track changes
    [postTitle, postDescription, postPrice, postCategory, postCondition].forEach((field) => {
        if (!field) return;

        field.addEventListener('input', updateCreatePostDirtyState);
        field.addEventListener('change', updateCreatePostDirtyState);
    });

// Modal open & close behavior
    if (createPostModal) {
        createPostModal.addEventListener('show.bs.modal', () => {
            resetCreatePostValidation();
            updatePublishButtonState();
        });

        createPostModal.addEventListener('hide.bs.modal', (event) => {
            if (allowCreatePostClose) return;

            updateCreatePostDirtyState();

            if (!isCreatePostDirty) {
                resetCreatePostForm();
                return;
            }

            event.preventDefault();

            if (cancelConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
                confirmModal.show();
            }
        });

        createPostModal.addEventListener('hidden.bs.modal', () => {
            if (allowCreatePostClose) {
                resetCreatePostForm();
                allowCreatePostClose = false;
            }
        });
    }

// Cancel button behavior
    if (cancelCreatePostBtn && createPostModal) {
        cancelCreatePostBtn.addEventListener('click', () => {
            updateCreatePostDirtyState();

            const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);

            if (!isCreatePostDirty) {
                allowCreatePostClose = true;
                modal.hide();
                return;
            }

            if (cancelConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
                confirmModal.show();
            }
        });
    }

// Confirm cancel
    if (confirmCancelCreatePost && createPostModal && cancelConfirmModal) {
        confirmCancelCreatePost.addEventListener('click', () => {
            allowCreatePostClose = true;

            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
            confirmModal.hide();

            const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);
            modal.hide();
        });
    }

    //Reporting
    const reportUserForm = document.getElementById('reportUserForm');
    const reportReason = document.getElementById('reportReason');
    const reportReasonError = document.getElementById('reportReasonError');
    const reportDescription = document.getElementById('reportDescription');
    const reportDescriptionError = document.getElementById('reportDescriptionError');
    const submitReportBtn = document.getElementById('submitReportBtn');

    function validateReportReason(showError = true) {
        if (!reportReason) return true;

        const isValid = !!reportReason.value;

        if (showError) {
            if (!isValid) {
                reportReason.classList.add('is-invalid');
            } else {
                reportReason.classList.remove('is-invalid');
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

        if (value.length < 10) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción debe tener al menos 10 caracteres.';
            }
            return false;
        }

        if (value.length > 500) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción no puede exceder 500 caracteres.';
            }
            return false;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'Solo se permiten letras, números, espacios, punto, coma y guion.';
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

    if (reportReason) {
        reportReason.addEventListener('change', () => {
            validateReportReason(true);
            updateReportButtonState();
        });
    }

    if (reportDescription) {
        reportDescription.addEventListener('input', () => {
            reportDescription.value = reportDescription.value.slice(0, 500);

            if (reportDescription.value.trim() === '') {
                reportDescription.classList.remove('is-invalid');
                reportDescriptionError.textContent = '';
            } else {
                validateReportDescription(true);
            }

            updateReportButtonState();
        });
    }

    if (submitReportBtn && reportUserForm) {
        submitReportBtn.addEventListener('click', () => {
            const isReasonValid = validateReportReason(true);
            const isDescriptionValid = validateReportDescription(true);

            if (!isReasonValid || !isDescriptionValid) {
                updateReportButtonState();
                return;
            }

            reportUserForm.submit();
        });
    }

    updateReportButtonState();

    const reportUserModal = document.getElementById('reportUserModal');
    const cancelReportBtn = document.getElementById('cancelReportBtn');
    const closeReportModalBtn = document.getElementById('closeReportModalBtn');
    const cancelReportConfirmModal = document.getElementById('cancelReportConfirmModal');
    const confirmCancelReport = document.getElementById('confirmCancelReport');

    let isReportDirty = false;
    let allowReportClose = false;

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
            reportReasonError.textContent = 'Selecciona una razón.';
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
        const hasDescription = !!(reportDescription && reportDescription.value.trim() !=='');

        isReportDirty = hasReason || hasDescription;
    }

    [reportReason, reportDescription].forEach((field) => {
        if (!field) return;

        field.addEventListener('input', updateReportDirtyState);
        field.addEventListener('change', updateReportDirtyState);
    });

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

    function tryCloseReportModal() {
        if (!reportUserModal) return;

        updateReportDirtyState();

        const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);

        if (!isReportDirty) {
            allowReportClose = true;
            reportModalInstance.hide();

            if(postDetailsModal){
                setTimeout(()=> {
                    const postModalInstance=bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show()
                })
            }
            return;
        }

        if (cancelReportConfirmModal) {
            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
            confirmModal.show();
        }
    }

    if (cancelReportBtn) {
        cancelReportBtn.addEventListener('click', tryCloseReportModal);
    }

    if (closeReportModalBtn) {
        closeReportModalBtn.addEventListener('click', (e) =>{
            e.preventDefault();
            tryCloseReportModal();
        })
    }

    const postDetailsModal = document.getElementById('postDetailsModal');

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

    //Rating Functionality
    const ratingContainer = document.getElementById('sellerRatingStars');
    const ratingInput = document.getElementById('sellerRatingValue');
    const ratingText = document.getElementById('sellerRatingText');

    if (ratingContainer && ratingInput && ratingText) {
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

                if (starValue <= value) {
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill');
                } else {
                    star.classList.remove('bi-star-fill');
                    star.classList.add('bi-star');
                }
            });

            ratingText.textContent = ratingLabels[value] || ratingLabels[0];
        }

        stars.forEach((star) => {
            star.addEventListener('mouseenter', function () {
                const value = Number(this.dataset.value);
                paintStars(value);
            });

            star.addEventListener('click', function () {
                const value = Number(this.dataset.value);
                ratingInput.value = value;
                paintStars(value);
            });

            star.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const value = Number(this.dataset.value);
                    ratingInput.value = value;
                    paintStars(value);
                }
            });
        });

        ratingContainer.addEventListener('mouseleave', () => {
            paintStars(Number(ratingInput.value));
        });

        paintStars(Number(ratingInput.value));
    }
});
