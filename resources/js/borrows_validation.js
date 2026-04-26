/**
 * Initializes borrow management page behavior once the DOM is fully loaded.
 *
 * Responsibilities:
 * - manages client-side filtering by search text and date
 * - paginates pending and active request cards independently
 * - preserves scroll position across page reloads and form submissions
 * - handles approve, deny, and return confirmation modals
 * - shows success toasts after actions complete
 */

document.querySelectorAll('[data-bs-toggle="tooltip"]')

    /**
       * Activates tool tips
       */
    .forEach(el => new bootstrap.Tooltip(el));

document.addEventListener('DOMContentLoaded', function () {

    /**
     * Number of request cards shown per page in each section.
     * Pending and active requests are paginated separately.
     */
    const ITEMS_PER_PAGE = 18;

    /**
     * Session storage key used to preserve the current vertical scroll position
     * after a page reload or form submission.
     */
    const SCROLL_KEY = 'borrowsScrollY';

    /**
     * Session storage keys used to determine which success toast
     * should be displayed after the next page load.
     */
    const APPROVE_TOAST_KEY = 'borrows_approve_toast';
    const DENY_TOAST_KEY = 'borrows_deny_toast';
    const RETURNED_TOAST_KEY = 'borrows_returned_toast';

    /**
    * Filter controls
    *
    * Search and date filter elements used to narrow visible requests.
    */
    const borrowDateFilter = document.getElementById('borrowDateFilter');
    const borrowSearch = document.getElementById('borrowSearch');
    const borrowSearchBtn = document.getElementById('borrowSearchBtn');

    /**
     * Empty state containers
     *
     * These are shown when a section has no visible cards after filtering.
     */
    const pendingEmptyState = document.getElementById('pendingEmptyState');
    const activeEmptyState = document.getElementById('activeEmptyState');

    /**
     * Toast elements
     *
     * Success messages shown after approving, denying, or returning a request.
     */
    const approveToastEl = document.getElementById('approveToast');
    const denyToastEl = document.getElementById('denyToast');
    const returnedToastEl = document.getElementById('returnedToast');

    /**
    * Approve confirmation modal references
    *
    * Used to confirm before submitting an approval form.
    */
    const approveConfirmModalEl = document.getElementById('approveConfirmModal');
    const approveConfirmText = document.getElementById('approveConfirmText');
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');

    /**
    * Deny confirmation modal references
    *
    * Used to confirm before submitting a denial form.
    */
    const denyConfirmModalEl = document.getElementById('denyConfirmModal');
    const denyConfirmText = document.getElementById('denyConfirmText');
    const confirmDenyBtn = document.getElementById('confirmDenyBtn');


    /**
    * Return confirmation modal references
    *
    * Used to confirm before marking a request as returned.
    */
    const returnConfirmModalEl = document.getElementById('returnConfirmModal');
    const returnConfirmText = document.getElementById('returnConfirmText');
    const confirmReturnBtn = document.getElementById('confirmReturnBtn');

    /**
     * Pagination containers
     *
     * Each section has its own pagination controls and wrapper.
     */
    const pendingPagination = document.getElementById('pendingPagination');
    const activePagination = document.getElementById('activePagination');
    const pendingPaginationWrapper = document.getElementById('pendingPaginationWrapper');
    const activePaginationWrapper = document.getElementById('activePaginationWrapper');

    /**
     * Stores the form that should be submitted after the user confirms
     * an approve, deny, or return action.
     */
    let approveFormToSubmit = null;
    let denyFormToSubmit = null;
    let returnFormToSubmit = null;

    /**
     * Tracks the currently selected page for each section.
     * Pending and active requests are paginated independently.
     */
    let pendingCurrentPage = 1;
    let activeCurrentPage = 1;


    /**
     * Saves the current scroll position in sessionStorage.
     * This helps restore the user's position after reloads.
     */
    function saveScroll() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
    }

    /**
     * Removes any saved scroll position from sessionStorage.
     */
    function clearScroll() {
        sessionStorage.removeItem(SCROLL_KEY);
    }

    /**
     * Checks whether a clicked link belongs to pagination controls.
     *
     * @param {HTMLElement} link - Anchor element being checked.
     * @returns {boolean} True if the link is inside a pagination component.
     */
    function isPaginationLink(link) {
        return !!link.closest('.pagination');
    }

    /**
     * Restores the user's previous scroll position after page load.
     * Retries several times because layout height may not be ready immediately.
     */
    function restoreScroll() {
        const saved = sessionStorage.getItem(SCROLL_KEY);
        if (!saved) return;

        const target = parseInt(saved, 10);

        if (Number.isNaN(target)) {
            clearScroll();
            return;
        }

        let attempts = 0;
        const maxAttempts = 20;

        const tryScroll = () => {
            window.scrollTo(0, target);
            attempts++;

            if (attempts < maxAttempts && Math.abs(window.scrollY - target) > 5) {
                setTimeout(tryScroll, 100);
            } else {
                clearScroll();
            }
        };

        setTimeout(tryScroll, 100);
    }

    /**
     * Returns all request cards from both sections.
     *
     * @returns {HTMLElement[]} Array of all borrow request cards.
     */
    function getAllRequests() {
        return Array.from(document.querySelectorAll('.borrow-request'));
    }

    /**
     * Returns only the pending request cards.
     *
     * @returns {HTMLElement[]} Array of pending request cards.
     */
    function getPendingRequests() {
        return Array.from(document.querySelectorAll('.pending-request'));
    }

    /**
     * Returns only the active request cards.
     *
     * @returns {HTMLElement[]} Array of active request cards.
     */
    function getActiveRequests() {
        return Array.from(document.querySelectorAll('.active-request'));
    }


    /**
     * Returns only cards that are still visible according to current filters.
     * Cards marked with data-filtered-out are excluded.
     *
     * @param {HTMLElement[]} cards - Cards from one section.
     * @returns {HTMLElement[]} Cards that passed filtering.
     */
    function getFilteredCards(cards) {
        return cards.filter(card => !card.dataset.filteredOut);
    }

    /**
     * Enables the search button only when the search input
     * contains non-whitespace text.
     */
    function updateBorrowSearchButtonState() {
        if (!borrowSearch || !borrowSearchBtn) return;
        borrowSearchBtn.disabled = borrowSearch.value.trim().length === 0;
    }

    /**
     * Displays a Bootstrap toast element if both the element
     * and Bootstrap are available.
     *
     * @param {HTMLElement|null} toastElement - Toast DOM element to show.
     */
    function showToast(toastElement) {
        if (!toastElement || !window.bootstrap) return;
        const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    }

    /**
     * Reads stored toast flags from sessionStorage and shows the
     * corresponding success message after page reload.
     * It was originally done before but message was seen only
     * for a split second. This ensures proper readability.
     */
    function handleStoredToasts() {
        if (sessionStorage.getItem(APPROVE_TOAST_KEY) === '1') {
            sessionStorage.removeItem(APPROVE_TOAST_KEY);
            showToast(approveToastEl);
        }

        if (sessionStorage.getItem(DENY_TOAST_KEY) === '1') {
            sessionStorage.removeItem(DENY_TOAST_KEY);
            showToast(denyToastEl);
        }

        if (sessionStorage.getItem(RETURNED_TOAST_KEY) === '1') {
            sessionStorage.removeItem(RETURNED_TOAST_KEY);
            showToast(returnedToastEl);
        }
    }

    /**
     * Renders pagination buttons inside a given container.
     * Includes "Anterior", numbered pages, and "Siguiente".
     *
     * @param {HTMLElement|null} container - Pagination list container.
     * @param {number} totalPages - Total number of pages.
     * @param {number} currentPage - Currently active page.
     * @param {Function} onPageChange - Callback fired when a page is selected.
     */
    function renderPagination(container, totalPages, currentPage, onPageChange) {
        if (!container) return;

        container.innerHTML = '';

        if (totalPages <= 1) {
            return;
        }

        /**
         * Creates and appends a single pagination button.
         *
         * @param {Object} options - Page button configuration.
         * @param {string} options.label - Visible label.
         * @param {number} options.page - Page number represented.
         * @param {boolean} [options.disabled=false] - Whether button is disabled.
         * @param {boolean} [options.active=false] - Whether button is active.
         * @param {string} [options.ariaLabel=''] - Accessibility label.
         */
        const createPageItem = ({ label, page, disabled = false, active = false, ariaLabel = '' }) => {
            const li = document.createElement('li');
            li.className = `page-item${disabled ? ' disabled' : ''}${active ? ' active' : ''}`;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;

            if (ariaLabel) {
                button.setAttribute('aria-label', ariaLabel);
            }

            if (!disabled && !active) {
                button.addEventListener('click', function () {
                    clearScroll();
                    onPageChange(page);
                });
            }

            li.appendChild(button);
            container.appendChild(li);
        };

        createPageItem({
            label: 'Anterior',
            page: currentPage - 1,
            disabled: currentPage === 1,
            ariaLabel: 'Página anterior'
        });

        for (let page = 1; page <= totalPages; page++) {
            createPageItem({
                label: String(page),
                page,
                active: page === currentPage
            });
        }

        createPageItem({
            label: 'Siguiente',
            page: currentPage + 1,
            disabled: currentPage === totalPages,
            ariaLabel: 'Página siguiente'
        });
    }

    /**
     * Applies pagination rules to one request section.
     * Handles:
     * - filtered card visibility
     * - empty state visibility
     * - pagination wrapper visibility
     * - rendering the correct page controls
     *
     * @param {Object} config - Section pagination configuration.
     * @param {HTMLElement[]} config.cards - Cards belonging to the section.
     * @param {number} config.currentPage - Current page number for the section.
     * @param {Function} config.setCurrentPage - Setter for section page state.
     * @param {HTMLElement|null} config.paginationContainer - Pagination list element.
     * @param {HTMLElement|null} config.paginationWrapper - Outer pagination wrapper.
     * @param {HTMLElement|null} config.emptyState - Empty state element.
     */
    function paginateSection({
                                 cards,
                                 currentPage,
                                 setCurrentPage,
                                 paginationContainer,
                                 paginationWrapper,
                                 emptyState
                             }) {
        const filteredCards = getFilteredCards(cards);
        const totalItems = filteredCards.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / ITEMS_PER_PAGE));

        if (currentPage > totalPages) {
            currentPage = totalPages;
            setCurrentPage(currentPage);
        }

        if (totalItems === 0) {
            cards.forEach(card => {
                card.classList.add('d-none');
            });

            if (emptyState) {
                emptyState.classList.remove('d-none');
            }

            if (paginationWrapper) {
                paginationWrapper.classList.add('d-none');
            }

            if (paginationContainer) {
                paginationContainer.innerHTML = '';
            }

            return;
        }

        if (emptyState) {
            emptyState.classList.add('d-none');
        }

        const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;

        cards.forEach(card => {
            if (card.dataset.filteredOut === 'true') {
                card.classList.add('d-none');
                return;
            }

            card.classList.add('d-none');
        });

        filteredCards.slice(startIndex, endIndex).forEach(card => {
            card.classList.remove('d-none');
        });

        if (paginationWrapper) {
            paginationWrapper.classList.toggle('d-none', totalPages <= 1);
        }

        renderPagination(paginationContainer, totalPages, currentPage, function (page) {
            setCurrentPage(page);
            applyFiltersAndPagination();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }


    /**
     * Applies current search/date filters to all request cards,
     * then paginates pending and active sections separately.
     *
     * @param {boolean} [resetPages=false] - Whether both sections should return to page 1.
     */
    function applyFiltersAndPagination(resetPages = false) {
        const selectedDate = borrowDateFilter ? borrowDateFilter.value : '';
        const searchValue = borrowSearch ? borrowSearch.value.trim().toLowerCase() : '';

        getAllRequests().forEach(card => {
            const cardDate = card.dataset.date || '';
            const cardSearch = (card.dataset.search || '').toLowerCase();

            const matchesDate = !selectedDate || cardDate === selectedDate;
            const matchesSearch = !searchValue || cardSearch.includes(searchValue);
            const isVisibleByFilter = matchesDate && matchesSearch;

            if (isVisibleByFilter) {
                delete card.dataset.filteredOut;
            } else {
                card.dataset.filteredOut = 'true';
            }
        });

        if (resetPages) {
            pendingCurrentPage = 1;
            activeCurrentPage = 1;
        }

        paginateSection({
            cards: getPendingRequests(),
            currentPage: pendingCurrentPage,
            setCurrentPage: function (page) {
                pendingCurrentPage = page;
            },
            paginationContainer: pendingPagination,
            paginationWrapper: pendingPaginationWrapper,
            emptyState: pendingEmptyState
        });

        paginateSection({
            cards: getActiveRequests(),
            currentPage: activeCurrentPage,
            setCurrentPage: function (page) {
                activeCurrentPage = page;
            },
            paginationContainer: activePagination,
            paginationWrapper: activePaginationWrapper,
            emptyState: activeEmptyState
        });
    }

    /**
     * Attaches click listeners to all approve buttons.
     * When clicked, the related form is stored and the approval
     * confirmation modal is shown before submission.
     */
    function attachApproveEvents() {
        document.querySelectorAll('.approve-special-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');
                const card = button.closest('.borrow-request');
                const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                approveFormToSubmit = form;
                approveConfirmText.textContent = `¿Seguro que quieres aprobar "${itemName}"?`;

                const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
                modal.show();
            });
        });
    }

    /**
     * Attaches click listeners to all deny buttons.
     * Stores the related form and opens the denial confirmation modal.
     */
    function attachDenyEvents() {
        document.querySelectorAll('.deny-special-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');
                const card = button.closest('.borrow-request');
                const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                denyFormToSubmit = form;
                denyConfirmText.textContent = `¿Seguro que quieres denegar "${itemName}"?`;

                const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
                modal.show();
            });
        });
    }

    /**
     * Attaches click listeners to all return buttons.
     * Stores the related form and opens the return confirmation modal.
     */
    function attachReturnEvents() {
        document.querySelectorAll('.mark-returned-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');

                returnFormToSubmit = form;
                returnConfirmText.textContent = '¿Estás seguro de que esta solicitud fue devuelta?';

                const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
                modal.show();
            });
        });
    }

    /**
     * Submits the stored approval form after the user confirms.
     * Also hides the modal, stores a toast flag, and saves scroll position.
     */
    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener('click', function () {
            if (!approveFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
            modal.hide();

            sessionStorage.setItem(APPROVE_TOAST_KEY, '1');
            saveScroll();

            setTimeout(() => {
                approveFormToSubmit.submit();
            }, 150);
        });
    }

    /**
     * Submits the stored denial form after the user confirms.
     * Also hides the modal, stores a toast flag, and saves scroll position.
     */
    if (confirmDenyBtn) {
        confirmDenyBtn.addEventListener('click', function () {
            if (!denyFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
            modal.hide();

            sessionStorage.setItem(DENY_TOAST_KEY, '1');
            saveScroll();

            setTimeout(() => {
                denyFormToSubmit.submit();
            }, 150);
        });
    }

    /**
     * Submits the stored return form after the user confirms.
     * Also hides the modal, stores a toast flag, and saves scroll position.
     */
    if (confirmReturnBtn) {
        confirmReturnBtn.addEventListener('click', function () {
            if (!returnFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
            modal.hide();

            sessionStorage.setItem(RETURNED_TOAST_KEY, '1');
            saveScroll();

            setTimeout(() => {
                returnFormToSubmit.submit();
            }, 150);
        });
    }

    /**
     * Saves scroll position before any form submission on the page.
     * This helps return the user to approximately the same place afterward.
     */
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function () {
            saveScroll();
        });
    });

    /**
     * Saves scroll position before navigating through regular links.
     * Pagination links are excluded because pagination should start from top.
     */
    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function () {
            if (isPaginationLink(link)) {
                clearScroll();
                return;
            }

            saveScroll();
        });
    });

    /**
     * Updates search button state and reapplies filtering as the user types.
     * Filtering resets both sections back to page 1.
     */
    if (borrowSearch) {
        borrowSearch.addEventListener('input', function () {
            updateBorrowSearchButtonState();
            applyFiltersAndPagination(true);
        });
    }


    /**
     * Reapplies filtering whenever the date filter changes.
     * Pagination is reset to page 1 in both sections.
     */
    if (borrowDateFilter) {
        borrowDateFilter.addEventListener('change', function () {
            applyFiltersAndPagination(true);
        });
    }

    /**
    * Initial page setup
    *
    * Binds action buttons, applies default filtering/pagination state,
    * updates the search button, handles pending toasts,
    * and restores scroll position after full page load.
    */
    attachApproveEvents();
    attachDenyEvents();
    attachReturnEvents();
    updateBorrowSearchButtonState();
    applyFiltersAndPagination(true);
    handleStoredToasts();

    window.addEventListener('load', restoreScroll);
});
