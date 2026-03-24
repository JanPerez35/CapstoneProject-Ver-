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
        class="d-flex flex-wrap justify-content-center py-3"
    >
        <a
            href="/"
            class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none"
        >
            <svg class="bi me-2" width="40" height="40" aria-hidden="true">
                <use xlink:href="#bootstrap"></use>
            </svg>
            <span class="fs-4 fw-bold text-success">MAIKINE</span>
        </a>
        <ul class="nav nav-pills">
            <li class="nav-item">
                <a href="#" class="btn btn-outline-success btn-md mx-3">
                    <i class="bi bi-person-fill"></i>
                    Mi perfil
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="btn btn-outline-success btn-md" >
                    <i class="bi bi-box-arrow-right"></i>
                    Cerrar Sesión
                </a>
            </li>
        </ul>
    </header>
</div>
<main>{{$slot}}</main>

<footer class="bg-light text-dark mt-5 pt-4 border-top">

    <div class="container">

        <div class="row text-start align-items-start">

            <!--The first column-->
            <div class="col-md-4 mb-3">
                <h5 class="d-flex align-items-center mb-2">
                <i class="bi bi-question-circle me-2 text-success"></i>
                 Ayuda y Soporte
                </h5>
                <p class="text-muted">
                    Soporte técnico y administración general del sistema
                </p>

                <p class="mb-1">
                    <i class="bi bi-envelope text-muted"></i>
                    <a href="mailto:superadmin@uprm.edu" class="text-success text-decoration-none">
                        superadmin@uprm.edu
                    </a>
                </p>

                <p>
                    <i class="bi bi-telephone text-muted"></i>
                    <span class="text-success">+1 (787)-832-4040 Ext. 3841, 2008</span>
                </p>
            </div>

            <!-- This is the second column-->
            <div class="col-md-4 mb-3">
                <h5>Departamento de Kinesiología</h5>
                <p class="text-muted mb-0 d-flex">
                    <i class="bi bi-geo-alt me-2"></i>
                    <span>
                    259 Norte Blvd. Alfonso Valdés Cobián<br>
                    Oficina A-2 Coliseo Rafael A. Mangual<br>
                    Mayagüez, Puerto Rico
                    </span>
                </p>
            </div>

            <!--This is the third column-->
            <div class="col-md-4 mb-3">
                <h5 class="mb-2">Contactos Adicionales</h5>

                <p class="mb-1">
                    <b>Kinventario</b><br>
                    <a href="mailto:inventario@kinesiologia.edu" class="text-success text-decoration-none">
                        inventario@uprm.edu
                    </a>
                </p>

                <p>
                    <b>Kinemercado</b><br>
                    <a href="mailto:mercado@kinesiologia.edu" class="text-success text-decoration-none">
                        mercado@uprm.edu
                    </a>
                </p>
            </div>

        </div>

        <hr>

        <div class="text-center pb-3">
            <p class="mb-1">
                © 2026 MAIKINE - Portal del Departamento de Kinesiología | Colegio de Artes y Ciencias | Recinto Universitario de Mayagüez |<br> Universidad de Puerto Rico.
                Todos los derechos reservados.
            </p>
            <small class="text-muted">
                Sistema exclusivo para comunidad de UPRM
            </small>
        </div>

    </div>
</footer>
</body>
</html>
