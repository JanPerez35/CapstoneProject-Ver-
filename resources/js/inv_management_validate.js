
document.querySelectorAll('[data-bs-toggle="tooltip"]')

    /*
       * Activates tool tips
       */
    .forEach(el => new bootstrap.Tooltip(el));

document.addEventListener('DOMContentLoaded', function () {

    /*
     * Global validation limits and shared constants used across the
     * inventory management forms.
     */
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
    const MAX_TEXT_LENGTH = 100;
    const MIN_TEXT_LENGTH = 3;
    const SCROLL_KEY = 'inventoryScrollY';

    /*
     * Validation patterns for text-based fields.
     * Category and description share the same allowed characters.
     */
    const TEXT_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/;
    const CATEGORY_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/;
    const LOCATION_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/;

    /*
     * References to search controls, toast notifications, and
     * confirmation modals used throughout the inventory page.
     */
    const searchInput = document.getElementById('inventorySearchInput');
    const searchBtn = document.getElementById('inventorySearchBtn');

    const addToastElement = document.getElementById('inventoryAddToast');
    const deleteToastElement = document.getElementById('inventoryDeleteToast');
    const editToastElement = document.getElementById('inventoryEditToast');

    const confirmDeleteModalElement = document.getElementById('confirmDeleteModal');
    const confirmDeleteText = document.getElementById('confirmDeleteText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const confirmDeleteWarningText = document.getElementById('confirmDeleteWarningText');

    const confirmEditModalElement = document.getElementById('confirmEditModal');
    const confirmEditText = document.getElementById('confirmEditText');
    const confirmEditBtn = document.getElementById('confirmEditBtn');

    /*
     * Stores the forms currently waiting for user confirmation
     * before delete or edit actions are submitted.
     */
    let pendingDeleteForm = null;
    let pendingEditForm = null;
    let isConfirmedEditSubmit = false;

    /*
     * Saves and restores scroll position so the user returns to the
     * same section after submitting forms or navigating the page.
     */
    function saveScroll() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
    }

    function clearScroll() {
        sessionStorage.removeItem(SCROLL_KEY);
    }

    function isPaginationLink(link) {
        return !!link.closest('.pagination');
    }

    function restoreScroll() {
        const saved = sessionStorage.getItem(SCROLL_KEY);
        if (!saved) return;

        const target = parseInt(saved, 10);
        if (Number.isNaN(target)) {
            clearScroll();
            return;
        }

        let attempts = 0;
        const maxAttempts = 20;

        const tryScroll = () => {
            window.scrollTo(0, target);
            attempts++;

            if (attempts < maxAttempts && Math.abs(window.scrollY - target) > 5) {
                setTimeout(tryScroll, 100);
            } else {
                clearScroll();
            }
        };

        setTimeout(tryScroll, 100);
    }

    /*
     * Enables or disables the search button depending on whether
     * the user has entered text in the search field.
     */
    function updateSearchButtonState() {
        if (!searchInput || !searchBtn) return;
        searchBtn.disabled = searchInput.value.trim().length === 0;
    }

    /*
     * Preserves scroll position across most form submissions and links,
     * except for pagination where the page should behave normally.
     */
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', saveScroll);
    });

    document.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            if (isPaginationLink(link)) {
                clearScroll();
                return;
            }
            saveScroll();
        });
    });

    window.addEventListener('load', restoreScroll);

    if (searchInput) {
        searchInput.addEventListener('input', updateSearchButtonState);
    }

    updateSearchButtonState();

    /*
     * Helper functions to safely retrieve Bootstrap toast and modal
     * instances before interacting with them.
     */
    function getToastInstance(element) {
        if (!element || typeof bootstrap === 'undefined') return null;
        return bootstrap.Toast.getOrCreateInstance(element);
    }

    function getModalInstance(element) {
        if (!element || typeof bootstrap === 'undefined') return null;
        return bootstrap.Modal.getOrCreateInstance(element);
    }

    function showToast(element) {
        const toast = getToastInstance(element);
        if (toast) toast.show();
    }

    function showModal(element) {
        const modal = getModalInstance(element);
        if (modal) modal.show();
    }

    function hideModal(element) {
        const modal = getModalInstance(element);
        if (modal) modal.hide();
    }

    /*
     * Standardized error handling utilities used by all validations.
     * They keep the validation feedback visually consistent.
     */
    function setError(input, errorElement, message) {
        if (input) {
            input.classList.add('is-invalid');
        }
        if (errorElement) {
            errorElement.textContent = message;
        }
    }

    function clearError(input, errorElement) {
        if (input) {
            input.classList.remove('is-invalid');
        }
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    /*
     * Resolves the final value for fields that can come from either
     * an existing option or a manually entered new value.
     */
    function getResolvedValue(existingInput, newInput, hiddenInput) {
        const newValue = newInput ? newInput.value.trim() : '';
        const existingValue = existingInput ? existingInput.value.trim() : '';
        const finalValue = newValue || existingValue;

        if (hiddenInput) {
            hiddenInput.value = finalValue;
        }

        return finalValue;
    }

    /*
     * Generic text validation used by description, category, and location
     * after resolving the final value that will be submitted.
     */
    function validateResolvedTextValue({
                                           value,
                                           visibleInput,
                                           errorElement,
                                           emptyMessage,
                                           invalidMessage,
                                           minMessage,
                                           maxMessage,
                                           min,
                                           max,
                                           regex,
                                           showError = true
                                       }) {
        if (showError) {
            clearError(visibleInput, errorElement);
        }

        if (!value) {
            if (showError) {
                setError(visibleInput, errorElement, emptyMessage);
            }
            return false;
        }

        if (!regex.test(value)) {
            if (showError) {
                setError(visibleInput, errorElement, invalidMessage);
            }
            return false;
        }

        if (value.length < min) {
            if (showError) {
                setError(visibleInput, errorElement, minMessage);
            }
            return false;
        }

        if (value.length > max) {
            if (showError) {
                setError(visibleInput, errorElement, maxMessage);
            }
            return false;
        }

        return true;
    }

    /*
     * Validates uploaded images by checking presence when required,
     * allowed format, and maximum file size.
     */
    function validateImageField(input, errorElement, required = false, showError = true) {
        if (showError) {
            clearError(input, errorElement);
        }

        const file = input.files[0];

        if (!file) {
            if (required) {
                if (showError) {
                    setError(input, errorElement, 'Debes subir una imagen.');
                }
                return false;
            }
            return true;
        }

        if (!['image/jpeg', 'image/jpg'].includes(file.type)) {
            input.value = '';

            if (showError) {
                setError(input, errorElement, 'Solo se permiten archivos JPG o JPEG.');
            }
            return false;
        }

        if (file.size > MAX_IMAGE_SIZE) {
            input.value = '';

            if (showError) {
                setError(input, errorElement, 'La imagen no puede exceder 2 MB.');
            }
            return false;
        }

        return true;
    }

    /*
     * Validates total quantity fields to ensure they contain a valid
     * positive integers.
     */
    function validateQuantityField(input, errorElement, showError = true) {
        if (showError) {
            clearError(input, errorElement);
        }

        const value = input.value.trim();

        if (value === '') {
            if (showError) {
                setError(input, errorElement, 'La cantidad total es obligatoria.');
            }
            return false;
        }

        const numericValue = Number(value);

        if (!Number.isInteger(numericValue) || numericValue < 1) {
            if (showError) {
                setError(input, errorElement, 'La cantidad total debe ser un número entero mayor o igual a 1.');
            }
            return false;
        }

        return true;
    }

    /*
     * Validates available quantity fields and ensures they do not exceed
     * the total quantity of the item.
     */
    function validateAvailableField(availableInput, quantityInput, errorElement, showError = true) {
        if (showError) {
            clearError(availableInput, errorElement);
        }

        const availableValue = availableInput.value.trim();
        const quantityValue = quantityInput.value.trim();

        if (availableValue === '') {
            if (showError) {
                setError(availableInput, errorElement, 'La cantidad disponible es obligatoria.');
            }
            return false;
        }

        const available = Number(availableValue);
        const quantity = Number(quantityValue);

        if (!Number.isInteger(available) || available < 0) {
            if (showError) {
                setError(availableInput, errorElement, 'La cantidad disponible debe ser un número entero mayor o igual a 0.');
            }
            return false;
        }

        if (!Number.isInteger(quantity) || quantity < 1) {
            return false;
        }

        if (available > quantity) {
            if (showError) {
                setError(availableInput, errorElement, 'La cantidad disponible no puede exceder la cantidad total.');
            }
            return false;
        }

        return true;
    }

    /*
     * Prevents text fields from exceeding the allowed length during
     * live typing or paste actions.
     */
    function validateTextLengthLive(input, errorElement, event = null) {
        if (!input) return true;

        const triedToInsert =
            event &&
            (
                event.inputType === 'insertText' ||
                event.inputType === 'insertFromPaste' ||
                event.inputType === 'insertCompositionText'
            );

        if (input.value.length >= MAX_TEXT_LENGTH && triedToInsert) {
            setError(input, errorElement, 'Solo puedes escribir hasta 100 caracteres.');
            return false;
        }

        if (input.value.length < MAX_TEXT_LENGTH) {
            clearError(input, errorElement);
        }

        return true;
    }

    /*
     * Retrieves a readable item name for confirmation messages,
     * using the form field first and the card title as fallback.
     */
    function getItemNameFromForm(form) {
        const descriptionInput = form.querySelector('input[name="description"]');
        if (descriptionInput && descriptionInput.value.trim()) {
            return descriptionInput.value.trim();
        }

        const card = form.closest('.inventory-card-wrapper');
        const cardTitle = card ? card.querySelector('.inventory-item-name') : null;

        if (cardTitle && cardTitle.textContent.trim()) {
            return cardTitle.textContent.trim();
        }

        return 'este item';
    }

    /*
     * Configures delete confirmation behavior so item removals are
     * explicitly confirmed before submitting the delete form.
     */
    function setupDeleteConfirmation() {
        document.querySelectorAll('.deleteItemForm').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                pendingDeleteForm = form;

                const card = form.closest('.inventory-card-wrapper');
                const itemNameElement = card ? card.querySelector('.inventory-item-name') : null;
                const itemName = itemNameElement ? itemNameElement.textContent.trim() : 'este item';

                if (confirmDeleteText) {
                    confirmDeleteText.textContent = `¿Seguro que quieres borrar "${itemName}"?`;
                }

                const hasActiveOrders = form.dataset.hasActiveOrders === '1';

                /*
                 * Shows an additional warning when the selected item
                 * is linked to active or pending borrow requests.
                 */
                if (confirmDeleteWarningText) {
                    confirmDeleteWarningText.classList.toggle('d-none', !hasActiveOrders);
                }

                showModal(confirmDeleteModalElement);
            });
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', function () {
                if (!pendingDeleteForm) return;

                const form = pendingDeleteForm;
                pendingDeleteForm = null;

                if (deleteToastElement) {
                    sessionStorage.setItem('inventory_delete_toast', '1');
                }

                hideModal(confirmDeleteModalElement);
                form.submit();
            });
        }
    }

    /*
     * Configures edit confirmation behavior so validated updates are
     * confirmed by the user before submission.
     */
    function setupEditConfirmation() {
        if (confirmEditBtn) {
            confirmEditBtn.addEventListener('click', function () {
                if (!pendingEditForm) return;

                const form = pendingEditForm;
                pendingEditForm = null;

                if (editToastElement) {
                    sessionStorage.setItem('inventory_edit_toast', '1');
                }

                hideModal(confirmEditModalElement);
                isConfirmedEditSubmit = true;
                form.requestSubmit();
            });
        }
    }

    /*
     * Restores toast notifications after redirects by reading flags
     * stored in sessionStorage or page dataset attributes.
     */
    function handleStoredToasts() {
        const addSuccess = document.body.dataset.inventoryAddSuccess === '1';
        const deleteSuccess = sessionStorage.getItem('inventory_delete_toast') === '1';
        const editSuccess = sessionStorage.getItem('inventory_edit_toast') === '1';

        if (addSuccess && addToastElement) {
            showToast(addToastElement);
        }

        if (deleteSuccess && deleteToastElement) {
            sessionStorage.removeItem('inventory_delete_toast');
            showToast(deleteToastElement);
        }

        if (editSuccess && editToastElement) {
            sessionStorage.removeItem('inventory_edit_toast');
            showToast(editToastElement);
        }
    }

    /*
     * Add item form validation logic.
     * This section validates all required fields, synchronizes the
     * hidden resolved values, controls the preview, and enables the
     * submit button only when the form is fully valid.
     */
    const addItemForm = document.getElementById('addItemForm');

    if (addItemForm) {
        const nameInput = document.getElementById('nombre_item');

        const categoryExisting = document.getElementById('categoria_existente');
        const categoryNew = document.getElementById('categoria_nueva');
        const categoryFinal = document.getElementById('categoria');

        const totalQuantityInput = document.getElementById('cantidad_total');
        const availableQuantityInput = document.getElementById('cantidad_disponible');

        const locationExisting = document.getElementById('ubicacion_existente');
        const locationNew = document.getElementById('ubicacion_nueva');
        const locationFinal = document.getElementById('ubicacion');

        const imageInput = document.getElementById('imagen');

        const nameError = document.getElementById('nombreItemError');
        const categoryError = document.getElementById('categoriaError');
        const totalQuantityError = document.getElementById('cantidadTotalError');
        const availableQuantityError = document.getElementById('cantidadDisponibleError');
        const locationError = document.getElementById('ubicacionError');
        const imageError = document.getElementById('imageError');

        const previewWrapper = document.getElementById('previewWrapper');
        const imagePreview = document.getElementById('imagePreview');
        const submitAddItemBtn = document.getElementById('submitAddItemBtn');

        /*
         * Field-specific validation helpers for the add item form.
         */
        function validateName(showError = true) {
            const value = nameInput.value.trim();

            if (nameInput.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(nameInput, nameError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput: nameInput,
                errorElement: nameError,
                emptyMessage: 'El nombre del equipo es obligatorio.',
                invalidMessage: 'El nombre del equipo contiene caracteres no permitidos.',
                minMessage: 'El nombre del equipo debe tener al menos 3 caracteres.',
                maxMessage: 'El nombre del equipo no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: TEXT_REGEX,
                showError
            });
        }

        function validateCategory(showError = true) {
            const newValue = categoryNew.value.trim();
            const existingValue = categoryExisting.value.trim();
            const isUsingNew = categoryNew.value !== '';

            if (showError) {
                clearError(categoryExisting, categoryError);
                clearError(categoryNew, categoryError);
            }

            if (isUsingNew) {
                categoryFinal.value = newValue;

                if (categoryNew.value.length >= MAX_TEXT_LENGTH) {
                    if (showError) {
                        setError(categoryNew, categoryError, 'Solo puedes escribir hasta 100 caracteres.');
                    }
                    return false;
                }

                return validateResolvedTextValue({
                    value: newValue,
                    visibleInput: categoryNew,
                    errorElement: categoryError,
                    emptyMessage: 'La categoría es obligatoria.',
                    invalidMessage: 'La categoría contiene caracteres no permitidos.',
                    minMessage: 'La categoría debe tener al menos 3 caracteres.',
                    maxMessage: 'La categoría no puede exceder 100 caracteres.',
                    min: 3,
                    max: MAX_TEXT_LENGTH,
                    regex: CATEGORY_REGEX,
                    showError
                });
            }

            categoryFinal.value = existingValue;

            return validateResolvedTextValue({
                value: existingValue,
                visibleInput: categoryExisting,
                errorElement: categoryError,
                emptyMessage: 'La categoría es obligatoria.',
                invalidMessage: 'La categoría contiene caracteres no permitidos.',
                minMessage: 'La categoría debe tener al menos 3 caracteres.',
                maxMessage: 'La categoría no puede exceder 100 caracteres.',
                min: 3,
                max: MAX_TEXT_LENGTH,
                regex: CATEGORY_REGEX,
                showError
            });
        }

        function validateLocation(showError = true) {
            const newValue = locationNew.value.trim();
            const existingValue = locationExisting.value.trim();
            const isUsingNew = locationNew.value !== '';

            if (showError) {
                clearError(locationExisting, locationError);
                clearError(locationNew, locationError);
            }


            if (isUsingNew) {
                locationFinal.value = newValue;

                if (locationNew.value.length >= MAX_TEXT_LENGTH) {
                    if (showError) {
                        setError(locationNew, locationError, 'Solo puedes escribir hasta 100 caracteres.');
                    }
                    return false;
                }

                return validateResolvedTextValue({
                    value: newValue,
                    visibleInput: locationNew,
                    errorElement: locationError,
                    emptyMessage: 'La ubicación es obligatoria.',
                    invalidMessage: 'La ubicación contiene caracteres no permitidos.',
                    minMessage: 'La ubicación debe tener al menos 3 caracteres.',
                    maxMessage: 'La ubicación no puede exceder 100 caracteres.',
                    min: 3,
                    max: MAX_TEXT_LENGTH,
                    regex: LOCATION_REGEX,
                    showError
                });
            }

            locationFinal.value = existingValue;

            return validateResolvedTextValue({
                value: existingValue,
                visibleInput: locationExisting,
                errorElement: locationError,
                emptyMessage: 'La ubicación es obligatoria.',
                invalidMessage: 'La ubicación contiene caracteres no permitidos.',
                minMessage: 'La ubicación debe tener al menos 3 caracteres.',
                maxMessage: 'La ubicación no puede exceder 100 caracteres.',
                min: 3,
                max: MAX_TEXT_LENGTH,
                regex: LOCATION_REGEX,
                showError
            });
        }

        function validateTotalQuantity(showError = true) {
            return validateQuantityField(totalQuantityInput, totalQuantityError, showError);
        }

        function validateAvailableQuantity(showError = true) {
            return validateAvailableField(availableQuantityInput, totalQuantityInput, availableQuantityError, showError);
        }

        function validateImage(showError = true) {
            return validateImageField(imageInput, imageError, true, showError);
        }

        /*
         * Updates the preview only when the selected image is valid.
         */
        function updatePreview() {
            const file = imageInput.files[0];

            if (!file) {
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
                return;
            }

            if (file.size > MAX_IMAGE_SIZE || !['image/jpeg', 'image/jpg'].includes(file.type)) {
                previewWrapper.classList.add('d-none');
                imagePreview.src = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
                previewWrapper.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        /*
         * Enables the add button only when every required field
         * currently satisfies validation rules.
         */
        function updateAddButtonState() {
            const valid =
                validateName(false) &&
                validateCategory(false) &&
                validateTotalQuantity(false) &&
                validateAvailableQuantity(false) &&
                validateLocation(false) &&
                validateImage(false);

            submitAddItemBtn.disabled = !valid;
        }

        /*
         * Live validation listeners for add form inputs.
         */
        nameInput.addEventListener('input', function (e) {
            const triedToInsert =
                e.inputType === 'insertText' ||
                e.inputType === 'insertFromPaste' ||
                e.inputType === 'insertCompositionText';

            if (nameInput.value.length >= MAX_TEXT_LENGTH && triedToInsert) {
                setError(nameInput, nameError, 'Solo puedes escribir hasta 100 caracteres.');
                updateAddButtonState();
                return;
            }

            if (nameInput.value.length < MAX_TEXT_LENGTH) {
                clearError(nameInput, nameError);
            }

            validateName(true);
            updateAddButtonState();
        });

        nameInput.addEventListener('paste', function (e) {
            const pasted = (e.clipboardData || window.clipboardData).getData('text');

            if ((nameInput.value.length + pasted.length) > MAX_TEXT_LENGTH) {
                e.preventDefault();
                setError(nameInput, nameError, 'Solo puedes escribir hasta 100 caracteres.');
            }
        });

        categoryExisting.addEventListener('change', function () {
            if (categoryExisting.value) {
                categoryNew.value = '';
            }
            validateCategory(true);
            updateAddButtonState();
        });

        categoryNew.addEventListener('input', function (e) {
            categoryExisting.value = '';
            categoryExisting.classList.remove('is-invalid');

            if (!validateTextLengthLive(categoryNew, categoryError, e)) {
                updateAddButtonState();
                return;
            }

            validateCategory(true);
            updateAddButtonState();
        });

        locationExisting.addEventListener('change', function () {
            if (locationExisting.value) {
                locationNew.value = '';
            }
            validateLocation(true);
            updateAddButtonState();
        });

        locationNew.addEventListener('input', function (e) {
            if (locationNew.value.trim()) {
                locationExisting.value = '';
                locationExisting.classList.remove('is-invalid');
            }

            if (!validateTextLengthLive(locationNew, locationError, e)) {
                updateAddButtonState();
                return;
            }

            validateLocation(true);
            updateAddButtonState();
        });

        totalQuantityInput.addEventListener('input', function () {
            validateTotalQuantity(true);
            validateAvailableQuantity(true);
            updateAddButtonState();
        });

        availableQuantityInput.addEventListener('input', function () {
            validateAvailableQuantity(true);
            updateAddButtonState();
        });

        imageInput.addEventListener('change', function () {
            validateImage(true);
            updatePreview();
            updateAddButtonState();
        });

        /*
         * Final validation before submitting a new inventory item.
         */
        addItemForm.addEventListener('submit', function (e) {
            const valid =
                validateName(true) &&
                validateCategory(true) &&
                validateTotalQuantity(true) &&
                validateAvailableQuantity(true) &&
                validateLocation(true) &&
                validateImage(true);

            if (!valid) {
                e.preventDefault();
                return;
            }

            if (addToastElement) {
                sessionStorage.setItem('inventory_add_toast', '1');
            }

            submitAddItemBtn.disabled = true;
        });

        updateAddButtonState();
    }

    /*
     * Edit item form validation logic.
     * Each edit modal gets its own validation behavior so updates remain
     * isolated to the selected inventory item.
     */
    document.querySelectorAll('.editItemForm').forEach(function (form) {
        const descriptionInput = form.querySelector('input[name="description"]');

        const categoryExisting = form.querySelector('.edit-category-existing');
        const categoryNew = form.querySelector('.edit-category-new');
        const categoryFinal = form.querySelector('.edit-category-final');

        const locationExisting = form.querySelector('.edit-location-existing');
        const locationNew = form.querySelector('.edit-location-new');
        const locationFinal = form.querySelector('.edit-location-final');

        const quantityInput = form.querySelector('input[name="quantity"]');
        const availableInput = form.querySelector('input[name="available_quantity"]');
        const imageInput = form.querySelector('input[name="image"]');

        const descriptionError = form.querySelector('.error-description');
        const categoryError = form.querySelector('.error-category');
        const locationError = form.querySelector('.error-location');
        const quantityError = form.querySelector('.error-quantity');
        const availableError = form.querySelector('.error-available');
        const imageError = form.querySelector('.error-image');

        const editPreviewWrapper = form.querySelector('.edit-preview-wrapper');
        const editImagePreview = form.querySelector('.edit-image-preview');

        const submitEditBtn = form.querySelector('button[type="submit"]');

        /*
 * Field-specific validation helpers for the edit item form.
 */
        function validateDescription(showError = true) {
            const value = descriptionInput.value.trim();

            if (descriptionInput.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(descriptionInput, descriptionError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput: descriptionInput,
                errorElement: descriptionError,
                emptyMessage: 'El nombre del equipo es obligatorio.',
                invalidMessage: 'El nombre del equipo contiene caracteres no permitidos.\n',
                minMessage: 'El nombre del equipo debe tener al menos 3 caracteres.',
                maxMessage: 'El nombre del equipo no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: TEXT_REGEX,
                showError
            });
        }

        function validateCategory(showError = true) {
            const newValue = categoryNew.value.trim();
            const existingValue = categoryExisting.value.trim();
            const isUsingNew = categoryNew.value !== '';

            if (showError) {
                clearError(categoryExisting, categoryError);
                clearError(categoryNew, categoryError);
            }

            if (isUsingNew) {
                categoryFinal.value = newValue;

                if (categoryNew.value.length > MAX_TEXT_LENGTH) {
                    if (showError) {
                        setError(categoryNew, categoryError, 'Solo puedes escribir hasta 100 caracteres.');
                    }
                    return false;
                }

                return validateResolvedTextValue({
                    value: newValue,
                    visibleInput: categoryNew,
                    errorElement: categoryError,
                    emptyMessage: 'La categoría es obligatoria.',
                    invalidMessage: 'La categoría contiene caracteres no permitidos.',
                    minMessage: 'La categoría debe tener al menos 3 caracteres.',
                    maxMessage: 'La categoría no puede exceder 100 caracteres.',
                    min: 3,
                    max: MAX_TEXT_LENGTH,
                    regex: CATEGORY_REGEX,
                    showError
                });
            }

            categoryFinal.value = existingValue;

            return validateResolvedTextValue({
                value: existingValue,
                visibleInput: categoryExisting,
                errorElement: categoryError,
                emptyMessage: 'La categoría es obligatoria.',
                invalidMessage: 'La categoría contiene caracteres no permitidos.',
                minMessage: 'La categoría debe tener al menos 3 caracteres.',
                maxMessage: 'La categoría no puede exceder 100 caracteres.',
                min: 3,
                max: MAX_TEXT_LENGTH,
                regex: CATEGORY_REGEX,
                showError
            });
        }

        function validateLocation(showError = true) {
            const value = getResolvedValue(locationExisting, locationNew, locationFinal);
            const isUsingNew = locationNew.value.trim() !== '';
            const activeInput = isUsingNew ? locationNew : locationExisting;

            if (showError) {
                clearError(locationExisting, locationError);
                clearError(locationNew, locationError);
            }

            if (locationNew.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(locationNew, locationError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput: activeInput,
                errorElement: locationError,
                emptyMessage: 'La ubicación es obligatoria.',
                invalidMessage: 'La ubicación contiene caracteres no permitidos.',
                minMessage: 'La ubicación debe tener al menos 3 caracteres.',
                maxMessage: 'La ubicación no puede exceder 100 caracteres.',
                min: 3,
                max: MAX_TEXT_LENGTH,
                regex: LOCATION_REGEX,
                showError
            });
        }

        function validateQuantity(showError = true) {
            return validateQuantityField(quantityInput, quantityError, showError);
        }

        function validateAvailable(showError = true) {
            return validateAvailableField(availableInput, quantityInput, availableError, showError);
        }

        function validateEditImage(showError = true) {
            return validateImageField(imageInput, imageError, false, showError);
        }

        function updateEditPreview() {
            const file = imageInput.files[0];

            if (!file) {
                editPreviewWrapper?.classList.add('d-none');
                if (editImagePreview) editImagePreview.src = '';
                return;
            }

            if (file.size > MAX_IMAGE_SIZE || !['image/jpeg', 'image/jpg'].includes(file.type)) {
                editPreviewWrapper?.classList.add('d-none');
                if (editImagePreview) editImagePreview.src = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                if (editImagePreview) editImagePreview.src = e.target.result;
                editPreviewWrapper?.classList.remove('d-none');
            };

            reader.readAsDataURL(file);
        }


        function updateEditButtonState() {
            const valid =
                validateDescription(false) &&
                validateCategory(false) &&
                validateLocation(false) &&
                validateQuantity(false) &&
                validateAvailable(false) &&
                validateEditImage(false);

            submitEditBtn.disabled = !valid;
        }

        /*
         * Live validation listeners for edit form inputs.
         */
        descriptionInput.addEventListener('input', function (e) {
            const triedToInsert =
                e.inputType === 'insertText' ||
                e.inputType === 'insertFromPaste' ||
                e.inputType === 'insertCompositionText';

            if (descriptionInput.value.length >= MAX_TEXT_LENGTH && triedToInsert) {
                setError(descriptionInput, descriptionError, 'Solo puedes escribir hasta 100 caracteres.');
                updateEditButtonState();
                return;
            }

            if (descriptionInput.value.length < MAX_TEXT_LENGTH) {
                clearError(descriptionInput, descriptionError);
            }

            validateDescription(true);
            updateEditButtonState();
        });

        categoryExisting.addEventListener('change', function () {
            if (categoryExisting.value) {
                categoryNew.value = '';
            }
            validateCategory(true);
            updateEditButtonState();
        });

        categoryNew.addEventListener('input', function (e) {
            categoryExisting.value = '';
            categoryExisting.classList.remove('is-invalid');

            if (!validateTextLengthLive(categoryNew, categoryError, e)) {
                updateEditButtonState();
                return;
            }

            validateCategory(true);
            updateEditButtonState();
        });

        locationExisting.addEventListener('change', function () {
            if (locationExisting.value) {
                locationNew.value = '';
            }
            validateLocation(true);
            updateEditButtonState();
        });

        locationNew.addEventListener('input', function (e) {
            if (locationNew.value.trim()) {
                locationExisting.value = '';
                locationExisting.classList.remove('is-invalid');
            }

            if (!validateTextLengthLive(locationNew, locationError, e)) {
                updateEditButtonState();
                return;
            }

            validateLocation(true);
            updateEditButtonState();
        });

        quantityInput.addEventListener('input', function () {
            validateQuantity(true);
            validateAvailable(true);
            updateEditButtonState();
        });

        availableInput.addEventListener('input', function () {
            validateAvailable(true);
            updateEditButtonState();
        });

        imageInput.addEventListener('change', function () {
            validateEditImage(true);
            updateEditPreview();
            updateEditButtonState();
        });

        /*
         * Final validation for edit submissions.
         * If valid, submission is paused until the user confirms the edit.
         */
        form.addEventListener('submit', function (e) {

            if (isConfirmedEditSubmit) {
                isConfirmedEditSubmit = false;
                return;
            }

            const valid =
                validateDescription(true) &&
                validateCategory(true) &&
                validateLocation(true) &&
                validateQuantity(true) &&
                validateAvailable(true) &&
                validateEditImage(true);

            if (!valid) {
                e.preventDefault();
                return;
            }

            e.preventDefault();
            pendingEditForm = form;

            const itemName = getItemNameFromForm(form);

            if (confirmEditText) {
                confirmEditText.textContent = `¿Seguro que quieres editar "${itemName}"?`;
            }

            showModal(confirmEditModalElement);
        });
        updateEditButtonState();
    });

    /*
     * Initializes delete/edit confirmations and restores any pending
     * success toasts after page reload.
     */
    setupDeleteConfirmation();
    setupEditConfirmation();

    if (sessionStorage.getItem('inventory_add_toast') === '1') {
        sessionStorage.removeItem('inventory_add_toast');
        showToast(addToastElement);
    }

    handleStoredToasts();
});
