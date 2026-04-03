document.addEventListener('DOMContentLoaded', function () {
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
    const MAX_TEXT_LENGTH = 100;
    const MIN_TEXT_LENGTH = 5;

    const TEXT_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/;
    const CATEGORY_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,-]+$/;
    const LOCATION_REGEX = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s.,\-\/]+$/;

    const addToastElement = document.getElementById('inventoryAddToast');
    const deleteToastElement = document.getElementById('inventoryDeleteToast');
    const editToastElement = document.getElementById('inventoryEditToast');

    const confirmDeleteModalElement = document.getElementById('confirmDeleteModal');
    const confirmDeleteText = document.getElementById('confirmDeleteText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    const confirmEditModalElement = document.getElementById('confirmEditModal');
    const confirmEditText = document.getElementById('confirmEditText');
    const confirmEditBtn = document.getElementById('confirmEditBtn');

    let pendingDeleteForm = null;
    let pendingEditForm = null;

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
        if (toast) {
            toast.show();
        }
    }

    function showModal(element) {
        const modal = getModalInstance(element);
        if (modal) {
            modal.show();
        }
    }

    function hideModal(element) {
        const modal = getModalInstance(element);
        if (modal) {
            modal.hide();
        }
    }

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

    function getResolvedValue(existingInput, newInput, hiddenInput) {
        const newValue = newInput ? newInput.value.trim() : '';
        const existingValue = existingInput ? existingInput.value.trim() : '';
        const finalValue = newValue || existingValue;

        if (hiddenInput) {
            hiddenInput.value = finalValue;
        }

        return finalValue;
    }

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
            if (showError) {
                setError(input, errorElement, 'Solo se permiten archivos JPG o JPEG.');
            }
            return false;
        }

        if (file.size > MAX_IMAGE_SIZE) {
            if (showError) {
                setError(input, errorElement, 'La imagen no puede exceder 2 MB.');
            }
            return false;
        }

        return true;
    }

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

    function validateTextLengthLive(input, errorElement) {
        if (!input) return true;

        if (input.value.length > MAX_TEXT_LENGTH) {
            setError(input, errorElement, 'Solo puedes escribir hasta 100 caracteres.');
            return false;
        }

        return true;
    }

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
                form.submit();
            });
        }
    }

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

    // =========================
    // ADD ITEM FORM
    // =========================
    const addItemForm = document.getElementById('addItemForm');

    if (addItemForm) {
        const nombreInput = document.getElementById('nombre_item');

        const categoriaExistente = document.getElementById('categoria_existente');
        const categoriaNueva = document.getElementById('categoria_nueva');
        const categoriaFinal = document.getElementById('categoria');

        const cantidadTotalInput = document.getElementById('cantidad_total');
        const cantidadDisponibleInput = document.getElementById('cantidad_disponible');

        const ubicacionExistente = document.getElementById('ubicacion_existente');
        const ubicacionNueva = document.getElementById('ubicacion_nueva');
        const ubicacionFinal = document.getElementById('ubicacion');

        const imageInput = document.getElementById('imagen');

        const nombreItemError = document.getElementById('nombreItemError');
        const categoriaError = document.getElementById('categoriaError');
        const cantidadTotalError = document.getElementById('cantidadTotalError');
        const cantidadDisponibleError = document.getElementById('cantidadDisponibleError');
        const ubicacionError = document.getElementById('ubicacionError');
        const imageError = document.getElementById('imageError');

        const previewWrapper = document.getElementById('previewWrapper');
        const imagePreview = document.getElementById('imagePreview');
        const submitAddItemBtn = document.getElementById('submitAddItemBtn');

        function validateNombre(showError = true) {
            const value = nombreInput.value.trim();

            if (nombreInput.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(nombreInput, nombreItemError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput: nombreInput,
                errorElement: nombreItemError,
                emptyMessage: 'El nombre del item es obligatorio.',
                invalidMessage: 'El nombre del item contiene caracteres no permitidos.',
                minMessage: 'El nombre del item debe tener al menos 5 caracteres.',
                maxMessage: 'El nombre del item no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: TEXT_REGEX,
                showError
            });
        }

        function validateCategoria(showError = true) {
            const value = getResolvedValue(categoriaExistente, categoriaNueva, categoriaFinal);
            const visibleInput = categoriaNueva.value.trim() ? categoriaNueva : categoriaExistente;

            if (categoriaNueva.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(categoriaNueva, categoriaError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput,
                errorElement: categoriaError,
                emptyMessage: 'La categoría es obligatoria.',
                invalidMessage: 'La categoría contiene caracteres no permitidos.',
                minMessage: 'La categoría debe tener al menos 5 caracteres.',
                maxMessage: 'La categoría no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: CATEGORY_REGEX,
                showError
            });
        }

        function validateUbicacion(showError = true) {
            const value = getResolvedValue(ubicacionExistente, ubicacionNueva, ubicacionFinal);
            const visibleInput = ubicacionNueva.value.trim() ? ubicacionNueva : ubicacionExistente;

            if (ubicacionNueva.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(ubicacionNueva, ubicacionError, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput,
                errorElement: ubicacionError,
                emptyMessage: 'La ubicación es obligatoria.',
                invalidMessage: 'La ubicación contiene caracteres no permitidos.',
                minMessage: 'La ubicación debe tener al menos 5 caracteres.',
                maxMessage: 'La ubicación no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: LOCATION_REGEX,
                showError
            });
        }

        function validateCantidadTotal(showError = true) {
            return validateQuantityField(cantidadTotalInput, cantidadTotalError, showError);
        }

        function validateCantidadDisponible(showError = true) {
            return validateAvailableField(cantidadDisponibleInput, cantidadTotalInput, cantidadDisponibleError, showError);
        }

        function validateImage(showError = true) {
            return validateImageField(imageInput, imageError, true, showError);
        }

        function updatePreview() {
            const file = imageInput.files[0];

            if (!file) {
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

        function updateAddButtonState() {
            const valid =
                validateNombre(false) &&
                validateCategoria(false) &&
                validateCantidadTotal(false) &&
                validateCantidadDisponible(false) &&
                validateUbicacion(false) &&
                validateImage(false);

            submitAddItemBtn.disabled = !valid;
        }

        nombreInput.addEventListener('input', function () {
            if (!validateTextLengthLive(nombreInput, nombreItemError)) {
                updateAddButtonState();
                return;
            }

            validateNombre(true);
            updateAddButtonState();
        });

        categoriaExistente.addEventListener('change', function () {
            if (categoriaExistente.value) {
                categoriaNueva.value = '';
            }
            validateCategoria(true);
            updateAddButtonState();
        });

        categoriaNueva.addEventListener('input', function () {
            if (categoriaNueva.value.trim()) {
                categoriaExistente.value = '';
            }

            if (!validateTextLengthLive(categoriaNueva, categoriaError)) {
                updateAddButtonState();
                return;
            }

            validateCategoria(true);
            updateAddButtonState();
        });

        ubicacionExistente.addEventListener('change', function () {
            if (ubicacionExistente.value) {
                ubicacionNueva.value = '';
            }
            validateUbicacion(true);
            updateAddButtonState();
        });

        ubicacionNueva.addEventListener('input', function () {
            if (ubicacionNueva.value.trim()) {
                ubicacionExistente.value = '';
            }

            if (!validateTextLengthLive(ubicacionNueva, ubicacionError)) {
                updateAddButtonState();
                return;
            }

            validateUbicacion(true);
            updateAddButtonState();
        });

        cantidadTotalInput.addEventListener('input', function () {
            validateCantidadTotal(true);
            validateCantidadDisponible(true);
            updateAddButtonState();
        });

        cantidadDisponibleInput.addEventListener('input', function () {
            validateCantidadDisponible(true);
            updateAddButtonState();
        });

        imageInput.addEventListener('change', function () {
            validateImage(true);
            updatePreview();
            updateAddButtonState();
        });

        addItemForm.addEventListener('submit', function (e) {
            const valid =
                validateNombre(true) &&
                validateCategoria(true) &&
                validateCantidadTotal(true) &&
                validateCantidadDisponible(true) &&
                validateUbicacion(true) &&
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

    // =========================
    // EDIT ITEM FORMS
    // =========================
    document.querySelectorAll('.editItemForm').forEach(function (form) {
        const description = form.querySelector('input[name="description"]');

        const categoryExisting = form.querySelector('.edit-category-existing');
        const categoryNew = form.querySelector('.edit-category-new');
        const categoryFinal = form.querySelector('.edit-category-final');

        const locationExisting = form.querySelector('.edit-location-existing');
        const locationNew = form.querySelector('.edit-location-new');
        const locationFinal = form.querySelector('.edit-location-final');

        const quantity = form.querySelector('input[name="quantity"]');
        const available = form.querySelector('input[name="available_quantity"]');
        const image = form.querySelector('input[name="image"]');

        const errorDescription = form.querySelector('.error-description');
        const errorCategory = form.querySelector('.error-category');
        const errorLocation = form.querySelector('.error-location');
        const errorQuantity = form.querySelector('.error-quantity');
        const errorAvailable = form.querySelector('.error-available');
        const errorImage = form.querySelector('.error-image');

        function validateDescription(showError = true) {
            const value = description.value.trim();

            if (description.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(description, errorDescription, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput: description,
                errorElement: errorDescription,
                emptyMessage: 'La descripción es obligatoria.',
                invalidMessage: 'La descripción contiene caracteres no permitidos.',
                minMessage: 'La descripción debe tener al menos 5 caracteres.',
                maxMessage: 'La descripción no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: TEXT_REGEX,
                showError
            });
        }

        function validateCategory(showError = true) {
            const value = getResolvedValue(categoryExisting, categoryNew, categoryFinal);
            const visibleInput = categoryNew.value.trim() ? categoryNew : categoryExisting;

            if (categoryNew.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(categoryNew, errorCategory, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput,
                errorElement: errorCategory,
                emptyMessage: 'La categoría es obligatoria.',
                invalidMessage: 'La categoría contiene caracteres no permitidos.',
                minMessage: 'La categoría debe tener al menos 5 caracteres.',
                maxMessage: 'La categoría no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: CATEGORY_REGEX,
                showError
            });
        }

        function validateLocation(showError = true) {
            const value = getResolvedValue(locationExisting, locationNew, locationFinal);
            const visibleInput = locationNew.value.trim() ? locationNew : locationExisting;

            if (locationNew.value.length > MAX_TEXT_LENGTH) {
                if (showError) {
                    setError(locationNew, errorLocation, 'Solo puedes escribir hasta 100 caracteres.');
                }
                return false;
            }

            return validateResolvedTextValue({
                value,
                visibleInput,
                errorElement: errorLocation,
                emptyMessage: 'La ubicación es obligatoria.',
                invalidMessage: 'La ubicación contiene caracteres no permitidos.',
                minMessage: 'La ubicación debe tener al menos 5 caracteres.',
                maxMessage: 'La ubicación no puede exceder 100 caracteres.',
                min: MIN_TEXT_LENGTH,
                max: MAX_TEXT_LENGTH,
                regex: LOCATION_REGEX,
                showError
            });
        }

        function validateQuantity(showError = true) {
            return validateQuantityField(quantity, errorQuantity, showError);
        }

        function validateAvailable(showError = true) {
            return validateAvailableField(available, quantity, errorAvailable, showError);
        }

        function validateEditImage(showError = true) {
            return validateImageField(image, errorImage, false, showError);
        }

        description.addEventListener('input', function () {
            if (description.value.length > MAX_TEXT_LENGTH) {
                setError(description, errorDescription, 'Solo puedes escribir hasta 100 caracteres.');
                return;
            }

            validateDescription(true);
        });

        categoryExisting.addEventListener('change', function () {
            if (categoryExisting.value) {
                categoryNew.value = '';
            }
            validateCategory(true);
        });

        categoryNew.addEventListener('input', function () {
            if (categoryNew.value.trim()) {
                categoryExisting.value = '';
            }

            if (categoryNew.value.length > MAX_TEXT_LENGTH) {
                setError(categoryNew, errorCategory, 'Solo puedes escribir hasta 100 caracteres.');
                return;
            }

            validateCategory(true);
        });

        locationExisting.addEventListener('change', function () {
            if (locationExisting.value) {
                locationNew.value = '';
            }
            validateLocation(true);
        });

        locationNew.addEventListener('input', function () {
            if (locationNew.value.trim()) {
                locationExisting.value = '';
            }

            if (locationNew.value.length > MAX_TEXT_LENGTH) {
                setError(locationNew, errorLocation, 'Solo puedes escribir hasta 100 caracteres.');
                return;
            }

            validateLocation(true);
        });

        quantity.addEventListener('input', function () {
            validateQuantity(true);
            validateAvailable(true);
        });

        available.addEventListener('input', function () {
            validateAvailable(true);
        });

        image.addEventListener('change', function () {
            validateEditImage(true);
        });

        form.addEventListener('submit', function (e) {
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
    });

    setupDeleteConfirmation();
    setupEditConfirmation();

    if (sessionStorage.getItem('inventory_add_toast') === '1') {
        sessionStorage.removeItem('inventory_add_toast');
        showToast(addToastElement);
    }

    handleStoredToasts();
});
