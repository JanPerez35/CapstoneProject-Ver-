<x-layout title="Gestión de Inventario - Estadísticas">
    <x-navbar></x-navbar>

    <div class="container py-4">
        {{-- Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí puedes administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal nav --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('inventory_management') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-box"></i>
                Inventario Administrativo
            </a>
            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-card-checklist"></i>
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
                <a href="{{ route('inventory_management.inventory_statistics.export', ['format' => 'csv', 'type' => $type, 'year' => $year, 'month' => $month]) }}"
                   class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold export-btn">
                    <i class="bi bi-download"></i>
                    Exportar a CSV
                </a>

                <a href="{{ route('inventory_management.inventory_statistics.export', ['format' => 'pdf', 'type' => $type, 'year' => $year, 'month' => $month]) }}"
                   class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold export-btn">
                    <i class="bi bi-download"></i>
                    Exportar a PDF
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET"
                      action="{{ route('inventory_management.inventory_statistics') }}"
                      id="reportFilterForm"
                      class="row g-3 align-items-end">

                    <div class="col-md-4">
                        <label for="reportType" class="form-label fw-semibold">Tipo de reporte</label>
                        <select id="reportType" name="type" class="form-select form-select-lg auto-submit">
                            <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="annual"  {{ $type === 'annual'  ? 'selected' : '' }}>Anual</option>
                        </select>
                    </div>

                    <div class="col-md-4 {{ $type === 'annual' ? 'd-none' : '' }}" id="monthFilterWrapper">
                        <label for="reportMonth" class="form-label fw-semibold">Mes</label>
                        <select id="reportMonth" name="month" class="form-select form-select-lg auto-submit">
                            @foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num => $name)
                                <option value="{{ $num }}" {{ $month === $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="reportYear" class="form-label fw-semibold">Año</label>
                        <select id="reportYear" name="year" class="form-select form-select-lg auto-submit">
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $year === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
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
                        <h4 class="fw-bold mb-1">
                            {{ $topItem ? $topItem->description : '—' }}
                        </h4>
                        <p class="mb-0 text-success fw-semibold">
                            {{ $topItem ? $topItem->total . ' pedidos' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2">Total de pedidos</p>
                        <h4 class="fw-bold mb-1">{{ $totalReqs }}</h4>
                        <p class="mb-0 text-muted">En el periodo seleccionado</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <p class="text-muted mb-2">Cantidad de artículos analizados</p>
                        <h4 class="fw-bold mb-1">{{ $totalItems }}</h4>
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
                        <h4 class="fw-bold mb-1">Artículos con más pedidos</h4>
                        <p class="text-muted mb-3">Gráfica comparativa del periodo seleccionado</p>

                        @if($items->isEmpty())
                            <div class="d-flex align-items-center justify-content-center text-muted"
                                 style="height: 380px;">
                                Sin datos para el período seleccionado.
                            </div>
                        @else
                            <div style="height: 380px;">
                                <canvas id="inventoryStatsChart"></canvas>
                            </div>
                        @endif
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
                                    <th>Rango</th>
                                    <th>Objeto</th>
                                    <th class="text-end">Pedidos</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($items->take(3) as $i => $item)
                                    <tr>
                                        <td class="fw-bold">{{ $i + 1 }}</td>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-end fw-semibold">{{ $item->total }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted text-center">
                                            Sin datos para el período seleccionado.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <hr class="my-4">

                        <p class="text-muted small mb-0">
                            Basado en las solicitudes de préstamo registradas en el sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST (ESTILO SUBTLE COMO EL TUYO) --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="downloadToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1">
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

    {{-- CHART --}}
    @if($items->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctx = document.getElementById('inventoryStatsChart').getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($items->pluck('description')),
                        datasets: [{
                            label: 'Cantidad de pedidos',
                            data: @json($items->pluck('total')),
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
            });
        </script>
    @endif

    {{-- FILTER SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('reportFilterForm');
            const reportType = document.getElementById('reportType');
            const monthWrapper = document.getElementById('monthFilterWrapper');

            document.querySelectorAll('.auto-submit').forEach(function (el) {
                el.addEventListener('change', function () { form.submit(); });
            });

            reportType.addEventListener('change', function () {
                if (this.value === 'annual') {
                    monthWrapper.classList.add('d-none');
                } else {
                    monthWrapper.classList.remove('d-none');
                }
            });
        });
    </script>

    {{-- TOAST SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastEl = document.getElementById('downloadToast');

            if (!toastEl || typeof bootstrap === 'undefined') return;

            const toast = new bootstrap.Toast(toastEl, { delay: 2500 });

            document.querySelectorAll('.export-btn').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();

                    const url = this.getAttribute('href');
                    toast.show();

                    setTimeout(function () {
                        window.location.href = url;
                    }, 500);
                });
            });
        });
    </script>

</x-layout>
