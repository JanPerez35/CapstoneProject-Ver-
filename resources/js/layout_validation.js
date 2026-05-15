/**
 * Initializes layout-level interactive behavior once the DOM is ready.
 *
 * Responsibilities:
 * - validates the loan request form inside the cart modal
 * - controls special-case fields visibility
 * - manages cart quantity buttons and cart badge updates
 * - handles cart item removal confirmation
 * - validates uploaded PDF files for terms and conditions
 * - reopens the cart modal when requested by backend session state
 * - shows global success toasts after page reloads
 */

import * as bootstrap from "bootstrap";
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es.js";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener('DOMContentLoaded', () => {

    /**
     * Activates tool tip
     */
    const tooltipTriggerList = document.querySelectorAll('[data-bs-title]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('#cartModal form');
    const body = document.body;

    /**
     * If the cart form is not present on the current page,
     * the script still shows any pending toast notifications
     * and exits early to avoid null-reference errors.
     */
    if (!form) {
        showGlobalToasts(body);
        return;
    }

    /**
     * Form field references
     *
     * Main borrowing form fields and related UI elements used throughout
     * the validation and modal behavior logic.
     */
    const pickupDate = document.getElementById('pickup_date');
    const pickupDateIcon = document.getElementById('pickupDateIcon');
    const pickupTime = document.getElementById('pickup_time');
    const specialCase = document.getElementById('special_case');
    const specialCaseFields = document.getElementById('specialCaseFields');
    const returnDate = document.getElementById('return_date');
    const returnDateIcon = document.getElementById('returnDateIcon');
    const specialReason = document.getElementById('special_reason');
    const acceptTerms = document.getElementById('accept_terms');
    const submitBtn = document.getElementById('submitLoanRequest');
    const cartCount = document.getElementById('cartCount');
    const cartButton = document.querySelector('[data-bs-target="#cartModal"]');
    const cartModal = document.getElementById('cartModal');
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');

    /**
    * Validation message containers/variables
    *
    * These elements receive inline error messages during client-side validation.
    * */
    const pickupDateError = document.getElementById('pickup_date_error');
    const pickupTimeError = document.getElementById('pickup_time_error');
    const returnDateError = document.getElementById('return_date_error');
    const specialReasonError = document.getElementById('special_reason_error');
    const acceptTermsError = document.getElementById('accept_terms_error');

    /**
    * Cart item removal modal references
    *
    * Used to confirm before deleting an item from the cart.
    * */
    const removeCartConfirmModal = document.getElementById('removeCartConfirmModal');
    const removeCartConfirmText = document.getElementById('removeCartConfirmText');
    const confirmRemoveCartItem = document.getElementById('confirmRemoveCartItem');

    /**
     * Terms PDF upload references
     *
     * Used in the terms modal for validating and submitting a replacement PDF.
     */
    const openTermsPdfPicker = document.getElementById('openTermsPdfPicker');
    const termsPdfInput = document.getElementById('termsPdfInput');
    const termsPdfError = document.getElementById('termsPdfError');
    const termsPdfSelectedName = document.getElementById('termsPdfSelectedName');
    const updateTermsForm = document.getElementById('updateTermsForm');
    const confirmTermsUpdateModal = document.getElementById('confirmTermsUpdateModal');
    const confirmTermsUpdateBtn = document.getElementById('confirmTermsUpdateBtn');

    /**
     * Allowed characters for the special-case reason field.
     * Restricts input to letters, accented characters, numbers,
     * spaces, periods, commas, and hyphens.
     */
    const allowedReasonRegex = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9 .,\-]+$/;

    /**
     * Stores the ID of the hidden delete form corresponding
     * to the cart item selected for removal.
     */
    let removeCartFormId = null;


    /**
     * Attaches the floating scroll-to-top button behavior.
     *
     * The button stays hidden while the user is near the top of the page.
     * Once the user scrolls down past a small threshold, the button becomes visible.
     * When clicked, it smoothly returns the user to the top of the page.
     */
    function attachScrollToTopButton() {
        if (!scrollToTopBtn) return;

        /**
         * Shows or hides the scroll-to-top button depending on the
         * current vertical scroll position of the page.
         */
        function toggleScrollButton() {
            if (window.scrollY > 300) {
                scrollToTopBtn.classList.remove('d-none');
            } else {
                scrollToTopBtn.classList.add('d-none');
            }
        }

        scrollToTopBtn.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        window.addEventListener('scroll', toggleScrollButton);
        toggleScrollButton();
    }

    /**
     * Displays a validation error by marking the field invalid
     * and writing a message into its paired error element.
     *
     * @param {HTMLElement|null} field - Input field to mark invalid.
     * @param {HTMLElement|null} errorEl - Element where error text is displayed.
     * @param {string} message - Validation message to show.
     */
    function setError(field, errorEl, message) {
        if (field) {
            field.classList.add('is-invalid');

            if (field._flatpickr?.altInput) {
                field._flatpickr.altInput.classList.add('is-invalid');
            }
        }

        if (errorEl) errorEl.textContent = message;
    }

    /**
     * Clears the invalid state and removes any visible error message
     * for a given input field.
     *
     * @param {HTMLElement|null} field - Input field to clear.
     * @param {HTMLElement|null} errorEl - Associated error message container.
     */
    function clearError(field, errorEl) {
        if (field) {
            field.classList.remove('is-invalid');

            if (field._flatpickr?.altInput) {
                field._flatpickr.altInput.classList.remove('is-invalid');
            }
        }

        if (errorEl) errorEl.textContent = '';
    }

    /**
     * Hides and clears the PDF upload validation message
     * for the terms and conditions file picker.
     */
    function clearTermsPdfError() {
        if (termsPdfError) {
            termsPdfError.textContent = '';
            termsPdfError.classList.add('d-none');
        }
    }

    /**
     * Shows a validation message related to the selected terms PDF file.
     *
     * @param {string} message - Error text to display.
     */
    function setTermsPdfError(message) {
        if (termsPdfError) {
            termsPdfError.textContent = message;
            termsPdfError.classList.remove('d-none');
        }
    }

    /**
     * Validates the uploaded terms file by checking that:
     * - a file was selected
     * - the file is a PDF by MIME type or extension
     *
     * @param {File|undefined} file - Uploaded file object.
     * @returns {boolean} True when the file is valid.
     */
    function validateTermsPdfFile(file) {
        if (!file) {
            setTermsPdfError('Debes seleccionar un archivo PDF.');
            return false;
        }

        const isPdfByMime = file.type === 'application/pdf';
        const isPdfByExtension = file.name.toLowerCase().endsWith('.pdf');

        if (!isPdfByMime && !isPdfByExtension) {
            setTermsPdfError('Solo se permiten archivos PDF.');
            return false;
        }
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (file.size > maxSize) {
            setTermsPdfError('El archivo no puede exceder los 2MB.');
            return false;
        }

        clearTermsPdfError();
        return true;
    }

    /**
     * Checks whether a given date falls on a blocked pickup/return day.
     * Blocked days are Friday, Saturday, and Sunday.
     *
     * @param {string} dateString - Date in YYYY-MM-DD format.
     * @returns {boolean} True if the date is not allowed.
     */
    function isBlockedPickupDay(dateString) {
        const date = new Date(`${dateString}T00:00:00`);
        const day = date.getDay();
        //To add context here, the week starts at Sunday which here is represented by a 0.
        return day === 5 || day === 6 || day === 0;
    }

    /**
     * Returns today's date as a YYYY-MM-DD string with time removed.
     *
     * @returns {string} Today's date formatted for date inputs.
     */
    function todayString() {
        const d = new Date();
        d.setHours(0, 0, 0, 0);
        return d.toISOString().split('T')[0];
    }

    /**
     * Calculates the earliest allowed pickup date based on business rules:
     * - pickup starts from tomorrow
     * - if it is already 1:00 PM or later, tomorrow is skipped
     * - Friday, Saturday, and Sunday are not allowed
     *
     * @returns {Date} Next valid pickup date.
     */
    function getNextAllowedPickupDate() {
        const now = new Date();
        const minDate = new Date();
        minDate.setHours(0, 0, 0, 0);

        // Base: tomorrow
        minDate.setDate(minDate.getDate() + 1);

        // If its past 1:00 PM, then the user can't asl for tomorrow
        // and is moved for the next available day.
        if (now.getHours() >= 13) {
            minDate.setDate(minDate.getDate() + 1);
        }

        // Skips fridays, saturdays, and sundays
        while ([5, 6, 0].includes(minDate.getDay())) {
            minDate.setDate(minDate.getDate() + 1);
        }

        return minDate;
    }

    /**
     * Formats a Date object into the YYYY-MM-DD format
     * expected by HTML date inputs.
     *
     * @param {Date} date - JavaScript Date object.
     * @returns {string} Formatted date string.
     */
    function formatDateToInputValue(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    /**
     * Returns the earliest allowed pickup date as an input-ready string.
     *
     * @returns {string} Minimum pickup date in YYYY-MM-DD format.
     */
    function minPickupDateString() {
        return formatDateToInputValue(getNextAllowedPickupDate());
    }

    /**
     * Applies minimum allowed dates to pickup and return date inputs
     * so users cannot select dates earlier than the allowed threshold.
     */
    function setMinDates() {
        const minPickup = minPickupDateString();

        if (pickupDate) {
            pickupDate.min = minPickup;
        }

        if (returnDate) {
            returnDate.min = minPickup;
        }
    }

    /**
     * Cleans tool tip so it doesnt get stuck on opened modals like the cart
     */
    function attachCartTooltipCleanup() {
        if (!cartButton) return;

        cartButton.addEventListener('click', () => {
            const tooltip = bootstrap.Tooltip.getInstance(cartButton);
            if (tooltip) tooltip.hide();
        });

        cartModal?.addEventListener('shown.bs.modal', () => {
            const tooltip = bootstrap.Tooltip.getInstance(cartButton);
            if (tooltip) tooltip.hide();
        });

        cartModal?.addEventListener('hidden.bs.modal', () => {
            const tooltip = bootstrap.Tooltip.getInstance(cartButton);
            if (tooltip) tooltip.hide();

            document.querySelectorAll('.tooltip').forEach(el => el.remove());
        });
    }

    function initializeSpanishDatePickers() {
        const sharedOptions = {
            locale: Spanish,

            /**
             * This is the real value submitted to Laravel.
             * Keep this as Day Month Year so backend validation continues working.
             */
            dateFormat: 'Y-m-d',

            /**
             * This is what the user sees.
             */
            altInput: true,
            altFormat: 'j F Y',

            allowInput: false,
            disableMobile: true,
            minDate: minPickupDateString(),

            /**
             * Disable Friday, Saturday, and Sunday.
             */
            disable: [
                function (date) {
                    const day = date.getDay();
                    return day === 5 || day === 6 || day === 0;
                }
            ]
        };

        if (pickupDate) {
            flatpickr(pickupDate, {
                ...sharedOptions,
                onChange: function () {
                    validatePickupDate(true);

                    if (returnDate?._flatpickr && pickupDate.value) {
                        returnDate._flatpickr.set('minDate', pickupDate.value);
                    }

                    validateReturnDate(true);
                    updateSubmitButtonStateQuietly();
                }
            });
            pickupDateIcon?.addEventListener('click', function () {
                pickupDate._flatpickr?.open();
            });
        }

        if (returnDate) {
            flatpickr(returnDate, {
                ...sharedOptions,
                onChange: function () {
                    validateReturnDate(true);
                    updateSubmitButtonStateQuietly();
                }
            });
            returnDateIcon?.addEventListener('click', function () {
                returnDate._flatpickr?.open();
            });
        }
    }

    /**
     * Shows or hides the special-case fields depending on whether
     * the special-case checkbox is selected.
     *
     * When hidden, it also resets those fields and clears errors.
     */
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

                if (returnDate._flatpickr) {
                    returnDate._flatpickr.clear();
                } else {
                    returnDate.value = '';
                }

                clearError(returnDate, returnDateError);
            }

            if (specialReason) {
                specialReason.required = false;
                specialReason.value = '';
                clearError(specialReason, specialReasonError);
            }
        }
    }

    /**
     * Validates the written reason for a special-case request.
     * Rules:
     * - required only when special case is enabled
     * - must contain only allowed characters
     * - must be between 10 and 500 characters
     *
     * @param {boolean} showErrors - Whether to display inline messages.
     * @returns {boolean} True when valid.
     */
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

    /**
     * Validates the pickup date according to system rules:
     * - required
     * - cannot be earlier than the next allowed business day
     * - cannot be Friday, Saturday, or Sunday
     *
     * @param {boolean} showErrors - Whether to display inline messages.
     * @returns {boolean} True when valid.
     */
    function validatePickupDate(showErrors = true) {
        const minAllowed = minPickupDateString();

        if (showErrors) clearError(pickupDate, pickupDateError);

        if (!pickupDate || !pickupDate.value) {
            if (showErrors) {
                setError(pickupDate, pickupDateError, 'La fecha de recogida es obligatoria.');
            }
            return false;
        }

        if (pickupDate.value < minAllowed) {
            if (showErrors) {
                setError(
                    pickupDate,
                    pickupDateError,
                    'Por logística, esa fecha ya no está disponible. Debes seleccionar el próximo día laborable permitido.'
                );
            }
            return false;
        }

        if (isBlockedPickupDay(pickupDate.value)) {
            if (showErrors) {
                setError(pickupDate, pickupDateError, 'No se permiten viernes, sábados ni domingos.');
            }
            return false;
        }

        return true;
    }

    /**
     * Validates the proposed return date for special-case requests.
     * Rules:
     * - required only for special cases
     * - must be in the future
     * - cannot be Friday, Saturday, or Sunday
     * - cannot be earlier than the pickup date
     *
     * @param {boolean} showErrors - Whether to display inline messages.
     * @returns {boolean} True when valid.
     */
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

        if (isBlockedPickupDay(returnDate.value)) {
            if (showErrors) setError(returnDate, returnDateError, 'No se permiten viernes, sábados ni domingos.');
            return false;
        }

        if (pickupDate?.value && returnDate.value < pickupDate.value) {
            if (showErrors) setError(returnDate, returnDateError, 'La devolución no puede ser antes de la recogida.');
            return false;
        }

        return true;
    }

    /**
     * Validates that the pickup time field has a selected value.
     *
     * @param {boolean} showErrors - Whether to display inline messages.
     * @returns {boolean} True when valid.
     */
    function validatePickupTime(showErrors = true) {
        if (showErrors) clearError(pickupTime, pickupTimeError);

        if (!pickupTime || !pickupTime.value) {
            if (showErrors) setError(pickupTime, pickupTimeError, 'La hora de recogida es obligatoria.');
            return false;
        }

        return true;
    }

    /**
     * Validates that the user accepted the loan terms and conditions.
     *
     * @param {boolean} showErrors - Whether to display inline messages.
     * @returns {boolean} True when checked.
     */
    function validateTerms(showErrors = true) {
        if (showErrors) clearError(acceptTerms, acceptTermsError);

        if (!acceptTerms || !acceptTerms.checked) {
            if (showErrors) setError(acceptTerms, acceptTermsError, 'Debes aceptar los términos y condiciones.');
            return false;
        }

        return true;
    }


    /**
     * Runs all form validations and updates the submit button state.
     *
     * @param {boolean} showErrors - Whether to show validation feedback.
     * @returns {boolean} True only if all required fields are valid.
     */
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

    /**
     * Re-checks form validity without aggressively showing error messages.
     * Used for live UI updates while the user is still filling the form.
     */
    function updateSubmitButtonStateQuietly() {
        const valid = validateForm(false);
        if (submitBtn) submitBtn.disabled = !valid;
    }

    /**
     * Recalculates the cart badge using all hidden cart quantity inputs,
     * then shows or hides the badge depending on whether the total is zero.
     */
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

    /**
     * Attaches increment/decrement controls for each cart item row.
     * Keeps quantities between 1 and the maximum available stock,
     * and updates the visible badge count in real time.
     */
    function attachCartQuantityControls() {
        document.querySelectorAll('.cart-item-row').forEach(row => {
            const decreaseBtn = row.querySelector('.decrease-cart-item');
            const increaseBtn = row.querySelector('.increase-cart-item');
            const qtyDisplay = row.querySelector('.cart-quantity-display');
            const qtyInput = row.querySelector('.cart-quantity-input');
            const errorEl = row.querySelector('.cart-item-error');

            const min = 1;
            const max = Number(row.dataset.maxQuantity || 1);

            /**
             * Clears the validation message for a single cart row.
             */
            function clearItemError() {
                if (errorEl) errorEl.textContent = '';
            }

            /**
             * Shows a validation message for a single cart row.
             *
             * @param {string} message - Error shown under the row controls.
             */
            function setItemError(message) {
                if (errorEl) errorEl.textContent = message;
            }

            /**
             * Synchronizes the cart quantity display and hidden input value
             * for one row, then refreshes the cart badge total.
             *
             * @param {number} newQty - New quantity to apply.
             */
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

    /**
     * Reopens the cart modal after page load when the backend indicates
     * that the previous request should return the user to that modal state.
     * The condition is that if the user deletes an item from the cart but there
     * are still items inside the cart modal reopens unless the user leaves.
     *
     * @param {HTMLElement} bodyEl - Document body carrying dataset flags.
     */
    function reopenCartModalIfNeeded(bodyEl) {
        const shouldReopen = bodyEl?.dataset?.reopenCartModal === '1';
        const cartModalEl = document.getElementById('cartModal');

        if (!shouldReopen || !cartModalEl) return;

        const cartModalInstance = bootstrap.Modal.getOrCreateInstance(cartModalEl);
        cartModalInstance.show();
    }

    /**
     * Attaches behavior for the remove-from-cart confirmation flow.
     * The user first opens a confirmation modal, then the matching hidden
     * delete form is submitted only if removal is confirmed.
     */
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

    /**
     * Displays any success toasts requested through backend session data.
     * Supports cart add, request submit, cart removal, and terms update toasts.
     *
     * @param {HTMLElement} bodyEl - Document body containing session flags in data attributes.
     */
    function showGlobalToasts(bodyEl) {
        const cartSuccess = bodyEl?.dataset?.cartSuccess || '';
        const requestSuccess = bodyEl?.dataset?.requestSuccess || '';
        const cartRemovedSuccess = bodyEl?.dataset?.cartRemovedSuccess || '';

        const cartToastEl = document.getElementById('cartToast');
        const cartToastMessage = document.getElementById('cartToastMessage');

        if (cartSuccess && cartToastEl && cartToastMessage) {
            cartToastMessage.textContent = cartSuccess;
            bootstrap.Toast.getOrCreateInstance(cartToastEl, { delay: 5000 }).show();
        }

        const errorMessage = bodyEl?.dataset?.errorMessage || '';

        const errorToastEl = document.getElementById('errorToast');
        const errorToastMessage = document.getElementById('errorToastMessage');

        if (errorMessage && errorToastEl && errorToastMessage) {
            errorToastMessage.textContent = errorMessage;
            bootstrap.Toast.getOrCreateInstance(errorToastEl, { delay: 5000 }).show();
        }

        const submitToastEl = document.getElementById('submitToast');
        const submitToastMessage = document.getElementById('submitToastMessage');

        if (requestSuccess && submitToastEl && submitToastMessage) {
            submitToastMessage.textContent = requestSuccess;
            bootstrap.Toast.getOrCreateInstance(submitToastEl, { delay: 5000 }).show();
        }

        const cartRemovedToastEl = document.getElementById('cartRemovedToast');
        const cartRemovedToastMessage = document.getElementById('cartRemovedToastMessage');

        if (cartRemovedSuccess && cartRemovedToastEl && cartRemovedToastMessage) {
            cartRemovedToastMessage.textContent = cartRemovedSuccess;
            bootstrap.Toast.getOrCreateInstance(cartRemovedToastEl, { delay: 5000 }).show();
        }

        const termsUpdatedSuccess = bodyEl?.dataset?.termsUpdatedSuccess || '';
        const termsUpdatedToastEl = document.getElementById('termsUpdatedToast');
        const termsUpdatedToastMessage = document.getElementById('termsUpdatedToastMessage');

        if (termsUpdatedSuccess && termsUpdatedToastEl && termsUpdatedToastMessage) {
            termsUpdatedToastMessage.textContent = termsUpdatedSuccess;
            bootstrap.Toast.getOrCreateInstance(termsUpdatedToastEl, { delay: 5000 }).show();
        }
    }

    /**
    * Live field validation listeners
    *
    * These listeners validate fields as the user interacts with them
    * and quietly update whether the submit button should stay enabled.
    * They serve as live/real-time  validation.
    */
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

    /**
     * Prevents the user from exceeding the 500-character limit
     * before the input is actually inserted into the textarea.
     */
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

    /**
     * Terms PDF upload flow
     *
     * Lets the user open the hidden file picker, validates the selected PDF,
     * shows the filename, and asks for confirmation before upload.
     */
    if (openTermsPdfPicker && termsPdfInput) {
        openTermsPdfPicker.addEventListener('click', () => {
            clearTermsPdfError();
            termsPdfInput.click();
        });
    }

    if (termsPdfInput) {
        termsPdfInput.addEventListener('change', () => {
            const file = termsPdfInput.files?.[0];

            if (!validateTermsPdfFile(file)) {
                termsPdfInput.value = '';

                if (termsPdfSelectedName) {
                    termsPdfSelectedName.textContent = '';
                    termsPdfSelectedName.classList.add('d-none');
                }

                return;
            }

            if (termsPdfSelectedName && file) {
                termsPdfSelectedName.textContent = `Archivo seleccionado: ${file.name}`;
                termsPdfSelectedName.classList.remove('d-none');
            }

            if (confirmTermsUpdateModal) {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(confirmTermsUpdateModal);
                modalInstance.show();
            }
        });
    }

    if (confirmTermsUpdateBtn) {
        confirmTermsUpdateBtn.addEventListener('click', () => {
            const file = termsPdfInput?.files?.[0];

            if (!validateTermsPdfFile(file)) {
                return;
            }

            updateTermsForm?.submit();
        });
    }

    /**
     * Validation checkpoint before the cart loan form submits.
     * Prevents submission if any required client-side rule fails.
     */
    form.addEventListener('submit', function (e) {
        toggleSpecialCaseFields();

        if (!validateForm(true)) {
            e.preventDefault();
        }
    });

    const termsErrorEl = document.querySelector('#termsPdfError');

    if (termsErrorEl && termsErrorEl.textContent.trim() !== '') {
        const modal = document.getElementById('termsModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }

    /**
    * Initial page setup
    *
    * Applies default form state, binds cart interactions, restores modal state,
    * updates counters, and shows any toast messages waiting from the backend.
    */
    setMinDates();
    initializeSpanishDatePickers();
    toggleSpecialCaseFields();
    attachCartQuantityControls();
    attachCartTooltipCleanup();
    attachScrollToTopButton();
    attachRemoveCartConfirmEvents();
    updateCartBadgeFromRows();
    updateSubmitButtonStateQuietly();
    reopenCartModalIfNeeded(body);
    showGlobalToasts(body);
});
