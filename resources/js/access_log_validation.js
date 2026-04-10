
    document.addEventListener('DOMContentLoaded', () => {
    const csvBtn = document.getElementById('downloadAccessLogsCsvBtn');
    const table = document.getElementById('accessLogsTable');

    const searchInput = document.getElementById('accessLogsSearch');
    const searchBtn = document.getElementById('searchAccessLogsBtn');
    const roleFilter = document.getElementById('accessLogsRoleFilter');
    const eventFilter = document.getElementById('accessLogsEventFilter');
    const clearBtn = document.getElementById('clearAccessLogsFiltersBtn');
    const emptyState = document.getElementById('accessLogsEmptyState');

    if (!table) return;

    function normalizeText(value) {
    return String(value ?? '')
    .toLowerCase()
    .trim();
}

    function escapeCSV(value) {
    const text = String(value ?? '').trim().replace(/\s+/g, ' ');
    return `"${text.replace(/"/g, '""')}"`;
}

    function getRows() {
    return Array.from(table.querySelectorAll('tbody tr'));
}

    function getVisibleRows() {
    return getRows().filter((row) => {
    return row.style.display !== 'none' && row.id !== 'accessLogsEmptyState';
});
}

    function updateEmptyState() {
    const emptyRow = document.getElementById('accessLogsEmptyState');
    if (!emptyRow) return;

    const visibleRows = getVisibleRows();
    emptyRow.style.display = visibleRows.length === 0 ? 'table-row' : 'none';
}

    function updateAccessLogsSearchButtonState() {
            if (!searchInput || !searchBtn) return;

            const value = searchInput.value;
            searchBtn.disabled = value.trim() === '';
    }

    function applyAccessLogsFilters() {
    const searchValue = normalizeText(searchInput?.value || '');
    const roleValue = normalizeText(roleFilter?.value || '');
    const eventValue = normalizeText(eventFilter?.value || '');

    getRows().forEach((row) => {
    const cols = row.querySelectorAll('td');
    if (cols.length < 6) return;

    const timestamp = normalizeText(cols[0].textContent);
    const user = normalizeText(cols[1].textContent);
    const role = normalizeText(cols[2].textContent);
    const eventText = normalizeText(cols[3].textContent);
    const ip = normalizeText(cols[4].textContent);
    const comment = normalizeText(cols[5].textContent);

    const matchesSearch =
    searchValue === '' ||
    timestamp.includes(searchValue) ||
    user.includes(searchValue) ||
    role.includes(searchValue) ||
    eventText.includes(searchValue) ||
    ip.includes(searchValue) ||
    comment.includes(searchValue);

    const matchesRole =
    roleValue === '' || role.includes(roleValue);

    const matchesEvent =
    eventValue === '' || eventText.includes(eventValue);

    row.style.display = (matchesSearch && matchesRole && matchesEvent) ? '' : 'none';
});

    updateEmptyState();
}

    function clearAccessLogsFilters() {
    if (searchInput) searchInput.value = '';
    if (roleFilter) roleFilter.value = '';
    if (eventFilter) eventFilter.value = '';

    getRows().forEach((row) => {
    row.style.display = '';
});

    updateAccessLogsSearchButtonState();
    updateEmptyState();
}

    if (searchBtn) {
    searchBtn.addEventListener('click', applyAccessLogsFilters);
}

    if (searchInput) {
            searchInput.addEventListener('input', () => {
                updateAccessLogsSearchButtonState();
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !searchBtn?.disabled) {
                    e.preventDefault();
                    applyAccessLogsFilters();
                }
            });
    }

    if (roleFilter) {
    roleFilter.addEventListener('change', applyAccessLogsFilters);
}

    if (eventFilter) {
    eventFilter.addEventListener('change', applyAccessLogsFilters);
}

    if (clearBtn) {
    clearBtn.addEventListener('click', clearAccessLogsFilters);
}

    if (csvBtn) {
    csvBtn.addEventListener('click', () => {
    const rows = getVisibleRows();

    const csv = [];

    csv.push([
    'Marca de Tiempo',
    'Usuario',
    'Rol',
    'Evento',
    'Dirección IP',
    'Comentario'
    ].map(escapeCSV).join(','));

    rows.forEach((row) => {
    const cols = row.querySelectorAll('td');
    if (cols.length < 6) return;

    csv.push([
    cols[0].textContent,
    cols[1].textContent,
    cols[2].textContent,
    cols[3].textContent,
    cols[4].textContent,
    cols[5].textContent
    ].map(escapeCSV).join(','));
});

    const blob = new Blob(['\uFEFF' + csv.join('\n')], {
    type: 'text/csv;charset=utf-8;'
});

    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');

    link.href = url;
    link.download = 'access_logs.csv';

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    URL.revokeObjectURL(url);
});
}

    updateAccessLogsSearchButtonState();
    updateEmptyState();
});
