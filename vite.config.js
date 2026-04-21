/**
 * Initializes the Vite configuration for the Laravel application.
 *
 * Handles:
 * - defines the asset entry points that Vite uses when building the app
 * - integrates Laravel with Vite
 * - enables automatic refresh on changes
 * - configures server watch behavior and controls which files trigger reloads
 */

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';


export default defineConfig({
    /**
     * Laravel Vite plugin
     * Handles asset bundling and Blade integration.
     */
    plugins: [
        laravel({
            /**
             * Entry assets
             * Files compiled and served by Vite.
             * Defines the root files used for bundling.
             */
            input: ['resources/css/app.css', 'resources/js/app.js',
                    'resources/js/pages/marketplace_profanity.js', 'resources/js/pages/messages_profanity.js'],
            /**
             * Enables auto refresh
             */
            refresh: true,
        })
    ],

    /**
     * Development server configuration
     */
    server: {

        /**
         * Watch settings
         * Prevents unnecessary reloads from compiled Blade view files.
         */
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
