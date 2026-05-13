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
import fs from 'fs';
import path from 'path';

/**
 * Gets all JavaScript files inside a directory recursively.
 *
 * This prevents having to manually add every JS file
 * from resources/js to the Vite input list.
 */
function getJsFiles(dir) {
    let results = [];

    fs.readdirSync(dir).forEach((file) => {
        const fullPath = path.join(dir, file);

        if (fs.statSync(fullPath).isDirectory()) {
            results = results.concat(getJsFiles(fullPath));
            function getJsFiles(dir) {
                let results = [];

                fs.readdirSync(dir).forEach((file) => {
                    const fullPath = path.join(dir, file);

                    if (fs.statSync(fullPath).isDirectory()) {
                        results = results.concat(getJsFiles(fullPath));
                    } else if (file.endsWith('.js') && file !== 'test-posts.js') {
                        results.push(fullPath.replace(/\\/g, '/'));
                    }
                });

                return results;
            }
            results.push(fullPath.replace(/\\/g, '/'));
        }
    });

    return results;
}

/**
 * JavaScript entry assets.
 * Includes every .js file inside resources/js.
 */
const jsFiles = getJsFiles('resources/js');

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
            input: [
                'resources/css/app.css',
                ...jsFiles,
            ],

            /**
             * Enables auto refresh
             */
            refresh: true,
        }),
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
