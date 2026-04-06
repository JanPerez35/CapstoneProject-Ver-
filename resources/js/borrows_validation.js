document.addEventListener('DOMContentLoaded', function () {
    const borrowDateFilter = document.getElementById('borrowDateFilter');
    const borrowSearch = document.getElementById('borrowSearch');

    const pendingEmptyState = document.getElementById('pendingEmptyState');
    const activeEmptyState = document.getElementById('activeEmptyState');

    const pendingRequestsList = document.getElementById('pendingRequestsList');
    const activeRequestsList = document.getElementById('activeRequestsList');

    const approveToastEl = document.getElementById('approveToast');
    const denyToastEl = document.getElementById('denyToast');
    const returnedToastEl = document.getElementById('returnedToast');

    const approveConfirmModalEl = document.getElementById('approveConfirmModal');
    const approveConfirmText = document.getElementById('approveConfirmText');
    const confirmApproveBtn = document.getElementById('confirmApproveBtn');

    const denyConfirmModalEl = document.getElementById('denyConfirmModal');
    const denyConfirmText = document.getElementById('denyConfirmText');
    const confirmDenyBtn = document.getElementById('confirmDenyBtn');

    const returnConfirmModalEl = document.getElementById('returnConfirmModal');
    const returnConfirmText = document.getElementById('returnConfirmText');
    const confirmReturnBtn = document.getElementById('confirmReturnBtn');

    let approveFormToSubmit = null;
    let denyFormToSubmit = null;
    let returnFormToSubmit = null;

    function getAllRequests() {
        return document.querySelectorAll('.borrow-request');
    }

    function updateEmptyStates() {
        const pendingVisible = [...document.querySelectorAll('.pending-request')]
            .filter(card => !card.classList.contains('d-none'));

        const activeVisible = [...document.querySelectorAll('.active-request')]
            .filter(card => !card.classList.contains('d-none'));

        if (pendingEmptyState) {
            pendingEmptyState.classList.toggle('d-none', pendingVisible.length !== 0);
        }

        if (activeEmptyState) {
            activeEmptyState.classList.toggle('d-none', activeVisible.length !== 0);
        }
    }

    function filterRequests() {
        const selectedDate = borrowDateFilter ? borrowDateFilter.value : '';
        const searchValue = borrowSearch ? borrowSearch.value.trim().toLowerCase() : '';

        getAllRequests().forEach(card => {
            const cardDate = card.dataset.date || '';
            const cardSearch = (card.dataset.search || '').toLowerCase();

            const matchesDate = !selectedDate || cardDate === selectedDate;
            const matchesSearch = !searchValue || cardSearch.includes(searchValue);

            card.classList.toggle('d-none', !(matchesDate && matchesSearch));
        });

        updateEmptyStates();
    }

    function showToast(toastElement) {
        if (!toastElement || !window.bootstrap) return;
        const toast = window.bootstrap.Toast.getOrCreateInstance(toastElement);
        toast.show();
    }

    function attachApproveEvents() {
        document.querySelectorAll('.approve-special-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');
                const card = button.closest('.borrow-request');
                const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                approveFormToSubmit = form;
                approveConfirmText.textContent = `¿Seguro que quieres aprobar "${itemName}"?`;

                const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
                modal.show();
            });
        });
    }

    function attachDenyEvents() {
        document.querySelectorAll('.deny-special-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');
                const card = button.closest('.borrow-request');
                const itemName = card?.querySelector('h5')?.textContent?.trim() || 'este caso especial';

                denyFormToSubmit = form;
                denyConfirmText.textContent = `¿Seguro que quieres denegar "${itemName}"?`;

                const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
                modal.show();
            });
        });
    }

    function attachReturnEvents() {
        document.querySelectorAll('.mark-returned-btn').forEach(button => {
            if (button.dataset.bound === 'true') return;
            button.dataset.bound = 'true';

            button.addEventListener('click', function () {
                const form = button.closest('form');
                const card = button.closest('.borrow-request');
                const itemName = card?.querySelector('h5')?.textContent?.trim() || 'el equipo';

                returnFormToSubmit = form;
                returnConfirmText.textContent = `¿Estás seguro de que "${itemName}" fue devuelto?`;

                const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
                modal.show();
            });
        });
    }

    if (confirmApproveBtn) {
        confirmApproveBtn.addEventListener('click', function () {
            if (!approveFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(approveConfirmModalEl);
            modal.hide();

            showToast(approveToastEl);

            setTimeout(() => {
                approveFormToSubmit.submit();
            }, 500);
        });
    }

    if (confirmDenyBtn) {
        confirmDenyBtn.addEventListener('click', function () {
            if (!denyFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(denyConfirmModalEl);
            modal.hide();

            showToast(denyToastEl);

            setTimeout(() => {
                denyFormToSubmit.submit();
            }, 500);
        });
    }

    if (confirmReturnBtn) {
        confirmReturnBtn.addEventListener('click', function () {
            if (!returnFormToSubmit) return;

            const modal = window.bootstrap.Modal.getOrCreateInstance(returnConfirmModalEl);
            modal.hide();

            showToast(returnedToastEl);

            setTimeout(() => {
                returnFormToSubmit.submit();
            }, 500);
        });
    }

    if (borrowSearch) {
        borrowSearch.addEventListener('input', filterRequests);
    }

    if (borrowDateFilter) {
        borrowDateFilter.addEventListener('change', filterRequests);
    }

    attachApproveEvents();
    attachDenyEvents();
    attachReturnEvents();
    updateEmptyStates();
});
