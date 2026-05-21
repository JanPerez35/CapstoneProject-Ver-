/**
 * Initializes the Search Users page once the DOM is fully loaded.
 *
 * Responsibilities:
 * - filters users by name/email and role
 * - paginates the visible user cards
 * - handles role change confirmation flow
 * - handles ban/unban confirmation flow
 * - updates UI badges and buttons after successful changes
 * - shows feedback toasts for completed actions
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Maximum number of user cards shown per page.
     */
    const USERS_PER_PAGE = 18;

    /**
    * Filter controls
    *
    * Elements used for searching and filtering the user list.
    */
    const userSearchInput = document.getElementById('userSearchInput');
    const roleFilterSelect = document.getElementById('roleFilterSelect');
    const searchUsersBtn = document.getElementById('searchUsersBtn');
    const clearUserFiltersBtn = document.getElementById('clearUserFilters');

    /**
    * Users list and pagination references
    *
    * Elements used to render, hide, and paginate user cards.
    */
    const usersList = document.getElementById('usersList');
    const userCards = Array.from(document.querySelectorAll('.user-card'));
    const usersEmptyState = document.getElementById('usersEmptyState');
    const usersPagination = document.getElementById('usersPagination');
    const usersPaginationSummary = document.getElementById('usersPaginationSummary');

    /**
     * Role change confirmation modal references
     *
     * Used to confirm before changing a user's role.
     */
    const confirmRoleModal = document.getElementById('confirmRoleModal');
    const confirmRoleText = document.getElementById('confirmRoleText');
    const confirmRoleBtn = document.getElementById('confirmRoleBtn');

    /**
     * Ban/unban confirmation modal references
     *
     * Used to confirm before banning or unbanning a user
     * */
    const confirmBanModal = document.getElementById('confirmBanModal');
    const confirmBanText = document.getElementById('confirmBanText');
    const confirmBanBtn = document.getElementById('confirmBanBtn');

    /**
     * References for the toast
     *
     * Feedback messages shown after successful role/status updates.
     * */
    const roleToastEl = document.getElementById('roleToast');
    const banToastEl = document.getElementById('banToast');
    const unbanToastEl = document.getElementById('unbanToast');

    /**
     * Stores the pending role change selected by the user
     * until they confirm it in the modal.
     */
    let pendingRoleChange = null;

    /**
     * Stores the pending ban/unban action selected by the user
     * until they confirm it in the modal.
     */
    let pendingBanAction = null;

    /**
     * Tracks the current page of the paginated user list.
     */
    let currentPage = 1;

    /**
     * Returns from app.css the CSS class string for a role badge based on role name.
     *
     * @param {string} role - User role label.
     * @returns {string} CSS classes used to style the role badge.
     */
    function getRoleBadgeClass(role) {
        if (role === 'Usuario') return 'label-badge badge-user';
        if (role === 'Super Administrador') return 'label-badge badge-super-admin';
        if (role === 'Administrador de Inventario') return 'label-badge badge-inventory-admin';
        if (role === 'Administrador de Instalaciones') return 'label-badge badge-facility-admin';
        if (role === 'Administrador de Mercado') return 'label-badge badge-market-admin';
        return 'label-badge badge-user';
    }

    /**
     * Finds the role badge element inside a user card.
     *
     * @param {HTMLElement} card - User card element.
     * @returns {HTMLElement|null} Role badge element if found.
     */
    function getRoleBadgeElement(card) {
        const nameLink = card.querySelector('.user-name-link');
        if (!nameLink) return null;

        const headerRow = nameLink.closest('.d-flex');
        if (!headerRow) return null;

        return headerRow.querySelector('.label-badge');
    }


    /**
     * Finds the status badge element inside a user card.
     * Status badges are identified by their text content.
     * With Activo meaning the user is free to browse and Bloqueado
     * meaning the user should not be allowed in the site.
     *
     * @param {HTMLElement} card - User card element.
     * @returns {HTMLElement|null} Status badge element if found.
     */
    function getStatusBadgeElement(card) {
        return Array.from(card.querySelectorAll('.label-badge')).find(badge => {
            const text = badge.textContent.trim();
            return text === 'Activo' || text === 'Bloqueado';
        }) || null;
    }

    /**
     * Returns all user cards that match the current search text and role filter.
     *
     * Search matches:
     * - user name
     * - user email
     *
     * Role filter matches:
     * - selected role
     * - or all roles when "all" is selected
     *
     * @returns {HTMLElement[]} Filtered user cards.
     */
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

    /**
     * Renders pagination buttons based on the number of filtered users.
     *
     * Includes:
     * - previous section button
     * - numbered page buttons
     * - next section button
     *
     * @param {number} totalItems - Total number of filtered users.
     */
    function renderPagination(totalItems) {
        if (!usersPagination) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / USERS_PER_PAGE));

        if (totalItems === 0) {
            usersPagination.innerHTML = '';
            if (usersPaginationSummary) usersPaginationSummary.textContent = '';
            return;
        }

        if (usersPaginationSummary) {
            const firstItem = ((currentPage - 1) * USERS_PER_PAGE) + 1;
            const lastItem = Math.min(currentPage * USERS_PER_PAGE, totalItems);
            usersPaginationSummary.textContent = `Mostrando ${firstItem} a ${lastItem} de ${totalItems} resultados`;
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

        /**
         * Attach click handlers to every pagination button after rendering.
         * Updates current page and refreshes the filtered list.
         */
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

    /**
     * Enables the search button only when the search input contains some text.
     */
    function updateSearchUsersButtonState() {
        if (!userSearchInput || !searchUsersBtn) return;

        const hasText = userSearchInput.value.trim().length > 0;
        searchUsersBtn.disabled = !hasText;
    }

    /**
     * Shows a toast after the page reloads.
     * The previous action stores the toast type in sessionStorage before refreshing.
     */
    function showPendingToastAfterReload() {
        const pendingToast = sessionStorage.getItem('searchUsersPendingToast');

        if (!pendingToast) return;

        sessionStorage.removeItem('searchUsersPendingToast');

        let toastElement = null;

        if (pendingToast === 'role') {
            toastElement = roleToastEl;
        }

        if (pendingToast === 'ban') {
            toastElement = banToastEl;
        }

        if (pendingToast === 'unban') {
            toastElement = unbanToastEl;
        }

        if (toastElement) {
            const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
            toast.show();
        }
    }

    /**
     * Applies current filters and current pagination state to the user list.
     *
     * Handles:
     * - showing/hiding user cards
     * - showing empty state when no users match
     * - hiding the list when no results exist
     * - rendering pagination
     */
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

        if (usersEmptyState) {
            usersEmptyState.classList.toggle('d-none', filteredUsers.length !== 0);
        }

        if (usersList) {
            usersList.classList.toggle('d-none', filteredUsers.length === 0);
        }

        renderPagination(filteredUsers.length);
    }

    /**
     * Resets the list to page 1 before applying filters again.
     * Used when search/filter criteria change.
     */
    function resetToFirstPageAndFilter() {
        currentPage = 1;
        filterUsers();
    }

    /**
     * Search button manually applies filters starting from page 1.
     */
    if (searchUsersBtn) {
        searchUsersBtn.addEventListener('click', function () {
            sessionStorage.setItem('selectedUserSearch', userSearchInput.value.trim());
            sessionStorage.setItem('selectedRoleFilter', roleFilterSelect.value);
            window.location.reload();
        });
    }

    /**
     * Search input behavior:
     * - pressing Enter triggers filtering
     * - typing updates whether the search button should stay enabled
     */
    if (userSearchInput) {
        userSearchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sessionStorage.setItem('selectedUserSearch', userSearchInput.value.trim());
                sessionStorage.setItem('selectedRoleFilter', roleFilterSelect.value);
                window.location.reload();
            }
        });
        userSearchInput.addEventListener('input', updateSearchUsersButtonState);

    }

    /**
     * Role filter dropdown reapplies filters whenever the selected role changes.
     */
    if (roleFilterSelect) {
        roleFilterSelect.addEventListener('change', function () {
            sessionStorage.setItem('selectedUserSearch', userSearchInput.value.trim());
            sessionStorage.setItem('selectedRoleFilter', roleFilterSelect.value);
            window.location.reload();
        });
    }

    /**
     * Clear filters button resets:
     * - search text
     * - role filter
     * - pagination back to page 1
     */
    if (clearUserFiltersBtn) {
        clearUserFiltersBtn.addEventListener('click', function () {
            sessionStorage.removeItem('selectedUserSearch');
            sessionStorage.removeItem('selectedRoleFilter');

            if (userSearchInput) userSearchInput.value = '';
            if (roleFilterSelect) roleFilterSelect.value = 'all';
            window.location.reload();
        });
    }

    /**
     * Attaches change handlers to all role selectors.
     * Instead of updating immediately, each change is stored
     * and confirmed through the role confirmation modal.
     */
    document.querySelectorAll('.role-select').forEach(select => {
        select.dataset.previousValue = select.value;

        select.addEventListener('change', async function () {
            const card = select.closest('.user-card');
            if (!card) return;

            const userId = card.dataset.userId;
            const userName = card.dataset.name;
            const newRole = select.value;
            const previousRole = select.dataset.previousValue;

            pendingRoleChange = {
                card,
                select,
                userId,
                userName,
                newRole,
                previousRole
            };

            if (confirmRoleText) {
                confirmRoleText.textContent = `¿Deseas cambiar el rol de ${userName} a "${newRole}"?`;
            }

            const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmRoleModal);
            modalInstance.show();
        });
    });

    /**
     * Confirms the pending role change.
     *
     * Behavior:
     * - updates the UI immediately
     * - sends the new role to the backend using fetch
     * - shows success toast if completed
     * - refreshes filtering in case the new role affects current results
     */
    if (confirmRoleBtn) {
        confirmRoleBtn.addEventListener('click', async function () {
            if (!pendingRoleChange) return;

            const { card, select, newRole, userId } = pendingRoleChange;
            const badge = getRoleBadgeElement(card);

            card.dataset.role = newRole;
            select.dataset.previousValue = newRole;

            if (badge) {
                badge.className = getRoleBadgeClass(newRole);
                badge.textContent = newRole;
            }

            const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmRoleModal);
            modalInstance.hide();

            try {
            const response = await fetch(`/users/${userId}/role`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    role: newRole
                })
            });

            const data = await response.json();

            if (!response.ok) {
                console.error('Error:', data);
                return;
            }

                console.log('Rol actualizado correctamente');

                sessionStorage.setItem('searchUsersPendingToast', 'role');
                window.location.reload();
                return;

            }
            catch (error) {
                console.error('Error:', error);
            }

            if (roleToastEl) {
                const toast = window.bootstrap.Toast.getOrCreateInstance(roleToastEl);
                toast.show();
            }

            pendingRoleChange = null;
            filterUsers();
        });
    }

    /**
     * If the role confirmation modal closes without confirmation,
     * restore the previous role value in the select element.
     */
    if (confirmRoleModal) {
        confirmRoleModal.addEventListener('hidden.bs.modal', function () {
            if (pendingRoleChange) {
                pendingRoleChange.select.value = pendingRoleChange.previousRole;
                pendingRoleChange = null;
            }
        });
    }
    /**
     * Attaches click handlers to all ban/unban buttons.
     * Stores the intended action and opens a confirmation modal.
     */
    document.querySelectorAll('.ban-toggle-btn').forEach(button => {
        button.addEventListener('click', function () {
            const card = button.closest('.user-card');
            if (!card) return;

            const userName = card.dataset.name;
            const currentStatus = card.dataset.status;

            pendingBanAction = { card, button, currentStatus };

            if (currentStatus === 'Activo') {
                if (confirmBanText) {
                    confirmBanText.textContent = `¿Estás seguro de bloquear a ${userName}?`;
                }
                confirmBanBtn.className = 'btn btn-danger';
                confirmBanBtn.textContent = 'Bloquear';
            } else {
                if (confirmBanText) {
                    confirmBanText.textContent = `¿Estás seguro de desbloquear a ${userName}?`;
                }
                confirmBanBtn.className = 'btn btn-success';
                confirmBanBtn.textContent = 'Desbloquear';
            }

            const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmBanModal);
            modalInstance.show();
        });
    });

    /**
     * Confirms the pending ban/unban action.
     *
     * Behavior:
     * - sends status update to backend
     * - updates the status badge and action button
     * - shows the corresponding success toast
     * - closes modal and refreshes filtered results
     */
    if (confirmBanBtn) {
        confirmBanBtn.addEventListener('click', async function () {
            if (!pendingBanAction) return;

            const { card, button, currentStatus } = pendingBanAction;
            const statusBadge = getStatusBadgeElement(card);
            const userId = card.dataset.userId;
            const newStatus = currentStatus === 'Activo' ? 'Bloqueado' : 'Activo';

            try {
                const response = await fetch(`/users/${userId}/status`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    console.error('Error:', data);
                    return;
                }
                sessionStorage.setItem(
                    'searchUsersPendingToast',
                    newStatus === 'Bloqueado' ? 'ban' : 'unban'
                );

                window.location.reload();
                return;

                card.dataset.status = newStatus;

                if (newStatus === 'Bloqueado') {
                    if (statusBadge) {
                        statusBadge.textContent = 'Bloqueado';
                        statusBadge.className = 'label-badge badge-blocked align-self-start';
                    }

                    button.className = 'btn btn-outline-success rounded-3 ban-toggle-btn btn-sm';
                    button.innerHTML = '<i class="bi bi-arrow-counterclockwise me-1"></i> Desbloquear';

                    if (banToastEl) {
                        const toast = window.bootstrap.Toast.getOrCreateInstance(banToastEl);
                        toast.show();
                    }
                } else {
                    if (statusBadge) {
                        statusBadge.textContent = 'Activo';
                        statusBadge.className = 'label-badge badge-active align-self-start';
                    }

                    button.className = 'btn btn-danger rounded-3 ban-toggle-btn btn-sm';
                    button.innerHTML = '<i class="bi bi-ban me-1"></i> Bloquear';

                    if (unbanToastEl) {
                        const toast = window.bootstrap.Toast.getOrCreateInstance(unbanToastEl);
                        toast.show();
                    }
                }

                const modalInstance = window.bootstrap.Modal.getOrCreateInstance(confirmBanModal);
                modalInstance.hide();

                pendingBanAction = null;
                filterUsers();
            } catch (error) {
                console.error('Error:', error);
            }
        });
    }

    /**
     * Clears any pending ban/unban action when the confirmation modal closes.
     */
    if (confirmBanModal) {
        confirmBanModal.addEventListener('hidden.bs.modal', function () {
            pendingBanAction = null;
        });
    }

    /**
     *
     * Initial page setup
     *
     *  Applies initial filters and syncs the search button state.
     * */

    const savedUserSearch = sessionStorage.getItem('selectedUserSearch');
    const savedRoleFilter = sessionStorage.getItem('selectedRoleFilter');

    if (savedUserSearch && userSearchInput) {
        userSearchInput.value = savedUserSearch;
    }

    if (savedRoleFilter && roleFilterSelect) {
        roleFilterSelect.value = savedRoleFilter;
    }

    filterUsers();
    updateSearchUsersButtonState();
    showPendingToastAfterReload();
});
