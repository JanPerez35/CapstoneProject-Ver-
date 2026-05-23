import * as bootstrap from 'bootstrap';
import { findProfanity } from './utils/profanity_checker.js';
/**
 * Initializes kine marketplace page behavior and all client-side kine marketplace interactions.
 *
 * Responsibilities:
 * - retrieves all marketplace posts from the backend
 * - renders post cards into the marketplace grid
 * - applies search, category, rating, price, and condition filters
 * - paginates filtered post results
 * - opens and populates the post details modal
 * - opens chat for a selected post and preserves return context
 * - manages delete-post confirmation flow
 * - validates the create-post form
 * - validates uploaded marketplace images
 * - previews selected images before submission
 * - submits new marketplace posts
 * - validates and submits seller reports
 * - manages the seller star-rating selection UI
 * - submits seller ratings
 * - controls create-post and report modal dirty-state confirmation behavior
 * - displays success toasts for rating, reporting, post creation, and post deletion
 *
 * This file acts as the main front-end controls for the kinemarketplace page.
 */


/**
 * DOM References
 * */

/**
 * Delete-post modal references.
 *
 * These elements are used to:
 * - open the delete confirmation modal
 * - inject/place the selected post title into the confirmation text
 * - confirm deletion of the selected post
 */
const deletePostModal = document.getElementById('deletePostModal');
const deletePostModalText = document.getElementById('deletePostModalText');
const confirmDeletePost = document.getElementById('confirmDeletePost');

/**
 * Marketplace filter and listing references.
 *
 * These elements are used to:
 * - search marketplace posts by text
 * - clear all active filters
 * - filter by category, seller rating, price range, and condition
 * - render marketplace post cards
 * - show the empty state when no posts match
 * - render pagination controls
 */
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
const marketplacePaginationSummary = document.getElementById('marketplacePaginationSummary');

/**
 * Post details modal references.
 * These elements are updated whenever the user opens
 * the details modal for a marketplace post.
 */
const postDetailsModal = document.getElementById('postDetailsModal');
const postDetailsModalLabel = document.getElementById('postDetailsModalLabel');
const postDetailsDescription = document.getElementById('postDetailsDescription');

/**
 * Post details rating display references.
 *
 * These elements show:
 * - graphical star icons
 * - numeric rating value
 * - number of reviews
 */
const postDetailsRatingStars = document.getElementById('postDetailsRatingStars');
const postDetailsRatingValue = document.getElementById('postDetailsRatingValue');
const postDetailsReviewCount = document.getElementById('postDetailsReviewCount');

/**
 * Additional post details fields displayed in the modal.
 *
 * These show:
 * - post price
 * - post availability/status
 * - item condition
 * - seller name
 * - seller rating summary
 * - post category
 */
const postDetailsPrice = document.getElementById('postDetailsPrice');
const postDetailsStatus = document.getElementById('postDetailsStatus');
const postDetailsCondition = document.getElementById('postDetailsCondition');
const postDetailsSeller = document.getElementById('postDetailsSeller');
const postDetailsSellerRating = document.getElementById('postDetailsSellerRating');
const postDetailsCategory = document.getElementById('postDetailsCategory');

/**
 * Carousel references used in the post details modal.
 * These elements are used to render one or more images
 * for the selected marketplace post.
 */
const postImagesCarouselIndicators = document.getElementById('postImagesCarouselIndicators');
const postImagesCarouselInner = document.getElementById('postImagesCarouselInner');
const postImagesCarouselPrev = document.getElementById('postImagesCarouselPrev');
const postImagesCarouselNext = document.getElementById('postImagesCarouselNext');

/**
 * Report modal text reference.
 * Used to dynamically show which seller the user is reporting.
 */
const reportUserText = document.getElementById('reportUserText');

/**
 * Toast references.
 *
 * These toasts provide the user feedback for:
 * - seller rating successfully sent
 * - report successfully sent
 * - post successfully created
 * - post successfully deleted
 */
const submitSellerRatingBtn = document.getElementById('submitSellerRatingBtn');
const ratingSentToastEl = document.getElementById('ratingSentToast');
const reportSentToastEl = document.getElementById('reportSentToast');
const postCreatedToastEl = document.getElementById('postCreatedToast');
const postDeletedToastEl = document.getElementById('postDeletedToast');

/**
 * Bootstrap toast instances.
 *
 * Each toast is only created if its corresponding document object model element exists.
 * If the element does not exist, the variable is set to null so that runtime errors
 * can be avoided.
 */
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

/**
 * Create-post form references.
 *
 * These elements are used to:
 * - read user input values
 * - validate required fields
 * - show validation messages
 * - preview uploaded images
 * - control modal closing behavior
 */
const createPostForm = document.getElementById('createPostForm');
const createPostModal = document.getElementById('createPostModal');
const cancelCreatePostBtn = document.getElementById('cancelCreatePostBtn');
const cancelConfirmModal = document.getElementById('cancelConfirmModal');
const confirmCancelCreatePost = document.getElementById('confirmCancelCreatePost');

const publishBtn = document.getElementById('publishBtn');
let isPublishingPost = false;
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

/**
 * Report form references.
 *
 * These elements are used to:
 * - collect the reason for the report
 * - collect the report description
 * - validate report fields
 * - control report modal closing behavior
 */
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

/**
 * Seller rating input references.
 * These elements support the interactive star selection UI
 * used when rating a seller from the post details modal.
 */
const ratingContainer = document.getElementById('sellerRatingStars');
const ratingInput = document.getElementById('sellerRatingValue');
const ratingText = document.getElementById('sellerRatingText');
const clearSellerRating = document.getElementById('clearSellerRating');

/**
 * Constants and states
 */

/**
 * Number of marketplace post cards shown per page.
 */
const POSTS_PER_PAGE = 18;

/**
 * Allowed characters for marketplace text fields and report description fields.
 *
 * Allows:
 * - uppercase and lowercase English letters
 * - accented vowels
 * - ñ / Ñ
 * - digits
 * - spaces
 * - periods
 * - commas
 * - hyphens
 */
const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

/**
 * Valid price format.
 * - allows 0
 * - allows positive integers without leading zeros
 * - optionally allows 1 or 2 decimal places
 *
 * Examples tof accepted values:
 * 0,5, 12, 12.5, 142.50, 02.45
 *
 * Examples of rejected values:
 * 12.345, .99 ,1., 2.E4, +2.5, -5.78
 */
const priceRegex = /^(\d+)(\.\d{1,2})?$/

/**
 * Maximum allowed post price.
 */
const MAX_PRICE = 10000;

/**
 * Maximum allowed image file size in bytes.
 * Current limit: 2 MB.
 */
const MAX_IMAGE_SIZE = 2 * 1024 * 1024;

/**
 * Maximum and minimum number of post images allowed.
 */
const MAX_IMAGES = 3;
const MIN_IMAGES = 1;


/**
 * Post limit modal references.
 *
 * These elements are used to:
 * - detect when the user reaches the 15-post limit within 15 days
 * - prevent the create-post modal from opening when the limit is reached
 * - open the max-post-limit modal instead
 */
const openCreatePostBtn = document.querySelector('.open-create-post');
const maxPostLimitModal = document.getElementById('maxPostLimitModal');
const nextPostAvailableTime = document.getElementById('nextPostAvailableTime');

/**
 * Post limit state.
 *
 * Stores the number of posts created by the current user within the last 15 days,
 * the maximum number of posts allowed in that period, and whether the limit
 * has already been reached.
 *
 * These values are loaded from the backend through /user/post-limit.
 */
let userPostsLast15Days = 0;
let MAX_POSTS_15_DAYS = 15;
let userHasReachedPostLimit = false;

/**
 * Loads the current user's marketplace post limit information from the backend.
 *
 * The backend returns how many posts the user has created within the last 15 days,
 * the maximum allowed posts, and whether the limit has been reached.
 *
 * If the request fails, the function falls back to safe default values
 * and still applies the post-limit state to the create-post button.
 */
async function loadPostLimit() {
    try {
        const res = await fetch('/user/post-limit');

        if (!res.ok) throw new Error('Error en endpoint');

        const data = await res.json();

        userPostsLast15Days = data.posts_last_15_days ?? 0;
        MAX_POSTS_15_DAYS = data.max_posts ?? 15;

    } catch (error) {
        console.error('Error cargando límite:', error);

        // Safety fallback
        userPostsLast15Days = 0;
        MAX_POSTS_15_DAYS = 15;
    }

    applyPostLimitState();
}



/**
 * Applies the 15-day post limit state to the create-post button.
 *
 * If the user has reached the maximum number of allowed posts, the button keeps
 * its visual disabled styling, but it is not truly disabled so the click listener
 * can still open the max-post-limit modal.
 *
 * When the user is below the limit, the button is restored so it can open
 * the create-post modal normally.
 */
function applyPostLimitState() {
    if (!openCreatePostBtn) return;

    userHasReachedPostLimit =
        MAX_POSTS_15_DAYS > 0 &&
        userPostsLast15Days >= MAX_POSTS_15_DAYS;

    openCreatePostBtn.classList.toggle('disabled', userHasReachedPostLimit);
    openCreatePostBtn.setAttribute('aria-disabled', userHasReachedPostLimit ? 'true' : 'false');

    if (userHasReachedPostLimit) {
        openCreatePostBtn.removeAttribute('data-bs-toggle');
        openCreatePostBtn.removeAttribute('data-bs-target');
    } else {
        openCreatePostBtn.setAttribute('data-bs-toggle', 'modal');
        openCreatePostBtn.setAttribute('data-bs-target', '#createPostModal');
    }
}

/**
 * Initializes the 15-day post limit state when the marketplace page loads.
 */
loadPostLimit();


/**
 * Handles clicks on the create-post button when the 15-day post limit is reached.
 *
 * Prevents the create-post modal from opening and shows the max-post-limit modal
 * explaining that the user must delete a post or wait until an existing post
 * falls outside the 15-day period.
 */
openCreatePostBtn?.addEventListener('click', (event) => {
    if (!userHasReachedPostLimit) return;

    event.preventDefault();
    event.stopPropagation();

    if (maxPostLimitModal) {
        bootstrap.Modal.getOrCreateInstance(maxPostLimitModal).show();
    }
});

/**
 * Marketplace runtime state.
 *
 * These elements are used to:
 * - track the currently visible page of marketplace cards
 * - store the selected post ID pending deletion confirmation
 * - store the image File objects chosen for post creation
 * - indicates whether the create-post form has unsaved content
 * - bypass flag that allows the create-post modal to close
 *   without prompting the user again
 * - indicates whether the report form has unsaved content
 * - bypass flag that allows the report modal to close
 *   without prompting the user again
 * - stores the seller ID associated with the currently viewed post
 */
let currentMarketplacePage = 1;
let postIdToDelete = null;
let selectedPostImages = [];
let isCreatePostDirty = false;
let allowCreatePostClose = false;
let isReportDirty = false;
let allowReportClose = false;
let reportedUserId = null;

/**
 * Marketplace data state.
 *
 * These elements are used to:
 *  store the full list of posts returned from the backend
 *  and store the currently selected post ID used for seller
 *  rating submission
 */
let allMarketplacePosts =[];
let currentPostId = null;


/**
 * Fetches all marketplace posts from the backend and refreshes the UI.
 *
 * Sends a GET request to /posts, parsed the JSON response, stores the
 * returned posts in allMarketplacePosts, re-renders the marketplace grid, and
 * reopens a returned post modal if the user came back from chat.
 */
async function fetchPosts() {
    const res = await fetch('/posts');
    const data = await res.json();

    allMarketplacePosts = data;
    renderMarketplace();
    openReturnedPostIfNeeded();
}

/**
 * Marks a form field as invalid and displays its error message.
 *
 *  It adds the Bootstrap is-invalid class to the field and
 *  writes the provided message into the error element if it is present
 *
 * @param {HTMLElement|null} field - The input/select/textarea to mark invalid.
 * @param {HTMLElement|null} errorElement - Element where the message should appear.
 * @param {string} message - Validation message to display.
 */
function setFieldError(field, errorElement, message) {
    if (!field) return;

    field.classList.add('is-invalid');

    if (errorElement) {
        errorElement.textContent = message;
    }
}


/**
 * Removes invalid styling and clears the related error message.
 *
 * Removes the Bootstrap is-invalid class from the field and
 * clears the text content of the provided error element if it is present
 *
 * @param {HTMLElement|null} field - The input/select/textarea to clear.
 * @param {HTMLElement|null} errorElement - Error element to clear.
 */
function clearFieldError(field, errorElement) {
    if (!field) return;

    field.classList.remove('is-invalid');

    if (errorElement) {
        errorElement.textContent = '';
    }
}


/**
 * Displays an image validation error for the create-post form.
 *
 * It sets the image error text and
 * makes the image error element visible
 *
 * @param {string} message - The error message shown to the user.
 */
function showImageError(message) {
    if (!imageError) return;

    imageError.textContent = message;
    imageError.classList.remove('d-none');
}


/**
 * Clears the image upload validation error and hides
 * the image error element.
 */
function clearImageError() {
    if (!imageError) return;

    imageError.textContent = '';
    imageError.classList.add('d-none');
}

/**
 * Formats the publication text shown on each marketplace card.
 *
 * If the post is less than 7 days old, it keeps the relative
 * time text returned by the backend, such as "hace 38 segundos",
 * "hace 2 horas", or "hace 4 días".
 *
 * If the post is 7 days old or older, it displays the exact
 * publication date using the format dd-MMM-yyyy in Spanish.
 *
 * @param {Object} post - Marketplace post object.
 * @returns {string} Formatted publication text.
 */
function formatPostPublishedDate(post) {
    if (!post.created_at) {
        return `Publicado ${post.time_ago}`;
    }

    const createdAt = new Date(post.created_at);
    const now = new Date();

    const daysPassed = (now - createdAt) / (1000 * 60 * 60 * 24);

    if (daysPassed < 7) {
        return `Publicado ${post.time_ago}`;
    }

    const formattedDate = createdAt.toLocaleDateString('es-PR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });

    const cleanedDate = formattedDate
        .replaceAll(' de ', ' ')
        .split(' ')
        .map((part, index) => {
            if (index === 1) {
                return part.charAt(0).toUpperCase() + part.slice(1);
            }

            return part;
        })
        .join(' ');

    return `Publicado el ${cleanedDate}`;


}

/**
 * Builds the HTML string for a single marketplace card.
 *
 * Includes information such as:
 * main image or fallback placeholder, title, status badge,
 * description, price , condition badge, category badge, seller name,
 * rating and review count, time-ago text, details button, and delete button.
 *
 * The card also stores several post attributes inside data attributes,
 * which can later be used by event handlers.
 *
 * @param {Object} post - The marketplace post object returned by the backend.
 * @returns {string} The HTML markup for one marketplace card.
 */
function createMarketplaceCardHTML(post) {
    const container = document.getElementById('marketplaceHome');
    const currentUserId = Number(container.dataset.currentUserId);
    const currentUserRole = container.dataset.currentUserRole;

    const isAdmin = ['Super Administrador', 'Administrador de Mercado'].includes(currentUserRole);
    const isOwner = currentUserId === post.user?.id;

    let deleteButton = '';

    if (isAdmin || isOwner) {
        deleteButton = `
            <button
                type="button"
                class="btn btn-danger rounded-3 open-delete-post-modal"
                data-id="${post.id}"
                data-post-title="${post.title}"
            >
                Eliminar Publicación
            </button>
        `;
    }

    return `
       <div
           class="col-md-6 col-lg-4 marketplace-card"
           data-id="${post.id}"
           data-title="${post.title}"
           data-description="${post.description}"
           data-category="${post.category}"
           data-status="${post.status}"
           data-condition="${post.condition}"
           data-seller="${post.user?.name || 'Usuario'}"
       >
           <div class="card h-100 rounded-4 overflow-hidden item-card border-dark border-3 marketplace-card-shell">

               <img
                   src="${post.photo_1_url ? '/storage/' + post.photo_1_url : '/images/marketplace_images/picture-not-available.png'}"
                   class="card-img-top"
                   alt="${post.title}"
                   style="height: 300px; object-fit: contain;"
                   onerror="this.onerror=null;this.src='/images/marketplace_images/picture-not-available.png';"
               >

               <div class="card-body p-4 marketplace-card-body">

                   <div class="marketplace-card-top">
                       <div class="marketplace-card-header mb-3">
                           <div class="marketplace-card-header-row">
                               <h5 class="card-title fw-bold marketplace-card-title flex-grow-1 mb-0" title="${post.title}">
                                   ${post.title}
                               </h5>

                               <span class="label-badge badge-available marketplace-status-badge">
                                     ${post.status}
                                </span>
                           </div>
                       </div>

                       <p class="text-muted marketplace-card-description mb-0" title="${post.description || ''}">
                           ${post.description || ''}
                       </p>
                   </div>

                   <div class="marketplace-card-meta mt-auto">
                       <h3 class="fw-bold text-success mb-3 marketplace-card-price">
                           $${post.cost}
                       </h3>

                       <div class="d-flex gap-2 mb-3 flex-wrap">
                          <span class="label-badge badge-available">
                             ${post.condition}
                          </span>

                           <span class="label-badge badge-available">
                               ${post.category}
                           </span>
                       </div>

                       <div class="small text-muted mb-3">
                           <div class="mb-2">
                               <i class="bi bi-person me-2"></i> ${post.user?.name || 'Usuario'}
                           </div>

                           <div class="mb-2">
                               <i class="bi bi-star-fill text-warning me-2"></i> ${post.rating} (${post.reviews} calificaciones)
                           </div>

                           <div>
                               <i class="bi bi-clock me-2"></i> ${formatPostPublishedDate(post)}
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

                            ${deleteButton}
                       </div>
                   </div>

               </div>
           </div>
       </div>
   `;
}

/**
 * Builds the HTML for a five-star display from a numeric rating.
 *
 * It converts the incoming value to a number, calculates full stars,
 * adds a half star when decimal part is 0.5 or greater, and
 * fills remaining spaces with empty stars.
 *
 * @param {number|string} value - The numeric or string rating value.
 * @returns {string} The HTML string containing Bootstrap icon markup for stars.
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
 * Renders the image carousel for the currently selected post.
 *
 * It clears images from previous post, inserts one indicator dot per image,
 * inserts one carousel item slide per image, marks the first image as active, and
 * hides previous/next carousel controls when there is only one or zero images.
 *
 * @param {string[]} [images=[]] - Array of post image URLs.
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

        /**
         * Populates the post details modal with the selected post data.
         *
         * @param {Object} post - The marketplace post to display.
         */
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
 * Populates the post details modal with the selected post's data.
 *
 * For the marketplace it stores the seller ID for future report submission,
 * stores the post ID on the modal dataset for future report submission,
 * wires the chat button to open or create a chat for the specific post,
 * updates modal title and description, rating stars and numeric rating summary,
 * price, seller, status, condition; and category,updates report text so it mentions
 *  the seller by name, and builds and renders the image carousel from available
 *  stored image paths.
 *
 * For the chat it sends a POST request to /chats/open, passes post_id and seller_id,
 * if backend redirects, appends: return_to=/kinemarket and post_id=<selected post id>, and
 * then navigates to the generated chat URL.
 *
 * @param {Object} post - The marketplace post object to display.
 */
window.populatePostDetailsModal = function (post) {
    if (post.user?.id) {
        reportedUserId = post.user.id;
    }

    if (postDetailsModal) {
        postDetailsModal.dataset.postId = post.id;
    }

    const container = document.getElementById('marketplaceHome');
    const currentUserId = container ? Number(container.dataset.currentUserId) : null;
    const isOwner = container && post.user ? currentUserId === post.user.id : false;
    const postDetailsChatLink = document.getElementById('postDetailsChatLink');
    const restrictedSection = document.getElementById('postOwnerRestrictedSection');

    if (restrictedSection) {
        if (isOwner) {
            restrictedSection.classList.add('d-none');
        } else {
            restrictedSection.classList.remove('d-none');
        }
    }

    if (postDetailsChatLink && post) {
        postDetailsChatLink.onclick = async (e) => {
            e.preventDefault();

            try {
                const response = await fetch('/chats/open', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        post_id: post.id,
                        seller_id: post.user.id
                    })
                });

                if (response.redirected) {
                    const redirectUrl = new URL(response.url, window.location.origin);

                    redirectUrl.searchParams.set('return_to', '/kinemarket');
                    redirectUrl.searchParams.set('post_id', String(post.id));

                    window.location.href = redirectUrl.toString();
                }

            }
            catch (error) {
                console.error('Error creando chat:', error);
            }
        };
    }

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
        postDetailsPrice.textContent = `$${post.cost || '0.00'}`;
    }


    if (postDetailsSeller) {
        postDetailsSeller.textContent = post.user?.name || 'Usuario';
    }

    if (reportUserText) {
        reportUserText.textContent = `Reportar a ${post.user?.name || 'Usuario'} por comportamiento sospechoso`;
    }

    if (postDetailsSellerRating) {
        postDetailsSellerRating.innerHTML =
            `<i class="bi bi-star-fill text-warning me-1"></i> ${post.rating || '0.0'} <span class="text-muted">(${post.reviews || 0} reseñas)</span>`;
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
 * Reopens a post details modal after the user returns from the chat view.
 *
 * It reads the stored post ID from sessionStorage under marketplaceReturnPostId.
 * Then it finds the matching post in allMarketplacePosts, removes the stored key so it only runs once,
 * repopulates and reopens the modal, and scrolls the matching marketplace card into view.
 *
 */
function openReturnedPostIfNeeded() {
    const storedPostId = Number(sessionStorage.getItem('marketplaceReturnPostId'));
    if (!storedPostId) return;

    const matchedPost = allMarketplacePosts.find(
        (post) => Number(post.id) === storedPostId
    );

    sessionStorage.removeItem('marketplaceReturnPostId');

    if (!matchedPost || !postDetailsModal) return;

    populatePostDetailsModal(matchedPost);

    const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
    modalInstance.show();

    const matchingCard = document.querySelector(`.marketplace-card[data-id="${storedPostId}"]`);
    if (matchingCard) {
        matchingCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

}

/**
 * Attaches click listeners to all "Ver Detalles" buttons currently rendered.
 *
 * It reads the clicked post ID from data-id, finds the matching post object in allMarketplacePosts,
 * stores currentPostId for seller rating submission, populates the details modal and
 * opens the details modal.
 */
function attachMarketplaceDetailsEvents() {
    if (!postDetailsModal) return;

    document.querySelectorAll('.open-post-details').forEach((button) => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const postId = Number(button.dataset.id);
            const selectedPost = allMarketplacePosts.find(
                (post) => Number(post.id) === postId
            );

            if (!selectedPost) return;

            currentPostId = selectedPost.id;

            populatePostDetailsModal(selectedPost);

            const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
            modalInstance.show();
        });
    });
}

/**
 * Truncates text to a maximum length and appends an (...) if required for viewing.
 * It is used when showing post titles inside the delete confirmation modal
 * so excessively long titles do not break the modal layout.
 *
 * @param {string} [text=''] - The original text.
 * @param {number} [maxLength=25] - The maximum length before truncation.
 * @returns {string} The original or truncated text.
 */
function truncateText(text = '', maxLength = 25) {
    const normalized = String(text).trim();

    if (normalized.length <= maxLength) {
        return normalized;
    }

    return normalized.slice(0, maxLength).trimEnd() + '...';
}


/**
 * Attaches click listeners to all delete-post buttons currently rendered.
 *
 *  It stores the selected post ID in postIdToDelete.
 * Then it reads the post title from the button dataset, truncates the title for display,
 *  injects the formatted confirmation message into the modal, and opens the delete confirmation modal.
 */
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

/**
 * Confirms post deletion.
 *
 * Sends a DELETE request for the selected post, refreshes the marketplace list,
 * reloads the user's 15-day post limit state, closes the delete modal, shows
 * the deletion success toast, and clears the selected post ID.
 */
if (deletePostModal && confirmDeletePost) {
    confirmDeletePost.addEventListener('click', async () => {
        if (postIdToDelete === null) return;

        await fetch(`/posts/${postIdToDelete}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        await fetchPosts();
        await loadPostLimit();

        const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
        modalInstance.hide();

        postDeletedToast?.show();

        postIdToDelete = null;
    });
}

/**
 * Returns all posts that match the current marketplace filters.
 *
 * Search matching is case-insensitive and checks:
 * - title
 * - description
 * - category
 * - condition
 * - seller name
 *
 * For the filters
 *
 * Category and Condition filters show an exact match unless the value is "all"
 *
 * In the rating filter 0 means only unrated / zero-rated posts. All other values
 * are rated in buckets where : 1 = (0, 1] , 2 = (1,2], etc.
 *
 * Price filters through ranged buckets where: 0, 0.01-9.99, 10-29.99, etc.
 * @returns {Object[]} Filtered array of marketplace post objects.
 */
function getFilteredMarketplacePosts() {
    const searchValue = (marketplaceSearch?.value || '').trim().toLowerCase();
    const selectedCategory = marketplaceCategoryFilter?.value || 'all';
    const selectedRating = marketplaceRatingFilter?.value || 'all';
    const selectedPriceRange = marketplacePriceFilter?.value || 'all';
    const selectedCondition = marketplaceConditionFilter?.value || 'all';

    return allMarketplacePosts.filter((post) => {
        const postRating = Number(post.rating) || 0;
        const postPriceValue = Number(post.cost) || 0;

        const matchesSearch =
            searchValue === '' ||
            post.title.toLowerCase().includes(searchValue) ||
            (post.description || 'Sin descripción').toLowerCase().includes(searchValue) ||
            post.category.toLowerCase().includes(searchValue) ||
            post.condition.toLowerCase().includes(searchValue) ||
            post.user?.name.toLowerCase().includes(searchValue);

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


/**
 * Renders pagination controls inside a container.
 *
 * Calculates the total pages from item count and itemsPerPage.
 * Renders the previous, next buttons, and the numbered page button.
 * Attaches click handlers that compute the destination page.
 *
 * Hides the pagination when there are zero items/posts on screen and
 * calls onPageChange(newPage) only when the page actually changes.
 *
 * @param {Object} config - Pagination configuration.
 * @param {HTMLElement|null} config.container - Pagination container element.
 * @param {number} config.currentPage - The current active page.
 * @param {number} config.totalItems - Total number of filtered items.
 * @param {number} config.itemsPerPage - The items to show per page.
 * @param {Function} config.onPageChange - The callback function executed when a new page is selected.
 */
function renderPagination({container, currentPage, totalItems, itemsPerPage, onPageChange,}) {
    if (!container) return;

    const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
    const summary = container === marketplacePagination ? marketplacePaginationSummary : null;

    if (totalItems <= 0) {
        container.innerHTML = '';
        if (summary) summary.textContent = '';
        return;
    }

    if (summary) {
        const firstItem = ((currentPage - 1) * itemsPerPage) + 1;
        const lastItem = Math.min(currentPage * itemsPerPage, totalItems);
        summary.innerHTML = `
            Mostrando <strong>${firstItem}</strong>
            a <strong>${lastItem}</strong>
            de <strong>${totalItems}</strong> resultados
        `;
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


/**
 * Renders the visible marketplace page.
 *
 *  Gets all filtered posts, calculates total page count,
 *  removes previously rendered marketplace cards, shows or hides the empty state,
 *  inserts the paginated cards into the grid, renders pagination controls
 *  and rebinds delete and details button listeners for the newly inserted cards/posts.
 *
 */
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

/**
 * Validates the create-post title field.
 *
 * The title is a required field, that only allows characters stated on
 * allowedTextRegex, and has a minimum length of 5 characters and a maximum length of 100 characters.
 *
 * @param {boolean} [showError=true] - Tells whether a validation messages should be displayed.
 * @returns {boolean} True if valid title, otherwise false.
 */
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
                'Solo se permiten letras, números, espacios, puntos, comas y guiones.'
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

/**
 * Validates create-post title and description fields for profanity.
 *
 * Checks both text fields using the shared profanity checker before allowing
 * a marketplace post to be published. If inappropriate language is detected,
 * the related field is marked invalid, the base validation message is cleared,
 * and a profanity-specific error message is displayed.
 *
 * @param {boolean} [showError=true] - Whether profanity error messages should be displayed.
 * @returns {boolean} True when no profanity is detected, otherwise false.
 */
function validateCreatePostProfanity(showError = true) {
    const titleValue = postTitle?.value.trim() || '';
    const descriptionValue = postDescription?.value.trim() || '';

    const titleProfanityError = document.getElementById('postTitleProfanityError');
    const descriptionProfanityError = document.getElementById('postDescriptionProfanityError');

    let isValid = true;

    if(showError) {
        if (titleProfanityError) titleProfanityError.textContent = '';
        if (descriptionProfanityError) descriptionProfanityError.textContent = '';
    }

    if (titleValue && findProfanity(titleValue)) {
        isValid = false;
        if (showError) {
            postTitle.classList.add('is-invalid');
            postTitleError.textContent = '';
            titleProfanityError.textContent = 'El título contiene lenguaje inapropiado.';
        }
    }

    if (descriptionValue && findProfanity(descriptionValue)) {
        isValid = false;
        if (showError) {
            postDescription.classList.add('is-invalid');
            postDescriptionError.textContent = '';
            descriptionProfanityError.textContent = 'La descripción contiene lenguaje inapropiado.';
        }
    }

    return isValid;
}

/**
 * Validates the create-post description field.
 *
 * The description  is an optional field, that only allows characters stated on
 * allowedTextRegex, and has a maximum length of 500 characters.
 *
 * @param {boolean} [showError=true] - Tells whether a validation messages should be displayed.
 * @returns {boolean} True if valid description, otherwise false.
 */
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
                'Solo se permiten letras, números, espacios, puntos, comas y guiones.'
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

/**
 * Validates the create-post price field.
 *
 *
 * The price is a required field, that only allows values stated on
 * priceRegex, and has a maximum value that can not exceed MAX_Price ($10k).
 * It additionally, it toggles the invalid styling of the price group.
 *
 * @param {boolean} [showError=true] - Tells whether validation messages should be displayed.
 * @returns {boolean} True if valid price, otherwise false.
 */
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

    if (!priceRegex.test(value)) {
        if (showError) {
            setFieldError(
                postPrice,
                postPriceError,
                'Ingresa un precio válido usando solo números y con hasta 2 dígitos después del punto decimal.'
            );
            postPriceGroup?.classList.add('is-invalid');
        }
        return false;
    }

    if (Number(value) > MAX_PRICE) {
        if (showError) {
            setFieldError(
                postPrice,
                postPriceError,
                'El precio no puede exceder $10,000.00.'
            );
            postPriceGroup?.classList.add('is-invalid');
        }
        return false;
    }

    return true;
}

/**
 * Validates a select or dropdown field by checking whether it has a value.
 *
 * If an option is not selected it adds the in-valid variable.
 * Removes said variable otherwise.
 *
 * @param {HTMLSelectElement|null} field - Select field to validate.
 * @param {boolean} [showError=true] - Whether styling should be updated.
 * @returns {boolean} True if the field has a value, otherwise false.
 */
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

/**
 * Validates the required category and condition dropdowns whenever
 * the user changes their selected value.
 *
 * The default placeholder options have an empty value, so returning
 * to those options marks the field as invalid and shows the related
 * Bootstrap invalid-feedback message.
 *
 * After each change, the publish button state is recalculated so the
 * user can only publish when both dropdowns contain valid selections.
 */
postCategory?.addEventListener('change', () => {
    validateSelect(postCategory, true);
    updatePublishButtonState();
});

postCondition?.addEventListener('change', () => {
    validateSelect(postCondition, true);
    updatePublishButtonState();
});

/**
 * Validates selected create-post images.
 *
 * The picture is a required field. Requires 1 image to be selected MIN_IMAGES.
 * There can not be more than 3 images selected, MAX_IMAGES.
 *
 * @param {boolean} [showError=true] - Whether image validation messages should be shown.
 * @returns {boolean} True if valid image/s, otherwise false.
 */
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

/**
 * Enables or disables the publish button based on
 * the overall create-post form state.
 *
 * The publish button is enabled only if all required validations pass:
 * - title
 * - price
 * - category
 * - condition
 * - images
 * - description (optional but if written then required)
 * - profanity words usage
 */
function updatePublishButtonState() {
    if (!publishBtn) return;

    const isReady =
        validateTitle(false) &&
        validatePrice(false) &&
        validateSelect(postCategory, false) &&
        validateSelect(postCondition, false) &&
        validateImages(false) &&
        validateDescription(false) &&
        validateCreatePostProfanity(false);

    publishBtn.disabled = !isReady;
}

/**
 * Renders preview cards for all selected create-post images.
 *
 * It creates one preview card per file, displays the file name, adds a
 * remove button for each image, and deletes any previous previews.
 * The function uses URL.createObjectURL for temporary local preview.
 *
 * The remove button deletes selected files from selectedPostImages,
 * updates the publish button state, clears image errors, re-renders the previews, and
 * updates the dirty-state tracking.
 *
 * @param {File[]} files - Array of selected image files.
 */
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
            if (selectedPostImages.length < MIN_IMAGES) {
                validateImages(true);
            } else {
                clearImageError();
            }

            if (postImage) {
                postImage.value = '';
            }

            updateCreatePostDirtyState();
            updatePublishButtonState();
        });
    });
}


/**
 * Verifies whether a file is a real JPEG by checking both MIME type and file signature.
 *
 * It requires files typing to include "jpeg", reads the first 3 bytes of the file, and
 * checks for the JPEG byte number FF D8 FF
 *
 * @param {File} file - File to inspect.
 * @returns {Promise<boolean>} True if the file appears to be a real JPEG, otherwise false.
 */
async function isRealJpeg(file) {
    if (!file || !file.type.includes('jpeg')) {
        return false;
    }

    const buffer = await file.slice(0, 3).arrayBuffer();
    const bytes = new Uint8Array(buffer);

    return bytes[0] === 0xff && bytes[1] === 0xd8 && bytes[2] === 0xff;
}


/**
 * Clears all validation UI from the create-post form.
 *
 * It clears the title, description, and price errors and error styling.
 * It also removes invalid styling from category and condition selects.
 * Clears image validation messages.
 *
 */
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

/**
 * Fully resets the create-post form and related local UI state.
 *
 * Clears and resets native form values, selected image files,
 * clears all validation UI, recalculates publish button state,
 * clears all dirty state flags, and empties the preview container.
 *
 */
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

/**
 * Resets the local state for the create-post form after successful submission.
 * It exists as a separate helper to make post-submission cleanup clearer.
 */
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

/**
 * Recomputes whether the create-post modal contains unsaved data.
 *
 * The form is considered dirty when any of the following are present:
 * - title, description, or price text
 * - category or condition selection
 * - one or more selected images
 *
 */
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

/**
 * Validates the report reason select field.
 * The reason is a required field.
 *
 * @param {boolean} [showError=true] - Whether invalid styling should be applied.
 * @returns {boolean} True if a reason is selected, otherwise false.
 */
function validateReportReason(showError = true) {
    if (!reportReason  || !reportReasonError) return true;

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

/**
 * Validates the report description field.
 * The description is a required field.
 *
 * The title is a required field, that only allows characters stated on
 * allowedTextRegex, and has a minimum length of 10 characters and a maximum length of 500 characters.
 *
 * @param {boolean} [showError=true] - Tells whether validation messages should be displayed.
 * @returns {boolean} True if valid, otherwise false.
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

    if (!allowedTextRegex.test(value)) {
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

/**
 * Enables or disables the report submit button based on report form validity.
 *
 * The report submit button is enabled only if:
 * A report reason has been selected abd the description format and value is valid.
 *
 */
function updateReportButtonState() {
    if (!submitReportBtn) return;

    const isReady =
        validateReportReason(false) &&
        validateReportDescription(false);

    submitReportBtn.disabled = !isReady;
}

/**
 * Clears all report form validation styling and
 * resets default error text state.
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
        reportReasonError.textContent = 'Selecciona una razón.';
    }
}


/**
 * Resets the report form and its related state.
 *
 * It clears the validation UI and resets the native form values.
 * Additionally, it recalculates the report button enabled state and
 * clears the dirty-state flags.
 */
function resetReportForm() {
    if (reportUserForm) {
        reportUserForm.reset();
    }

    isReportDirty = false;
    allowReportClose = false;

    resetReportValidation();
    updateReportButtonState();
}

/**
 * Recomputes whether the report modal contains unsaved data.
 *
 * The report form is considered dirty when:
 * A reason has been selected, or the description contains non-whitespace text.
 *
 */
function updateReportDirtyState() {
    const hasReason = !!(reportReason && reportReason.value);
    const hasDescription = !!(reportDescription && reportDescription.value.trim() !== '');

    isReportDirty = hasReason || hasDescription;
}

/**
 * Attempts to close the report modal safely.
 *
 * Updates report dirty-state first.
 * If the report is not dirty:
 *   - allows the report modal to close, hides it.
 *   - reopens the post details modal shortly afterward
 *
 * If the report is dirty:
 *   - prevents silent loss of data
 *   - opens the cancel confirmation modal instead
 *
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
            });
        }
        return;
    }

    if (cancelReportConfirmModal) {
        const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
        confirmModal.show();
    }
}

/**
 * Reopens the post details modal when the report modal is dismissed
 * by clicking outside the modal or by using Bootstrap's default close behavior.
 */
if (reportUserModal && postDetailsModal) {
    reportUserModal.addEventListener('hidden.bs.modal', () => {
        if (allowReportClose) return;

        const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
        postModalInstance.show();
    });
}

/**
 * Initializes the interactive seller star-rating widget.
 *
 * It reads all .rating-star elements inside ratingContainer,
 * maps rating values to text labels, and supports the following:
 *   - hover preview
 *   - click to select rating
 *   - keyboard selection with Enter or Space
 *   - mouseleave restore to the currently selected rating
 *   - clear or quitar button that resets the value to 0
 *
 * There is a helper called paintStars(value) that
 * updates star fill states and label text.
 *
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


/**
 * Seller rating submission handler.
 *
 * It reads the selected rating from #sellerRatingValue, requires both a rating value and currentPostId,
 * sends a POST /marketplace/{currentPostId}/review, refreshes marketplace posts after successful submission,
 * updates seller rating summary in the modal using backend response values, shows success toast, and
 *
 * Backend response fields used: seller_rating_average and seller_rating_count.
 */
if (submitSellerRatingBtn) {
    submitSellerRatingBtn.addEventListener('click', async () => {
        const ratingValue = Number(document.getElementById('sellerRatingValue')?.value || 0);

        if (!ratingValue || !currentPostId) {
            alert('Selecciona una calificación válida.');
            return;
        }

        try {
            const response = await fetch(`/marketplace/${currentPostId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    rating: ratingValue
                })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al guardar la calificación.');
            }

            await fetchPosts();

            // Update seller rating in modal
            if (postDetailsSellerRating) {
                postDetailsSellerRating.innerHTML = `
                    <i class="bi bi-star-fill text-warning me-1"></i>
                    ${data.seller_rating_average}
                    <span class="text-muted">(${data.seller_rating_count} reseñas)</span>
                `;
            }

            // Show toast
            ratingSentToast?.show();

        } catch (error) {
            console.error(error);
            alert(error.message);
        }
    });
}

/**
 * Search button handler.
 *
 * Resets marketplace pagination back to page 1
 * and re-renders using the current filter/search state.
 */
if (searchMarketplaceBtn) {
    searchMarketplaceBtn.addEventListener('click', () => {
        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

/**
 * Clear-filters handler.
 *
 * Resets the search input, category filter,rating filter,
 * price filter, condition filter, and current page.
 * Then re-renders the marketplace.
 */
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

/**
 * Enables the search button only when the search input contains non-whitespace text.
 */
function updateSearchButtonState() {
    if (!marketplaceSearch || !searchMarketplaceBtn) return;

    const value = marketplaceSearch.value;
    searchMarketplaceBtn.disabled = value.trim() === '';
}


/**
 * Search input handlers.
 *
 * Updates whether the search button should be enabled.
 * When the Enter key is pressed and search button is enabled,
 * prevents default form submission behavior, resets to page 1,
 * and renders filtered results.
 */
if (marketplaceSearch) {
    marketplaceSearch.addEventListener('input', () => {
        updateSearchButtonState();
    });

    marketplaceSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !searchMarketplaceBtn.disabled) {
            e.preventDefault();
            currentMarketplacePage = 1;
            renderMarketplace();
        }
    });
}

/**
 * Initializes search button enabled state on first load.
 */
updateSearchButtonState();


/**
 * Filter change listeners.
 *
 * Any filter change:
 * Resets pagination to page 1 and re-renders the marketplace.
 */
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

if (marketplaceConditionFilter) {
    marketplaceConditionFilter.addEventListener('change', () => {
        currentMarketplacePage = 1;
        renderMarketplace();
    });
}

/**
 * Title input handlers.
 *
 * Enforces the hard max length of 100 characters and shows "maximum reached" messages at
 * 100 and when exceeding 100.
 *
 * Otherwise, it runs normal title validation where it: updates publish button state and
 * dirty-state.
 *
 *  The blur clears the special max-length message when exactly 100 characters.
 *  Otherwise, runs normal validation where it: updates publish button state and
 *  dirty-state.
 */
if (postTitle) {
    postTitle.addEventListener('input', () => {
        const value = postTitle.value;

        if (value.length > 100) {
            postTitle.value = value.slice(0, 100);
            setFieldError(
                postTitle,
                postTitleError,
                'Has alcanzado el máximo de 100 caracteres. No puedes escribir más.'
            );
        } else if (value.length === 100) {
            setFieldError(
                postTitle,
                postTitleError,
                'Has alcanzado el máximo de 100 caracteres. Puedes someter el texto tal como está.'
            );
        } else {
            validateTitle(true);
            validateCreatePostProfanity(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });

    postTitle.addEventListener('blur', () => {
        const value = postTitle.value.trim();

        if (value.length === 100) {
            clearFieldError(postTitle, postTitleError);
        } else {
            validateTitle(true);
            validateCreatePostProfanity(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

/**
 * Description input handlers.
 *
 *  Enforces hard max length of 500 characters and shows "maximum reached" messaging.
 *  Otherwise, runs normal description validation where it: updates publish button state and
 *   dirty-state.
 *
 *
 *  The blur clears the special max-length message when exactly 500 characters.
 *  Otherwise, runs normal validation where it: updates publish button state
 *  and updates dirty-state.
 */
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
                'Has alcanzado el máximo de 500 caracteres. Puedes someter el texto tal como está.'
            );
        } else {
            validateDescription(true);
            validateCreatePostProfanity(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });

    postDescription.addEventListener('blur', () => {
        const value = postDescription.value.trim();

        if (value.length === 500) {
            clearFieldError(postDescription, postDescriptionError);
        } else {
            validateDescription(true);
            validateCreatePostProfanity(true);
        }

        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

/**
 * Report description input handlers.
 *
 * Enforces hard max length of 500 characters and shows invalid styling and maximum-reached messages.
 * Otherwise, runs normal report description validation where it: updates report submit button state and
 * updates report dirty-state.
 *
 * The blur clears the special max-length message when exactly 500 characters.
 * Otherwise, runs normal validation where it: updates report submit button state and
 * updates report dirty-state.
 */
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
                'Has alcanzado el máximo de 500 caracteres. Puedes someter el texto tal como está.';
        } else {
            validateReportDescription(true);
        }

        updateReportButtonState();
        updateReportDirtyState();
    });

    reportDescription.addEventListener('blur', () => {
        const value = reportDescription.value.trim();

        if (value.length === 500) {
            reportDescription.classList.remove('is-invalid');
            reportDescriptionError.textContent = '';
        } else {
            validateReportDescription(true);
        }

        updateReportButtonState();
        updateReportDirtyState();
    });
}


/**
 * Price input handlers.
 *
 * RUns the full price validation, updates the publish button state, and
 * updates the dirty-state.
 */
if (postPrice) {
    postPrice.addEventListener('input', () => {
        validatePrice(true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
    });

    postPrice.addEventListener('blur', () => {

        validatePrice(true);
        updatePublishButtonState();
        updateCreatePostDirtyState();
    });
}

/**
 * Category and condition select handlers.
 *
 * On a change they,validate their fields, update publish button state and
 * update dirty-state.
 */
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

/**
 * Post image input handler.
 *
 * Reads selected files from the file input, clears previous image error,
 * verifies how many image slots remain, rejects selection if user exceeds max image count,
 * validates every file size <= 2MB and if it's a real JPEG/JPG according to isRealJpeg.
 *
 *  Additionally, it appends valid files to selectedPostImages, re-renders previews,
 *  clears the native file input value so user can reselect files later, updates
 *  the pusbkish button state, and the dirty state.
 */
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
                showImageError(`"${file.name}" no es un JPEG/JPG válido.`);
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

/**
 * Additional dirty-state tracking for create-post fields.
 * Input and change listeners are attached to all create-post fields
 * so unsaved changes can be detected even when validation is not triggered
 * by a specific interaction path.
 */
[postTitle, postDescription, postPrice, postCategory, postCondition].forEach((field) => {
    if (!field) return;

    field.addEventListener('input', updateCreatePostDirtyState);
    field.addEventListener('change', updateCreatePostDirtyState);
});

/**
 * Publish post handler.
 *
 * Validates all create-post fields, stops and keeps button state updated if any validation fails,
 * appends title, description, cost, category, and condition,and appends all selected images under images[].
 *
 * Additionally, it sends POST /posts with CSRF token, resets current page to 1,
 * refreshes posts from backend, allows modal to close without dirty warning, hides the create-post modal,
 * resets local form state, and shows created toast after publication.
 */
if (publishBtn && createPostForm) {
    publishBtn.addEventListener('click', async () => {
        if (isPublishingPost) return;

        isPublishingPost = true;
        publishBtn.disabled = true;

        try {
            const isTitleValid = validateTitle(true);
            const isDescriptionValid = validateDescription(true);
            const isPriceValid = validatePrice(true);
            const isCategoryValid = validateSelect(postCategory, true);
            const isConditionValid = validateSelect(postCondition, true);
            const areImagesValid = validateImages(true);
            const isProfanityValid = validateCreatePostProfanity(true);

            if (
                !isTitleValid ||
                !isDescriptionValid ||
                !isPriceValid ||
                !isCategoryValid ||
                !isConditionValid ||
                !areImagesValid ||
                !isProfanityValid
            ) {
                return;
            }

            const formData = new FormData();

            formData.append('title', postTitle.value);
            formData.append('description', postDescription.value);
            formData.append('cost', postPrice.value);
            formData.append('category', postCategory.value);
            formData.append('condition', postCondition.value);

            selectedPostImages.forEach(file => {
                formData.append('images[]', file);
            });

            const response = await fetch('/posts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            if (!response.ok) {
                return;
            }

            currentMarketplacePage = 1;

            await fetchPosts();
            await loadPostLimit();

            const createModalInstance = bootstrap.Modal.getOrCreateInstance(createPostModal);
            allowCreatePostClose = true;
            createModalInstance.hide();

            resetCreatePostLocalState();

            postCreatedToast?.show();

            if (userHasReachedPostLimit && maxPostLimitModal) {
                setTimeout(() => {
                    bootstrap.Modal.getOrCreateInstance(maxPostLimitModal).show();
                }, 400);
            }

        } finally {
            isPublishingPost = false;
            updatePublishButtonState();
        }
    });
}

/**
 * Create-post modal lifecycle behavior.
 *
 * Clears prior validation messages and recalculates publish button state.
 *
 * Allows immediate close only when allowCreatePostClose is true.
 * Otherwise, checks dirty-state:
 *  if form is clean, resets it normally
 *  if form is dirty, prevents close and opens cancel confirmation modal
 *

 *  After a permitted close, fully resets the form
 *  Clears the allowCreatePostClose bypass flag
 */
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

/**
 * Cancel create-post button handler.
 * Recomputes dirty-state:
 *   If form is clean, allows modal close immediately
 *   If form is dirty, opens the cancel confirmation modal
 */
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

/**
 * Confirm cancel create-post handler.
 *
 * Allows the create-post modal to close and closes the cancel confirmation modal.
 */
if (confirmCancelCreatePost && createPostModal && cancelConfirmModal) {
    confirmCancelCreatePost.addEventListener('click', () => {
        allowCreatePostClose = true;

        const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
        confirmModal.hide();

        const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);
        modal.hide();
    });
}

/**
 * Report reason change handler.
 *
 * Validates the reason select and updates report button state.
 */
if (reportReason) {
    reportReason.addEventListener('change', () => {
        validateReportReason(true);
        updateReportButtonState();
    });
}

/**
 * Dirty-state tracking for report form fields.
 * Attached to both the reason and description fields.
 */
[reportReason, reportDescription].forEach((field) => {
    if (!field) return;

    field.addEventListener('input', updateReportDirtyState);
    field.addEventListener('change', updateReportDirtyState);
});

/**
 * Report submission handler.
 *
 * Prevents the default form submission.
 * Validates reason and description and exits and disables/enables button correctly if invalid.
 * Reads post ID from postDetailsModal dataset, and sends POST /reports with: reported_user_id,
 * report_reason, description, and post_id.
 *
 * Additionally,attempts to parse JSON response:
 *  Logs non-JSON responses for debugging
 *  Logs backend errors if response is not ok
 *
 * On a success it allows report modal to close without warning, hides report modal,
 * shows report toast and reopens post details modal shortly afterward.
 *
 */
if (submitReportBtn) {
    submitReportBtn.addEventListener('click', async (e) => {
        e.preventDefault();

        console.log('CLICK EN ENVIAR QUERELLA');

        const isReasonValid = validateReportReason(true);
        const isDescriptionValid = validateReportDescription(true);

        if (!isReasonValid || !isDescriptionValid) {
            console.log('Formulario inválido');
            updateReportButtonState();
            return;
        }

        const postId = document.getElementById('postDetailsModal')?.dataset.postId;

        const payload = {
            reported_user_id: reportedUserId,
            report_reason: reportReason.value,
            description: reportDescription.value,
            post_id: postId
        };

        console.log('Payload querella:', payload);

        try {
            const response = await fetch('/reports', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });

            const rawText = await response.text();

            console.log('Status:', response.status);
            console.log('Respuesta cruda:', rawText);

            let data = null;

            try {
                data = rawText ? JSON.parse(rawText) : null;
            } catch (jsonError) {
                console.error('La respuesta no es JSON válido:', rawText);
                alert('Error: el servidor no devolvió JSON. Revisa la consola.');
                return;
            }

            if (!response.ok) {
                console.error('Error backend:', data);
                alert(data?.message || 'No se pudo enviar la querella. Revisa la consola.');
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

            resetReportForm();

        } catch (error) {
            console.error('Error enviando reporte:', error);
            alert('Error enviando la querella. Revisa la consola.');
        }
    });
}

/**
 * Report modal lifecycle behavior.
 *
 * It clears prior validation UI and recalculates report submit button state.
 *
 *  Allows close only when allowReportClose is true.
 *  Otherwise, checks dirty-state:
 *     If clean, resets normally
 *      If dirty, prevents close and opens cancel confirmation modal
 *
 * After an allowed is close, resets report form and clears the allowReportClose bypass flag.
 */
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

/**
 * Report cancel button handler.
 *
 * Uses the shared safe-close helper that either closes immediately
 * or shows a confirmation modal if there is unsaved data.
 */
if (cancelReportBtn) {
    cancelReportBtn.addEventListener('click', tryCloseReportModal);
}

/**
 * Report modal close (X) button handler.
 *
 * Prevents the default close behavior so the custom safe-close logic
 * can decide whether the modal should really close.
 */
if (closeReportModalBtn) {
    closeReportModalBtn.addEventListener('click', (e) => {
        e.preventDefault();
        tryCloseReportModal();
    });
}

/**
 * Confirm cancel report handler.
 *
 * Allows report modal to close.
 * Hides the confirmation modal and the report modal.
 * Reopens the post details modal afterwards.
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
 * Initial page setup.
 *
 * It initializes the seller rating UI, computes initial create-post button
 * enabled state,computes initial report button enabled state, and
 * fetches and renders marketplace posts from the backend.
 *
 * This ensures both forms and the marketplace list are ready
 * as soon as the script loads.
 */
initializeSellerRating();
updatePublishButtonState();
updateReportButtonState();
fetchPosts();
