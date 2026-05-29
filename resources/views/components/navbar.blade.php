<div>
    {{--Load global styles and scripts for the navbar--}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--Main navigation header used across all system modules--}}
    <header class="p-3 app-navbar border">
        <div class="container-fluid">

            {{--Navigation menu containing links to all major system sections--}}
            <ul class="nav w-100 justify-content-between flex-wrap justify-content-between gap-2">


                {{--Kinemercado navigation button:
                    - Redirects to marketplace page
                    - Highlights when user is in the kinemarketplace or messaging related to a post--}}
                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('kinemarket') }}"
                       class="btn w-100 {{ request()->routeIs('kinemarket') || (request()->routeIs('my_messages') && request()->filled('post_id')) ? 'btn-success' : 'btn-outline-success' }}"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-custom-class="custom-tooltip"
                       data-bs-title="Mercado comunitario para compartir equipos deportivos, conectar y gestionar sus publicaciones!">
                        <i class="bi bi-shop"></i>
                        Kinemercado
                    </a>
                </li>

                {{--Gestión de Mercado navigation button:
                    - Redirects to kinemarketplace management page
                    - Highlights when user is in the kinemarketplace management view
                    - Only Administrador de Mercado and Super Administrador has access to see this navbar--}}
                @if(auth()->check() && in_array(auth()->user()->role, ['Administrador de Mercado', 'Super Administrador']))
                    <li class="nav-item flex-fill text-center">
                        <a href="{{ route('marketplace_management') }}"
                        class="btn w-100
                        {{request()->routeIs('marketplace_management')? 'btn-success': 'btn-outline-success'}}"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           data-bs-custom-class="custom-tooltip"
                           data-bs-title="Manejar querellas, moderar publicaciones reportadas y resolver incidencias de publicaciones!">
                            <i class="bi bi-shop-window"></i>
                            Gestión de Mercado
                        </a>
                    </li>
                @endif

                {{--Kinventario navigation button:
                    - Redirects to equipment browsing page
                    - Highlights when user is in the kinventory--}}
                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('kinventory') }}"
                       class="btn w-100
                        {{ request()->routeIs('kinventory') ? 'btn-success' : 'btn-outline-success' }}"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       data-bs-custom-class="custom-tooltip"
                       data-bs-title="Pide prestado equipo deportivo!">
                        <i class="bi bi-box-seam"></i>
                        Kinventario
                    </a>
                </li>

                {{--Gestión de Inventario navigation button:
                   - Covers multiple subroutes (borrows, statistics,and management)
                   - Highlights when user is in the kinventory management view or any of its subviews
                   - Only Administrador de Inventario and Super Administrador has access to see this navbar--}}
                @if(auth()->check() && in_array(auth()->user()->role, ['Administrador de Inventario', 'Super Administrador']))
                    <li class="nav-item flex-fill text-center">
                        <a href="{{ route('inventory_management') }}"
                        class="btn w-100
                        {{ request()->routeIs('inventory_management', 'inventory_management.borrows', 'inventory_management.inventory_statistics') ? 'btn-success' : 'btn-outline-success' }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Manejar equipo, préstamos y estadísticas del inventario!">
                            <i class="bi bi-boxes"></i>
                            Gestión de Inventario
                        </a>
                    </li>
                @endif

                {{--Buscar Usuarios navigation button:
                    - Redirects to user search page
                    - Highlights when the super admin is in the search users view
                    - Only Super Administrador has access to see this navbar--}}
                @if(auth()->check() && in_array(auth()->user()->role, ['Super Administrador']))
                    <li class="nav-item flex-fill text-center">
                        <a href="{{ route('search_user') }}"
                        class="btn w-100
                            {{ request()->routeIs('search_user') ? 'btn-success' : 'btn-outline-success' }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="bottom"
                            data-bs-custom-class="custom-tooltip"
                            data-bs-title="Busca usuarios para cambiar su rol o estatus!">
                            <i class="bi bi-people"></i>

                            Buscar Usuarios
                        </a>
                    </li>
                @endif


                {{--Registros de Acceso navigation button:
                   - Redirects to access logs page
                   - Highlights when the super admin is in the access log view
                   - Only Super Administrador has access to see this navbar--}}
                @if(auth()->check() && in_array(auth()->user()->role, ['Super Administrador']))
                    <li class="nav-item flex-fill text-center">
                        <a href="{{ route('access_logs') }}"
                        class="btn w-100
                            {{ request()->routeIs('access_logs') ? 'btn-success' : 'btn-outline-success' }}"
                               data-bs-toggle="tooltip"
                               data-bs-placement="bottom"
                               data-bs-custom-class="custom-tooltip"
                               data-bs-title="Observa, filtra y descarga las actividades registradas de los usarios!">

                            <i class="bi bi-clipboard-check"></i>
                            Registros de Acceso
                        </a>
                    </li>
                @endif

                {{--Gestión de Costos navigation button:
                   - Redirects to facility cost management (Mangual)
                   - Highlights when the super admin or facility admin are in the facility cost management view
                   - Only Administrador de Instalaciones and Super Administrador has access to see this navbar--}}
                @if(auth()->check() && in_array(auth()->user()->role, ['Administrador de Instalaciones', 'Super Administrador']))
                    <li class="nav-item flex-fill text-center">
                        <a href="{{ route('facility_management') }}"
                        class="btn w-100
                        {{ request()->routeIs('facility_management') ? 'btn-success' : 'btn-outline-success' }}"
                           data-bs-toggle="tooltip"
                           data-bs-placement="bottom"
                           data-bs-custom-class="custom-tooltip"
                           data-bs-title="Gestiona tarifas, eventos y reportes de costos operacionales de las áreas internas del Coliseo Rafael Mangual!"
                            >

                            <i class="bi bi-currency-dollar"></i>
                            Gestión de Costos Operacionales
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </header>
</div>
