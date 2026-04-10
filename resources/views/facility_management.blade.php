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
            <p class="text-muted mb-0">Ver, filtrar y exportar costos estimados de uso de instalaciones.</p>
        </div>

        <!--Warning-->
        <div class="alert bg-warning-subtle text-warning-emphasis rounded-4 border-0 shadow-sm mb-4 px-4 py-4" id="costEstimateNotice">
            <div class="d-flex align-items-start gap-3">
                <div>
                    <strong><i class="bi bi-exclamation-circle me-2"></i>Aviso importante:</strong> Los costos mostrados en esta página son
                    <strong>estimaciones</strong> calculadas según las tarifas configuradas, el salón,
                    el horario y los servicios seleccionados. Estos valores pueden cambiar y deben ser
                    validados administrativamente antes de considerarse finales.
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

            <a
                href="{{ route('facility.export.csv', request()->query()) }}"
                id="downloadCsvBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
            >
                <i class="bi bi-download"></i>
                Exportar a CSV
            </a>

            <a
                href="{{ route('facility.export.pdf', request()->query()) }}"
                id="downloadPdfBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
            >
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
                    Eventflow API simulado
                </button>
            </form>

        </div>

        <!--Search and Filters-->
            <div class="card-body p-4">
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
                                    class="form-control border-0"
                                    placeholder="Buscar por fecha, salón, hora, periodo, servicios o total..."
                                >
                            </div>
                        </div>

                        <div class="col-md-2 d-grid">
                            <button type="button" id="searchFacilityBtn" class="btn btn-success" disabled>
                                Buscar
                            </button>
                        </div>

                        <div class="col-md-3">
                            <select id="reportType" name="report_type" class="form-select border-2 border-dark">
                                <option value="" disabled {{ empty($reportType) ? 'selected' : '' }}>Tipo de reporte</option>
                                <option value="monthly" {{ ($reportType ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Mensual</option>
                                <option value="annual" {{ ($reportType ?? 'monthly') === 'annual' ? 'selected' : '' }}>Anual</option>
                            </select>
                        </div>

                        <div class="col-md-3" id="monthFilterWrapper">
                            <select id="reportMonth" name="report_month" class="form-select border-2 border-dark">
                                <option value="1" {{ ($reportMonth ?? now()->month) == 1 ? 'selected' : '' }}>Enero</option>
                                <option value="2" {{ ($reportMonth ?? now()->month) == 2 ? 'selected' : '' }}>Febrero</option>
                                <option value="3" {{ ($reportMonth ?? now()->month) == 3 ? 'selected' : '' }}>Marzo</option>
                                <option value="4" {{ ($reportMonth ?? now()->month) == 4 ? 'selected' : '' }}>Abril</option>
                                <option value="5" {{ ($reportMonth ?? now()->month) == 5 ? 'selected' : '' }}>Mayo</option>
                                <option value="6" {{ ($reportMonth ?? now()->month) == 6 ? 'selected' : '' }}>Junio</option>
                                <option value="7" {{ ($reportMonth ?? now()->month) == 7 ? 'selected' : '' }}>Julio</option>
                                <option value="8" {{ ($reportMonth ?? now()->month) == 8 ? 'selected' : '' }}>Agosto</option>
                                <option value="9" {{ ($reportMonth ?? now()->month) == 9 ? 'selected' : '' }}>Septiembre</option>
                                <option value="10" {{ ($reportMonth ?? now()->month) == 10 ? 'selected' : '' }}>Octubre</option>
                                <option value="11" {{ ($reportMonth ?? now()->month) == 11 ? 'selected' : '' }}>Noviembre</option>
                                <option value="12" {{ ($reportMonth ?? now()->month) == 12 ? 'selected' : '' }}>Diciembre</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select id="reportYear" name="report_year" class="form-select border-2 border-dark">
                                <option value="2024" {{ ($reportYear ?? now()->year) == 2024 ? 'selected' : '' }}>2024</option>
                                <option value="2025" {{ ($reportYear ?? now()->year) == 2025 ? 'selected' : '' }}>2025</option>
                                <option value="2026" {{ ($reportYear ?? now()->year) == 2026 ? 'selected' : '' }}>2026</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <select id="filterClassroom" name="filter_classroom" class="form-select border-2 border-dark">
                                <option value="all" {{ ($filterClassroom ?? 'all') === 'all' ? 'selected' : '' }}>Todos los salones</option>
                                @foreach ($facilityCosts as $cost)
                                    <option value="{{ $cost->classroom_name }}" {{ ($filterClassroom ?? 'all') === $cost->classroom_name ? 'selected' : '' }}>
                                        {{ $cost->classroom_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="clearFacilityFilters">
                                Limpiar filtros
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
                        <th class="fw-bold">Fecha</th>
                        <th class="fw-bold">Salón</th>
                        <th class="fw-bold">Hora</th>
                        <th class="fw-bold">Periodo</th>
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
                                <td>{{ \Carbon\Carbon::parse($item->event_date)->translatedFormat('j M Y') }}</td>
                                <td>{{ $item->facilityCost->classroom_name }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }}
                                    -
                                    {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}
                                </td>
                                <td>
                                    @if ($item->period_type === 'laborable')
                                        Laborable
                                    @elseif ($item->period_type === 'no_laborable_sabado')
                                        No laborable sábado
                                    @elseif ($item->period_type === 'no_laborable_domingo_festivo')
                                        No laborable domingo o festivo
                                    @else
                                        {{ $item->period_type }}
                                    @endif
                                </td>
                                <td>
                                    @foreach (($item->services ?? []) as $service)
                                        <span class="label-badge badge-available me-2 mb-1">                                            @if ($service === 'utilidades')
                                                Utilidades
                                            @elseif ($service === 'electricidad')
                                                Electricidad
                                            @elseif ($service === 'agua')
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
                        <th colspan="5" class="fw-bold">Total estimado del período</th>
                        <th class="text-end fw-bold" id="facilityCostGrandTotal">${{ number_format($grandTotal, 2) }}</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>

                <div id="facilityCostEmptyState" class="card border-0 shadow-sm rounded-0 d-none container mb-4">
                    <div class="card-body py-5 text-center">
                    <i class="bi bi-currency-dollar fs-1 text-muted"></i>
                    <h4 class="fw-bold mb-2">No hay costos para mostrar</h4>
                    <p class="text-muted mb-0">Prueba añadiendo un evento o cambiando los parametros de la busqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <nav class="mt-4" aria-label="Paginación de costos">
        <ul class="pagination justify-content-center" id="facilityCostPagination"></ul>
    </nav>

    <!--Configuration button function-->
    <div class="modal fade" id="configureRatesModal" tabindex="-1" aria-labelledby="configureRatesModalLabel" aria-hidden="true">
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
                        <form id="configureRatesForm" method="POST" action="{{ route('facility.rates.save') }}" novalidate>
                            @csrf

                            <div class="mb-4">
                                <label class="form-label fw-semibold fs-5">
                                    Salones a configurar <span class="text-danger">*</span>
                                </label>

                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <button type="button" class="btn btn-outline-success btn-sm" id="selectAllClassroomsBtn">
                                        <i class="bi bi-check2-square me-1"></i>Seleccionar todos
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="selectAcademicClassroomsBtn">
                                        <i class="bi bi-building me-1"></i>Solo salones
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="selectLateralClassroomsBtn">
                                        <i class="bi bi-grid me-1"></i>Solo laterales
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" id="clearClassroomsSelectionBtn">
                                        <i class="bi bi-eraser me-1"></i>Limpiar Selección
                                    </button>

                                   <div class="d-flex flex-wrap gap-2 ms-md-2">
                                       <button type="button" class="btn btn-success btn-sm" id="openAddClassroomModalBtn"
                                               data-bs-toggle="modal" data-bs-target="#addClassroomModal">
                                           <i class="bi bi-plus-lg me-1"></i>Agregar salón
                                       </button>
                                       <button type="button" class="btn btn-danger btn-sm" id="openDiscardSelectedClassroomsBtn">
                                           <i class="bi bi-trash me-1"></i>Descartar salones
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
                                                <label class="d-flex align-items-start gap-3 mb-0 flex-grow-1" for="cfg{{ str_replace(' ', '', $salon) }}">
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
                                <hr class="my-4">
                                <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 px-4 py-3" id="configureRatesHelpNotice">
                                    <div class="d-flex align-items-start gap-3">
                                        <div>
                                            <strong><i class="bi bi-warning-circle me-2"></i>Aviso:</strong>
                                            Si el salón ya fue configurado anteriormente, se mostrarán sus tarifas guardadas para que puedas modificarlas.
                                            Si el salón nunca ha sido configurado, los campos aparecerán vacíos con ejemplos como referencia.
                                        </div>
                                    </div>
                                </div>
                                <h5 class="mb-3 section-title-match">Información base del salón</h5>

                                <div class="row g-3">
                                    @foreach ([
                                        ['id' => 'configAreaSalon', 'label' => 'Área del salón', 'name' => 'classroom_space', 'help' => 'Costo base por pie cuadrado.'],
                                        ['id' => 'configUtilidades', 'label' => 'Utilidades', 'name' => 'supply_cost'],
                                        ['id' => 'configElectricidad', 'label' => 'Electricidad', 'name' => 'electricity_cost'],
                                        ['id' => 'configAgua', 'label' => 'Agua', 'name' => 'water_cost'],
                                    ] as $campo)
                                        <div class="col-md-3">
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
{{--                                                        value = "0.00"--}}
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block" id="{{ $campo['id'] }}Error"></div>
                                                @isset($campo['help'])
                                                    <small class="text-muted d-block mt-2">{{ $campo['help'] }}</small>
                                                @endisset
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

                                                <label for="configDiaria{{ $periodo['sufijo'] }}" class="form-label fw-semibold">
                                                    Diario <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configDiaria{{ $periodo['sufijo'] }}"
                                                        name="daily_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block" id="configDiaria{{ $periodo['sufijo'] }}Error"></div>

                                                <label for="configSemanal{{ $periodo['sufijo'] }}" class="form-label fw-semibold">
                                                    Semanal <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configSemanal{{ $periodo['sufijo'] }}"
                                                        name="weekly_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block" id="configSemanal{{ $periodo['sufijo'] }}Error"></div>

                                                <label for="configMensual{{ $periodo['sufijo'] }}" class="form-label fw-semibold">
                                                    Mensual <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg mb-1 money-input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="configMensual{{ $periodo['sufijo'] }}"
                                                        name="monthly_cost_{{ $periodo['sufijo'] }}"
                                                        placeholder="Ej. 25.00"
                                                        required
                                                    >
                                                </div>
                                                <div class="invalid-feedback d-block" id="configMensual{{ $periodo['sufijo'] }}Error"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa período laborable</div>
                                        <div class="fw-bold text-success fs-4" id="configPreviewLaborable">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa no laborable sábado</div>
                                        <div class="fw-bold text-success fs-4" id="configPreviewSabado">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa domingo o festivo</div>
                                        <div class="fw-bold text-success fs-4" id="configPreviewDomingo">$0.00</div>
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

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="rentalClassroom" class="form-label fw-semibold">
                                    Salón <span class="text-danger">*</span>
                                </label>
                                <select id="rentalClassroom" name="salon" class="form-select form-select-lg" required>
                                    <option value="" selected disabled>Seleccionar salón</option>
                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <option value="{{ $salon }}">{{ $salon }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="rentalDate" class="form-label fw-semibold">
                                    Fecha <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="rentalDate"
                                    name="fecha"
                                    class="form-control form-control-lg"
                                    min="{{now()->toDateString()}}"
                                    required
                                >
                                <div class="invalid-feedback d-block" id="rentalDateError"></div>
                            </div>

                            <div class="col-md-4">
                                <label for="rentalResponsable" class="form-label fw-semibold">
                                    Responsable <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="rentalResponsable"
                                    name="responsable"
                                    class="form-control form-control-lg"
                                    placeholder="Nombre del responsable"
                                    minlength="10"
                                    maxlength="40"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Entre 10 y 40 caracteres. Solo letras y espacios.
                                </small>
                                <div class="invalid-feedback" id="rentalResponsableError">
                                    El responsable debe tener entre 10 y 40 caracteres.
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="rentalStartTime" class="form-label fw-semibold">
                                    Horario inicio <span class="text-danger">*</span>
                                </label>
                                <select id="rentalStartTime" name="hora_inicio" class="form-select form-select-lg" required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalEndTime" class="form-label fw-semibold">
                                    Horario fin <span class="text-danger">*</span>
                                </label>
                                <select id="rentalEndTime" name="hora_fin" class="form-select form-select-lg" required></select>
                                <div class="invalid-feedback d-block" id="rentalTimeError"></div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rentalDescripcion" class="form-label fw-semibold">
                                Descripción del evento <span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="rentalDescripcion"
                                name="descripcion"
                                class="form-control form-control-lg"
                                rows="4"
                                placeholder="Descripción del evento"
                                minlength="10"
                                maxlength="500"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 500 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </small>
                            <div class="invalid-feedback" id="rentalDescripcionError">
                                La descripción debe tener entre 10 y 500 caracteres y solo usar caracteres permitidos.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="rentalPeriodType" class="form-label fw-semibold">
                                Tipo de período <span class="text-danger">*</span>
                            </label>
                            <select id="rentalPeriodType" name="tipo_periodo" class="form-select form-select-lg" required>
                                <option value="" selected disabled>Seleccionar tipo de período</option>
                                <option value="laborable">Laborable</option>
                                <option value="no_laborable_sabado">No laborable sábado</option>
                                <option value="no_laborable_domingo_festivo">No laborable domingo o festivo</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold d-block mb-3">
                                Servicio a aplicar <span class="text-danger">*</span>
                            </label>

                            <div class="row g-4">
                                <div class="col-md-6 col-lg-4">
                                    <label class="service-toggle-card w-100" for="rentalUtilities">
                                        <input class="service-toggle-input rental-service-check" type="checkbox" value="utilidades" id="rentalUtilities" name="servicios[]">
                                        <div class="service-toggle-content service-toggle-content-lg">
                                            <span class="service-toggle-check"><i class="bi bi-check-lg"></i></span>
                                            <span class="fw-semibold">Utilidades</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="service-toggle-card w-100" for="rentalElectricity">
                                        <input class="service-toggle-input rental-service-check" type="checkbox" value="electricidad" id="rentalElectricity" name="servicios[]">
                                        <div class="service-toggle-content service-toggle-content-lg">
                                            <span class="service-toggle-check"><i class="bi bi-check-lg"></i></span>
                                            <span class="fw-semibold">Electricidad</span>
                                        </div>
                                    </label>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <label class="service-toggle-card w-100" for="rentalWater">
                                        <input class="service-toggle-input rental-service-check" type="checkbox" value="agua" id="rentalWater" name="servicios[]">
                                        <div class="service-toggle-content service-toggle-content-lg">
                                            <span class="service-toggle-check"><i class="bi bi-check-lg"></i></span>
                                            <span class="fw-semibold">Agua</span>
                                        </div>
                                    </label>
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
                                    <small class="text-muted d-block">Se determina según período y servicios seleccionados.</small>
                                </div>
                                <span class="fw-bold text-success fs-2 mb-0" id="rentalEstimatedTotal">$0.00</span>
                            </div>
                            <div class="mt-3">
                                <small class="text-muted d-block">Período seleccionado: <span id="detectedPeriodLabel" class="fw-semibold">—</span></small>
                                <small class="text-muted d-block">Duración estimada: <span id="detectedHoursLabel" class="fw-semibold">0.00 horas</span></small>
                            </div>
                        </div>

                        <input type="hidden" id="rentalEstimatedTotalInput" name="total_estimado" value="0.00">
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4 rental-cancel-btn">Cancelar</button>
                    <button type="submit" form="addRentalForm" class="btn btn-success px-4" id="saveRentalBtn" disabled>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir editando</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelConfigureBtn">Sí, cancelar</button>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir editando</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelRentalBtn">Sí, cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Delete modal-->
    <div class="modal fade" id="deleteCostEntryModal" tabindex="-1" aria-labelledby="deleteCostEntryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-1" id="deleteCostEntryModalLabel">Eliminar registro</h5>
                        <p class="text-muted mb-0">¿Estás seguro de que deseas eliminar este registro de costo?</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCostEntryBtn">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!--Add Classroom Modal-->
    <div class="modal fade" id="addClassroomModal" tabindex="-1" aria-labelledby="addClassroomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 align-items-start">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-1" id="addClassroomModalLabel">Agregar salón</h5>
                        <p class="text-muted mb-0">Escribe el nombre del nuevo salón.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmAddClassroomBtn" disabled>
                        Agregar salón
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="deleteClassroomModal" tabindex="-1" aria-labelledby="deleteClassroomModalLabel" aria-hidden="true">
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
                        Eliminar salón
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!--Notification Toasts-->
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="ratesSavedToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">Tarifas guardadas correctamente.</div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="transform:scale(0.8);"></button>
            </div>
        </div>

        <div id="rentalSavedToast" class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">Evento guardado correctamente.</div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="transform:scale(0.8);"></button>
            </div>
        </div>

        <div id="deleteEntryToast" class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">Registro eliminado correctamente.</div>
                <button type="button" class="btn-close p-0 ms-1 me-2" data-bs-dismiss="toast" aria-label="Cerrar" style="transform:scale(0.8);"></button>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        @if(session('mock_imported'))
            <div id="mockImportToast" class="toast align-items-center text-bg-success border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('mock_imported') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        @endif
    </div>

    <form id="deleteCostEntryForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
    <div id="downloadToast"
         class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
         role="alert"
         aria-live="assertive"
         aria-atomic="true"
         style="width:auto; max-width:fit-content;">
        <div class="d-flex align-items-center">
            <div class="toast-body fw-semibold pe-1">
                Tu documento se descargará en unos instantes.
            </div>
            <button type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="transform:scale(0.8);"></button>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">

        <!-- Tarifa Toast -->
        <div id="ratesSavedToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    Tarifas guardadas correctamente.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>

        <!-- Evento Toast -->
        <div id="rentalSavedToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    Evento creado correctamente.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>

    </div>

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

        .service-badge-table {
            background-color: #6FC21F;
            color: white;
            font-weight: 600;
        }

        .service-toggle-card {
            display: block;
            cursor: pointer;
            margin: 0;
        }

        .service-toggle-input {
            display: none;
        }

        .service-toggle-content {
            min-height: 84px;
            border: 1px solid #ced4da;
            border-radius: 1rem;
            background: #fff;
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.95rem;
            transition: all 0.2s ease;
        }

        .service-toggle-content-lg {
            min-height: 98px;
            padding: 1.15rem 1.15rem;
        }

        .service-toggle-check {
            width: 24px;
            height: 24px;
            border: 1px solid #ced4da;
            border-radius: 0.45rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: transparent;
            background: #fff;
        }

        .service-toggle-input:checked + .service-toggle-content {
            border-color: #0d6efd;
            background: #f8fbff;
            box-shadow: 0 0 0 1px rgba(13, 110, 253, 0.15);
        }

        .service-toggle-input:checked + .service-toggle-content .service-toggle-check {
            background: #0d6efd;
            border-color: #0d6efd;
            color: #fff;
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
        //                $tarifasPorSalon = $facilityCosts->mapWithKeys(function ($cost) {
        //                    return [
        //                        $cost->classroom_name => [
        //                            'area' => (float) $cost->classroom_space,
        //                            'utilidades' => (float) $cost->supply_cost,
        //                            'electricidad' => (float) $cost->electricity_cost,
        //                            'agua' => (float) $cost->water_cost,
        //                            'diaria1' => (float) $cost->daily_cost_1,
        //                            'semanal1' => (float) $cost->weekly_cost_1,
        //                            'mensual1' => (float) $cost->monthly_cost_1,
        //                            'diaria2' => (float) $cost->daily_cost_2,
        //                            'semanal2' => (float) $cost->weekly_cost_2,
        //                            'mensual2' => (float) $cost->monthly_cost_2,
        //                            'diaria3' => (float) $cost->daily_cost_3,
        //                            'semanal3' => (float) $cost->weekly_cost_3,
        //                            'mensual3' => (float) $cost->monthly_cost_3,
        //                        ],
        //                    ];
        //                })->toArray();
            $tarifasPorSalon = $facilityCosts->mapWithKeys(function ($cost) {
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
            'utilidades' => $isConfigured ? (float) $cost->supply_cost : null,
            'electricidad' => $isConfigured ? (float) $cost->electricity_cost : null,
            'agua' => $isConfigured ? (float) $cost->water_cost : null,
            'diaria1' => $isConfigured ? (float) $cost->daily_cost_1 : null,
            'semanal1' => $isConfigured ? (float) $cost->weekly_cost_1 : null,
            'mensual1' => $isConfigured ? (float) $cost->monthly_cost_1 : null,
            'diaria2' => $isConfigured ? (float) $cost->daily_cost_2 : null,
            'semanal2' => $isConfigured ? (float) $cost->weekly_cost_2 : null,
            'mensual2' => $isConfigured ? (float) $cost->monthly_cost_2 : null,
            'diaria3' => $isConfigured ? (float) $cost->daily_cost_3 : null,
            'semanal3' => $isConfigured ? (float) $cost->weekly_cost_3 : null,
            'mensual3' => $isConfigured ? (float) $cost->monthly_cost_3 : null,
            ],
            ];
        })->toArray();
    @endphp

    <script>

        window.facilityManagementConfig = {
            facilityManagementUrl: @json(route('facility_management')),
            tarifasPorSalon: @json($tarifasPorSalon),
        };

        const tarifasPorSalon = @json($tarifasPorSalon);

    </script>

</x-layout>
