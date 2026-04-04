<x-layout title="Registros de Acceso">
<x-navbar></x-navbar>


    <div class="container-fluid py-4 px-4">


        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Panel de Administración</h1>
            <p>Monitorear acceso al sistema y costos de instalaciones.</p>
        </div>


        <div class="d-flex justify-content-start gap-3 mb-4">
            <button
                type="button"
                id="downloadAccessLogsCsvBtn"
                class="btn btn-success px-4 py-2"
            >
                <i class="bi bi-box-arrow-in-down me-2"></i>Exportar a CSV
            </button>
        </div>


        <!-- Filters and searches -->
        @csrf
        <form id="accessLogsFilterForm" class="mb-5" onsubmit="return false;">
            <div class="row g-3 mb-3 align-items-stretch">
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                <span class="input-group-text bg-white border-0">
                    <i class="bi bi-search"></i>
                </span>

                        <input
                            type="text"
                            id="accessLogsSearch"
                            class="form-control border-0"
                            placeholder="Buscar por usuario, IP o detalles..."
                        >
                    </div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="button" id="searchAccessLogsBtn" class="btn btn-success h-100">
                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                    <select id="accessLogsRoleFilter" class="form-select border-2 border-dark">
                        <option value="">Todos los Roles</option>
                        <option value="usuario">Usuario</option>
                        <option value="administrador de mercado">Administrador de Mercado</option>
                        <option value="administrador de inventario">Administrador de Inventario</option>
                        <option value="administrador de facilidad">Administrador de Facilidad</option>
                        <option value="super administrador">Super Administrador</option>
                    </select>
                </div>

                <div class="col-md-6 col-lg-4">
                    <select id="accessLogsEventFilter" class="form-select border-2 border-dark">
                        <option value="">Todos los Eventos</option>
                        <option value="Inicio de Sesión">Inicio de Sesión</option>
                        <option value="Cierre de Sesión">Cierre de Sesión</option>
                        <option value="Error de Acceso">Error de Acceso</option>
                        <option value="Acceso Admin">Acceso Admin</option>
                        <option value="Ver Mercado">Ver Mercado</option>
                        <option value="Ver Inventario">Ver Inventario</option>
                        <option value="Solicitud de Préstamo">Solicitud de Préstamo</option>
                        <option value="Publicación Creada">Publicación Creada</option>
                    </select>
                </div>

                <div class="col-md-auto">
                    <button type="button" id="clearAccessLogsFiltersBtn" class="btn btn-outline-secondary">
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </form>



{{--        <form method="GET" action="{{ route('access_logs') }}" class="row mb-4 g-3">--}}
{{--            <div class="col-md-10">--}}
{{--                <div class="input-group search-group">--}}
{{--                    <span class="input-group-text bg-white border-0">--}}
{{--                        <i class="bi bi-search"></i>--}}
{{--                    </span>--}}
{{--                    <input--}}
{{--                        type="text"--}}
{{--                        name="search"--}}
{{--                        class="form-control border-0"--}}
{{--                        placeholder="Buscar por usuario, IP o detalles..."--}}
{{--                        value="{{ request('search') }}"--}}
{{--                    >--}}
{{--                </div>--}}
{{--            </div>--}}

{{--            <div class="col-md-2 d-flex align-items-end">--}}
{{--                <button type="submit" class="btn btn-success w-100">--}}
{{--                    Buscar--}}
{{--                </button>--}}
{{--            </div>--}}

{{--            <div class="col-md-3">--}}
{{--                <select name="role" class="form-select border-2 border-dark" onchange="this.form.submit()">--}}
{{--                    <option value="">Todos los Roles</option>--}}
{{--                    <option value="usuario" {{ request('role') == 'usuario' ? 'selected' : '' }}>Usuario</option>--}}
{{--                    <option value="administrador de mercado" {{ request('role') == 'administrador de mercado' ? 'selected' : '' }}>Administrador de Mercado</option>--}}
{{--                    <option value="administrador de inventario" {{ request('role') == 'administrador de inventario' ? 'selected' : '' }}>Administrador de Inventario</option>--}}
{{--                    <option value="administrador de facilidad" {{ request('role') == 'administrador de facilidad' ? 'selected' : '' }}>Administrador de Facilidad</option>--}}
{{--                    <option value="super administrador" {{ request('role') == 'super administrador' ? 'selected' : '' }}>Super Administrador</option>--}}
{{--                </select>--}}
{{--            </div>--}}

{{--            <div class="col-lg-3">--}}
{{--                <select name="event" class="form-select border-2 border-dark" onchange="this.form.submit()">--}}
{{--                    <option value="">Todos los Eventos</option>--}}
{{--                    <option value="Inicio de Sesión" {{ request('event') == 'Inicio de Sesión' ? 'selected' : '' }}>Inicio de Sesión</option>--}}
{{--                    <option value="Cierre de Sesión" {{ request('event') == 'Cierre de Sesión' ? 'selected' : '' }}>Cierre de Sesión</option>--}}
{{--                    <option value="Error de Acceso" {{ request('event') == 'Error de Acceso' ? 'selected' : '' }}>Error de Acceso</option>--}}
{{--                    <option value="Acceso Admin" {{ request('event') == 'Acceso Admin' ? 'selected' : '' }}>Acceso Admin</option>--}}
{{--                    <option value="Ver Mercado" {{ request('event') == 'Ver Mercado' ? 'selected' : '' }}>Ver Mercado</option>--}}
{{--                    <option value="Ver Inventario" {{ request('event') == 'Ver Inventario' ? 'selected' : '' }}>Ver Inventario</option>--}}
{{--                    <option value="Solicitud de Préstamo" {{ request('event') == 'Solicitud de Préstamo' ? 'selected' : '' }}>Solicitud de Préstamo</option>--}}
{{--                    <option value="Publicación Creada" {{ request('event') == 'Publicación Creada' ? 'selected' : '' }}>Publicación Creada</option>--}}
{{--                </select>--}}
{{--            </div>--}}

{{--            <div class="col-md-3 d-flex align-items-end">--}}
{{--                <a href="{{ route('access_logs') }}" class="btn btn-outline-secondary">--}}
{{--                    Limpiar filtros--}}
{{--                </a>--}}
{{--            </div>--}}
{{--        </form>--}}

        <!-- Access log table -->
        <div class="card border rounded-4 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <h3 class="fw-bold mb-2">Registros de Acceso</h3>
                <p class="text-muted mb-0 fs-5">Monitoreo en tiempo real del acceso al sistema</p>
            </div>


            <div class="table-responsive">
                <table class="table align-middle mb-0" id="accessLogsTable">
                    <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 fw-bold">Marca de Tiempo</th>
                        <th class="px-4 py-3 fw-bold">Usuario</th>
                        <th class="px-4 py-3 fw-bold">Rol</th>
                        <th class="px-4 py-3 fw-bold">Evento</th>
                        <th class="px-4 py-3 fw-bold">Dirección IP</th>
                        <th class="px-4 py-3 fw-bold">Comentario</th>
                    </tr>
                    </thead>
                    <tbody id="accessLogsTbody">
                        @foreach($logs as $log)
                        <tr>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($log->created_at)->timezone('America/Puerto_Rico')->format('m/d/Y h:i A') }}</td>
                            <td class="px-4 py-3">
                                {{ trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: 'Usuario' }}
                            </td>
                            <td class="py-3 text-center align-middle">
                                @php
                                    $roleClass = match(strtolower(trim($log->role))) {
                                        'usuario' => 'badge user-role-badge bg-primary-subtle text-primary-emphasis',

                                        'administrador de inventario' => 'badge user-role-badge bg-success-subtle text-success-emphasis',

                                        'administrador de mercado' => 'badge user-role-badge bg-warning-subtle text-warning-emphasis',


                                        'super administrador' => 'badge user-role-badge bg-danger-subtle text-danger-emphasis',

                                        'administrador de facilidad' => 'badge user-role-badge bg-info-subtle text-info-emphasis',

                                        default => 'badge user-role-badge bg-primary-subtle text-primary-emphasis',
                                    };
                                @endphp

                                <span class="{{ $roleClass }} px-2 py-1 small">
    {{ ucfirst($log->role) }}
</span>

                            </td>
                            <td class="px-4 py-3">{{ $log->action }}</td>
                            <td class="px-4 py-3">{{ $log->ip_address }}</td>
                            <td class="px-4 py-3">{{ $log->comment }}</td>
                        </tr>
                        @endforeach

                        <tr id="accessLogsEmptyState" style="display: none;">
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-search fs-1 text-muted"></i>
                                <h4 class="fw-bold mt-3">No se encontraron registros</h4>
                                <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro término.</p>
                            </td>
                        </tr>
                        </tbody>
                </table>
            </div>
        </div>

    <!--Pagination placeholder-->
    @if ($logs->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $logs->links('pagination::bootstrap-5') }}
    </div>
@endif
</x-layout>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csvBtn = document.getElementById('downloadAccessLogsCsvBtn');
        const table = document.getElementById('accessLogsTable');

        const searchInput = document.getElementById('accessLogsSearch');
        const searchBtn = document.getElementById('searchAccessLogsBtn');
        const roleFilter = document.getElementById('accessLogsRoleFilter');
        const eventFilter = document.getElementById('accessLogsEventFilter');
        const clearBtn = document.getElementById('clearAccessLogsFiltersBtn');
        const emptyState = document.getElementById('accessLogsEmptyState');

        if (!table) return;

        function normalizeText(value) {
            return String(value ?? '')
                .toLowerCase()
                .trim();
        }

        function escapeCSV(value) {
            const text = String(value ?? '').trim().replace(/\s+/g, ' ');
            return `"${text.replace(/"/g, '""')}"`;
        }

        function getRows() {
            return Array.from(table.querySelectorAll('tbody tr'));
        }

        function getVisibleRows() {
            return getRows().filter((row) => {
                return row.style.display !== 'none' && row.id !== 'accessLogsEmptyState';
            });
        }

        function updateEmptyState() {
            const emptyRow = document.getElementById('accessLogsEmptyState');
            if (!emptyRow) return;

            const visibleRows = getVisibleRows();
            emptyRow.style.display = visibleRows.length === 0 ? 'table-row' : 'none';
        }

        function applyAccessLogsFilters() {
            const searchValue = normalizeText(searchInput?.value || '');
            const roleValue = normalizeText(roleFilter?.value || '');
            const eventValue = normalizeText(eventFilter?.value || '');

            getRows().forEach((row) => {
                const cols = row.querySelectorAll('td');
                if (cols.length < 6) return;

                const timestamp = normalizeText(cols[0].textContent);
                const user = normalizeText(cols[1].textContent);
                const role = normalizeText(cols[2].textContent);
                const eventText = normalizeText(cols[3].textContent);
                const ip = normalizeText(cols[4].textContent);
                const comment = normalizeText(cols[5].textContent);

                const matchesSearch =
                    searchValue === '' ||
                    timestamp.includes(searchValue) ||
                    user.includes(searchValue) ||
                    role.includes(searchValue) ||
                    eventText.includes(searchValue) ||
                    ip.includes(searchValue) ||
                    comment.includes(searchValue);

                const matchesRole =
                    roleValue === '' || role.includes(roleValue);

                const matchesEvent =
                    eventValue === '' || eventText.includes(eventValue);

                row.style.display = (matchesSearch && matchesRole && matchesEvent) ? '' : 'none';
            });

            updateEmptyState();
        }

        function clearAccessLogsFilters() {
            if (searchInput) searchInput.value = '';
            if (roleFilter) roleFilter.value = '';
            if (eventFilter) eventFilter.value = '';

            getRows().forEach((row) => {
                row.style.display = '';
            });

            updateEmptyState();
        }

        if (searchBtn) {
            searchBtn.addEventListener('click', applyAccessLogsFilters);
        }

        if (searchInput) {
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyAccessLogsFilters();
                }
            });
        }

        if (roleFilter) {
            roleFilter.addEventListener('change', applyAccessLogsFilters);
        }

        if (eventFilter) {
            eventFilter.addEventListener('change', applyAccessLogsFilters);
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', clearAccessLogsFilters);
        }

        if (csvBtn) {
            csvBtn.addEventListener('click', () => {
                const rows = getVisibleRows();

                const csv = [];

                csv.push([
                    'Marca de Tiempo',
                    'Usuario',
                    'Rol',
                    'Evento',
                    'Dirección IP',
                    'Comentario'
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

        updateEmptyState();
    });
</script>

