document.addEventListener('DOMContentLoaded', () => {
    const SCROLL_KEY = 'kinventoryScrollY';

    const borrowButtons = document.querySelectorAll('.open-borrow-modal');
    const borrowModal = document.getElementById('borrowModal');
    const borrowEquipmentId = document.getElementById('borrowEquipmentId');
    const borrowModalText = document.getElementById('borrowModalText');
    const borrowModalImage = document.getElementById('borrowModalImage');
    const borrowModalStock = document.getElementById('borrowModalStock');
    const borrowQuantity = document.getElementById('borrowQuantity');
    const confirmAddToCart = document.getElementById('confirmAddToCart');
    const borrowQuantityError = document.getElementById('borrowQuantityError');
    const borrowForm = document.getElementById('borrowForm');

    function saveScrollPosition() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
    }

    function clearScrollPosition() {
        sessionStorage.removeItem(SCROLL_KEY);
    }

    function isPaginationLink(link) {
        return !!link.closest('.pagination');
    }

    function restoreScrollPosition() {
        const savedScrollY = sessionStorage.getItem(SCROLL_KEY);
        if (savedScrollY === null) return;

        const targetY = parseInt(savedScrollY, 10);
        if (Number.isNaN(targetY)) {
            clearScrollPosition();
            return;
        }

        let attempts = 0;
        const maxAttempts = 20;

        const tryRestore = () => {
            window.scrollTo(0, targetY);
            attempts++;

            if (attempts < maxAttempts && Math.abs(window.scrollY - targetY) > 5) {
                setTimeout(tryRestore, 100);
            } else {
                clearScrollPosition();
            }
        };

        setTimeout(tryRestore, 100);
    }

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            saveScrollPosition();
        });
    });

    document.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            if (isPaginationLink(link)) {
                clearScrollPosition();
                return;
            }

            saveScrollPosition();
        });
    });

    const categorySelect = document.querySelector('select[name="category"]');
    if (categorySelect) {
        categorySelect.addEventListener('change', () => {
            saveScrollPosition();
        });
    }

    window.addEventListener('load', restoreScrollPosition);

    const hasBorrowModal =
        borrowModal &&
        borrowEquipmentId &&
        borrowModalText &&
        borrowModalImage &&
        borrowModalStock &&
        borrowQuantity &&
        confirmAddToCart &&
        borrowQuantityError &&
        borrowForm;

    if (!hasBorrowModal) return;

    let currentItem = {
        id: null,
        name: '',
        stock: 0,
        image: '',
        location: ''
    };

    function setBorrowQuantityError(message) {
        borrowQuantity.classList.add('is-invalid');
        borrowQuantityError.textContent = message;
        borrowQuantityError.style.display = 'block';
    }

    function clearBorrowQuantityError() {
        borrowQuantity.classList.remove('is-invalid');
        borrowQuantityError.textContent = '';
        borrowQuantityError.style.display = 'none';
    }

    function normalizeQuantityValue() {
        const value = parseInt(borrowQuantity.value, 10);

        if (Number.isNaN(value)) return null;
        return value;
    }

    function validateBorrowQuantity(showError = true) {
        const value = normalizeQuantityValue();

        if (borrowQuantity.value === '') {
            if (showError) {
                setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            }
            return false;
        }

        if (value === null || value < 1) {
            if (showError) {
                setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            }
            return false;
        }

        if (value > currentItem.stock) {
            if (showError) {
                setBorrowQuantityError(`No puedes añadir más de ${currentItem.stock} unidades disponibles.`);
            }
            return false;
        }

        if (showError) clearBorrowQuantityError();
        return true;
    }

    borrowButtons.forEach((button) => {
        button.addEventListener('click', () => {
            currentItem.id = button.dataset.itemId || '';
            currentItem.name = button.dataset.itemName || '';
            currentItem.stock = parseInt(button.dataset.itemStock || '0', 10);
            currentItem.image = button.dataset.itemImage || '';
            currentItem.location = button.dataset.itemLocation || '';

            borrowEquipmentId.value = currentItem.id;
            borrowModalText.textContent = `Selecciona la cantidad que deseas de "${currentItem.name}"`;
            borrowModalImage.src = currentItem.image;
            borrowModalImage.alt = currentItem.name || 'Equipo';
            borrowModalStock.textContent = String(currentItem.stock);

            borrowQuantity.value = '1';
            borrowQuantity.min = '1';
            borrowQuantity.max = String(currentItem.stock);
            clearBorrowQuantityError();

            setTimeout(() => {
                borrowQuantity.focus();
                borrowQuantity.select();
            }, 150);
        });
    });

    borrowQuantity.addEventListener('input', () => {
        clearBorrowQuantityError();

        if (borrowQuantity.value === '') {
            return;
        }

        let value = normalizeQuantityValue();

        if (value === null) {
            setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            return;
        }

        if (value < 1) {
            borrowQuantity.value = '1';
            setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            return;
        }

        if (value > currentItem.stock) {
            borrowQuantity.value = String(currentItem.stock);
            setBorrowQuantityError(`No puedes añadir más de ${currentItem.stock} unidades disponibles.`);
            return;
        }

        clearBorrowQuantityError();
    });

    borrowQuantity.addEventListener('blur', () => {
        if (borrowQuantity.value === '') {
            borrowQuantity.value = '1';
        }

        validateBorrowQuantity(true);
    });

    borrowQuantity.addEventListener('paste', (event) => {
        const pastedText = (event.clipboardData || window.clipboardData).getData('text').trim();
        const pastedValue = parseInt(pastedText, 10);

        if (Number.isNaN(pastedValue)) {
            event.preventDefault();
            setBorrowQuantityError('Debes ingresar un número válido.');
            return;
        }

        if (pastedValue < 1) {
            event.preventDefault();
            borrowQuantity.value = '1';
            setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            return;
        }

        if (pastedValue > currentItem.stock) {
            event.preventDefault();
            borrowQuantity.value = String(currentItem.stock);
            setBorrowQuantityError(`No puedes añadir más de ${currentItem.stock} unidades disponibles.`);
        }
    });

    confirmAddToCart.addEventListener('click', () => {
        if (!validateBorrowQuantity(true)) {
            return;
        }

        saveScrollPosition();
        borrowForm.submit();
    });
});
