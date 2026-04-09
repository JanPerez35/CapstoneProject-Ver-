    const REPORTS_PER_PAGE = 18;
    let currentReportsPage = 1;

    document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);
    const rows = () => document.querySelectorAll('#reportsTable tbody tr');
    const resolvedReports = new Set();

    const allowedSearchCharRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ.\s]$/;

    function sanitizeSearchValue(value) {
    return [...value]
    .filter((char) => allowedSearchCharRegex.test(char))
    .join('');
}



    function formatDateForDisplay(dateValue) {
    if (!dateValue) return '';
    const [year, month, day] = dateValue.split('-');
    return `${month}/${day}/${year}`;
}

    const els = {
    filterReason: $('filterReason'),
    filterSearchBy: $('filterSearchBy'),
    filterDate: $('filterDate'),

    resolveModal: $('resolveQuerellaModal'),
    deleteModal: $('deletePostModal'),
    banModal: $('bloquearUserModal'),

    confirmResolve: $('confirmResolveQuerella'),
    confirmDelete: $('confirmDeletePost'),
    confirmBan: $('confirmBloquearUser'),

    reportsTable: $('reportsTable'),
    emptyState: $('reportsEmptyState'),
    reportsPagination: $('querellasPagination'),
};

    const clearReportsFiltersBtn = document.getElementById('clearReportsFilters');

    const toastIds = {
    resolve: 'resolveToast',
    delete: 'deleteToast',
    ban: 'banToast',
    view: 'viewToast',
};

    const toasts = Object.fromEntries(
    Object.entries(toastIds).map(([key, id]) => [
    key,
    bootstrap.Toast.getOrCreateInstance($(id), { delay: key === 'view' ? 2500 : 3000 })
    ])
    );

    let selected = {
    resolve: null,
    delete: null,
    ban: null,
};

    const normalize = (text) => text.toLowerCase().trim();


    function markResolved(row) {
    if (!row) return;
    const reportId = row.dataset.reportId || '';
    if (reportId) resolvedReports.add(reportId);
    row.remove();
    renderReports();
}

    function applyFilters() {
    currentReportsPage = 1;
    renderReports();
}

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

        function getFilteredRows() {
            const filters = {
                reason: normalize(els.filterReason.value),
                user: normalize(els.filterSearchBy.value),
                date: formatDateForDisplay(els.filterDate.value)
            };

            return [...rows()].filter((row) => {
                const reportId = row.dataset.reportId || '';
                if (reportId && resolvedReports.has(reportId)) {
                    return false;
                }

                const reportedBy = normalize(row.cells[0].textContent);
                const seller = normalize(row.cells[1].textContent);
                const reason = normalize(row.cells[2].textContent);
                const date = row.cells[3].textContent.trim();

                return (
                    (!filters.reason || reason === filters.reason) &&
                    (
                        !filters.user ||
                        reportedBy.includes(filters.user) ||
                        seller.includes(filters.user)
                    ) &&
                    (!filters.date || date === filters.date)
                );
            });
        }

    clearReportsFiltersBtn?.addEventListener('click', () => {
    els.filterSearchBy.value = '';
    els.filterReason.value = '';
    els.filterDate.value = '';
    applyFilters();
});

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


    function bindAction(selector, modalEl, key) {
    document.querySelectorAll(selector).forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
    if (!this.checked) return;
    selected[key] = this;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});
});
}

    document.querySelectorAll('.action-view').forEach((radio) => {
            radio.addEventListener('change', function () {
                if (!this.checked) return;
                toasts.view?.show();
            });
        });

        bindAction('.action-resolve', els.resolveModal, 'resolve');
        bindAction('.action-delete-post', els.deleteModal, 'delete');
        bindAction('.action-block-user', els.banModal, 'ban');

    function bindModalReset(modalEl, key) {
    modalEl?.addEventListener('hidden.bs.modal', () => {
    if (selected[key]) {
    selected[key].checked = false;
    selected[key] = null;
}
});
}

    function bindConfirm(button, key, modalEl, toastKey) {
    button?.addEventListener('click', () => {
    if (selected[key]) {
    markResolved(selected[key].closest('tr'));
    selected[key] = null;
    toasts[toastKey]?.show();
}
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
});
}

    bindNameInput(els.filterSearchBy);
    els.filterSearchBy?.addEventListener('input',applyFilters);

    [els.filterReason, els.filterDate].forEach((el) => {
    el.addEventListener('input', applyFilters);
    el.addEventListener('change', applyFilters);
});


    document.querySelectorAll('.action-view').forEach((checkbox) => {
    checkbox.addEventListener('change', function () {
    if (this.checked) {
    toasts.view?.show();
}
});
});

    bindAction('.action-resolve', els.resolveModal, 'resolve');
    bindAction('.action-delete-post', els.deleteModal, 'delete');
    bindAction('.action-ban-user', els.banModal, 'ban');

    bindConfirm(els.confirmResolve, 'resolve', els.resolveModal, 'resolve');
    bindConfirm(els.confirmDelete, 'delete', els.deleteModal, 'delete');
    bindConfirm(els.confirmBan, 'ban', els.banModal, 'ban');

    bindModalReset(els.resolveModal, 'resolve');
    bindModalReset(els.deleteModal, 'delete');
    bindModalReset(els.banModal, 'ban');

    renderReports();
});
