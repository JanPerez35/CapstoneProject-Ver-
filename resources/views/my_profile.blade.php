<x-layout title="Mi Perfil">
    <x-navbar></x-navbar>

    <div class="container py-4">

        {{-- Back button --}}
{{--        <div class="mb-4">--}}

{{--            <a href="{{ url()->previous() != url()->current() ? url()->previous() : route('kinemarket') }}"--}}
{{--               class="btn btn-outline-secondary rounded-3 px-4">--}}
{{--                <i class="bi bi-arrow-left me-2"></i> Volver--}}
{{--            </a>--}}
{{--        </div>--}}

        {{-- Profile summary card --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                            <h1 class="fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>
                            <span class="bg-primary-subtle text-primary-emphasis fw-semibold rounded-0 px-2 py-1">
                                Usuario
                            </span>
                        </div>
                        <p class="text-muted fs-4 mb-0">Miembro de MAIKINE</p>

                        {{-- Rating del usuario --}}
                        @php
                            $userRating = 4.3;
                            $reviewCount = 8;
                        @endphp

                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <span class="text-muted fw-medium">Calificación:</span>

                            <div class="rating-stars" style="--rating: {{ $userRating }};">
                                <div class="rating-stars-base">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>

                                <div class="rating-stars-fill">
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                    <i class="bi bi-star-fill"></i>
                                </div>
                            </div>

                            <span class="fw-bold">{{ number_format($userRating, 1) }}</span>
                            <span class="text-muted">({{ $reviewCount }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section tabs --}}
        <ul class="nav w-100 flex-wrap gap-2 mb-4" id="profileTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button
                    class="btn {{ request('tab') === 'requests' ? 'btn-outline-success' : 'btn-success' }} rounded-3 px-4 py-2"
                    id="posts-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#posts-pane"
                    type="button"
                    role="tab"
                    aria-controls="posts-pane"
                    aria-selected="{{ request('tab') === 'requests' ? 'false' : 'true' }}"
                >
                    <i class="bi bi-bag me-2"></i> Publicaciones (3)
                </button>
                            </li>


            <li class="nav-item" role="presentation">
                <button
                    class="btn {{ request('tab') === 'requests' ? 'btn-success' : 'btn-outline-success' }} rounded-3 px-4 py-2"
                    id="requests-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#requests-pane"
                    type="button"
                    role="tab"
                    aria-controls="requests-pane"
                    aria-selected="{{ request('tab') === 'requests' ? 'true' : 'false' }}"
                >
                    <i class="bi bi-clipboard-check me-2"></i>
                    Solicitudes de Artículos ({{ $requests->total() }})
                </button>
            </li>
        </ul>

        <div class="tab-content">

            {{-- Posts tab --}}
            <div class="tab-pane fade {{ request('tab') === 'requests' ? '' : 'show active' }}" id="posts-pane" role="tabpanel" aria-labelledby="posts-tab">

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4 post-card-wrapper">
                        <div class="card h-100 shadow-sm rounded-4 overflow-hidden border-0">

                            <img
                                src="{{ asset('images/kinventory_images/Baloncesto.jpg') }}"
                                class="card-img-top"
                                alt="Baloncesto - Spalding"
                                style="height: 300px; object-fit: cover;"
                            >

                            <div class="card-body d-flex flex-column p-4">

                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="fw-bold mb-0">Baloncesto - Spalding</h5>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Disponible
                                    </span>
                                </div>

                                <p class="text-muted mb-3">
                                    Balón de baloncesto tamaño oficial, uso interior/exterior.
                                </p>

                                <h3 class="fw-bold text-success mb-3">$25</h3>

                                <div class="d-flex gap-2 mb-3">
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Muy Bueno
                                    </span>
                                    <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                                        Baloncesto
                                    </span>
                                </div>

                                <div class="small text-muted mb-3">
                                    <div><i class="bi bi-person me-2"></i> John Davis</div>
                                    <div><i class="bi bi-star-fill text-warning me-2"></i> 4.3 (8)</div>
                                    <div><i class="bi bi-clock me-2"></i> hace 2 días</div>
                                </div>

                                <div class="mt-auto d-grid">
                                    <button
                                        type="button"
                                        class="btn btn-danger rounded-3 open-delete-post-modal"
                                        data-post-title="Baloncesto - Spalding"
                                    >
                                        Borrar
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- Requests tab --}}
            <div class="tab-pane fade {{ request('tab') === 'requests' ? 'show active' : '' }}" id="requests-pane" role="tabpanel" aria-labelledby="requests-tab">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-4">Solicitudes de Artículos</h2>

                        @forelse($requests as $request)
                            <div class="border rounded-4 p-4 mb-3">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h5 class="fw-bold mb-1">
                                            @if($request->items->count())
                                                @foreach($request->items as $item)
                                                    <div>{{ $item->equipment->description }} (x{{ $item->quantity }})</div>
                                                @endforeach
                                            @else
                                                Sin artículos
                                            @endif
                                        </h5>

                                        <p class="text-muted mb-0">
                                            Solicitado: {{ \Carbon\Carbon::parse($request->created_at)->format('m/d/Y') }}
                                        </p>

                                        @if($request->status === 'returned')
                                            <p class="text-muted mb-0">
                                                Devuelto: {{ \Carbon\Carbon::parse($request->end_time)->format('m/d/Y') }}
                                            </p>
                                        @endif
                                    </div>

                                    @php
                                        $statusClass = match($request->status) {
                                            'pending' => 'bg-warning text-dark',
                                            'approved', 'active' => 'bg-success',
                                            'rejected' => 'bg-danger',
                                            'returned', 'finished' => '',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    @if(in_array($request->status, ['returned', 'finished']))
                                        <span class="badge rounded-0 px-3 py-2" style="background-color:#e5e7eb; color:#374151;">
                                            Finalizado
                                        </span>
                                    @else
                                        <span class="badge {{ $statusClass }} rounded-0 px-3 py-2">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-info rounded-4 mb-0">
                                No tienes solicitudes registradas todavía.
                            </div>
                        @endforelse
                        @if($requests->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $requests->appends(['tab' => 'requests'])->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Delete confirmation modal --}}
    <div class="modal fade" id="deletePostModal" tabindex="-1" aria-labelledby="deletePostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="deletePostModalLabel">¿Seguro que quieres borrar?</h4>
                        <p class="text-muted mb-0" id="deletePostModalText">
                            Esta publicación será eliminada de la vista.
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body pt-3">
                    <div class="alert alert-warning rounded-4 mb-0">
                        Esta acción no se puede deshacer en esta vista.
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDeletePost">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profileTabButtons = document.querySelectorAll('#profileTabs button');
            const requestsTab = document.getElementById('requests-tab');
            const postsTab = document.getElementById('posts-tab');

            function syncTabButtonStyles(activeButton) {
                profileTabButtons.forEach((btn) => {
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                });

                activeButton.classList.remove('btn-outline-success');
                activeButton.classList.add('btn-success');
            }

            if (window.location.search.includes('tab=requests')) {
                syncTabButtonStyles(requestsTab);
            } else {
                syncTabButtonStyles(postsTab);
            }

            profileTabButtons.forEach((button) => {
                button.addEventListener('shown.bs.tab', function (event) {
                    syncTabButtonStyles(event.target);
                });
            });
        });
        </script>

</x-layout>
