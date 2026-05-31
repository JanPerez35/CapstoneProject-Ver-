
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    {{-- Ensures the landing page scales correctly on mobile devices --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MAIKINE Portal</title>

    {{-- Loads global compiled CSS and JavaScript assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

<main>

    {{--
         Hero Section
         Main introduction area for the MAIKINE landing page.
         Presents the purpose of the system and provides direct
         access to institutional authentication.
      --}}
    <section class="hero">

        {{-- Main informational content --}}
        <div>

            {{-- Application title --}}
            <h1>MAIKINE</h1>

            {{-- Short system description --}}
            <h2>
                Sistema de Mercado e Inventario para el Departamento de Kinesiología
            </h2>

            {{-- High-level overview of the platform --}}
            <p>
                MAIKINE centraliza las herramientas principales del Departamento de
                Kinesiología en una sola plataforma. Permite administrar inventario,
                manejar solicitudes de préstamo, publicar artículos deportivos en el
                mercado, comunicarse entre usuarios, gestionar querellas y
                dar seguimiento a estimaciones de costos operacionales en las áreas
                prestadas del Coliseo Rafael A. Mangual.
            </p>

            {{-- Institutional login button using UPRM SAML authentication --}}
            <div class="mt-5">
                <a href="{{ route('saml.login') }}" class="login-btn shadow">
                    Accede con tu cuenta UPRM*
                </a>
            </div>

        </div>

        {{-- Institutional portal information card --}}
        <div class="hero-card">

            {{-- Department logo --}}
            <img
                src="{{ asset('images/kine_logo.png') }}"
                class="hero-logo"
                alt="Logo de Kinesiología">

            {{-- Card title --}}
            <h3 class="fw-bold">
                Portal Institucional
            </h3>

            {{-- Access restriction notice --}}
            <p class="text-muted mb-0">
                Acceso exclusivo para usuarios registrados y autorizados mediante
                la cuenta institucional UPRM.
            </p>

        </div>

    </section>

    {{--
         Features Section
         Highlights the primary functionalities available within
         the MAIKINE platform.
         --}}
    <section class="features-section">

        {{-- Section heading --}}
        <h2 class="section-title">
            Funciones principales
        </h2>

        <div class="features-grid">

            {{-- Inventory Management --}}
            <div class="feature-card">
                <h3>Inventario</h3>
                <p>
                    Consulta, administración y seguimiento de equipos disponibles
                    del departamento.
                </p>
            </div>

            {{-- Equipment Lending --}}
            <div class="feature-card">
                <h3>Préstamos</h3>
                <p>
                    Solicitud, aprobación, rechazo y devolución de equipos
                    departamentales.
                </p>
            </div>

            {{-- Marketplace --}}
            <div class="feature-card">
                <h3>Mercado</h3>
                <p>
                    Publicación, revisión y manejo de artículos deportivos
                    dentro del mercado.
                </p>
            </div>

            {{-- Internal Messaging --}}
            <div class="feature-card">
                <h3>Mensajería</h3>
                <p>
                    Comunicación directa entre usuarios para coordinar compras
                    de artículos publicados.
                </p>
            </div>

            {{-- Administrative Functions --}}
            <div class="feature-card">
                <h3>Administración</h3>
                <p>
                    Manejo de querellas, exportación de reportes, bitácora de
                    acceso, manejo de usuarios, roles y estimaciones de costos
                    operacionales.
                </p>
            </div>

        </div>

    </section>

</main>

{{--
     Footer Notice
     Institutional access restriction disclaimer.
 --}}
<footer class="notice">

    *MAIKINE es de uso exclusivo para usuarios registrados en la
    Universidad de Puerto Rico, Recinto Universitario de Mayagüez (UPRM).

</footer>

{{--
     Blocked Account Notification Toast
     Displays a Bootstrap toast message when the user is denied
     access due to a blocked account.
 --}}
@if (session('toast_error'))

    <div class="toast-container position-fixed bottom-0 start-0 p-3"
         style="z-index: 9999;">

        <div id="blockedAccountToast"
             class="toast align-items-center
                    bg-danger-subtle
                    text-danger-emphasis
                    border border-danger-subtle
                    border-0 shadow"
             role="alert"
             aria-live="assertive"
             aria-atomic="true">

            <div class="d-flex">

                {{-- Error message --}}
                <div class="toast-body fw-semibold">
                    {{ session('toast_error') }}
                </div>

                {{-- Close button --}}
                <button type="button"
                        class="btn-close me-2 m-auto"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar">
                </button>

            </div>
        </div>
    </div>

    {{-- Automatically displays the toast when the page loads --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const toastElement =
                document.getElementById('blockedAccountToast');

            if (toastElement && window.bootstrap) {

                const toast = new bootstrap.Toast(
                    toastElement,
                    {
                        delay: 5000
                    }
                );

                toast.show();
            }
        });
    </script>

@endif

</body>
</html>

