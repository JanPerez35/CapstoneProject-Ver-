<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    {{-- Makes the page scale correctly on phones instead of shrinking the desktop layout --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MAIKINE Portal</title>

    {{-- Load global compiled CSS and JavaScript assets --}}
    {{-- Bootstrap styling is currently coming through the app bundle --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Full-page centered layout with soft green institutional background as requested by client */
        body {
            background: linear-gradient(135deg, #e8f5e9, #ffffff, #e8f5e9);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        /* Main logo image styling */
        .logo {
            width: 250px;
            height: 250px;
            object-fit: contain;
        }

        /* Main application title */
        .main-title {
            font-size: 4rem;
            font-weight: bold;
        }

        /* Subtitle below the application title */
        .subtitle {
            font-size: 1.5rem;
            color: #555;
        }

        /* Main login button */
        .login-btn {
            padding: 18px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            background: #176333;
            border: none;
            color: #ffffff;
        }

        /* Hover state for login button */
        .login-btn:hover {
            background: #124f29;
            color: #ffffff;
        }

        /* Keeps the login button from turning white when clicked or focused */
        .login-btn:focus,
        .login-btn:active,
        .login-btn:focus-visible,
        .login-btn:active:focus {
            background: #176333 !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 0 0 0.25rem rgba(23, 99, 51, 0.35) !important;
        }

        /* Footer informational text */
        .footer-text {
            font-size: 0.9rem;
            color: #777;
            margin-top: 30px;
        }

        /* Makes the portal landing page easier to read and use on phones,
           while keeping the original desktop layout unchanged. */
        @media (max-width: 576px) {
            body {
                padding: 24px 18px;
                align-items: center;
            }

            .container {
                max-width: 100%;
            }

            .logo {
                width: 190px;
                height: 190px;
            }

            .main-title {
                font-size: 3.2rem;
                line-height: 1.1;
                margin-bottom: 10px;
            }

            .subtitle {
                font-size: 1.2rem;
                line-height: 1.35;
                color: #444;
                margin-bottom: 0;
            }

            .login-btn {
                width: 100%;
                max-width: 340px;
                padding: 16px 18px;
                font-size: 1.05rem;
                border-radius: 12px;
            }

            .footer-text {
                font-size: 0.9rem;
                line-height: 1.5;
                margin-top: 24px;
                padding: 0 4px;
            }
        }

        @media (max-width: 380px) {
            .logo {
                width: 170px;
                height: 170px;
            }

            .main-title {
                font-size: 2.8rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .login-btn {
                font-size: 1rem;
            }

            .footer-text {
                font-size: 0.85rem;
            }
        }
    </style>
</head>

<body>

<main class="text-center container">

    {{-- Portal logo section --}}
    <div class="mb-4">
        <img src="images/kine_logo.png" class="logo" alt="Kinesiología Logo">
    </div>

    {{-- Portal title and subtitle --}}
    <h1 class="main-title">MAIKINE</h1>
    <p class="subtitle">Sistema de Mercado e Inventario para el Departamento de Kinesiología</p>

    {{-- Authentication button using institutional UPRM login --}}
    <div class="mt-4">
        <a href="{{ route('saml.login') }}" class="btn login-btn text-white shadow">
            Accede con tu cuenta UPRM*
        </a>
    </div>

    {{-- Institutional access restriction notice --}}
    <p class="footer-text fw-bold">
        *El sistema de mercado e Inventario para el Departamento de Kinesiología llamado MAIKINE <br>
        es de uso exclusivo para usuarios registrados en la Universidad
        de <br>  Puerto Rico Recinto de Mayagüez (UPRM)
    </p>

    {{-- Short system description --}}
    <p class="footer-text">
        Portal para del Departamento de Kinesiología
    </p>

</main>

@if (session('toast_error'))
    <div class="toast-container position-fixed bottom-0 start-0 p-3" style="z-index: 9999;">
        <div id="blockedAccountToast"
             class="toast align-items-center bg-danger-subtle text-danger-emphasis border border-danger-subtle border-0 shadow"
             role="alert"
             aria-live="assertive"
             aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-semibold">
                    {{ session('toast_error') }}
                </div>

                <button type="button"
                        class="btn-close  me-2 m-auto"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"></button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toastElement = document.getElementById('blockedAccountToast');

            if (toastElement && window.bootstrap) {
                const toast = new bootstrap.Toast(toastElement, {
                    delay: 5000
                });

                toast.show();
            }
        });
    </script>
@endif
</body>
</html>
