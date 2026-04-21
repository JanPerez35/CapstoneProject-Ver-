import { findProfanity } from '../utils/profanity_checker';

/**
 * Initializes marketplace profanity validation once the DOM is fully loaded.
 *
 * Responsibilities:
 * - validates title and description fields for inappropriate language
 * - disables send button when profanity is detected
 * - keeps profanity validation synchronized with user input and modal state
 * - prevents base validation messages from overriding profanity messages
 */
document.addEventListener('DOMContentLoaded', () => {
    /**
     * Form controls
     * Main inputs and action button used in marketplace post creation.
     */
    const publishBtn = document.getElementById('publishBtn');
    const titleInput = document.getElementById('postTitle');
    const descriptionInput = document.getElementById('postDescription');

    const titleBaseError = document.getElementById('postTitleError');
    const descriptionBaseError = document.getElementById('postDescriptionError');

    /**
     * Profanity validation error containers
     * These display profanity-specific validation messages.
     */
    const titleProfanityError = document.getElementById('postTitleProfanityError');
    const descriptionProfanityError = document.getElementById('postDescriptionProfanityError');

    /**
     * Stops execution if the required marketplace form elements
     * are not available on the page.
     */
    if (
        !publishBtn ||
        !titleInput ||
        !descriptionInput ||
        !titleBaseError ||
        !descriptionBaseError ||
        !titleProfanityError ||
        !descriptionProfanityError
    ) {
        return;
    }

    /**
     * Clears the profanity error message for a specific field.
     *
     * @param {HTMLElement} errorElement - The element displaying the profanity error message.
     */
    function clearProfanityFieldError(errorElement) {
        errorElement.textContent = '';
    }

    /**
     * Gives profanity validation priority over base validation.
     * Marks the field invalid, clears the base error, and shows
     * the profanity-specific error message.
     *
     * @param {HTMLInputElement|HTMLTextAreaElement} field - The input field being validated.
     * @param {HTMLElement} baseErrorElement - The element showing the base validation error.
     * @param {HTMLElement} profanityErrorElement - The element showing the profanity validation error.
     * @param {string} message - The profanity validation message to display.
     */
    function setProfanityPriority(field, baseErrorElement, profanityErrorElement, message) {
        field.classList.add('is-invalid');
        baseErrorElement.textContent = '';
        profanityErrorElement.textContent = message;
    }

    /**
     * Clears the profanity error message for the title field.
     */
    function clearTitleProfanityIfNeeded() {
        clearProfanityFieldError(titleProfanityError);
    }

    /**
     * Clears the profanity error message for the description field.
     */
    function clearDescriptionProfanityIfNeeded() {
        clearProfanityFieldError(descriptionProfanityError);
    }

    /**
     * Validates the title field for profanity.
     * If profanity is found, the profanity error is shown
     * instead of the base validation error.
     *
     * @returns {boolean} True if valid, false if profanity is detected.
     */
    function validateTitleProfanity() {
        const value = titleInput.value.trim();

        clearTitleProfanityIfNeeded();

        if (!value) {
            return true;
        }

        const matchedWord = findProfanity(value);

        if (matchedWord) {
            setProfanityPriority(
                titleInput,
                titleBaseError,
                titleProfanityError,
                'El título contiene lenguaje inapropiado.'
            );
            return false;
        }

        return true;
    }

    /**
     * Validates the description field for profanity.
     * If profanity is found, the profanity error is shown
     * instead of the base validation error.
     *
     * @returns {boolean} True if valid, false if profanity is detected.
     */
    function validateDescriptionProfanity() {
        const value = descriptionInput.value.trim();

        clearDescriptionProfanityIfNeeded();

        if (!value) {
            return true;
        }

        const matchedWord = findProfanity(value);

        if (matchedWord) {
            setProfanityPriority(
                descriptionInput,
                descriptionBaseError,
                descriptionProfanityError,
                'La descripción contiene lenguaje inapropiado.'
            );
            return false;
        }

        return true;
    }

    /**
     * Re-applies profanity priority when profanity errors exist.
     * Prevents base validation messages from replacing
     * profanity-specific messages.
     */
    function enforceProfanityPriority() {
        const titleHasProfanity = titleProfanityError.textContent.trim() !== '';
        const descriptionHasProfanity = descriptionProfanityError.textContent.trim() !== '';

        if (titleHasProfanity) {
            titleBaseError.textContent = '';
            titleInput.classList.add('is-invalid');
        }

        if (descriptionHasProfanity) {
            descriptionBaseError.textContent = '';
            descriptionInput.classList.add('is-invalid');
        }
    }

    /**
     * Runs profanity validation for all supported fields
     * and then reapplies profanity priority.
     */
    function updateProfanityState() {
        validateTitleProfanity();
        validateDescriptionProfanity();
        enforceProfanityPriority();
    }

    /**
     * Delays profanity validation until after base validation finishes.
     * This keeps both validation systems from conflicting.
     *
     * @param {Function} callback - The function to run after base validation completes.
     */
    function runAfterBaseValidation(callback) {
        setTimeout(callback, 0);
    }

    /**
     * Revalidates profanity as the user types in the title field.
     */
    titleInput.addEventListener('input', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    /**
     * Revalidates profanity as the user types in the description field.
     */
    descriptionInput.addEventListener('input', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    /**
     * Revalidates profanity when the title field loses focus.
     */
    titleInput.addEventListener('blur', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    /**
     * Revalidates profanity when the description field loses focus.
     */
    descriptionInput.addEventListener('blur', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    /**
     * Marketplace create post modal reference
     * Used to trigger profanity validation when the modal opens.
     */
    const createPostModal = document.getElementById('createPostModal');

    /**
     * Re-applies profanity validation when the create post modal is shown.
     */
    if (createPostModal) {
        createPostModal.addEventListener('shown.bs.modal', () => {
            runAfterBaseValidation(updateProfanityState);
        });
    }

    /**
     * Observes changes to base validation error containers.
     * This ensures profanity errors remain visible even if
     * the base validation logic updates the DOM.
     */
    const observer = new MutationObserver(() => {
        enforceProfanityPriority();
    });

    observer.observe(titleBaseError, {
        childList: true,
        characterData: true,
        subtree: true
    });

    observer.observe(descriptionBaseError, {
        childList: true,
        characterData: true,
        subtree: true
    });
});
