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

    let postCardToDelete = null;
    let currentPostsPage = 1;
    let currentRequestsPage = 1;

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

    function paginateItems(items, currentPage, paginationContainer, onPageChange) {
        const totalPages = Math.max(1, Math.ceil(items.length / ITEMS_PER_PAGE));
        const safePage = Math.min(currentPage, totalPages);
        const start = (safePage - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;

        items.forEach((item, index) => {
            item.classList.toggle('d-none', !(index >= start && index < end));
        });

        buildPagination(paginationContainer, totalPages, safePage, onPageChange);

        return safePage;
    }

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

    function filterPosts(resetPage = false) {
        const searchValue = postSearch ? postSearch.value.trim().toLowerCase() : '';
        const sportValue = sportFilter ? sportFilter.value : '';
        const priceValue = priceFilter ? priceFilter.value : '';

        const allCards = Array.from(document.querySelectorAll('.post-card-wrapper'));
        const matchingCards = [];

        allCards.forEach((card) => {
            const title = (card.dataset.title || '').toLowerCase();
            const description = (card.dataset.description || '').toLowerCase();
            const sport = card.dataset.sport || '';
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
        });
    }

    if (clearPostsFilters) {
        clearPostsFilters.addEventListener('click', function () {
            if (postSearch) postSearch.value = '';
            if (sportFilter) sportFilter.value = '';
            if (priceFilter) priceFilter.value = '';
            currentPostsPage = 1;
            filterPosts();
        });
    }

    const requestsFilterForm = document.getElementById('requestsFilterForm');
    const requestSearch = document.getElementById('requestSearch');
    const statusFilter = document.getElementById('statusFilter');
    const clearRequestsFilters = document.getElementById('clearRequestsFilters');
    const requestsEmptyState = document.getElementById('requestsEmptyState');
    const requestsPagination = document.getElementById('requestsPagination');

    function filterRequests(resetPage = false) {
        const searchValue = requestSearch ? requestSearch.value.trim().toLowerCase() : '';
        const statusValue = statusFilter ? statusFilter.value : '';

        const allCards = Array.from(document.querySelectorAll('.request-card'));
        const matchingCards = [];

        allCards.forEach((card) => {
            const title = (card.dataset.title || '').toLowerCase();
            const status = card.dataset.status || '';

            const matchesSearch = !searchValue || title.includes(searchValue);
            const matchesStatus = !statusValue || status === statusValue;

            if (matchesSearch && matchesStatus) {
                matchingCards.push(card);
            } else {
                card.classList.add('d-none');
            }
        });

        if (resetPage) {
            currentRequestsPage = 1;
        }

        if (requestsEmptyState) {
            requestsEmptyState.classList.toggle('d-none', matchingCards.length !== 0);
        }

        if (matchingCards.length === 0) {
            if (requestsPagination) requestsPagination.innerHTML = '';
            return;
        }

        currentRequestsPage = paginateItems(
            matchingCards,
            currentRequestsPage,
            requestsPagination,
            function (page) {
                currentRequestsPage = page;
                filterRequests(false);
            }
        );
    }

    if (requestsFilterForm) {
        requestsFilterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            currentRequestsPage = 1;
            filterRequests();
        });
    }

    if (requestSearch) {
        requestSearch.addEventListener('input', function () {
            currentRequestsPage = 1;
            filterRequests();
        });
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            currentRequestsPage = 1;
            filterRequests();
        });
    }

    if (clearRequestsFilters) {
        clearRequestsFilters.addEventListener('click', function () {
            if (requestSearch) requestSearch.value = '';
            if (statusFilter) statusFilter.value = '';
            currentRequestsPage = 1;
            filterRequests();
        });
    }

    updatePostsTabCount();
    filterPosts(true);
    filterRequests(true);
});
