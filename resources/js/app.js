import './bootstrap';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    const borrowButtons = document.querySelectorAll('.open-borrow-modal');
    const borrowModal = document.getElementById('borrowModal');
    const borrowEquipmentId = document.getElementById('borrowEquipmentId');
    const borrowModalText = document.getElementById('borrowModalText');
    const borrowModalImage = document.getElementById('borrowModalImage');
    const borrowModalStock = document.getElementById('borrowModalStock');
    const borrowQuantity = document.getElementById('borrowQuantity');
    const confirmAddToCart = document.getElementById('confirmAddToCart');
    const borrowQuantityError = document.getElementById('borrowQuantityError');

    if(!window.useDedicatedLayoutValidation) {
        const specialCaseCheck = document.getElementById('special_case');
        const specialCaseFields = document.getElementById('specialCaseFields');
        const loanFullName = document.getElementById('loanFullName');
        const loanFullNameError = document.getElementById('loanFullNameError');
        const loanPickupDate = document.getElementById('pickup_date') || document.getElementById('loanPickupDate');
        const pickupTimeBlock = document.getElementById('pickupTimeBlock');
        const returnDate = document.getElementById('return_date') || document.getElementById('returnDate');
        const specialReason = document.getElementById('special_reason') || document.getElementById('specialReason');

        const loanTermsCheck = document.getElementById('loanTermsCheck');
        const loanTermsError = document.getElementById('loanTermsError');
        const loanPickupDateError = document.getElementById('loanPickupDateError');
        const pickupTimeBlockError = document.getElementById('pickupTimeBlockError');
        const returnDateError = document.getElementById('returnDateError');
        const specialReasonError = document.getElementById('specialReasonError');

        const cartModal = document.getElementById('cartModal');
        const checkoutCartForm = document.getElementById('checkoutCartForm');
        const submitLoanRequest = document.getElementById('submitLoanRequest');

        const submitToastEl = document.getElementById('submitToast');

        const hasBorrowModal =
            borrowModal &&
            borrowModalText &&
            borrowModalImage &&
            borrowModalStock &&
            borrowQuantity &&
            confirmAddToCart;

        let currentItem = {
            id: null,
            name: '',
            stock: 0,
            image: '',
            location: 'Sala de Equipo A'
        };

        const loanAllowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        function toLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function getTodayAtMidnight() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        }

        function isBlockedPickupDay(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            const day = date.getDay();
            return day === 0 || day === 5 || day === 6;
        }

        function setMinDates() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = toLocalDateString(tomorrow);

            if (loanPickupDate) loanPickupDate.min = minDate;
            if (returnDate) returnDate.min = minDate;
        }

        function setLoanFieldError(field, errorElement, message) {
            if (!field) return;
            field.classList.add('is-invalid');
            if (errorElement) errorElement.textContent = message;
        }

        function clearLoanFieldError(field, errorElement) {
            if (!field) return;
            field.classList.remove('is-invalid');
            if (errorElement) errorElement.textContent = '';
        }

        function clearLoanValidation() {
            clearLoanFieldError(loanFullName, loanFullNameError);
            clearLoanFieldError(loanTermsCheck, loanTermsError);
            clearLoanFieldError(loanPickupDate, loanPickupDateError);
            clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
            clearLoanFieldError(returnDate, returnDateError);
            clearLoanFieldError(specialReason, specialReasonError);
        }

        function setBorrowQuantityError(message) {
            if (borrowQuantity) borrowQuantity.classList.add('is-invalid');
            if (borrowQuantityError) borrowQuantityError.textContent = message;
        }

        function clearBorrowQuantityError() {
            if (borrowQuantity) borrowQuantity.classList.remove('is-invalid');
            if (borrowQuantityError) borrowQuantityError.textContent = '';
        }

        function validateLoanFullNameField(showError = true) {
            if (!loanFullName) return true;

            const name = loanFullName.value.trim();

            if (showError) {
                clearLoanFieldError(loanFullName, loanFullNameError);
            }

            if (!name) {
                if (showError) setLoanFieldError(loanFullName, loanFullNameError, 'El nombre completo es obligatorio.');
                return false;
            }

            if (name.length < 5) {
                if (showError) setLoanFieldError(loanFullName, loanFullNameError, 'El nombre debe tener al menos 5 caracteres.');
                return false;
            }

            if (name.length > 80) {
                if (showError) setLoanFieldError(loanFullName, loanFullNameError, 'El nombre no puede exceder 80 caracteres.');
                return false;
            }

            if (!loanAllowedTextRegex.test(name)) {
                if (showError) {
                    setLoanFieldError(
                        loanFullName,
                        loanFullNameError,
                        'Solo se permiten letras, números, espacios, punto, coma y guion.'
                    );
                }
                return false;
            }

            return true;
        }

        function validateLoanTermsField(showError = true) {
            if (!loanTermsCheck) return true;

            const isValid = loanTermsCheck.checked;

            if (showError) {
                clearLoanFieldError(loanTermsCheck, loanTermsError);
            }

            if (!isValid) {
                if (showError) {
                    setLoanFieldError(loanTermsCheck, loanTermsError, 'Debes aceptar los términos y condiciones.');
                }
                return false;
            }

            return true;
        }

        function validateSpecialReasonField(showError = true) {
            if (!specialCaseCheck?.checked) return true;
            if (!specialReason) return true;

            const reason = specialReason.value.trim();

            if (showError) {
                clearLoanFieldError(specialReason, specialReasonError);
            }

            if (!reason) {
                if (showError) {
                    setLoanFieldError(specialReason, specialReasonError, 'La razón del caso especial es obligatoria.');
                }
                return false;
            }

            if (reason.length < 10) {
                if (showError) {
                    setLoanFieldError(specialReason, specialReasonError, 'La razón debe tener al menos 10 caracteres.');
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

        function validateLoanForm(showErrors = true) {
            if (showErrors) {
                clearLoanValidation();
            }

            let hasError = false;

            const pickupDateValue = loanPickupDate?.value || '';
            const pickupTime = pickupTimeBlock?.value || '';
            const isSpecialCase = specialCaseCheck?.checked;
            const returnVal = returnDate?.value || '';

            if (loanFullName && !validateLoanFullNameField(showErrors)) {
                hasError = true;
            }

            if (!validateLoanTermsField(showErrors)) {
                hasError = true;
            }

            if (loanPickupDate && !pickupDateValue) {
                if (showErrors) {
                    setLoanFieldError(loanPickupDate, loanPickupDateError, 'La fecha de recogida es obligatoria.');
                }
                hasError = true;
            } else if (loanPickupDate && pickupDateValue) {
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

            if (pickupTimeBlock && !pickupTime) {
                if (showErrors) {
                    setLoanFieldError(pickupTimeBlock, pickupTimeBlockError, 'La hora de recogida es obligatoria.');
                }
                hasError = true;
            }

            if (isSpecialCase) {
                if (returnDate && !returnVal) {
                    if (showErrors) {
                        setLoanFieldError(returnDate, returnDateError, 'La fecha de devolución es obligatoria.');
                    }
                    hasError = true;
                } else if (returnDate && returnVal) {
                    const today = getTodayAtMidnight();
                    const returnDateObj = new Date(`${returnVal}T00:00:00`);

                    if (returnDateObj <= today) {
                        if (showErrors) {
                            setLoanFieldError(returnDate, returnDateError, 'La fecha de devolución debe ser futura.');
                        }
                        hasError = true;
                    }
                }

                if (!validateSpecialReasonField(showErrors)) {
                    hasError = true;
                }
            }

            return !hasError;
        }

        function updateLoanSubmitButtonState() {
            if (!submitLoanRequest) return;
            const formIsValid = validateLoanForm(false);
            submitLoanRequest.disabled = !formIsValid;
        }

        if (specialCaseCheck && specialCaseFields) {
            specialCaseCheck.addEventListener('change', () => {
                if (specialCaseCheck.checked) {
                    specialCaseFields.classList.remove('d-none');
                    if (returnDate) returnDate.required = true;
                    if (specialReason) specialReason.required = true;
                } else {
                    specialCaseFields.classList.add('d-none');
                    if (returnDate) {
                        returnDate.required = false;
                        returnDate.value = '';
                    }
                    if (specialReason) {
                        specialReason.required = false;
                        specialReason.value = '';
                    }
                    clearLoanFieldError(returnDate, returnDateError);
                    clearLoanFieldError(specialReason, specialReasonError);
                }

                updateLoanSubmitButtonState();
            });
        }

        if (loanFullName) {
            loanFullName.addEventListener('input', () => {
                loanFullName.value = loanFullName.value.slice(0, 80);
                validateLoanFullNameField(true);
                updateLoanSubmitButtonState();
            });
        }

        if (loanTermsCheck) {
            loanTermsCheck.addEventListener('change', () => {
                clearLoanFieldError(loanTermsCheck, loanTermsError);
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

        if (pickupTimeBlock) {
            pickupTimeBlock.addEventListener('change', () => {
                clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
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

        if (specialReason) {
            specialReason.addEventListener('input', () => {
                specialReason.value = specialReason.value.slice(0, 500);
                validateSpecialReasonField(true);
                updateLoanSubmitButtonState();
            });
        }

        if (checkoutCartForm && submitLoanRequest) {
            checkoutCartForm.addEventListener('submit', (e) => {
                const isValid = validateLoanForm(true);

                if (!isValid) {
                    e.preventDefault();
                    updateLoanSubmitButtonState();
                    return;
                }
            });
        }

        if (hasBorrowModal) {
            borrowButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const itemId = button.dataset.itemId;
                    currentItem.id = itemId;
                    currentItem.name = button.dataset.itemName || '';
                    currentItem.stock = parseInt(button.dataset.itemStock || '1', 10);
                    currentItem.image = button.dataset.itemImage || '';
                    currentItem.location = button.dataset.itemLocation || 'Sala de Equipo A';

                    if (borrowEquipmentId) borrowEquipmentId.value = itemId;
                    borrowModalText.textContent = `Selecciona la cantidad de ${currentItem.name} que deseas`;
                    borrowModalImage.src = currentItem.image;
                    borrowModalImage.alt = currentItem.name;
                    borrowModalStock.textContent = currentItem.stock;

                    borrowQuantity.value = 1;
                    borrowQuantity.min = 1;
                    borrowQuantity.max = currentItem.stock;

                    clearBorrowQuantityError();
                });
            });

            borrowQuantity.addEventListener('input', () => {
                clearBorrowQuantityError();

                if (borrowQuantity.value === '') return;

                let value = parseInt(borrowQuantity.value, 10);

                if (isNaN(value) || value < 1) {
                    borrowQuantity.value = 1;
                    setBorrowQuantityError('Debes pedir al menos 1 unidad.');
                    return;
                }

                if (value > currentItem.stock) {
                    borrowQuantity.value = currentItem.stock;
                    setBorrowQuantityError(`No puedes pedir más de la cantidad disponible (${currentItem.stock}).`);
                    return;
                }

                borrowQuantity.value = value;
            });

            confirmAddToCart.addEventListener('click', () => {
                clearBorrowQuantityError();

                const quantity = parseInt(borrowQuantity.value, 10);

                if (isNaN(quantity) || quantity < 1) {
                    setBorrowQuantityError('Debes pedir al menos 1 unidad.');
                    return;
                }

                if (quantity > currentItem.stock) {
                    setBorrowQuantityError(`No puedes pedir más de la cantidad disponible (${currentItem.stock}).`);
                    return;
                }

                const borrowForm = document.getElementById('borrowForm');
                if (borrowForm) {
                    borrowForm.submit();
                }
            });
        }
        setMinDates();
        updateLoanSubmitButtonState();
    }

});

