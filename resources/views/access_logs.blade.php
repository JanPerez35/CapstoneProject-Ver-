{{--
    Access Logs View

    Displays the administrative access logs dashboard.

    Responsibilities:
    - Render the access logs search and filter interface
    - Show access log records in a tabular format
    - Provide CSV export functionality
    - Show pagination for large result sets
    - Display an empty state when no matching records are found

    Expected data:
    - $logs: paginated collection of access log records
--}}
<x-layout title="Registros de Acceso">
    {{-- Main navigation bar --}}
    <x-navbar></x-navbar>

    {{-- Frontend script for CSV export, search state, and empty-state handling --}}
    @vite('resources/js/access_log_validation.js')

    <div class="container py-4 px-4">

        {{-- Page header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Panel de Administración</h1>
            <p>Aquí puedes consultar las acciones registradas en el sistema, incluyendo usuarios, roles, eventos, direcciones IP y fechas de actividad.</p>
        </div>

        {{-- Action buttons section --}}
        <div class="d-flex justify-content-start gap-3 mb-4">
            <button
                type="button"
                id="downloadAccessLogsCsvBtn"
                class="btn btn-success px-4 py-2 d-flex align-items-center gap-2 fw-semibold"
            >
                <i class="bi bi-download"></i>  Exportar a CSV
            </button>
        </div>

        {{--
            Filters and search form

            Allows administrators to:
            - Search logs by user or IP
            - Filter logs by role
            - Filter logs by event type
            - Filter logs by date
            - Reset filters back to default state
        --}}
        <form id="accessLogsFilterForm" method="GET" action="{{ route('access_logs') }}" class="mb-5">
            <div class="row g-3 mb-3 align-items-stretch">
                {{-- Search input --}}
                <div class="col-lg-10">
                    <div class="input-group search-group h-100">
                        <span class="input-group-text bg-white border-0">
                            <i class="bi bi-search"></i>
                        </span>
                        <input
                            type="text"
                            id="accessLogsSearch"
                            name="search"
                            class="form-control border-0"
                            placeholder="Buscar por usuario o IP..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                {{-- Search button --}}
                <div class="col-lg-2 d-grid">
                    <button type="submit" id="searchAccessLogsBtn" class="btn btn-success h-100" {{ request('search') ? '' : 'disabled' }}>
                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3">
                {{-- Role filter --}}
                <div class="col-md-6 col-lg-4">
                    <select id="accessLogsRoleFilter" name="role" class="form-select border-2 border-dark" onchange="this.form.submit()">
                        <option value="">Todos los Roles</option>
                        <option value="Usuario" {{ request('role') == 'Usuario' ? 'selected' : '' }}>Usuario</option>
                        <option value="Admin Mercado" {{ request('role') == 'Admin Mercado' ? 'selected' : '' }}>Administrador de Mercado</option>
                        <option value="Admin Inventario" {{ request('role') == 'Admin Inventario' ? 'selected' : '' }}>Administrador de Inventario</option>
                        <option value="Admin Facilidades" {{ request('role') == 'Admin Facilidades' ? 'selected' : '' }}>Administrador de Facilidad</option>
                        <option value="Admin Super" {{ request('role') == 'Admin Super' ? 'selected' : '' }}>Super Administrador</option>
                    </select>
                </div>

                {{-- Event filter --}}
                <div class="col-md-6 col-lg-4">
                    <select id="accessLogsEventFilter" name="event" class="form-select border-2 border-dark" onchange="this.form.submit()">
                        <option value="">Todos los Eventos</option>

                        {{-- Marketplace-related events --}}
                        <optgroup label="Mercado">
                            <option value="Crear publicación" {{ request('event') == 'Crear publicación' ? 'selected' : '' }}>Crear publicación</option>
                            <option value="Eliminar publicación" {{ request('event') == 'Eliminar publicación' ? 'selected' : '' }}>Eliminar publicación</option>
                            <option value="Calificar usuario" {{ request('event') == 'Calificar usuario' ? 'selected' : '' }}>Calificar usuario</option>
                            <option value="Crear reporte de usuario" {{ request('event') == 'Crear reporte de usuario' ? 'selected' : '' }}>Crear reporte de usuario</option>
                            <option value="Crear chat" {{ request('event') == 'Crear chat' ? 'selected' : '' }}>Crear chat</option>
                            <option value="Enviar mensaje" {{ request('event') == 'Enviar mensaje' ? 'selected' : '' }}>Enviar mensaje</option>
                        </optgroup>

                        {{-- Inventory-related events --}}
                        <optgroup label="Inventario">
                            <option value="Agregar equipo" {{ request('event') == 'Agregar equipo' ? 'selected' : '' }}>Agregar equipo</option>
                            <option value="Actualizar equipo" {{ request('event') == 'Actualizar equipo' ? 'selected' : '' }}>Actualizar equipo</option>
                            <option value="Marcar equipo para eliminación" {{ request('event') == 'Marcar equipo para eliminación' ? 'selected' : '' }}>Marcar equipo para eliminación</option>
                            <option value="Eliminar equipo" {{ request('event') == 'Eliminar equipo' ? 'selected' : '' }}>Eliminar equipo</option>
                            <option value="Creó solicitud" {{ request('event') == 'Creó solicitud' ? 'selected' : '' }}>Creó solicitud</option>
                            <option value="Solicitud pendiente de revisión" {{ request('event') == 'Solicitud pendiente de revisión' ? 'selected' : '' }}>Solicitud pendiente de revisión</option>
                            <option value="Aprobó solicitud" {{ request('event') == 'Aprobó solicitud' ? 'selected' : '' }}>Aprobó solicitud</option>
                            <option value="Rechazó solicitud" {{ request('event') == 'Rechazó solicitud' ? 'selected' : '' }}>Rechazó solicitud</option>
                            <option value="Devolución de equipo" {{ request('event') == 'Devolución de equipo' ? 'selected' : '' }}>Devolución de equipo</option>
                        </optgroup>

                        {{-- Facility-related events --}}
                        <optgroup label="Facilidades">
                            <option value="Agregar área" {{ request('event') == 'Agregar área' ? 'selected' : '' }}>Agregar área</option>
                            <option value="Eliminar área" {{ request('event') == 'Eliminar área' ? 'selected' : '' }}>Eliminar área</option>
                            <option value="Agregar evento de facilidad" {{ request('event') == 'Agregar evento de facilidad' ? 'selected' : '' }}>Agregar evento de facilidad</option>
                            <option value="Eliminar evento de facilidad" {{ request('event') == 'Eliminar evento de facilidad' ? 'selected' : '' }}>Eliminar evento de facilidad</option>
                            <option value="Guardar tarifas de facilidades" {{ request('event') == 'Guardar tarifas de facilidades' ? 'selected' : '' }}>Guardar tarifas de facilidades</option>
                            <option value="Importar eventos simulados" {{ request('event') == 'Importar eventos simulados' ? 'selected' : '' }}>Importar eventos simulados</option>
                        </optgroup>

                        {{-- User-management events --}}
                        <optgroup label="Usuarios">
                            <option value="Cambiar rol de usuario" {{ request('event') == 'Cambiar rol de usuario' ? 'selected' : '' }}>Cambiar rol de usuario</option>
                            <option value="Cambiar estado de usuario" {{ request('event') == 'Cambiar estado de usuario' ? 'selected' : '' }}>Cambiar estado de usuario</option>
                            <option value="Resolver reporte" {{ request('event') == 'Resolver reporte' ? 'selected' : '' }}>Resolver reporte</option>
                            <option value="Bloquear usuario" {{ request('event') == 'Bloquear usuario' ? 'selected' : '' }}>Bloquear usuario</option>
                        </optgroup>

                        {{-- Authentication/session events --}}
                        <optgroup label="Sesión">
                            <option value="Inicio de sesión" {{ request('event') == 'Inicio de sesión' ? 'selected' : '' }}>Inicio de sesión</option>
                            <option value="Cierre de sesión" {{ request('event') == 'Cierre de sesión' ? 'selected' : '' }}>Cierre de sesión</option>
                        </optgroup>
                    </select>
                </div>

                {{-- Date filter --}}
                <div class="col-md-6 col-lg-4">
                    <div class="date-picker-wrapper h-100">
                        <input
                            type="text"
                            id="accessLogsDateFilter"
                            name="date"
                            class="form-control border-dark border-2 py-2 date-picker-input"
                            placeholder="dd-mm-aaaa"
                            autocomplete="off"
                            inputmode="none"
                            value="{{ request('date') }}"
                        >

                        <button type="button"
                                class="date-picker-icon"
                                id="accessLogsDateFilterIcon"
                                aria-label="Abrir calendario del filtro de registros de acceso">
                            <i class="bi bi-calendar3"></i>
                        </button>
                    </div>
                </div>

                {{-- Reset filters button --}}
                <div class="col-md-auto">
                    <a href="{{ route('access_logs') }}" class="btn btn-outline-secondary">
                        Limpiar Filtros
                    </a>
                </div>
            </div>
        </form>

        {{--
            Access logs table container

            Displays:
            - timestamp
            - user name
            - user role
            - logged event/action
            - IP address
            - optional comment/details
        --}}
        <div class="card border rounded-4 shadow-sm overflow-hidden">
            <div class="card-body p-4 border-bottom">
                <h3 class="fw-bold mb-2">Registros de Acceso</h3>
                <p class="text-muted mb-0 fs-5"> Historial de actividades y acciones realizadas dentro del sistema.</p>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="accessLogsTable">
                    <thead class="bg-light">
                        <tr>
                            {{-- Timestamp column --}}
                            <th class="px-4 py-3 fw-bold text-nowrap" style="min-width: 220px;">
                                Marca de Tiempo
                                <div class="small mt-1">
                                    (mm/dd/yyyy) (hh:mm AM/PM)
                                </div>
                            </th>

                            {{-- User full name column --}}
                            <th class="px-4 py-3 fw-bold">Usuario</th>

                            {{-- Role badge column --}}
                            <th class="px-4 py-3 fw-bold">Rol</th>

                            {{-- Logged action/event column --}}
                            <th class="px-4 py-3 fw-bold">Evento</th>

                            {{-- IP address column --}}
                            <th class="px-4 py-3 fw-bold">Dirección IP</th>

                            {{-- Additional details/comment column --}}
                            <th class="px-4 py-3 fw-bold">Comentario</th>
                        </tr>
                    </thead>

                    <tbody id="accessLogsTbody">
                        {{-- Render each access log record --}}
                        @foreach($logs as $log)
                        <tr>
                            {{-- Timestamp converted to Puerto Rico timezone --}}
                            <td class="px-4 py-3">
                                {{ \Carbon\Carbon::parse($log->created_at)->timezone('America/Puerto_Rico')->format('m/d/Y h:i A') }}
                            </td>

                            {{-- User full name fallback --}}
                            <td class="px-4 py-3">
                                {{ trim(($log->user->first_name ?? '') . ' ' . ($log->user->last_name ?? '')) ?: 'Usuario' }}
                            </td>

                            {{-- Role badge with dynamic style based on saved role --}}
                            <td class="py-3 text-center align-middle">
                                @php
                                    $roleClass = match(trim($log->role)) {
                                        'Usuario' => 'badge-user',
                                        'Admin Super' => 'badge-super-admin',
                                        'Admin Inventario' => 'badge-inventory-admin',
                                        'Admin Facilidades' => 'badge-facility-admin',
                                        'Admin Mercado' => 'badge-market-admin',
                                        default => 'badge-user',
                                    };
                                @endphp

                                <span class="label-badge {{ $roleClass }}">
                                    {{ $log->role }}
                                </span>
                            </td>

                            {{-- Logged action --}}
                            <td class="px-4 py-3">{{ $log->action }}</td>

                            {{-- Stored IP address --}}
                            <td class="px-4 py-3">{{ $log->ip_address }}</td>

                            {{-- Optional log comment/details --}}
                            <td class="px-4 py-3">{{ $log->comment }}</td>
                        </tr>
                        @endforeach

                        {{-- Empty state row shown dynamically --}}
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

        {{-- Pagination controls for multi-page log results --}}
        @if ($logs->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{--CSV Download Toast Notification--}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="downloadToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2 market-toast"
             role="alert" aria-live="assertive" aria-atomic="true">

            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold pe-1 market-toast-body">
                    Tu documento se descargará en unos instantes.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2 market-toast-close"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar">
                </button>
            </div>
        </div>
    </div>
</x-layout>
