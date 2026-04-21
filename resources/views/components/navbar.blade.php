<div>
    <!-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin -->
    <!-- resources/views/components/navbar.blade.php -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <header class="p-3 bg-white border shadow-sm">
        <div class="container-fluid">
            <ul class="nav w-100 justify-content-between flex-wrap justify-content-between gap-2">

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('kinemarket') }}"
                       class="btn w-100 {{ request()->routeIs('kinemarket') || (request()->routeIs('my_messages') && request()->filled('post_id')) ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-shop"></i>
                        Kinemercado
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('marketplace_management') }}"
                       class="btn w-100
                       {{request()->routeIs('marketplace_management')? 'btn-success': 'btn-outline-success'}}">
                        <i class="bi bi-shop-window"></i>
                        Gestión de Mercado
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('kinventory') }}"
                       class="btn w-100
                        {{ request()->routeIs('kinventory') ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-box-seam"></i>
                        Kinventario
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('inventory_management') }}"
                       class="btn w-100
                            {{ request()->routeIs('inventory_management', 'inventory_management.borrows', 'inventory_management.inventory_statistics') ? 'btn-success' : 'btn-outline-success' }}">
                        <i class="bi bi-boxes"></i>
                        Gestión de Inventario
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('search_user') }}"
                       class="btn w-100
                         {{ request()->routeIs('search_user') ? 'btn-success' : 'btn-outline-success' }}">

                        <i class="bi bi-people"></i>

                        Buscar Usuarios
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('access_logs') }}"
                       class="btn w-100
                        {{ request()->routeIs('access_logs') ? 'btn-success' : 'btn-outline-success' }}">

                        <i class="bi bi-clipboard-check"></i>
                        Registros de Acceso
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('facility_management') }}"
                       class="btn w-100
                       {{ request()->routeIs('facility_management') ? 'btn-success' : 'btn-outline-success' }}">

                        <i class="bi bi-currency-dollar"></i>
                        Gestión de Costos (Mangual)
                    </a>
                </li>

            </ul>
        </div>
    </header>
</div>
