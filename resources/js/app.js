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

    const loanPickupDate = document.getElementById('loanPickupDate');
    const pickupTimeBlock = document.getElementById('pickupTimeBlock');

    const returnDate = document.getElementById('returnDate');
    const specialReason = document.getElementById('specialReason');

    const loanTermsCheck = document.getElementById('loanTermsCheck');
    const loanTermsError = document.getElementById('loanTermsError');

    const loanPickupDateError = document.getElementById('loanPickupDateError');
    const pickupTimeBlockError = document.getElementById('pickupTimeBlockError');
    const returnDateError = document.getElementById('returnDateError');
    const specialReasonError = document.getElementById('specialReasonError');

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
        const loanAllowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

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
            updateLoanSubmitButtonState();
        }


        function markInvalid(field) {
            if (field) field.classList.add('is-invalid');
        }

        function clearInvalid(field) {
            if (field) field.classList.remove('is-invalid');
        }


        function setLoanFieldError(field, errorElement, message) {
            if (!field) return;
            field.classList.add('is-invalid');
            if (errorElement) {
                errorElement.textContent = message;
            }
        }

        function clearLoanFieldError(field, errorElement) {
            if (!field) return;
            field.classList.remove('is-invalid');
            if (errorElement) {
                errorElement.textContent = '';
            }
        }

        function clearLoanValidation() {
            clearLoanFieldError(loanTermsCheck, loanTermsError);
            clearLoanFieldError(loanPickupDate, loanPickupDateError);
            clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
            clearLoanFieldError(returnDate, returnDateError);
            clearLoanFieldError(specialReason, specialReasonError);
        }

        function validateSpecialReasonField(showError = true) {
            const reason = specialReason?.value.trim() || '';

            if (showError) {
                clearLoanFieldError(specialReason, specialReasonError);
            }

            if (!specialCaseCheck?.checked) {
                return true;
            }

            if (!reason) {
                if (showError) {
                    setLoanFieldError(specialReason, specialReasonError, 'La razón del caso especial es obligatoria.');
                }
                return false;
            }

            if (reason.length > 500) {
                if (showError) {
                    setLoanFieldError(specialReason, specialReasonError, 'La razón no puede exceder 500 caracteres.');
                }
                return false;
            }

            if (!loanAllowedTextRegex.test(reason)) {
                if (showError) {
                    setLoanFieldError(
                        specialReason,
                        specialReasonError,
                        'Solo se permiten letras, números, espacios, punto, coma y guion.'
                    );
                }
                return false;
            }

            return true;
        }

        function validateLoanTermsField(showError = true) {
            const isValid = !!loanTermsCheck?.checked;

            if (showError) {
                clearLoanFieldError(loanTermsCheck, loanTermsError);
            }

            if (!isValid) {
                if (showError) {
                    setLoanFieldError(
                        loanTermsCheck,
                        loanTermsError,
                        'Debes aceptar los términos y condiciones.'
                    );
                }
                return false;
            }

            return true;
        }

        function resetLoanForm() {
            if (loanTermsCheck) loanTermsCheck.checked = false;
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
            updateLoanSubmitButtonState();
        }

        function validateLoanForm(showErrors = true) {
            if (showErrors) {
                clearLoanValidation();
            }

            let hasError = false;

            const pickupDateValue = loanPickupDate?.value;
            const pickupTime = pickupTimeBlock?.value;
            const isSpecialCase = specialCaseCheck?.checked;
            const returnVal = returnDate?.value;
            const reason = specialReason?.value.trim();

            if (!validateLoanTermsField(showErrors)) {
                hasError = true;
            }


            if (!pickupDateValue) {
                if (showErrors) {
                    setLoanFieldError(loanPickupDate, loanPickupDateError, 'La fecha de recogida es obligatoria.');
                }
                hasError = true;
            } else {
                const today = getTodayAtMidnight();
                const pickupDateObj = new Date(`${pickupDateValue}T00:00:00`);

                if (pickupDateObj <= today) {
                    if (showErrors) {
                        setLoanFieldError(loanPickupDate, loanPickupDateError, 'La fecha debe ser futura.');
                    }
                    hasError = true;
                } else if (isBlockedPickupDay(pickupDateValue)) {
                    if (showErrors) {
                        setLoanFieldError(loanPickupDate, loanPickupDateError, 'No se permiten viernes, sábados ni domingos.');
                    }
                    hasError = true;
                }
            }

            if (!pickupTime) {
                if (showErrors) {
                    setLoanFieldError(pickupTimeBlock, pickupTimeBlockError, 'La hora de recogida es obligatoria.');
                }
                hasError = true;
            }

            if (isSpecialCase) {
                if (!returnVal) {
                    if (showErrors) {
                        setLoanFieldError(returnDate, returnDateError, 'La fecha de devolución es obligatoria.');
                    }
                    hasError = true;
                } else {
                    const today = getTodayAtMidnight();
                    const returnDateObj = new Date(`${returnVal}T00:00:00`);

                    if (returnDateObj <= today) {
                        if (showErrors) {
                            setLoanFieldError(returnDate, returnDateError, 'La fecha de devolución debe ser futura.');
                        }
                        hasError = true;
                    }
                }

                if (!reason) {
                    if (showErrors) {
                        setLoanFieldError(specialReason, specialReasonError, 'La razón del caso especial es obligatoria.');
                    }
                    hasError = true;
                } else if (reason.length < 10) {
                    if (showErrors) {
                        setLoanFieldError(specialReason, specialReasonError, 'La razón debe tener al menos 10 caracteres.');
                    }
                    hasError = true;
                } else if (reason.length > 500) {
                    if (showErrors) {
                        setLoanFieldError(specialReason, specialReasonError, 'La razón no puede exceder 500 caracteres.');
                    }
                    hasError = true;
                } else if (!loanAllowedTextRegex.test(reason)) {
                    if (showErrors) {
                        setLoanFieldError(
                            specialReason,
                            specialReasonError,
                            'Solo se permiten letras, números, espacios, punto, coma y guion.'
                        );
                    }
                    hasError = true;
                }

            }

            return !hasError;
        }

        function updateLoanSubmitButtonState() {
            if (!submitLoanRequest) return;

            const formIsValid = validateLoanForm(false);
            const hasItems = cart.length > 0;

            submitLoanRequest.disabled = !(formIsValid && hasItems);
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
                    clearLoanFieldError(returnDate, returnDateError);
                    clearLoanFieldError(specialReason, specialReasonError);
                }

                updateLoanSubmitButtonState();
            });
        }

        if (loanPickupDate) {
            loanPickupDate.addEventListener('input', () => {
                clearLoanFieldError(loanPickupDate, loanPickupDateError);
                updateLoanSubmitButtonState();
            });

            loanPickupDate.addEventListener('change', () => {
                clearLoanFieldError(loanPickupDate, loanPickupDateError);

                if (loanPickupDate.value && isBlockedPickupDay(loanPickupDate.value)) {
                    setLoanFieldError(loanPickupDate, loanPickupDateError, 'No se permiten viernes, sábados ni domingos.');
                }

                updateLoanSubmitButtonState();
            });
        }

        if (returnDate) {
            returnDate.addEventListener('input', () => {
                clearLoanFieldError(returnDate, returnDateError);
                updateLoanSubmitButtonState();
            });

            returnDate.addEventListener('change', () => {
                clearLoanFieldError(returnDate, returnDateError);

                if (!returnDate.value) {
                    updateLoanSubmitButtonState();
                    return;
                }

                const selected = new Date(`${returnDate.value}T00:00:00`);
                const today = getTodayAtMidnight();

                if (selected <= today) {
                    setLoanFieldError(returnDate, returnDateError, 'La fecha de devolución debe ser futura.');
                }

                updateLoanSubmitButtonState();
            });
        }



        if (pickupTimeBlock) {
            pickupTimeBlock.addEventListener('change', () => {
                clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
                updateLoanSubmitButtonState();
            });
        }


        if (loanTermsCheck) {
            loanTermsCheck.addEventListener('change', () => {
                clearLoanFieldError(loanTermsCheck, loanTermsError);
                updateLoanSubmitButtonState();
            });
        }

        if (specialReason) {
            specialReason.addEventListener('input', () => {
                specialReason.value = specialReason.value.slice(0, 500);
                clearLoanFieldError(specialReason, specialReasonError);
                updateLoanSubmitButtonState();
            });
        }


        if (submitLoanRequest) {
            const submitToast = bootstrap.Toast.getOrCreateInstance(submitToastEl);

            submitLoanRequest.addEventListener('click',() => {
                const isValid = validateLoanForm(true);

                if (!isValid) {
                    updateLoanSubmitButtonState();
                    return;
                }

                const cartModalInstance = bootstrap.Modal.getOrCreateInstance(cartModal);
                cartModalInstance.hide();

                cart = [];
                updateCartUI();
                resetLoanForm();
                updateLoanSubmitButtonState();

                setTimeout(() => {
                    submitToast.show();
                }, 250);

            });

        }


        setMinDates();
        updateCartUI();
        updateLoanSubmitButtonState();
    }

    const deletePostModal = document.getElementById('deletePostModal');
    const deletePostModalText = document.getElementById('deletePostModalText');
    const confirmDeletePost = document.getElementById('confirmDeletePost');


    let postIdToDelete = null;


    function attachMarketplaceDeleteEvents() {
        if (!deletePostModal || !confirmDeletePost) return;


        document.querySelectorAll('.open-delete-post-modal').forEach((button) => {
            button.addEventListener('click', () => {
                postIdToDelete = Number(button.dataset.id);
                const postTitle = button.dataset.postTitle || 'esta publicación';


                if (deletePostModalText) {
                    deletePostModalText.textContent = `Vas a eliminar "${postTitle}". Esta acción no se puede deshacer.`;
                }


                const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
                modalInstance.show();
            });
        });
    }


    if (deletePostModal && confirmDeletePost) {
        confirmDeletePost.addEventListener('click', () => {
            if (postIdToDelete === null) return;


            allMarketplacePosts = allMarketplacePosts.filter(
                (post) => Number(post.id) !== postIdToDelete
            );


            saveMarketplacePosts(allMarketplacePosts);


            const modalInstance = bootstrap.Modal.getOrCreateInstance(deletePostModal);
            modalInstance.hide();


            postIdToDelete = null;
            renderMarketplace();
        });
    }
    // Marketplace search and filters includes pagination
    const marketplaceSearch = document.getElementById('marketplaceSearch');
    const marketplaceCategoryFilter = document.getElementById('marketplaceCategoryFilter');
    const marketplaceRatingFilter = document.getElementById('marketplaceRatingFilter');
    const marketplacePriceFilter = document.getElementById('marketplacePriceFilter');
    const marketplaceConditionFilter = document.getElementById('marketplaceConditionFilter');
    const marketplaceCardsContainer = document.getElementById('marketplaceCardsContainer');
    const marketplaceEmptyState = document.getElementById('marketplaceEmptyState');
    const marketplacePagination = document.getElementById('marketplacePagination');
    const postDetailsModalLabel = document.getElementById('postDetailsModalLabel');
    const postDetailsDescription = document.getElementById('postDetailsDescription');
    const postDetailsRatingStars = document.getElementById('postDetailsRatingStars');
    const postDetailsRatingValue = document.getElementById('postDetailsRatingValue');
    const postDetailsReviewCount = document.getElementById('postDetailsReviewCount');
    const postDetailsPrice = document.getElementById('postDetailsPrice');
    const postDetailsStatus = document.getElementById('postDetailsStatus');
    const postDetailsCondition = document.getElementById('postDetailsCondition');
    const postDetailsSeller = document.getElementById('postDetailsSeller');
    const postDetailsSellerRating = document.getElementById('postDetailsSellerRating');
    const postDetailsCategory = document.getElementById('postDetailsCategory');
    const postImagesCarouselIndicators = document.getElementById('postImagesCarouselIndicators');
    const postImagesCarouselInner = document.getElementById('postImagesCarouselInner');
    const postImagesCarouselPrev = document.getElementById('postImagesCarouselPrev');
    const postImagesCarouselNext = document.getElementById('postImagesCarouselNext');
    const postDetailsModal = document.getElementById('postDetailsModal');
    const submitSellerRatingBtn = document.getElementById('submitSellerRatingBtn');
    const ratingSentToastEl = document.getElementById('ratingSentToast');

    //Access log Pagination
    const accessLogsTableBody = document.querySelector('table tbody');
    const accessLogsPagination = document.getElementById('accessLogsPagination');

    const ACCESS_LOGS_PER_PAGE = 10;
    let currentAccessLogsPage = 1;

    function renderAccessLogs() {
        if (!accessLogsTableBody || !accessLogsPagination) return;

        const rows = Array.from(accessLogsTableBody.querySelectorAll('tr'));
        const totalPages = Math.max(1, Math.ceil(rows.length / ACCESS_LOGS_PER_PAGE));

        if (currentAccessLogsPage > totalPages) {
            currentAccessLogsPage = totalPages;
        }

        const start = (currentAccessLogsPage - 1) * ACCESS_LOGS_PER_PAGE;
        const end = start + ACCESS_LOGS_PER_PAGE;

        rows.forEach((row, index) => {
            row.classList.toggle('d-none', index < start || index >= end);
        });

        renderPagination({
            container: accessLogsPagination,
            currentPage: currentAccessLogsPage,
            totalItems: rows.length,
            itemsPerPage: ACCESS_LOGS_PER_PAGE,
            onPageChange: (page) => {
                currentAccessLogsPage = page;
                renderAccessLogs();
            }
        });
    }

    renderAccessLogs();

    const ratingSentToast = ratingSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(ratingSentToastEl)
        : null;


    const submitReportBtn = document.getElementById('submitReportBtn');
    const reportSentToastEl = document.getElementById('reportSentToast');
    const postCreatedToastEl = document.getElementById('postCreatedToast');
    const postCreatedToast = postCreatedToastEl
        ? bootstrap.Toast.getOrCreateInstance(postCreatedToastEl)
        : null;
    const reportSentToast = reportSentToastEl
        ? bootstrap.Toast.getOrCreateInstance(reportSentToastEl)
        : null;


    if (submitSellerRatingBtn) {
        submitSellerRatingBtn.addEventListener('click', () => {
            const ratingValue = Number(document.getElementById('sellerRatingValue')?.value || 0);


            if (!ratingValue) return;


            ratingSentToast?.show();
        });
    }
    const POSTS_PER_PAGE = 18;
    let currentMarketplacePage = 1;


    function getStoredMarketplacePosts() {
        try {
            const raw = localStorage.getItem('marketplacePosts');
            return raw ? JSON.parse(raw) : [];
        } catch {
            return [];
        }
    }


    function saveMarketplacePosts(posts) {
        localStorage.setItem('marketplacePosts', JSON.stringify(posts));
    }


    let allMarketplacePosts = getStoredMarketplacePosts();


    function createMarketplacePostObject() {
        const imageUrls = selectedPostImages.map((file) => URL.createObjectURL(file));
        const firstImage = imageUrls[0] || '';


        return {
            id: Date.now(),
            title: postTitle.value.trim(),
            description: postDescription.value.trim(),
            price: postPrice.value.trim(),
            category: postCategory.value,
            condition: postCondition.value,
            status: 'Disponible',
            seller: 'Usuario actual',
            rating: '0.0',
            reviews: '0',
            createdAt: 'hace unos segundos',
            image: firstImage,
            images: imageUrls
        };
    }


    function createMarketplaceCardHTML(post) {
        return `
       <div
           class="col-md-6 col-lg-4 marketplace-card"
           data-id="${post.id}"
           data-title="${post.title}"
           data-description="${post.description}"
           data-category="${post.category}"
           data-status="${post.status}"
           data-condition="${post.condition}"
           data-seller="${post.seller}"
       >
           <div class="card h-100 shadow-sm rounded-4 overflow-hidden item-card border-0">
               <img
                   src="${post.image}"
                   class="card-img-top"
                   alt="${post.title}"
                   style="height: 220px; object-fit: contain;"
               >


               <div class="card-body d-flex flex-column p-4">
                   <div class="d-flex justify-content-between align-items-start mb-2">
                       <h5 class="card-title mb-0 fw-bold">${post.title}</h5>
                       <span class="badge rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                           ${post.status}
                       </span>
                   </div>


                   <p class="text-muted mb-3">${post.description}</p>


                   <h3 class="fw-bold text-success mb-3">$${post.price}</h3>


                   <div class="d-flex gap-2 mb-3 flex-wrap">
                       <span class="badge border rounded-0 px-3 py-2" style="background-color:#6FC21F; color:white;">
                           ${post.condition}
                       </span>
                       <span class="badge px-3 py-2 rounded-0" style="background-color:#6FC21F; color:white;">
                           ${post.category}
                       </span>
                   </div>


                   <div class="small text-muted mb-3">
                       <div class="mb-2">
                           <i class="bi bi-person me-2"></i> ${post.seller}
                       </div>
                       <div class="mb-2">
                           <i class="bi bi-star-fill text-warning me-2"></i> ${post.rating} (${post.reviews} calificaciones)
                       </div>
                       <div>
                           <i class="bi bi-clock me-2"></i> ${post.createdAt}
                       </div>
                   </div>
                   <div class="d-grid gap-2">
                       <button
                           type="button"
                           class="btn btn-success rounded-3 open-post-details"
                           data-id="${post.id}"
                       >
                           Ver Detalles
                       </button>
                       <button
                           type="button"
                           class="btn btn-danger rounded-3 open-delete-post-modal"
                           data-id="${post.id}"
                           data-post-title="${post.title}"
                       >
                           Eliminar Publicación
                       </button>
                   </div>
               </div>
           </div>
       </div>
   `;
    }


    function buildStarsHTML(value) {
        const rating = Number(value) || 0;
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);


        let starsHTML = '';


        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<i class="bi bi-star-fill"></i>';
        }


        if (hasHalfStar) {
            starsHTML += '<i class="bi bi-star-half"></i>';
        }


        for (let i = 0; i < emptyStars; i++) {
            starsHTML += '<i class="bi bi-star"></i>';
        }


        return starsHTML;
    }


    function renderPostDetailsCarousel(images = []) {
        if (!postImagesCarouselIndicators || !postImagesCarouselInner) return;


        postImagesCarouselIndicators.innerHTML = '';
        postImagesCarouselInner.innerHTML = '';


        const validImages = Array.isArray(images) && images.length ? images : [];


        validImages.forEach((image, index) => {
            postImagesCarouselIndicators.insertAdjacentHTML('beforeend', `
           <button
               type="button"
               data-bs-target="#postImagesCarousel"
               data-bs-slide-to="${index}"
               class="${index === 0 ? 'active' : ''}"
               ${index === 0 ? 'aria-current="true"' : ''}
               aria-label="Slide ${index + 1}"
           ></button>
       `);


            postImagesCarouselInner.insertAdjacentHTML('beforeend', `
           <div class="carousel-item ${index === 0 ? 'active' : ''} post-carousel-item">
               <div class="carousel-image-box">
                   <img
                       src="${image}"
                       alt="Imagen ${index + 1}"
                       class="post-carousel-img"
                   >
               </div>
           </div>
       `);
        });


        const shouldHideControls = validImages.length <= 1;


        if (postImagesCarouselPrev) {
            postImagesCarouselPrev.classList.toggle('d-none', shouldHideControls);
        }


        if (postImagesCarouselNext) {
            postImagesCarouselNext.classList.toggle('d-none', shouldHideControls);
        }
    }


    function populatePostDetailsModal(post) {
        if (!post) return;


        if (postDetailsModalLabel) postDetailsModalLabel.textContent = post.title || 'Detalle de la publicación';
        if (postDetailsDescription) {
            const description = (post.description || '').trim();


            if (description) {
                postDetailsDescription.textContent = description;
                postDetailsDescription.classList.remove('d-none');
            } else {
                postDetailsDescription.textContent = '';
                postDetailsDescription.classList.add('d-none');
            }
        }
        if (postDetailsRatingStars) postDetailsRatingStars.innerHTML = buildStarsHTML(post.rating);
        if (postDetailsRatingValue) postDetailsRatingValue.textContent = post.rating || '0.0';
        if (postDetailsReviewCount) postDetailsReviewCount.textContent = `(${post.reviews || 0})`;
        if (postDetailsPrice) postDetailsPrice.textContent = `$${post.price || '0.00'}`;
        if (postDetailsStatus) postDetailsStatus.textContent = post.status || 'Disponible';
        if (postDetailsCondition) postDetailsCondition.textContent = post.condition || 'Sin especificar';
        if (postDetailsSeller) postDetailsSeller.textContent = post.seller || 'Usuario';
        if (postDetailsSellerRating) {
            postDetailsSellerRating.innerHTML = `<i class="bi bi-star-fill text-warning me-1"></i> ${post.rating || '0.0'} <span class="text-muted">(${post.reviews || 0} reseñas)</span>`;
        }
        if (postDetailsCategory) postDetailsCategory.textContent = post.category || 'Sin categoría';


        const images = Array.isArray(post.images) && post.images.length
            ? post.images
            : (post.image ? [post.image] : []);


        renderPostDetailsCarousel(images);
    }


    function attachMarketplaceDetailsEvents() {
        if (!postDetailsModal) return;


        document.querySelectorAll('.open-post-details').forEach((button) => {
            button.addEventListener('click', () => {
                const postId = Number(button.dataset.id);
                const selectedPost = allMarketplacePosts.find((post) => Number(post.id) === postId);


                if (!selectedPost) return;


                populatePostDetailsModal(selectedPost);


                const modalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                modalInstance.show();
            });
        });
    }


    function getFilteredMarketplacePosts() {
        const searchValue = (marketplaceSearch?.value || '').trim().toLowerCase();
        const selectedCategory = marketplaceCategoryFilter?.value || 'all';
        const selectedRating = marketplaceRatingFilter?.value || 'all';
        const selectedPriceRange = marketplacePriceFilter?.value || 'all';
        const selectedCondition = marketplaceConditionFilter?.value || 'all';


        return allMarketplacePosts.filter((post) => {
            const postRating = Number(post.rating) || 0;
            const postPriceValue = Number(post.price) || 0;


            const matchesSearch =
                searchValue === '' ||
                post.title.toLowerCase().includes(searchValue) ||
                (post.description || '').toLowerCase().includes(searchValue) ||
                post.category.toLowerCase().includes(searchValue) ||
                post.condition.toLowerCase().includes(searchValue) ||
                post.seller.toLowerCase().includes(searchValue);


            const matchesCategory =
                selectedCategory === 'all' || post.category === selectedCategory;


            let matchesRating = true;


            if (selectedRating !== 'all') {
                const ratingFilter = Number(selectedRating);


                if (ratingFilter === 0) {
                    matchesRating = postRating === 0;
                } else {
                    matchesRating = postRating > (ratingFilter - 1) && postRating <= ratingFilter;
                }
            }


            const matchesCondition =
                selectedCondition === 'all' || post.condition === selectedCondition;


            let matchesPrice = true;


            if (selectedPriceRange === '0') {
                matchesPrice = postPriceValue === 0;
            } else if (selectedPriceRange === '0.01-9.99') {
                matchesPrice = postPriceValue >= 0.01 && postPriceValue <= 9.99;
            } else if (selectedPriceRange === '10-29.99') {
                matchesPrice = postPriceValue >= 10 && postPriceValue <= 29.99;
            } else if (selectedPriceRange === '30-49.99') {
                matchesPrice = postPriceValue >= 30 && postPriceValue <= 49.99;
            } else if (selectedPriceRange === '50+') {
                matchesPrice = postPriceValue >= 50;
            }


            return matchesSearch && matchesCategory && matchesRating && matchesPrice && matchesCondition;
        });
    }


    function renderPagination({
                                  container,
                                  currentPage,
                                  totalItems,
                                  itemsPerPage,
                                  onPageChange
                              }) {
        if (!container) return;

        const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

        if (totalItems <= 0) {
            container.innerHTML = '';
            return;
        }

        let paginationHTML = '';

        paginationHTML += `
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

        container.innerHTML = paginationHTML;

        container.querySelectorAll('.page-link').forEach((button) => {
            button.addEventListener('click', () => {
                const action = button.dataset.page;
                let newPage = currentPage;

                if (action === 'prev' && currentPage > 1) {
                    newPage = currentPage - 1;
                } else if (action === 'next' && currentPage < totalPages) {
                    newPage = currentPage + 1;
                } else if (!isNaN(action)) {
                    newPage = Number(action);
                }

                if (newPage !== currentPage) {
                    onPageChange(newPage);
                }
            });
        });
    }


    function renderMarketplace() {
        if (!marketplaceCardsContainer) return;


        const filteredPosts = getFilteredMarketplacePosts();
        const totalPages = Math.max(1, Math.ceil(filteredPosts.length / POSTS_PER_PAGE));


        if (currentMarketplacePage > totalPages) {
            currentMarketplacePage = totalPages;
        }


        const start = (currentMarketplacePage - 1) * POSTS_PER_PAGE;
        const end = start + POSTS_PER_PAGE;
        const paginatedPosts = filteredPosts.slice(start, end);


        marketplaceCardsContainer.querySelectorAll('.marketplace-card').forEach((card) => card.remove());


        if (marketplaceEmptyState) {
            marketplaceEmptyState.classList.toggle('d-none', filteredPosts.length !== 0);
        }


        paginatedPosts.forEach((post) => {
            marketplaceCardsContainer.insertAdjacentHTML('beforeend', createMarketplaceCardHTML(post));
        });


        renderPagination({
            container: marketplacePagination,
            currentPage: currentMarketplacePage,
            totalItems: filteredPosts.length,
            itemsPerPage: POSTS_PER_PAGE,
            onPageChange: (page) => {
                currentMarketplacePage = page;
                renderMarketplace();
            }
        });
        attachMarketplaceDeleteEvents();
        attachMarketplaceDetailsEvents();
    }


    function resetCreatePostLocalState() {
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


    if (marketplaceSearch) {
        marketplaceSearch.addEventListener('input', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }


    if (marketplaceConditionFilter) {
        marketplaceConditionFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }


    if (marketplaceCategoryFilter) {
        marketplaceCategoryFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }


    if (marketplaceRatingFilter) {
        marketplaceRatingFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }


    if (marketplacePriceFilter) {
        marketplacePriceFilter.addEventListener('change', () => {
            currentMarketplacePage = 1;
            renderMarketplace();
        });
    }


    renderMarketplace();


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
        const postPriceGroup = document.getElementById('postPriceGroup');


        if (showError) {
            clearFieldError(postPrice, postPriceError);
            postPriceGroup?.classList.remove('is-invalid');
        }


        if (!value) {
            if (showError) {
                setFieldError(postPrice, postPriceError, 'El precio es obligatorio.');
                postPriceGroup?.classList.add('is-invalid');
            }
            return false;
        }


        if (/[eE+\-]/.test(value)) {
            if (showError) {
                setFieldError(postPrice, postPriceError, 'No se permite usar e, E, + ni - en el precio.');
                postPriceGroup?.classList.add('is-invalid');
            }
            return false;
        }


        if (!priceRegex.test(value)) {
            if (showError) {
                setFieldError(postPrice, postPriceError, 'Ingresa un precio válido usando solo números y hasta 2 decimales.');
                postPriceGroup?.classList.add('is-invalid');
            }
            return false;
        }


        if (Number(value) < 0) {
            if (showError) {
                setFieldError(postPrice, postPriceError, 'El precio no puede ser negativo.');
                postPriceGroup?.classList.add('is-invalid');
            }
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
            let value = postPrice.value.replace(/[^0-9.]/g, '');


            if (value.startsWith('.')) {
                value = '0' + value;
            }


            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }


            const firstDotIndex = value.indexOf('.');
            if (firstDotIndex !== -1) {
                const integerPart = value.slice(0, firstDotIndex);
                const decimalPart = value.slice(firstDotIndex + 1).slice(0, 2);
                value = integerPart + '.' + decimalPart;
            }






            postPrice.value = value;


            validatePrice(true);
            updatePublishButtonState();
            updateCreatePostDirtyState();
        });


        postPrice.addEventListener('blur', () => {
            const rawValue = postPrice.value.trim();


            if (!rawValue) return;


            const numericValue = Number(rawValue);


            if (Number.isNaN(numericValue)) {
                postPrice.value = '';
                validatePrice(true);
                updatePublishButtonState();
                updateCreatePostDirtyState();
                return;
            }


            postPrice.value = numericValue.toFixed(2);
            validatePrice(true);
            updatePublishButtonState();
            updateCreatePostDirtyState();
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


            const newPost = createMarketplacePostObject();
            allMarketplacePosts.unshift(newPost);
            saveMarketplacePosts(allMarketplacePosts);


            currentMarketplacePage = 1;
            renderMarketplace();


            const createModalInstance = bootstrap.Modal.getOrCreateInstance(createPostModal);
            allowCreatePostClose = true;
            createModalInstance.hide();


            resetCreatePostLocalState();
            setTimeout(() => {
                postCreatedToast?.show();
            }, 250);
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


        const postPriceGroup = document.getElementById('postPriceGroup');
        postPriceGroup?.classList.remove('is-invalid');


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


// Modal open & close behavior
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


    //Reporting
    const reportUserForm = document.getElementById('reportUserForm');
    const reportReason = document.getElementById('reportReason');
    const reportReasonError = document.getElementById('reportReasonError');
    const reportDescription = document.getElementById('reportDescription');
    const reportDescriptionError = document.getElementById('reportDescriptionError');
    const reportUserModal = document.getElementById('reportUserModal');
    const cancelReportBtn = document.getElementById('cancelReportBtn');
    const closeReportModalBtn = document.getElementById('closeReportModalBtn');
    const cancelReportConfirmModal = document.getElementById('cancelReportConfirmModal');
    const confirmCancelReport = document.getElementById('confirmCancelReport');


    let isReportDirty = false;
    let allowReportClose = false;


    function validateReportReason(showError = true) {
        if (!reportReason) return true;


        const isValid = !!reportReason.value;


        if (showError) {
            if (!isValid) {
                reportReason.classList.add('is-invalid');
            } else {
                reportReason.classList.remove('is-invalid');
            }
        }


        return isValid;
    }


    function validateReportDescription(showError = true) {
        if (!reportDescription || !reportDescriptionError) return true;


        const value = reportDescription.value.trim();


        if (showError) {
            reportDescription.classList.remove('is-invalid');
            reportDescriptionError.textContent = '';
        }


        if (!value) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción es obligatoria.';
            }
            return false;
        }


        if (value.length < 10) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción debe tener al menos 10 caracteres.';
            }
            return false;
        }


        if (value.length > 500) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'La descripción no puede exceder 500 caracteres.';
            }
            return false;
        }


        if (!allowedTextRegex.test(value)) {
            if (showError) {
                reportDescription.classList.add('is-invalid');
                reportDescriptionError.textContent = 'Solo se permiten letras, números, espacios, punto, coma y guion.';
            }
            return false;
        }


        return true;
    }


    function updateReportButtonState() {
        if (!submitReportBtn) return;


        const isReady =
            validateReportReason(false) &&
            validateReportDescription(false);


        submitReportBtn.disabled = !isReady;
    }


    if (reportReason) {
        reportReason.addEventListener('change', () => {
            validateReportReason(true);
            updateReportButtonState();
        });
    }


    if (reportDescription) {
        reportDescription.addEventListener('input', () => {
            reportDescription.value = reportDescription.value.slice(0, 500);


            if (reportDescription.value.trim() === '') {
                reportDescription.classList.remove('is-invalid');
                reportDescriptionError.textContent = '';
            } else {
                validateReportDescription(true);
            }


            updateReportButtonState();
        });
    }


    if (submitReportBtn) {
        submitReportBtn.addEventListener('click', (e) => {
            e.preventDefault();


            const isReasonValid = validateReportReason(true);
            const isDescriptionValid = validateReportDescription(true);


            if (!isReasonValid || !isDescriptionValid) {
                updateReportButtonState();
                return;
            }


            allowReportClose = true;


            const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);
            reportModalInstance.hide();


            setTimeout(() => {
                reportSentToast?.show();
            }, 250);


            if (postDetailsModal) {
                setTimeout(() => {
                    const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show();
                }, 300);
            }
        });
    }
    updateReportButtonState();


    function resetReportValidation() {
        if (reportReason) {
            reportReason.classList.remove('is-invalid');
        }


        if (reportDescription) {
            reportDescription.classList.remove('is-invalid');
        }


        if (reportDescriptionError) {
            reportDescriptionError.textContent = '';
        }


        if (reportReasonError) {
            reportReasonError.textContent = 'Selecciona una razón.';
        }
    }


    function resetReportForm() {
        if (reportUserForm) {
            reportUserForm.reset();
        }


        isReportDirty = false;
        allowReportClose = false;


        resetReportValidation();
        updateReportButtonState();
    }


    function updateReportDirtyState() {
        const hasReason = !!(reportReason && reportReason.value);
        const hasDescription = !!(reportDescription && reportDescription.value.trim() !=='');


        isReportDirty = hasReason || hasDescription;
    }


    [reportReason, reportDescription].forEach((field) => {
        if (!field) return;


        field.addEventListener('input', updateReportDirtyState);
        field.addEventListener('change', updateReportDirtyState);
    });


    if (reportUserModal) {
        reportUserModal.addEventListener('show.bs.modal', () => {
            resetReportValidation();
            updateReportButtonState();
        });


        reportUserModal.addEventListener('hide.bs.modal', (event) => {
            if (allowReportClose) {
                return;
            }


            updateReportDirtyState();


            if (!isReportDirty) {
                resetReportForm();
                return;
            }


            event.preventDefault();


            if (cancelReportConfirmModal) {
                const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
                confirmModal.show();
            }
        });


        reportUserModal.addEventListener('hidden.bs.modal', () => {
            if (allowReportClose) {
                resetReportForm();
                allowReportClose = false;
            }
        });
    }


    function tryCloseReportModal() {
        if (!reportUserModal) return;


        updateReportDirtyState();


        const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);


        if (!isReportDirty) {
            allowReportClose = true;
            reportModalInstance.hide();


            if(postDetailsModal){
                setTimeout(()=> {
                    const postModalInstance=bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show()
                })
            }
            return;
        }


        if (cancelReportConfirmModal) {
            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
            confirmModal.show();
        }
    }


    if (cancelReportBtn) {
        cancelReportBtn.addEventListener('click', tryCloseReportModal);
    }


    if (closeReportModalBtn) {
        closeReportModalBtn.addEventListener('click', (e) =>{
            e.preventDefault();
            tryCloseReportModal();
        })
    }




    if (confirmCancelReport && reportUserModal && cancelReportConfirmModal) {
        confirmCancelReport.addEventListener('click', () => {
            allowReportClose = true;


            const confirmModal = bootstrap.Modal.getOrCreateInstance(cancelReportConfirmModal);
            confirmModal.hide();


            const reportModalInstance = bootstrap.Modal.getOrCreateInstance(reportUserModal);
            reportModalInstance.hide();


            if (postDetailsModal) {
                setTimeout(() => {
                    const postModalInstance = bootstrap.Modal.getOrCreateInstance(postDetailsModal);
                    postModalInstance.show();
                }, 200);
            }
        });
    }


    //Rating Functionality
    const ratingContainer = document.getElementById('sellerRatingStars');
    const ratingInput = document.getElementById('sellerRatingValue');
    const ratingText = document.getElementById('sellerRatingText');
    const clearSellerRating = document.getElementById('clearSellerRating');


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


                star.classList.remove('bi-star', 'bi-star-fill');


                if (starValue <= value) {
                    star.classList.add('bi-star-fill');
                } else {
                    star.classList.add('bi-star');
                }
            });


            ratingText.textContent = ratingLabels[value] || ratingLabels[0];
        }


        stars.forEach((star) => {
            star.addEventListener('mouseenter', function () {
                paintStars(Number(this.dataset.value));
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
            paintStars(Number(ratingInput.value) || 0);
        });


        if (clearSellerRating) {
            clearSellerRating.addEventListener('click', () => {
                ratingInput.value = 0;
                paintStars(0);
            });
        }


        paintStars(Number(ratingInput.value) || 0);
    }


});

