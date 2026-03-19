<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAIKINE</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    {{-- Main Navbar --}}
<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('marketplace.index') }}">
            MAIKINE
        </a>

        <div class="ms-auto d-flex align-items-center gap-2">
            <span class="text-muted">Usuario</span>
            <a href="#" class="btn btn-outline-secondary btn-sm">Mi Perfil</a>
            <a href="#" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
        </div>
    </div>
</nav>

{{-- Secondary Navigation --}}
<div class="bg-light border-bottom">
    <div class="container py-2 d-flex gap-4">

        <a href="{{ route('marketplace.index') }}"
           class="text-decoration-none fw-semibold {{ request()->routeIs('marketplace.*') ? 'text-success' : 'text-dark' }}">
            KineMercado
        </a>

        <a href="{{ route('inventory.index') }}"
           class="text-decoration-none fw-semibold {{ request()->routeIs('inventory.*') ? 'text-success' : 'text-dark' }}">
            Kinventario
        </a>

        <a href="{{ route('users.search') }}"
           class="text-decoration-none fw-semibold {{ request()->routeIs('users.*') ? 'text-success' : 'text-dark' }}">
            Buscar Usuarios
        </a>

    </div>
</div>

{{-- Content --}}
<main>
    @yield('content')
</main>

</body>
</html>