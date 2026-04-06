import { findProfanity } from '../utils/profanity_checker.js';

document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('chatMessageInput');
    const sendBtn = document.getElementById('sendChatMessageBtn');
    const errorEl = document.getElementById('chatMessageError');

    if (!input || !sendBtn || !errorEl) return;

    function setProfanityError(message) {
        input.classList.add('is-invalid');
        errorEl.textContent = message;
        errorEl.dataset.errorType = 'profanity';
        sendBtn.disabled = true;
    }

    function clearProfanityError() {
        if(errorEl.dataset.errorType === 'profanity') {
            input.classList.remove('is-invalid');
            errorEl.textContent = '';
            delete errorEl.dataset.errorType;
        }
    }

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


    input.addEventListener('input', validateMessageProfanity);
    input.addEventListener('blur', () => {
        if (!input.value.trim()) {
            clearProfanityError();
            return;
        }

        validateMessageProfanity();
    });

});
