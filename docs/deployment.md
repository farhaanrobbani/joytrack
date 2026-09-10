# Deployment

## Environment

- PHP 8.3, MariaDB 10.11, Node 26 (Vite + Tailwind)
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

Push ke `origin main` trigger GitHub Actions → deploy ke VPS (AGENTS.md §18).

```bash
git push origin main
```

## Production Checklist

- `APP_DEBUG=false`, `APP_ENV=production`
- `php artisan migrate --force` (no destructive manual changes)
- `php artisan storage:link` (attachments `storage/app/public`)
- `npm run build` artifacts `public/build`
- `php -l` dan `php artisan test` hijau
- Policies aktif (authorization), Form Requests validasi, `DECIMAL(15,2)` untuk uang, `DB::transaction()` untuk saldo
```

