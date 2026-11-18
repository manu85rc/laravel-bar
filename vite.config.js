import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/pos.js',
                'resources/js/libs/alpine.js',
                'resources/js/libs/fontawesome.js',
                'resources/js/libs/flatpickr.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
        VitePWA({
            // Integración con laravel/vite-plugin: los archivos PWA viven en /build
            buildBase: '/build/',
            // El SW corre desde la raíz del dominio aunque los archivos estén en /build.
            // Requiere el header `Service-Worker-Allowed: /` en el servidor (ver nginx.conf).
            scope: '/',
            base: '/',
            registerType: 'autoUpdate',
            injectRegister: null, // registro manual en app.js
            registerOptions: {
                skipWaiting: true,
                clientsClaim: true,
                updateViaCache: 'none',
            },
            includeAssets: [], // Laravel no usa publicDir en el build (evita prefijo /build incorrecto)
            manifest: false, // servimos el manifest dinámicamente desde Laravel
            devOptions: {
                enabled: false,
            },
            workbox: {
                cleanupOutdatedCaches: true,
                globPatterns: ['**/*.{js,css,woff2,png,svg,ico}'],
                navigateFallback: '/offline',
                navigateFallbackDenylist: [/^\/api\//, /^\/auth\//, /^\/broadcasting\//, /^\/ping$/, /^\/pedidos\/create$/],
                maximumFileSizeToCacheInBytes: 4000000,
                // Manejadores de Web Push (push/notificationclick). Deben vivir
                // en /public para que workbox los inyecte en el sw.js generado.
                importScripts: ['/push-handlers.js'],
                runtimeCaching: [
                    {
                        // POS disponible offline: red primero con fallback a caché.
                        // Al visitar el POS online se cachea su HTML; sin conexión se
                        // sirve ese HTML y el app.js hidrata desde snapshots en IndexedDB.
                        urlPattern: ({ url }) =>
                            url.pathname === '/pedidos/create' ||
                            url.pathname.startsWith('/pedidos/'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'pos-pages',
                            networkTimeoutSeconds: 2,
                            expiration: { maxEntries: 30, maxAgeSeconds: 60 * 60 * 12 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        // Página "sin conexión": se calienta al registrar el SW para
                        // que navigateFallback pueda servirla la primera vez.
                        urlPattern: ({ url }) => url.pathname === '/offline',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'offline-page',
                            networkTimeoutSeconds: 2,
                            expiration: { maxEntries: 5, maxAgeSeconds: 60 * 60 * 24 * 7 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        // Cualquier página visitada queda disponible sin red.
                        urlPattern: ({ url, request, sameOrigin }) =>
                            sameOrigin && request.mode === 'navigate' &&
                            !url.pathname.startsWith('/api/') &&
                            !url.pathname.startsWith('/auth/') &&
                            url.pathname !== '/ping',
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'app-pages',
                            networkTimeoutSeconds: 3,
                            expiration: { maxEntries: 60, maxAgeSeconds: 60 * 60 * 24 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: ({ request }) => request.destination === 'image',
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'images',
                            expiration: { maxEntries: 120, maxAgeSeconds: 60 * 60 * 24 * 30 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: ({ url }) => url.pathname.startsWith('/storage/'),
                        handler: 'CacheFirst',
                        options: {
                            cacheName: 'storage-images',
                            expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 * 30 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: ({ url }) =>
                            url.pathname.startsWith('/api/offline/') && !url.pathname.endsWith('/csrf'),
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'offline-api',
                            networkTimeoutSeconds: 3,
                            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: ({ url }) => url.hostname === 'cdnjs.cloudflare.com',
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'cdn-css',
                            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 30 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                    {
                        urlPattern: ({ url }) =>
                            url.hostname.includes('fonts.googleapis.com') ||
                            url.hostname.includes('fonts.gstatic.com'),
                        handler: 'StaleWhileRevalidate',
                        options: {
                            cacheName: 'google-fonts',
                            expiration: { maxEntries: 20, maxAgeSeconds: 60 * 60 * 24 * 365 },
                            cacheableResponse: { statuses: [0, 200] },
                        },
                    },
                ],
            },
        }),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
