# Deployment

## Environment

- PHP 8.5, MariaDB 10.11, Node 26 (Vite + Tailwind 4)
- App timezone `Asia/Jakarta` (`config/app.php:68`)
- Locale `id` (`APP_LOCALE=id`)

## Setup

```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Edit .env: DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL, MAIL_*
php artisan migrate --force
php artisan storage:link
npm install && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## .env

Lihat `.env.example` untuk variabel wajib: `APP_KEY`, `DB_*`, `MAIL_*`, `FILESYSTEM_DISK`, `VITE_APP_NAME`.
Jangan commit `.env`.

## CI/CD

Push ke `origin main` (atau PR) trigger GitHub Actions (`.github/workflows/ci.yml`):

1. `composer install` → `php artisan test` (SQLite in-memory, 126 tests)
2. `npm ci` → `npm run build` → salin `sw.js` / `workbox-*.js` / `manifest.webmanifest` ke `public/`

Deploy ke VPS masih manual (lihat Production Checklist di bawah):

```bash
git push origin main
# di server:
git pull && composer install --no-dev && npm ci && npm run build
php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## Production `.env`

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda
# DB_*, SESSION_DRIVER=database, CACHE_STORE=database, MAIL_*
```

## Production Checklist

- `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL` sesuai domain
- `php artisan migrate --force` (no destructive manual changes)
- `php artisan storage:link` (attachments `storage/app/public`)
- `npm run build` artifacts `public/build` + salin PWA assets ke `public/`
- `php artisan test` hijau (126 tests)
- Policies aktif (authorization), Form Requests validasi, `DECIMAL(15,2)` untuk uang, `DB::transaction()` untuk saldo
- Halaman error custom (`resources/views/errors/`) aktif otomatis untuk 404/403/419/500

