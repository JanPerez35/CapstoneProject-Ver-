<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MAIKINE Portal</title>

    <!-- Bootstrap CDN, this is temporary until we figure out how to install it locally -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body{
            background: linear-gradient(135deg,#e8f5e9,#ffffff,#e8f5e9);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .logo{
            width:250px;
            height:250px;
            object-fit:contain;
        }

        .main-title{
            font-size:4rem;
            font-weight:bold;
        }

        .subtitle{
            font-size:1.5rem;
            color:#555;
        }

        .login-btn{
            padding:18px 40px;
            font-size:1.2rem;
            font-weight:600;
            background:#28a745;
            border:none;
        }

        .login-btn:hover{
            background:#218838;
        }

        .footer-text{
            font-size:0.9rem;
            color:#777;
            margin-top:30px;
        }
    </style>
</head>

<body>

<div class="text-center container">

    <!-- Logo -->
    <div class="mb-4">
        <img src="images/kines_logo.png" class="logo" alt="Kinesiología Logo">
    </div>

    <!-- Title -->
    <h1 class="main-title">MAIKINE</h1>
    <p class="subtitle">Portal del Departamento de Kinesiología</p>

    <!-- Button -->
    <div class="mt-4">
{{--        <a href="{{ route('saml.login') }}" class="btn login-btn text-white shadow">--}}

        <a href="{{ route('saml.login') }}" class="btn login-btn text-white shadow">
                Accede con tu cuenta UPRM*
            </a>
        </div>

    <!-- Footer -->
    <p class="footer-text fw-bold">
        *El portal del departamento de Kinesiologia llamado MAIKINE <br>
        es de uso exclusivo para usuarios registrados de la Universidad <br>
        de Puerto Rico Recinto de Mayagüez (UPRM)

    <p class="footer-text">
        Sistema de Marketplace e Inventario de Kinesiología
    </p>

</div>

</body>
</html>
