<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>

    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0" style="min-height: 650px;">

                <!-- LEFT SIDE (CHAT LIST) -->
                <div class="col-md-4 border-end">
                    <div class="p-4 border-bottom">
                        <a href="{{ url('/kinemarket') }}" class="text-decoration-none text-dark fw-semibold">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>
                        <h1 class="fw-bold mt-3 mb-1">Mensajes</h1>
                        <p class="text-muted mb-0">Chats relacionados con tus publicaciones</p>
                    </div>

                    <div class="p-3 border-bottom">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" placeholder="Buscar chats...">
                        </div>
                    </div>

                    <!-- Example Chat -->
                    <div class="p-4 bg-success bg-opacity-10 border-start border-4 border-success">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 48px; height: 48px;">
                                J
                            </div>

                            <div class="flex-grow-1">
                                <h5 class="mb-1 fw-bold">John Davis</h5>
                                <div class="text-muted">Sin mensajes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT SIDE (LIVEWIRE CHAT) -->
                <div class="col-md-8 d-flex flex-column">

                    <!-- HEADER -->
                    <div class="p-4 border-bottom">
                        <h4 class="fw-bold">Chat en tiempo real</h4>
                    </div>

                    <!-- 💬 LIVEWIRE CHAT COMPONENT -->
                    <div class="flex-grow-1">
                        @livewire('chatbox') 
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-layout>