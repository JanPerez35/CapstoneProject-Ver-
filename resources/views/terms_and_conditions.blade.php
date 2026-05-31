<!DOCTYPE html>
<html lang="es">
<head>
    {{-- Character encoding for proper Spanish text rendering --}}
    <meta charset="UTF-8">

    {{-- Responsive behavior for mobile and desktop screens --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Browser tab title --}}
    <title>Términos y Condiciones - Kinventory</title>

    {{-- Load the main compiled application CSS and JavaScript through Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /*
         * Sets a light background color for the full page
         * to keep the view visually clean and readable.
         */
        body {
            background: #f8f9fa;
        }

        /*
         * Limits the maximum width of the terms container
         * and centers it horizontally on the page.
         */
        .terms-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        /*
         * Styles the embedded PDF viewer so the document
         * occupies the full available width with a fixed height.
         */
        .pdf-frame {
            width: 100%;
            height: 900px;
            border: 0;
        }

        @media (max-width: 768px) {
            .pdf-frame {
                height: 60vh;
            }

            .card-body {
                padding: 1rem !important;
            }
        }

        /*
         * Visual style for the disabled confirmation button.
         * Prevents interaction and reduces opacity so users
         * can identify it as inactive.
         */
        .confirm-disabled {
            pointer-events: none;
            opacity: 0.65;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="terms-wrapper">

        {{-- Page title and short instruction for the user --}}
        <div class="mb-4">
            <h1 class="fw-bold">Términos y Condiciones de MAIKINE</h1>
            <p class="text-muted mb-0 fw-bold">
                Debes leer y aceptar los términos y condiciones antes de continuar.
            </p>
        </div>

        {{-- Main card containing the PDF viewer, acceptance form, and admin update section --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">

                {{-- Embedded PDF document containing the current Terms and Conditions --}}
                <div class="border rounded-4 overflow-hidden mb-4">
                    <iframe
                        id="termsPdfFrame"
                        src="{{ asset('documents/terms_conditions.pdf') }}?v={{ file_exists(public_path('documents/terms_conditions.pdf')) ? filemtime(public_path('documents/terms_conditions.pdf')) : time() }}"
                        class="pdf-frame"
                    ></iframe>
                </div>

                {{-- Warning notice shown until the user reaches the bottom of the page --}}
                <div id="scrollNotice" class="alert alert-warning rounded-4 mb-4">
                    Debes bajar hasta el final de la página para poder aceptarlo.
                </div>

                {{-- Acceptance checkbox, initially disabled until the user reaches the bottom --}}
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="acceptTermsCheck" disabled>
                    <label class="form-check-label fw-semibold" for="acceptTermsCheck">
                        Acepto los términos y condiciones
                    </label>
                </div>

                {{-- Form used by the user to confirm acceptance of the terms --}}
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

                {{--
                    Admin-only section.
                    Allows the Super Admin to upload a new Terms and Conditions PDF.
                --}}

                {{-- Only show the update button when the terms and conditions are accessed throw the footer link--}}
                @if($allowUpdate ?? false)
                    <form method="POST" action="{{ route('terms.update') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- File input for uploading a replacement PDF --}}
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

                        {{-- Submit button to replace the current Terms and Conditions PDF --}}
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
    /*
     * Wait until the DOM is fully loaded before attaching
     * event listeners and initializing the interaction logic.
     */
    document.addEventListener('DOMContentLoaded', function () {
        /*
         * References to the interactive elements used in the
         * acceptance workflow.
         */
        const acceptTermsCheck = document.getElementById('acceptTermsCheck');
        const confirmTermsBtn = document.getElementById('confirmTermsBtn');
        const scrollNotice = document.getElementById('scrollNotice');

        /*
         * Tracks whether the user has reached the bottom
         * of the page at least once.
         */
        let reachedBottom = false;

        /*
         * Checks whether the user has scrolled to the bottom
         * of the page. Once reached, it enables the checkbox
         * and updates the warning message to a success message.
         */
        function checkIfReachedBottom() {
            const scrollTop = window.scrollY || window.pageYOffset;
            const viewportHeight = window.innerHeight;
            const fullHeight = document.documentElement.scrollHeight;

            if (scrollTop + viewportHeight >= fullHeight - 80) {
                reachedBottom = true;
                acceptTermsCheck.disabled = false;

                scrollNotice.classList.remove('alert-warning');
                scrollNotice.classList.add('alert-success');
                scrollNotice.textContent = 'Ya puedes aceptar los términos y condiciones.';
            }
        }

        /*
         * Re-check scrolling conditions whenever the user scrolls
         * or resizes the browser window.
         */
        window.addEventListener('scroll', checkIfReachedBottom);
        window.addEventListener('resize', checkIfReachedBottom);
        document.addEventListener('touchmove', checkIfReachedBottom);

        /*
         * Perform an initial check in case the page already fits
         * entirely within the viewport.
         */
        checkIfReachedBottom();

        /*
         * Enables the confirmation button only when:
         * 1. The user has reached the bottom of the page
         * 2. The acceptance checkbox is checked
         */
        acceptTermsCheck.addEventListener('change', function () {
            if (reachedBottom && acceptTermsCheck.checked) {
                confirmTermsBtn.classList.remove('confirm-disabled');
                confirmTermsBtn.disabled = false;
            } else {
                confirmTermsBtn.classList.add('confirm-disabled');
                confirmTermsBtn.disabled = true;
            }
        });

        /*
         * Additional safeguard to prevent submission if the
         * button is still visually and functionally disabled.
         */
        confirmTermsBtn.addEventListener('click', function (e) {
            if (confirmTermsBtn.classList.contains('confirm-disabled')) {
                e.preventDefault();
            }
        });
    });
</script>

</body>
</html>
