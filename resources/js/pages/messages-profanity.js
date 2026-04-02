import { findProfanity } from '../utils/profanity-checker';

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatMessageInput');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const errorEl = document.getElementById('chatMessageError');

    if (!input || !sendBtn || !errorEl) return;

    function setFieldError(message) {
        input.classList.add('is-invalid');
        errorEl.textContent = message;
    }

    function clearFieldError() {
        input.classList.remove('is-invalid');
        errorEl.textContent = '';
    }

    function validateMessageBase() {
        const value = input.value.trim();

        clearFieldError();

        if (!value) {
            setFieldError('El mensaje no puede estar vacío.');
            return false;
        }

        return true;
    }

    function validateMessageProfanity() {
        const value = input.value.trim();

        if (!value) {
            return true;
        }

        const matchedWord = findProfanity(value);

        if (matchedWord) {
            setFieldError('El mensaje contiene lenguaje inapropiado.');
            return false;
        }

        return true;
    }

    function updateMessageValidationState() {
        const isBaseValid = validateMessageBase();

        if (!isBaseValid) {
            sendBtn.disabled = true;
            return;
        }

        const isProfanityValid = validateMessageProfanity();
        sendBtn.disabled = !isProfanityValid;
    }

    input.addEventListener('input', () => {
        updateMessageValidationState();
    });

    input.addEventListener('blur', () => {
        updateMessageValidationState();
    });

    updateMessageValidationState();
});
