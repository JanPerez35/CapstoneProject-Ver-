<x-layout title="Buscar Usuarios">
    <x-navbar></x-navbar>

    <div class="container py-4">
        <div class="mb-4">
            <h1 class="fw-bold">Buscar Usuarios</h1>
            <p class="text-muted mb-0">
                Buscar usuarios y ver sus perfiles, administrar roles y banear/desbanear cuentas
            </p>
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-lg-6">
                        <label for="userSearchInput" class="form-label fw-semibold">Buscar Usuarios</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-4">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                type="text"
                                id="userSearchInput"
                                class="form-control border-start-0 rounded-end-4"
                                placeholder="Buscar por nombre o correo..."
                            >
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <label for="roleFilterSelect" class="form-label fw-semibold">Filtrar por Rol</label>
                        <select id="roleFilterSelect" class="form-select rounded-4">
                            <option value="all" selected>Todos los Roles</option>
                            <option value="Usuario">Usuario</option>
                            <option value="Admin Super">Admin Super</option>
                            <option value="Admin Inventario">Admin Inventario</option>
                            <option value="Admin Facilidades">Admin Facilidades</option>
                            <option value="Admin Mercado">Admin Mercado</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Empty state --}}
        <div id="usersEmptyState" class="card border-0 shadow-sm rounded-4 d-none">
            <div class="card-body py-5 text-center">
                <i class="bi bi-people fs-1 text-muted"></i>
                <h4 class="fw-bold mt-3">No se encontraron usuarios</h4>
                <p class="text-muted mb-0">Intenta con otro nombre, correo o filtro de rol.</p>
            </div>
        </div>

        {{-- Users list --}}
        <div id="usersList" class="d-grid gap-3">

            {{-- User 1 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="John Davis"
                 data-email="john.davis@university.edu"
                 data-role="Usuario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            John Davis
                                        </a>

                                        <span class="badge rounded-pill user-role-badge bg-primary-subtle text-primary-emphasis px-2 py-1 small">
                                            <i class="bi bi-box-seam me-1"></i> Usuario
                                        </span>
                                    </div>

                                    <div class="text-muted small">john.davis@university.edu</div>
                                    <div class="text-muted small">Miembro desde 1/14/2024</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-4 role-select">
                                <option selected>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge bg-success user-status-badge align-self-start px-2 py-1 rounded-3">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 2 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Sarah Chen"
                 data-email="sarah.chen@university.edu"
                 data-role="Usuario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Sarah Chen
                                        </a>

                                        <span class="badge rounded-pill user-role-badge bg-primary-subtle text-primary-emphasis px-2 py-1 small">
                                            <i class="bi bi-box-seam me-1"></i> Usuario
                                        </span>
                                    </div>

                                    <div class="text-muted small">sarah.chen@university.edu</div>
                                    <div class="text-muted small">Miembro desde 2/19/2024</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-4 role-select">
                                <option selected>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge bg-success user-status-badge align-self-start px-2 py-1 rounded-3">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 3 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Mike Johnson"
                 data-email="mike.johnson@university.edu"
                 data-role="Admin Inventario"
                 data-status="Activo">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Mike Johnson
                                        </a>

                                        <span class="badge rounded-pill user-role-badge bg-success-subtle text-success-emphasis px-2 py-1 small">
                                            <i class="bi bi-box-seam me-1"></i> Admin Inventario
                                        </span>
                                    </div>

                                    <div class="text-muted small">mike.johnson@university.edu</div>
                                    <div class="text-muted small">Miembro desde 3/08/2023</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-4 role-select">
                                <option>Usuario</option>
                                <option>Admin Super</option>
                                <option selected>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge bg-success user-status-badge align-self-start px-2 py-1 rounded-3">Activo</span>

                                <button type="button" class="btn btn-danger rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-ban me-1"></i>
                                    Banear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- User 4 --}}
            <div class="card border-0 shadow-sm rounded-4 user-card"
                 data-name="Laura Gómez"
                 data-email="laura.gomez@university.edu"
                 data-role="Admin Mercado"
                 data-status="Baneado">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-7">
                            <div class="d-flex align-items-start gap-2">
                                <div class="bg-light rounded-4 d-flex align-items-center justify-content-center flex-shrink-0"
                                     style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard"></i>
                                </div>

                                <div>
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <a href="{{ route('my_profile') }}"
                                           class="fw-semibold fs-5 text-decoration-none user-name-link">
                                            Laura Gómez
                                        </a>

                                        <span class="badge rounded-pill user-role-badge bg-warning-subtle text-warning-emphasis px-2 py-1 small">
                                            <i class="bi bi-box-seam me-1"></i> Admin Mercado
                                        </span>
                                    </div>

                                    <div class="text-muted small">laura.gomez@university.edu</div>
                                    <div class="text-muted small">Miembro desde 5/21/2023</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label fw-semibold small mb-1">Cambiar Rol</label>
                            <select class="form-select rounded-4 role-select">
                                <option>Usuario</option>
                                <option>Admin Super</option>
                                <option>Admin Inventario</option>
                                <option>Admin Facilidades</option>
                                <option selected>Admin Mercado</option>
                                <option>Admin Mercado</option>
                            </select>
                        </div>

                        <div class="col-lg-2">
                            <label class="form-label fw-semibold small d-block mb-1">Estado</label>
                            <div class="d-flex flex-column gap-2">
                                <span class="badge bg-danger user-status-badge align-self-start px-2 py-1 rounded-3">Baneado</span>

                                <button type="button" class="btn btn-outline-success rounded-3 ban-toggle-btn btn-sm">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                                    Desbanear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Change role modal --}}
    <div class="modal fade" id="confirmRoleModal" tabindex="-1" aria-labelledby="confirmRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="confirmRoleModalLabel">Confirmar cambio de rol</h4>
                        <p class="text-muted mb-0" id="confirmRoleText">
                            ¿Deseas cambiar el rol de este usuario?
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmRoleBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Ban / unban modal --}}
    <div class="modal fade" id="confirmBanModal" tabindex="-1" aria-labelledby="confirmBanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h4 class="modal-title fw-bold" id="confirmBanModalLabel">Confirmar acción</h4>
                        <p class="text-muted mb-0" id="confirmBanText">
                            ¿Estás seguro de realizar esta acción?
                        </p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmBanBtn">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Toasts --}}
    <div class="toast-container position-fixed bottom-0 start-0 p-3">
        <div id="roleToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    Rol actualizado correctamente.
                </div>
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="banToast"
             class="toast align-items-center shadow-sm border border-danger-subtle bg-danger-subtle text-danger-emphasis rounded-0 mb-2"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">

            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido baneada.
                </div>

                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>

        <div id="unbanToast"
             class="toast align-items-center shadow-sm border border-success-subtle bg-success-subtle text-success-emphasis rounded-0"
             role="alert"
             aria-live="assertive"
             aria-atomic="true"
             style="width: auto; max-width: fit-content;">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-semibold rounded-0 pe-1" style="padding-right: 0;">
                    La cuenta ha sido desbaneada.
                </div>
                <button type="button"
                        class="btn-close p-0 ms-1 me-2"
                        data-bs-dismiss="toast"
                        aria-label="Cerrar"
                        style="background-color: transparent; border: none; transform: scale(0.8);">
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userSearchInput = document.getElementById('userSearchInput');
            const roleFilterSelect = document.getElementById('roleFilterSelect');
            const usersList = document.getElementById('usersList');
            const userCards = document.querySelectorAll('.user-card');
            const usersEmptyState = document.getElementById('usersEmptyState');

            const confirmRoleModal = document.getElementById('confirmRoleModal');
            const confirmRoleText = document.getElementById('confirmRoleText');
            const confirmRoleBtn = document.getElementById('confirmRoleBtn');

            const confirmBanModal = document.getElementById('confirmBanModal');
            const confirmBanText = document.getElementById('confirmBanText');
            const confirmBanBtn = document.getElementById('confirmBanBtn');

            const roleToastEl = document.getElementById('roleToast');
            const banToastEl = document.getElementById('banToast');
            const unbanToastEl = document.getElementById('unbanToast');

            let pendingRoleChange = null;
            let pendingBanAction = null;

            function getRoleBadgeClass(role) {
                if (role === 'Usuario') return 'bg-primary-subtle text-primary-emphasis';
                if (role === 'Admin Inventario') return 'bg-success-subtle text-success-emphasis';
                if (role === 'Admin Mercado') return 'bg-warning-subtle text-warning-emphasis';
                if (role === 'Admin Facilidades') return 'bg-info-subtle text-info-emphasis';
                if (role === 'Admin Super') return 'bg-danger-subtle text-danger-emphasis';
                return 'bg-secondary-subtle text-secondary-emphasis';
            }

            function filterUsers() {
                const searchValue = userSearchInput.value.trim().toLowerCase();
                const roleValue = roleFilterSelect.value;

                let visibleCount = 0;

                userCards.forEach(card => {
                    const name = card.dataset.name.toLowerCase();
                    const email = card.dataset.email.toLowerCase();
                    const role = card.dataset.role;

                    const matchesSearch =
                        !searchValue ||
                        name.includes(searchValue) ||
                        email.includes(searchValue);

                    const matchesRole =
                        roleValue === 'all' ||
                        role === roleValue;

                    const shouldShow = matchesSearch && matchesRole;
                    card.classList.toggle('d-none', !shouldShow);

                    if (shouldShow) visibleCount++;
                });

                usersEmptyState.classList.toggle('d-none', visibleCount !== 0);
                usersList.classList.toggle('d-none', visibleCount === 0);
            }

            userSearchInput.addEventListener('input', filterUsers);
            roleFilterSelect.addEventListener('change', filterUsers);

            document.querySelectorAll('.role-select').forEach(select => {
                select.dataset.previousValue = select.value;

                select.addEventListener('change', function () {
                    const card = select.closest('.user-card');
                    const userName = card.dataset.name;
                    const newRole = select.value;
                    const previousRole = select.dataset.previousValue;

                    pendingRoleChange = {
                        card,
                        select,
                        userName,
                        newRole,
                        previousRole
                    };

                    confirmRoleText.textContent = `¿Deseas cambiar el rol de ${userName} a "${newRole}"?`;

                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmRoleModal);
                    modalInstance.show();
                });
            });

            confirmRoleBtn.addEventListener('click', function () {
                if (!pendingRoleChange) return;

                const { card, select, newRole } = pendingRoleChange;
                const badge = card.querySelector('.user-role-badge');

                card.dataset.role = newRole;
                select.dataset.previousValue = newRole;

                badge.className = `badge rounded-pill user-role-badge px-2 py-1 small ${getRoleBadgeClass(newRole)}`;
                badge.innerHTML = `<i class="bi bi-box-seam me-1"></i> ${newRole}`;

                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmRoleModal);
                modalInstance.hide();

                const toast = window.bootstrap.Toast.getOrCreateInstance(roleToastEl);
                toast.show();

                pendingRoleChange = null;
                filterUsers();
            });

            confirmRoleModal.addEventListener('hidden.bs.modal', function () {
                if (pendingRoleChange) {
                    pendingRoleChange.select.value = pendingRoleChange.previousRole;
                    pendingRoleChange = null;
                }
            });

            document.querySelectorAll('.ban-toggle-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const card = button.closest('.user-card');
                    const userName = card.dataset.name;
                    const currentStatus = card.dataset.status;

                    pendingBanAction = { card, button, currentStatus };

                    if (currentStatus === 'Activo') {
                        confirmBanText.textContent = `¿Estás seguro de banear a ${userName}?`;
                        confirmBanBtn.className = 'btn btn-danger';
                        confirmBanBtn.textContent = 'Banear';
                    } else {
                        confirmBanText.textContent = `¿Estás seguro de desbanear a ${userName}?`;
                        confirmBanBtn.className = 'btn btn-success';
                        confirmBanBtn.textContent = 'Desbanear';
                    }

                    const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmBanModal);
                    modalInstance.show();
                });
            });

            confirmBanBtn.addEventListener('click', function () {
                if (!pendingBanAction) return;

                const { card, button, currentStatus } = pendingBanAction;
                const statusBadge = card.querySelector('.user-status-badge');

                if (currentStatus === 'Activo') {
                    card.dataset.status = 'Baneado';
                    statusBadge.textContent = 'Baneado';
                    statusBadge.className = 'badge bg-danger user-status-badge align-self-start px-2 py-1 rounded-3';

                    button.className = 'btn btn-outline-success rounded-3 ban-toggle-btn btn-sm';
                    button.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Desbanear';

                    const toast = window.bootstrap.Toast.getOrCreateInstance(banToastEl);
                    toast.show();
                } else {
                    card.dataset.status = 'Activo';
                    statusBadge.textContent = 'Activo';
                    statusBadge.className = 'badge bg-success user-status-badge align-self-start px-2 py-1 rounded-3';

                    button.className = 'btn btn-danger rounded-3 ban-toggle-btn btn-sm';
                    button.innerHTML = '<i class="bi bi-ban me-1"></i> Banear';

                    const toast = window.bootstrap.Toast.getOrCreateInstance(unbanToastEl);
                    toast.show();
                }

                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmBanModal);
                modalInstance.hide();

                pendingBanAction = null;
            });

            confirmBanModal.addEventListener('hidden.bs.modal', function () {
                pendingBanAction = null;
            });

            filterUsers();
        });
    </script>
</x-layout>
