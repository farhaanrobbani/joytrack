# PWA

JoyTrack dapat di-install sebagai Progressive Web App.

## Fitur

- Installable (manifest `public/manifest.webmanifest` via `vite-plugin-pwa`)
- Ikon `public/icons/icon-192x192.png` & `512x512.png` (brand emerald)
- Standalone display, `start_url: /dashboard`, `theme_color: #059669`
- Offline fallback `/offline`
- Shortcuts: Transaksi, Tambah BBM, Laporan
- Workbox precache `build/assets/*` + runtime cache `fonts.bunny.net` & `cdn.jsdelivr.net` (CacheFirst)

## Build

`npm run build` akan generate `public/sw.js`, `public/workbox-*.js`, `public/manifest.webmanifest`.

Pastikan `public/icons/*` ikut deploy.

## Install

Di Chrome/Android: menu → Install app. Di iOS: Share → Add to Home Screen.

## Verifikasi

DevTools → Application → Manifest & Service Workers → Lighthouse PWA audit harus hijau.

File terkait: `vite.config.js` (VitePWA), `resources/views/layouts/app.blade.php` (meta theme-color), `resources/views/offline.blade.php`, `routes/web.php` (`/offline`).

