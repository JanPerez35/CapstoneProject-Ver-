import { findProfanity } from '../utils/profanity_checker.js';

/**
 * Initializes chat message profanity validation once the DOM is fully loaded.
 *
 * Responsibilities:
 * - validates chat message input for inappropriate language
 * - displays profanity-specific error messages
 * - disables send button when profanity is detected
 * - clears profanity errors when input becomes valid or empty
 *
 */
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatMessageInput');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const errorEl = document.getElementById('chatMessageError');
    const chatMessageGroup = document.getElementById('chatMessageGroup');

    /**
     * Stops execution if required chat elements are not present.
     */
    if (!input || !sendBtn || !errorEl || !chatMessageGroup) return;

    /**
     * Sets a profanity validation error on the chat input.
     *
     * @param {string} message - The error message to display.
     */
    function setProfanityError(message) {
        input.classList.add('is-invalid');
        chatMessageGroup.classList.remove('border-dark');
        chatMessageGroup.classList.add('border-danger');
        errorEl.textContent = message;
        errorEl.dataset.errorType = 'profanity';
        sendBtn.disabled = true;
    }

    /**
     * Clears the profanity validation error if it exists.
     */
    function clearProfanityError() {
        if(errorEl.dataset.errorType === 'profanity') {
            input.classList.remove('is-invalid');
            chatMessageGroup.classList.remove('border-danger');
            chatMessageGroup.classList.add('border-dark');
            errorEl.textContent = '';
            delete errorEl.dataset.errorType;
        }
    }

    /**
     * Validates the chat message input for profanity.
     *
     * @returns {boolean} True if the message is valid, false if profanity is detected.
     */
    function validateMessageProfanity() {
        const value = input.value.trim();

        if (!value) {
            clearProfanityError();
            return true;
        }
        const matchedWord = findProfanity(value);

        if (matchedWord){
            setProfanityError('El mensaje contiene lenguaje inapropiado.');
            sendBtn.disabled = true;
            return false;
        }
        clearProfanityError();
        return true;
    }

    /**
     * Revalidates profanity as the user types in the chat input.
     */
    input.addEventListener('input', validateMessageProfanity);

    /**
     * Revalidates profanity when the input loses focus.
     * Clears the error if the field is empty.
     */
    input.addEventListener('blur', () => {
        if (!input.value.trim()) {
            clearProfanityError();
            return;
        }

        validateMessageProfanity();
    });

});
