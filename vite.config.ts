import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig } from 'vite';

const PORT = 5173;
const HOST = '0.0.0.0';

const CLIENT_PORT = 5173;
const CLIENT_HOST = 'localhost';

export default defineConfig({
    server: {
        allowedHosts: true,
        host: HOST,
        port: PORT,
        strictPort: true, // if you want Vite to fail if the port is already in use
        cors: {
            origin: [
                // Supports: SCHEME://DOMAIN.laravel[:PORT]
                /^https?:\/\/.*\.csb.app(:\d+)?$/,
            ],
        },
        hmr: {
            host: CLIENT_HOST,
            protocol: 'wss',
            clientPort: CLIENT_PORT,
        },
    },
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
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
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            'ziggy-js': resolve(__dirname, 'vendor/tightenco/ziggy'),
        },
    },
    css: {
        postcss: {
            plugins: [tailwindcss, autoprefixer],
        },
    },
});
