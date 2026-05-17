import * as bootstrap from 'bootstrap';
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.min.css";

/**
 * Marketplace report/querella management front-end behavior controller.
 *
 * This file controls the client-side behavior for the Gestión de Mercado page.
 * Responsible:
 * - loading marketplace reports from the backend
 * - rendering report rows dynamically inside the reports table
 * - marking "Contenido inapropiado" reports as urgent
 * - filtering reports by reason, user name, seller name, and report date
 * - validating the report search input allowed characters
 * - rendering local pagination for the reports table
 * - opening confirmation modals for administrative actions
 * - resolving reports
 * - deleting reported marketplace posts
 * - blocking reported users
 * - opening and populating the post details modal
 * - resetting selected action radios when modals close
 * - displaying Bootstrap toast notifications after successful actions
 */


/**
* Variables that specify the maximum amount of reports per pagination page and
 * the current pagination page upon loading.
*/
    const REPORTS_PER_PAGE = 18;
    let currentReportsPage = 1;


/**
 * Main page initializer.
 *
 * Runs after the DOM (Document object model) is ready so all Blade-rendered elements exist before
 * references, Bootstrap components, date pickers, and event listeners are registered.
 */
document.addEventListener('DOMContentLoaded', () => {

    /**
     * Activates every Bootstrap tooltip used by the report table action headers.
     *
     * The Blade table headers use data-bs-title to explain each action icon.
     */
     const tooltipTriggerList = document.querySelectorAll('[data-bs-title]');
     tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    /**
     * Short helper for finding elements by ID.
     *
     * @param {string} id - Element ID without the # symbol.
     * @returns {HTMLElement|null} Matching DOM element.
     */
     const $ = (id) => document.getElementById(id);

    /**
     * Gets all currently rendered report table rows.
     *
     * Rows are inserted dynamically by renderReportsFromBackend().
     *
     * @returns {NodeListOf<HTMLTableRowElement>} Current table body rows.
     */
     const rows = () => document.querySelectorAll('#reportsTable tbody tr');

    /**
     * Stores reports that were resolved locally.
     *
     * This prevents a report from being shown again during local filtering after
     * it was removed from the interface.
     */
     const resolvedReports = new Set();

    /**
     * Search input allowed character regex.
     *
     * The report search only accepts letters, Spanish accents, periods, and spaces
     * because the filter searches by reporting user name and seller name.
     */
     const allowedSearchCharRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ.\s]$/;


    /**
     * Removes unsupported characters from the report search input.
     *
     * Used for typing, paste cleanup, and blur cleanup.
     *
     * @param {string} value - Raw search value.
     * @returns {string} Search value containing only allowed characters.
     */
     function sanitizeSearchValue(value) {
         return [...value]
             .filter((char) => allowedSearchCharRegex.test(char))
             .join('');
     }

    /**
     * Loads marketplace reports from the backend.
     *
     * The backend returns report data from /reports/data, then this function
     * passes that data to renderReportsFromBackend() so the table can be rebuilt.
     */
     async function fetchReports() {
         const res = await fetch('/reports/data');
         const data = await res.json();

         renderReportsFromBackend(data);
     }

    /**
     * Renders all backend reports inside the report table body.
     *
     * Each row receives data-* attributes used later for filtering and action handling.
     * Reports with reason "Contenido inapropiado" are visually marked as urgent by
     * adding a red badge and red border styling around the full row.
     *
     * @param {Array<Object>} reports - Marketplace reports returned by the backend.
     */
     function renderReportsFromBackend(reports) {
            const tbody = document.querySelector('#reportsTable tbody');
            tbody.innerHTML = '';

            reports.forEach(report => {
                const isUrgentReport = normalize(report.report_reason) === 'contenido inapropiado';

                const urgentCellStyle = isUrgentReport
                    ? 'background-color: rgba(220,53,69,.15); border-top: 2px solid #dc3545; border-bottom: 2px solid #dc3545;'
                    : '';

                const urgentFirstCellStyle = isUrgentReport
                    ? `${urgentCellStyle} border-left: 2px solid #dc3545;`
                    : '';

                const urgentLastCellStyle = isUrgentReport
                    ? `${urgentCellStyle} border-right: 2px solid #dc3545;`
                    : '';

                const reasonDisplay = isUrgentReport
                    ? `<span class="badge bg-danger mb-1">Urgente</span><br>${report.report_reason}`
                    : report.report_reason;


                /**
                 * Action radio buttons.
                 *
                 * Each report row includes four radio actions:
                 * - action-view opens the reported publication details modal
                 * - action-resolve opens the resolve confirmation modal
                 * - action-delete-post opens the delete publication confirmation modal
                 * - action-block-user opens the block user confirmation modal
                 *
                 * All radios in the same row share the same name value so only one action
                 * can be selected at a time for that specific report.
                 */
                const row = `
            <tr
                data-report-id="${report.id}"
                data-report-date="${formatReportDateForFilter(report.created_at)}"
                data-report-reason="${report.report_reason || ''}"
                data-post="${encodeURIComponent(JSON.stringify(report.post || {}))}"
                data-seller-id="${report.reported_user_id}"
                data-post-id="${report.post_id}"
            >
                <td style="${urgentFirstCellStyle}">${report.reporter?.name || ''}</td>
                <td style="${urgentCellStyle}">${report.reported_user?.name || ''}</td>
                <td style="${urgentCellStyle}" class="${isUrgentReport ? 'fw-bold text-danger' : ''}">
                    ${reasonDisplay}
                </td>
                <td style="${urgentCellStyle}">${formatReportDisplayDate(report.created_at)}</td>
                <td style="${urgentCellStyle}">${report.description}</td>

                <td class="text-center" style="${urgentCellStyle}">
                    <input class="form-check-input action-radio action-view" type="radio" name="action_${report.id}">
                </td>
                <td class="text-center" style="${urgentCellStyle}">
                    <input class="form-check-input action-radio action-resolve" type="radio" name="action_${report.id}">
                </td>
                <td class="text-center" style="${urgentCellStyle}">
                    <input class="form-check-input action-radio action-delete-post" type="radio" name="action_${report.id}">
                </td>
                <td class="text-center" style="${urgentLastCellStyle}">
                    <input class="form-check-input action-radio action-block-user" type="radio" name="action_${report.id}">
                </td>
            </tr>
        `;

                tbody.insertAdjacentHTML('beforeend', row);
            });

            bindAction('.action-resolve', els.resolveModal, 'resolve');
            bindAction('.action-delete-post', els.deleteModal, 'delete');
            bindAction('.action-block-user', els.banModal, 'ban');

            renderReports();
     }

    /**
     * Formats report dates for display inside the reports table.
     *
     * Converts backend timestamps into the format:
     * Day Month Year
     *
     * Example:
     * 9 Mayo 2026
     *
     * @param {string} dateValue - Backend report creation date.
     * @returns {string} Formatted display date.
     */
    function formatReportDisplayDate(dateValue) {
        if (!dateValue) return '';

        const date = new Date(dateValue);

        const formattedDate = date.toLocaleDateString('es-PR', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

        return formattedDate
            .replaceAll(' de ', ' ')
            .split(' ')
            .map((part, index) => {
                if (index === 1) {
                    return part.charAt(0).toUpperCase() + part.slice(1);
                }

                return part;
            })
            .join(' ');
    }

    /**
     * Formats a backend report date for filter comparison.
     *
     * The Flatpickr filter stores dates as YYYY-MM-DD, so report rows need the
     * same format in data-report-date for exact filtering.
     *
     * @param {string} dateValue - Backend report creation date.
     * @returns {string} Date formatted as YYYY-MM-DD.
     */
    function formatReportDateForFilter(dateValue) {
            if (!dateValue) return '';

            const date = new Date(dateValue);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

    /**
     * DOM references.
     *
     * These elements control report filtering, modals, action buttons,
     * table visibility, empty state, and pagination.
     */
    const els = {
    filterReason: $('filterReason'),
    filterSearchBy: $('filterSearchBy'),
    filterDate: $('filterDate'),
    filterDateIcon: $('filterDateIcon'),

    resolveModal: $('resolveQuerellaModal'),
    deleteModal: $('deletePostModal'),
    banModal: $('bloquearUserModal'),
    searchReportsBtn: $('searchReportsBtn'),
    confirmResolve: $('confirmResolveQuerella'),
    confirmDelete: $('confirmDeletePost'),
    confirmBan: $('confirmBloquearUser'),

    reportsTable: $('reportsTable'),
    emptyState: $('reportsEmptyState'),
    reportsPagination: $('querellasPagination'),
    };

    /**
     * Clear filters button reference.
     *
     * Resets the search text, reason filter, date picker, and current filtered results.
     */
    const clearReportsFiltersBtn = document.getElementById('clearReportsFilters');

    /**
     * Toast element IDs grouped by action.
     *
     * Each key maps to the Bootstrap toast that should appear after that action succeeds.
     */
     const toastIds = {
         resolve: 'resolveToast',
         delete: 'deleteToast',
         ban: 'banToast',
     };

    /**
     * Bootstrap toast instances.
     *
     * Created from the toastIds map so action handlers can call toasts[key].show().
     */
    const toasts = Object.fromEntries(
    Object.entries(toastIds).map(([key, id]) => [
            key,
            bootstrap.Toast.getOrCreateInstance($(id), { delay: 3000 })
       ])
    );

    /**
     * Stores the selected radio/action before confirmation.
     *
     * When the user clicks an action radio, the matching property is set.
     * The confirmation button later uses this selected radio to find the report row.
     */
     let selected = {
         resolve: null,
         delete: null,
         ban: null,
    };

    /**
     * Normalizes text for safe filter comparison.
     *
     * @param {string} text - Text to normalize.
     * @returns {string} Lowercase trimmed text.
     */
    const normalize = (text) => (text || '').toLowerCase().trim();

    /**
     * Applies the current filters starting from the first page.
     *
     * Used when the user changes filters or clicks the search button.
     */
    function applyFilters() {
    currentReportsPage = 1;
    renderReports();
    }

    /**
     * Enables or disables the reports search button.
     *
     * The search button stays disabled until the search input contains text.
     */
    function updateReportsSearchButtonState() {
        if (!els.filterSearchBy || !els.searchReportsBtn) return;

        const value = els.filterSearchBy.value;
        els.searchReportsBtn.disabled = value.trim() === '';
    }

    /**
     * Renders local pagination controls for the filtered reports.
     *
     * The pagination is rebuilt every time reports are filtered or the page changes.
     *
     * @param {HTMLElement} container - Pagination list container.
     * @param {number} currentPage - Current active page.
     * @param {number} totalItems - Number of filtered report rows.
     * @param {number} itemsPerPage - Number of report rows shown per page.
     * @param {Function} onPageChange - Callback executed when a new page is selected.
     */
    function renderLocalPagination(container, currentPage, totalItems, itemsPerPage, onPageChange) {
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

    /**
     * Returns the report rows that match the active filters.
     *
     * Filters checked:
     * - report reason
     * - reporting user name
     * - seller name
     * - report date
     * - locally resolved report IDs
     *
     * @returns {HTMLTableRowElement[]} Filtered report rows.
     */
        function getFilteredRows() {
            const filters = {
                reason: normalize(els.filterReason.value),
                user: normalize(els.filterSearchBy.value),
                date: els.filterDate.value
            };

            return [...rows()].filter((row) => {
                const reportId = row.dataset.reportId || '';
                if (reportId && resolvedReports.has(reportId)) {
                    return false;
                }

                const reportedBy = normalize(row.cells[0].textContent);
                const seller = normalize(row.cells[1].textContent);
                const reason = normalize(row.dataset.reportReason || row.cells[2].textContent);
                const date = row.dataset.reportDate || '';

                return (
                    (!filters.reason || filters.reason === '' || reason === filters.reason) &&
                    (
                        !filters.user ||
                        filters.user === '' ||
                        reportedBy.includes(filters.user) ||
                        seller.includes(filters.user)
                    ) &&
                    (!filters.date || filters.date === '' || date === filters.date)
                );
            });
        }

    /**
     * Clear filters button handler.
     *
     * Clears text search, reason dropdown, Flatpickr date value,
     * search button state, and visible report results.
     */
        clearReportsFiltersBtn?.addEventListener('click', () => {
        els.filterSearchBy.value = '';
        els.filterReason.value = '';
        els.filterDate._flatpickr?.clear();

        updateReportsSearchButtonState();
        applyFilters();
    });

    /**
     * Renders the report table based on filters and pagination.
     *
     * This function hides all rows first, then shows only the rows that match the
     * current page. It also toggles the empty state and rebuilds pagination.
     */
        function renderReports() {
        const allRows = [...rows()];
        const filteredRows = getFilteredRows();

        const totalPages = Math.max(1, Math.ceil(filteredRows.length / REPORTS_PER_PAGE));

        if (currentReportsPage > totalPages) {
        currentReportsPage = totalPages;
    }

        const start = (currentReportsPage - 1) * REPORTS_PER_PAGE;
        const end = start + REPORTS_PER_PAGE;
        const paginatedRows = filteredRows.slice(start, end);

        allRows.forEach((row) => {
        row.style.display = 'none';
    });

        paginatedRows.forEach((row) => {
        row.style.display = '';
    });

        const shouldShowEmpty = filteredRows.length === 0;
        els.emptyState.classList.toggle('d-none', !shouldShowEmpty);
        els.reportsTable.classList.toggle('d-none', shouldShowEmpty);

        renderLocalPagination(
        els.reportsPagination,
        currentReportsPage,
        filteredRows.length,
        REPORTS_PER_PAGE,
        (page) => {
        currentReportsPage = page;
        renderReports();
    }
        );
    }

    /**
     * This prevents unsupported characters from being typed or pasted into the
     * search field while keeping cursor position as stable as possible.
     *
     * @param {HTMLInputElement} input - Search input element.
     */
        function bindNameInput(input) {
            if (!input) return;

            input.addEventListener('keydown', (event) => {
                const allowedControlKeys = [
                    'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight',
                    'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End', 'Enter'
                ];

                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyFilters();
                    return;
                }

                if (allowedControlKeys.includes(event.key) || event.ctrlKey || event.metaKey) return;
                if (!allowedSearchCharRegex.test(event.key)) event.preventDefault();
            });



                input.addEventListener('input', () => {
                    const cleanValue = sanitizeSearchValue(input.value);

                    if (input.value !== cleanValue) {
                        const cursorPos = input.selectionStart;
                        input.value = cleanValue;
                        input.setSelectionRange(cursorPos, cursorPos);
                    }
                });

                input.addEventListener('paste', (event) => {
                    event.preventDefault();

                    const pasted = (event.clipboardData || window.clipboardData).getData('text');
                    const clean = sanitizeSearchValue(pasted);

                    const start = input.selectionStart;
                    const end = input.selectionEnd;

                    const newValue = input.value.slice(0, start) + clean + input.value.slice(end);
                    input.value = sanitizeSearchValue(newValue);

                    const newCursor = start + clean.length;
                    input.setSelectionRange(newCursor, newCursor);
                });

                input.addEventListener('blur', () => {
                    input.value = sanitizeSearchValue(input.value);
                });
            }


    /**
     * Opens the matching confirmation modal when an action radio changes.
     *
     * The selected radio is stored so the confirmation button knows which row
     * should be affected.
     *
     * @param {string} selector - Action radio selector.
     * @param {HTMLElement} modalEl - Modal element connected to the action.
     * @param {string} key - Selected action key.
     */
        function bindAction(selector, modalEl, key) {
        document.querySelectorAll(selector).forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
        if (!this.checked) return;
        selected[key] = this;
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
       });
     });
    }


    /**
     * Resets a selected action radio when its confirmation modal closes.
     *
     * This prevents the row from keeping a checked radio after the user cancels
     * or closes the modal without confirming.
     *
     * @param {HTMLElement} modalEl - Modal element that resets the selected action.
     * @param {string} key - Selected action key to clear.
     */
    function bindModalReset(modalEl, key) {
    modalEl?.addEventListener('hidden.bs.modal', () => {
    if (selected[key]) {
    selected[key].checked = false;
    selected[key] = null;
       }
     });
    }

    /**
     * Binds confirmation buttons to their backend administrative actions.
     *
     * Actions that can be done:
     * - resolve: marks the report as resolved
     * - delete: deletes the post, then resolves the report
     * - ban: blocks the reported user, then resolves the report
     *
     * @param {HTMLButtonElement} button - Confirmation button.
     * @param {string} key - Action key.
     * @param {HTMLElement} modalEl - Modal to close after action.
     * @param {string} toastKey - Toast key to show after action.
     */
    function bindConfirm(button, key, modalEl, toastKey) {
        button?.addEventListener('click', async () => {
            if (!selected[key]) return;

            const row = selected[key].closest('tr');
            const reportId = row.dataset.reportId;
            const postId = row.dataset.postId;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            try {
                if (key === 'resolve') {
                    await fetch(`/reports/${reportId}/resolve`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });
                }

                if (key === 'delete') {
                    await fetch(`/posts/${postId}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });

                    await fetch(`/reports/${reportId}/resolve`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });
                    fetchReports();
                }

                if (key === 'ban') {
                    await fetch(`/reports/${reportId}/ban`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });

                    await fetch(`/reports/${reportId}/resolve`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf }
                    });
                }

                // Removes the row from the user interface
                row.remove();
                selected[key] = null;
                toasts[toastKey]?.show();

            } catch (error) {
                console.error('Error:', error);
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).hide();
        });
    }

        bindNameInput(els.filterSearchBy);

        /**
         * Updates the search button state while the user types.
         */
        els.filterSearchBy?.addEventListener('input', () => {
                updateReportsSearchButtonState();
            });

        /**
         * Applies the search filter with Enter only when the search button is enabled.
         */
        els.filterSearchBy?.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();

                    if (!els.searchReportsBtn?.disabled) {
                        applyFilters();
                    }
                }
            });

    /**
     * Applies the search filter when the search button is clicked.
     */
        els.searchReportsBtn?.addEventListener('click', () => {
                applyFilters();
            });

    /**
     * Applies the reason filter when the dropdown changes.
     */
        els.filterReason?.addEventListener('change', applyFilters);

    /**
     * Reapplies filters when the date value is manually cleared.
     */
        els.filterDate?.addEventListener('input', () => {
            if (els.filterDate.value === '') {
                    applyFilters();
            }
        });

    /**
     * Initializes the report date picker.
     *
     * Flatpickr stores the real input value as YYYY-MM-DD for filtering,
     * while the alt input shows a user-friendly Spanish date.
     */
        function initializeReportsDatePicker() {
            if (!els.filterDate) return;

            flatpickr(els.filterDate, {
                locale: Spanish,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'j F Y',
                allowInput: false,
                disableMobile: true,
                onChange: function () {
                    applyFilters();
                }
            });

            if (els.filterDate._flatpickr?.altInput) {
                els.filterDate._flatpickr.altInput.placeholder = 'Día Mes Año';
                els.filterDate._flatpickr.altInput.classList.add('date-picker-input');
            }

            els.filterDateIcon?.addEventListener('click', () => {
                els.filterDate._flatpickr?.open();
            });
        }

    /**
     * Handles dynamically rendered radio buttons
     *
     * Opens modals corresponding to:
     * - resolve opens resolve modal
     * - delete opens delete modal
     * - block opens block modal
     * - view fetches post details and opens the post details modal
     */
    document.addEventListener('change', async (e) => {
        const target = e.target;

        if (target.matches('.action-resolve')) {
            selected.resolve = target;
            bootstrap.Modal.getOrCreateInstance(els.resolveModal).show();
        }

        if (target.matches('.action-delete-post')) {
            selected.delete = target;
            bootstrap.Modal.getOrCreateInstance(els.deleteModal).show();
        }

        if (target.matches('.action-block-user')) {
            selected.ban = target;
            bootstrap.Modal.getOrCreateInstance(els.banModal).show();
        }

        if (target.matches('.action-view')) {
            const row = target.closest('tr');
            const postId = row.dataset.postId;

            if (!postId) return;

            const res = await fetch(`/posts/${postId}`);
            const post = await res.json();

            console.log('FULL POST:', post);

            window.populatePostDetailsModal(post);

            const modal = bootstrap.Modal.getOrCreateInstance(
                document.getElementById('postDetailsModal')
            );

            modal.show();
        }

    });

    /**
     * Post details modal reference.
     *
     * Used to reset the selected view radio after the modal closes.
     */
    const modalEl = document.getElementById('postDetailsModal');

    /**
     * Resets every view-publication radio when the post details modal closes.
     *
     * This keeps the table action radios visually clean after viewing a post.
     */
    modalEl.addEventListener('hidden.bs.modal', () => {
        document.querySelectorAll('.action-view').forEach(radio => {
            radio.checked = false;
            radio.dataset.wasChecked = 'false';
            radio.classList.remove('active-radio');
        });
    });

    bindConfirm(els.confirmResolve, 'resolve', els.resolveModal, 'resolve');
    bindConfirm(els.confirmDelete, 'delete', els.deleteModal, 'delete');
    bindConfirm(els.confirmBan, 'ban', els.banModal, 'ban');

    bindModalReset(els.resolveModal, 'resolve');
    bindModalReset(els.deleteModal, 'delete');
    bindModalReset(els.banModal, 'ban');

    /**
     * Allows action radios to be unchecked by clicking the same selected radio again.
     *
     * Native radio buttons normally cannot be deselected by clicking them again.
     * This custom behavior tracks the previous checked state through data-was-checked.
     */
    document.addEventListener('click', (event) => {
        const radio = event.target;

        if (!radio.matches('.action-radio')) return;

        const row = radio.closest('tr');
        const radios = row.querySelectorAll('.action-radio');
        const wasChecked = radio.dataset.wasChecked === 'true';

        radios.forEach((r) => {
            r.dataset.wasChecked = 'false';
            r.classList.remove('active-radio');
        });

        if (wasChecked) {
            radio.checked = false;
            radio.dataset.wasChecked = 'false';

            if (selected.resolve === radio) selected.resolve = null;
            if (selected.delete === radio) selected.delete = null;
            if (selected.ban === radio) selected.ban = null;

            return;
        }

                radio.dataset.wasChecked = 'true';
                radio.classList.add('active-radio');
            });

    /**
     * Initial filter state.
     *
     * Clears old values and disables the search button before the reports are loaded.
     */
        updateReportsSearchButtonState();

        els.filterSearchBy.value = '';
        els.filterReason.value = '';
        els.filterDate.value = '';


    /**
     * Initial page load actions.
     *
     * Loads report data from the backend and activates the Flatpickr date filter.
     */
        fetchReports();
        initializeReportsDatePicker();
    });

