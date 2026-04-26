<x-layout title="Mi Perfil">
    <x-navbar></x-navbar>

    {{-- Loads the validation and UI behavior logic for the profile page --}}
    @vite('resources/js/my_profile_validation.js')

    {{-- Custom pagination styling specific to the profile view --}}
    <style>
        .profile-pagination .page-item .page-link {
            border: none;
            color: #198754;
            background-color: #e9ecef;
            min-width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            box-shadow: none;
        }

        .profile-pagination .page-item.active .page-link {
            background-color: #198754;
            color: white;
        }

        .profile-pagination .page-item:first-child .page-link {
            border-top-left-radius: 0.75rem;
            border-bottom-left-radius: 0.75rem;
        }

        .profile-pagination .page-item:last-child .page-link {
            border-top-right-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
        }

        .profile-pagination .page-item.disabled .page-link {
            color: #198754;
            background-color: #e9ecef;
            opacity: 0.65;
        }

        .profile-pagination .page-link:focus {
            box-shadow: none;
        }
    </style>

    <div class="container py-4">

        {{-- Profile summary card displaying user information and rating --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-3">

                    {{-- User identity and role --}}
                    <div>
                        <div class="d-flex align-items-center gap-3 flex-wrap mb-2">
                            <h1 class="fw-bold mb-0">{{ $user->first_name }} {{ $user->last_name }}</h1>

                            {{-- Role badge dynamically styled --}}
                            <span class="label-badge {{ $user->role_badge_class }}">
                                {{ $user->role_label }}
                            </span>
                        </div>

                        <p class="text-muted fs-4 mb-0">Miembro de MAIKINE</p>

                        {{-- Seller rating visualization --}}
                        <div class="d-flex align-items-center gap-2 mt-2 flex-wrap">
                            <span class="text-muted fw-medium">Calificación:</span>

                            <div class="rating-stars" style="--rating: {{ $sellerAverageRating }};">
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

                            <span class="fw-bold">{{ number_format($sellerAverageRating, 1) }}</span>
                            <span class="text-muted">({{ $sellerReviewsCount }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Navigation tabs for switching between posts and requests --}}
        <ul class="nav w-100 flex-wrap gap-2 mb-4 ps-3" id="profileTabs" role="tablist">
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
                    <i class="bi bi-bag me-2"></i> Publicaciones (<span id="postsTabCount">{{ $posts->count() }}</span>)
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

                {{-- Filter and search controls for user posts --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-1">Mis Publicaciones</h2>
                        <p class="text-muted mb-4">
                            Busca y filtra tus publicaciones por deporte o rango de precio.
                        </p>

                         {{-- Form used for client-side filtering of posts --}}
                        <form id="postsFilterForm" class="mb-0">
                            <div class="row g-3 align-items-stretch mb-3">
                                <div class="col-lg-10">
                                    <div class="input-group search-group h-100">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="bi bi-search"></i>
                                        </span>

                                        <input
                                            type="text"
                                            id="postSearch"
                                            class="form-control border-0"
                                            placeholder="Buscar publicaciones..."
                                        >
                                    </div>
                                </div>

                                <div class="col-lg-2 d-grid">
                                    <button type="submit" id="postsSearchBtn" class="btn btn-success h-100 fw-semibold" disabled>
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-lg-4">
                                    <select id="sportFilter" class="form-select border-2 border-dark">
                                        <option value="">Todos los Deportes</option>
                                        <option value="Baloncesto">Baloncesto</option>
                                        <option value="Tenis">Tenis</option>
                                        <option value="Fútbol">Fútbol</option>
                                        <option value="Deporte Recreativo">Deporte Recreativo</option>
                                        <option value="Volibol">Volibol</option>
                                        <option value="Levantamiento de Pesas">Levantamiento de Pesas</option>
                                        <option value="Otros">Otros</option>
                                    </select>
                                </div>

                                <div class="col-md-6 col-lg-4">
                                    <select id="priceFilter" class="form-select border-2 border-dark">
                                        <option value="all">Rango de Precios</option>
                                        <option value="0">Gratis</option>
                                        <option value="0.01-9.99">$0.01 - $9.99</option>
                                        <option value="10-29.99">$10.00 - $29.99</option>
                                        <option value="30-49.99">$30.00 - $49.99</option>
                                        <option value="50+">$50.00 o más</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <button type="button" id="clearPostsFilters" class="btn btn-outline-secondary">
                                        Limpiar Filtros
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Posts grid displaying all user publications --}}
                <div class="row g-4" id="postsGrid">

                    {{-- Single post card with metadata used for filtering --}}
                    @forelse($posts as $post)
                        <div
                            class="col-md-6 col-lg-4 post-card-wrapper"
                            data-title="{{ strtolower($post->title ?? '') }}"
                            data-description="{{ strtolower($post->description ?? '') }}"
                            data-sport="{{ strtolower($post->category ?? '') }}"
                            data-price="{{ $post->cost ?? 0 }}"
                        >
                            <div class="card h-100 shadow-sm rounded-4 overflow-hidden item-card border-0 marketplace-card-shell">
                                <img
                                    src="{{ $post->photo_1_url ? asset('storage/' . $post->photo_1_url) : asset('images/marketplace_images/picture-not-available.png') }}"
                                    class="card-img-top"
                                    alt="{{ $post->title }}"
                                    style="height: 220px; object-fit: contain;"
                                    onerror="this.onerror=null;this.src='{{ asset('images/marketplace_images/picture-not-available.png') }}';"
                                >

                                <div class="card-body p-4 marketplace-card-body">
                                    <div class="marketplace-card-top">

                                        <div class="d-flex justify-content-between align-items-start mb-2">

                                            <h5
                                                class="fw-bold mb-0 marketplace-card-title"
                                                title="{{ $post->title }}"
                                            >
                                                {{ $post->title }}
                                            </h5>
                                            <span class="label-badge badge-available">
                                                {{ $post->status ?? 'Disponible' }}
                                            </span>
                                        </div>

                                        <p
                                            class="text-muted mb-3 marketplace-card-description"
                                            title="{{ $post->description }}"
                                        >
                                            {{ $post->description  }}
                                        </p>

                                    </div>

                                    <div class="marketplace-card-meta mt-auto">

                                    <h3 class="fw-bold text-success mb-3">
                                        ${{ number_format($post->cost, 2) }}
                                    </h3>

                                    <div class="d-flex gap-2 mb-3 flex-wrap">
                                        <span class="label-badge badge-available">
                                            {{ $post->condition }}
                                        </span>

                                        <span class="label-badge badge-available">
                                            {{ $post->category }}
                                        </span>
                                    </div>

                                    <div class="small text-muted mb-3">
                                        <div>
                                            <i class="bi bi-person me-2"></i>
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div>
                                            <i class="bi bi-star-fill text-warning me-2"></i>
                                            {{ number_format($sellerAverageRating, 1) }} ({{ $sellerReviewsCount }})
                                        </div>
                                        <div>
                                            <i class="bi bi-clock me-2"></i>
                                            {{ $post->created_at?->diffForHumans() }}
                                        </div>
                                    </div> {{-- marketplace-card-meta --}}
                                    </div>

                                    <div class="mt-auto d-grid gap-2">
                                        <button
                                            type="button"
                                            class="btn btn-success rounded-3 open-profile-post-details"
                                            data-post-id="{{ $post->id }}"
                                        >
                                            Ver Detalles
                                        </button>

                                        <button
                                            type="button"
                                            class="btn btn-danger rounded-3 open-delete-post-modal"
                                            data-post-id="{{ $post->id }}"
                                            data-post-title="{{ $post->title }}"
                                        >
                                            Eliminar Publicación
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body py-5 text-center">
                                    <i class="bi bi-bag-x fs-1 text-muted"></i>
                                    <h4 class="fw-bold mt-3">No tienes publicaciones todavía</h4>
                                    <p class="text-muted mb-0">Cuando crees una publicación, aparecerá aquí.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse

                    {{-- Empty state shown when no posts match filters --}}
                    <div id="postsEmptyState" class="col-12 d-none">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body py-5 text-center">
                                <i class="bi bi-search fs-1 text-muted"></i>
                                <h4 class="fw-bold mt-3">No se encontraron publicaciones</h4>
                                <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro artículo.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <nav aria-label="Paginación de publicaciones" class="mt-4">
                    <ul class="pagination justify-content-center mb-0 profile-pagination" id="postsPagination"></ul>
                </nav>
            </div>

            {{-- Requests tab --}}
            <div class="tab-pane fade {{ request('tab') === 'requests' ? 'show active' : '' }}" id="requests-pane" role="tabpanel" aria-labelledby="requests-tab">

                {{-- Requests filtering and listing --}}
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold mb-1">Solicitudes de Artículos</h2>
                        <p class="text-muted mb-4">
                            Busca tus solicitudes y filtra por estado.
                        </p>

                        <form method="GET" action="{{ route('my_profile') }}" id="requestsFilterForm" class="mb-4">
                            <input type="hidden" name="tab" value="requests">

                            <div class="row g-3 align-items-stretch mb-3">
                                <div class="col-lg-10">
                                    <div class="input-group search-group h-100">
                                        <span class="input-group-text bg-white border-0">
                                            <i class="bi bi-search"></i>
                                        </span>

                                        {{-- Filter request by search bar --}}
                                        <input
                                            type="text"
                                            id="requestSearch"
                                            name="request_search"
                                            class="form-control border-0"
                                            placeholder="Buscar solicitudes..."
                                            value="{{ request('request_search') }}"
                                        >
                                    </div>
                                </div>

                                {{-- Search filter button --}}
                                <div class="col-lg-2 d-grid">
                                    <button type="submit" id="requestsSearchBtn" class="btn btn-success h-100 fw-semibold" disabled>
                                        Buscar
                                    </button>
                                </div>
                            </div>

                            {{-- Filter requests using the request status dropdown --}}
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6 col-lg-4">
                                    <select id="statusFilter" name="request_status" class="form-select border-2 border-dark">
                                        <option value="">Todos los Estados</option>
                                        <option value="pending" {{ request('request_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="approved" {{ request('request_status') == 'approved' ? 'selected' : '' }}>Aprobada</option>
                                        <option value="rejected" {{ request('request_status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                                        <option value="finished" {{ request('request_status') == 'finished' ? 'selected' : '' }}>Finalizado</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    {{-- Clean filters --}}

                                    <a
                                        href="{{ route('my_profile', ['tab' => 'requests']) }}"
                                        class="btn btn-outline-secondary"
                                        id="clearRequestsFilters"
                                    >
                                        Limpiar Filtros
                                    </a>
                                </div>
                            </div>
                        </form>

                        @forelse($requests as $request)
                            @php

                                /*
                                 * Prepares a readable string of items in the request.
                                 * Used for filtering and display purposes.
                                 */
                                $itemsText = $request->items->count()
                                    ? $request->items->map(fn($item) => $item->equipment->description . ' x' . $item->quantity)->implode(' ')
                                    : 'sin articulos';

                                /*
                                 * Normalizes request status for consistent filtering.
                                 */
                                $normalizedStatus = in_array($request->status, ['returned', 'finished'])
                                    ? 'finished'
                                    : strtolower($request->status);
                            @endphp

                            <div
                                class="border rounded-4 p-4 mb-3 request-card"
                                data-title="{{ strtolower($itemsText) }}"
                                data-status="{{ $normalizedStatus }}"
                            >
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        {{-- Show requests quantity and display the current requests --}}
                                        <h5 class="fw-bold mb-1">
                                            @if($request->items->count())
                                                @foreach($request->items as $item)
                                                    <div>{{ $item->equipment->description }} (x{{ $item->quantity }})</div>
                                                @endforeach
                                            @else
                                                Sin artículos
                                            @endif
                                        </h5>

                                        {{-- Item requested date --}}
                                        <p class="text-muted mb-0">
                                            Solicitado: {{ \Carbon\Carbon::parse($request->created_at)->format('m/d/Y') }}
                                        </p>

                                        {{-- Item returned or pending return date --}}
                                        @if($request->status === 'returned')
                                            <p class="text-muted mb-0">
                                                Devuelto: {{ \Carbon\Carbon::parse($request->end_time)->format('m/d/Y') }}
                                            </p>
                                        @endif
                                    </div>

                                    @php
                                        /*
                                         * Maps request status to a visual badge class.
                                         * Ensures consistent UI representation across states.
                                         */
                                        $statusClass = match($request->status) {
                                            'pending' => 'badge-request-pending',
                                            'approved', 'active' => 'badge-request-approved',
                                            'rejected' => 'badge-request-rejected',
                                            'returned', 'finished' => 'badge-request-finished',
                                            default => 'badge-request-default',
                                        };
                                    @endphp


                                    <span class="label-badge {{ $statusClass }}">
    {{
        match(strtolower($request->status)) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'active' => 'Aprobada',
            'rejected' => 'Rechazada',
            'returned' => 'Finalizado',
            'finished' => 'Finalizado',
            default => ucfirst($request->status),
        }
    }}
</span>

                                </div>
                            </div>

                        {{-- Empty state for lack of requests--}}
                        @empty
                            <div class="card border-0 shadow-sm rounded-4">
                                <div class="card-body py-5 text-center">
                                    <i class="bi bi-search fs-1 text-muted"></i>
                                    <h4 class="fw-bold mt-3">No se encontraron solicitudes</h4>
                                    <p class="text-muted mb-0">Intenta cambiar los filtros o buscar otro artículo.</p>
                                </div>
                            </div>
                        @endforelse


                        {{-- Connection for pagination --}}
                        @if($requests->hasPages())
                            <div class="mt-4 d-flex justify-content-center">

                                {{ $requests->appends([
     'tab' => 'requests',
     'request_search' => request('request_search'),
     'request_status' => request('request_status'),
 ])->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

     {{-- Modal used to display detailed information of a selected post --}}
    <div class="modal fade" id="profilePostDetailsModal" tabindex="-1" aria-labelledby="profilePostDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow overflow-hidden">
                <div class="modal-header border-0 pt-4 px-4 pb-2 align-items-start position-relative">
                    <div class="pe-5">
                        <h4 class="modal-title fw-bold mb-1" id="profilePostDetailsModalLabel">Detalle de la publicación</h4>
                        <p class="text-muted mb-0">Detalles de tu publicación</p>
                    </div>

                    <button
                        type="button"
                        class="btn-close position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body px-4 pt-2 pb-4 post-details-body">
                    {{-- Image carousel of the selected post --}}
                    <div id="profilePostImagesCarousel" class="carousel slide mb-4">
                        <div class="carousel-indicators" id="profilePostImagesCarouselIndicators"></div>

                        <div class="carousel-inner rounded-4 overflow-hidden post-carousel-inner" id="profilePostImagesCarouselInner"></div>

                        <button
                            class="carousel-control-prev"
                            type="button"
                            data-bs-target="#profilePostImagesCarousel"
                            data-bs-slide="prev"
                            id="profilePostImagesCarouselPrev"
                        >
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            {{-- Previous photo on the carousel --}}
                            <span class="visually-hidden">Anterior</span>
                        </button>

                        <button
                            class="carousel-control-next"
                            type="button"
                            data-bs-target="#profilePostImagesCarousel"
                            data-bs-slide="next"
                            id="profilePostImagesCarouselNext"
                        >
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            {{-- Next photo on the carousel --}}
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>

                    <p class="mb-3 text-muted" id="profilePostDetailsDescription"></p>
                    <hr>

                    <div class="row gy-3 pb-2">
                        {{-- Post price --}}
                        <div class="col-6 text-muted">Precio:</div>
                        <div class="col-6 text-end fw-bold text-success" id="profilePostDetailsPrice">$0.00</div>

                        {{-- Post current status --}}
                        <div class="col-6 text-muted">Estado:</div>
                        <div class="col-6 text-end">
                            <span class="label-badge badge-available" id="profilePostDetailsStatus">Disponible</span>
                        </div>

                        {{-- Post condition --}}
                        <div class="col-6 text-muted">Condición:</div>
                        <div class="col-6 text-end">
                            <span class="label-badge badge-available" id="profilePostDetailsCondition">Sin especificar</span>
                        </div>

                        {{-- Post owner name --}}
                        <div class="col-6 text-muted">Vendedor:</div>
                        <div class="col-6 text-end fw-bold" id="profilePostDetailsSeller">Usuario</div>

                        {{-- Post owner rating --}}
                        <div class="col-6 text-muted">Calificación del Vendedor:</div>
                        <div class="col-6 text-end" id="profilePostDetailsSellerRating">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            0.0 <span class="text-muted">(0 reseñas)</span>
                        </div>

                        {{-- Post item category --}}
                        <div class="col-6 text-muted">Categoría:</div>
                        <div class="col-6 text-end">
                            <span class="label-badge badge-available" id="profilePostDetailsCategory">Sin categoría</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Confirmation modal before removing a post from the view --}}
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

                {{-- Cancel post deletion --}}
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    {{-- Confirm post deletion --}}
                    <button type="button" class="btn btn-danger px-4" id="confirmDeletePost">
                        Continuar
                    </button>
                </div>
            </div>
        </div>
    </div>

     {{-- Toast notification displayed after a post is removed --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div
            id="deletePostToast"
            class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            style="width: auto; max-width: fit-content;"
        >
            <div class="d-flex align-items-center">
                {{-- Item deleted succesfully toast --}}
                <div class="toast-body fw-semibold rounded-0 pe-1">
                    Item borrado correctamente.
                </div>
                <button
                    type="button"
                    class="btn-close p-0 ms-1 me-2"
                    data-bs-dismiss="toast"
                    aria-label="Cerrar"
                    style="background-color: transparent; border: none; transform: scale(0.8);"
                ></button>
            </div>
        </div>
    </div>

</x-layout>
