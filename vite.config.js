import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import vuetify from 'vite-plugin-vuetify';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/main.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        vuetify({
            autoImport: true,
            // احذف هذا الجزء بالكامل ↓
            // styles: {
            //     configFile: 'resources/js/styles/settings.scss',
            // },
        }),
    ],
    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js',
                import.meta.url)),
            '~': fileURLToPath(new URL('./resources',
                import.meta.url)),
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        hmr: {
            host: 'localhost',
        },
    },
});