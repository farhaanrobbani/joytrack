# JoyTrack — Manajemen Keuangan & Kendaraan

Aplikasi web manajemen keuangan pribadi + kendaraan (BBM, servis, pengingat, laporan) — Laravel 12, Livewire, Tailwind, MySQL.

## Stack

Laravel 12 · PHP 8.3 · MySQL/MariaDB · Livewire 4 · Tailwind 3 · Vite · dompdf

## Fitur

Auth · Akun (bank/cash/ewallet) · Kategori · Transaksi (income/expense/transfer, saldo terpusat `TransactionService`, `DB::transaction`) · Vehicle · Fuel (km/L, biaya/km, integrasi transaksi) · Service (next date/km, integrasi transaksi) · Reminder (date + odometer) · Dashboard (cashflow 6 bulan, kategori) · Reports (finance/vehicle) · Attachments (polymorphic) · Export CSV + PDF

## Quick Start

```bash
composer install
cp .env.example .env && php artisan key:generate
# edit DB_* , APP_URL
php artisan migrate --force
php artisan storage:link
npm install && npm run build
php artisan serve --host=0.0.0.0 --port=7011
```

## Docs

- `AGENTS.md` — workflow & rules
- `docs/database.md` — schema
- `docs/business-rules.md` — aturan saldo, odometer, fuel efficiency
- `docs/deployment.md` — deploy & CI/CD (`git push origin main`)
- `docs/backup.md` — backup DB & files
- `PRD.md` / `DESIGN.md` / `ARCHITECTURE.md`

## Tests

```bash
php artisan test   # 98 tests
npm run build
```
