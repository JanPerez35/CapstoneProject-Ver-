import * as bootstrap from 'bootstrap';
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

    async function fetchReports() {
    const res = await fetch('/reports/data');
    const data = await res.json();

    renderReportsFromBackend(data);
}

    function renderReportsFromBackend(reports) {
    const tbody = document.querySelector('#reportsTable tbody');
    tbody.innerHTML = '';

    reports.forEach(report => {

        console.log('REPORT COMPLETO:', report);
        console.log('POST ID:', report.post_id);

        const row = `
            <tr data-report-id="${report.id}"
                data-post="${encodeURIComponent(JSON.stringify(report.post || {}))}"
                data-seller-id="${report.reported_user_id}"
                data-post-id="${report.post_id}"
                >
                <td>${report.reporter?.name || ''}</td>
                <td>${report.reported_user?.name || ''}</td>
                <td>${report.report_reason}</td>
                <td>${new Date(report.created_at).toLocaleDateString()}</td>
                <td>${report.description}</td>

                <td class="text-center"><input class="form-check-input action-radio action-view" type="radio" name="action_${report.id}"></td>
                <td class="text-center"><input class="form-check-input action-radio action-resolve" type="radio" name="action_${report.id}"></td>
                <td class="text-center"><input class="form-check-input action-radio action-delete-post" type="radio" name="action_${report.id}"></td>
                <td class="text-center"><input class="form-check-input action-radio action-block-user" type="radio" name="action_${report.id}"></td>
            </tr>
        `;

        tbody.insertAdjacentHTML('beforeend', row);
    });

    bindAction('.action-resolve', els.resolveModal, 'resolve');
    bindAction('.action-delete-post', els.deleteModal, 'delete');
    bindAction('.action-block-user', els.banModal, 'ban');

    renderReports();
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
    searchReportsBtn: $('searchReportsBtn'),
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
};

    const toasts = Object.fromEntries(
    Object.entries(toastIds).map(([key, id]) => [
            key,
            bootstrap.Toast.getOrCreateInstance($(id), { delay: 3000 })
       ])
    );

    let selected = {
    resolve: null,
    delete: null,
    ban: null,
};

    const normalize = (text) => (text || '').toLowerCase().trim();

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

    function updateReportsSearchButtonState() {
        if (!els.filterSearchBy || !els.searchReportsBtn) return;

        const value = els.filterSearchBy.value;
        els.searchReportsBtn.disabled = value.trim() === '';
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

    clearReportsFiltersBtn?.addEventListener('click', () => {
    els.filterSearchBy.value = '';
    els.filterReason.value = '';
    els.filterDate.value = '';
    updateReportsSearchButtonState();
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


    function bindModalReset(modalEl, key) {
    modalEl?.addEventListener('hidden.bs.modal', () => {
    if (selected[key]) {
    selected[key].checked = false;
    selected[key] = null;
}
});
}

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

                // también marcar reporte como resuelto
                await fetch(`/reports/${reportId}/resolve`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf }
                });
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

            // UI
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
    els.filterSearchBy?.addEventListener('input', () => {
            updateReportsSearchButtonState();
        });

    els.filterSearchBy?.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();

                if (!els.searchReportsBtn?.disabled) {
                    applyFilters();
                }
            }
        });

    els.searchReportsBtn?.addEventListener('click', () => {
            applyFilters();
        });

    [els.filterReason, els.filterDate].forEach((el) => {
    el.addEventListener('input', applyFilters);
    el.addEventListener('change', applyFilters);
});

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

const modalEl = document.getElementById('postDetailsModal');

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

    updateReportsSearchButtonState();

    els.filterSearchBy.value = '';
    els.filterReason.value = '';
    els.filterDate.value = '';

    renderReports();
    fetchReports();
});

