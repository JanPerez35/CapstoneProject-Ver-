import { findProfanity } from '../utils/profanity_checker';

document.addEventListener('DOMContentLoaded', () => {
    const publishBtn = document.getElementById('publishBtn');
    const titleInput = document.getElementById('postTitle');
    const descriptionInput = document.getElementById('postDescription');

    const titleBaseError = document.getElementById('postTitleError');
    const descriptionBaseError = document.getElementById('postDescriptionError');

    const titleProfanityError = document.getElementById('postTitleProfanityError');
    const descriptionProfanityError = document.getElementById('postDescriptionProfanityError');

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

    function clearProfanityFieldError(errorElement) {
        errorElement.textContent = '';
    }

    function setProfanityPriority(field, baseErrorElement, profanityErrorElement, message) {
        field.classList.add('is-invalid');
        baseErrorElement.textContent = '';
        profanityErrorElement.textContent = message;
    }

    function clearTitleProfanityIfNeeded() {
        clearProfanityFieldError(titleProfanityError);
    }

    function clearDescriptionProfanityIfNeeded() {
        clearProfanityFieldError(descriptionProfanityError);
    }

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

    function updateProfanityState() {
        validateTitleProfanity();
        validateDescriptionProfanity();
        enforceProfanityPriority();
    }

    function runAfterBaseValidation(callback) {
        setTimeout(callback, 0);
    }

    titleInput.addEventListener('input', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    descriptionInput.addEventListener('input', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    titleInput.addEventListener('blur', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    descriptionInput.addEventListener('blur', () => {
        runAfterBaseValidation(updateProfanityState);
    });

    const createPostModal = document.getElementById('createPostModal');

    if (createPostModal) {
        createPostModal.addEventListener('shown.bs.modal', () => {
            runAfterBaseValidation(updateProfanityState);
        });
    }

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
