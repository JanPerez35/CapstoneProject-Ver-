<x-layout title="Gestión de Mercado">
    <x-navbar></x-navbar>

    <div class="container py-4">

        {{-- Header --}}
        <div class="mb-4">
            <h1 class="fw-bold">Gestión de Mercado</h1>
            <p>
                Aquí podrás administrar los reportes del mercado.
            </p>
        </div>

        {{-- Internal nav --}}
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('marketplace_management') }}"
               class="btn btn-outline-success px-4 fw-semibold">
                <i class="bi bi-flag-fill me-1"></i>Reportes
            </a>

            <a href="{{ route('marketplace_management.admin_marketplace') }}"
               class="btn btn-success px-4 fw-semibold">
                Mercado Administrativo
            </a>
        </div>

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <h2 class="fw-bold mb-1">Mercado Administrativo</h2>
                <p class="text-muted mb-0">
                    Revisa publicaciones, editarlos o eliminarlos.
                </p>
            </div>
        </div>

    </div>
</x-layout>
