<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAIKINE Portal</title>

    {{-- Load global compiled CSS and JavaScript assets --}}
    {{-- Bootstrap styling is currently coming through the app bundle --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Full-page centered layout with soft green institutional background as requested by client */
        body{
            background: linear-gradient(135deg,#e8f5e9,#ffffff,#e8f5e9);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        /* Main logo image styling */
        .logo{
            width:250px;
            height:250px;
            object-fit:contain;
        }

        /* Main application title */
        .main-title{
            font-size:4rem;
            font-weight:bold;
        }

        /* Subtitle below the application title */
        .subtitle{
            font-size:1.5rem;
            color:#555;
        }

        /* Main login button */
        .login-btn{
            padding:18px 40px;
            font-size:1.2rem;
            font-weight:600;
            background:#28a745;
            border:none;
        }

        /* Hover state for login button */
        .login-btn:hover{
            background:#218838;
        }

        /* Footer informational text */
        .footer-text{
            font-size:0.9rem;
            color:#777;
            margin-top:30px;
        }
    </style>
</head>

<body>

<div class="text-center container">

    {{-- Portal logo section --}}
    <div class="mb-4">
        <img src="images/kine_logo.png" class="logo" alt="Kinesiología Logo">
    </div>

    {{-- Portal title and subtitle --}}
    <h1 class="main-title">MAIKINE</h1>
    <p class="subtitle">Portal del Departamento de Kinesiología</p>

    {{-- Authentication button using institutional UPRM login --}}
    <div class="mt-4">
        <a href="{{ route('saml.login') }}" class="btn login-btn text-white shadow">
            Accede con tu cuenta UPRM*
        </a>
    </div>

    {{-- Institutional access restriction notice --}}
    <p class="footer-text fw-bold">
        *El portal del departamento de Kinesiología llamado MAIKINE <br>
        es de uso exclusivo para usuarios registrados de la Universidad <br>
        de Puerto Rico Recinto de Mayagüez (UPRM)
    </p>

    {{-- Short system description --}}
    <p class="footer-text">
        Sistema de Marketplace e Inventario de Kinesiología
    </p>

</div>

</body>
</html>
