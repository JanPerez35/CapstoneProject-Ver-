@php use Illuminate\Support\Str; @endphp
@php use Carbon\Carbon; @endphp
<x-layout title="Gestión de Costos de Instalaciones">
    <x-navbar></x-navbar>
    @vite('resources/js/facility_management.js')
    <div class="container py-4">

        @if(session('rates_saved'))
            <div id="ratesSavedAutoTrigger"></div>
        @endif

        @if(session('entry_deleted'))
            <div id="deleteEntryAutoTrigger"></div>
        @endif

        @if(session('rental_saved'))
            <div id="rentalSavedAutoTrigger"></div>
        @endif

        @if(session('mock_imported'))
            <div id="mockImportAutoTrigger"></div>
        @endif

        @if(session('classroom_added'))
            <div id="classroomAddedAutoTrigger"></div>
        @endif

        @if(session('classrooms_deleted'))
            <div id="classroomsDeletedAutoTrigger"></div>
        @endif

        {{-- Page header and cost estimate notice --}}
        <div class="mb-4">
            <h1 class="fw-bold mb-1">Gestión de Costos Operacionales</h1>
            <p class="text mb-0">Aquí puedes ver, filtrar, agregar y modificar eventos estimados, así como exportar estimaciones
                de costos operacionales por áreas específicas dentro del Coliseo Rafael A. Mangual.</p>
        </div>

        {{-- Notice --}}
        <div class="alert bg-warning-subtle text-warning-emphasis rounded-0 border-0 shadow-sm mb-4 px-4 py-4"
             id="costEstimateNotice">
            <div class="d-flex align-items-start gap-3">
                <div>
                    <strong>Aviso importante:</strong> Los costos mostrados
                    en esta página son
                    <strong>estimaciones</strong> calculadas según las tarifas configuradas, el área,
                    el horario y los servicios seleccionados. Las tarifas están sujetas a cambios y deberán ser
                    ratificadas por el área administrativa
                    para ser consideradas como definitivas.

                    <br><br>
                    Los registros de estimados de costos operacionales permanecerán almacenados en el sistema durante un
                    período de <strong>3 años </strong> a partir de su fecha de creación. Luego de ese período,
                    serán eliminados automáticamente del sistema.

                    <br><br>
                    <strong>Nota normativa:</strong>
                    El sistema sigue como referencia la Certificación 20-21-090 <strong>Procedimiento para el Préstamo y Uso de Instalaciones Físicas
                    del Recinto Universitario de Mayagüez</strong>
                    para la estructura de tarifas y
                    períodos.
                    Sin embargo, los horarios de días no laborables fueron ajustados operacionalmente en el sistema ya
                    que se esta utilizando
                    las normas/certificaciones específicas del Departamento de Kinesiología;
                    por tanto, los períodos no laborables se manejan de 8:00 AM a 9:30 PM.
                </div>
            </div>
        </div>

        {{-- Buttons --}}
        <div id="facilityActionButtons" class="d-flex flex-wrap gap-3 mb-4">
            {{-- Only Super Administrator can add, edit or delete configurations --}}
            @if(auth()->user()->role === 'Super Administrador')
                <span
                    data-bs-toggle="tooltip"
                    data-bs-placement="left"
                    data-bs-custom-class="custom-tooltip"
                    data-bs-title="Configura tarifas de áreas, medidas de áreas y costos por período"
                >
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
            </span>
            @endif

            {{-- Add a new event record --}}
            <span
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-custom-class="custom-tooltip"
                data-bs-title="Registra eventos y calcula costos estimados."
            >
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
            </span>

            {{-- Export via CSV --}}
            <span
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-custom-class="custom-tooltip"
                data-bs-title="Solo incluye la información según los filtros aplicados"
            >
                    <a href="{{ route('facility.export.csv') }}"
                       data-base-url="{{ route('facility.export.csv') }}"
                       id="downloadCsvBtn"
                       class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                            <i class="bi bi-download"></i>
                            Exportar a CSV
                        </a>
                </span>

            {{-- Export via PDF --}}
            <span
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-custom-class="custom-tooltip"
                data-bs-title="Solo incluye la información según los filtros aplicados"
            >
                    <a href="{{ route('facility.export.pdf') }}"
                       data-base-url="{{ route('facility.export.pdf') }}"
                       id="downloadPdfBtn"
                       class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold">
                            <i class="bi bi-download"></i>
                            Exportar a PDF
                        </a>
                </span>

            {{-- EventFlow import bridge --}}
            <span
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-custom-class="custom-tooltip"
                data-bs-title="Importa eventos desde EventFlow"
            >
            <form method="POST" action="{{ route('facility.import.eventflow') }}" class="d-inline">
                @csrf
                <button
                    type="submit"
                    class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
                >
                    <i class="bi bi-cloud-arrow-down"></i>
                    Importar Data de EventFlow
                </button>
            </form>
            </span>
        </div>

        {{-- Search and filters --}}
        <div class="mb-4">
            <form id="facilityCostFilterForm" method="GET" action="{{ route('facility_management') }}" class="mb-4">
                @csrf

                <div class="row mb-4 g-3">
                    <div class="col-md-10">
                        <div class="input-group search-group">
                                <span class="input-group-text bg-white border-0">
                                    <i class="bi bi-search"></i>
                                </span>

                            {{-- Search bar --}}
                            <input
                                type="text"
                                id="facilitySearch"
                                name="search"
                                class="form-control border-0"
                                placeholder="Buscar por nombre del responsable o por fecha del evento..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-2 d-grid">
                        <button type="button" id="searchFacilityBtn" class="btn btn-success">
                            Buscar
                        </button>
                    </div>

                    {{-- Report type filter --}}
                    <div class="col-md-3">
                        <select id="reportType" name="report_type" class="form-select border-2 border-dark">
                            <option value="" {{ $reportType === '' ? 'selected' : '' }}>Tipo de Informe</option>
                            <option value="monthly" {{ $reportType === 'monthly' ? 'selected' : '' }}>Mensual</option>
                            <option value="annual" {{ $reportType === 'annual' ? 'selected' : '' }}>Anual</option>
                        </select>
                    </div>

                    {{-- Month filter --}}
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

                    {{-- Yearly filter --}}
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

                    {{-- Area filter --}}
                    <div class="col-md-3">
                        <select id="filterClassroom" name="filter_classroom" class="form-select border-2 border-dark">
                            <option value="" {{ ($filterClassroom ?? '') === '' ? 'selected' : '' }}>Área</option>
                            @foreach ($allFacilityCosts as $cost)
                                <option
                                    value="{{ $cost->classroom_name }}" {{ $filterClassroom === $cost->classroom_name ? 'selected' : '' }}>
                                    {{ $cost->classroom_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Period type filter --}}
                    <div class="col-md-3">
                        <select id="filterPeriodType" name="filter_period_type"
                                class="form-select border-2 border-dark">
                            <option value="">Tipo de Período</option>
                            <option value="Laborable">Laborable</option>
                            <option value="No laborable sábado">No laborable sábado</option>
                            <option value="No laborable domingo o festivo">No laborable domingo o festivo</option>
                        </select>
                    </div>

                    {{-- Rate mode filter --}}
                    <div class="col-md-3">
                        <select id="filterRateMode" name="filter_rate_mode" class="form-select border-2 border-dark">
                            <option value="">Tipo de Tarifa</option>
                            <option value="Diario">Diario</option>
                            <option value="Semanal">Semanal</option>
                            <option value="Mensual">Mensual</option>
                        </select>
                    </div>

                    {{-- Services filter --}}
                    <div class="col-md-3">
                        <select id="filterServices" name="filter_services" class="form-select border-2 border-dark">
                            <option value=""> Tipo de Servicios</option>
                            <option value="Utilidades">Utilidades</option>
                            <option value="Electricidad">Electricidad</option>
                            <option value="Agua">Agua</option>
                        </select>
                    </div>

                    {{-- Clean filters --}}
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary" id="clearFacilityFilters">
                            Limpiar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Data table --}}
        <div class="card border-dark border-2 shadow-sm rounded-2 overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                    <div>
                        <h2 class="fw-bold mb-1">Uso de Áreas y Costos Estimados</h2>
                        <p class="text-muted mb-0">Organización de eventos y estimaciones de costos operacionales</p>
                        <p class="text-success fw-bold mb-0">Desliza para ver más información <i class="bi bi-arrow-right-circle"></i>
                        </p>
                    </div>

                    {{-- Actions leyend --}}
                    <div class="d-flex align-items-start gap-3 me-3 ms-lg-0 ms-auto">
                        <span class="fw-semibold">Leyenda:</span>

                        <div class="d-flex flex-column align-items-start">
                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-pencil text-primary"></i> Editar Evento
                            </span>

                            <span class="d-flex align-items-center gap-2">
                                  <i class="bi bi-calendar-event text-warning"></i> Modificar Días
                            </span>

                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-diagram-3 text-success"></i> Crear Evento <br>Relacionado
                            </span>

                            <span class="d-flex align-items-center gap-2">
                                <i class="bi bi-trash text-danger"></i> Eliminar Evento
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table columns headers --}}
            <div class="table-fit-wrapper mt-0" style="max-height: 620px; overflow: auto;">
                <table class="table align-middle mb-0" id="facilityCostTable">
                    <thead class="table-light position-sticky top-0" style="z-index: 20;">
                    <tr>
                        <th class="fw-bold">
                            Fecha inicial del evento <br>
                            <small>(Día Mes Año)</small>
                        </th>

                        <th class="fw-bold">
                            Fecha final del evento <br>
                            <small>(Día Mes Año)</small>
                        </th>
                        <th class="fw-bold">Responsable</th>
                        <th class="fw-bold area-col">Área</th>
                        <th class="fw-bold">Descripción</th>
                        <th class="fw-bold">Hora</th>
                        <th class="fw-bold">Periodo</th>
                        <th class="fw-bold">
                            Tipo <br>
                            <small>de Tarifa</small>
                        </th>
                        <th class="fw-bold">Servicios</th>
                        <th class="fw-bold text-end">Costo Total</th>
                        <th class="text-center action-header-icon px-2" style="width: 58px; min-width: 58px;">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                  data-bs-title="Editar Evento">
                                <i class="bi bi-pencil fs-5 text-primary"></i>
                            </span>
                        </th>

                        <th class="text-center action-header-icon px-2" style="width: 58px; min-width: 58px;">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                  data-bs-title="Modificar Días">
                                <i class="bi bi-calendar-event fs-5 text-warning"></i>
                            </span>
                        </th>

                        <th class="text-center action-header-icon px-2" style="width: 58px; min-width: 58px;">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                  data-bs-title="Crear Evento Relacionado">
                                <i class="bi bi-diagram-3 fs-5 text-success"></i>
                            </span>
                        </th>

                        <th class="text-center action-header-icon px-2" style="width: 58px; min-width: 58px;">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                  data-bs-title="Eliminar evento">
                                <i class="bi bi-trash fs-5 text-danger"></i>
                            </span>
                        </th>
                    </tr>
                    </thead>

                    <tbody id="facilityCostTableBody">
                    @forelse ($eventGroups as $event)
                        @php
                            $subItems = collect($event->sub_items ?? [$event]);
                            $parent = $subItems->firstWhere('is_group_parent', true) ?? $event;
                            $children = $subItems->where('id', '!=', $parent->id);
                            $relatedAreas = $children->where('sub_event_type', 'related_area');
                            $customDaysByParent = $children
                                ->where('sub_event_type', 'custom_day')
                                ->groupBy('custom_parent_item_id');
                            $knownCustomParentIds = $relatedAreas
                                ->pluck('id')
                                ->push($parent->id)
                                ->map(fn ($id) => (string) $id);
                            $orphanCustomDays = $customDaysByParent
                                ->except($knownCustomParentIds)
                                ->flatten();
                            $groupKey = $parent->event_group_id ?: 'single-' . $parent->id;
                        @endphp

                        {{-- Group header --}}
                        <tr class="event-group-header" data-group-key="{{ $groupKey }}" data-group-header="1">
                            <td colspan="14">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                            <span class="fw-bold">
                                                Evento principal #{{ $parent->id }}
                                            </span>

                                        <span class="text-muted ms-2">
                                               {{ \Illuminate\Support\Str::title(
                                                    Carbon::parse($parent->event_date)
                                                        ->locale('es')
                                                        ->translatedFormat('j F Y')
                                                ) }}
                                                <i class="bi bi-arrow-right"></i>
                                               {{ \Illuminate\Support\Str::title(
                                                    Carbon::parse($parent->end_date ?? $parent->event_date)
                                                        ->locale('es')
                                                        ->translatedFormat('j F Y')
                                                ) }}
                                            </span>

                                        @php
                                            $relatedAreasCount = $children->where('sub_event_type', 'related_area')->count();
                                            $modificationsCount = $children->where('sub_event_type', 'custom_day')->count();
                                        @endphp

                                        @if($relatedAreasCount > 0)
                                            <span class="label-badge label-badge-on-header bg-info-subtle text-info-emphasis ms-2">
                                                    {{ $relatedAreasCount }} área(s) relacionada(s)
                                                </span>
                                        @endif

                                        @if($modificationsCount > 0)
                                            <span class="label-badge label-badge-on-header bg-warning-subtle text-warning-emphasis ms-2">
                                                    {{ $modificationsCount }} modificación(es)
                                                </span>
                                        @endif

                                        @if($children->count() === 0)
                                            <span class="label-badge label-badge-on-header bg-secondary-subtle text-secondary-emphasis ms-2">
                                                    Sin elementos relacionados
                                                </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Parent row --}}
                        @include('partials.facility_event_row', [
                            'item' => $parent,
                            'rowType' => 'parent',
                            'groupKey' => $groupKey
                        ])

                        {{-- Parent custom-day modifications --}}
                        @foreach ($customDaysByParent->get($parent->id, collect()) as $child)
                            @include('partials.facility_event_row', [
                                'item' => $child,
                                'rowType' => 'child',
                                'groupKey' => $groupKey
                            ])
                        @endforeach

                        @foreach ($orphanCustomDays as $child)
                            @include('partials.facility_event_row', [
                                'item' => $child,
                                'rowType' => 'child',
                                'groupKey' => $groupKey
                            ])
                        @endforeach

                        {{-- Related areas and their custom-day modifications --}}
                        @foreach ($relatedAreas as $relatedArea)
                            @include('partials.facility_event_row', [
                                'item' => $relatedArea,
                                'rowType' => 'child',
                                'groupKey' => $groupKey
                            ])

                            @foreach ($customDaysByParent->get($relatedArea->id, collect()) as $child)
                                @include('partials.facility_event_row', [
                                    'item' => $child,
                                    'rowType' => 'child',
                                    'groupKey' => $groupKey
                                ])
                            @endforeach
                        @endforeach

                        <tr class="event-group-total" data-group-key="{{ $groupKey }}">
                            <td colspan="9" class="text-end fw-bold text-muted bg-light">
                                Total del evento
                            </td>

                            <td class="text-end fw-bold bg-light" style="min-width: 140px;">
                                ${{ number_format($event->group_total, 2) }}
                            </td>

                            <td colspan="4" class="bg-light"></td>
                        </tr>

                        {{-- Space between event groups --}}
                        <tr class="event-group-spacer" data-group-key="{{ $groupKey }}" data-group-spacer="1">
                            <td colspan="14"></td>
                        </tr>
                    @empty
                    @endforelse
                    </tbody>

                    <tfoot class="table-light">
                    <tr>
                        <th colspan="9" class="fw-bold text-end">Total estimado del período</th>
                        <th class="text-end fw-bold" id="facilityCostGrandTotal" style="min-width: 140px;">
                            ${{ number_format($grandTotal, 2) }}</th>
                        <th colspan="4"></th>
                    </tr>
                    </tfoot>
                </table>

                {{-- Filters does not return anything --}}
                <div id="facilityCostEmptyState" class="card border-0 shadow-sm rounded-0 d-none container mb-4">
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-currency-dollar fs-1 text-muted"></i>
                        <h4 class="fw-bold mb-2">No hay costos para mostrar</h4>
                        <p class="text-muted mb-0">Prueba añadiendo un evento o cambiando los parámetros de la
                            búsqueda.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <nav aria-label="Paginación de costos" class="mt-4 d-flex justify-content-center align-items-center flex-wrap gap-3">
        <p class="text-muted small mb-0" id="facilityCostPaginationSummary"></p>
        <ul class="pagination mb-0" id="facilityCostPagination"></ul>
    </nav>

    {{--
        Configure Rates Modal

        Allows a Super Administrator to configure rate values for one or more areas.
        The modal includes:
        - area selection shortcuts
        - add/discard area controls
        - base area information
        - hourly service costs
        - daily, weekly, and monthly rates for each period type
        - live preview totals handled by JavaScript
    --}}
    <div class="modal fade" id="configureRatesModal" tabindex="-1" aria-labelledby="configureRatesModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-lg-down modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="configureRatesModalLabel">Configurar Tarifas</h4>
                        <p class="text-muted mb-1">
                            Puedes configurar un área o varias áreas con los mismos valores.
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
                                <label class="form-label fw-semibold fs-5 mb-3">
                                    Áreas a configurar <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 justify-content-start ps-3">
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

                                    <button type="button" class="btn btn-success btn-sm"
                                            id="openAddClassroomModalBtn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addClassroomModal">
                                        <i class="bi bi-plus-lg me-1"></i>Agregar Área
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger btn-sm"
                                            id="openDiscardSelectedClassroomsBtn"
                                            disabled>
                                        <i class="bi bi-trash me-1"></i>Descartar Área(s)
                                    </button>
                                </div>

                                <div class="row g-2" id="configClassroomGroup">
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

                            <div id="configClassroomSelectionError" class="text-danger small d-none mt-2 mb-4 ps-3">
                                Debes seleccionar al menos un área para configurar tarifas.
                            </div>

                            <hr class="my-4">

                            <div class="mb-4">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-4 px-4 py-3"
                                     id="configureRatesHelpNotice">
                                    <div class="d-flex align-items-start gap-3">
                                        <div>
                                            <strong>Aviso
                                                importante:</strong>
                                            Si el área ya fue configurado anteriormente, se mostrarán sus tarifas
                                            guardadas para que puedas modificarlas.
                                            Si el área nunca ha sido configurado, los campos aparecerán vacíos con
                                            ejemplos de como llenarlos como referencia.
                                        </div>
                                    </div>
                                </div>
                                <h5 class="mb-3 section-title-match">Información base del área</h5>

                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-lg-5">
                                        <div class="service-option-card">
                                            <label for="configClassroomArea" class="form-label fw-semibold">
                                                Medida del área <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-lg money-input-group">
                                                <span class="input-group-text">ft²</span>
                                                <input
                                                    type="text"
                                                    class="form-control"
                                                    id="configClassroomArea"
                                                    name="classroom_space"
                                                    placeholder="Ejemplo: 1200.00"
                                                    required
                                                >
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Ingresa la medida en pies cuadrados (ft²). Solo números y hasta 2 lugares
                                                decimales. El máximo permitido es 25,000,000.00 ft².
                                            </small>
                                            <div class="invalid-feedback d-block" id="configClassroomAreaError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    @foreach ([
                                        ['id' => 'configUtilities', 'label' => 'Utilidades', 'name' => 'supply_cost'],
                                        ['id' => 'configElectricity', 'label' => 'Electricidad', 'name' => 'electricity_cost'],
                                        ['id' => 'configWater', 'label' => 'Agua', 'name' => 'water_cost'],
                                    ] as $campo)
                                        <div class="col-12 col-lg-4">
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
                                                        placeholder="Ejemplo: 25.00"
                                                        required
                                                    >
                                                </div>

                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 lugares decimales. El máximo permitido es
                                                    $500.00
                                                </small>

                                                <div class="invalid-feedback d-block"
                                                     id="{{ $campo['id'] }}Error"></div>

                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="form-label fw-semibold fs-5 mb-3">Tarifas por período</h5>

                                <div class="row g-3">
                                    @php
                                        $periodos = [
                                            [
                                                'titulo' => 'Período laborable',
                                                'texto' => 'Lunes a viernes, 7:30 AM a 4:30 PM',
                                                'sufijo' => '1',
                                                'diario' => '0.21',
                                                'semanal' => '0.86',
                                                'mensual' => '2.74',
                                            ],
                                            [
                                                'titulo' => 'No laborable sábado',
                                                'texto' => 'Lunes a sábado, 8:00 AM a 9:30 PM',
                                                'sufijo' => '2',
                                                'diario' => '0.26',
                                                'semanal' => '1.03',
                                                'mensual' => '3.29',
                                            ],
                                            [
                                                'titulo' => 'No laborable domingo o festivo',
                                                'texto' => 'Lunes a viernes, domingo o festivo, 8:00 AM a 9:30 PM',
                                                'sufijo' => '3',
                                                'diario' => '0.31',
                                                'semanal' => '0.00',
                                                'mensual' => '0.00',
                                            ],
                                        ];
                                    @endphp

                                    @foreach ($periodos as $periodo)
                                        <div class="col-12 col-lg-4">
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
                                                        placeholder="Ejemplo: 25.00"
                                                        required
                                                    >
                                                </div>
                                                <small class="text-muted d-block mt-2  mb-2">
                                                    Escribe solo números y hasta 2 lugares decimales. El máximo permitido es
                                                    $500.00.
                                                </small>

                                                <div class="invalid-feedback d-block mb-3"
                                                     id="configDaily{{ $periodo['sufijo'] }}Error"></div>

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
                                                        placeholder="Ejemplo: 25.00"
                                                        required
                                                    >
                                                </div>
                                                <small class="text-muted d-block mt-2 mb-2">
                                                    Escribe solo números y hasta 2 lugares decimales. El máximo permitido es
                                                    $500.00.
                                                </small>

                                                <div class="invalid-feedback d-block mb-3"
                                                     id="configWeekly{{ $periodo['sufijo'] }}Error"></div>

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
                                                        placeholder="Ejemplo: 25.00"
                                                        required
                                                    >
                                                </div>

                                                <small class="text-muted d-block mt-2">
                                                    Escribe solo números y hasta 2 lugares decimales. El máximo permitido es
                                                    $500.00.
                                                </small>

                                                <div class="invalid-feedback d-block"
                                                     id="configMonthly{{ $periodo['sufijo'] }}Error"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa período laborable</div>
                                        <div class="fw-bold text-success fs-4" id="configWorkdayPreview">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4">
                                    <div class="total-hour-box rounded-4 p-4 h-100">
                                        <div class="fw-bold fs-6 mb-2">Vista previa no laborable sábado</div>
                                        <div class="fw-bold text-success fs-4" id="configSaturdayPreview">$0.00</div>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-4">
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

    {{--
        Add Event Modal

        Creates a new parent facility usage event.
        The event is limited to one main area. If the same activity uses multiple
        areas, the user should create the main event first and then add related
        area sub-events from the table.
    --}}
    <div class="modal fade" id="addRentalModal" tabindex="-1" aria-labelledby="addRentalModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-lg-down modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="addRentalModalLabel">Agregar Evento</h4>
                        <p class="text-muted mb-1">
                            Registra un evento y calcula su costo estimado de uso.
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

                        <div class="row g-2 mb-2 justify-content-center">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-1 px-3 py-2">
                                    <strong>Aviso:</strong>
                                    Solo se puede seleccionar <strong>una (1) área</strong> por evento.
                                    Las áreas disponibles corresponden exclusivamente a instalaciones internas del
                                    Coliseo Rafael Mangual.
                                    Si un mismo evento utiliza más de un área, primero registre el área principal y
                                    luego utilice la opción
                                    <strong>Crear Evento relacionado</strong> desde la tabla.
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6">
                                <label for="rentalClassroom" class="form-label fw-semibold">
                                    Área <span class="text-danger">*</span>
                                </label>
                                <select id="rentalClassroom" name="classroom" class="form-select form-select-lg"
                                        required>
                                    <option value="" selected>Selecciona el área</option>
                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <option value="{{ $salon }}">{{ $salon }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block fst-italic">
                                    Seleccione el área donde se llevará a cabo el evento.
                                </small>
                                <div class="invalid-feedback" id="rentalClassroomError"></div>
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
                                    minlength="8"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Incluya nombre y apellido. Escriba entre 8 y 40 caracteres. Solo letras y espacios.
                                </small>
                                <div class="invalid-feedback" id="rentalResponsibleError"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="rentalDescription" class="form-label fw-semibold">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="rentalDescription"
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Descripción del evento"
                                minlength="10"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 250 caracteres. Solo letras, números, espacios, puntos, comas y guiones.
                            </small>
                            <div class="invalid-feedback" id="rentalDescriptionError">
                                La descripción debe tener entre 10 y 250 caracteres y solo usar caracteres permitidos.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Servicios <span class="text-danger">*</span>
                            </label>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input rental-service-check"
                                                type="checkbox"
                                                id="rentalUtilities"
                                                value="utilities"
                                                name="services[]"
                                            >
                                            <label class="form-check-label fw-semibold" for="rentalUtilities">
                                                Utilidades
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento incluye costos generales de utilidades asociados al
                                            uso del área.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input rental-service-check"
                                                type="checkbox"
                                                id="rentalElectricity"
                                                value="electricity"
                                                name="services[]"
                                            >
                                            <label class="form-check-label fw-semibold" for="rentalElectricity">
                                                Electricidad
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo eléctrico.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input rental-service-check"
                                                type="checkbox"
                                                id="rentalWater"
                                                value="water"
                                                name="services[]"
                                            >
                                            <label class="form-check-label fw-semibold" for="rentalWater">
                                                Agua
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo de agua.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="invalid-feedback d-block d-none mt-2" id="servicesRequiredMessage">
                                Debes seleccionar al menos un servicio.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-2 px-3 py-2">
                                    <strong>Aviso importante:</strong>
                                    Si el evento combina días u horarios laborables con días u horarios no laborables,
                                    seleccione el período no laborable correspondiente:
                                    <strong>No laborable sábado</strong> o
                                    <strong>No laborable domingo o festivo</strong>.

                                    Las horas seleccionadas aplican para todos los días dentro del rango del evento.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="rentalPeriodType" class="form-label fw-semibold">
                                    Tipo de período <span class="text-danger">*</span>
                                </label>

                                <select id="rentalPeriodType" name="period_type" class="form-select  form-select-lg"
                                        required>
                                    <option value="" selected>Selecciona el tipo de período</option>

                                    <option value="workday">
                                        Laborable
                                    </option>

                                    <option value="non_workday_saturday">
                                        No laborable sábado
                                    </option>

                                    <option value="non_workday_sunday_holiday">
                                        No laborable domingo o festivo
                                    </option>
                                </select>
                                <small class="text-muted d-block fst-italic mt-1">
                                    <strong>Laborable: </strong> lunes a viernes, 7:30 PM a 4:30 PM <br>
                                    <strong>No laborable sábado: </strong> lunes a sábado, 8:00 AM a 9:30 PM;
                                    <br>
                                    <strong>No laborable domingo o festivo: </strong> lunes a viernes, domingo o festivo, 8:00 AM a 9:30 PM
                                </small>
                                <div class="invalid-feedback" id="rentalPeriodTypeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalRateModeDisplay" class="form-label fw-semibold">
                                    Tipo de Tarifa
                                </label>

                                <input
                                    type="text"
                                    id="rentalRateModeDisplay"
                                    class="form-control form-control-lg"
                                    value="Se calculará automáticamente"
                                    readonly
                                    tabindex="-1"
                                    onfocus="this.blur()"
                                >

                                <input type="hidden" id="rentalRangeType" name="rate_mode">

                                <small class="text-muted fw-bold d-block mt-1">
                                    El tipo de tarifa se calcula automáticamente según la duración del evento.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalStartDate" class="form-label fw-semibold" id="rentalStartDateLabel">
                                    Fecha inicial del evento <span class="text-danger">*</span>
                                </label>

                                <div class="date-picker-wrapper">
                                    <input
                                        type="text"
                                        id="rentalStartDate"
                                        name="event_date"
                                        class="form-control form-control-lg date-picker-input"
                                        placeholder="Día Mes Año"
                                        autocomplete="off"
                                        inputmode="none"
                                        readonly
                                        required

                                    >

                                    <button
                                        type="button"
                                        class="date-picker-icon"
                                        id="rentalStartDateIcon"
                                        aria-label="Abrir calendario de fecha inicial del evento"
                                    >
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>

                                <div class="invalid-feedback d-block" id="rentalStartDateError"></div>
                            </div>

                            <div class="col-md-6" id="rentalEndDateRow">
                                <label for="rentalEndDate" class="form-label fw-semibold">
                                    Fecha final del evento <span class="text-danger">*</span>
                                </label>

                                <div class="date-picker-wrapper">
                                    <input
                                        type="text"
                                        id="rentalEndDate"
                                        name="event_end_date"
                                        class="form-control form-control-lg date-picker-input"
                                        placeholder="Día Mes Año"
                                        autocomplete="off"
                                        inputmode="none"
                                        required
                                        readonly
                                    >

                                    <button
                                        type="button"
                                        class="date-picker-icon"
                                        id="rentalEndDateIcon"
                                        aria-label="Abrir calendario de fecha final del evento"
                                    >
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>

                                <div class="invalid-feedback d-block" id="rentalEndDateError"></div>
                            </div>
                        </div>


                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="rentalStartTime" class="form-label fw-semibold">
                                    Horario inicial del evento <span class="text-danger">*</span>
                                </label>
                                <select id="rentalStartTime" name="start_time" class="form-select form-select-lg"
                                        required>
                                    <option value="" selected>Seleccionar horario inicial</option>
                                </select>
                                <div class="invalid-feedback" id="rentalStartTimeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="rentalEndTime" class="form-label fw-semibold">
                                    Horario final del evento <span class="text-danger">*</span>
                                </label>
                                <select id="rentalEndTime" name="end_time" class="form-select form-select-lg" required>
                                    <option value="" selected>Seleccionar horario final</option>
                                </select>
                                <div class="invalid-feedback" id="rentalEndTimeError"></div>
                                <div class="invalid-feedback d-block" id="rentalTimeError"></div>
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
                    <button type="button" class="btn btn-success px-4" id="saveRentalBtn">
                        Guardar Evento
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm cancellation modal for unsaved rate configuration changes --}}
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
                    <button type="button" class="btn btn-danger" id="confirmCancelConfigureBtn">Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm to cancel the event --}}
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
                    <button type="button" class="btn btn-danger" id="confirmCancelRentalBtn">Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete event confirmation modal --}}
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

    {{--
        Create Related Event Modal

        Creates a related-area sub-event linked to an existing parent event group.
        This is used when one real-world event uses more than one facility area,
        but each area needs its own independent cost calculation.
    --}}
    <div class="modal fade" id="createRelatedModal" tabindex="-1" aria-labelledby="createRelatedModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-lg-down modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="createRelatedModalLabel">Crear Evento Relacionado</h4>
                        <p class="text-muted mb-1">
                            Registra un evento adicional vinculado al evento principal.
                        </p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close related-close-btn" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="createRelatedForm" novalidate>
                        <input type="hidden" id="relatedParentEventId">
                        <input type="hidden" id="relatedRateMode" name="rate_mode">

                        <div class="row g-2 mb-2 justify-content-center">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-4 px-4 py-3">
                                    <strong>Aviso:</strong>
                                    <span id="relatedModalNoticeText">
                                        Esta opción permite agregar otra área relacionada al mismo evento principal.
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6">
                                <label for="relatedArea" class="form-label fw-semibold">
                                    Área <span class="text-danger">*</span>
                                </label>
                                <select id="relatedArea" class="form-select form-select-lg" required>
                                    <option value="" selected>Selecciona el área</option>
                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <option value="{{ $salon }}">{{ $salon }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block fst-italic">
                                    Seleccione el área relacionada al evento principal.
                                </small>
                                <div class="invalid-feedback" id="relatedAreaError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="relatedResponsible" class="form-label fw-semibold">
                                    Responsable <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="relatedResponsible"
                                    class="form-control form-control-lg"
                                    placeholder="Nombre del responsable"
                                    minlength="8"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Incluya nombre y apellido. Escriba entre 8 y 40 caracteres. Solo letras y espacios.
                                </small>
                                <div class="invalid-feedback" id="relatedResponsibleError"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="relatedDescription" class="form-label fw-semibold">
                                Descripción<span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="relatedDescription"
                                class="form-control form-control-lg"
                                rows="3"
                                placeholder="Descripción del evento relacionado"
                                minlength="10"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Escriba entre 10 y 250 caracteres. Solo letras, números, espacios, puntos, comas y guiones.
                            </small>
                            <div class="invalid-feedback" id="relatedDescriptionError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Servicios <span class="text-danger">*</span>
                            </label>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="relatedUtilities"
                                                   value="utilities">
                                            <label class="form-check-label fw-semibold" for="relatedUtilities">
                                                Utilidades
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento incluye costos generales de utilidades asociados al
                                            uso del área.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="relatedElectricity"
                                                   value="electricity">
                                            <label class="form-check-label fw-semibold" for="relatedElectricity">
                                                Electricidad
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo eléctrico.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="relatedWater"
                                                   value="water">
                                            <label class="form-check-label fw-semibold" for="relatedWater">
                                                Agua
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo de agua.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="invalid-feedback d-block d-none mt-2" id="relatedServicesError">
                                Debes seleccionar al menos un servicio.
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-3 px-3 py-2">
                                <strong>Aviso importante:</strong>
                                Si el evento combina días u horarios laborables con días u horarios no laborables,
                                seleccione el período no laborable correspondiente:
                                <strong>No laborable sábado</strong> o
                                <strong>No laborable domingo o festivo</strong>.

                                Las horas seleccionadas aplican para todos los días dentro del rango del evento.
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="relatedPeriodType" class="form-label fw-semibold">
                                        Tipo de período <span class="text-danger">*</span>
                                    </label>
                                    <select id="relatedPeriodType" class="form-select form-select-lg" required>
                                        <option value="" selected>Selecciona el tipo de período</option>
                                        <option value="workday">Laborable</option>
                                        <option value="non_workday_saturday">No laborable sábado</option>
                                        <option value="non_workday_sunday_holiday">No laborable domingo o festivo
                                        </option>
                                    </select>

                                    <small class="text-muted d-block fst-italic mt-1">
                                        <strong>Laborable: </strong> lunes a viernes, 7:30 AM a 4:30 PM <br>
                                        <strong>No laborable sábado: </strong> lunes a sábado ,8:00 AM a 9:30 PM <br>
                                        <strong>No laborable domingo o festivo: </strong> lunes a viernes, domingo o festivo, 8:00 AM a 9:30 PM
                                    </small>
                                    <div class="invalid-feedback" id="relatedPeriodTypeError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="relatedRateModeDisplay" class="form-label fw-semibold">
                                        tipo de tarifa <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="relatedRateModeDisplay"
                                        class="form-control form-control-lg"
                                        value="Se calculará automáticamente"
                                        readonly
                                        tabindex="-1"
                                        onfocus="this.blur()"
                                    >
                                    <small class="text-muted fw-bold d-block fst-italic">
                                        El tipo de tarifa se calcula automáticamente según la duración del evento.
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label for="relatedStartDate" class="form-label fw-semibold">
                                        Fecha inicial del evento <span class="text-danger">*</span>
                                    </label>
                                    <div class="date-picker-wrapper">
                                        <input
                                            type="text"
                                            id="relatedStartDate"
                                            name="related_event_start_date"
                                            class="form-control form-control-lg date-picker-input"
                                            placeholder="Día Mes Año"
                                            autocomplete="off"
                                            inputmode="none"
                                            required
                                        >

                                        <button type="button"
                                                class="date-picker-icon"
                                                id="relatedStartDateIcon"
                                                aria-label="Abrir calendario de fecha inicial del evento relacionado">
                                            <i class="bi bi-calendar3"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="relatedStartDateError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="relatedEndDate" class="form-label fw-semibold">
                                        Fecha final del evento <span class="text-danger">*</span>
                                    </label>
                                    <div class="date-picker-wrapper">
                                        <input
                                            type="text"
                                            id="relatedEndDate"
                                            name="related_event_end_date"
                                            class="form-control form-control-lg date-picker-input"
                                            placeholder="Día Mes Año"
                                            autocomplete="off"
                                            inputmode="none"
                                            required
                                        >

                                        <button type="button"
                                                class="date-picker-icon"
                                                id="relatedEndDateIcon"
                                                aria-label="Abrir calendario de fecha inicial">
                                            <i class="bi bi-calendar3"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback" id="relatedEndDateError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="relatedStartTime" class="form-label fw-semibold">
                                        Horario inicial del evento <span class="text-danger">*</span>
                                    </label>
                                    <select id="relatedStartTime" class="form-select form-select-lg" required>
                                        <option value="" selected>Primero selecciona el tipo de período</option>
                                    </select>
                                    <div class="invalid-feedback" id="relatedStartTimeError"></div>
                                </div>

                                <div class="col-md-6">
                                    <label for="relatedEndTime" class="form-label fw-semibold">
                                        Horario final del evento <span class="text-danger">*</span>
                                    </label>
                                    <select id="relatedEndTime" class="form-select form-select-lg" required>
                                        <option value="" selected>Primero selecciona el tipo de período</option>
                                    </select>
                                    <div class="invalid-feedback" id="relatedEndTimeError"></div>
                                </div>

                                <div class="col-12">
                                    <div class="invalid-feedback d-block mt-2" id="relatedTimeError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-4 border bg-light p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold fs-5">Costo estimado calculado</div>
                                    <small class="text-muted d-block">Se determina según período y servicios
                                        seleccionados.</small>
                                </div>
                                <span class="fw-bold text-success fs-2 mb-0" id="relatedEstimatedTotal">$0.00</span>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted d-block">
                                    Período seleccionado:
                                    <span id="relatedDetectedPeriodLabel" class="fw-semibold">—</span>
                                </small>
                                <small class="text-muted d-block">
                                    Duración estimada:
                                    <span id="relatedDetectedHoursLabel" class="fw-semibold">0.00 horas</span>
                                </small>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 modal-footer-safe">
                    <button type="button" class="btn btn-outline-secondary px-4 related-cancel-btn">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success px-4" id="saveRelatedEventBtn" disabled>
                        Guardar Evento Relacionado
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm cancellation modal for unsaved related-event changes --}}
    <div class="modal fade" id="confirmCancelRelatedModal" tabindex="-1" aria-hidden="true">
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
                    <button type="button" class="btn btn-danger" id="confirmCancelRelatedBtn"> Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{--
        Customize Days Modal

        Creates a custom-day modification for an existing event.
        This allows one selected day, or the selected day and following days,
        to use a different schedule from the parent event while remaining grouped
        under the same main event.
    --}}
    <div class="modal fade" id="customizeDaysModal" tabindex="-1" aria-labelledby="customizeDaysModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="customizeDaysModalLabel">Modificar Días</h4>
                        <p class="text-muted mb-1">
                            Ajusta una fecha u horario específico del evento seleccionado.
                        </p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close customize-close-btn" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="customizeDaysForm" novalidate>
                        <input type="hidden" id="customizeEventId">

                        <div class="row g-3">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-3 px-4 py-3">
                                    <strong>Aviso:</strong>
                                    Esta opción permite preparar modificaciones puntuales de días u horarios.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="customizeDate" class="form-label fw-semibold">
                                    Fecha <span class="text-danger">*</span>
                                </label>

                                <div class="date-picker-wrapper">
                                    <input
                                        type="text"
                                        id="customizeDate"
                                        name="event_start_date"
                                        class="form-control form-control-lg date-picker-input"
                                        placeholder="Día Mes Año"
                                        autocomplete="off"
                                        inputmode="none"
                                        required
                                    >

                                    <button type="button"
                                            class="date-picker-icon"
                                            id="customizeDateIcon"
                                            aria-label="Abrir calendario de personalizar fechas">
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>

                                <small class="text-muted d-block fst-italic">
                                    Seleccione la fecha que desea modificar.
                                </small>

                                <div class="invalid-feedback" id="customizeDateError"></div>
                            </div>

                            <div class="col-12 col-lg-6">
                                <label for="customizePeriodType" class="form-label fw-semibold">
                                    Tipo de período <span class="text-danger">*</span>
                                </label>

                                <select
                                    id="customizePeriodType"
                                    name="period_type"
                                    class="form-select form-select-lg"
                                    required
                                >
                                    <option value="" selected>Seleccionar período</option>
                                    <option value="workday">Laborable</option>
                                    <option value="non_workday_saturday">No laborable sábado</option>
                                    <option value="non_workday_sunday_holiday">No laborable dom./festivo</option>
                                </select>

                                <small class="text-muted d-block fst-italic mt-1">
                                    <strong>Laborable: </strong> lunes a viernes, 7:30 AM a 4:30 PM <br>
                                    <strong>No laborable sábado: </strong> lunes a sábado ,8:00 AM a 9:30 PM <br>
                                    <strong>No laborable domingo o festivo: </strong> lunes a viernes, domingo o festivo, 8:00 AM a 9:30 PM
                                </small>

                                <div class="invalid-feedback d-block" id="customizePeriodTypeError"></div>
                            </div>

                            <div class="col-12"></div>
                            <div class="col-md-6">
                                <label for="customizeStartTime" class="form-label fw-semibold">
                                    Horario inicial del evento <span class="text-danger">*</span>
                                </label>
                                <select id="customizeStartTime" class="form-select form-select-lg" required>
                                    <option value="" selected>Seleccionar horario inicial</option>
                                </select>
                                <div class="invalid-feedback" id="customizeStartTimeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="customizeEndTime" class="form-label fw-semibold">
                                    Horario final del evento <span class="text-danger">*</span>
                                </label>
                                <select id="customizeEndTime" class="form-select form-select-lg" required>
                                    <option value="" selected>Seleccionar horario final</option>
                                </select>
                                <div class="invalid-feedback" id="customizeEndTimeError"></div>
                            </div>

                            <div class="col-12">
                                <div class="invalid-feedback d-block mt-2" id="customizeTimeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="customizeScope" class="form-label fw-semibold">
                                    Alcance <span class="text-danger">*</span>
                                </label>
                                <select id="customizeScope" class="form-select form-select-lg" required>
                                    <option value="" selected>Selecciona el alcance</option>
                                    <option value="single_day">Solo este día</option>
                                    <option value="this_and_following">Este día y siguientes</option>
                                </select>
                                <small class="text-muted d-block fst-italic">
                                    Seleccione cómo desea aplicar la modificación de fecha.
                                </small>
                                <div class="invalid-feedback" id="customizeScopeError"></div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 modal-footer-safe">
                    <button type="button" class="btn btn-outline-secondary px-4 customize-cancel-btn">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success px-4" id="saveCustomizeDaysBtn" disabled>
                        Guardar Modificación
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm cancellation modal for unsaved customize-days changes --}}
    <div class="modal fade" id="confirmCancelCustomizeModal" tabindex="-1" aria-hidden="true">
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
                    <button type="button" class="btn btn-danger" id="confirmCancelCustomizeBtn"> Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    {{--
        Edit Event Modal

        Updates an existing parent event or editable sub-event.
        JavaScript populates this modal using the selected table row's data-* attributes.
        When editing a parent event, changing the date range or area may affect
        existing custom-day modifications.
    --}}
    <div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl modal-fullscreen-lg-down modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0 align-items-start">
                    <div class="pe-4">
                        <h4 class="modal-title fw-bold mb-2" id="editEventModalLabel">Editar Evento</h4>
                        <p class="text-muted mb-1">
                            Modifica la información del evento seleccionado.
                        </p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close edit-close-btn" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <form id="editEventForm" novalidate>
                        <input type="hidden" id="editEventId">
                        <input type="hidden" id="editRateMode" name="rate_mode">

                        <div class="row g-2 mb-2 justify-content-center">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-1 px-3 py-2">
                                    <strong>Aviso:</strong>
                                    Si editas un evento principal y cambias su rango de fechas, cualquier modificación
                                    fuera del nuevo rango puede ser eliminada.
                                </div>
                            </div>

                            <div class="col-md-6 col-lg-6">
                                <label for="editClassroom" class="form-label fw-semibold">
                                    Área <span class="text-danger">*</span>
                                </label>
                                <select id="editClassroom" class="form-select form-select-lg" required>
                                    <option value="" selected>Selecciona el área</option>
                                    @foreach ($facilityCosts as $cost)
                                        @php $salon = $cost->classroom_name; @endphp
                                        <option value="{{ $salon }}">{{ $salon }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block fst-italic">
                                    Seleccione el área donde se llevará a cabo el evento.
                                </small>
                                <div class="invalid-feedback" id="editClassroomError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="editResponsible" class="form-label fw-semibold">
                                    Responsable <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="editResponsible"
                                    class="form-control form-control-lg"
                                    placeholder="Nombre del responsable"
                                    minlength="8"
                                    required
                                >
                                <small class="text-muted d-block fst-italic">
                                    Incluya nombre y apellido. Escriba entre 8 y 40 caracteres. Solo letras y espacios.
                                </small>
                                <div class="invalid-feedback" id="editResponsibleError"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="editDescription" class="form-label fw-semibold">
                                Descripción <span class="text-danger">*</span>
                            </label>
                            <textarea
                                id="editDescription"
                                class="form-control form-control-lg"
                                rows="4"
                                placeholder="Descripción del evento"
                                minlength="10"
                                required
                            ></textarea>
                            <small class="text-muted d-block fst-italic">
                                Entre 10 y 250 caracteres. Solo letras, números, espacios, puntos, comas y guiones.
                            </small>
                            <div class="invalid-feedback" id="editDescriptionError"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Servicios <span class="text-danger">*</span>
                            </label>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editUtilities"
                                                   value="utilities">
                                            <label class="form-check-label fw-semibold" for="editUtilities">
                                                Utilidades
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento incluye costos generales de utilidades asociados al
                                            uso del área.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editElectricity"
                                                   value="electricity">
                                            <label class="form-check-label fw-semibold" for="editElectricity">
                                                Electricidad
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo eléctrico.
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="service-option-card h-100 p-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="editWater"
                                                   value="water">
                                            <label class="form-check-label fw-semibold" for="editWater">
                                                Agua
                                            </label>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Seleccione si el evento requiere consumo de agua.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="invalid-feedback d-block d-none mt-2" id="editServicesError">
                                Debes seleccionar al menos un servicio.
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <div class="alert alert-warning rounded-0 border-0 shadow-sm mb-2 px-3 py-2">
                                    <strong>Aviso importante:</strong>
                                    Si el evento combina días u horarios laborables con días u horarios no laborables,
                                    seleccione el período no laborable correspondiente:
                                    <strong>No laborable sábado</strong> o
                                    <strong>No laborable domingo o festivo</strong>.

                                    Las horas seleccionadas aplican para todos los días dentro del rango del evento.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="editPeriodType" class="form-label fw-semibold">
                                    Tipo de período <span class="text-danger">*</span>
                                </label>

                                <select id="editPeriodType" class="form-select form-select-lg" required>
                                    <option value="" selected>Selecciona el tipo de período</option>
                                    <option value="workday">
                                        Laborable
                                    </option>
                                    <option value="non_workday_saturday">
                                        No laborable sábado
                                    </option>
                                    <option value="non_workday_sunday_holiday">
                                        No laborable domingo o festivo
                                    </option>
                                </select>
                                <small class="text-muted d-block fst-italic mt-1">
                                    <strong>Laborable: </strong> lunes a viernes, 7:30 AM a 4:30 PM <br>
                                    <strong>No laborable sábado: </strong> lunes a sábado, 8:00 AM a 9:30 p.m. <br>
                                    <strong> No laborable domingo o festivo: </strong> lunes a viernes, domingo o festivo, 8:00 AM a 9:30 PM
                                </small>
                                <div class="invalid-feedback" id="editPeriodTypeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="editRateModeDisplay" class="form-label fw-semibold">
                                    Tipo de Tarifa
                                </label>

                                <input
                                    type="text"
                                    id="editRateModeDisplay"
                                    class="form-control form-control-lg"
                                    value="Se calculará automáticamente"
                                    readonly
                                    tabindex="-1"
                                    onfocus="this.blur()"
                                >

                                <small class="text-muted fw-bold d-block mt-1">
                                    El tipo de tarifa se calcula automáticamente según la duración del evento.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="editStartDate" class="form-label fw-semibold">
                                    Fecha inicial del evento <span class="text-danger">*</span>
                                </label>

                                <div class="date-picker-wrapper">
                                    <input
                                        type="text"
                                        id="editStartDate"
                                        class="form-control form-control-lg date-picker-input"
                                        placeholder="Día Mes Año"
                                        autocomplete="off"
                                        inputmode="none"
                                        readonly
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="date-picker-icon"
                                        id="editStartDateIcon"
                                        aria-label="Abrir calendario de fecha inicial"
                                    >
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>

                                <div class="invalid-feedback d-block" id="editStartDateError"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="editEndDate" class="form-label fw-semibold">
                                    Fecha final del evento <span class="text-danger">*</span>
                                </label>

                                <div class="date-picker-wrapper">
                                    <input
                                        type="text"
                                        id="editEndDate"
                                        class="form-control form-control-lg date-picker-input"
                                        placeholder="Día Mes Año"
                                        autocomplete="off"
                                        inputmode="none"
                                        readonly
                                        required
                                    >

                                    <button
                                        type="button"
                                        class="date-picker-icon"
                                        id="editEndDateIcon"
                                        aria-label="Abrir calendario de fecha final"
                                    >
                                        <i class="bi bi-calendar3"></i>
                                    </button>
                                </div>

                                <div class="invalid-feedback d-block" id="editEndDateError"></div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="editStartTime" class="form-label fw-semibold">
                                    Horario inicial del evento <span class="text-danger">*</span>
                                </label>
                                <select id="editStartTime" class="form-select form-select-lg" required>
                                    <option value="" selected>Primero selecciona el tipo de período</option>
                                </select>
                                <div class="invalid-feedback" id="editStartTimeError"></div>
                            </div>

                            <div class="col-md-6">
                                <label for="editEndTime" class="form-label fw-semibold">
                                    Horario final del evento <span class="text-danger">*</span>
                                </label>
                                <select id="editEndTime" class="form-select form-select-lg" required>
                                    <option value="" selected>Primero selecciona el tipo de período</option>
                                </select>
                                <div class="invalid-feedback" id="editEndTimeError"></div>
                                <div class="invalid-feedback d-block" id="editTimeError"></div>
                            </div>
                        </div>

                        <div class="rounded-4 border bg-light p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <div class="fw-bold fs-5">Costo estimado calculado</div>
                                    <small class="text-muted d-block">
                                        Se determina según período y servicios seleccionados.
                                    </small>
                                </div>
                                <span class="fw-bold text-success fs-2 mb-0" id="editEstimatedTotal">$0.00</span>
                            </div>

                            <div class="mt-3">
                                <small class="text-muted d-block">
                                    Período seleccionado:
                                    <span id="editDetectedPeriodLabel" class="fw-semibold">—</span>
                                </small>
                                <small class="text-muted d-block">
                                    Duración estimada:
                                    <span id="editDetectedHoursLabel" class="fw-semibold">0.00 horas</span>
                                </small>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 modal-footer-safe">
                    <button type="button" class="btn btn-outline-secondary px-4 edit-cancel-btn">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-success px-4" id="saveEditEventBtn" disabled>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirm cancellation modal for unsaved edit-event changes --}}
    <div class="modal fade" id="confirmCancelEditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">¿Seguro que deseas cancelar?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-0">
                    Tienes información editada. Si cancelas, perderás los cambios no guardados.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Seguir Editando
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmCancelEditBtn"> Sí, Cancelar</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Add classroom modal --}}
    <div class="modal fade" id="addClassroomModal" tabindex="-1" aria-labelledby="addClassroomModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 align-items-start">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-2">Agregar área</h5>
                        <p class="text-muted mb-1">Escribe el nombre del área nueva.</p>
                        <small class="text-muted">
                            <span class="text-danger">*</span> Campos requeridos
                        </small>
                    </div>
                    <button type="button" class="btn-close" id="closeAddClassroomBtn" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                </div>


                <div class="modal-body pt-0">
                    <label for="newClassroomType" class="form-label fw-semibold">
                        Tipo de área <span class="text-danger">*</span>
                    </label>
                    <select id="newClassroomType" class="form-select border-2 border-dark mb-3">
                        <option value="" selected>Selecciona el tipo de área</option>
                        <option value="classroom">Salón</option>
                        <option value="lateral">Lateral</option>
                    </select>

                    <div class="invalid-feedback d-block" id="newClassroomTypeError"></div>

                    <label for="newClassroomName" class="form-label fw-semibold">
                        Nombre del área <span class="text-danger">*</span>
                    </label>
                    <input
                        type="text"
                        id="newClassroomName"
                        class="form-control border-2 border-dark"
                        placeholder="Ejemplo: CM 211 o Lateral 3"
                    >
                    <small class="text-muted d-block fst-italic">
                        Entre 6 y 40 caracteres. Solo letras, números, espacios, comas, puntos y guiones.
                    </small>
                    <div class="invalid-feedback d-block" id="newClassroomNameError"></div>
                </div>

                {{-- Add a new area --}}
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" id="cancelAddClassroomBtn"
                            data-bs-dismiss="modal">Cancelar
                    </button>
                    <button type="button" class="btn btn-success" id="confirmAddClassroomBtn" disabled>
                        Agregar Área
                    </button>
                    <form id="addClassroomForm" method="POST" action="{{ route('facility.classrooms.store') }}"
                          class="d-none">
                        @csrf
                        <input type="hidden" name="classroom_name" id="hiddenNewClassroomName">
                        <input type="hidden" name="classroom_type" id="hiddenNewClassroomType">
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Deleted selected area --}}
    <div class="modal fade" id="deleteClassroomModal" tabindex="-1" aria-labelledby="deleteClassroomModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 align-items-start">
                    <div class="pe-3">
                        <h5 class="modal-title fw-bold mb-1" id="deleteClassroomModalLabel">Descartar área</h5>
                        <p class="text-muted mb-0">
                            ¿Estás seguro de que deseas eliminar el/las área(s) selecionadas? Esta acción es permanente.
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
                        Eliminar Área(s)
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Warnings if the Super Administrator or Facility Administrator change an event that affect a child event --}}
    <div class="modal fade" id="parentRangeWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Advertencia de rango</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0" id="parentRangeWarningText">
                        Esta edición deja una o más modificaciones fuera del rango del evento padre.
                        Si continúas, esas modificaciones serán eliminadas.
                    </p>
                </div>

                <div class="modal-footer border-0 d-flex flex-nowrap justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Arreglar Evento Principal
                    </button>

                    <button type="button" class="btn btn-danger" id="confirmParentRangeDeleteBtn">
                        Continuar y Borrar Modificaciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Warnings if the Super Administrator or Facility Administrator change an event that affect a child event --}}
    <div class="modal fade" id="customizeOverwriteWarningModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Advertencia: modificaciones existentes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-0" id="customizeOverwriteWarningText">
                        El rango seleccionado incluye días que ya tienen modificaciones. Si continúas, esas
                        modificaciones serán eliminadas.
                    </p>
                </div>

                <div class="modal-footer border-0 d-flex flex-nowrap justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="button" class="btn btn-danger" id="confirmCustomizeOverwriteBtn">
                        Continuar y borrar modificaciones
                    </button>
                </div>
            </div>
        </div>
    </div>


    {{-- Notifications toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="deleteEntryToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">Evento eliminado correctamente.</div>
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

        {{-- Areas costs saved toast --}}
        <div id="ratesSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Tarifas guardadas correctamente.
                </div>
                <button type="button" class="btn-close ms-auto me-3" data-bs-dismiss="toast"></button>
            </div>
        </div>

        {{-- Event creation toast --}}
        <div id="rentalSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center w-100">
                <div class="toast-body fw-semibold">
                    Evento creado correctamente.
                </div>
                <button type="button" class="btn-close ms-auto me-3" data-bs-dismiss="Cerrar"></button>
            </div>
        </div>

        <div id="classroomAddedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Área agregada correctamente.
                </div>
                <button type="button" class="btn-close ms-auto me-3" data-bs-dismiss="toast"></button>
            </div>
        </div>

        <div id="classroomsDeletedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    Área(s) descartada(s) correctamente.
                </div>
                <button type="button" class="btn-close ms-auto me-3" data-bs-dismiss="toast"></button>
            </div>
        </div>

        {{-- EventFlow imported simulation toast --}}
        @if(session('mock_imported'))
            <div id="mockImportToast"
                 class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
                 role="alert" aria-live="assertive" aria-atomic="true" style="width:auto; max-width:fit-content;">
                <div class="d-flex align-items-center">
                    <div class="toast-body fw-semibold pe-1">
                        {{ session('mock_imported') }}
                    </div>
                    <button type="button" class="btn-close ms-auto me-3" data-bs-dismiss="toast" aria-label="Cerrar"
                            style="transform:scale(0.8);"></button>
                </div>
            </div>
        @endif

        <div id="customizeSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true"
             style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    La excepción fue guardada correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast" aria-label="Cerrar"
                        style="transform:scale(0.8);"></button>
            </div>
        </div>

        <div id="editSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true"
             style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    El evento fue actualizado correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast" aria-label="Cerrar"
                        style="transform:scale(0.8);"></button>
            </div>
        </div>

        <div id="relatedSavedToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert" aria-live="assertive" aria-atomic="true"
             style="width:auto; max-width:fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1">
                    El evento relacionado fue preparado correctamente.
                </div>
                <button type="button" class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast" aria-label="Cerrar"
                        style="transform:scale(0.8);"></button>
            </div>
        </div>
    </div>

    {{-- Hidden forms used by JavaScript to perform CSRF-protected DELETE submissions --}}
    <form id="deleteCostEntryForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    <form id="deleteClassroomsForm" method="POST" action="{{ route('facility.classrooms.destroy') }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    {{--
        Build the ratesByClassroom lookup array used by facility_management.js.
        Maps each classroom name to its configured rate values (or null if not yet configured).
        Passed to the front end via window.facilityManagementConfig below.
    --}}
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

    {{-- Expose server-side config and rate data to facility_management.js --}}
    <script>

        window.facilityManagementConfig = {
            facilityManagementUrl: @json(route('facility_management')),
            ratesByClassroom: @json($ratesByClassroom),
        };

        const ratesByClassroom = @json($ratesByClassroom);

    </script>
</x-layout>
