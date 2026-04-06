document.addEventListener('DOMContentLoaded', function () {
    const USERS_PER_PAGE = 18;

    const userSearchInput = document.getElementById('userSearchInput');
    const roleFilterSelect = document.getElementById('roleFilterSelect');
    const searchUsersBtn = document.getElementById('searchUsersBtn');
    const clearUserFiltersBtn = document.getElementById('clearUserFilters');

    const usersList = document.getElementById('usersList');
    const userCards = Array.from(document.querySelectorAll('.user-card'));
    const usersEmptyState = document.getElementById('usersEmptyState');
    const usersPagination = document.getElementById('usersPagination');

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
    let currentPage = 1;

    function getRoleBadgeClass(role) {
        if (role === 'Usuario') return 'bg-primary-subtle text-primary-emphasis';
        if (role === 'Admin Inventario') return 'bg-success-subtle text-success-emphasis';
        if (role === 'Admin Mercado') return 'bg-warning-subtle text-warning-emphasis';
        if (role === 'Admin Facilidades') return 'bg-info-subtle text-info-emphasis';
        if (role === 'Admin Super') return 'bg-danger-subtle text-danger-emphasis';
        return 'bg-secondary-subtle text-secondary-emphasis';
    }

    function getFilteredUsers() {
        const searchValue = userSearchInput.value.trim().toLowerCase();
        const roleValue = roleFilterSelect.value;

        return userCards.filter(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const email = (card.dataset.email || '').toLowerCase();
            const role = card.dataset.role || '';

            const matchesSearch =
                !searchValue ||
                name.includes(searchValue) ||
                email.includes(searchValue);

            const matchesRole =
                roleValue === 'all' ||
                role === roleValue;

            return matchesSearch && matchesRole;
        });
    }

    function renderPagination(totalItems) {
        if (!usersPagination) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / USERS_PER_PAGE));

        if (totalItems === 0) {
            usersPagination.innerHTML = '';
            return;
        }

        let paginationHTML = `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <button type="button" class="page-link" data-page="prev">&laquo;</button>
            </li>
        `;

        for (let page = 1; page <= totalPages; page++) {
            paginationHTML += `
                <li class="page-item ${page === currentPage ? 'active' : ''}">
                    <button type="button" class="page-link" data-page="${page}">${page}</button>
                </li>
            `;
        }

        paginationHTML += `
            <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                <button type="button" class="page-link" data-page="next">&raquo;</button>
            </li>
        `;

        usersPagination.innerHTML = paginationHTML;

        usersPagination.querySelectorAll('.page-link').forEach(button => {
            button.addEventListener('click', function () {
                const action = this.dataset.page;
                const totalPagesLocal = Math.max(1, Math.ceil(totalItems / USERS_PER_PAGE));
                let newPage = currentPage;

                if (action === 'prev' && currentPage > 1) {
                    newPage = currentPage - 1;
                } else if (action === 'next' && currentPage < totalPagesLocal) {
                    newPage = currentPage + 1;
                } else if (!isNaN(action)) {
                    newPage = Number(action);
                }

                if (newPage !== currentPage) {
                    currentPage = newPage;
                    filterUsers();
                }
            });
        });
    }

    function filterUsers() {
        const filteredUsers = getFilteredUsers();
        const totalPages = Math.max(1, Math.ceil(filteredUsers.length / USERS_PER_PAGE));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const start = (currentPage - 1) * USERS_PER_PAGE;
        const end = start + USERS_PER_PAGE;
        const paginatedUsers = filteredUsers.slice(start, end);

        userCards.forEach(card => {
            card.classList.add('d-none');
        });

        paginatedUsers.forEach(card => {
            card.classList.remove('d-none');
        });

        usersEmptyState.classList.toggle('d-none', filteredUsers.length !== 0);
        usersList.classList.toggle('d-none', filteredUsers.length === 0);

        renderPagination(filteredUsers.length);
    }

    function resetToFirstPageAndFilter() {
        currentPage = 1;
        filterUsers();
    }

    if (searchUsersBtn) {
        searchUsersBtn.addEventListener('click', resetToFirstPageAndFilter);
    }

    if (userSearchInput) {
        userSearchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                resetToFirstPageAndFilter();
            }
        });
    }

    if (roleFilterSelect) {
        roleFilterSelect.addEventListener('change', resetToFirstPageAndFilter);
    }

    if (clearUserFiltersBtn) {
        clearUserFiltersBtn.addEventListener('click', function () {
            userSearchInput.value = '';
            roleFilterSelect.value = 'all';
            resetToFirstPageAndFilter();
        });
    }

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

        badge.className = `badge user-role-badge px-2 py-1 small ${getRoleBadgeClass(newRole)}`;
        badge.textContent = newRole;

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
            statusBadge.className = 'badge bg-danger user-status-badge align-self-start px-2 py-1 rounded-0';

            button.className = 'btn btn-outline-success rounded-3 ban-toggle-btn btn-sm';
            button.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Desbanear';

            const toast = window.bootstrap.Toast.getOrCreateInstance(banToastEl);
            toast.show();
        } else {
            card.dataset.status = 'Activo';
            statusBadge.textContent = 'Activo';
            statusBadge.className = 'badge user-status-badge status-active-badge align-self-start px-2 py-1 rounded-0';

            button.className = 'btn btn-danger rounded-3 ban-toggle-btn btn-sm';
            button.innerHTML = '<i class="bi bi-ban me-1"></i> Banear';

            const toast = window.bootstrap.Toast.getOrCreateInstance(unbanToastEl);
            toast.show();
        }

        const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmBanModal);
        modalInstance.hide();

        pendingBanAction = null;
        filterUsers();
    });

    confirmBanModal.addEventListener('hidden.bs.modal', function () {
        pendingBanAction = null;
    });

    filterUsers();
});
