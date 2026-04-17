<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Términos y Condiciones - Kinventory</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background: #f8f9fa;
        }

        .terms-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        .pdf-frame {
            width: 100%;
            height: 900px;
            border: 0;
        }

        .confirm-disabled {
            pointer-events: none;
            opacity: 0.65;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="terms-wrapper">
        <div class="mb-4">
            <h1 class="fw-bold">Términos y Condiciones de MAIKINE</h1>
            <p class="text-muted mb-0 fw-bold">
                Debes leer y aceptar los términos y condiciones antes de continuar.
            </p>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                <div class="border rounded-4 overflow-hidden mb-4">
                    <iframe
                        id="termsPdfFrame"
                        src="{{ asset('documents/terms_conditions.pdf') }}"
                        class="pdf-frame"
                    ></iframe>
                </div>

                <div id="scrollNotice" class="alert alert-warning rounded-4 mb-4">
                    Debes bajar hasta el final de la página para poder aceptarlo.
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="acceptTermsCheck" disabled>
                    <label class="form-check-label fw-semibold" for="acceptTermsCheck">
                        Acepto los términos y condiciones
                    </label>
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <form method="POST" action="{{ route('terms.accept') }}">
                        @csrf
                        <button
                            type="submit"
                            id="confirmTermsBtn"
                            class="btn btn-success px-4 py-2 fw-semibold confirm-disabled"
                            disabled
                        >
                            Confirmar
                        </button>
                    </form>
                </div>

                @if(auth()->check() && auth()->user()->role === 'Admin Super')
                    <form method="POST" action="{{ route('terms.update') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="terms_pdf" class="form-label fw-semibold">Actualizar PDF</label>
                            <input
                                type="file"
                                name="terms_pdf"
                                id="terms_pdf"
                                class="form-control"
                                accept="application/pdf"
                                required
                            >
                        </div>

                        <button type="submit" class="btn btn-outline-success">
                            Actualizar términos y condiciones
                        </button>
                    </form>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const acceptTermsCheck = document.getElementById('acceptTermsCheck');
        const confirmTermsBtn = document.getElementById('confirmTermsBtn');
        const scrollNotice = document.getElementById('scrollNotice');

        let reachedBottom = false;

        function checkIfReachedBottom() {
            const scrollTop = window.scrollY || window.pageYOffset;
            const viewportHeight = window.innerHeight;
            const fullHeight = document.documentElement.scrollHeight;

            if (scrollTop + viewportHeight >= fullHeight - 10) {
                reachedBottom = true;
                acceptTermsCheck.disabled = false;

                scrollNotice.classList.remove('alert-warning');
                scrollNotice.classList.add('alert-success');
                scrollNotice.textContent = 'Ya puedes aceptar los términos y condiciones.';
            }
        }

        window.addEventListener('scroll', checkIfReachedBottom);
        window.addEventListener('resize', checkIfReachedBottom);
        checkIfReachedBottom();

        acceptTermsCheck.addEventListener('change', function () {
            if (reachedBottom && acceptTermsCheck.checked) {
                confirmTermsBtn.classList.remove('confirm-disabled');
                confirmTermsBtn.disabled = false;
            } else {
                confirmTermsBtn.classList.add('confirm-disabled');
                confirmTermsBtn.disabled = true;
            }
        });

        confirmTermsBtn.addEventListener('click', function (e) {
            if (confirmTermsBtn.classList.contains('confirm-disabled')) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>