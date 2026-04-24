
/**
 * Initializes Kinventory/Kinventario page behavior after the DOM is fully loaded.
 * Handles:
 * - search button enable/disable behavior
 * - scroll position persistence across reloads
 * - borrow modal population
 * - quantity validation before adding items to the cart
 */

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Storage key used to persist the user's vertical scroll position
     * between page navigations and form submissions.
     */
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

    const searchInput = document.getElementById('kinventorySearchInput');
    const searchBtn = document.getElementById('kinventorySearchBtn');

    /**
     * Stores the current window scroll position in sessionStorage.
     * This helps restore the user's location after page reloads.
     */
    function saveScrollPosition() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
    }

    /**
     * Removes any previously saved scroll position from sessionStorage.
     */
    function clearScrollPosition() {
        sessionStorage.removeItem(SCROLL_KEY);
    }

    /**
     * Enables the search button only when the search input contains
     * non-whitespace text. Prevents empty searches from being submitted.
     */
    function updateSearchButtonState() {
        if (!searchInput || !searchBtn) return;

        const hasText = searchInput.value.trim().length > 0;

        searchBtn.disabled = !hasText;
    }

    /**
     * Determines whether a clicked link belongs to the pagination controls.
     *
     * @param {HTMLElement} link - The anchor element being checked.
     * @returns {boolean} True if the link is inside a pagination component.
     */
    function isPaginationLink(link) {
        return !!link.closest('.pagination');
    }

    /**
     * Restores the user's previous scroll position after the page reloads.
     * Retries multiple times because content may finish rendering slightly
     * after the initial page load. Returns to said position for consistency.
     */
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

    if (searchInput) {
        searchInput.addEventListener('input', updateSearchButtonState);
    }

    updateSearchButtonState();

    /**
     * Verifies that all required borrow modal elements exist before
     * attaching modal-specific behavior and validation logic.
     */
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

    /**
     * Tracks the item currently selected in the borrow modal.
     * This data is populated from the clicked "Pedir Prestado" button.
     */
    let currentItem = {
        id: null,
        name: '',
        stock: 0,
        image: '',
        location: ''
    };

    /**
     * Displays a validation error for the borrow quantity input.
     *
     * @param {string} message - Error message shown below the input.
     */
    function setBorrowQuantityError(message) {
        borrowQuantity.classList.add('is-invalid');
        borrowQuantityError.textContent = message;
        borrowQuantityError.style.display = 'block';
    }

    /**
     * Clears any active validation error from the borrow quantity input.
     */
    function clearBorrowQuantityError() {
        borrowQuantity.classList.remove('is-invalid');
        borrowQuantityError.textContent = '';
        borrowQuantityError.style.display = 'none';
    }

    /**
     * Parses the current quantity input value as an integer.
     *
     * @returns {number|null} Parsed integer value, or null if invalid.
     */

    function normalizeQuantityValue() {
        const value = parseInt(borrowQuantity.value, 10);
        return Number.isNaN(value) ? null : value;
    }

    /**
     * Validates the borrow quantity against required rules:
     * - must not be empty
     * - must be a valid number
     * - must be at least 1
     * - must not exceed available stock
     *
     * It provides safety against code injection.
     *
     * @param {boolean} showError - Whether to display validation feedback.
     * @returns {boolean} True when the quantity is valid.
     */

    function validateBorrowQuantity(showError = true) {
        const value = normalizeQuantityValue();

        if (borrowQuantity.value === '') {
            if (showError) {
                setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            }
            return false;
        }

        if (value === null) {
            if (showError) {
                setBorrowQuantityError('Debes ingresar un número válido.');
            }
            return false;
        }

        if (value < 1) {
            if (showError) {
                setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            }
            return false;
        }

        if (value > currentItem.stock) {
            if (showError) {
                setBorrowQuantityError(`No puedes pedir más de ${currentItem.stock} unidades disponibles.`);
            }
            return false;
        }

        clearBorrowQuantityError();
        return true;
    }

    /**
     * Populates the borrow modal with item-specific data when a borrow
     * button is clicked, then resets the quantity input to a safe default.
     */
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
            clearBorrowQuantityError();

            setTimeout(() => {
                borrowQuantity.focus();
                borrowQuantity.select();
            }, 150);
        });
    });

    /**
     * Performs live validation while the user types in the quantity field.
     * Automatically corrects values that fall outside the valid range.
     */
    borrowQuantity.addEventListener('input', () => {
        if (borrowQuantity.value === '') {
            setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            return;
        }

        const value = normalizeQuantityValue();

        if (value === null) {
            setBorrowQuantityError('Debes ingresar un número válido.');
            return;
        }

        if (value < 1) {
            borrowQuantity.value = '1';
            setBorrowQuantityError('Debes pedir al menos 1 unidad.');
            return;
        }

        if (value > currentItem.stock) {
            borrowQuantity.value = String(currentItem.stock);
            setBorrowQuantityError(`No puedes pedir más de ${currentItem.stock} unidades disponibles.`);
            return;
        }

        clearBorrowQuantityError();
    });

    /**
     * Prevents keyboard-based incrementing beyond the available stock.
     */
    borrowQuantity.addEventListener('keydown', (event) => {
        const value = normalizeQuantityValue();

        if (
            (event.key === 'ArrowUp' || event.key === 'PageUp') &&
            value !== null &&
            value >= currentItem.stock
        ) {
            event.preventDefault();
            setBorrowQuantityError(`No puedes pedir más de ${currentItem.stock} unidades disponibles.`);
        }
    });

    /**
     * Prevents mouse-wheel incrementing beyond the available stock
     * when the quantity input is focused. The team mainly used laptops but this was
     * necessary for computers that use a mouse when deployed.
     */
    borrowQuantity.addEventListener('wheel', (event) => {
        if (document.activeElement !== borrowQuantity) return;

        const value = normalizeQuantityValue();

        if (event.deltaY < 0 && value !== null && value >= currentItem.stock) {
            event.preventDefault();
            setBorrowQuantityError(`No puedes pedir más de ${currentItem.stock} unidades disponibles.`);
        }
    }, { passive: false });

    /**
     * Restores a default value if the input is left empty and re-validates
     * the field when the user leaves the quantity input.
     */
    borrowQuantity.addEventListener('blur', () => {
        if (borrowQuantity.value === '') {
            borrowQuantity.value = '1';
        }

        validateBorrowQuantity(true);
    });

    /**
     * Validates pasted input before it is applied to the quantity field.
     * Rejects non-numeric, too-small, and too-large values.
     */
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
            setBorrowQuantityError(`No puedes pedir más de ${currentItem.stock} unidades disponibles.`);
            return;
        }

        clearBorrowQuantityError();
    });

    /**
     * Validates the quantity before submitting the borrow form.
     * Saves scroll position so the user can return to the same location
     * after the request completes.
     */
    confirmAddToCart.addEventListener('click', () => {
        if (!validateBorrowQuantity(true)) {
            return;
        }

        saveScrollPosition();
        borrowForm.submit();
    });

    /** 
     * Show error toast if exists 
     */
    const requestErrorToastElement = document.getElementById('inventoryRequestErrorToast');

    if (requestErrorToastElement && typeof bootstrap !== 'undefined') {
        const toast = bootstrap.Toast.getOrCreateInstance(requestErrorToastElement);
        toast.show();
    }

});
