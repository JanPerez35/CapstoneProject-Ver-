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
    const table = document.getElementById('accessLogsTable');
    const searchInput = document.getElementById('accessLogsSearch');
    const searchBtn = document.getElementById('searchAccessLogsBtn');
    const emptyState = document.getElementById('accessLogsEmptyState');

    if (!table) return;

    const dateFilter = document.getElementById('accessLogsDateFilter');
    const dateFilterIcon = document.getElementById('accessLogsDateFilterIcon');
    const filterForm = document.getElementById('accessLogsFilterForm');

    /**
     * Initializes the access logs date filter using Flatpickr.
     *
     * - Replaces the native HTML date input with the shared borrows-style calendar component
     * - Displays the calendar in Spanish
     * - Keeps Laravel-compatible values in YYYY-MM-DD format
     * - Displays user-friendly dates using  dd-MMM-yyyy format
     * - Automatically submits the filter form when a date is selected
     * - Opens the calendar when the custom calendar icon is clicked
     */
    function initializeAccessLogsDatePicker() {
        if (!dateFilter) return;

        flatpickr(dateFilter, {
            locale: Spanish,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'j F Y',
            allowInput: false,
            disableMobile: true,
            onChange: function () {
                filterForm?.submit();
            }
        });

        if (dateFilter._flatpickr?.altInput) {
            dateFilter._flatpickr.altInput.placeholder = 'Día Mes Año';
            dateFilter._flatpickr.altInput.classList.add('date-picker-input');
        }

        dateFilterIcon?.addEventListener('click', () => {
            dateFilter._flatpickr?.open();
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
        const visibleRows = Array.from(table.querySelectorAll('tbody tr')).filter(
            (row) => row.style.display !== 'none' && row.id !== 'accessLogsEmptyState'
        );
        emptyState.style.display = visibleRows.length === 0 ? 'table-row' : 'none';
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

    updateSearchButtonState();
    updateEmptyState();
    initializeAccessLogsDatePicker();
});
