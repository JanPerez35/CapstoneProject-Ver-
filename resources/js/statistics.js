/**
 * Initializes inventory statistics page behavior after DOM is loaded.
 *
 * Responsibilities:
 * - preserves scroll position across reloads and exports
 * - auto-submits report filters (type, month, year)
 * - toggles month filter visibility based on report type
 * - handles export button behavior with toast feedback
 * - renders Chart.js bar chart using data from Blade
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Session storage key used to persist vertical scroll position
     * between page reloads or navigation events.
     */
    const SCROLL_KEY = 'inventoryStatisticsScrollY';

    /**
    * DOM element references
    */
    const form = document.getElementById('reportFilterForm');
    const reportType = document.getElementById('reportType');
    const monthWrapper = document.getElementById('monthFilterWrapper');
    const toastEl = document.getElementById('downloadToast');
    const chartCanvas = document.getElementById('inventoryStatsChart');

    /**
     * Saves the current vertical scroll position into sessionStorage.
     */
    function saveScroll() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
    }

    /**
     * Removes any stored scroll position from sessionStorage.
     */
    function clearScroll() {
        sessionStorage.removeItem(SCROLL_KEY);
    }

    /**
     * Restores the scroll position after page load.
     *
     * Uses a retry mechanism because layout rendering may delay
     * correct positioning (especially with charts or dynamic content).
     * It attempts to reload 20 times because page might not load immediately.
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
     * Displays a Bootstrap toast notification if available.
     *
     * @param {HTMLElement|null} toastElement - Toast element to display.
     */
    function showToast(toastElement) {
        if (!toastElement || typeof bootstrap === 'undefined') return;
        const toast = bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 2500 });
        toast.show();
    }

    /**
     * Enables automatic form submission when filter values change.
     *
     * Also:
     * - saves scroll position before submission
     * - toggles month filter visibility based on report type
     */
    function setupAutoSubmitFilters() {
        if (!form) return;

        document.querySelectorAll('.auto-submit').forEach(function (element) {
            element.addEventListener('change', function () {
                saveScroll();
                form.submit();
            });
        });

        /**
         * Toggles visibility of the month filter:
         * - hidden when report type is "annual"
         * - visible when report type is "monthly"
         */
        if (reportType && monthWrapper) {
            reportType.addEventListener('change', function () {
                if (this.value === 'annual') {
                    monthWrapper.classList.add('d-none');
                } else {
                    monthWrapper.classList.remove('d-none');
                }
            });
        }
    }

    /**
     * Handles export button clicks (CSV and PDF).
     *
     * Behavior:
     * - prevents immediate navigation
     * - saves scroll position
     * - shows download toast
     * - then redirects after a short delay
     */
    function setupExportButtons() {
        document.querySelectorAll('.export-btn').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();

                const url = this.getAttribute('href');
                if (!url) return;

                saveScroll();
                showToast(toastEl);

                setTimeout(function () {
                    window.location.href = url;
                }, 350);
            });
        });
    }

    /**
     * Initializes Chart.js bar chart using data provided from Blade.
     *
     * Requirements:
     * - Chart.js must be loaded globally
     * - window.inventoryStatsChartData must exist
     *
     * Data format:
     * {
     *   labels: [item names],
     *   values: [request counts]
     * }
     */
    function setupChart() {
        if (!chartCanvas || typeof Chart === 'undefined' || !window.inventoryStatsChartData) return;

        const ctx = chartCanvas.getContext('2d');
        const chartData = window.inventoryStatsChartData;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: [{
                    data: chartData.values,
                    backgroundColor: '#198754',
                    borderColor: '#146c43',
                    borderWidth: 1,
                    borderRadius: 10
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { displayColors: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    }

    /**
  * Initial setup
  *
  * Initializes filters, export behavior, chart rendering,
  * and restores scroll position after full page load.
  */
    setupAutoSubmitFilters();
    setupExportButtons();
    setupChart();

    window.addEventListener('load', restoreScroll);
});
