import './bootstrap';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    const borrowModal = document.getElementById('borrowModal');
    const borrowModalText = document.getElementById('borrowModalText');
    const borrowModalImage = document.getElementById('borrowModalImage');
    const borrowModalStock = document.getElementById('borrowModalStock');
    const borrowQuantity = document.getElementById('borrowQuantity');
    const confirmAddToCart = document.getElementById('confirmAddToCart');

    const loanDetailsSection = document.getElementById('loanDetailsSection');
    const emptyCartSection = document.getElementById('emptyCartSection');
    const cartFooterActions = document.getElementById('cartFooterActions');

    const cartModal = document.getElementById('cartModal');
    const cartCount = document.getElementById('cartCount');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartToastEl = document.getElementById('cartToast');
    const cartToastMessage = document.getElementById('cartToastMessage');

    const cartItemCountLabel = document.getElementById('cartItemCountLabel');
    const submitItemCount = document.getElementById('submitItemCount');

    const specialCaseCheck = document.getElementById('specialCaseCheck');
    const specialCaseFields = document.getElementById('specialCaseFields');

    const submitLoanRequest = document.getElementById('submitLoanRequest');
    const submitToastEl = document.getElementById('submitToast');

    const loanFullName = document.getElementById('loanFullName');
    const loanPickupDate = document.getElementById('loanPickupDate');
    const pickupTimeBlock = document.getElementById('pickupTimeBlock');

    const returnDate = document.getElementById('returnDate');
    const specialReason = document.getElementById('specialReason');

    const hasBorrowModal =
        borrowModal &&
        borrowModalText &&
        borrowModalImage &&
        borrowModalStock &&
        borrowQuantity &&
        confirmAddToCart;

    const hasCartUI =
        cartCount &&
        cartItemsContainer &&
        cartToastEl &&
        cartToastMessage;

    if (hasCartUI) {
        let currentItem = {
            name: '',
            stock: 0,
            image: '',
            location: 'Sala de Equipo A'
        };

        let cart = [];

        const cartToast = bootstrap.Toast.getOrCreateInstance(cartToastEl);

        function toLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function setMinDates() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            const minDate = toLocalDateString(tomorrow);

            if (loanPickupDate) {
                loanPickupDate.min = minDate;
            }

            if (returnDate) {
                returnDate.min = minDate;
            }
        }

        function isBlockedPickupDay(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            const day = date.getDay();
            return day === 0 || day === 5 || day === 6;
        }

        function getTodayAtMidnight() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        }

        function getTotalUnits() {
            return cart.reduce((sum, item) => sum + item.quantity, 0);
        }

        function updateCartBadge() {
            const totalUnits = getTotalUnits();

            cartCount.textContent = totalUnits;

            if (totalUnits >= 1) {
                cartCount.classList.remove('d-none');
            } else {
                cartCount.classList.add('d-none');
            }
        }

        function updateCartLabels() {
            const totalUnits = getTotalUnits();

            if (cartItemCountLabel) {
                cartItemCountLabel.textContent = totalUnits;
            }

            if (submitItemCount) {
                submitItemCount.textContent = totalUnits;
            }
        }

        function updateCartUI() {
            updateCartBadge();
            updateCartLabels();
            renderCart();

            const hasItems = cart.length > 0;

            if (loanDetailsSection && emptyCartSection) {
                if (hasItems) {
                    loanDetailsSection.classList.remove('d-none');
                    emptyCartSection.classList.add('d-none');
                } else {
                    loanDetailsSection.classList.add('d-none');
                    emptyCartSection.classList.remove('d-none');
                }
            }

            if (cartFooterActions) {
                if (hasItems) {
                    cartFooterActions.classList.remove('d-none');
                } else {
                    cartFooterActions.classList.add('d-none');
                }
            }
        }

        function markInvalid(field) {
            if (field) field.classList.add('is-invalid');
        }

        function clearInvalid(field) {
            if (field) field.classList.remove('is-invalid');
        }

        function clearLoanValidation() {
            [
                loanFullName,
                loanPickupDate,
                pickupTimeBlock,
                returnDate,
                specialReason
            ].forEach(clearInvalid);
        }

        function resetLoanForm() {
            if (loanFullName) loanFullName.value = '';
            if (loanPickupDate) loanPickupDate.value = '';
            if (pickupTimeBlock) pickupTimeBlock.value = '';
            if (returnDate) returnDate.value = '';
            if (specialReason) specialReason.value = '';

            clearLoanValidation();

            if (specialCaseCheck) {
                specialCaseCheck.checked = false;
            }

            if (specialCaseFields) {
                specialCaseFields.classList.add('d-none');
            }
        }

        function validateLoanForm() {
            clearLoanValidation();

            let hasError = false;

            const name = loanFullName?.value.trim();
            const pickupDateValue = loanPickupDate?.value;
            const pickupTime = pickupTimeBlock?.value;
            const isSpecialCase = specialCaseCheck?.checked;
            const returnVal = returnDate?.value;
            const reason = specialReason?.value.trim();

            if (!name || name.length < 5 || name.length > 80) {
                markInvalid(loanFullName);
                hasError = true;
            }

            if (!pickupDateValue) {
                markInvalid(loanPickupDate);
                hasError = true;
            }

            if (!pickupTime) {
                markInvalid(pickupTimeBlock);
                hasError = true;
            }

            if (pickupDateValue) {
                const today = getTodayAtMidnight();
                const pickupDateObj = new Date(`${pickupDateValue}T00:00:00`);

                if (pickupDateObj <= today || isBlockedPickupDay(pickupDateValue)) {
                    markInvalid(loanPickupDate);
                    hasError = true;
                }
            }

            if (isSpecialCase) {
                if (!returnVal) {
                    markInvalid(returnDate);
                    hasError = true;
                }

                if (returnVal) {
                    const today = getTodayAtMidnight();
                    const returnDateObj = new Date(`${returnVal}T00:00:00`);

                    if (returnDateObj <= today) {
                        markInvalid(returnDate);
                        hasError = true;
                    }
                }

                if (!reason || reason.length > 500) {
                    markInvalid(specialReason);
                    hasError = true;
                }
            }

            return !hasError;
        }

        function attachCartActionEvents() {
            document.querySelectorAll('.remove-cart-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = parseInt(button.dataset.index, 10);
                    cart.splice(index, 1);
                    updateCartUI();
                });
            });

            document.querySelectorAll('.increase-cart-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = parseInt(button.dataset.index, 10);

                    if (cart[index].quantity < cart[index].stock) {
                        cart[index].quantity += 1;
                        updateCartUI();
                    }
                });
            });

            document.querySelectorAll('.decrease-cart-item').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = parseInt(button.dataset.index, 10);

                    if (cart[index].quantity > 1) {
                        cart[index].quantity -= 1;
                        updateCartUI();
                    }
                });
            });
        }

        function renderCart() {
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '';
                return;
            }

            cartItemsContainer.innerHTML = cart.map((item, index) => `
            <div class="row g-0 align-items-center px-3 py-3 border-bottom">
                <div class="col-6">
                    <div class="d-flex align-items-center gap-3">
                        <img
                            src="${item.image}"
                            alt="${item.name}"
                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 12px;"
                        >
                        <div>
                            <h6 class="fw-bold mb-1">${item.name}</h6>
                            <p class="text-muted mb-0">${item.location}</p>
                        </div>
                    </div>
                </div>

                <div class="col-3">
                    <div class="d-flex justify-content-center align-items-center gap-3">
                        <button class="btn btn-outline-secondary btn-sm decrease-cart-item" data-index="${index}">
                            -
                        </button>

                        <span class="fw-bold fs-6">${item.quantity}</span>

                        <button class="btn btn-outline-secondary btn-sm increase-cart-item" data-index="${index}">
                            +
                        </button>
                    </div>
                </div>

                <div class="col-3 text-center">
                    <button class="btn btn-link text-danger p-0 remove-cart-item" data-index="${index}">
                        <i class="bi bi-trash-fill fs-5"></i>
                    </button>
                </div>
            </div>
        `).join('');

            attachCartActionEvents();
        }

        if (hasBorrowModal) {
            document.querySelectorAll('.open-borrow-modal').forEach((button) => {
                button.addEventListener('click', () => {
                    currentItem.name = button.dataset.itemName || '';
                    currentItem.stock = parseInt(button.dataset.itemStock || '1', 10);
                    currentItem.image = button.dataset.itemImage || '';
                    currentItem.location = button.dataset.itemLocation || 'Sala de Equipo A';

                    borrowModalText.textContent = `Selecciona la cantidad de ${currentItem.name} que deseas`;
                    borrowModalImage.src = currentItem.image;
                    borrowModalImage.alt = currentItem.name;
                    borrowModalStock.textContent = currentItem.stock;

                    borrowQuantity.value = 1;
                    borrowQuantity.min = 1;
                    borrowQuantity.max = currentItem.stock;
                });
            });

            borrowQuantity.addEventListener('input', () => {
                let value = parseInt(borrowQuantity.value, 10);

                if (isNaN(value) || value < 1) {
                    value = 1;
                }

                if (value > currentItem.stock) {
                    value = currentItem.stock;
                }

                borrowQuantity.value = value;
            });

            confirmAddToCart.addEventListener('click', () => {
                const quantity = parseInt(borrowQuantity.value, 10);

                if (isNaN(quantity) || quantity < 1 || quantity > currentItem.stock) {
                    borrowQuantity.value = 1;
                    return;
                }

                const existingItemIndex = cart.findIndex((item) => item.name === currentItem.name);

                if (existingItemIndex !== -1) {
                    const newQuantity = cart[existingItemIndex].quantity + quantity;
                    cart[existingItemIndex].quantity = Math.min(newQuantity, cart[existingItemIndex].stock);
                } else {
                    cart.push({
                        name: currentItem.name,
                        quantity: quantity,
                        stock: currentItem.stock,
                        image: currentItem.image,
                        location: currentItem.location
                    });
                }

                updateCartUI();

                cartToastMessage.textContent = `${currentItem.name} agregado al carrito`;
                cartToast.show();

                const borrowModalInstance = bootstrap.Modal.getOrCreateInstance(borrowModal);
                borrowModalInstance.hide();
            });
        }

        if (specialCaseCheck && specialCaseFields) {
            specialCaseCheck.addEventListener('change', () => {
                if (specialCaseCheck.checked) {
                    specialCaseFields.classList.remove('d-none');
                } else {
                    specialCaseFields.classList.add('d-none');
                    clearInvalid(returnDate);
                    clearInvalid(specialReason);
                }
            });
        }

        if (loanPickupDate) {
            loanPickupDate.addEventListener('change', () => {
                clearInvalid(loanPickupDate);

                if (loanPickupDate.value && isBlockedPickupDay(loanPickupDate.value)) {
                    markInvalid(loanPickupDate);
                }
            });
        }

        if (returnDate) {
            returnDate.addEventListener('change', () => {
                clearInvalid(returnDate);

                if (!returnDate.value) return;

                const selected = new Date(`${returnDate.value}T00:00:00`);
                const today = getTodayAtMidnight();

                if (selected <= today) {
                    markInvalid(returnDate);
                }
            });
        }

        if (loanFullName) {
            loanFullName.addEventListener('input', () => {
                loanFullName.value = loanFullName.value.slice(0, 80);
                clearInvalid(loanFullName);
            });
        }

        if (pickupTimeBlock) {
            pickupTimeBlock.addEventListener('change', () => {
                clearInvalid(pickupTimeBlock);
            });
        }

        if (loanPickupDate) {
            loanPickupDate.addEventListener('input', () => {
                clearInvalid(loanPickupDate);
            });
        }

        if (returnDate) {
            returnDate.addEventListener('input', () => {
                clearInvalid(returnDate);
            });
        }

        if (specialReason) {
            specialReason.addEventListener('input', () => {
                specialReason.value = specialReason.value.slice(0, 500);
                clearInvalid(specialReason);
            });
        }

        if (submitLoanRequest && submitToastEl && cartModal) {
            const submitToast = bootstrap.Toast.getOrCreateInstance(submitToastEl);

            submitLoanRequest.addEventListener('click', () => {
                const isValid = validateLoanForm();

                if (!isValid) {
                    return;
                }

                if (cart.length === 0) {
                    return;
                }

                const cartModalInstance = bootstrap.Modal.getOrCreateInstance(cartModal);
                cartModalInstance.hide();

                cart = [];
                updateCartUI();
                resetLoanForm();

                setTimeout(() => {
                    submitToast.show();
                }, 250);
            });
        }

        setMinDates();
        updateCartUI();
    }

    const deletePostModal = document.getElementById('deletePostModal');
    const deletePostModalText = document.getElementById('deletePostModalText');
    const confirmDeletePost = document.getElementById('confirmDeletePost');

    let postCardToDelete = null;

    if (deletePostModal && confirmDeletePost) {
        document.querySelectorAll('.open-delete-post-modal').forEach((button) => {
            button.addEventListener('click', () => {
                postCardToDelete = button.closest('.post-card-wrapper');

                const postTitle = button.dataset.postTitle || 'esta publicación';

                if (deletePostModalText) {
                    deletePostModalText.textContent = `Vas a borrar "${postTitle}".`;
                }

                const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
                modalInstance.show();
            });
        });

        confirmDeletePost.addEventListener('click', () => {
            if (postCardToDelete) {
                postCardToDelete.remove();
                postCardToDelete = null;
            }

            const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
            modalInstance.hide();
        });
    }
    // Post validation
    const createPostForm = document.getElementById('createPostForm');
    const publishBtn = document.getElementById('publishBtn');
    const postTitle = document.getElementById('postTitle');
    const postTitleError = document.getElementById('postTitleError');
    const postDescription = document.getElementById('postDescription');
    const postDescriptionError = document.getElementById('postDescriptionError');
    const postPrice = document.getElementById('postPrice');
    const postPriceError = document.getElementById('postPriceError');
    const postCategory = document.getElementById('postCategory');
    const postCondition = document.getElementById('postCondition');
    const postImage = document.getElementById('postImage');
    const imageError = document.getElementById('imageError');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    const allowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;
    const priceRegex = /^(?:0|[1-9]\d*)(?:\.\d{1,2})?$/;
    const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
    const MAX_IMAGES = 3;
    const MIN_IMAGES = 1;

    let selectedPostImages = [];

    function setFieldError(field, errorElement, message) {
        if (!field) return;
        field.classList.add('is-invalid');
        if (errorElement) {
            errorElement.textContent = message;
        }
    }

    function clearFieldError(field, errorElement) {
        if (!field) return;
        field.classList.remove('is-invalid');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    function showImageError(message) {
        if (!imageError) return;
        imageError.textContent = message;
        imageError.classList.remove('d-none');
    }

    function clearImageError() {
        if (!imageError) return;
        imageError.textContent = '';
        imageError.classList.add('d-none');
    }

    function renderImagePreviews(files) {
        if (!imagePreviewContainer) return;

        imagePreviewContainer.innerHTML = '';

        files.forEach((file, index) => {
            const url = URL.createObjectURL(file);

            imagePreviewContainer.insertAdjacentHTML('beforeend', `
    <div class="col-md-4">
        <div class="border rounded-4 overflow-hidden bg-light position-relative">

            <button
                type="button"
                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle d-flex align-items-center justify-content-center remove-preview-image"
                style="width:32px; height:32px;"
                data-index="${index}"
            >
                <i class="bi bi-x"></i>
            </button>

            <img
                src="${url}"
                alt="Preview"
                class="w-100"
                style="height: 180px; object-fit: cover;"
            >

            <div class="p-2 small text-muted text-truncate">
                ${file.name}
            </div>
        </div>
    </div>
`);
        });

        document.querySelectorAll('.remove-preview-image').forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number(button.dataset.index);

                selectedPostImages.splice(index, 1);

                renderImagePreviews(selectedPostImages);
                clearImageError();
                if (postImage) {
                    postImage.value = '';
                }
                updateCreatePostDirtyState();
                updatePublishButtonState();
            });
        });
    }

    async function isRealJpeg(file) {
        if (!file || !file.type.includes('jpeg')) {
            return false;
        }

        const buffer = await file.slice(0, 3).arrayBuffer();
        const bytes = new Uint8Array(buffer);

        return bytes[0] === 0xFF && bytes[1] === 0xD8 && bytes[2] === 0xFF;
    }

    function validateTitle(showError = true) {
        if (!postTitle || !postTitleError) return true;

        const value = postTitle.value.trim();

        if (showError) {
            clearFieldError(postTitle, postTitleError);
        }

        if (!value) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título es obligatorio.');
            return false;
        }

        if (value.length < 5) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título debe tener al menos 5 caracteres.');
            return false;
        }

        if (value.length > 100) {
            if (showError) setFieldError(postTitle, postTitleError, 'El título no puede exceder 100 caracteres.');
            return false;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) setFieldError(postTitle, postTitleError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            return false;
        }

        return true;
    }

    function validateDescription(showError = true) {
        if (!postDescription || !postDescriptionError) return true;

        const value = postDescription.value.trim();

        if (showError) {
            clearFieldError(postDescription, postDescriptionError);
        }

        if (!value) {
            return true;
        }

        if (value.length < 10) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'La descripción debe tener al menos 10 caracteres.');
            return false;
        }

        if (value.length > 1000) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'La descripción no puede exceder 1000 caracteres.');
            return false;
        }

        if (!allowedTextRegex.test(value)) {
            if (showError) setFieldError(postDescription, postDescriptionError, 'Solo se permiten letras, números, espacios, punto, coma y guion.');
            return false;
        }

        return true;
    }

    function validatePrice(showError = true) {
        if (!postPrice || !postPriceError) return true;

        const value = postPrice.value.trim();

        if (showError) {
            clearFieldError(postPrice, postPriceError);
        }

        if (!value) {
            if (showError) setFieldError(postPrice, postPriceError, 'El precio es obligatorio.');
            return false;
        }

        if (/[eE+\-]/.test(value)) {
            if (showError) setFieldError(postPrice, postPriceError, 'No se permite usar e, E, + ni - en el precio.');
            return false;
        }

        if (!priceRegex.test(value)) {
            if (showError) setFieldError(postPrice, postPriceError, 'Ingresa un precio válido usando solo números y hasta 2 decimales.');
            return false;
        }

        return true;
    }

    function validateSelect(field, showError = true) {
        if (!field) return true;

        const isValid = !!field.value;

        if (showError) {
            if (!isValid) {
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        }

        return isValid;
    }

    function validateImages(showError = true) {
        if (showError) {
            clearImageError();
        }

        if (selectedPostImages.length < MIN_IMAGES) {
            if (showError) showImageError('Debes subir al menos 1 imagen.');
            return false;
        }

        if (selectedPostImages.length > MAX_IMAGES) {
            if (showError) showImageError('Solo puedes subir un máximo de 3 imágenes.');
            return false;
        }

        return true;
    }

    function updatePublishButtonState() {
        if (!publishBtn) return;

        const isReady =
            validateTitle(false) &&
            validatePrice(false) &&
            validateSelect(postCategory, false) &&
            validateSelect(postCondition, false) &&
            validateImages(false) &&
            validateDescription(false);

        publishBtn.disabled = !isReady;
    }

    if (postTitle) {
        postTitle.addEventListener('input', () => {
            postTitle.value = postTitle.value.slice(0, 100);
            validateTitle(true);
            updatePublishButtonState();
        });
    }

    if (postDescription) {
        postDescription.addEventListener('input', () => {
            postDescription.value = postDescription.value.slice(0, 1000);
            validateDescription(true);
            updatePublishButtonState();
        });
    }

    if (postPrice) {
        postPrice.addEventListener('keydown', (e) => {
            if (['e', 'E', '+', '-'].includes(e.key)) {
                e.preventDefault();
            }
        });

        postPrice.addEventListener('input', () => {
            postPrice.value = postPrice.value.replace(/[^0-9.]/g, '');

            const firstDotIndex = postPrice.value.indexOf('.');
            if (firstDotIndex !== -1) {
                const integerPart = postPrice.value.slice(0, firstDotIndex + 1);
                const decimalPart = postPrice.value
                    .slice(firstDotIndex + 1)
                    .replace(/\./g, '')
                    .slice(0, 2);

                postPrice.value = integerPart + decimalPart;
            }

            validatePrice(true);
            updatePublishButtonState();
        });
    }

    if (postCategory) {
        postCategory.addEventListener('change', () => {
            validateSelect(postCategory, true);
            updatePublishButtonState();
        });
    }

    if (postCondition) {
        postCondition.addEventListener('change', () => {
            validateSelect(postCondition, true);
            updatePublishButtonState();
        });
    }

    if (postImage) {
        postImage.addEventListener('change', async () => {
            const newFiles = Array.from(postImage.files || []);
            if (newFiles.length === 0) return;

            clearImageError();

            const allowedSlots = MAX_IMAGES - selectedPostImages.length;

            if (allowedSlots <= 0) {
                showImageError('Solo puedes subir un máximo de 3 imágenes.');
                postImage.value = '';
                updatePublishButtonState();
                return;
            }

            if (newFiles.length > allowedSlots) {
                showImageError(`Solo puedes agregar ${allowedSlots} imagen${allowedSlots === 1 ? '' : 'es'} más.`);
                postImage.value = '';
                updatePublishButtonState();
                return;
            }

            for (const file of newFiles) {
                if (file.size > MAX_IMAGE_SIZE) {
                    showImageError(`"${file.name}" excede el tamaño máximo de 2MB.`);
                    postImage.value = '';
                    updatePublishButtonState();
                    return;
                }

                if (!(await isRealJpeg(file))) {
                    showImageError(`"${file.name}" no es un JPEG válido.`);
                    postImage.value = '';
                    updatePublishButtonState();
                    return;
                }
            }

            selectedPostImages = [...selectedPostImages, ...newFiles];
            renderImagePreviews(selectedPostImages);

            postImage.value = '';
            updateCreatePostDirtyState();
            updatePublishButtonState();
        });
    }

    if (publishBtn && createPostForm) {
        publishBtn.addEventListener('click', async () => {
            const isTitleValid = validateTitle(true);
            const isDescriptionValid = validateDescription(true);
            const isPriceValid = validatePrice(true);
            const isCategoryValid = validateSelect(postCategory, true);
            const isConditionValid = validateSelect(postCondition, true);
            const areImagesValid = validateImages(true);

            if (
                !isTitleValid ||
                !isDescriptionValid ||
                !isPriceValid ||
                !isCategoryValid ||
                !isConditionValid ||
                !areImagesValid
            ) {
                updatePublishButtonState();
                return;
            }

            createPostForm.submit();
        });
    }

    updatePublishButtonState();

    const createPostModal = document.getElementById('createPostModal');
    const cancelCreatePostBtn = document.getElementById('cancelCreatePostBtn');
    const cancelConfirmModal = document.getElementById('cancelConfirmModal');
    const confirmCancelCreatePost = document.getElementById('confirmCancelCreatePost');

    let isCreatePostDirty = false;
    let allowCreatePostClose = false;

    function resetCreatePostValidation() {
        clearFieldError(postTitle, postTitleError);
        clearFieldError(postDescription, postDescriptionError);
        clearFieldError(postPrice, postPriceError);

        if (postCategory) postCategory.classList.remove('is-invalid');
        if (postCondition) postCondition.classList.remove('is-invalid');

        clearImageError();
    }

    function resetCreatePostForm() {
        if (createPostForm) {
            createPostForm.reset();
        }

        selectedPostImages = [];
        isCreatePostDirty = false;
        allowCreatePostClose = false;

        if (imagePreviewContainer) {
            imagePreviewContainer.innerHTML = '';
        }

        if (postImage) {
            postImage.value = '';
        }

        resetCreatePostValidation();
        updatePublishButtonState();
    }

    function updateCreatePostDirtyState() {
        const hasText =
            (postTitle?.value.trim() || '') !== '' ||
            (postDescription?.value.trim() || '') !== '' ||
            (postPrice?.value.trim() || '') !== '';

        const hasSelects =
            !!postCategory?.value ||
            !!postCondition?.value;

        const hasImages = selectedPostImages.length > 0;

        isCreatePostDirty = hasText || hasSelects || hasImages;
    }

// Track changes
    [postTitle, postDescription, postPrice, postCategory, postCondition].forEach((field) => {
        if (!field) return;

        field.addEventListener('input', updateCreatePostDirtyState);
        field.addEventListener('change', updateCreatePostDirtyState);
    });

// Modal open / close behavior
    if (createPostModal) {
        createPostModal.addEventListener('show.bs.modal', () => {
            resetCreatePostValidation();
            updatePublishButtonState();
        });

        createPostModal.addEventListener('hide.bs.modal', (event) => {
            if (allowCreatePostClose) return;

            updateCreatePostDirtyState();

            if (!isCreatePostDirty) {
                resetCreatePostForm();
                return;
            }

            event.preventDefault();

            if (cancelConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
                confirmModal.show();
            }
        });

        createPostModal.addEventListener('hidden.bs.modal', () => {
            if (allowCreatePostClose) {
                resetCreatePostForm();
                allowCreatePostClose = false;
            }
        });
    }

// Cancel button behavior
    if (cancelCreatePostBtn && createPostModal) {
        cancelCreatePostBtn.addEventListener('click', () => {
            updateCreatePostDirtyState();

            const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);

            if (!isCreatePostDirty) {
                allowCreatePostClose = true;
                modal.hide();
                return;
            }

            if (cancelConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
                confirmModal.show();
            }
        });
    }

// Confirm cancel
    if (confirmCancelCreatePost && createPostModal && cancelConfirmModal) {
        confirmCancelCreatePost.addEventListener('click', () => {
            allowCreatePostClose = true;

            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelConfirmModal);
            confirmModal.hide();

            const modal = bootstrap.Modal.getOrCreateInstance(createPostModal);
            modal.hide();
        });
    }

    //Rating Functionality
    const ratingContainer = document.getElementById('sellerRatingStars');
    const ratingInput = document.getElementById('sellerRatingValue');
    const ratingText = document.getElementById('sellerRatingText');

    if (ratingContainer && ratingInput && ratingText) {
        const stars = ratingContainer.querySelectorAll('.rating-star');

        const ratingLabels = {
            0: 'Selecciona una calificación',
            1: 'Malo',
            2: 'Regular',
            3: 'Bueno',
            4: 'Muy Bueno',
            5: 'Excelente'
        };

        function paintStars(value) {
            stars.forEach((star) => {
                const starValue = Number(star.dataset.value);

                if (starValue <= value) {
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill');
                } else {
                    star.classList.remove('bi-star-fill');
                    star.classList.add('bi-star');
                }
            });

            ratingText.textContent = ratingLabels[value] || ratingLabels[0];
        }

        stars.forEach((star) => {
            star.addEventListener('mouseenter', function () {
                const value = Number(this.dataset.value);
                paintStars(value);
            });

            star.addEventListener('click', function () {
                const value = Number(this.dataset.value);
                ratingInput.value = value;
                paintStars(value);
            });

            star.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const value = Number(this.dataset.value);
                    ratingInput.value = value;
                    paintStars(value);
                }
            });
        });

        ratingContainer.addEventListener('mouseleave', () => {
            paintStars(Number(ratingInput.value));
        });

        paintStars(Number(ratingInput.value));
    }
});
