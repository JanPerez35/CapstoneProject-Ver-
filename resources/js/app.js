import './bootstrap';
import * as bootstrap from 'bootstrap';

//This is backend stuff, we might have to reference this -Jan
document.addEventListener('DOMContentLoaded', () => {
    const borrowModal = document.getElementById('borrowModal');
    const borrowModalText = document.getElementById('borrowModalText');
    const borrowModalImage = document.getElementById('borrowModalImage');
    const borrowModalStock = document.getElementById('borrowModalStock');
    const borrowQuantity = document.getElementById('borrowQuantity');
    const confirmAddToCart = document.getElementById('confirmAddToCart');
    const cartCount = document.getElementById('cartCount');
    const cartItemsContainer = document.getElementById('cartItemsContainer');
    const cartToastEl = document.getElementById('cartToast');
    const cartToastMessage = document.getElementById('cartToastMessage');

    if (
        !borrowModal ||
        !borrowModalText ||
        !borrowModalImage ||
        !borrowModalStock ||
        !borrowQuantity ||
        !confirmAddToCart ||
        !cartCount ||
        !cartItemsContainer ||
        !cartToastEl ||
        !cartToastMessage
    ) {
        return;
    }

    let currentItem = {
        name: '',
        stock: 0,
        image: ''
    };

    let cart = [];

    function updateCartBadge() {
        const count = cart.length;

        cartCount.textContent = count;

        if (count >= 1) {
            cartCount.classList.remove('d-none');
        } else {
            cartCount.classList.add('d-none');
        }
    }

    const toast = bootstrap.Toast.getOrCreateInstance(cartToastEl);

    document.querySelectorAll('.open-borrow-modal').forEach((button) => {
        button.addEventListener('click', () => {
            currentItem.name = button.dataset.itemName || '';
            currentItem.stock = parseInt(button.dataset.itemStock || '1', 10);
            currentItem.image = button.dataset.itemImage || '';

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

        cart.push({
            name: currentItem.name,
            quantity: quantity,
            stock: currentItem.stock,
            image: currentItem.image
        });

        updateCartBadge();
        renderCart();

        cartToastMessage.textContent = `${currentItem.name} agregado al carrito`;
        toast.show();

        const modalInstance = bootstrap.Modal.getOrCreateInstance(borrowModal);
        modalInstance.hide();
    });

    function renderCart() {
        if (cart.length === 0) {
            cartItemsContainer.innerHTML = `<p class="text-muted mb-0">Tu carrito está vacío.</p>`;
            return;
        }

        cartItemsContainer.innerHTML = cart.map((item, index) => `
            <div class="card border-0 shadow-sm rounded-4 mb-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <img
                        src="${item.image}"
                        alt="${item.name}"
                        style="width: 72px; height: 72px; object-fit: cover; border-radius: 12px;"
                    >

                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${item.name}</h6>
                        <p class="text-muted mb-1">Cantidad: ${item.quantity}</p>
                        <p class="text-muted mb-0">Disponible: ${item.stock}</p>
                    </div>

                    <button class="btn btn-outline-danger btn-sm remove-cart-item" data-index="${index}">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            </div>
        `).join('');

        document.querySelectorAll('.remove-cart-item').forEach((button) => {
            button.addEventListener('click', () => {
                const index = parseInt(button.dataset.index, 10);
                cart.splice(index, 1);
                updateCartBadge();
                renderCart();
            });
        });
    }

    updateCartBadge();
});
