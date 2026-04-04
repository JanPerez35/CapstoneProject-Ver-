import * as bootstrap from 'bootstrap';

// DOM references

// Delete post
const deletePostModal = document.getElementById('deletePostModal');
const deletePostModalText = document.getElementById('deletePostModalText');
const confirmDeletePost = document.getElementById('confirmDeletePost');

// Marketplace filters + listing
const clearMarketplaceFilters = document.getElementById('clearMarketplaceFilters');
const marketplaceSearch = document.getElementById('marketplaceSearch');
const searchMarketplaceBtn = document.getElementById('searchMarketplaceBtn');
const marketplaceCategoryFilter = document.getElementById('marketplaceCategoryFilter');
const marketplaceRatingFilter = document.getElementById('marketplaceRatingFilter');
const marketplacePriceFilter = document.getElementById('marketplacePriceFilter');
const marketplaceConditionFilter = document.getElementById('marketplaceConditionFilter');
const marketplaceCardsContainer = document.getElementById('marketplaceCardsContainer');
const marketplaceEmptyState = document.getElementById('marketplaceEmptyState');
const marketplacePagination = document.getElementById('marketplacePagination');

// Post details modal
const postDetailsModal = document.getElementById('postDetailsModal');
const postDetailsModalLabel = document.getElementById('postDetailsModalLabel');
const postDetailsDescription = document.getElementById('postDetailsDescription');

// Star rating system
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

// Toasts
const submitSellerRatingBtn = document.getElementById('submitSellerRatingBtn');
const ratingSentToastEl = document.getElementById('ratingSentToast');
const reportSentToastEl = document.getElementById('reportSentToast');
const postCreatedToastEl = document.getElementById('postCreatedToast');
const postDeletedToastEl = document.getElementById('postDeletedToast');

const ratingSentToast = ratingSentToastEl
    ? bootstrap.Toast.getOrCreateInstance(ratingSentToastEl)
    : null;

const reportSentToast = reportSentToastEl
    ? bootstrap.Toast.getOrCreateInstance(reportSentToastEl)
    : null;

const postCreatedToast = postCreatedToastEl
    ? bootstrap.Toast.getOrCreateInstance(postCreatedToastEl)
    : null;

const postDeletedToast = postDeletedToastEl
    ? bootstrap.Toast.getOrCreateInstance(postDeletedToastEl)
    : null;

// Create post form
const createPostForm = document.getElementById('createPostForm');
const createPostModal = document.getElementById('createPostModal');
const cancelCreatePostBtn = document.getElementById('cancelCreatePostBtn');
const cancelConfirmModal = document.getElementById('cancelConfirmModal');
const confirmCancelCreatePost = document.getElementById('confirmCancelCreatePost');

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

// Reporting
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

// Rating
const ratingContainer = document.getElementById('sellerRatingStars');
const ratingInput = document.getElementById('sellerRatingValue');
const ratingText = document.getElementById('sellerRatingText');
const clearSellerRating = document.getElementById('clearSellerRating');

// Constants and states
const POSTS_PER_PAGE = 18;
const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
const priceRegex = /^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/;
const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const MAX_IMAGES = 3;
const MIN_IMAGES = 1;

let currentMarketplacePage = 1;
let postIdToDelete = null;
let selectedPostImages = [];
let isCreatePostDirty = false;
let allowCreatePostClose = false;
let isReportDirty = false;
let allowReportClose = false;

// Storage
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

// Helpers
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

// Marketplace data and card rendering
async function fileToDataURL(file) {
    return await new Promise((resolve, reject) => {
        const reader = new FileReader();

        reader.onload = () => resolve(reader.result);
        reader.onerror = () => reject(new Error(`No se pudo leer la imagen "${file.name}".`));

        reader.readAsDataURL(file);
    });
}

async function createMarketplacePostObject() {
    const imageUrls = await Promise.all(
        selectedPostImages.map((file) => fileToDataURL(file))
    );

    const firstImage = imageUrls[0] || '';

    return {
        id: Date.now(),
        title: postTitle.value.trim(),
        description: postDescription.value.trim(),
        price: postPrice.value.trim(),
        category: postCategory.value,
        condition: postCondition.value,
        status: 'Disponible',
        seller: 'Usuario actual',
        rating: '0.0',
        reviews: '0',
        createdAt: 'hace unos segundos',
        image: firstImage,
        images: imageUrls,
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
           <div class="card h-100 shadow-sm rounded-4 overflow-hidden item-card border-0 marketplace-card-shell">

               <img
                   src="${post.image}"
                   class="card-img-top"
                   alt="${post.title}"
                   style="height: 220px; object-fit: contain;"
                   onerror="this.onerror=null;this.src='/images/marketplace_images/picture-not-available.png';"
               >

               <div class="card-body p-4 marketplace-card-body">

                   <div class="marketplace-card-top">
                       <div class="marketplace-card-header mb-3">
                           <div class="marketplace-card-header-row">
                               <h5 class="card-title fw-bold marketplace-card-title flex-grow-1 mb-0" title="${post.title}">
                                   ${post.title}
                               </h5>

                               <span class="badge rounded-0 px-3 py-2 marketplace-status-badge" style="background-color:#6FC21F; color:white;">
                                   ${post.status}
                               </span>
                           </div>
                       </div>

                       <p class="text-muted marketplace-card-description mb-0" title="${post.description || ''}">
                           ${post.description}
                       </p>
                   </div>

                   <div class="marketplace-card-meta mt-auto">
                       <h3 class="fw-bold text-success mb-3 marketplace-card-price">
                           $${post.price}
                       </h3>

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

                       <div class="d-grid gap-2">
                           <button
                               type="button"
                               class="btn btn-success rounded-3 open-post-details"
                               data-id="${post.id}"
                           >
                               Ver Detalles
                           </button>

                           <button
                               type="button"
                               class="btn btn-danger rounded-3 open-delete-post-modal"
                               data-id="${post.id}"
                               data-post-title="${post.title}"
                           >
                               Eliminar Publicación
                           </button>
                       </div>
                   </div>

               </div>
           </div>
       </div>
   `;
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

// Marketplace Modal Details
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

    if (postDetailsRatingStars) {
        postDetailsRatingStars.innerHTML = buildStarsHTML(post.rating);
    }

    if (postDetailsRatingValue) {
        postDetailsRatingValue.textContent = post.rating || '0.0';
    }

    if (postDetailsReviewCount) {
        postDetailsReviewCount.textContent = `(${post.reviews || 0})`;
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
        postDetailsSellerRating.innerHTML = `<i class="bi bi-star-fill text-warning me-1"></i> ${post.rating || '0.0'} <span class="text-muted">(${post.reviews || 0} reseñas)</span>`;
    }

    if (postDetailsCategory) {
        postDetailsCategory.textContent = post.category || 'Sin categoría';
    }

    const images = Array.isArray(post.images) && post.images.length
        ? post.images
        : (post.image ? [post.image] : []);

    renderPostDetailsCarousel(images);
}


function attachMarketplaceDetailsEvents() {
    if (!postDetailsModal) return;

    document.querySelectorAll('.open-post-details').forEach((button) => {
        button.addEventListener('click', () => {
            const postId = Number(button.dataset.id);
            const selectedPost = allMarketplacePosts.find(
                (post) => Number(post.id) === postId
            );

            if (!selectedPost) return;

            populatePostDetailsModal(selectedPost);

            const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
            modalInstance.show();
        });
    });
}

function truncateText(text = '', maxLength = 25) {
    const normalized = String(text).trim();

    if (normalized.length <= maxLength) {
        return normalized;
    }

    return normalized.slice(0, maxLength).trimEnd() + '...';
}

// Delete Post
function attachMarketplaceDeleteEvents() {
    if (!deletePostModal || !confirmDeletePost) return;

    document.querySelectorAll('.open-delete-post-modal').forEach((button) => {
        button.addEventListener('click', () => {
            postIdToDelete = Number(button.dataset.id);
            const postTitle = button.dataset.postTitle || 'esta publicación';
            const truncatedPostTitle = truncateText(postTitle, 45);

            if (deletePostModalText) {
                deletePostModalText.innerHTML = `
                <span class="d-block mb-2">Vas a eliminar</span>
                <span class="d-block mb-2 text-break">"<strong>${truncatedPostTitle}</strong>".</span>
                <span class="d-block">Esta acción no se puede deshacer.</span>
            `;
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
            modalInstance.show();
        });
    });
}

if (deletePostModal && confirmDeletePost) {
    confirmDeletePost.addEventListener('click', () => {
        if (postIdToDelete === null) return;

        allMarketplacePosts = allMarketplacePosts.filter(
            (post) => Number(post.id) !== postIdToDelete
        );

        saveMarketplacePosts(allMarketplacePosts);

        const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
        modalInstance.hide();

        postDeletedToast?.show();

        postIdToDelete = null;
        renderMarketplace();
    });
}

// Filters and pagination
function getFilteredMarketplacePosts() {
    const searchValue = (marketplaceSearch?.value || '').trim().toLowerCase();
    const selectedCategory = marketplaceCategoryFilter?.value || 'all';
    const selectedRating = marketplaceRatingFilter?.value || 'all';
    const selectedPriceRange = marketplacePriceFilter?.value || 'all';
    const selectedCondition = marketplaceConditionFilter?.value || 'all';

    return allMarketplacePosts.filter((post) => {
        const postRating = Number(post.rating) || 0;
        const postPriceValue = Number(post.price) || 0;

        const matchesSearch =
            searchValue === '' ||
            post.title.toLowerCase().includes(searchValue) ||
            (post.description || '').toLowerCase().includes(searchValue) ||
            post.category.toLowerCase().includes(searchValue) ||
            post.condition.toLowerCase().includes(searchValue) ||
            post.seller.toLowerCase().includes(searchValue);

        const matchesCategory =
            selectedCategory === 'all' || post.category === selectedCategory;

        let matchesRating = true;

        if (selectedRating !== 'all') {
            const ratingFilter = Number(selectedRating);

            if (ratingFilter === 0) {
                matchesRating = postRating === 0;
            } else {
                matchesRating = postRating > (ratingFilter - 1) && postRating <= ratingFilter;
            }
        }

        const matchesCondition =
            selectedCondition === 'all' || post.condition === selectedCondition;

        let matchesPrice = true;

        if (selectedPriceRange === '0') {
            matchesPrice = postPriceValue === 0;
        } else if (selectedPriceRange === '0.01-9.99') {
            matchesPrice = postPriceValue >= 0.01 && postPriceValue <= 9.99;
        } else if (selectedPriceRange === '10-29.99') {
            matchesPrice = postPriceValue >= 10 && postPriceValue <= 29.99;
        } else if (selectedPriceRange === '30-49.99') {
            matchesPrice = postPriceValue >= 30 && postPriceValue <= 49.99;
        } else if (selectedPriceRange === '50+') {
            matchesPrice = postPriceValue >= 50;
        }

        return matchesSearch && matchesCategory && matchesRating && matchesPrice && matchesCondition;
    });
}

function renderPagination({
                              container,
                              currentPage,
                              totalItems,
                              itemsPerPage,
                              onPageChange,
                          }) {
    if (!container) return;

    const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

    if (totalItems <= 0) {
        container.innerHTML = '';
        return;
    }

    let paginationHTML = '';

    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <button type="button" class="page-link" data-page="prev">&laquo;</button>
        </li>
    `;

    for (let page = 1; page <= totalPages; page++) {
        paginationHTML += `
            <li class="page-item ${page === currentPage ? 'active' : ''}">
                <button type="button" class="page-link" data-page="${page}">${page}</button>
            </li>
        `;
    }

    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <button type="button" class="page-link" data-page="next">&raquo;</button>
        </li>
    `;

    container.innerHTML = paginationHTML;

    container.querySelectorAll('.page-link').forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.page;
            let newPage = currentPage;

            if (action === 'prev' && currentPage > 1) {
                newPage = currentPage - 1;
            } else if (action === 'next' && currentPage < totalPages) {
                newPage = currentPage + 1;
            } else if (!isNaN(action)) {
                newPage = Number(action);
            }

            if (newPage !== currentPage) {
                onPageChange(newPage);
            }
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

    marketplaceCardsContainer
        .querySelectorAll('.marketplace-card')
        .forEach((card) => card.remove());

    if (marketplaceEmptyState) {
        marketplaceEmptyState.classList.toggle('d-none', filteredPosts.length !== 0);
    }

    paginatedPosts.forEach((post) => {
        marketplaceCardsContainer.insertAdjacentHTML(
            'beforeend',
            createMarketplaceCardHTML(post)
        );
    });

    renderPagination({
        container: marketplacePagination,
        currentPage: currentMarketplacePage,
        totalItems: filteredPosts.length,
        itemsPerPage: POSTS_PER_PAGE,
        onPageChange: (page) => {
            currentMarketplacePage = page;
            renderMarketplace();
        },
    });

    attachMarketplaceDeleteEvents();
    attachMarketplaceDetailsEvents();
}

// Post Creation Validation
function validateTitle(showError = true) {
    if (!postTitle || !postTitleError) return true;

    const value = postTitle.value.trim();

    if (showError) {
        clearFieldError(postTitle, postTitleError);
    }

    if (!value) {
        if (showError) {
            setFieldError(postTitle, postTitleError, 'El título es obligatorio.');
        }
        return false;
    }

    if (!allowedTextRegex.test(value)) {
        if (showError) {
            setFieldError(
                postTitle,
                postTitleError,
                'Solo se permiten letras, números, espacios, punto, coma y guion.'
            );
        }
        return false;
    }

    if (value.length < 5) {
        if (showError) {
            setFieldError(postTitle, postTitleError, 'El título debe tener al menos 5 caracteres.');
        }
        return false;
    }

    if (value.length > 100) {
        if (showError) {
            setFieldError(postTitle, postTitleError, 'El título no puede exceder 100 caracteres.');
        }
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

    if (!allowedTextRegex.test(value)) {
        if (showError) {
            setFieldError(
                postDescription,
                postDescriptionError,
                'Solo se permiten letras, números, espacios, punto, coma y guion.'
            );
        }
        return false;
    }

    if (value.length > 500) {
        if (showError) {
            setFieldError(
                postDescription,
                postDescriptionError,
                'La descripción no puede exceder 500 caracteres.'
            );
        }
        return false;
    }

    return true;
}

function validatePrice(showError = true) {
    if (!postPrice || !postPriceError) return true;

    const value = postPrice.value.trim();
    const postPriceGroup = document.getElementById('postPriceGroup');

    if (showError) {
        clearFieldError(postPrice, postPriceError);
        postPriceGroup?.classList.remove('is-invalid');
    }

    if (!value) {
        if (showError) {
            setFieldError(postPrice, postPriceError, 'El precio es obligatorio.');
            postPriceGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (/[eE+\-]/.test(value)) {
        if (showError) {
            setFieldError(
                postPrice,
                postPriceError,
                'No se permite usar e, E, + ni - en el precio.'
            );
            postPriceGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (!priceRegex.test(value)) {
        if (showError) {
            setFieldError(
                postPrice,
                postPriceError,
                'Ingresa un precio válido usando solo números y hasta 2 decimales.'
            );
            postPriceGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (Number(value) < 0) {
        if (showError) {
            setFieldError(postPrice, postPriceError, 'El precio no puede ser negativo.');
            postPriceGroup?.classList.add('is-invalid');
        }
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

// Post creation images
function renderImagePreviews(files) {
    if (!imagePreviewContainer) return;

    imagePreviewContainer.innerHTML = '';

    files.forEach((file, index) => {
        const url = URL.createObjectURL(file);

        imagePreviewContainer.insertAdjacentHTML(
            'beforeend',
            `
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
            `
        );
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

    return bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff;
}

// Post creation modals and state
function resetCreatePostValidation() {
    clearFieldError(postTitle, postTitleError);
    clearFieldError(postDescription, postDescriptionError);
    clearFieldError(postPrice, postPriceError);

    const postPriceGroup = document.getElementById('postPriceGroup');
    postPriceGroup?.classList.remove('is-invalid');

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

// Reporting
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

    if (!allowedTextRegex.test(value)) {
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

    if (value.length > 500) {
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
            });
        }
        return;
    }

    if (cancelReportConfirmModal) {
        const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
        confirmModal.show();
    }
}

// Rating
function initializeSellerRating() {
    if (!ratingContainer || !ratingInput || !ratingText) return;

    const stars = ratingContainer.querySelectorAll('.rating-star');

    const ratingLabels = {
        0: 'Selecciona una calificación',
        1: 'Malo',
        2: 'Regular',
        3: 'Bueno',
        4: 'Muy Bueno',
        5: 'Excelente',
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
        paintStars(Number(ratingInput.value) || 0);
    });

    if (clearSellerRating) {
        clearSellerRating.addEventListener('click', () => {
            ratingInput.value = 0;
            paintStars(0);
        });
    }

    paintStars(Number(ratingInput.value) || 0);
}

// Event Listeners

// Seller rating toast
if (submitSellerRatingBtn) {
    submitSellerRatingBtn.addEventListener('click', () => {
        const ratingValue = Number(document.getElementById('sellerRatingValue')?.value || 0);

        if (!ratingValue) return;

        ratingSentToast?.show();
    });
}

// Search and filters
if (searchMarketplaceBtn) {
    searchMarketplaceBtn.addEventListener('click', () => {
        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

if (clearMarketplaceFilters) {
    clearMarketplaceFilters.addEventListener('click', () => {
        if (marketplaceSearch) marketplaceSearch.value = '';
        if (marketplaceCategoryFilter) marketplaceCategoryFilter.value = 'all';
        if (marketplaceRatingFilter) marketplaceRatingFilter.value = 'all';
        if (marketplacePriceFilter) marketplacePriceFilter.value = 'all';
        if (marketplaceConditionFilter) marketplaceConditionFilter.value = 'all';

        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

if (marketplaceSearch) {
    marketplaceSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            currentMarketplacePage = 1;
            renderMarketplace();
        }
    });
}

if (marketplaceConditionFilter) {
    marketplaceConditionFilter.addEventListener('change', () => {
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

if (marketplaceRatingFilter) {
    marketplaceRatingFilter.addEventListener('change', () => {
        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

if (marketplacePriceFilter) {
    marketplacePriceFilter.addEventListener('change', () => {
        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

// Create post inputs
if (postTitle) {
    postTitle.addEventListener('input', () => {
        const value = postTitle.value;

        if (value.length > 100) {
            postTitle.value = value.slice(0, 100);
            setFieldError(postTitle, postTitleError, 'Has alcanzado el máximo de 100 caracteres. No puedes escribir más.');
        } else if (value.length === 100) {
            setFieldError(postTitle, postTitleError, 'Has alcanzado el máximo de 100 caracteres.');
        } else {
            validateTitle(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

if (postDescription) {
    postDescription.addEventListener('input', () => {
        const value = postDescription.value;

        if (value.length > 500) {
            postDescription.value = value.slice(0, 500);
            setFieldError(
                postDescription,
                postDescriptionError,
                'Has alcanzado el máximo de 500 caracteres. No puedes escribir más.'
            );
        } else if (value.length === 500) {
            setFieldError(
                postDescription,
                postDescriptionError,
                'Has alcanzado el máximo de 500 caracteres.'
            );
        } else {
            validateDescription(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

if (reportDescription) {
    reportDescription.addEventListener('input', () => {
        const value = reportDescription.value;

        if (value.length > 500) {
            reportDescription.value = value.slice(0, 500);
            reportDescription.classList.add('is-invalid');
            reportDescriptionError.textContent =
                'Has alcanzado el máximo de 500 caracteres. No puedes escribir más.';
        } else if (value.length === 500) {
            reportDescription.classList.add('is-invalid');
            reportDescriptionError.textContent =
                'Has alcanzado el máximo de 500 caracteres.';
        } else {
            validateReportDescription(true);
        }

        updateReportButtonState();
        updateReportDirtyState();
    });
}


if (postPrice) {
    postPrice.addEventListener('keydown', (e) => {
        if (['e', 'E', '+', '-'].includes(e.key)) {
            e.preventDefault();
        }
    });

    postPrice.addEventListener('input', () => {
        let value = postPrice.value.replace(/[^0-9.]/g, '');

        if (value.startsWith('.')) {
            value = '0' + value;
        }

        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        const firstDotIndex = value.indexOf('.');
        if (firstDotIndex !== -1) {
            const integerPart = value.slice(0, firstDotIndex);
            const decimalPart = value.slice(firstDotIndex + 1).slice(0, 2);
            value = integerPart + '.' + decimalPart;
        }

        postPrice.value = value;

        validatePrice(true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
    });

    postPrice.addEventListener('blur', () => {
        const rawValue = postPrice.value.trim();

        if (!rawValue) return;

        const numericValue = Number(rawValue);

        if (Number.isNaN(numericValue)) {
            postPrice.value = '';
            validatePrice(true);
            updatePublishButtonState();
            updateCreatePostDirtyState();
            return;
        }

        postPrice.value = numericValue.toFixed(2);
        validatePrice(true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

if (postCategory) {
    postCategory.addEventListener('change', () => {
        validateSelect(postCategory, true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

if (postCondition) {
    postCondition.addEventListener('change', () => {
        validateSelect(postCondition, true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
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
            showImageError(
                `Solo puedes agregar ${allowedSlots} imagen${allowedSlots === 1 ? '' : 'es'} más.`
            );
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

// Track dirty state
[postTitle, postDescription, postPrice, postCategory, postCondition].forEach((field) => {
    if (!field) return;

    field.addEventListener('input', updateCreatePostDirtyState);
    field.addEventListener('change', updateCreatePostDirtyState);
});

// Publish post
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

        const newPost = await createMarketplacePostObject();
        allMarketplacePosts.unshift(newPost);
        saveMarketplacePosts(allMarketplacePosts);

        currentMarketplacePage = 1;
        renderMarketplace();

        const createModalInstance = bootstrap.Modal.getOrCreateInstance(createPostModal);
        allowCreatePostClose = true;
        createModalInstance.hide();

        resetCreatePostLocalState();

        setTimeout(() => {
            postCreatedToast?.show();
        }, 250);
    });
}

// Create post modal behavior
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

if (confirmCancelCreatePost && createPostModal && cancelConfirmModal) {
    confirmCancelCreatePost.addEventListener('click', () => {
        allowCreatePostClose = true;

        const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
        confirmModal.hide();

        const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);
        modal.hide();
    });
}

// Reporting inputs
if (reportReason) {
    reportReason.addEventListener('change', () => {
        validateReportReason(true);
        updateReportButtonState();
    });
}

[reportReason, reportDescription].forEach((field) => {
    if (!field) return;

    field.addEventListener('input', updateReportDirtyState);
    field.addEventListener('change', updateReportDirtyState);
});

// Submit report
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

// Report modal behavior
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

// Initialization
initializeSellerRating();
updatePublishButtonState();
updateReportButtonState();
renderMarketplace();
