import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';


export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js',
                    'resources/js/pages/marketplace_profanity.js', 'resources/js/pages/messages_profanity.js',
                     'resources/js/pages/marketplace.js', 'resources/js/pages/marketplace_reports.js'],
            refresh: true,
        })
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
