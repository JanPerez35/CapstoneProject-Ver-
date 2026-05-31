<x-layout title="Gestión de Inventario - Estadísticas">
    <x-navbar></x-navbar>

    {{-- Load JS for report filters, chart rendering, and download toast behavior --}}
    @vite(['resources/js/statistics.js'])

    <div class="container py-4">

        {{-- Page header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Inventario</h1>
            <p>
                Aquí puedes administrar el inventario de equipo deportivo del departamento de Kinesiología.
            </p>
        </div>

        {{-- Internal navigation between inventory sections --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            {{-- Inventory administration --}}
            <a href="{{ route('inventory_management') }}"
               class="btn btn-outline-success px-4 fw-semibold"
               data-bs-toggle="tooltip"
               data-bs-placement="top"
               data-bs-custom-class="custom-tooltip"
               data-bs-title="Gestiona el equipo disponible (crear, editar, eliminar)">
                <i class="bi bi-box"></i>
                Inventario Administrativo
            </a>

            {{-- Borrows --}}
            <a href="{{ route('inventory_management.borrows') }}"
               class="btn btn-outline-success px-4 fw-semibold"
               data-bs-toggle="tooltip"
               data-bs-placement="bottom"
               data-bs-custom-class="custom-tooltip"
               data-bs-title="Aprueba solicitudes y maneja préstamos activos">
                <i class="bi bi-card-checklist"></i> Préstamos
            </a>

            {{-- Statistics --}}
            <a href="{{ route('inventory_management.inventory_statistics') }}"
               class="btn btn-success px-4 fw-semibold"
               data-bs-toggle="tooltip"
               data-bs-placement="bottom"
               data-bs-custom-class="custom-tooltip"
               data-bs-title="Visualiza informes y descarga estadísticas del inventario">
                <i class="bi bi-graph-up-arrow me-1"></i> Estadísticas
            </a>
        </div>

        {{-- Page title and export actions --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Informe de Estadísticas</h2>
                <p class="text-muted mb-0">
                    Visualiza los artículos más solicitados y descarga informes del inventario.
                </p>
            </div>

            {{-- Export buttons keep current filters (format, type, year, and month) --}}
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

        {{-- Report filters, they allow the user to pick data from specific periods of time --}}
        <div class="card border-dark border-2 rounded-4 mb-4">
            <div class="card-body p-3">
                {{-- Filter form auto-submits through JS when selections change and reloads page --}}
                <form method="GET"
                      action="{{ route('inventory_management.inventory_statistics') }}"
                      id="reportFilterForm"
                      class="row g-3 align-items-end">

                    {{-- Report type selector (monthly or annual) --}}
                    <div class="col-md-4">
                        <label for="reportType" class="form-label fw-semibold">Tipo de informe</label>
                        <select id="reportType" name="type" class="form-select form-select-md auto-submit border-2 border-dark">
                            <option value="monthly" {{ $type === 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="annual"  {{ $type === 'annual'  ? 'selected' : '' }}>Anual</option>
                        </select>
                    </div>

                    {{-- Month selector is hidden when annual report type is selected --}}
                    <div class="col-md-4 {{ $type === 'annual' ? 'd-none' : '' }}" id="monthFilterWrapper">
                        <label for="reportMonth" class="form-label fw-semibold">Mes</label>
                        <select id="reportMonth" name="month" class="form-select form-select-md auto-submit border-2 border-dark">
                            @foreach([1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'] as $num => $name)
                                <option value="{{ $num }}" {{ $month === $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Year selector, the selector is dynamic. Meaning that if data from 2027 gets added it will appear on the selector --}}
                    <div class="col-md-4">
                        <label for="reportYear" class="form-label fw-semibold">Año</label>
                        <select id="reportYear" name="year" class="form-select form-select-md auto-submit border-2 border-dark">
                            @foreach($availableYears as $yr)
                                <option value="{{ $yr }}" {{ $year === $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>

                </form>
            </div>
        </div>

        {{-- Summary cards showing key statistics for the selected period --}}
        <div class="row g-3 mb-4">

            {{-- Most requested item --}}
            <div class="col-sm-4">
                <div class="card border-dark border-2 shadow-sm rounded-4 h-100">
                    <div class="card-body py-3 px-3">
                        <p class="text-muted mb-2">Equipo con más pedidos</p>
                        <h4 class="fw-bold mb-1">
                            {{ $topItem ? $topItem->description : '—' }}
                        </h4>
                        <p class="mb-0 text-success fw-semibold">
                            {{ $topItem ? $topItem->total . ' pedidos' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Total number of requests in selected period --}}
            <div class="col-md-4">
                <div class="card border-dark border-2 rounded-4 h-100">
                    <div class="card-body py-3 px-3">
                        <p class="text-muted mb-2">Total de pedidos</p>
                        <h4 class="fw-bold mb-1">{{ $totalReqs }}</h4>
                        <p class="mb-0 text-muted">En el periodo seleccionado</p>
                    </div>
                </div>
            </div>

            {{-- Total number of analyzed items --}}
            <div class="col-md-4">
                <div class="card border-dark border-2 rounded-4 h-100">
                    <div class="card-body py-3 px-3">
                        <p class="text-muted mb-2">Cantidad de equipos analizados</p>
                        <h4 class="fw-bold mb-1">{{ $totalItems }}</h4>
                        <p class="mb-0 text-muted">Resumen del inventario más solicitado</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main statistics section with chart and ranking table --}}
        <div class="row g-4">

            {{-- Left side: chart showing most requested items, limited to the top 5 --}}
            <div class="col-md-8">
                <div class="card border-dark border-2 rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-1">Equipos con más pedidos</h4>
                        <p class="text-muted mb-3">Gráfica comparativa del periodo seleccionado</p>

                        {{-- Show empty chart state if no data exists --}}
                        @if($items->isEmpty())
                            <div class="d-flex align-items-center justify-content-center text-muted"
                                 style="height: 380px;">
                                Sin datos para el período seleccionado.
                            </div>
                        @else
                            {{-- Chart canvas populated by statistics.js --}}
                            <div style="height: 380px;">
                                <canvas id="inventoryStatsChart"></canvas>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right side with top 3 table summary --}}
            <div class="col-md-4">
                <div class="card border-2 border-dark rounded-4 h-100">
                    <div class="card-body p-4">
                        <h4 class="fw-bold mb-3">Top 3 equipos pedidos</h4>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                <tr>
                                    <th>Rango</th>
                                    <th>Equipo</th>
                                    <th class="text-end">Pedidos</th>
                                </tr>
                                </thead>
                                <tbody>
                                {{-- Show only the top 3 ranked items --}}
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



                        {{-- Data source note clarifying that they may not be 100% validated --}}
                        <p class="text-muted small mb-0">
                            Basado en las solicitudes de préstamo registradas en el sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Download feedback toast shown when export buttons are used --}}
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3"
         style="z-index: 2000; margin-top: 18vh;">
        <div id="downloadToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: fit-content; max-width: min(520px, calc(100vw - 2rem));">
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

    {{-- Pass chart data from Blade to JavaScript only when data exists --}}
    @if($items->isNotEmpty())
        <div
            id="inventoryChartData"
            data-labels='@json($items->take(5)->pluck("description"))'
            data-values='@json($items->take(5)->pluck("total"))'>
        </div>
    @endif
</x-layout>
