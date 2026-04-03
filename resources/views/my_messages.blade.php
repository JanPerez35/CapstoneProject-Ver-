<x-layout title="Mensajes - MAIKINE">
    <x-navbar></x-navbar>
    @vite('resources/js/pages/messages-profanity.js')
    <div class="container-fluid py-4">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="row g-0" style="min-height: 650px;">

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

                    <div class="p-4 bg-success bg-opacity-10 border-start border-4 border-success">
                        <div class="d-flex align-items-start">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 48px; height: 48px;">
                                J
                            </div>

                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-1 fw-bold">John Davis</h5>
                                    <span class="badge border text-dark rounded-pill">Vendedor</span>
                                </div>

                                <div class="text-muted mb-2">
                                    <i class="bi bi-box-seam me-1"></i>
                                    Baloncesto - Spalding
                                </div>

                                <div class="text-muted">Sin mensajes</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8 d-flex flex-column">
                    <div class="p-4 border-bottom">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3"
                                 style="width: 48px; height: 48px;">
                                J
                            </div>

                            <div>
                                <h4 class="mb-1 fw-bold">John Davis</h4>
                                <div class="text-muted">
                                    <i class="bi bi-box-seam me-1"></i>
                                    Baloncesto - Spalding
                                    <span class="badge border text-dark rounded-pill ms-2">Vendedor</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex-grow-1 d-flex flex-column justify-content-center align-items-center text-center text-muted">
                        <i class="bi bi-chat fs-1 mb-3"></i>
                        <h3 class="fw-normal">No hay mensajes aún</h3>
                        <p>Envía el primer mensaje para comenzar la conversación</p>
                    </div>

                    <div class="p-4 border-top">
                        <div class="input-group">
                            <input
                                type="text"
                                id="chatMessageInput"
                                class="form-control form-control-lg border-end-0"
                                placeholder="Escribe un mensaje..."
                                maxlength="1000"
                            >
                            <button
                                class="btn btn-success px-4"
                                id="sendChatMessageBtn"
                                type="button"
                            >
                                <i class="bi bi-send"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback d-block" id="chatMessageError"></div>
                    </div>
                </div>
                <div class="toast-container position-fixed bottom-0 start-0 p-3">
                    <div
                        id="chatProfanityToast"
                        class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
                        role="alert"
                        aria-live="assertive"
                        aria-atomic="true"
                        style="width: auto; max-width: 360px;"
                    >
                        <div class="d-flex">
                            <div class="toast-body fw-semibold">
                                Se detectó lenguaje inapropiado. Revisa el mensaje.
                            </div>
                            <button
                                type="button"
                                class="btn-close me-2 m-auto"
                                data-bs-dismiss="toast"
                                aria-label="Cerrar"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
