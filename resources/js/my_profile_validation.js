document.addEventListener('DOMContentLoaded', function () {
    const ITEMS_PER_PAGE = 18;

    const profileTabButtons = document.querySelectorAll('#profileTabs button');

    profileTabButtons.forEach((button) => {
        button.addEventListener('shown.bs.tab', function (event) {
            profileTabButtons.forEach((btn) => {
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');
            });

            event.target.classList.remove('btn-outline-success');
            event.target.classList.add('btn-success');
        });
    });

    const deletePostModalEl = document.getElementById('deletePostModal');
    const deletePostModalText = document.getElementById('deletePostModalText');
    const confirmDeletePostBtn = document.getElementById('confirmDeletePost');
    const deletePostToastEl = document.getElementById('deletePostToast');
    const postsTabCount = document.getElementById('postsTabCount');

    const profileDetailsModalEl = document.getElementById('profilePostDetailsModal');
    const profileDetailsModal = profileDetailsModalEl && window.bootstrap
        ? window.bootstrap.Modal.getOrCreateInstance(profileDetailsModalEl)
        : null;

    const carouselInner = document.getElementById('profilePostImagesCarouselInner');
    const carouselIndicators = document.getElementById('profilePostImagesCarouselIndicators');
    const carouselPrev = document.getElementById('profilePostImagesCarouselPrev');
    const carouselNext = document.getElementById('profilePostImagesCarouselNext');

    const detailsModalLabel = document.getElementById('profilePostDetailsModalLabel');
    const detailsDescription = document.getElementById('profilePostDetailsDescription');
    const detailsPrice = document.getElementById('profilePostDetailsPrice');
    const detailsStatus = document.getElementById('profilePostDetailsStatus');
    const detailsCondition = document.getElementById('profilePostDetailsCondition');
    const detailsSeller = document.getElementById('profilePostDetailsSeller');
    const detailsSellerRating = document.getElementById('profilePostDetailsSellerRating');
    const detailsCategory = document.getElementById('profilePostDetailsCategory');

    let postCardToDelete = null;
    let currentPostsPage = 1;

    function showToast(toastElement) {
        if (!toastElement || !window.bootstrap) return;
        const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    }

    function updatePostsTabCount() {
        if (!postsTabCount) return;
        const totalPosts = document.querySelectorAll('.post-card-wrapper').length;
        postsTabCount.textContent = totalPosts;
    }

    function buildPagination(container, totalPages, currentPage, onPageClick) {
        if (!container) return;

        container.innerHTML = '';

        if (totalPages <= 1) return;

        const createItem = (label, page, disabled = false, active = false) => {
            const li = document.createElement('li');
            li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;

            if (!disabled && !active) {
                button.addEventListener('click', function () {
                    onPageClick(page);
                });
            }

            li.appendChild(button);
            container.appendChild(li);
        };

        createItem('«', currentPage - 1, currentPage === 1);

        for (let page = 1; page <= totalPages; page++) {
            createItem(String(page), page, false, page === currentPage);
        }

        createItem('»', currentPage + 1, currentPage === totalPages);
    }

    function paginateItems(items, currentPageValue, paginationContainer, onPageChange) {
        const totalPages = Math.max(1, Math.ceil(items.length / ITEMS_PER_PAGE));
        const safePage = Math.min(currentPageValue, totalPages);
        const start = (safePage - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;

        items.forEach((item, index) => {
            item.classList.toggle('d-none', !(index >= start && index < end));
        });

        buildPagination(paginationContainer, totalPages, safePage, onPageChange);

        return safePage;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.querySelectorAll('.open-profile-post-details').forEach((button) => {
        button.addEventListener('click', async function () {
            const postId = this.dataset.postId;
            if (!postId) return;

            try {
                const response = await fetch(`/posts/${postId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar la publicación.');
                }

                const post = await response.json();

                const title = post.title || 'Detalle de la publicación';
                const description = post.description || 'Sin descripción.';
                const price = Number(post.cost || 0).toFixed(2);
                const status = post.status || 'Disponible';
                const condition = post.condition || 'Sin especificar';
                const category = post.category || 'Sin categoría';
                const seller = post.user
                    ? `${post.user.first_name ?? ''} ${post.user.last_name ?? ''}`.trim()
                    : 'Usuario';

                const rating = post.user?.average_rating ?? 0;
                const reviews = post.user?.reviews_count ?? 0;

                const images = [
                    post.photo_1_url ? `/storage/${post.photo_1_url}` : null,
                    post.photo_2_url ? `/storage/${post.photo_2_url}` : null,
                    post.photo_3_url ? `/storage/${post.photo_3_url}` : null
                ].filter(Boolean);

                if (detailsModalLabel) detailsModalLabel.textContent = title;
                if (detailsDescription) detailsDescription.textContent = description;
                if (detailsPrice) detailsPrice.textContent = `$${price}`;
                if (detailsStatus) detailsStatus.textContent = status;
                if (detailsCondition) detailsCondition.textContent = condition;
                if (detailsCategory) detailsCategory.textContent = category;
                if (detailsSeller) detailsSeller.textContent = seller;

                if (detailsSellerRating) {
                    detailsSellerRating.innerHTML = `
                    <i class="bi bi-star-fill text-warning me-1"></i>
                    ${escapeHtml(rating)} <span class="text-muted">(${escapeHtml(reviews)} reseñas)</span>
                `;
                }

                if (carouselInner) carouselInner.innerHTML = '';
                if (carouselIndicators) carouselIndicators.innerHTML = '';

                images.forEach((img, index) => {
                    if (carouselIndicators) {
                        carouselIndicators.insertAdjacentHTML(
                            'beforeend',
                            `
                        <button
                            type="button"
                            data-bs-target="#profilePostImagesCarousel"
                            data-bs-slide-to="${index}"
                            class="${index === 0 ? 'active' : ''}"
                            ${index === 0 ? 'aria-current="true"' : ''}
                            aria-label="Slide ${index + 1}"
                        ></button>
                        `
                        );
                    }

                    if (carouselInner) {
                        carouselInner.insertAdjacentHTML(
                            'beforeend',
                            `
        <div class="carousel-item ${index === 0 ? 'active' : ''} post-carousel-item">
            <div class="carousel-image-box">
                <img
                    src="${img}"
                    alt="Imagen ${index + 1}"
                    class="post-carousel-img"
                >
            </div>
        </div>
        `
                        );
                    }
                });

                const showControls = images.length > 1;

                if (carouselPrev) carouselPrev.classList.toggle('d-none', !showControls);
                if (carouselNext) carouselNext.classList.toggle('d-none', !showControls);
                if (carouselIndicators) carouselIndicators.classList.toggle('d-none', !showControls);

                if (profileDetailsModal) {
                    profileDetailsModal.show();
                }
            } catch (error) {
                console.error(error);
            }
        });
    });
    document.querySelectorAll('.open-delete-post-modal').forEach((button) => {
        button.addEventListener('click', function () {
            const postTitle = this.dataset.postTitle || 'esta publicación';
            postCardToDelete = this.closest('.post-card-wrapper');

            if (deletePostModalText) {
                deletePostModalText.textContent = `"${postTitle}" será eliminada de la vista.`;
            }

            if (deletePostModalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(deletePostModalEl);
                modal.show();
            }
        });
    });

    if (confirmDeletePostBtn) {
        confirmDeletePostBtn.addEventListener('click', function () {
            if (!postCardToDelete) return;

            if (deletePostModalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(deletePostModalEl);
                modal.hide();
            }

            showToast(deletePostToastEl);

            setTimeout(() => {
                postCardToDelete.remove();
                postCardToDelete = null;
                updatePostsTabCount();
                filterPosts(true);
            }, 500);
        });
    }

    const postsFilterForm = document.getElementById('postsFilterForm');
    const postSearch = document.getElementById('postSearch');
    const postsSearchBtn = document.getElementById('postsSearchBtn');

    const requestSearch = document.getElementById('requestSearch');
    const requestsSearchBtn = document.getElementById('requestsSearchBtn');

    const sportFilter = document.getElementById('sportFilter');
    const priceFilter = document.getElementById('priceFilter');
    const clearPostsFilters = document.getElementById('clearPostsFilters');
    const postsEmptyState = document.getElementById('postsEmptyState');
    const postsPagination = document.getElementById('postsPagination');

    function matchesPriceRange(price, selectedRange) {
        if (!selectedRange) return true;

        const numericPrice = Number(price);

        if (selectedRange === '0-25') return numericPrice >= 0 && numericPrice <= 25;
        if (selectedRange === '26-50') return numericPrice >= 26 && numericPrice <= 50;
        if (selectedRange === '51-100') return numericPrice >= 51 && numericPrice <= 100;
        if (selectedRange === '101+') return numericPrice >= 101;

        return true;
    }

    function updatePostsSearchButtonState() {
        if (!postSearch || !postsSearchBtn) return;
        const hasText = postSearch.value.trim().length > 0;
        postsSearchBtn.disabled = !hasText;
    }

    function updateRequestsSearchButtonState() {
        if (!requestSearch || !requestsSearchBtn) return;
        const hasText = requestSearch.value.trim().length > 0;
        requestsSearchBtn.disabled = !hasText;
    }

    function filterPosts(resetPage = false) {
        const searchValue = postSearch ? postSearch.value.trim().toLowerCase() : '';
        const sportValue = sportFilter ? sportFilter.value.toLowerCase() : '';
        const priceValue = priceFilter ? priceFilter.value : '';

        const allCards = Array.from(document.querySelectorAll('.post-card-wrapper'));
        const matchingCards = [];

        allCards.forEach((card) => {
            const title = (card.dataset.title || '').toLowerCase();
            const description = (card.dataset.description || '').toLowerCase();
            const sport = (card.dataset.sport || '').toLowerCase();
            const price = card.dataset.price || '0';

            const matchesSearch =
                !searchValue ||
                title.includes(searchValue) ||
                description.includes(searchValue);

            const matchesSport = !sportValue || sport === sportValue;
            const matchesPrice = matchesPriceRange(price, priceValue);

            if (matchesSearch && matchesSport && matchesPrice) {
                matchingCards.push(card);
            } else {
                card.classList.add('d-none');
            }
        });

        if (resetPage) {
            currentPostsPage = 1;
        }

        if (postsEmptyState) {
            postsEmptyState.classList.toggle('d-none', matchingCards.length !== 0);
        }

        if (matchingCards.length === 0) {
            if (postsPagination) postsPagination.innerHTML = '';
            return;
        }

        currentPostsPage = paginateItems(
            matchingCards,
            currentPostsPage,
            postsPagination,
            function (page) {
                currentPostsPage = page;
                filterPosts(false);
            }
        );
    }

    if (postsFilterForm) {
        postsFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            currentPostsPage = 1;
            filterPosts();
        });
    }

    if (sportFilter) {
        sportFilter.addEventListener('change', function () {
            currentPostsPage = 1;
            filterPosts();
        });
    }

    if (priceFilter) {
        priceFilter.addEventListener('change', function () {
            currentPostsPage = 1;
            filterPosts();
        });
    }

    if (postSearch) {
        postSearch.addEventListener('input', function () {
            currentPostsPage = 1;
            filterPosts();
            updatePostsSearchButtonState();
        });
    }

    if (requestSearch) {
        requestSearch.addEventListener('input', updateRequestsSearchButtonState);
    }

    if (clearPostsFilters) {
        clearPostsFilters.addEventListener('click', function () {
            if (postSearch) postSearch.value = '';
            if (sportFilter) sportFilter.value = '';
            if (priceFilter) priceFilter.value = '';
            currentPostsPage = 1;
            filterPosts();
            updatePostsSearchButtonState();
        });
    }

    updatePostsTabCount();
    filterPosts(true);
    updatePostsSearchButtonState();
    updateRequestsSearchButtonState();
});
