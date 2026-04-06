import { PROFANITY_WORDS } from '../config/profanity_words.js';

function normalizeText(text = '') {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/n\u0303/g, 'ñ')
        .replace(/N\u0303/g, 'ñ')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[@]/g, 'a')
        .replace(/[0]/g, 'o')
        .replace(/[1]/g, 'i')
        .replace(/[3]/g, 'e')
        .replace(/[4]/g, 'a')
        .replace(/[5]/g, 's')
        .replace(/[7]/g, 't')
        .replace(/[^a-zñ0-9\s]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

function escapeRegExp(text) {
    return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function buildSpacedPattern(word) {
    const lettersOnly = word.replace(/\s+/g, '');
    const escapedLetters = lettersOnly.split('').map((char) => escapeRegExp(char));
    return new RegExp(`\\b${escapedLetters.join('\\s*')}\\b`, 'i');
}

export function findProfanity(text = '') {
    const normalized = normalizeText(text);

    for (const word of PROFANITY_WORDS) {
        const normalizedWord = normalizeText(word);
        if (!normalizedWord) continue;

        const exactPattern = new RegExp(`\\b${escapeRegExp(normalizedWord)}\\b`, 'i');
        const spacedPattern = buildSpacedPattern(normalizedWord);

        if (exactPattern.test(normalized) || spacedPattern.test(normalized)) {
            return normalizedWord;
        }
    }

    return null;
}
