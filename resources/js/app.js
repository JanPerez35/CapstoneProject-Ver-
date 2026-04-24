import './bootstrap';
import * as bootstrap from 'bootstrap';
import Chart from 'chart.js/auto';
import './echo';

window.Chart = Chart;
window.bootstrap = bootstrap;

/**
 * Initializes shared frontend behavior after the DOM is loaded.
 * This app.js is mainly used for reference. It was a building a block
 * for the initial version of the website.
 *
 * Most js logic was migrated to its unique js related to it's blade view.
 *
 * This file only activates the legacy loan/cart validation logic when
 * the page is not using the dedicated layout validation script.
 */

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Skip this block when the page explicitly uses its own layout validation.
     */
    if(!window.useDedicatedLayoutValidation) {

        /**
         * Loan form element references
         *
         * These elements belong to the cart/loan request workflow.
         */

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

        /**
         * Allowed character pattern for free-text loan fields.
         */
        const loanAllowedTextRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

        /**
         * Converts a Date object into YYYY-MM-DD format for HTML date inputs.
         *
         * @param {Date} date
         * @returns {string}
         */
        function toLocalDateString(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        /**
         * Returns today's date with the time reset to midnight.
         *
         * @returns {Date}
         */
        function getTodayAtMidnight() {
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return today;
        }

        /**
         * Checks whether a selected pickup date falls on a blocked day.
         * Blocked days are Sunday, Friday, and Saturday.
         *
         * @param {string} dateString - Date in YYYY-MM-DD format.
         * @returns {boolean}
         */
        function isBlockedPickupDay(dateString) {
            const date = new Date(`${dateString}T00:00:00`);
            const day = date.getDay();
            return day === 0 || day === 5 || day === 6;
        }

        /**
         * Sets the minimum selectable date for pickup and return fields
         * to tomorrow.
         */
        function setMinDates() {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const minDate = toLocalDateString(tomorrow);

            if (loanPickupDate) loanPickupDate.min = minDate;
            if (returnDate) returnDate.min = minDate;
        }

        /**
         * Marks a loan field as invalid and displays its error message.
         *
         * @param {HTMLElement|null} field
         * @param {HTMLElement|null} errorElement
         * @param {string} message
         */
        function setLoanFieldError(field, errorElement, message) {
            if (!field) return;
            field.classList.add('is-invalid');
            if (errorElement) errorElement.textContent = message;
        }


        /**
         * Clears the invalid state and error message for a loan field.
         *
         * @param {HTMLElement|null} field
         * @param {HTMLElement|null} errorElement
         */
        function clearLoanFieldError(field, errorElement) {
            if (!field) return;
            field.classList.remove('is-invalid');
            if (errorElement) errorElement.textContent = '';
        }

        /**
         * Clears all visible validation errors in the loan form.
         */
        function clearLoanValidation() {
            clearLoanFieldError(loanFullName, loanFullNameError);
            clearLoanFieldError(loanTermsCheck, loanTermsError);
            clearLoanFieldError(loanPickupDate, loanPickupDateError);
            clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
            clearLoanFieldError(returnDate, returnDateError);
            clearLoanFieldError(specialReason, specialReasonError);
        }

        /**
         * Validates the full name field used in the loan form.
         *
         *
         * Rules (these rules changed for the full version):
         * - required
         * - minimum 5 characters
         * - maximum 80 characters
         * - only allowed characters
         *
         * @param {boolean} showError
         * @returns {boolean}
         */
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


        /**
         * Validates that the user accepted the loan terms.
         *
         * @param {boolean} showError
         * @returns {boolean}
         */
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

        /**
         * Validates the special-case reason field.
         *
         * Rules only apply when special case is enabled:
         * - required
         * - minimum 10 characters
         * - maximum 500 characters
         * - only allowed characters
         *
         * @param {boolean} showError
         * @returns {boolean}
         */
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


        /**
         * Validates the full loan form.
         *
         * Checks:
         * - full name
         * - terms acceptance
         * - pickup date
         * - pickup time
         * - return date for special cases
         * - special reason for special cases
         *
         * @param {boolean} showErrors
         * @returns {boolean}
         */
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

        /**
         * Enables or disables the submit button depending on whether
         * the current loan form is valid.
         */
        function updateLoanSubmitButtonState() {
            if (!submitLoanRequest) return;
            const formIsValid = validateLoanForm(false);
            submitLoanRequest.disabled = !formIsValid;
        }

        /**
         * Toggles extra fields required for special-case requests.
         * Also clears those fields when special case is disabled.
         * This is to make the website more dynamic.
         */
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

        /**
         * Live validation for full name field.
         */
        if (loanFullName) {
            loanFullName.addEventListener('input', () => {
                loanFullName.value = loanFullName.value.slice(0, 80);
                validateLoanFullNameField(true);
                updateLoanSubmitButtonState();
            });
        }

        /**
         * Live validation for terms checkbox.
         */
        if (loanTermsCheck) {
            loanTermsCheck.addEventListener('change', () => {
                clearLoanFieldError(loanTermsCheck, loanTermsError);
                updateLoanSubmitButtonState();
            });
        }

        /**
         * Live validation for pickup date.
         */
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

        /**
         * Live validation for pickup time.
         */
        if (pickupTimeBlock) {
            pickupTimeBlock.addEventListener('change', () => {
                clearLoanFieldError(pickupTimeBlock, pickupTimeBlockError);
                updateLoanSubmitButtonState();
            });
        }

        /**
         * Live validation for return date in special cases.
         */
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

        /**
         * Live validation for special-case reason.
         */
        if (specialReason) {
            specialReason.addEventListener('input', () => {
                specialReason.value = specialReason.value.slice(0, 500);
                validateSpecialReasonField(true);
                updateLoanSubmitButtonState();
            });
        }

        /**
         * Final validation gate before loan form submission.
         */
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


        /**
         * Initial setup for legacy loan validation flow.
         */
        setMinDates();
        updateLoanSubmitButtonState();
    }

});
