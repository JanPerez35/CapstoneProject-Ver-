<x-layout title="Registros de Acceso">
<x-navbar></x-navbar>

    @vite('resources/js/access_log_validation.js')

    <div class="container-fluid py-4 px-4">


        <div class="mb-4">
            <h1 class="fw-bold">Bienvenido al Panel de Administración</h1>
            <p> Aquí puedes monitorear acceso al sistema y costos de instalaciones.</p>
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
        <form id="accessLogsFilterForm" method="GET" action="{{ route('access_logs') }}" class="mb-5">
            <div class="row g-3 mb-3 align-items-stretch">
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
                            placeholder="Buscar por usuario, IP o detalles..."
                            value="{{ request('search') }}"
                        >
                    </div>
                </div>

                <div class="col-lg-2 d-grid">
                    <button type="submit" id="searchAccessLogsBtn" class="btn btn-success h-100" {{ request('search') ? '' : 'disabled' }}>
                        Buscar
                    </button>
                </div>
            </div>

            <div class="row g-3">
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

                <div class="col-md-6 col-lg-4">
                    <select id="accessLogsEventFilter" name="event" class="form-select border-2 border-dark" onchange="this.form.submit()">
                        <option value="">Todos los Eventos</option>
                        <optgroup label="Mercado">
                            <option value="Crear publicación" {{ request('event') == 'Crear publicación' ? 'selected' : '' }}>Crear publicación</option>
                            <option value="Eliminar publicación" {{ request('event') == 'Eliminar publicación' ? 'selected' : '' }}>Eliminar publicación</option>
                            <option value="Calificar usuario" {{ request('event') == 'Calificar usuario' ? 'selected' : '' }}>Calificar usuario</option>
                            <option value="Crear reporte de usuario" {{ request('event') == 'Crear reporte de usuario' ? 'selected' : '' }}>Crear reporte de usuario</option>
                            <option value="Crear chat" {{ request('event') == 'Crear chat' ? 'selected' : '' }}>Crear chat</option>
                            <option value="Enviar mensaje" {{ request('event') == 'Enviar mensaje' ? 'selected' : '' }}>Enviar mensaje</option>
                        </optgroup>
                        <optgroup label="Inventario">
                            <option value="Agregar equipo" {{ request('event') == 'Agregar equipo' ? 'selected' : '' }}>Agregar equipo</option>
                            <option value="Actualizar equipo" {{ request('event') == 'Actualizar equipo' ? 'selected' : '' }}>Actualizar equipo</option>
                            <option value="Marcar equipo para eliminación" {{ request('event') == 'Marcar equipo para eliminación' ? 'selected' : '' }}>Marcar equipo para eliminación</option>
                            <option value="Eliminar equipo" {{ request('event') == 'Eliminar equipo' ? 'selected' : '' }}>Eliminar equipo</option>
                            <option value="Solicitud de Préstamo" {{ request('event') == 'Solicitud de Préstamo' ? 'selected' : '' }}>Solicitud de Préstamo</option>
                            <option value="Creó solicitud" {{ request('event') == 'Creó solicitud' ? 'selected' : '' }}>Creó solicitud</option>
                            <option value="Aprobó solicitud" {{ request('event') == 'Aprobó solicitud' ? 'selected' : '' }}>Aprobó solicitud</option>
                            <option value="Rechazó solicitud" {{ request('event') == 'Rechazó solicitud' ? 'selected' : '' }}>Rechazó solicitud</option>
                            <option value="Devolución de equipo" {{ request('event') == 'Devolución de equipo' ? 'selected' : '' }}>Devolución de equipo</option>
                        </optgroup>
                        <optgroup label="Facilidades">
                            <option value="Agregar salón" {{ request('event') == 'Agregar salón' ? 'selected' : '' }}>Agregar salón</option>
                            <option value="Eliminar/procesar salones" {{ request('event') == 'Eliminar/procesar salones' ? 'selected' : '' }}>Eliminar/procesar salones</option>
                            <option value="Agregar evento de facilidad" {{ request('event') == 'Agregar evento de facilidad' ? 'selected' : '' }}>Agregar evento de facilidad</option>
                            <option value="Eliminar evento de facilidad" {{ request('event') == 'Eliminar evento de facilidad' ? 'selected' : '' }}>Eliminar evento de facilidad</option>
                            <option value="Guardar tarifas de facilidades" {{ request('event') == 'Guardar tarifas de facilidades' ? 'selected' : '' }}>Guardar tarifas de facilidades</option>
                            <option value="Importar eventos simulados" {{ request('event') == 'Importar eventos simulados' ? 'selected' : '' }}>Importar eventos simulados</option>
                        </optgroup>
                        <optgroup label="Usuarios">
                            <option value="Cambiar rol de usuario" {{ request('event') == 'Cambiar rol de usuario' ? 'selected' : '' }}>Cambiar rol de usuario</option>
                            <option value="Cambiar estado de usuario" {{ request('event') == 'Cambiar estado de usuario' ? 'selected' : '' }}>Cambiar estado de usuario</option>
                            <option value="Resolver reporte" {{ request('event') == 'Resolver reporte' ? 'selected' : '' }}>Resolver reporte</option>
                            <option value="Bloquear usuario" {{ request('event') == 'Bloquear usuario' ? 'selected' : '' }}>Bloquear usuario</option>
                        </optgroup>
                        <optgroup label="Sesión">
                            <option value="Inicio de sesión" {{ request('event') == 'Inicio de sesión' ? 'selected' : '' }}>Inicio de sesión</option>
                            <option value="Cierre de sesión" {{ request('event') == 'Cierre de sesión' ? 'selected' : '' }}>Cierre de sesión</option>
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('access_logs') }}" class="btn btn-outline-secondary">
                        Limpiar Filtros
                    </a>
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
                        <th class="px-4 py-3 fw-bold text-nowrap" style="min-width: 220px;">
                            Marca de Tiempo
                            <div class="small mt-1">
                                (mm/dd/yyyy) (hh:mm AM/PM)
                            </div>
                        </th>
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



