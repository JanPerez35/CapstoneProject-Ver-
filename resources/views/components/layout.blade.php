@props([
    'title' => 'MAIKINE'
])
<!doctype html>
<html lang="en">
@vite(['resources/css/app.css', 'resources/js/app.js'])
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$title}}</title>
</head>
<body>
<div class="container">
    <header
        class="d-flex flex-wrap justify-content-center py-3 mb-4 border-bottom"
    >
        <a
            href="/"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none"
        >
            <svg class="bi me-2" width="40" height="32" aria-hidden="true">
                <use xlink:href="#bootstrap"></use>
            </svg>
            <span class="fs-4 fw-bold text-success">MAIKINE</span>
        </a>
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="#" class="nav-link active bg-success" aria-current="page">Mi perfil</a>
            </li>
            <li class="nav-item"><a href="#" class="nav-link text-success" >Cerrar Sesión</a></li>
        </ul>
    </header>
</div>
<main>{{$slot}}</main>
</body>
</html>
