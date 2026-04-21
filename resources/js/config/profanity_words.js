/**
 * List of prohibited words used for client-side profanity validation.
 *
 * Responsibilities:
 * - defines restricted words in Spanish and English
 * - used to detect inappropriate language in user input
 * - supports validation for marketplace and messaging features
 *
 * @type {string[]}
 *
 * Note:
 *  * - words are matched as lowercase values
 *  * - input should be normalized before validation
 *  * - list can be extended as needed
 */
export const PROFANITY_WORDS = [
    /**
     * Spanish profanity words
     * Includes common offensive terms, slurs, variations, and plural forms.
     */

    'puñeta',
    'carajo',
    'cabron',
    'cabrona',
    'cabrón',
    'pendejo',
    'pendeja',
    'bicho',
    'coño',
    'cojones',
    'mamabicho',
    'mamaguevo',
    'hijo de puta',
    'hijueputa',
    'imbecil',
    'puta',
    'puto',
    'mierda',
    'joder',
    'joda',
    'jodete',
    'puneta',
    'mama ñema',
    'mama pinga',
    'maricon',
    'marica',
    'matate',
    'muerete',
    'retrasado',
    'cabrones',
    'cabronazo',
    'pendejos',
    'pendejas',
    'bichos',
    'putas',
    'putos',
    'mamabichos',
    'maricones',
    'bastardo',
    'pene',



    /**
     * English profanity words
     * Includes common offensive terms, slurs, variations, and plural forms.
     */

    'fuck',
    'shit',
    'bitch',
    'bitches',
    'asshole',
    'assholes',
    'bastard',
    'bastards',
    'damn',
    'crap',
    'slut',
    'whore',
    'faggot',
    'nigga',
    'nigger',
    'fag',
    'retard',
    'cunt',
    'chink',
    'dumbass',
    'coon',
    'kill yourself',

];
