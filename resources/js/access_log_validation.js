/**
 * Flatpickr imports
 *
 * Responsibilities:
 * - Provides the custom calendar component used by the access logs date filter
 * - Replaces the browser-native date input for consistent UI behavior
 * - Enables Spanish localization support
 * - Loads Flatpickr default styles
 */
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.min.css";
/**
 * Access Logs Frontend Controller
 *
 * Handles client-side interactions for the Access Logs view.
 *
 * Responsibilities:
 * - Export visible access logs to CSV format
 * - Manage search input behavior (enable/disable button, submit on Enter)
 * - Handle manual search submission
 * - Control empty state visibility when no rows are displayed
 *
 * Features:
 * - CSV export only includes currently visible (filtered) rows
 * - Proper CSV escaping for special characters and quotes
 * - Dynamic UI feedback for search input, date filtering, and empty table state
 *
 * Dependencies:
 * - HTML elements with IDs:
 *   - downloadAccessLogsCsvBtn
 *   - accessLogsTable
 *   - accessLogsSearch
 *   - searchAccessLogsBtn
 *   - accessLogsEmptyState
 *   - accessLogsDateFilter
 *
 * @event DOMContentLoaded Initializes all event listeners and UI state
 */
document.addEventListener('DOMContentLoaded', () => {
    const csvBtn = document.getElementById('downloadAccessLogsCsvBtn');
    const table = document.getElementById('accessLogsTable');
    const searchInput = document.getElementById('accessLogsSearch');
    const searchBtn = document.getElementById('searchAccessLogsBtn');
    const emptyState = document.getElementById('accessLogsEmptyState');

    if (!table) return;

    const downloadToastEl = document.getElementById('downloadToast');
    const downloadToast = downloadToastEl
        ? bootstrap.Toast.getOrCreateInstance(downloadToastEl, { delay: 3000 })
        : null;

    const dateFilter = document.getElementById('accessLogsDateFilter');
    const dateFilterIcon = document.getElementById('accessLogsDateFilterIcon');
    const filterForm = document.getElementById('accessLogsFilterForm');

    /**
     * Initializes the access logs date filter using Flatpickr.
     *
     * - Replaces the native HTML date input with the shared borrows-style calendar component
     * - Displays the calendar in Spanish
     * - Keeps Laravel-compatible values in YYYY-MM-DD format
     * - Displays user-friendly dates using day-month-year format
     * - Automatically submits the filter form when a date is selected
     * - Opens the calendar when the custom calendar icon is clicked
     */
    function initializeAccessLogsDatePicker() {
        if (!dateFilter) return;

        flatpickr(dateFilter, {
            locale: Spanish,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j-F-Y',
            allowInput: false,
            disableMobile: true,
            onChange: function () {
                filterForm?.submit();
            }
        });

        if (dateFilter._flatpickr?.altInput) {
            dateFilter._flatpickr.altInput.placeholder = 'dd-mm-aaaa';
            dateFilter._flatpickr.altInput.classList.add('date-picker-input');
        }

        dateFilterIcon?.addEventListener('click', () => {
            dateFilter._flatpickr?.open();
        });
    }

    /**
     * Escapes a value for safe inclusion in a CSV file.
     *
     * - Converts undefined to empty string
     * - Trims extra whitespace
     * - Replaces multiple spaces with a single space
     * - Escapes double quotes by duplicating them
     * - Wraps the result in double quotes
     *
     * @param {any} value - The value to escape
     * @returns {string} Properly formatted CSV-safe string
     */
    function escapeCSV(value) {
        const text = String(value ?? '').trim().replace(/\s+/g, ' ');
        return `"${text.replace(/"/g, '""')}"`;
    }

    /**
     * Retrieves all currently visible table rows.
     *
     * Excludes:
     * - If rows are hidden via CSS it display (none)
     * - The empty state row
     *
     * @returns {HTMLTableRowElement[]} Array of visible table rows
     */
    function getVisibleRows() {
        return Array.from(table.querySelectorAll('tbody tr')).filter((row) => {
            return row.style.display !== 'none' && row.id !== 'accessLogsEmptyState';
        });
    }

    /**
     * Toggles the visibility of the empty state row.
     *
     * - Shows the empty state when no visible rows exist,
     * otherwise hides it.
     *
     * @returns {void}
     */
    function updateEmptyState() {
        if (!emptyState) return;
        emptyState.style.display = getVisibleRows().length === 0 ? 'table-row' : 'none';
    }

    /**
     * Enables or disables the search button based on input content.
     *
     * - Disabled when input is empty
     * - Enabled when input contains text
     *
     * @returns {void}
     */
    function updateSearchButtonState() {
        if (!searchInput || !searchBtn) return;
        searchBtn.disabled = searchInput.value.trim() === '';
    }

    /**
     * Handles search input interactions:
     *
     * - Updates button state on input
     * - Submits form when Enter key is pressed (if enabled)
     *
     * @event input
     * @event keydown
     */
    if (searchInput) {
        searchInput.addEventListener('input', updateSearchButtonState);

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !searchBtn?.disabled) {
                e.preventDefault();
                searchInput.closest('form').submit();
            }
        });
    }

    /**
    * Submits the access logs search form when clicked.
    *
    * @event click
    */
    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            searchInput.closest('form').submit();
        });
    }

    /**
     * Handles CSV export of access logs.
     *
     * Process:
     * - Collects only visible table rows
     * - Extracts column data from each row
     * - Escapes values for CSV compliance
     * - Prepends UTF-8 BOM for Excel compatibility
     * - Generates and triggers file download
     *
     * Output:
     * - File name: access_logs.csv
     * - Encoding: UTF-8
     *
     * @event click
     */
    if (csvBtn) {
        csvBtn.addEventListener('click', () => {
            downloadToast?.show();
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
    initializeAccessLogsDatePicker();
});
