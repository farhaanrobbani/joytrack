# 0002 — CI dengan GitHub Actions

## Status

Accepted (2026-10-08)

## Konteks

`docs/deployment.md` & `README.md` mengklaim CI/CD GitHub Actions, tetapi direktori `.github/` tidak ada (klaim tidak valid).

## Keputusan

Buat `.github/workflows/ci.yml` (job `test`, trigger `push: main` + `pull_request`):

1. Setup PHP 8.3 + ekstensi (`gd`, `pdo_sqlite`, `zip`).
2. `composer install` → `cp .env.example .env` → `php artisan key:generate`.
3. `php artisan test` — memakai SQLite `:memory:` (lihat `phpunit.xml`), jadi **tanpa service database**.
4. `npm ci` → `npm run build` → salin `sw.js`, `workbox-*.js`, `manifest.webmanifest` ke `public/`.

Deploy ke VPS tetap manual (belum ada job deploy).

## Konsekuensi

- Setiap push/PR memverifikasi 126 test + build aset.
- Klaim CI/CD di dokumentasi kini sesuai kenyataan.
