# JoyTrack — Manajemen Keuangan & Kendaraan

Aplikasi web manajemen keuangan pribadi + kendaraan (BBM, servis, pengingat, laporan) — Laravel 13, Livewire, Tailwind, MySQL.

## Stack

Laravel 13 · PHP 8.5 · MySQL/MariaDB · Livewire 4 · Tailwind 4 · Vite · dompdf · openspout

## Fitur

Auth · Cloudflare Turnstile (form auth publik) · Akun (bank/cash/ewallet/savings/other + Kartu Kredit/Paylater dengan limit & pengingat jatuh tempo) · Kategori · Transaksi (income/expense/transfer, saldo terpusat `TransactionService`, `DB::transaction`) · Vehicle · Fuel (km/L, biaya/km, integrasi transaksi) · Service (next date/km, integrasi transaksi) · Reminder (date + odometer) · Pengingat dokumen kadaluarsa, perpanjangan berlangganan & tagihan kartu (`/reminders`) · Perpanjangan berlangganan (siklus bulanan/3 bulan/tahunan, riwayat, expense opsional) · Dashboard (cashflow 6 bulan, kategori) · Reports (finance/vehicle/fuel/service) · Attachments (polymorphic) · Export Excel (xlsx) + CSV + PDF · Navigasi sidebar `wire:navigate` (pindah halaman instan tanpa reload penuh)

## Quick Start

```bash
composer install
cp .env.example .env && php artisan key:generate
# edit DB_* , APP_URL
php artisan migrate --force
php artisan storage:link
npm install && npm run build
php artisan serve --host=0.0.0.0 --port=7041
```

## Turnstile (opsional)

Buat site di [dash.cloudflare.com/turnstile](https://dash.cloudflare.com/turnstile) (mode Managed), lalu isi kedua kunci di `.env`:

```
TURNSTILE_SITE_KEY=...
TURNSTILE_SECRET_KEY=...
```

Proteksi aktif otomatis di form auth publik (login, register, lupa/reset password) begitu secret key terisi. Tanpa kunci, semua form tetap berjalan normal (verifikasi dilewati) — cocok untuk development & test.

## Docs

- `AGENTS.md` — workflow & rules
- `docs/database.md` — schema
- `docs/business-rules.md` — aturan saldo, odometer, fuel efficiency
- `docs/deployment.md` — deploy & CI (`git push origin main` → GitHub Actions `.github/workflows/ci.yml`)
- `docs/backup.md` — backup DB & files
- `docs/decisions/` — ADR (format export, CI/CD, error page, migrasi Livewire)
- `docs/security-review.md` / `docs/phase13-review.md` — hasil review
- `PRD.md` / `DESIGN.md` / `ARCHITECTURE.md`

## Tests

```bash
php artisan test   # 268 tests
npm run build
```
