import './bootstrap';
import * as bootstrap from 'bootstrap';

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

    if (!hasCartUI) {
        return;
    }

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

    function resetLoanForm() {
        if (loanFullName) loanFullName.value = '';
        if (loanPickupDate) loanPickupDate.value = '';
        if (pickupTimeBlock) pickupTimeBlock.value = '';
        if (returnDate) returnDate.value = '';
        if (specialReason) specialReason.value = '';

        if (specialCaseCheck) {
            specialCaseCheck.checked = false;
        }

        if (specialCaseFields) {
            specialCaseFields.classList.add('d-none');
        }
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
            }
        });
    }

    if (loanPickupDate) {
        loanPickupDate.addEventListener('change', () => {
            if (loanPickupDate.value && isBlockedPickupDay(loanPickupDate.value)) {
                alert('No se permiten recogidas viernes, sábado ni domingo.');
                loanPickupDate.value = '';
            }
        });
    }

    if (returnDate) {
        returnDate.addEventListener('change', () => {
            if (!returnDate.value) return;

            const selected = new Date(`${returnDate.value}T00:00:00`);
            const today = getTodayAtMidnight();

            if (selected <= today) {
                alert('La fecha de devolución propuesta debe ser futura.');
                returnDate.value = '';
            }
        });
    }

    if (loanFullName) {
        loanFullName.addEventListener('input', () => {
            loanFullName.value = loanFullName.value.slice(0, 80);
        });
    }

    if (specialReason) {
        specialReason.addEventListener('input', () => {
            specialReason.value = specialReason.value.slice(0, 500);
        });
    }

    if (submitLoanRequest && submitToastEl && cartModal) {
        const submitToast = bootstrap.Toast.getOrCreateInstance(submitToastEl);

        submitLoanRequest.addEventListener('click', () => {
            const name = loanFullName?.value.trim();
            const pickupDateValue = loanPickupDate?.value;
            const pickupTime = pickupTimeBlock?.value;

            if (!name || !pickupDateValue || !pickupTime) {
                alert('Por favor completa todos los campos obligatorios.');
                return;
            }

            if (name.length < 5 || name.length > 80) {
                alert('El nombre completo debe tener entre 5 y 80 caracteres.');
                return;
            }

            const today = getTodayAtMidnight();
            const pickupDateObj = new Date(`${pickupDateValue}T00:00:00`);

            if (pickupDateObj <= today) {
                alert('La fecha de recogida debe ser futura.');
                return;
            }

            if (isBlockedPickupDay(pickupDateValue)) {
                alert('La fecha de recogida no puede ser viernes, sábado ni domingo.');
                return;
            }

            if (specialCaseCheck?.checked) {
                const returnVal = returnDate?.value;
                const reason = specialReason?.value.trim();

                if (!returnVal || !reason) {
                    alert('Debes completar los campos del caso especial.');
                    return;
                }

                const returnDateObj = new Date(`${returnVal}T00:00:00`);

                if (returnDateObj <= today) {
                    alert('La fecha de devolución propuesta debe ser futura.');
                    return;
                }

                if (reason.length > 500) {
                    alert('La razón del caso especial no puede exceder 500 caracteres.');
                    return;
                }
            }

            if (cart.length === 0) {
                alert('Tu carrito está vacío.');
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
});
