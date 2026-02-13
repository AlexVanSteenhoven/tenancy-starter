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
            '@lang': path.resolve(__dirname, 'lang'),
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@lib': path.resolve(__dirname, 'resources/js/lib'),
            '@hooks': path.resolve(__dirname, 'resources/js/hooks'),
            '@layouts': path.resolve(__dirname, 'resources/js/layouts'),
            '@pages': path.resolve(__dirname, 'resources/js/pages'),
            '@types': path.resolve(__dirname, 'resources/js/types'),
            '@utils': path.resolve(__dirname, 'resources/js/utils'),
        },
    },
    esbuild: {
        jsx: 'automatic',
    },
});
