document.addEventListener('DOMContentLoaded', function () {

    /**
     * Defines the number of post cards displayed per page
     * in the client-side publications section.
     */
    const ITEMS_PER_PAGE = 18;

    /**
     * Storage keys used to persist UI state between reloads.
     * - PROFILE_SCROLL_KEY stores the user's vertical scroll position
     * - PROFILE_ACTIVE_TAB_KEY stores the active profile tab
     */
    const PROFILE_SCROLL_KEY = 'myProfileScrollY';
    const PROFILE_ACTIVE_TAB_KEY = 'myProfileActiveTab';

    const POSTS_SEARCH_KEY = 'myProfilePostsSearch';
    const POSTS_SPORT_KEY = 'myProfilePostsSport';
    const POSTS_PRICE_KEY = 'myProfilePostsPrice';

    /**
     * Profile tab buttons and shared tab UI references.
     */
    const postsTab = document.getElementById('posts-tab');
    const requestsTab = document.getElementById('requests-tab');
    const profileTabButtons = document.querySelectorAll('#profileTabs button');

    /**
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
    * Modal and toast elements for database backup confirmation.
    */
    const databaseBackupModalEl = document.getElementById('databaseBackupWarningModal');
    const confirmDatabaseBackupBtn = document.getElementById('confirmDatabaseBackup');
    const databaseBackupToastEl = document.getElementById('databaseBackupToast');

    /*
     * Tracks the current post selected for deletion and
     * the active page in the client-side posts pagination.
     */
    let postCardToDelete = null;
    let postIdToDelete = null;
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
     * Saves the current publications filters before reloading the page.
     */
    function savePostsFilters() {
        if (postSearch) {
            sessionStorage.setItem(POSTS_SEARCH_KEY, postSearch.value.trim());
        }

        if (sportFilter) {
            sessionStorage.setItem(POSTS_SPORT_KEY, sportFilter.value);
        }

        if (priceFilter) {
            sessionStorage.setItem(POSTS_PRICE_KEY, priceFilter.value);
        }

        saveActiveTab('posts');
        saveScrollPosition();
    }

    /**
     * Restores publications filters after the page reloads.
     */
    function restorePostsFilters() {
        const savedSearch = sessionStorage.getItem(POSTS_SEARCH_KEY);
        const savedSport = sessionStorage.getItem(POSTS_SPORT_KEY);
        const savedPrice = sessionStorage.getItem(POSTS_PRICE_KEY);

        if (savedSearch !== null && postSearch) {
            postSearch.value = savedSearch;
        }

        if (savedSport !== null && sportFilter) {
            sportFilter.value = savedSport;
        }

        if (savedPrice !== null && priceFilter) {
            priceFilter.value = savedPrice;
        }
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

        if (tabFromUrl === 'requests') {
            activateTab(requestsTab);
            return;
        }

        activateTab(postsTab);
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
     * Evaluates whether a post's price belongs to the selected price range.
     *
     * @param {string|number} price - Post price.
     * @param {string} selectedRange - Selected filter value.
     * @returns {boolean} True when the price matches the selected range, otherwise false.
     */
    function matchesPriceRange(price, selectedRange) {
        const numericPrice = Number(price);

        if (selectedRange === 'all') return true;
        if (selectedRange === '0') return numericPrice === 0;
        if (selectedRange === '0.01-9.99') return numericPrice >= 0.01 && numericPrice <= 9.99;
        if (selectedRange === '10-29.99') return numericPrice >= 10 && numericPrice <= 29.99;
        if (selectedRange === '30-49.99') return numericPrice >= 30 && numericPrice <= 49.99;
        if (selectedRange === '50+') return numericPrice >= 50;

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
     * Formats the publication date shown on each profile post card.
     *
     * If the post is less than 7 days old, it keeps the relative
     * time text generated by Laravel, such as "hace 38 segundos" or
     * "hace 4 días".
     *
     * If the post is 7 days old or older, it displays the exact
     * publication date using the format dd-MMM-yyyy in Spanish.
     *
     * @param {string|null} createdAtValue - ISO creation date stored in the post card dataset.
     * @param {string|null} timeAgoValue - Relative date text generated by Laravel.
     * @returns {string} Formatted publication text.
     */
    function formatProfilePublishedDate(createdAtValue, timeAgoValue) {
        if (!createdAtValue) {
            return `Publicado ${timeAgoValue}`;
        }

        const createdAt = new Date(createdAtValue);
        const now = new Date();
        const daysPassed = (now - createdAt) / (1000 * 60 * 60 * 24);

        if (daysPassed < 7) {
            return `Publicado ${timeAgoValue}`;
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
     * Updates all profile post cards with the correct publication date text.
     *
     * It reads the post creation date and relative time from each card's
     * data attributes, formats the text, and replaces the visible
     * publication label inside the card.
     */
    function updateProfilePublishedDates() {
        document.querySelectorAll('.post-card-wrapper').forEach((card) => {
            const publishedDate = card.querySelector('.profile-post-published-date');

            if (!publishedDate) return;

            publishedDate.textContent = formatProfilePublishedDate(
                card.dataset.createdAt,
                card.dataset.timeAgo
            );
        });
    }

    updateProfilePublishedDates();

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
  * Saves scroll position before link navigation.
  * This keeps the user's position after GET reloads such as
  * requests pagination and filter links.
  */
    document.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            saveScrollPosition();

            if (isPaginationLink(link)) {
                saveActiveTab('requests');
            }
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

            postIdToDelete = this.dataset.postId;
            postCardToDelete = this.closest('.post-card-wrapper');

            if (deletePostModalText) {
                deletePostModalText.textContent = `"${postTitle}" será eliminada permanentemente.`;
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
        confirmDeletePostBtn.addEventListener('click', async function () {
            if (!postIdToDelete || !postCardToDelete) return;

            confirmDeletePostBtn.disabled = true;

            try {
                const response = await fetch(`/posts/${postIdToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                if (!response.ok) {
                    throw new Error('No se pudo eliminar la publicación.');
                }

                if (deletePostModalEl && window.bootstrap) {
                    const modal = window.bootstrap.Modal.getOrCreateInstance(deletePostModalEl);
                    modal.hide();
                }

                postCardToDelete.remove();
                postCardToDelete = null;
                postIdToDelete = null;

                updatePostsTabCount();
                filterPosts(true);
                showToast(deletePostToastEl);

            } catch (error) {
                console.error(error);
                alert('No se pudo eliminar la publicación. Intenta nuevamente.');
            } finally {
                confirmDeletePostBtn.disabled = false;
            }
        });
    }

    /*
     * Prevents full reload in the publications filter form because
     * that section is fully filtered on the client side.
     */
    if (postsFilterForm) {
        postsFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();

            savePostsFilters();
            window.location.reload();
        });
    }

    /*
     * Reapplies client-side publications filtering when the sport changes.
     */
    if (sportFilter) {
        sportFilter.addEventListener('change', function () {
            savePostsFilters();
            window.location.reload();
        });
    }

    /*
     * Reapplies client-side publications filtering when the price changes.
     */
    if (priceFilter) {
        priceFilter.addEventListener('change', function () {
            savePostsFilters();
            window.location.reload();
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
            sessionStorage.removeItem(POSTS_SEARCH_KEY);
            sessionStorage.removeItem(POSTS_SPORT_KEY);
            sessionStorage.removeItem(POSTS_PRICE_KEY);

            if (postSearch) postSearch.value = '';
            if (sportFilter) sportFilter.value = '';
            if (priceFilter) priceFilter.value = 'all';

            saveActiveTab('posts');
            saveScrollPosition();

            window.location.reload();
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
    * Shows a warning confirmation flow before creating a database backup.
    * The actual download starts after the Super Administrator confirms.
    */
    if (confirmDatabaseBackupBtn) {
        confirmDatabaseBackupBtn.addEventListener('click', function () {
            if (databaseBackupModalEl && window.bootstrap) {
                const modal = window.bootstrap.Modal.getOrCreateInstance(databaseBackupModalEl);
                modal.hide();
            }

            showToast(databaseBackupToastEl);
        });
    }

    /*
     * Initializes the publications section and search button states.
     */
    restorePostsFilters();


    updatePostsTabCount();
    filterPosts(true);
    updatePostsSearchButtonState();
    updateRequestsSearchButtonState();
});
