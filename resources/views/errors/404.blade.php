<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ruta no encontrada | MAIKINE</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body style="min-height: 100vh; background: linear-gradient(135deg, #f4f8f1 0%, #ffffff 45%, #e8f5e9 100%);">

<main class="min-vh-100 d-flex align-items-center justify-content-center px-3 py-5">

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
         style="max-width: 620px; width: 100%;">

        <div class="card-body p-0">

            {{-- Top green bar --}}
            <div class="py-3 px-4 text-white"
                 style="background: linear-gradient(90deg, #198754, #2fb344);">
                <div class="d-flex align-items-center gap-3 justify-content-center">
                    <img
                        src="{{ asset('images/kine_logo.png') }}"
                        alt="Logo de Kinesiología"
                        style="width: 54px; height: 54px; object-fit: contain; background: white; border-radius: 50%; padding: 6px;"
                    >

                    <div class="text-start">
                        <div class="fw-bold fs-5 lh-sm">MAIKINE</div>
                        <div class="small opacity-75">Portal del Departamento de Kinesiología</div>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="p-5 text-center">


                <h1 class="fw-bold mb-3" style="color: #1f2937;">
                    Ruta no encontrada
                </h1>

                <p class="text-muted mb-4 fs-5">
                    La página que intentas abrir no existe.
                </p>


                <a href="{{ url('/kinemercado') }}"
                   class="btn btn-success btn-lg px-4 rounded-3 shadow-sm">
                        Volver a MAIKINE
                    </a>

            </div>
        </div>
    </div>

</main>

</body>
</html>
