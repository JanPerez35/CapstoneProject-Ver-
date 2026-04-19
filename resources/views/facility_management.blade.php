<x-layout title="Gestión de Costos de Instalaciones">
    <x-navbar></x-navbar>
    @vite('resources/js/facility_management.js')
    <div class="container py-4">

        @if(session('rates_saved'))
            <div id="ratesSavedAutoTrigger"></div>
        @endif

        @if(session('rental_saved'))
            <div id="rentalSavedAutoTrigger"></div>
        @endif

        @if(session('mock_imported'))
            <div id="mockImportAutoTrigger"></div>
        @endif

        <!--Header-->
        <div class="mb-4">
            <h1 class="fw-bold mb-1">Gestión de Costos de Facilidad</h1>
            <p class="text mb-0">Aquí puedes ver, filtrar y exportar costos estimados de uso de instalaciones.</p>
        </div>

        <!--Warning-->
        <div class="alert bg-warning-subtle text-warning-emphasis rounded-4 border-0 shadow-sm mb-4 px-4 py-4"
             id="costEstimateNotice">
            <div class="d-flex align-items-start gap-3">
                <div>
                    <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong> Los costos mostrados
                    en esta página son
                    <strong>estimaciones</strong> calculadas según las tarifas configuradas, el salón,
                    el horario y los servicios seleccionados. Todas las tarifas estan sujetas a cambios y deberan ser
                    ratificadas por el area
                    administrativa para ser consideradas como definitivas.
                </div>
            </div>
        </div>

        <!--Buttons-->
        <div class="d-flex flex-wrap gap-3 mb-4">
            <button
                type="button"
                id="openConfigureRatesModalBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
                data-bs-toggle="modal"
                data-bs-target="#configureRatesModal"
            >
                <i class="bi bi-gear"></i>
                Configurar Tarifas
            </button>

            <button
                type="button"
                id="openAddRentalModalBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
                data-bs-toggle="modal"
                data-bs-target="#addRentalModal"
            >
                <i class="bi bi-plus-lg"></i>
                Agregar Evento
            </button>

            <a href="{{ route('facility.export.csv') }}" id="downloadCsvBtn"
               class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-download"></i>
                Exportar a CSV
            </a>

            <a href="{{ route('facility.export.pdf') }}" id="downloadPdfBtn"
               class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-download"></i>
                Exportar a PDF
            </a>

            <form method="POST" action="{{ route('facility.import.mock') }}" class="d-inline">
                @csrf
                <button
                    type="submit"
                    class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
                >
                    <i class="bi bi-cloud-arrow-down"></i>
                    Eventflow API Simulado
                </button>
            </form>

        </div>

        <!--Search and Filters-->
        <div class="mb-4">
            <form id="facilityCostFilterForm" method="GET" action="{{ route('facility_management') }}" class="mb-4">
                @csrf

                <div class="row mb-4 g-3">
                    <div class="col-md-10">
                        <div class="input-group search-group">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search"></i>
                                </span>

                            <input
                                type="text"
                                id="facilitySearch"
                                name="search"
                                class="form-control border-0"
                                placeholder="Buscar por fecha, salón, hora, periodo, servicios..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="submit" id="searchFacilityBtn" class="btn btn-success">
                            Buscar
                        </button>
                    </div>

                    <div class="col-md-3">
                        <select id="reportType" name="report_type" class="form-select border-2 border-dark">
                            <option value="" {{ $reportType === '' ? 'selected' : '' }}>Tipo de Reporte</option>
                            <option value="monthly" {{ $reportType === 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="annual" {{ $reportType === 'annual' ? 'selected' : '' }}>Anual</option>
                        </select>
                    </div>

                    <div class="col-md-3 {{ $reportType !== 'monthly' ? 'd-none' : '' }}" id="monthFilterWrapper">
                        <select id="reportMonth" name="report_month" class="form-select border-2 border-dark">
                            <option value="" {{ $reportMonth === '' ? 'selected' : '' }}>Mes</option>
                            <option value="1" {{ (string)$reportMonth === '1' ? 'selected' : '' }}>Enero</option>
                            <option value="2" {{ (string)$reportMonth === '2' ? 'selected' : '' }}>Febrero</option>
                            <option value="3" {{ (string)$reportMonth === '3' ? 'selected' : '' }}>Marzo</option>
                            <option value="4" {{ (string)$reportMonth === '4' ? 'selected' : '' }}>Abril</option>
                            <option value="5" {{ (string)$reportMonth === '5' ? 'selected' : '' }}>Mayo</option>
                            <option value="6" {{ (string)$reportMonth === '6' ? 'selected' : '' }}>Junio</option>
                            <option value="7" {{ (string)$reportMonth === '7' ? 'selected' : '' }}>Julio</option>
                            <option value="8" {{ (string)$reportMonth === '8' ? 'selected' : '' }}>Agosto</option>
                            <option value="9" {{ (string)$reportMonth === '9' ? 'selected' : '' }}>Septiembre</option>
                            <option value="10" {{ (string)$reportMonth === '10' ? 'selected' : '' }}>Octubre</option>
                            <option value="11" {{ (string)$reportMonth === '11' ? 'selected' : '' }}>Noviembre</option>
                            <option value="12" {{ (string)$reportMonth === '12' ? 'selected' : '' }}>Diciembre</option>
                        </select>
                    </div>

                    <div class="col-md-3 {{ $reportType === '' ? 'd-none' : '' }}" id="yearFilterWrapper">
                        <select id="reportYear" name="report_year" class="form-select border-2 border-dark">
                            <option value="" {{ $reportYear === '' ? 'selected' : '' }}>Año</option>
                            @for($year = $minYear; $year <= $maxYear; $year++)
                                <option
                                    value="{{ $year }}" {{ (string)$reportYear === (string)$year ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select id="filterClassroom" name="filter_classroom" class="form-select border-2 border-dark">
                            <option value="" {{ ($filterClassroom ?? '') === '' ? 'selected' : '' }}>Salón</option>
                            @foreach ($allFacilityCosts as $cost)
                                <option
                                    value="{{ $cost->classroom_name }}" {{ $filterClassroom === $cost->classroom_name ? 'selected' : '' }}>
                                    {{ $cost->classroom_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="clearFacilityFilters">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!--Data Table-->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <h2 class="fw-bold mb-1">Uso de Instalaciones y Costos</h2>
                <p class="text-muted mb-0">Seguimiento estimado del uso de instalaciones y costos asociados.</p>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="facilityCostTable">
                    <thead class="table-light">
                    <tr>
                        <th class="fw-bold">
                            Fecha Inicio <br>
                            <small>(mm/dd/yyyy)</small>
                        </th>

                        <th class="fw-bold">
                            Fecha Fin <br>
                            <small>(mm/dd/yyyy)</small>
                        </th>
                        <th class="fw-bold">Responsable</th>
                        <th class="fw-bold">Salón</th>
                        <th class="fw-bold">Descripción</th>
                        <th class="fw-bold">Hora</th>
                        <th class="fw-bold">Periodo</th>
                        <th class="fw-bold">
                            Modo <br>
                            <small>de tarifa</small>
                        </th>
                        <th class="fw-bold">Servicios</th>
                        <th class="fw-bold text-end">Total</th>
                        <th class="fw-bold text-center">Acciones</th>
                    </tr>
                    </thead>

                    <tbody id="facilityCostTableBody">
                    @forelse ($items as $item)
                        <tr
                            data-entry-id="cost-row-{{ $item->id }}"
                            data-date="{{ \Carbon\Carbon::parse($item->event_date)->format('Y-m-d') }}"
                            data-month="{{ \Carbon\Carbon::parse($item->event_date)->format('n') }}"
                            data-year="{{ \Carbon\Carbon::parse($item->event_date)->format('Y') }}"
                            data-classroom="{{ $item->facilityCost->classroom_name }}"
                        >
                            <td>{{ \Carbon\Carbon::parse($item->event_date)->format('m/d/Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->end_date ?? $item->event_date)->format('m/d/Y') }}</td>
                            <td>{{ $item->responsible }}</td>
                            <td>{{ $item->facilityCost->classroom_name }}</td>
                            <td>{{ $item->event_description }}</td>
                            <td>
                                {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}
                                -
                                {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                            </td>
                            <td>
                                @if ($item->period_type === 'workday')
                                    Laborable
                                @elseif ($item->period_type === 'non_workday_saturday')
                                    No laborable sábado
                                @elseif ($item->period_type === 'non_workday_sunday_holiday')
                                    No laborable domingo o festivo
                                @else
                                    {{ $item->period_type }}
                                @endif
                            </td>

                            <td>
                                @if ($item->rate_mode === 'daily')
                                    Diario
                                @elseif ($item->rate_mode === 'weekly')
                                    Semanal
                                @elseif ($item->rate_mode === 'monthly')
                                    Mensual
                                @else
                                    {{ $item->rate_mode }}
                                @endif
                            </td>

                            <td>
                                @foreach (($item->services ?? []) as $service)
                                    @php
                                        $badgeClass = match($service) {
                                            'electricity' => 'badge-electricidad',
                                            'water' => 'badge-agua',
                                            'utilities' => 'badge-available',
                                            default => 'badge-available',
                                        };
                                    @endphp

                                    <span class="label-badge {{ $badgeClass }} me-2 mb-1">
                                            @if ($service === 'utilities')
                                            Utilidades
                                        @elseif ($service === 'electricity')
                                            Electricidad
                                        @elseif ($service === 'water')
                                            Agua
                                        @else
                                            {{ ucfirst($service) }}
                                        @endif
                                        </span>
                                @endforeach
                            </td>
                            <td class="text-end fw-semibold">${{ number_format($item->calculated_cost, 2) }}</td>
                            <td class="text-center">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger delete-cost-row-btn"
                                    data-entry-id="{{ $item->id }}"
                                    data-delete-url="{{ route('facility.events.destroy', $item->id) }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteCostEntryModal"
                                >
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>

                    <tfoot class="table-light">
                    <tr>
                        <th colspan="9" class="fw-bold text-end">Total estimado del período</th>
                        <th class="text-end fw-bold" id="facilityCostGrandTotal">
                            ${{ number_format($grandTotal, 2) }}</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>

                <div id="facilityCostEmptyState" class="card border-0 shadow-sm rounded-0 d-none container mb-4">
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-currency-dollar fs-1 text-muted"></i>
                        <h4 class="fw-bold mb-2">No hay costos para mostrar</h4>
                        <p class="text-muted mb-0">Prueba añadiendo un evento o cambiando los parametros de la
                            busqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <nav class="mt-4" aria-label="Paginación de costos">
        <ul class="pagination justify-content-center" id="facilityCostPagination"></ul>
    </nav>

    <!--Configuration button function-->
    <div class="modal fade" id="configureRatesModal" tabindex="-1" aria-labelledby="configureRatesModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="configureRatesModalLabel">Configurar Tarifas</h4>
                        <p class="text-muted mb-1">
                            Puedes configurar un salón o varios salones con los mismos valores.
                        </p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close configure-close-btn" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3 modal-scroll-safe">
                    <div class="scroll-edge-pad">
                        <form id="configureRatesForm" method="POST" action="{{ route('facility.rates.save') }}"
                              novalidate>
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold fs-5">
                                    Salones a configurar <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            id="selectAllClassroomsBtn">
                                        <i class="bi bi-check2-square me-1"></i>Seleccionar Todos
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            id="selectAcademicClassroomsBtn">
                                        <i class="bi bi-building me-1"></i>Solo Salones
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            id="selectLateralClassroomsBtn">
                                        <i class="bi bi-grid me-1"></i>Solo Laterales
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm"
                                            id="clearClassroomsSelectionBtn">
                                        <i class="bi bi-eraser me-1"></i>Limpiar Selección
                                    </button>

                                    <div class="d-flex flex-wrap gap-2 ms-md-2">
                                        <button type="button" class="btn btn-success btn-sm"
                                                id="openAddClassroomModalBtn"
                                                data-bs-toggle="modal" data-bs-target="#addClassroomModal">
                                            <i class="bi bi-plus-lg me-1"></i>Agregar Salón
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                                id="openDiscardSelectedClassroomsBtn">
                                            <i class="bi bi-trash me-1"></i>Descartar Salones
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2" id="configClassroomGroup">
                                    {{--                                    @foreach ($facilityCosts as $cost)--}}
                                    {{--                                        @php $salon = $cost->classroom_name; @endphp--}}
                                    {{--                                        <div class="col-md-3">--}}
                                    {{--                                            <label class="multi-classroom-card" for="cfg{{ str_replace(' ', '', $salon) }}">--}}
                                    {{--                                                <input--}}
                                    {{--                                                    class="form-check-input config-classroom-check"--}}
                                    {{--                                                    type="checkbox"--}}
                                    {{--                                                    id="cfg{{ str_replace(' ', '', $salon) }}"--}}
                                    {{--                                                    name="classrooms[]"--}}
                                    {{--                                                    value="{{ $salon }}"--}}
                                    {{--                                                >--}}
                                    {{--                                                <span>{{ $salon }}</span>--}}
                                    {{--                                            </label>--}}
                                    {{--                                        </div>--}}
                                    {{--                                    @endforeach--}}

                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <div class="col-md-4 classroom-card-col" data-classroom-name="{{ $salon }}">
                                            <div class="multi-classroom-card">
                                                <label class="d-flex align-items-start gap-3 mb-0 flex-grow-1"
                                                       for="cfg{{ str_replace(' ', '', $salon) }}">
                                                    <input
                                                        class="form-check-input config-classroom-check prominent-checkbox"
                                                        type="checkbox"
                                                        id="cfg{{ str_replace(' ', '', $salon) }}"
                                                        name="classrooms[]"
                                                        value="{{ $salon }}"
                                                    >
                                                    <span class="fw-medium classroom-name" title="{{ $salon }}">
                                                       {{ $salon }}
                                                   </span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 px-4 py-3"
                                     id="configureRatesHelpNotice">
                                    <div class="d-flex align-items-start gap-3">
                                        <div>
                                            <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso
                                                importante:</strong>
                                            Si el salón ya fue configurado anteriormente, se mostrarán sus tarifas
                                            guardadas para que puedas modificarlas.
                                            Si el salón nunca ha sido configurado, los campos aparecerán vacíos con
                                            ejemplos de como llenarlos como referencia.
                                        </div>
                                    </div>
                                </div>
                                <h5 class="mb-3 section-title-match">Información base del salón</h5>

                                <div class="row g-3 mb-3 justify-content-center">
                                    <div class="col-md-12 mx-auto">
                                        <div class="service-option-card">
                                            <label for="configClassroomArea" class="form-label fw-semibold">
                                                Área del salón <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-lg money-input-group">
                                                <span class="input-group-text">ft²</span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="configClassroomArea"
                                                    name="classroom_space"
                                                    placeholder="Ej. 1200.00"
                                                    required
                                                >
                                            </div>
                                            <div class="invalid-feedback d-block" id="configClassroomAreaError"></div>
                                            <small class="text-muted d-block mt-2">
                                                Ingresa el área en pies cuadrados (ft²). Solo números y hasta 2
                                                decimales. El máximo permitido es 25,000,000.00 ft².
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    @foreach ([
                                        ['id' => 'configUtilities', 'label' => 'Utilidades', 'name' => 'supply_cost'],
                                        ['id' => 'configElectricity', 'label' => 'Electricidad', 'name' => 'electricity_cost'],
                                        ['id' => 'configWater', 'label' => 'Agua', 'name' => 'water_cost'],
                                    ] as $campo)
                                        <div class="col-md-4">
                                            <div class="service-option-card">
                                                <label for="{{ $campo['id'] }}" class="form-label fw-semibold">
                                                    {{ $campo['label'] }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="{{ $campo['id'] }}"
                                                        name="{{ $campo['name'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block"
                                                     id="{{ $campo['id'] }}Error"></div>
                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 decimales. El máximo permitido es
                                                    $500.00
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="fw-semibold mb-3 period-title-small">Tarifas por período</h6>

                                <div class="row g-3">
                                    @php
                                        $periodos = [
                                            [
                                                'titulo' => 'Período laborable',
                                                'texto' => 'Lunes a viernes, 7:30 a.m. a 4:30 p.m.',
                                                'sufijo' => '1',
                                                'diario' => '0.21',
                                                'semanal' => '0.86',
                                                'mensual' => '2.74',
                                            ],
                                            [
                                                'titulo' => 'No laborable sábado',
                                                'texto' => 'Lunes a viernes, 4:30 p.m. a 9:30 p.m.; y sábados, 8:00 a.m. a 9:30 p.m.',
                                                'sufijo' => '2',
                                                'diario' => '0.26',
                                                'semanal' => '1.03',
                                                'mensual' => '3.29',
                                            ],
                                            [
                                                'titulo' => 'No laborable domingo o festivo',
                                                'texto' => 'Domingo o festivo, 8:00 a.m. a 9:30 p.m.',
                                                'sufijo' => '3',
                                                'diario' => '0.31',
                                                'semanal' => '0.00',
                                                'mensual' => '0.00',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($periodos as $periodo)
                                        <div class="col-lg-4">
                                            <div class="service-option-card h-100">
                                                <h6 class="fw-bold mb-2">{{ $periodo['titulo'] }}</h6>
                                                <p class="text-muted small mb-3">{{ $periodo['texto'] }}</p>

                                                <label for="configDaily{{ $periodo['sufijo'] }}"
                                                       class="form-label fw-semibold">
                                                    Diario <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configDaily{{ $periodo['sufijo'] }}"
                                                        name="daily_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block"
                                                     id="configDaily{{ $periodo['sufijo'] }}Error"></div>
                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 decimales. El máximo permitido es
                                                    $500.00.
                                                </small>

                                                <label for="configWeekly{{ $periodo['sufijo'] }}"
                                                       class="form-label fw-semibold">
                                                    Semanal <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configWeekly{{ $periodo['sufijo'] }}"
                                                        name="weekly_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block"
                                                     id="configWeekly{{ $periodo['sufijo'] }}Error"></div>
                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 decimales. El máximo permitido es
                                                    $500.00.
                                                </small>

                                                <label for="configMonthly{{ $periodo['sufijo'] }}"
                                                       class="form-label fw-semibold">
                                                    Mensual <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configMonthly{{ $periodo['sufijo'] }}"
                                                        name="monthly_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block"
                                                     id="configMonthly{{ $periodo['sufijo'] }}Error"></div>
                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 decimales. El máximo permitido es
                                                    $500.00.
                                                </small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa período laborable</div>
                                        <div class="fw-bold text-success fs-4" id="configWorkdayPreview">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa no laborable sábado</div>
                                        <div class="fw-bold text-success fs-4" id="configSaturdayPreview">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa domingo o festivo</div>
                                        <div class="fw-bold text-success fs-4" id="configSundayHolidayPreview">$0.00
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 modal-footer-safe">
                    <button type="button" class="btn btn-outline-secondary px-4 configure-cancel-btn">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success px-4" id="saveRatesBtn" disabled>
                        Guardar Tarifas
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!--Add event button-->
    <div class="modal fade" id="addRentalModal" tabindex="-1" aria-labelledby="addRentalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="addRentalModalLabel">Agregar Evento</h4>
                        <p class="text-muted mb-1">
                            Registra un evento y calcula su costo estimado.
                        </p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close rental-close-btn" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="addRentalForm" method="POST" action="{{ route('facility.events.store') }}" novalidate>
                        @csrf

                        <div class="row g-3 mb-3 justify-content-center">
                            <div class="col-md-6 col-lg-6">
                                <label for="rentalClassroom" class="form-label fw-semibold">
                                    Salón <span class="text-danger">*</span>
                                </label>
                                <select id="rentalClassroom" name="classroom" class="form-select form-select-lg"
                                        required>
                                    <option value="" selected disabled>Seleccionar salón</option>
                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <option value="{{ $salon }}">{{ $salon }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalResponsible" class="form-label fw-semibold">
                                    Responsable <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="rentalResponsible"
                                    name="responsible"
                                    class="form-control form-control-lg"
                                    placeholder="Nombre del responsable"
                                    minlength="10"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Entre 10 y 40 caracteres. Solo letras y espacios.
                                </small>
                                <div class="invalid-feedback" id="rentalResponsibleError">
                                    El responsable debe tener entre 10 y 40 caracteres.
                                </div>
                            </div>

                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="rentalRangeType" class="form-label fw-semibold">
                                    Duración del evento <span class="text-danger">*</span>
                                </label>
                                <select id="rentalRangeType" name="rate_mode" class="form-select form-select-lg"
                                        required>
                                    <option value="" selected disabled>Seleccionar duración</option>
                                    <option value="daily">Dia</option>
                                    <option value="weekly">Semana</option>
                                    <option value="monthly">Mes</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalStartDate" class="form-label fw-semibold" id="rentalStartDateLabel">
                                    Fecha <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="rentalStartDate"
                                    name="event_date"
                                    class="form-control form-control-lg"
                                    min="{{ now()->toDateString() }}"
                                    required
                                >
                                <div class="invalid-feedback d-block" id="rentalStartDateError"></div>
                            </div>


                            <div class="col-12">
                                <div class="row g-3 mb-3 d-none" id="rentalEndDateRow">
                                    <div class="col-md-4">
                                        <label for="rentalEndDate" class="form-label fw-semibold">
                                            Fecha de fin <span class="text-danger">*</span>
                                        </label>
                                        <input
                                            type="date"
                                            id="rentalEndDate"
                                            name="event_end_date"
                                            class="form-control form-control-lg"
                                            min="{{ now()->toDateString() }}"
                                        >
                                        <div class="invalid-feedback d-block" id="rentalEndDateError"></div>
                                    </div>

                                    <div class="col-md-8">
                                        <div
                                            class="alert alert-warning rounded-4 border-0 shadow-sm mb-0 px-4 py-3 h-100 d-flex align-items-center">
                                            <div>
                                                <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso
                                                    importante:</strong>
                                                Si seleccionas semana o mes, debes indicar una fecha de inicio y una
                                                fecha de fin válidas.
                                                El horario seleccionado (hora de inicio y fin) se aplicará a cada día
                                                dentro del rango de
                                                fechas indicado, por lo que el evento representará la misma cantidad de
                                                horas en cada uno
                                                de esos días.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="rentalStartTime" class="form-label fw-semibold">
                                    Horario inicio <span class="text-danger">*</span>
                                </label>
                                <select id="rentalStartTime" name="start_time" class="form-select form-select-lg"
                                        required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalEndTime" class="form-label fw-semibold">
                                    Horario fin <span class="text-danger">*</span>
                                </label>
                                <select id="rentalEndTime" name="end_time" class="form-select form-select-lg"
                                        required></select>
                                <div class="invalid-feedback d-block" id="rentalTimeError"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rentalDescription" class="form-label fw-semibold">
                                Descripción del evento <span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="rentalDescription"
                                name="description"
                                class="form-control form-control-lg"
                                rows="4"
                                placeholder="Descripción del evento"
                                minlength="10"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 250 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </small>
                            <div class="invalid-feedback" id="rentalDescriptionError">
                                La descripción debe tener entre 10 y 250 caracteres y solo usar caracteres permitidos.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rentalPeriodType" class="form-label fw-semibold">
                                Tipo de período <span class="text-danger">*</span>
                            </label>
                            <select id="rentalPeriodType" name="period_type" class="form-select form-select-lg"
                                    required>
                                <option value="" selected disabled>Seleccionar tipo de período</option>
                                <option value="workday">Laborable</option>
                                <option value="non_workday_saturday">No laborable sábado</option>
                                <option value="non_workday_sunday_holiday">No laborable domingo o festivo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block mb-3">
                                Servicio a aplicar <span class="text-danger">*</span>
                            </label>

                            <div class="row g-2">
                                <div class="col-md-4">
                                    <div class="multi-classroom-card">
                                        <label class="d-flex align-items-start gap-3 mb-0 flex-grow-1"
                                               for="rentalUtilities">
                                            <input
                                                class="form-check-input rental-service-check prominent-checkbox"
                                                type="checkbox"
                                                value="utilities"
                                                id="rentalUtilities"
                                                name="services[]"
                                            >
                                            <span class="fw-medium">Utilidades</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="multi-classroom-card">
                                        <label class="d-flex align-items-start gap-3 mb-0 flex-grow-1"
                                               for="rentalElectricity">
                                            <input
                                                class="form-check-input rental-service-check prominent-checkbox"
                                                type="checkbox"
                                                value="electricity"
                                                id="rentalElectricity"
                                                name="services[]"
                                            >
                                            <span class="fw-medium">Electricidad</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="multi-classroom-card">
                                        <label class="d-flex align-items-start gap-3 mb-0 flex-grow-1"
                                               for="rentalWater">
                                            <input
                                                class="form-check-input rental-service-check prominent-checkbox"
                                                type="checkbox"
                                                value="water"
                                                id="rentalWater"
                                                name="services[]"
                                            >
                                            <span class="fw-medium">Agua</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="invalid-feedback d-none" id="servicesRequiredMessage">
                                Debes seleccionar al menos un servicio.
                            </div>
                        </div>

                        <div class="rounded-4 border bg-light p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold fs-5">Costo estimado calculado</div>
                                    <small class="text-muted d-block">Se determina según período y servicios
                                        seleccionados.</small>
                                </div>
                                <span class="fw-bold text-success fs-2 mb-0" id="rentalEstimatedTotal">$0.00</span>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted d-block">Período seleccionado: <span id="detectedPeriodLabel"
                                                                                              class="fw-semibold">—</span></small>
                                <small class="text-muted d-block">Duración estimada: <span id="detectedHoursLabel"
                                                                                           class="fw-semibold">0.00 horas</span></small>
                            </div>
                        </div>

                        <input type="hidden" id="rentalEstimatedTotalInput" name="total_estimado" value="0.00">
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 rental-cancel-btn">Cancelar</button>
                    <button type="submit" form="addRentalForm" class="btn btn-success px-4" id="saveRentalBtn">
                        Guardar Evento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!--Cancel Confirm button-->
    <div class="modal fade" id="confirmCancelConfigureModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">¿Seguro que deseas cancelar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    Tienes información escrita. Si cancelas, perderás los cambios no guardados.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir Editando
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelConfigureBtn">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmar cancelar evento -->
    <div class="modal fade" id="confirmCancelRentalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">¿Seguro que deseas cancelar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    Tienes información escrita. Si cancelas, perderás los cambios no guardados.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir Editando
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelRentalBtn">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Delete modal-->
    <div class="modal fade" id="deleteCostEntryModal" tabindex="-1" aria-labelledby="deleteCostEntryModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-1" id="deleteCostEntryModalLabel">Eliminar registro</h5>
                        <p class="text-muted mb-0">¿Estás seguro de que deseas eliminar este registro de costo?</p>
                    </div>
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCostEntryBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Add Classroom Modal-->
    <div class="modal fade" id="addClassroomModal" tabindex="-1" aria-labelledby="addClassroomModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 align-items-start">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-2">Agregar salón</h5>
                        <p class="text-muted mb-1">Escribe el nombre del nuevo salón.</p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close" id="closeAddClassroomBtn" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>


                <div class="modal-body pt-0">
                    <label for="newClassroomName" class="form-label fw-semibold">
                        Nombre del salón <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="newClassroomName"
                        class="form-control border-2 border-dark"
                        placeholder="Ej. CM 211"
                    >
                    <small class="text-muted d-block fst-italic">
                        Entre 6 y 40 caracteres. Solo letras, espacios, coma, punto y guion.
                    </small>
                    <div class="invalid-feedback d-block" id="newClassroomNameError"></div>
                </div>


                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelAddClassroomBtn"
                            data-bs-dismiss="modal">Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmAddClassroomBtn" disabled>
                        Agregar Salón
                    </button>
                    <form id="addClassroomForm" method="POST" action="{{ route('facility.classrooms.store') }}"
                          class="d-none">
                        @csrf
                        <input type="hidden" name="classroom_name" id="hiddenNewClassroomName">
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deleteClassroomModal" tabindex="-1" aria-labelledby="deleteClassroomModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 align-items-start">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-1" id="deleteClassroomModalLabel">Descartar salón</h5>
                        <p class="text-muted mb-0">
                            ¿Estás seguro de que deseas eliminar los salon/es selecionados? Esta acción es permanente.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>


                <div class="modal-body pt-0">
                    <div class="fw-semibold mb-2">Salones selecionados:</div>
                    <div id="deleteClassroomNameText">—</div>
                </div>


                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteClassroomBtn">
                        Eliminar Salón
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!--Notification Toasts-->
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="deleteEntryToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">Registro eliminado correctamente.</div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar"
                        style="transform:scale(0.8);"></button>
            </div>
        </div>

        <div id="downloadToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Tu documento se descargará en unos instantes.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar"
                        style="transform:scale(0.8);"></button>
            </div>
        </div>

        <!-- Tarifa Toast -->
        <div id="ratesSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Tarifas guardadas correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast"></button>
            </div>
        </div>

        <!-- Evento Toast -->
        <div id="rentalSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Evento creado correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast"></button>
            </div>
        </div>


        <!--Eventflow toast-->

        @if(session('mock_imported'))
            <div id="mockImportToast"
                 class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                 role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold pe-1">
                        {{ session('mock_imported') }}
                    </div>
                    <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar"
                            style="transform:scale(0.8);"></button>
                </div>
            </div>
        @endif
    </div>

    <form id="deleteCostEntryForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <form id="deleteClassroomsForm" method="POST" action="{{ route('facility.classrooms.destroy') }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <style>

        .money-input::placeholder {
            color: #6c757d;
            font-style: italic;
            opacity: 1;
        }

        .service-option-card {
            border: 1px solid #dee2e6;
            border-radius: 1rem;
            padding: 1rem;
            background: #fff;
            height: 100%;
        }

        .section-title-match {
            font-size: 1.25rem;
        }

        .period-title-small {
            font-size: 1rem;
        }

        .total-hour-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .multi-classroom-card {
            display: flex;
            align-items: center;
            gap: 0.7rem;
            border: 1px solid #dee2e6;
            border-radius: 0.85rem;
            padding: 0.85rem 1rem;
            background: #fff;
            cursor: pointer;
            width: 100%;
        }

        .multi-classroom-card:hover {
            background: #f8f9fa;
        }

        #facilityCostTable thead th,
        #facilityCostTable tbody td,
        #facilityCostTable tfoot th,
        #facilityCostTable tfoot td {
            padding: 1rem 1rem;
            vertical-align: middle;
        }

        #configureRatesModal .modal-scroll-safe {
            padding-left: 1.25rem;
            padding-right: 1.25rem;
        }

        #configureRatesModal .scroll-edge-pad {
            padding-left: .35rem;
            padding-right: .35rem;
        }

        #configureRatesModal .modal-footer-safe {
            padding-left: 1.5rem !important;
            padding-right: 1.5rem !important;
        }

        #configureRatesModal .row {
            margin-left: 0;
            margin-right: 0;
        }

        #configureRatesModal [class*="col-"] {
            padding-left: .75rem;
            padding-right: .75rem;
        }
    </style>

    @php
        $ratesByClassroom = $facilityCosts->mapWithKeys(function ($cost) {
        $isConfigured =
        !is_null($cost->classroom_space) &&
        !is_null($cost->supply_cost) &&
        !is_null($cost->electricity_cost) &&
        !is_null($cost->water_cost) &&
        !is_null($cost->daily_cost_1) &&
        !is_null($cost->weekly_cost_1) &&
        !is_null($cost->monthly_cost_1) &&
        !is_null($cost->daily_cost_2) &&
        !is_null($cost->weekly_cost_2) &&
        !is_null($cost->monthly_cost_2) &&
        !is_null($cost->daily_cost_3) &&
        !is_null($cost->weekly_cost_3) &&
        !is_null($cost->monthly_cost_3);

        return [
        $cost->classroom_name => [
        'configured' => $isConfigured,
        'area' => $isConfigured ? (float) $cost->classroom_space : null,
        'utilities' => $isConfigured ? (float) $cost->supply_cost : null,
        'electricity' => $isConfigured ? (float) $cost->electricity_cost : null,
        'water' => $isConfigured ? (float) $cost->water_cost : null,
        'daily1' => $isConfigured ? (float) $cost->daily_cost_1 : null,
        'weekly1' => $isConfigured ? (float) $cost->weekly_cost_1 : null,
        'monthly1' => $isConfigured ? (float) $cost->monthly_cost_1 : null,
        'daily2' => $isConfigured ? (float) $cost->daily_cost_2 : null,
        'weekly2' => $isConfigured ? (float) $cost->weekly_cost_2 : null,
        'monthly2' => $isConfigured ? (float) $cost->monthly_cost_2 : null,
        'daily3' => $isConfigured ? (float) $cost->daily_cost_3 : null,
        'weekly3' => $isConfigured ? (float) $cost->weekly_cost_3 : null,
        'monthly3' => $isConfigured ? (float) $cost->monthly_cost_3 : null,
        ],
        ];
    })->toArray();
    @endphp

    <script>

        window.facilityManagementConfig = {
            facilityManagementUrl: @json(route('facility_management')),
            ratesByClassroom: @json($ratesByClassroom),
        };

        const ratesByClassroom = @json($ratesByClassroom);

    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // 🔹 Restore scroll
            const savedScroll = sessionStorage.getItem('facilityScroll');

            if (savedScroll) {
                window.scrollTo({
                    top: parseInt(savedScroll),
                    behavior: 'smooth'
                });
                sessionStorage.removeItem('facilityScroll');
            }

            // 🔹 Save scroll on filter submit
            const filterForm = document.getElementById('facilityCostFilterForm');

            if (filterForm) {
                filterForm.addEventListener('submit', () => {
                    sessionStorage.setItem('facilityScroll', window.scrollY);
                });
            }

            // 🔹 Save scroll on clear filters
            const clearBtn = document.getElementById('clearFacilityFilters');

            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    sessionStorage.setItem('facilityScroll', window.scrollY);
                });
            }

        });
    </script>

    <style>
        .classroom-name {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
        }

        .multi-classroom-card label {
            min-width: 0;
            flex: 1;
        }
    </style>

</x-layout>
