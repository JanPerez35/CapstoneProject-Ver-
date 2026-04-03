document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#cartModal form');
    const body = document.body;
    if (!form) {
        showGlobalToasts(body);
        return;
    }

    const pickupDate = document.getElementById('pickup_date');
    const pickupTime = document.getElementById('pickup_time');
    const specialCase = document.getElementById('special_case');
    const specialCaseFields = document.getElementById('specialCaseFields');
    const returnDate = document.getElementById('return_date');
    const specialReason = document.getElementById('special_reason');
    const acceptTerms = document.getElementById('accept_terms');
    const submitBtn = document.getElementById('submitLoanRequest');
    const cartCount = document.getElementById('cartCount');

    const pickupDateError = document.getElementById('pickup_date_error');
    const pickupTimeError = document.getElementById('pickup_time_error');
    const returnDateError = document.getElementById('return_date_error');
    const specialReasonError = document.getElementById('special_reason_error');
    const acceptTermsError = document.getElementById('accept_terms_error');

    const removeCartConfirmModal = document.getElementById('removeCartConfirmModal');
    const removeCartConfirmText = document.getElementById('removeCartConfirmText');
    const confirmRemoveCartItem = document.getElementById('confirmRemoveCartItem');

    const allowedReasonRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

    let removeCartFormId = null;

    function setError(field, errorEl, message) {
        if (field) field.classList.add('is-invalid');
        if (errorEl) errorEl.textContent = message;
    }

    function clearError(field, errorEl) {
        if (field) field.classList.remove('is-invalid');
        if (errorEl) errorEl.textContent = '';
    }

    function isBlockedPickupDay(dateString) {
        const date = new Date(`${dateString}T00:00:00`);
        const day = date.getDay();
        return day === 5 || day === 6 || day === 0;
    }

    function todayString() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d.toISOString().split('T')[0];
    }

    function tomorrowString() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        d.setDate(d.getDate() + 1);
        return d.toISOString().split('T')[0];
    }

    function setMinDates() {
        const min = tomorrowString();
        if (pickupDate) pickupDate.min = min;
        if (returnDate) returnDate.min = min;
    }

    function toggleSpecialCaseFields() {
        if (!specialCase || !specialCaseFields) return;

        if (specialCase.checked) {
            specialCaseFields.classList.remove('d-none');
            if (returnDate) returnDate.required = true;
            if (specialReason) specialReason.required = true;
        } else {
            specialCaseFields.classList.add('d-none');

            if (returnDate) {
                returnDate.required = false;
                returnDate.value = '';
                clearError(returnDate, returnDateError);
            }

            if (specialReason) {
                specialReason.required = false;
                specialReason.value = '';
                clearError(specialReason, specialReasonError);
            }
        }
    }

    function validateSpecialReason(showErrors = true) {
        if (!specialCase?.checked) {
            clearError(specialReason, specialReasonError);
            return true;
        }

        const reason = specialReason?.value.trim() || '';

        if (showErrors) {
            clearError(specialReason, specialReasonError);
        }

        if (!reason) {
            if (showErrors) {
                setError(specialReason, specialReasonError, 'La razón del caso especial es obligatoria.');
            }
            return false;
        }

        if (!allowedReasonRegex.test(reason)) {
            if (showErrors) {
                setError(
                    specialReason,
                    specialReasonError,
                    'La razón contiene caracteres no permitidos. Solo se permiten letras, números, espacios, punto, coma y guion.'
                );
            }
            return false;
        }

        if (reason.length < 10) {
            if (showErrors) {
                setError(specialReason, specialReasonError, 'La razón debe tener al menos 10 caracteres.');
            }
            return false;
        }

        if (reason.length > 500) {
            if (showErrors) {
                setError(specialReason, specialReasonError, 'No puedes escribir más de 500 caracteres.');
            }
            return false;
        }

        return true;
    }

    function validatePickupDate(showErrors = true) {
        const today = todayString();

        if (showErrors) clearError(pickupDate, pickupDateError);

        if (!pickupDate || !pickupDate.value) {
            if (showErrors) setError(pickupDate, pickupDateError, 'La fecha de recogida es obligatoria.');
            return false;
        }

        if (pickupDate.value <= today) {
            if (showErrors) setError(pickupDate, pickupDateError, 'La fecha debe ser futura.');
            return false;
        }

        if (isBlockedPickupDay(pickupDate.value)) {
            if (showErrors) setError(pickupDate, pickupDateError, 'No se permiten viernes, sábados ni domingos.');
            return false;
        }

        return true;
    }

    function validateReturnDate(showErrors = true) {
        if (!specialCase?.checked) {
            clearError(returnDate, returnDateError);
            return true;
        }

        const today = todayString();

        if (showErrors) clearError(returnDate, returnDateError);

        if (!returnDate || !returnDate.value) {
            if (showErrors) setError(returnDate, returnDateError, 'La fecha de devolución es obligatoria.');
            return false;
        }

        if (returnDate.value <= today) {
            if (showErrors) setError(returnDate, returnDateError, 'La fecha de devolución debe ser futura.');
            return false;
        }

        if (pickupDate?.value && returnDate.value < pickupDate.value) {
            if (showErrors) setError(returnDate, returnDateError, 'La devolución no puede ser antes de la recogida.');
            return false;
        }

        return true;
    }

    function validatePickupTime(showErrors = true) {
        if (showErrors) clearError(pickupTime, pickupTimeError);

        if (!pickupTime || !pickupTime.value) {
            if (showErrors) setError(pickupTime, pickupTimeError, 'La hora de recogida es obligatoria.');
            return false;
        }

        return true;
    }

    function validateTerms(showErrors = true) {
        if (showErrors) clearError(acceptTerms, acceptTermsError);

        if (!acceptTerms || !acceptTerms.checked) {
            if (showErrors) setError(acceptTerms, acceptTermsError, 'Debes aceptar los términos y condiciones.');
            return false;
        }

        return true;
    }

    function validateForm(showErrors = true) {
        let valid = true;

        if (!validatePickupDate(showErrors)) valid = false;
        if (!validatePickupTime(showErrors)) valid = false;
        if (!validateReturnDate(showErrors)) valid = false;
        if (!validateSpecialReason(showErrors)) valid = false;
        if (!validateTerms(showErrors)) valid = false;

        if (submitBtn) submitBtn.disabled = !valid;

        return valid;
    }

    function updateSubmitButtonStateQuietly() {
        const valid = validateForm(false);
        if (submitBtn) submitBtn.disabled = !valid;
    }

    function updateCartBadgeFromRows() {
        if (!cartCount) return;

        const qtyInputs = document.querySelectorAll('.cart-quantity-input');
        let total = 0;

        qtyInputs.forEach(input => {
            total += Number(input.value || 0);
        });

        cartCount.textContent = total;

        if (total > 0) {
            cartCount.classList.remove('d-none');
        } else {
            cartCount.classList.add('d-none');
        }
    }

    function attachCartQuantityControls() {
        document.querySelectorAll('.cart-item-row').forEach(row => {
            const decreaseBtn = row.querySelector('.decrease-cart-item');
            const increaseBtn = row.querySelector('.increase-cart-item');
            const qtyDisplay = row.querySelector('.cart-quantity-display');
            const qtyInput = row.querySelector('.cart-quantity-input');
            const errorEl = row.querySelector('.cart-item-error');

            const min = 1;
            const max = Number(row.dataset.maxQuantity || 1);

            function clearItemError() {
                if (errorEl) errorEl.textContent = '';
            }

            function setItemError(message) {
                if (errorEl) errorEl.textContent = message;
            }

            function syncQuantity(newQty) {
                if (qtyDisplay) qtyDisplay.textContent = String(newQty);
                if (qtyInput) qtyInput.value = String(newQty);
                updateCartBadgeFromRows();
            }

            decreaseBtn?.addEventListener('click', () => {
                clearItemError();
                const current = Number(qtyInput?.value || 1);
                if (current > min) syncQuantity(current - 1);
            });

            increaseBtn?.addEventListener('click', () => {
                clearItemError();
                const current = Number(qtyInput?.value || 1);

                if (current < max) {
                    syncQuantity(current + 1);
                } else {
                    setItemError(`No puedes pedir más de la cantidad disponible (${max}).`);
                }
            });
        });
    }

    function reopenCartModalIfNeeded(bodyEl) {
        const shouldReopen = bodyEl?.dataset?.reopenCartModal === '1';
        const cartModalEl = document.getElementById('cartModal');

        if (!shouldReopen || !cartModalEl) return;

        const cartModalInstance = bootstrap.Modal.getOrCreateInstance(cartModalEl);
        cartModalInstance.show();
    }

    function attachRemoveCartConfirmEvents() {
        if (!removeCartConfirmModal || !confirmRemoveCartItem) return;

        document.querySelectorAll('.open-remove-cart-confirm').forEach((button) => {
            button.addEventListener('click', () => {
                removeCartFormId = button.dataset.formId || null;
                const itemName = button.dataset.itemName || 'este item';

                if (removeCartConfirmText) {
                    removeCartConfirmText.textContent = `¿Estás seguro que quieres remover "${itemName}" del carrito?`;
                }

                const modalInstance = bootstrap.Modal.getOrCreateInstance(removeCartConfirmModal);
                modalInstance.show();
            });
        });

        confirmRemoveCartItem.addEventListener('click', () => {
            if (!removeCartFormId) return;

            const formToSubmit = document.getElementById(removeCartFormId);
            if (!formToSubmit) return;

            formToSubmit.submit();
        });
    }

    function showGlobalToasts(bodyEl) {
        const cartSuccess = bodyEl?.dataset?.cartSuccess || '';
        const requestSuccess = bodyEl?.dataset?.requestSuccess || '';
        const cartRemovedSuccess = bodyEl?.dataset?.cartRemovedSuccess || '';

        const cartToastEl = document.getElementById('cartToast');
        const cartToastMessage = document.getElementById('cartToastMessage');

        if (cartSuccess && cartToastEl && cartToastMessage) {
            cartToastMessage.textContent = cartSuccess;
            bootstrap.Toast.getOrCreateInstance(cartToastEl, { delay: 3000 }).show();
        }

        const submitToastEl = document.getElementById('submitToast');
        const submitToastMessage = document.getElementById('submitToastMessage');

        if (requestSuccess && submitToastEl && submitToastMessage) {
            submitToastMessage.textContent = requestSuccess;
            bootstrap.Toast.getOrCreateInstance(submitToastEl, { delay: 4000 }).show();
        }

        const cartRemovedToastEl = document.getElementById('cartRemovedToast');
        const cartRemovedToastMessage = document.getElementById('cartRemovedToastMessage');

        if (cartRemovedSuccess && cartRemovedToastEl && cartRemovedToastMessage) {
            cartRemovedToastMessage.textContent = cartRemovedSuccess;
            bootstrap.Toast.getOrCreateInstance(cartRemovedToastEl, { delay: 3000 }).show();
        }
    }

    if (pickupDate) {
        pickupDate.addEventListener('input', () => {
            validatePickupDate(true);
            updateSubmitButtonStateQuietly();
        });

        pickupDate.addEventListener('change', () => {
            validatePickupDate(true);
            updateSubmitButtonStateQuietly();
        });
    }

    if (pickupTime) {
        pickupTime.addEventListener('change', () => {
            validatePickupTime(true);
            updateSubmitButtonStateQuietly();
        });
    }

    if (returnDate) {
        returnDate.addEventListener('input', () => {
            validateReturnDate(true);
            updateSubmitButtonStateQuietly();
        });

        returnDate.addEventListener('change', () => {
            validateReturnDate(true);
            updateSubmitButtonStateQuietly();
        });
    }

    if (acceptTerms) {
        acceptTerms.addEventListener('change', () => {
            validateTerms(true);
            updateSubmitButtonStateQuietly();
        });
    }

    if (specialCase) {
        specialCase.addEventListener('change', () => {
            toggleSpecialCaseFields();

            if (!specialCase.checked) {
                clearError(returnDate, returnDateError);
                clearError(specialReason, specialReasonError);
            }

            updateSubmitButtonStateQuietly();
        });
    }

    if (specialReason) {
        specialReason.addEventListener('beforeinput', (e) => {
            if (!specialCase?.checked) return;
            if (e.inputType && e.inputType.startsWith('delete')) return;

            const currentValue = specialReason.value ?? '';
            const selectionStart = specialReason.selectionStart ?? currentValue.length;
            const selectionEnd = specialReason.selectionEnd ?? currentValue.length;
            const incomingText = e.data ?? '';

            const nextLength =
                currentValue.length - (selectionEnd - selectionStart) + incomingText.length;

            if (nextLength > 500) {
                e.preventDefault();
                setError(
                    specialReason,
                    specialReasonError,
                    'No puedes escribir más de 500 caracteres.'
                );
                if (submitBtn) submitBtn.disabled = true;
            }
        });

        specialReason.addEventListener('input', () => {
            validateSpecialReason(true);
            updateSubmitButtonStateQuietly();
        });

        specialReason.addEventListener('change', () => {
            validateSpecialReason(true);
            updateSubmitButtonStateQuietly();
        });
    }

    form.addEventListener('submit', function (e) {
        toggleSpecialCaseFields();

        if (!validateForm(true)) {
            e.preventDefault();
        }
    });

    setMinDates();
    toggleSpecialCaseFields();
    attachCartQuantityControls();
    attachRemoveCartConfirmEvents();
    updateCartBadgeFromRows();
    updateSubmitButtonStateQuietly();
    reopenCartModalIfNeeded(body);
    showGlobalToasts(body);
});
