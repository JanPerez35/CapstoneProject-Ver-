document.addEventListener('DOMContentLoaded', function () {

    /*
     * Defines the number of post cards displayed per page
     * in the client-side publications section.
     */
    const ITEMS_PER_PAGE = 18;

    /*
     * Storage keys used to persist UI state between reloads.
     * - PROFILE_SCROLL_KEY stores the user's vertical scroll position
     * - PROFILE_ACTIVE_TAB_KEY stores the active profile tab
     */
    const PROFILE_SCROLL_KEY = 'myProfileScrollY';
    const PROFILE_ACTIVE_TAB_KEY = 'myProfileActiveTab';

    /*
     * Profile tab buttons and shared tab UI references.
     */
    const postsTab = document.getElementById('posts-tab');
    const requestsTab = document.getElementById('requests-tab');
    const profileTabButtons = document.querySelectorAll('#profileTabs button');

    /*
     * Modal and UI elements related to post deletion.
     */
    const deletePostModalEl = document.getElementById('deletePostModal');
    const deletePostModalText = document.getElementById('deletePostModalText');
    const confirmDeletePostBtn = document.getElementById('confirmDeletePost');
    const deletePostToastEl = document.getElementById('deletePostToast');
    const postsTabCount = document.getElementById('postsTabCount');

    /*
     * Modal and UI elements for viewing post details.
     */
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

    /*
     * References the filter form and related controls used to search,
     * filter, reset, and paginate the posts section.
     */
    const postsFilterForm = document.getElementById('postsFilterForm');
    const postSearch = document.getElementById('postSearch');
    const postsSearchBtn = document.getElementById('postsSearchBtn');
    const sportFilter = document.getElementById('sportFilter');
    const priceFilter = document.getElementById('priceFilter');
    const clearPostsFilters = document.getElementById('clearPostsFilters');
    const postsEmptyState = document.getElementById('postsEmptyState');
    const postsPagination = document.getElementById('postsPagination');

    /*
     * References for the requests section.
     * This section is server-rendered, so searches and filters use GET.
     */
    const requestsFilterForm = document.getElementById('requestsFilterForm');
    const requestSearch = document.getElementById('requestSearch');
    const requestsSearchBtn = document.getElementById('requestsSearchBtn');
    const statusFilter = document.getElementById('statusFilter');
    const clearRequestsFilters = document.getElementById('clearRequestsFilters');

    /*
     * Tracks the current post selected for deletion and
     * the active page in the client-side posts pagination.
     */
    let postCardToDelete = null;
    let currentPostsPage = 1;

    /**
     * Stores the current vertical scroll position in sessionStorage.
     * This allows the page to return the user to the same place
     * after a reload caused by searches or filters.
     */
    function saveScrollPosition() {
        sessionStorage.setItem(PROFILE_SCROLL_KEY, String(window.scrollY));
    }

    /**
     * Removes any saved scroll position from sessionStorage.
     */
    function clearScrollPosition() {
        sessionStorage.removeItem(PROFILE_SCROLL_KEY);
    }

    /**
     * Restores the user's previous vertical scroll position.
     * Retries multiple times because layout and Bootstrap content
     * may finish rendering slightly after the initial page load.
     */
    function restoreScrollPosition() {
        const savedScrollY = sessionStorage.getItem(PROFILE_SCROLL_KEY);
        if (savedScrollY === null) return;

        const targetY = parseInt(savedScrollY, 10);

        if (Number.isNaN(targetY)) {
            clearScrollPosition();
            return;
        }

        let attempts = 0;
        const maxAttempts = 20;

        const tryRestore = () => {
            window.scrollTo(0, targetY);
            attempts++;

            if (attempts < maxAttempts && Math.abs(window.scrollY - targetY) > 5) {
                setTimeout(tryRestore, 100);
            } else {
                clearScrollPosition();
            }
        };

        setTimeout(tryRestore, 100);
    }

    /**
     * Determines whether a clicked link belongs to pagination.
     *
     * @param {HTMLElement} link - The clicked anchor element.
     * @returns {boolean} True when the link is inside a pagination component.
     */
    function isPaginationLink(link) {
        return !!link.closest('.pagination');
    }

    /**
     * Saves the currently active tab so it can be restored after reload.
     *
     * @param {string} tabName - Either "posts" or "requests".
     */
    function saveActiveTab(tabName) {
        sessionStorage.setItem(PROFILE_ACTIVE_TAB_KEY, tabName);
    }

    /**
     * Returns the last saved active tab.
     *
     * @returns {string|null} The saved tab name, or null if none exists.
     */
    function getSavedActiveTab() {
        return sessionStorage.getItem(PROFILE_ACTIVE_TAB_KEY);
    }

    /**
     * Displays a Bootstrap toast notification safely.
     *
     * @param {HTMLElement|null} toastElement - The toast element to display.
     */
    function showToast(toastElement) {
        if (!toastElement || !window.bootstrap) return;
        const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    }

    /**
     * Escapes dynamic values before injecting them as HTML.
     *
     * @param {any} value - Dynamic value to be escaped.
     * @returns {string} Escaped HTML-safe string.
     */
    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Synchronizes the visual style of the tab buttons so that
     * only the active tab appears filled.
     *
     * @param {HTMLElement} activeButton - The button that should appear active.
     */
    function syncTabButtonStyles(activeButton) {
        profileTabButtons.forEach((btn) => {
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        });

        activeButton.classList.remove('btn-outline-success');
        activeButton.classList.add('btn-success');
    }

    /**
     * Updates the current URL so the active tab survives page reloads.
     * Also removes the "page" parameter when switching tabs to prevent
     * carrying an invalid pagination page into a different section.
     *
     * @param {string} tabName - Either "posts" or "requests".
     */
    function updateTabInUrl(tabName) {
        const url = new URL(window.location.href);

        if (tabName === 'requests') {
            url.searchParams.set('tab', 'requests');
        } else {
            url.searchParams.delete('tab');
        }

        url.searchParams.delete('page');
        window.history.replaceState({}, '', url.toString());
    }

    /**
     * Activates a Bootstrap tab and updates its visual state.
     *
     * @param {HTMLElement|null} tabButton - The tab button to activate.
     */
    function activateTab(tabButton) {
        if (!tabButton || !window.bootstrap) return;

        const tabInstance = new bootstrap.Tab(tabButton);
        tabInstance.show();
        syncTabButtonStyles(tabButton);
    }

    /**
     * Initializes the correct active tab.
     * Priority:
     * 1. URL query parameter
     * 2. Saved sessionStorage tab
     * 3. Posts tab by default
     */
    function initializeActiveTab() {
        const url = new URL(window.location.href);
        const tabFromUrl = url.searchParams.get('tab');
        const savedTab = getSavedActiveTab();

        if (tabFromUrl === 'requests') {
            activateTab(requestsTab);
            saveActiveTab('requests');
            return;
        }

        if (savedTab === 'requests') {
            activateTab(requestsTab);
            updateTabInUrl('requests');
            return;
        }

        activateTab(postsTab);
        saveActiveTab('posts');
    }

    /**
     * Updates the total number of posts displayed in the tab header.
     */
    function updatePostsTabCount() {
        if (!postsTabCount) return;
        const totalPosts = document.querySelectorAll('.post-card-wrapper').length;
        postsTabCount.textContent = totalPosts;
    }

    /**
     * Builds client-side pagination buttons for the posts section.
     *
     * @param {HTMLElement|null} container - Pagination container.
     * @param {number} totalPages - Total number of pages.
     * @param {number} currentPage - Current active page.
     * @param {Function} onPageClick - Callback for page button clicks.
     */
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

    /**
     * Applies client-side pagination to the publications cards.
     *
     * @param {HTMLElement[]} items - Matching visible items.
     * @param {number} currentPageValue - Desired current page.
     * @param {HTMLElement|null} paginationContainer - Pagination wrapper.
     * @param {Function} onPageChange - Callback executed when page changes.
     * @returns {number} Safe current page after bounds validation.
     */
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

    /**
     * Evaluates whether a post price belongs to the selected price range.
     *
     * @param {string|number} price - Post price.
     * @param {string} selectedRange - Selected filter value.
     * @returns {boolean} True when the price matches the selected range.
     */
    function matchesPriceRange(price, selectedRange) {
        if (!selectedRange) return true;

        const numericPrice = Number(price);

        if (selectedRange === '0-25') return numericPrice >= 0 && numericPrice <= 25;
        if (selectedRange === '26-50') return numericPrice >= 26 && numericPrice <= 50;
        if (selectedRange === '51-100') return numericPrice >= 51 && numericPrice <= 100;
        if (selectedRange === '101+') return numericPrice >= 101;

        return true;
    }

    /**
     * Enables or disables the publications search button depending on
     * whether the user has entered non-whitespace text.
     */
    function updatePostsSearchButtonState() {
        if (!postSearch || !postsSearchBtn) return;
        const hasText = postSearch.value.trim().length > 0;
        postsSearchBtn.disabled = !hasText;
    }

    /**
     * Enables or disables the requests search button depending on
     * whether the user has entered non-whitespace text.
     */
    function updateRequestsSearchButtonState() {
        if (!requestSearch || !requestsSearchBtn) return;
        const hasText = requestSearch.value.trim().length > 0;
        requestsSearchBtn.disabled = !hasText;
    }

    /**
     * Applies search and filter criteria to the client-side
     * publications grid and rebuilds pagination.
     *
     * @param {boolean} resetPage - Whether to return to page 1 first.
     */
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

    /**
     * Submits the requests GET form after saving the current tab
     * and current scroll position.
     */
    function submitRequestsFilters() {
        if (!requestsFilterForm) return;

        saveActiveTab('requests');
        saveScrollPosition();
        requestsFilterForm.submit();
    }

    /*
     * Restores scroll position after full reload.
     */
    window.addEventListener('load', restoreScrollPosition);

    /*
     * Saves scroll position on all form submissions.
     * This mainly affects the requests section, which uses GET.
     */
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            saveScrollPosition();

            if (form === requestsFilterForm) {
                saveActiveTab('requests');
            }
        });
    });

    /*
     * Saves or clears scroll position depending on the clicked link.
     * Pagination keeps default behavior and does not restore scroll.
     */
    document.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (isPaginationLink(link)) {
                clearScrollPosition();
                return;
            }

            saveScrollPosition();
        });
    });

    /*
     * Initializes the active tab when the page first loads.
     */
    initializeActiveTab();

    /*
     * Keeps tab styles synced when Bootstrap finishes activating a tab.
     */
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

    /*
     * Updates URL and saved tab when the publications tab is clicked.
     */
    if (postsTab) {
        postsTab.addEventListener('click', function () {
            syncTabButtonStyles(postsTab);
            saveActiveTab('posts');
            updateTabInUrl('posts');
        });
    }

    /*
     * Updates URL and saved tab when the requests tab is clicked.
     */
    if (requestsTab) {
        requestsTab.addEventListener('click', function () {
            syncTabButtonStyles(requestsTab);
            saveActiveTab('requests');
            updateTabInUrl('requests');
        });
    }

    /*
     * Loads and displays publication details in the modal.
     */
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

    /*
     * Opens the delete confirmation modal for the selected publication.
     */
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

    /*
     * Confirms the visual deletion of a publication card and
     * refreshes the publications grid.
     */
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

    /*
     * Prevents full reload in the publications filter form because
     * that section is fully filtered on the client side.
     */
    if (postsFilterForm) {
        postsFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            currentPostsPage = 1;
            filterPosts();
        });
    }

    /*
     * Reapplies client-side publications filtering when the sport changes.
     */
    if (sportFilter) {
        sportFilter.addEventListener('change', function () {
            currentPostsPage = 1;
            filterPosts();
        });
    }

    /*
     * Reapplies client-side publications filtering when the price changes.
     */
    if (priceFilter) {
        priceFilter.addEventListener('change', function () {
            currentPostsPage = 1;
            filterPosts();
        });
    }

    /*
  * Updates the publications search button state while the user types.
  * The actual filtering now happens only when the user presses
  * the search button or submits the form with Enter.
  */
    if (postSearch) {
        postSearch.addEventListener('input', function () {
            updatePostsSearchButtonState();
        });
    }

    /*
     * Updates the requests search button state while the user types.
     * This section uses GET and reloads from the backend.
     */
    if (requestSearch) {
        requestSearch.addEventListener('input', updateRequestsSearchButtonState);
    }

    /*
     * Automatically submits the requests form when the user changes
     * the status filter dropdown.
     *
     * This is the missing behavior that makes the requests filters
     * apply immediately through GET.
     */
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            submitRequestsFilters();
        });
    }

    /*
     * Clears all publications filters locally and restores
     * the full client-side publications view.
     */
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

    /*
     * Ensures the requests clear-filters action preserves the
     * requests tab and stores the current scroll position first.
     */
    if (clearRequestsFilters) {
        clearRequestsFilters.addEventListener('click', function () {
            saveActiveTab('requests');
            saveScrollPosition();
        });
    }

    /*
     * Initializes the publications section and search button states.
     */
    updatePostsTabCount();
    filterPosts(true);
    updatePostsSearchButtonState();
    updateRequestsSearchButtonState();
});
