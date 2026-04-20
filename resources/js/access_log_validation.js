
document.addEventListener('DOMContentLoaded', () => {
    const csvBtn = document.getElementById('downloadAccessLogsCsvBtn');
    const table = document.getElementById('accessLogsTable');
    const searchInput = document.getElementById('accessLogsSearch');
    const searchBtn = document.getElementById('searchAccessLogsBtn');
    const emptyState = document.getElementById('accessLogsEmptyState');

    if (!table) return;

    function escapeCSV(value) {
        const text = String(value ?? '').trim().replace(/\s+/g, ' ');
        return `"${text.replace(/"/g, '""')}"`;
    }

    function getVisibleRows() {
        return Array.from(table.querySelectorAll('tbody tr')).filter((row) => {
            return row.style.display !== 'none' && row.id !== 'accessLogsEmptyState';
        });
    }

    function updateEmptyState() {
        if (!emptyState) return;
        emptyState.style.display = getVisibleRows().length === 0 ? 'table-row' : 'none';
    }

    function updateSearchButtonState() {
        if (!searchInput || !searchBtn) return;
        searchBtn.disabled = searchInput.value.trim() === '';
    }

    if (searchInput) {
        searchInput.addEventListener('input', updateSearchButtonState);

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !searchBtn?.disabled) {
                e.preventDefault();
                searchInput.closest('form').submit();
            }
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            searchInput.closest('form').submit();
        });
    }

    if (csvBtn) {
        csvBtn.addEventListener('click', () => {
            const rows = getVisibleRows();
            const csv = [];

            csv.push([
                'Marca de Tiempo', 'Usuario', 'Rol', 'Evento', 'Dirección IP', 'Comentario'
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

    updateSearchButtonState();
    updateEmptyState();
});
