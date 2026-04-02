<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0" style="min-height: 650px;">

                <!-- ================= LEFT SIDE ================= -->
                <div class="col-md-4 border-end">
                    <div class="p-4 border-bottom">
                        @php
                           $volverUrl = request('return_to', route('kinemarket'));
                       @endphp
                       
                        <a href="{{ $volverUrl }}"
                           class="btn btn-outline-secondary rounded-3 px-4">
                            <i class="bi bi-arrow-left me-2"></i> Volver
                        </a>
                        <h1 class="fw-bold mt-3 mb-1">Mensajes</h1>
                        <p class="text-muted mb-0">Chats relacionados con tus publicaciones</p>
                    </div>

                    <!-- SEARCH -->
                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Buscar chats...">
                        </div>
                    </div>

                    <!-- CHAT LIST -->
                    <div class="p-4">
                        @php
                            $userId = auth()->id();

                            $chats = \App\Models\Chat::with(['buyer', 'seller', 'post'])
                                ->where('buyer_user_id', $userId)
                                ->orWhere('seller_user_id', $userId)
                                ->latest()
                                ->get();
                        @endphp

                        @forelse($chats as $chat)
                            @php
                                $isBuyer = $chat->buyer_user_id === $userId;
                                $otherUser = $isBuyer ? $chat->seller : $chat->buyer;
                            @endphp

                            <a href="{{ url('/chat/' . $chat->post_id . '/' . $otherUser->id) }}"
                               class="d-block p-3 mb-2 rounded text-decoration-none text-dark
                               {{ request()->is('chat/'.$chat->post_id.'/'.$otherUser->id)
                                    ? 'bg-success bg-opacity-10 border-start border-4 border-success'
                                    : '' }}">

                                <div class="d-flex align-items-start">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3"
                                         style="width: 40px; height: 40px;">
                                        {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                    </div>

                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold">{{ $otherUser->name }}</h6>
                                        <small class="text-muted">
                                            {{ $chat->post->title ?? 'Sin título' }}
                                        </small>
                                    </div>
                                </div>

                            </a>
                        @empty
                            <p class="text-muted">No tienes chats aún.</p>
                        @endforelse
                    </div>
                </div>

                <!-- ================= RIGHT SIDE ================= -->
                <div class="col-md-8 d-flex flex-column h-100">

                    @php
                        $selectedChat = null;
                        $otherUser = null;
                        $post = null;

                        if(isset($postId, $sellerId)) {
                            $userId = auth()->id();

                            $selectedChat = \App\Models\Chat::with(['buyer','seller','post'])
                                ->where('post_id', $postId)
                                ->where(function ($q) use ($userId, $sellerId) {
                                    $q->where([
                                        ['buyer_user_id', $userId],
                                        ['seller_user_id', $sellerId],
                                    ])->orWhere([
                                        ['buyer_user_id', $sellerId],
                                        ['seller_user_id', $userId],
                                    ]);
                                })
                                ->first();

                            if ($selectedChat) {
                                $otherUser = $selectedChat->buyer_user_id === $userId
                                    ? $selectedChat->seller
                                    : $selectedChat->buyer;

                                $post = $selectedChat->post;
                            }
                        }
                    @endphp

                    <!-- HEADER DINÁMICO -->
                    <div class="p-4 border-bottom">
                        @if($selectedChat && $otherUser)
                            <div class="d-flex justify-content-between align-items-center">

                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3"
                                         style="width: 48px; height: 48px;">
                                        {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                                    </div>

                                    <div>
                                        <h4 class="mb-1 fw-bold">
                                            {{ $otherUser->name }}
                                        </h4>

                                        <div class="text-muted">
                                            {{ $post->title }}
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="btn btn-success rounded-3 px-4"
                                    data-bs-toggle="modal"
                                    data-bs-target="#chatPostDetailsModal"
                                    data-post-id="{{ $post->id }}">
                                    <i class="bi bi-eye me-2"></i> Ver Publicación
                                </button>

                            </div>
                        @else
                            <h4 class="fw-bold text-muted">
                                Selecciona un chat
                            </h4>
                        @endif
                    </div>

                    <!-- CHAT -->
                    <div class="flex-grow-1 d-flex flex-column">

                        @if($selectedChat)
                            <div class="flex-grow-1 d-flex">
                                <div class="w-100 h-100">
                                    @livewire('chatbox', [
                                        'postId' => $postId,
                                        'sellerId' => $sellerId
                                    ])
                                </div>
                            </div>
                        @else
                            <div class="flex-grow-1 d-flex justify-content-center align-items-center text-muted">
                                <div class="text-center">
                                    <i class="bi bi-chat fs-1 mb-3"></i>
                                    <h4>Selecciona un chat</h4>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>

            </div>
        </div>
    </div>
    @if($selectedChat && $post)

   <div class="modal fade" id="chatPostDetailsModal" tabindex="-1" aria-labelledby="chatPostDetailsModalLabel" aria-hidden="true">
       <div class="modal-dialog modal-dialog-scrollable modal-lg">
           <div class="modal-content rounded-4 border-0 shadow">

            <!-- HEADER -->
            <div class="modal-header border-0 pb-0 align-items-start">
                <div class="pe-4">
                    <h4 class="modal-title fw-bold mb-1" id="chatPostDetailsModalLabel">{{ $post->title }}</h4>
                    <p class="text-muted mb-0">Detalles de la Publicación</p>
                </div>
                <button type="button" class="btn-close mt-1" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body pt-3">
                <!-- CAROUSEL -->
                <div id="chatPostImagesCarousel" class="carousel slide mb-4">
                       <div class="carousel-indicators">
                        @if($post->photo_1_url)
                            <button type="button" data-bs-target="#chatPostImagesCarousel" data-bs-slide-to="0" class="active"></button>
                        @endif
                        @if($post->photo_2_url)
                            <button type="button" data-bs-target="#chatPostImagesCarousel" data-bs-slide-to="1" class="{{ !$post->photo_1_url ? 'active' : '' }}"></button>
                        @endif
                        @if($post->photo_3_url)
                            <button type="button" data-bs-target="#chatPostImagesCarousel" data-bs-slide-to="2" class="{{ !$post->photo_1_url && !$post->photo_2_url ? 'active' : '' }}"></button>
                        @endif
                    </div>
                    <div class="carousel-inner rounded-4 overflow-hidden post-carousel-inner">
                        @if($post->photo_1_url)
                            <div class="carousel-item active post-carousel-item">
                                <div class="carousel-image-box">
                                    <img src="{{ asset('storage/'.$post->photo_1_url) }}" class="w-100"
                                        alt="Imagen 1"
                                        class="post-carousel-img"
                                    >
                                </div>
                            </div>
                        @endif

                        @if($post->photo_2_url)
                            <div class="carousel-item {{ !$post->photo_1_url ? 'active' : '' }}">
                                <div class="carousel-image-box">
                                    <img src="{{ asset('storage/'.$post->photo_2_url) }}" class="w-100"
                                        alt="Imagen 2"
                                        class="post-carousel-img"
                                    >
                                </div>
                            </div>
                        @endif

                        @if($post->photo_3_url)
                            <div class="carousel-item {{ !$post->photo_1_url && !$post->photo_2_url ? 'active' : '' }}">
                                <div class="carousel-image-box">
                                    <img src="{{ asset('storage/'.$post->photo_3_url) }}" class="w-100"
                                        alt="Imagen 3"
                                        class="post-carousel-img"
                                    >
                                </div>
                            </div>
                        @endif

                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#chatPostImagesCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>

                    <button class="carousel-control-next" type="button" data-bs-target="#chatPostImagesCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>

                <!-- DESCRIPTION -->
                <p class="mb-3 text-muted">
                    {{ $post->description }}
                </p>

                <!-- RATING -->
                <div class="mb-3">
                    <span class="text-muted">Calificación:</span>
                    <span class="ms-2 text-warning">
                       <i class="bi bi-star-fill"></i>
                       <i class="bi bi-star-fill"></i>
                       <i class="bi bi-star-fill"></i>
                       <i class="bi bi-star-fill"></i>
                       <i class="bi bi-star-half"></i>
                   </span>
                    <strong class="ms-2">{{ $post->rating ?? 'N/A' }}</strong>

                </div>

                <hr>

                <!-- INFO -->
                <div class="row gy-3 pb-2">

                    <div class="col-6 text-muted">Precio:</div>
                    <div class="col-6 text-end fw-bold text-success">${{ $post->cost }}</div>

                    <div class="col-6 text-muted">Estado:</div>
                    <div class="col-6 text-end">
                       <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            {{ ucfirst($post->status) }}
                        </span>
                    </div>

                    <div class="col-6 text-muted">Condición:</div>
                    <div class="col-6 text-end">
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            {{ ucfirst($post->condition) }}
                        </span>
                    </div>

                    <div class="col-6 text-muted">Vendedor:</div>
                    <div class="col-6 text-end fw-bold">
                        {{ $post->user->name }}
                    </div>

                    <div class="col-6 text-muted">Categoría:</div>
                    <div class="col-6 text-end">
                        <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                            {{ ucfirst($post->category) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@endif
</x-layout>