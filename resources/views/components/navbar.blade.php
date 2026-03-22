<div>
    <!-- Let all your things have their places; let each part of your business have its time. - Benjamin Franklin -->
    <!-- resources/views/components/navbar.blade.php -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <header class="p-3 bg-white border shadow-sm">
        <div class="container-fluid">
            <ul class="nav w-100 justify-content-between flex-wrap justify-content-between gap-2">

                <li class="nav-item flex-fill text-center">
                    <a href="#" class="btn btn-outline-success w-100">Kinemercado</a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="#" class="btn btn-outline-success w-100">Gestión de Mercado</a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('kinventory') }}"
                       class="btn w-100
   {{ request()->routeIs('kinventory') ? 'btn-success' : 'btn-outline-success' }}">
                        Kinventario
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('inventory_management') }}"
                       class="btn w-100
   {{ request()->routeIs('inventory_management') ? 'btn-success' : 'btn-outline-success' }}">
                        Gestión de Inventario
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="{{ route('search_user') }}"
                       class="btn w-100
   {{ request()->routeIs('search_user') ? 'btn-success' : 'btn-outline-success' }}">
                        Buscar Usuarios
                    </a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="#" class="btn btn-outline-success w-100">Registros de Acceso</a>
                </li>

                <li class="nav-item flex-fill text-center">
                    <a href="#" class="btn btn-outline-success w-100">Gestión de Costos</a>
                </li>

            </ul>
        </div>
    </header>
</div>
