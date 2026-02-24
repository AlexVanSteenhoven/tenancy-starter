import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@assets': path.resolve(__dirname, 'resources/assets'),
            '@lang': path.resolve(__dirname, 'lang'),
            '@styles': path.resolve(__dirname, 'resources/css'),
            '@hooks': path.resolve(__dirname, 'resources/js/hooks'),
            '@utils': path.resolve(__dirname, 'resources/js/utils'),
            '@lib': path.resolve(__dirname, 'resources/js/lib'),
            '@types': path.resolve(__dirname, 'resources/js/types'),
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
});
