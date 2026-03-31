<x-layout title="Gestión de Costos de Instalaciones">
    <x-navbar></x-navbar>

    <div class="container py-4">

        <!--Header-->
        <div class="mb-4">
            <h1 class="fw-bold mb-1">Gestión de Costos de Facilidad</h1>
            <p class="text-muted mb-0">Ver, filtrar y exportar costos estimados de uso de instalaciones.</p>
        </div>

        <!--Warning-->
        <div class="alert alert-warning rounded-4 border-0 shadow-sm mb-4 px-4 py-4" id="costEstimateNotice">
            <div class="d-flex align-items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4 mt-1"></i>
                <div>
                    <strong>Aviso importante:</strong> Los costos mostrados en esta página son
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

            <button
                type="button"
                id="downloadCsvBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
            >
                <i class="bi bi-download"></i>
                Exportar a CSV
            </button>

            <button
                type="button"
                id="downloadPdfBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
            >
                <i class="bi bi-download"></i>
                Exportar a PDF
            </button>
        </div>

        <!--Filters-->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form id="facilityCostFilterForm" class="row g-3 align-items-end">
                    @csrf

                    <div class="col-md-4 col-lg-3">
                        <label for="reportType" class="form-label fw-semibold">Tipo de reporte</label>
                        <select id="reportType" name="report_type" class="form-select form-select-lg">
                            <option value="monthly" selected>Mensual</option>
                            <option value="annual">Anual</option>
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3" id="monthFilterWrapper">
                        <label for="reportMonth" class="form-label fw-semibold">Mes</label>
                        <select id="reportMonth" name="report_month" class="form-select form-select-lg">
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3" selected>Marzo</option>
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

                    <div class="col-md-4 col-lg-3">
                        <label for="reportYear" class="form-label fw-semibold">Año</label>
                        <select id="reportYear" name="report_year" class="form-select form-select-lg">
                            <option value="2024" selected>2024</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>

                    <div class="col-md-12 col-lg-3">
                        <label for="filterClassroom" class="form-label fw-semibold">Salón</label>
                        <select id="filterClassroom" name="filter_classroom" class="form-select form-select-lg">
                            <option value="all" selected>Todos los salones</option>
                            <option value="Cancha CM">Cancha CM</option>
                            <option value="Lateral 1">Lateral 1</option>
                            <option value="Lateral 2">Lateral 2</option>
                            <option value="CM 201">CM 201</option>
                            <option value="CM 202">CM 202</option>
                            <option value="CM 203">CM 203</option>
                            <option value="CM 204">CM 204</option>
                            <option value="CM 210">CM 210</option>
                        </select>
                    </div>
                </form>
            </div>
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
                    <tr
                        data-entry-id="cost-row-001"
                        data-date="2024-03-05"
                        data-month="3"
                        data-year="2024"
                        data-classroom="CM 202"
                    >
                        <td>5 mar 2024</td>
                        <td>CM 202</td>
                        <td>09:00 AM - 11:00 AM</td>
                        <td>Laborable</td>
                        <td>
                            <span class="badge rounded-0 px-3 py-2 me-2 mb-1 service-badge-table">Utilidades</span>
                            <span class="badge rounded-0 px-3 py-2 me-2 mb-1 service-badge-table">Electricidad</span>
                        </td>
                        <td class="text-end fw-semibold">$134.25</td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger delete-cost-row-btn"
                                data-entry-id="cost-row-001"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteCostEntryModal"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <tr
                        data-entry-id="cost-row-002"
                        data-date="2024-03-09"
                        data-month="3"
                        data-year="2024"
                        data-classroom="Cancha CM"
                    >
                        <td>9 mar 2024</td>
                        <td>Cancha CM</td>
                        <td>06:00 PM - 10:00 PM</td>
                        <td>No laborable sábado</td>
                        <td>
                            <span class="badge rounded-0 px-3 py-2 me-2 mb-1 service-badge-table">Agua</span>
                        </td>
                        <td class="text-end fw-semibold">$3535.60</td>
                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger delete-cost-row-btn"
                                data-entry-id="cost-row-002"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteCostEntryModal"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    </tbody>

                    <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="fw-bold">Total estimado del período</th>
                        <th class="text-end fw-bold" id="facilityCostGrandTotal">$3669.85</th>
                        <th></th>
                    </tr>
                    </tfoot>
                </table>

                <div id="facilityCostEmptyState" class="d-none p-5 text-center">
                    <h5 class="fw-bold mb-2">No hay costos para mostrar</h5>
                    <p class="text-muted mb-0">Prueba cambiando el período o el salón seleccionado.</p>
                </div>
            </div>
        </div>
    </div>

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
                        <form id="configureRatesForm" novalidate>
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
                                </div>

                                <div class="row g-2" id="configClassroomGroup">
                                    @foreach (['Cancha CM','Lateral 1','Lateral 2','CM 201','CM 202','CM 203','CM 204','CM 210'] as $salon)
                                        <div class="col-md-3">
                                            <label class="multi-classroom-card" for="cfg{{ str_replace(' ', '', $salon) }}">
                                                <input class="form-check-input config-classroom-check" type="checkbox" id="cfg{{ str_replace(' ', '', $salon) }}" value="{{ $salon }}">
                                                <span>{{ $salon }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <h5 class="mb-3 section-title-match">Información base del salón</h5>

                                <div class="row g-3">
                                    @foreach ([
                                        ['id' => 'configAreaSalon', 'label' => 'Área del salón', 'name' => 'area_salon', 'help' => 'Costo base por pie cuadrado.'],
                                        ['id' => 'configUtilidades', 'label' => 'Utilidades', 'name' => 'utilidades'],
                                        ['id' => 'configElectricidad', 'label' => 'Electricidad', 'name' => 'electricidad'],
                                        ['id' => 'configAgua', 'label' => 'Agua', 'name' => 'agua'],
                                    ] as $campo)
                                        <div class="col-md-3">
                                            <div class="service-option-card">
                                                <label for="{{ $campo['id'] }}" class="form-label fw-semibold">
                                                    {{ $campo['label'] }} <span class="text-danger">*</span>
                                                </label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text">$</span>
                                                    <input
                                                        type="text"
                                                        class="form-control money-input"
                                                        id="{{ $campo['id'] }}"
                                                        name="{{ $campo['name'] }}"
                                                        value="0.00"
                                                        required
                                                    >
                                                </div>
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

                                                <label for="configDiaria{{ $periodo['sufijo'] }}" class="form-label fw-semibold">Diario <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg mb-3">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control money-input" id="configDiaria{{ $periodo['sufijo'] }}" name="diaria_{{ $periodo['sufijo'] }}" value="{{ $periodo['diario'] }}" required>
                                                </div>

                                                <label for="configSemanal{{ $periodo['sufijo'] }}" class="form-label fw-semibold">Semanal <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg mb-3">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control money-input" id="configSemanal{{ $periodo['sufijo'] }}" name="semanal_{{ $periodo['sufijo'] }}" value="{{ $periodo['semanal'] }}" required>
                                                </div>

                                                <label for="configMensual{{ $periodo['sufijo'] }}" class="form-label fw-semibold">Mensual <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-lg">
                                                    <span class="input-group-text">$</span>
                                                    <input type="text" class="form-control money-input" id="configMensual{{ $periodo['sufijo'] }}" name="mensual_{{ $periodo['sufijo'] }}" value="{{ $periodo['mensual'] }}" required>
                                                </div>
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
                    <form id="addRentalForm" novalidate>
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="rentalClassroom" class="form-label fw-semibold">
                                    Salón <span class="text-danger">*</span>
                                </label>
                                <select id="rentalClassroom" name="salon" class="form-select form-select-lg" required>
                                    <option value="" selected disabled>Seleccionar salón</option>
                                    @foreach (['Cancha CM','Lateral 1','Lateral 2','CM 201','CM 202','CM 203','CM 204','CM 210'] as $salon)
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
                                    required
                                >
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
                                    maxlength="60"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Entre 10 y 60 caracteres. Solo letras y espacios. La primera letra irá en mayúscula.
                                </small>
                                <div class="invalid-feedback" id="rentalResponsableError">
                                    El responsable debe tener entre 10 y 60 caracteres.
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
                                maxlength="1000"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 1000 caracteres. Solo letras, números, espacios, punto, coma y guion.
                            </small>
                            <div class="invalid-feedback" id="rentalDescripcionError">
                                La descripción debe tener entre 10 y 1000 caracteres y solo usar caracteres permitidos.
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
                    <button type="button" class="btn btn-success px-4" id="saveRentalBtn" disabled>Guardar Evento</button>
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

    <style>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const $ = (id) => document.getElementById(id);

            const classroomIds = ['Cancha CM', 'Lateral 1', 'Lateral 2', 'CM 201', 'CM 202', 'CM 203', 'CM 204', 'CM 210'];
            const academicRooms = ['CM 201', 'CM 202', 'CM 203', 'CM 204', 'CM 210'];
            const lateralRooms = ['Lateral 1', 'Lateral 2'];

            const reportType = $('reportType');
            const monthFilterWrapper = $('monthFilterWrapper');
            const reportMonth = $('reportMonth');
            const reportYear = $('reportYear');
            const filterClassroom = $('filterClassroom');

            const facilityCostTable = $('facilityCostTable');
            const facilityCostTableBody = $('facilityCostTableBody');
            const facilityCostEmptyState = $('facilityCostEmptyState');
            const facilityCostGrandTotal = $('facilityCostGrandTotal');

            const configureRatesModal = $('configureRatesModal');
            const addRentalModal = $('addRentalModal');

            const configureRatesForm = $('configureRatesForm');
            const addRentalForm = $('addRentalForm');

            const saveRatesBtn = $('saveRatesBtn');
            const saveRentalBtn = $('saveRentalBtn');

            const configClassroomChecks = [...document.querySelectorAll('.config-classroom-check')];
            const moneyInputs = [...document.querySelectorAll('.money-input')];

            const configAreaSalon = $('configAreaSalon');
            const configUtilidades = $('configUtilidades');
            const configElectricidad = $('configElectricidad');
            const configAgua = $('configAgua');
            const configDiaria1 = $('configDiaria1');
            const configSemanal1 = $('configSemanal1');
            const configMensual1 = $('configMensual1');
            const configDiaria2 = $('configDiaria2');
            const configSemanal2 = $('configSemanal2');
            const configMensual2 = $('configMensual2');
            const configDiaria3 = $('configDiaria3');
            const configSemanal3 = $('configSemanal3');
            const configMensual3 = $('configMensual3');

            const configPreviewLaborable = $('configPreviewLaborable');
            const configPreviewSabado = $('configPreviewSabado');
            const configPreviewDomingo = $('configPreviewDomingo');

            const rentalClassroom = $('rentalClassroom');
            const rentalDate = $('rentalDate');
            const rentalResponsable = $('rentalResponsable');
            const rentalStartTime = $('rentalStartTime');
            const rentalEndTime = $('rentalEndTime');
            const rentalDescripcion = $('rentalDescripcion');
            const rentalPeriodType = $('rentalPeriodType');

            const rentalUtilities = $('rentalUtilities');
            const rentalElectricity = $('rentalElectricity');
            const rentalWater = $('rentalWater');
            const rentalServiceChecks = [rentalUtilities, rentalElectricity, rentalWater];

            const rentalEstimatedTotal = $('rentalEstimatedTotal');
            const rentalEstimatedTotalInput = $('rentalEstimatedTotalInput');
            const detectedPeriodLabel = $('detectedPeriodLabel');
            const detectedHoursLabel = $('detectedHoursLabel');
            const servicesRequiredMessage = $('servicesRequiredMessage');
            const rentalResponsableError = $('rentalResponsableError');
            const rentalDescripcionError = $('rentalDescripcionError');

            const confirmDeleteCostEntryBtn = $('confirmDeleteCostEntryBtn');
            const deleteButtons = () => [...document.querySelectorAll('.delete-cost-row-btn')];

            const toasts = {
                ratesSaved: bootstrap.Toast.getOrCreateInstance($('ratesSavedToast'), { delay: 2500 }),
                rentalSaved: bootstrap.Toast.getOrCreateInstance($('rentalSavedToast'), { delay: 2500 }),
                deleteEntry: bootstrap.Toast.getOrCreateInstance($('deleteEntryToast'), { delay: 2500 }),
            };

            let selectedRowToDelete = null;
            let nextEntryId = 3;
            let configureDirty = false;
            let rentalDirty = false;

            const tarifasPorSalon = {
                'Cancha CM': { area: 12082.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'Lateral 1': { area: 4706.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'Lateral 2': { area: 4706.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'CM 201': { area: 568.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'CM 202': { area: 564.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'CM 203': { area: 564.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'CM 204': { area: 568.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
                'CM 210': { area: 820.00, utilidades: 0.00, electricidad: 0.00, agua: 0.00, diaria1: 0.21, semanal1: 0.86, mensual1: 2.74, diaria2: 0.26, semanal2: 1.03, mensual2: 3.29, diaria3: 0.31, semanal3: 0.00, mensual3: 0.00 },
            };

            const formatMoney = (value) => Number(value).toLocaleString('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            const parseMoney = (text) => Number(String(text).replace(/[^0-9.]/g, '')) || 0;
            const toNumber = (value) => Number(String(value).replace(/[^0-9.]/g, '')) || 0;

            function sanitizeMoneyInput(input) {
                let value = input.value.replace(/[^0-9.]/g, '');
                const firstDot = value.indexOf('.');

                if (firstDot !== -1) {
                    value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, '');
                }

                if (value.startsWith('.')) value = '0' + value;
                input.value = value;
            }

            function normalizeMoneyInput(input) {
                input.value = toNumber(input.value).toFixed(2);
            }

            function timeToMinutes(timeValue) {
                if (!timeValue) return 0;
                const [hour, minute] = timeValue.split(':').map(Number);
                return (hour * 60) + minute;
            }

            function calculateHours(start, end) {
                const diff = timeToMinutes(end) - timeToMinutes(start);
                return diff > 0 ? diff / 60 : 0;
            }

            function getSelectedClassrooms() {
                return configClassroomChecks.filter(check => check.checked).map(check => check.value);
            }

            function setSelectionByList(list) {
                configClassroomChecks.forEach(check => {
                    check.checked = list.includes(check.value);
                });
                configureDirty = true;
                updateConfigureSaveState();
            }

            function loadRatesIntoForm(classroomId) {
                const data = tarifasPorSalon[classroomId];
                if (!data) return;

                configAreaSalon.value = Number(data.area).toFixed(2);
                configUtilidades.value = Number(data.utilidades).toFixed(2);
                configElectricidad.value = Number(data.electricidad).toFixed(2);
                configAgua.value = Number(data.agua).toFixed(2);
                configDiaria1.value = Number(data.diaria1).toFixed(2);
                configSemanal1.value = Number(data.semanal1).toFixed(2);
                configMensual1.value = Number(data.mensual1).toFixed(2);
                configDiaria2.value = Number(data.diaria2).toFixed(2);
                configSemanal2.value = Number(data.semanal2).toFixed(2);
                configMensual2.value = Number(data.mensual2).toFixed(2);
                configDiaria3.value = Number(data.diaria3).toFixed(2);
                configSemanal3.value = Number(data.semanal3).toFixed(2);
                configMensual3.value = Number(data.mensual3).toFixed(2);

                updateConfigPreview();
            }

            function updateConfigPreview() {
                const area = toNumber(configAreaSalon.value);
                configPreviewLaborable.textContent = formatMoney((area * toNumber(configDiaria1.value)).toFixed(2));
                configPreviewSabado.textContent = formatMoney((area * toNumber(configDiaria2.value)).toFixed(2));
                configPreviewDomingo.textContent = formatMoney((area * toNumber(configDiaria3.value)).toFixed(2));
            }

            function isConfigureFormValid() {
                if (!getSelectedClassrooms().length) return false;

                const requiredValues = [
                    configAreaSalon.value, configUtilidades.value, configElectricidad.value, configAgua.value,
                    configDiaria1.value, configSemanal1.value, configMensual1.value,
                    configDiaria2.value, configSemanal2.value, configMensual2.value,
                    configDiaria3.value, configSemanal3.value, configMensual3.value,
                ];

                return requiredValues.every(value => value !== '' && !Number.isNaN(toNumber(value)));
            }

            function updateConfigureSaveState() {
                saveRatesBtn.disabled = !isConfigureFormValid();
            }

            function periodLabelFromValue(value) {
                if (value === 'laborable') return 'Laborable';
                if (value === 'no_laborable_sabado') return 'No laborable sábado';
                if (value === 'no_laborable_domingo_festivo') return 'No laborable domingo o festivo';
                return '—';
            }

            function getPeriodRateData(classroomId, periodType) {
                const data = tarifasPorSalon[classroomId];
                if (!data) return { diaria: 0, semanal: 0, mensual: 0 };

                if (periodType === 'laborable') return { diaria: toNumber(data.diaria1), semanal: toNumber(data.semanal1), mensual: toNumber(data.mensual1) };
                if (periodType === 'no_laborable_sabado') return { diaria: toNumber(data.diaria2), semanal: toNumber(data.semanal2), mensual: toNumber(data.mensual2) };
                if (periodType === 'no_laborable_domingo_festivo') return { diaria: toNumber(data.diaria3), semanal: toNumber(data.semanal3), mensual: toNumber(data.mensual3) };

                return { diaria: 0, semanal: 0, mensual: 0 };
            }

            function hasSelectedServices() {
                return rentalServiceChecks.some(input => input.checked);
            }

            function toggleServicesError(show) {
                servicesRequiredMessage.classList.toggle('d-none', !show);
                servicesRequiredMessage.classList.toggle('d-block', show);
            }

            function setFieldError(field, errorElement, message) {
                if (!field) return;
                field.classList.add('is-invalid');
                if (errorElement) errorElement.textContent = message;
            }

            function clearFieldError(field, errorElement) {
                if (!field) return;
                field.classList.remove('is-invalid');
                if (errorElement) errorElement.textContent = '';
            }

            function capitalizeWords(value) {
                return value
                    .toLowerCase()
                    .replace(/\s+/g, ' ')
                    .trimStart()
                    .replace(/(^|\s)([A-Za-zÁÉÍÓÚáéíóúÑñ])/g, (match, space, letter) => `${space}${letter.toUpperCase()}`);
            }

            function validateResponsable(showError = true) {
                const value = rentalResponsable.value.trim();
                const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;

                if (showError) clearFieldError(rentalResponsable, rentalResponsableError);

                if (!value) {
                    if (showError) setFieldError(rentalResponsable, rentalResponsableError, 'El responsable es requerido.');
                    return false;
                }

                if (value.length < 10) {
                    if (showError) setFieldError(rentalResponsable, rentalResponsableError, 'El responsable debe tener al menos 10 caracteres.');
                    return false;
                }

                if (value.length > 60) {
                    if (showError) setFieldError(rentalResponsable, rentalResponsableError, 'El responsable no puede exceder 60 caracteres.');
                    return false;
                }

                if (!regex.test(value)) {
                    if (showError) setFieldError(rentalResponsable, rentalResponsableError, 'Solo se permiten letras y espacios.');
                    return false;
                }

                return true;
            }

            function validateDescripcion(showError = true) {
                const value = rentalDescripcion.value.trim();
                const regex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

                if (showError) clearFieldError(rentalDescripcion, rentalDescripcionError);

                if (!value) {
                    if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción es requerida.');
                    return false;
                }

                if (value.length < 10) {
                    if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción debe tener al menos 10 caracteres.');
                    return false;
                }

                if (value.length > 1000) {
                    if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'La descripción no puede exceder 1000 caracteres.');
                    return false;
                }

                if (!regex.test(value)) {
                    if (showError) setFieldError(rentalDescripcion, rentalDescripcionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
                    return false;
                }

                return true;
            }

            function calculateRentalEstimate() {
                const classroomId = rentalClassroom.value;
                const startTime = rentalStartTime.value;
                const endTime = rentalEndTime.value;
                const periodType = rentalPeriodType.value;
                const hours = calculateHours(startTime, endTime);

                detectedPeriodLabel.textContent = periodLabelFromValue(periodType);
                detectedHoursLabel.textContent = `${hours.toFixed(2)} horas`;

                if (!classroomId || !hours || !periodType || !tarifasPorSalon[classroomId]) {
                    rentalEstimatedTotal.textContent = formatMoney(0);
                    rentalEstimatedTotalInput.value = '0.00';
                    return 0;
                }

                const data = tarifasPorSalon[classroomId];
                const area = toNumber(data.area);
                const periodRates = getPeriodRateData(classroomId, periodType);

                let total = area * periodRates.diaria;
                if (rentalUtilities.checked) total += toNumber(data.utilidades) * hours;
                if (rentalElectricity.checked) total += toNumber(data.electricidad) * hours;
                if (rentalWater.checked) total += toNumber(data.agua) * hours;

                rentalEstimatedTotal.textContent = formatMoney(total);
                rentalEstimatedTotalInput.value = total.toFixed(2);
                return total;
            }

            function isRentalFormValid() {
                const validTimes =
                    rentalStartTime.value &&
                    rentalEndTime.value &&
                    timeToMinutes(rentalEndTime.value) > timeToMinutes(rentalStartTime.value);

                const validResponsable = validateResponsable(false);
                const validDescription = validateDescripcion(false);
                const validServices = hasSelectedServices();

                toggleServicesError(!validServices);

                return !!(
                    rentalClassroom.value &&
                    rentalDate.value &&
                    rentalPeriodType.value &&
                    validTimes &&
                    validResponsable &&
                    validDescription &&
                    validServices
                );
            }

            function updateRentalSaveState() {
                saveRentalBtn.disabled = !isRentalFormValid();
            }

            function toggleMonthFilter() {
                monthFilterWrapper.classList.toggle('d-none', reportType.value !== 'monthly');
            }

            function applyTableFilters() {
                const type = reportType.value;
                const month = reportMonth.value;
                const year = reportYear.value;
                const classroom = filterClassroom.value;

                let visibleRows = 0;
                let totalAmount = 0;

                [...facilityCostTableBody.querySelectorAll('tr')].forEach((row) => {
                    const rowMonth = row.dataset.month;
                    const rowYear = row.dataset.year;
                    const rowClassroom = row.dataset.classroom;

                    const matchesType = type === 'annual'
                        ? rowYear === year
                        : (rowYear === year && rowMonth === month);

                    const matchesClassroom = classroom === 'all' ? true : rowClassroom === classroom;
                    const shouldShow = matchesType && matchesClassroom;

                    row.style.display = shouldShow ? '' : 'none';

                    if (shouldShow) {
                        visibleRows += 1;
                        totalAmount += parseMoney(row.querySelector('td:nth-child(6)').textContent);
                    }
                });

                facilityCostGrandTotal.textContent = formatMoney(totalAmount);
                const hasRows = visibleRows > 0;
                facilityCostTable.classList.toggle('d-none', !hasRows);
                facilityCostEmptyState.classList.toggle('d-none', hasRows);
            }

            function createServiceBadges(services) {
                return services.map(service => `<span class="badge rounded-0 px-3 py-2 me-2 mb-1 service-badge-table">${service}</span>`).join('');
            }

            function formatDisplayDate(dateValue) {
                const date = new Date(`${dateValue}T12:00:00`);
                const months = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
                return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
            }

            function formatDisplayTime24To12(timeValue) {
                const [hourStr, minuteStr] = timeValue.split(':');
                let hour = Number(hourStr);
                const suffix = hour >= 12 ? 'PM' : 'AM';
                hour = hour % 12 || 12;
                return `${String(hour).padStart(2, '0')}:${minuteStr} ${suffix}`;
            }

            function appendRentalRow() {
                const classroomId = rentalClassroom.value;
                const eventDate = rentalDate.value;
                const startTime = rentalStartTime.value;
                const endTime = rentalEndTime.value;
                const periodLabel = periodLabelFromValue(rentalPeriodType.value);

                const services = [];
                if (rentalUtilities.checked) services.push('Utilidades');
                if (rentalElectricity.checked) services.push('Electricidad');
                if (rentalWater.checked) services.push('Agua');

                const total = calculateRentalEstimate();
                const rowId = `cost-row-${String(nextEntryId).padStart(3, '0')}`;
                nextEntryId += 1;

                const row = document.createElement('tr');
                row.dataset.entryId = rowId;
                row.dataset.date = eventDate;
                row.dataset.month = String(new Date(`${eventDate}T12:00:00`).getMonth() + 1);
                row.dataset.year = String(new Date(`${eventDate}T12:00:00`).getFullYear());
                row.dataset.classroom = classroomId;

                row.innerHTML = `
                    <td>${formatDisplayDate(eventDate)}</td>
                    <td>${classroomId}</td>
                    <td>${formatDisplayTime24To12(startTime)} - ${formatDisplayTime24To12(endTime)}</td>
                    <td>${periodLabel}</td>
                    <td>${createServiceBadges(services)}</td>
                    <td class="text-end fw-semibold">${formatMoney(total)}</td>
                    <td class="text-center">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger delete-cost-row-btn"
                            data-entry-id="${rowId}"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteCostEntryModal"
                        >
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                facilityCostTableBody.appendChild(row);
                bindDeleteButtons();
                applyTableFilters();
            }

            function bindDeleteButtons() {
                deleteButtons().forEach((btn) => {
                    btn.onclick = () => {
                        selectedRowToDelete = btn.closest('tr');
                    };
                });
            }

            function buildTimeOptions(selectElement, startHour, startMinute, endHour, endMinute) {
                selectElement.innerHTML = '<option value="" selected disabled>Seleccionar</option>';

                let current = (startHour * 60) + startMinute;
                const end = (endHour * 60) + endMinute;

                while (current <= end) {
                    const hour = Math.floor(current / 60);
                    const minute = current % 60;
                    const value = `${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
                    const label = formatDisplayTime24To12(value);

                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = label;
                    selectElement.appendChild(option);

                    current += 15;
                }
            }

            const configureHasChanges = () => configureDirty;
            const rentalHasChanges = () => rentalDirty;

            function resetConfigureFormState() {
                configureRatesForm.reset();
                setSelectionByList(['Cancha CM']);
                loadRatesIntoForm('Cancha CM');
                configureRatesForm.classList.remove('was-validated');
                configureDirty = false;
                updateConfigureSaveState();
                updateConfigPreview();
            }

            function resetRentalFormState() {
                addRentalForm.reset();
                addRentalForm.classList.remove('was-validated');
                rentalDirty = false;

                toggleServicesError(false);
                clearFieldError(rentalResponsable, rentalResponsableError);
                clearFieldError(rentalDescripcion, rentalDescripcionError);

                rentalEstimatedTotal.textContent = formatMoney(0);
                rentalEstimatedTotalInput.value = '0.00';
                detectedPeriodLabel.textContent = '—';
                detectedHoursLabel.textContent = '0.00 horas';

                updateRentalSaveState();
            }

            $('selectAllClassroomsBtn').addEventListener('click', () => setSelectionByList(classroomIds));
            $('selectAcademicClassroomsBtn').addEventListener('click', () => setSelectionByList(academicRooms));
            $('selectLateralClassroomsBtn').addEventListener('click', () => setSelectionByList(lateralRooms));
            $('clearClassroomsSelectionBtn').addEventListener('click', () => setSelectionByList([]));

            configClassroomChecks.forEach((check) => {
                check.addEventListener('change', () => {
                    const selected = getSelectedClassrooms();
                    if (selected.length === 1) loadRatesIntoForm(selected[0]);
                    configureDirty = true;
                    updateConfigureSaveState();
                });
            });

            moneyInputs.forEach((input) => {
                input.addEventListener('input', () => {
                    sanitizeMoneyInput(input);
                    configureDirty = true;
                    updateConfigPreview();
                    updateConfigureSaveState();
                });

                input.addEventListener('blur', () => {
                    normalizeMoneyInput(input);
                    updateConfigPreview();
                    updateConfigureSaveState();
                });

                input.addEventListener('keydown', (e) => {
                    if (['e', 'E', '+', '-'].includes(e.key)) e.preventDefault();
                });
            });

            [rentalClassroom, rentalDate, rentalStartTime, rentalEndTime, rentalPeriodType].forEach((el) => {
                el.addEventListener('change', () => {
                    rentalDirty = true;
                    calculateRentalEstimate();
                    updateRentalSaveState();
                });
            });

            rentalDescripcion.addEventListener('input', () => {
                rentalDirty = true;

                rentalDescripcion.value = rentalDescripcion.value
                    .replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]/g, '')
                    .slice(0, 1000);

                validateDescripcion(true);
                updateRentalSaveState();
            });

            rentalDescripcion.addEventListener('blur', () => {
                validateDescripcion(true);
                updateRentalSaveState();
            });

            rentalServiceChecks.forEach((el) => {
                el.addEventListener('change', () => {
                    rentalDirty = true;
                    calculateRentalEstimate();
                    toggleServicesError(!hasSelectedServices());
                    updateRentalSaveState();
                });
            });

            rentalResponsable.addEventListener('input', () => {
                rentalDirty = true;

                let cleanedValue = rentalResponsable.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');
                cleanedValue = cleanedValue.replace(/\s{2,}/g, ' ');
                cleanedValue = capitalizeWords(cleanedValue);

                if (cleanedValue.length > 60) {
                    cleanedValue = cleanedValue.slice(0, 60);
                }

                rentalResponsable.value = cleanedValue;
                validateResponsable(true);
                updateRentalSaveState();
            });

            rentalResponsable.addEventListener('blur', () => {
                rentalResponsable.value = capitalizeWords(rentalResponsable.value.trim());
                validateResponsable(true);
                updateRentalSaveState();
            });

            [reportType, reportMonth, reportYear, filterClassroom].forEach((el) => {
                el.addEventListener('change', () => {
                    toggleMonthFilter();
                    applyTableFilters();
                });
            });

            saveRatesBtn.addEventListener('click', () => {
                configureRatesForm.classList.add('was-validated');
                if (!isConfigureFormValid()) return updateConfigureSaveState();

                getSelectedClassrooms().forEach((classroomId) => {
                    tarifasPorSalon[classroomId] = {
                        area: toNumber(configAreaSalon.value),
                        utilidades: toNumber(configUtilidades.value),
                        electricidad: toNumber(configElectricidad.value),
                        agua: toNumber(configAgua.value),
                        diaria1: toNumber(configDiaria1.value),
                        semanal1: toNumber(configSemanal1.value),
                        mensual1: toNumber(configMensual1.value),
                        diaria2: toNumber(configDiaria2.value),
                        semanal2: toNumber(configSemanal2.value),
                        mensual2: toNumber(configMensual2.value),
                        diaria3: toNumber(configDiaria3.value),
                        semanal3: toNumber(configSemanal3.value),
                        mensual3: toNumber(configMensual3.value),
                    };
                });

                configureDirty = false;
                calculateRentalEstimate();
                toasts.ratesSaved.show();
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
            });

            saveRentalBtn.addEventListener('click', () => {
                addRentalForm.classList.add('was-validated');

                const responsableOk = validateResponsable(true);
                const descripcionOk = validateDescripcion(true);
                const servicesOk = hasSelectedServices();
                toggleServicesError(!servicesOk);

                if (!(isRentalFormValid() && responsableOk && descripcionOk && servicesOk)) {
                    updateRentalSaveState();
                    return;
                }

                appendRentalRow();
                rentalDirty = false;
                toasts.rentalSaved.show();
                bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
                resetRentalFormState();
            });

            confirmDeleteCostEntryBtn.addEventListener('click', () => {
                if (selectedRowToDelete) {
                    selectedRowToDelete.remove();
                    selectedRowToDelete = null;
                    applyTableFilters();
                    toasts.deleteEntry.show();
                }
                bootstrap.Modal.getOrCreateInstance($('deleteCostEntryModal')).hide();
            });

            document.querySelectorAll('.configure-cancel-btn, .configure-close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (configureHasChanges()) {
                        bootstrap.Modal.getOrCreateInstance($('confirmCancelConfigureModal')).show();
                    } else {
                        bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
                    }
                });
            });

            document.querySelectorAll('.rental-cancel-btn, .rental-close-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (rentalHasChanges()) {
                        bootstrap.Modal.getOrCreateInstance($('confirmCancelRentalModal')).show();
                    } else {
                        bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
                    }
                });
            });

            $('confirmCancelConfigureBtn').addEventListener('click', () => {
                configureDirty = false;
                bootstrap.Modal.getOrCreateInstance($('confirmCancelConfigureModal')).hide();
                bootstrap.Modal.getOrCreateInstance(configureRatesModal).hide();
                resetConfigureFormState();
            });

            $('confirmCancelRentalBtn').addEventListener('click', () => {
                rentalDirty = false;
                bootstrap.Modal.getOrCreateInstance($('confirmCancelRentalModal')).hide();
                bootstrap.Modal.getOrCreateInstance(addRentalModal).hide();
                resetRentalFormState();
            });

            configureRatesModal.addEventListener('show.bs.modal', resetConfigureFormState);
            addRentalModal.addEventListener('show.bs.modal', resetRentalFormState);

            buildTimeOptions(rentalStartTime, 7, 30, 21, 30);
            buildTimeOptions(rentalEndTime, 7, 45, 21, 45);

            bindDeleteButtons();
            toggleMonthFilter();
            resetConfigureFormState();
            resetRentalFormState();
            applyTableFilters();
        });
    </script>
</x-layout>
