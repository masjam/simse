import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
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
        VitePWA({
            registerType: 'autoUpdate',
            injectRegister: 'auto',
            manifest: {
                name: 'SIMSE SD Muhammadiyah Al Mujahidin',
                short_name: 'SIMSE SDAM',
                description: 'Sistem Informasi Manajemen Sekolah SD Muhammadiyah Al Mujahidin Wonosari',
                theme_color: '#0f766e',
                background_color: '#ffffff',
                display: 'standalone',
                start_url: '/',
                scope: '/',
                icons: [
                    {
                        src: '/icons/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                    },
                    {
                        src: '/icons/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png',
                        purpose: 'any maskable',
                    },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                navigateFallback: '/offline',
                navigateFallbackDenylist: [
                    /^\/api/,
                    /^\/siswa/,
                    /^\/guru/,
                    /^\/ortu/,
                    /^\/admin/,
                    /^\/bendahara/,
                    /^\/kantin/,
                    /^\/staf/,
                ],
            },
        }),
    ],
});
