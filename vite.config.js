import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        VitePWA({
            registerType: 'autoUpdate',
            includeAssets: ['favicon-32x32.png', 'icons/*.png'],
            manifest: {
                name: 'JoyTrack',
                short_name: 'JoyTrack',
                description: 'Manajemen Keuangan & Kendaraan',
                theme_color: '#059669',
                background_color: '#ffffff',
                display: 'standalone',
                start_url: '/dashboard',
                icons: [
                    { src: '/icons/icon-192x192.png', sizes: '192x192', type: 'image/png' },
                    { src: '/icons/icon-512x512.png', sizes: '512x512', type: 'image/png' },
                    { src: '/icons/icon-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
                ],
                shortcuts: [
                    { name: 'Transaksi', url: '/transactions', description: 'Lihat transaksi' },
                    { name: 'Tambah BBM', url: '/fuel-records/create', description: 'Catat BBM' },
                    { name: 'Laporan', url: '/reports/finance', description: 'Laporan keuangan' },
                ],
            },
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
                navigateFallback: '/offline',
                navigateFallbackAllowlist: [/^\/$/],
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/fonts\.bunny\.net\/.*/i,
                        handler: 'CacheFirst',
                        options: { cacheName: 'fonts', expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 30 } },
                    },
                    {
                        urlPattern: /^https:\/\/cdn\.jsdelivr\.net\/.*/i,
                        handler: 'CacheFirst',
                        options: { cacheName: 'cdn', expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 7 } },
                    },
                ],
            },
        }),
    ],
});
