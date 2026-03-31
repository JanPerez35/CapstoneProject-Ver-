<x-layout title="Gestión de Inventario - Estadísticas">
    <x-navbar></x-navbar>

    <div class="container py-4">
        {{-- Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí podrás administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal nav --}}
        <div class="d-flex flex-wrap gap-2 mb-4">

            <a href="{{ route('inventory_management') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                Inventario Administrativo
            </a>

            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                Préstamos
            </a>

            <a href="{{ route('inventory_management.inventory_statistics') }}"
               class="btn btn-success px-4 fw-semibold">
                <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
            </a>


        </div>

        {{-- Page title + export buttons --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Reporte de Estadísticas</h2>
                <p class="text-muted mb-0">
                    Visualiza los artículos más solicitados y descarga reportes del inventario.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-3">
                <button type="button" id="downloadCsvBtn" class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                    <i class="bi bi-download"></i>
                    Exportar a CSV
                </button>

                <button type="button" id="downloadPdfBtn" class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                    <i class="bi bi-download"></i>
                    Exportar a PDF
                </button>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form id="reportFilterForm" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="reportType" class="form-label fw-semibold">Tipo de reporte</label>
                        <select id="reportType" class="form-select form-select-lg border-2 border-dark">
                            <option value="monthly" selected>Mensual</option>
                            <option value="annual">Anual</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="monthFilterWrapper">
                        <label for="reportMonth" class="form-label fw-semibold">Mes</label>
                        <select id="reportMonth" class="form-select form-select-lg border-2 border-dark">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="reportYear" class="form-label fw-semibold">Año</label>
                        <select id="reportYear" class="form-select form-select-lg border-2 border-dark">
                            <option value="2023">2023</option>
                            <option value="2024" selected>2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2">Objeto con más pedidos</p>
                        <h4 class="fw-bold mb-1" id="topItemName">Balón de Baloncesto</h4>
                        <p class="mb-0 text-success fw-semibold" id="topItemOrders">34 pedidos</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2">Total de pedidos</p>
                        <h4 class="fw-bold mb-1" id="totalOrders">86</h4>
                        <p class="mb-0 text-muted">En el periodo seleccionado</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2">Cantidad de artículos analizados</p>
                        <h4 class="fw-bold mb-1" id="totalItemsAnalyzed">5</h4>
                        <p class="mb-0 text-muted">Resumen del inventario más solicitado</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart + Top 3 table --}}
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="fw-bold mb-1">Artículos con más pedidos</h4>
                                <p class="text-muted mb-0">Gráfica comparativa del periodo seleccionado</p>
                            </div>
                        </div>

                        <div style="height: 380px;">
                            <canvas id="inventoryStatsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">Top 3 artículos</h4>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Objeto</th>
                                    <th class="text-end">Pedidos</th>
                                </tr>
                                </thead>
                                <tbody id="topItemsTableBody">
                                <tr>
                                    <td class="fw-bold">1</td>
                                    <td>Balón de Baloncesto</td>
                                    <td class="text-end fw-semibold">34</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">2</td>
                                    <td>Raqueta de Tenis</td>
                                    <td class="text-end fw-semibold">26</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">3</td>
                                    <td>Balón de Fútbol</td>
                                    <td class="text-end fw-semibold">18</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <p class="text-muted small mb-0">
                            Este resumen es visual y demostrativo para el panel administrativo.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Download Toast --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="downloadToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">

            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Tu documento se descargará en unos instantes.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const reportType = document.getElementById('reportType');
            const reportMonth = document.getElementById('reportMonth');
            const reportYear = document.getElementById('reportYear');
            const monthFilterWrapper = document.getElementById('monthFilterWrapper');

            const topItemName = document.getElementById('topItemName');
            const topItemOrders = document.getElementById('topItemOrders');
            const totalOrders = document.getElementById('totalOrders');
            const totalItemsAnalyzed = document.getElementById('totalItemsAnalyzed');
            const topItemsTableBody = document.getElementById('topItemsTableBody');

            const downloadCsvBtn = document.getElementById('downloadCsvBtn');
            const downloadPdfBtn = document.getElementById('downloadPdfBtn');
            const downloadToastEl = document.getElementById('downloadToast');

            const monthlyData = {
                labels: ['Balón de Baloncesto', 'Raqueta de Tenis', 'Balón de Fútbol', 'Mancuernas', 'Conos'],
                values: [34, 26, 18, 12, 8]
            };

            const annualData = {
                labels: ['Balón de Baloncesto', 'Balón de Fútbol', 'Raqueta de Tenis', 'Mancuernas', 'Bandas Elásticas'],
                values: [240, 198, 176, 121, 97]
            };

            const chartCtx = document.getElementById('inventoryStatsChart').getContext('2d');

            const statsChart = new Chart(chartCtx, {
                type: 'bar',
                data: {
                    labels: monthlyData.labels,
                    datasets: [{
                        label: 'Cantidad de pedidos',
                        data: monthlyData.values,
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
                        legend: {
                            display: false
                        },
                        tooltip: {
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 5
                            }
                        }
                    }
                }
            });

            function getCurrentDataSet() {
                return reportType.value === 'annual' ? annualData : monthlyData;
            }

            function renderTopTable(labels, values) {
                const topThree = labels.map((label, index) => ({
                    label,
                    value: values[index]
                }))
                    .sort((a, b) => b.value - a.value)
                    .slice(0, 3);

                topItemsTableBody.innerHTML = topThree.map((item, index) => `
                    <tr>
                        <td class="fw-bold">${index + 1}</td>
                        <td>${item.label}</td>
                        <td class="text-end fw-semibold">${item.value}</td>
                    </tr>
                `).join('');
            }

            function updateSummary(labels, values) {
                const ranked = labels.map((label, index) => ({
                    label,
                    value: values[index]
                }))
                    .sort((a, b) => b.value - a.value);

                const top = ranked[0];
                const total = values.reduce((sum, value) => sum + value, 0);

                topItemName.textContent = top.label;
                topItemOrders.textContent = `${top.value} pedidos`;
                totalOrders.textContent = total;
                totalItemsAnalyzed.textContent = values.length;
            }

            function updateChartAndStats() {
                const dataSet = getCurrentDataSet();

                statsChart.data.labels = dataSet.labels;
                statsChart.data.datasets[0].data = dataSet.values;
                statsChart.update();

                renderTopTable(dataSet.labels, dataSet.values);
                updateSummary(dataSet.labels, dataSet.values);

                if (reportType.value === 'annual') {
                    monthFilterWrapper.classList.add('d-none');
                } else {
                    monthFilterWrapper.classList.remove('d-none');
                }
            }

            function triggerFakeDownload(type) {
                const toast = window.bootstrap.Toast.getOrCreateInstance(downloadToastEl);
                toast.show();

                const selectedType = reportType.value === 'annual' ? 'anual' : 'mensual';
                const selectedYear = reportYear.value;
                const selectedMonth = reportMonth.options[reportMonth.selectedIndex]?.text || '';
                const dataSet = getCurrentDataSet();

                let content = '';

                if (type === 'csv') {
                    content = 'Objeto,Pedidos\n' + dataSet.labels.map((label, index) => `${label},${dataSet.values[index]}`).join('\n');
                } else {
                    content =
                        `REPORTE DE INVENTARIO
Tipo: ${selectedType}
${selectedType === 'mensual' ? 'Mes: ' + selectedMonth : ''}
Año: ${selectedYear}

TOP ARTÍCULOS:
${dataSet.labels.map((label, index) => `${index + 1}. ${label} - ${dataSet.values[index]} pedidos`).join('\n')}`;
                }

                const blob = new Blob([content], { type: type === 'csv' ? 'text/csv;charset=utf-8;' : 'application/pdf' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);

                link.href = url;
                link.download = type === 'csv'
                    ? `reporte_inventario_${selectedType}_${selectedYear}.csv`
                    : `reporte_inventario_${selectedType}_${selectedYear}.pdf`;

                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            }

            reportType.addEventListener('change', updateChartAndStats);
            reportMonth.addEventListener('change', updateChartAndStats);
            reportYear.addEventListener('change', updateChartAndStats);

            downloadCsvBtn.addEventListener('click', function () {
                triggerFakeDownload('csv');
            });

            downloadPdfBtn.addEventListener('click', function () {
                triggerFakeDownload('pdf');
            });

            updateChartAndStats();
        });
    </script>
</x-layout>
