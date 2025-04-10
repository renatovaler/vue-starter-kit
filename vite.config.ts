import vue from '@vitejs/plugin-vue';
import autoprefixer from 'autoprefixer';
import laravel from 'laravel-vite-plugin';
import { resolve } from 'node:path';
import path from 'path';
import tailwindcss from 'tailwindcss';
import { defineConfig } from 'vite';

const HOST = 'zcc7kx-5173.csb.app';

export default defineConfig({
    server: {
        allowedHosts: true,
        host: '0.0.0.0',
        watch: {
            usePolling: true,
        },
        port: 5173,
        strictPort: true,
        cors: {
            origin: [
                // Supports: SCHEME://DOMAIN.laravel[:PORT]
                /^https?:\/\/.*\.csb.app(:\d+)?$/,
            ],
            methods: ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            allowedHeaders: ['Content-Type', 'Authorization'],
            credentials: true,
        },
        hmr: {
            protocol: 'wss',
            host: HOST,
            clientPort: 443,
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
