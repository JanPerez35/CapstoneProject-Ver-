@props([
    'title' => 'MAIKINE'
])
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$title}}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container-fluid px-0">
    <!--Webpage Header-->
    <header class="d-flex flex-wrap align-items-center justify-content-between py-2 px-2 border-bottom bg-light">

        <!-- Left-side -->
        <a href="/kinemercado"
           class="d-flex align-items-center mb-2 mb-md-0 text-decoration-none">

            <img src="/images/kines_logo.png"
                 alt="Logo"
                 style="height: 75px; width:auto;"
                 class="me-2">

            <span class="fs-3 fw-bold text-success m-0">MAIKINE</span>
        </a>

        <!-- Right-side-->
        <div class="d-flex align-items-center text-end">
            <a href="/search_user" class="btn btn-outline-success me-2">
                <i class="bi bi-person-fill"></i>
                Mi perfil
            </a>

            <a href="/" class="btn btn-success">
                <i class="bi bi-box-arrow-right"></i>
                Cerrar Sesión
            </a>
        </div>

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

        <!--All rights reserved & terms and condition information-->
        <div class="text-center pb-3">
            <p class="mb-1">
                © 2026 MAIKINE - Portal del Departamento de Kinesiología | Colegio de Artes y Ciencias | Recinto Universitario de Mayagüez |<br> Universidad de Puerto Rico.
                Todos los derechos reservados.
            </p>
            <a href="" class="text-success d-block mb-1">
                Terminos y Condiciones
            </a>
            <small class="text-muted">
                Sistema exclusivo para comunidad de UPRM
            </small>
        </div>
    </div>
</footer>
</body>
</html>
