# Development Tasks

## Phase 0 — Project Setup

- [x] Create Laravel project
- [x] Configure MySQL
- [x] Configure `.env`
- [x] Configure timezone Asia/Jakarta
- [x] Install authentication
- [x] Install Tailwind
- [x] Install Livewire
- [x] Configure storage
- [x] Configure testing

---

# Phase 1 — Authentication

- [x] Register
- [x] Login
- [x] Logout
- [x] Forgot password
- [x] Reset password
- [x] Profile
- [x] Change password

---

# Phase 2 — Account

- [x] Account migration
- [x] Account model
- [x] Account factory
- [x] Account seeder
- [x] Account CRUD
- [x] Initial balance
- [x] Current balance
- [x] Account active/inactive
- [x] Account tests

---

# Phase 3 — Categories

- [x] Category migration
- [x] Category model
- [x] Default category seeder
- [x] Category CRUD
- [x] Income categories
- [x] Expense categories
- [x] Category tests

---

# Phase 4 — Transactions

- [x] Transaction migration
- [x] Transaction model
- [x] Transaction service
- [x] Income
- [x] Expense
- [x] Transfer
- [x] Balance calculation
- [x] Edit transaction
- [x] Delete transaction
- [x] Transaction filters
- [x] Transaction pagination
- [x] Transaction tests

---

# Phase 5 — Dashboard

- [x] Total balance
- [x] Monthly income
- [x] Monthly expense
- [x] Net cashflow
- [x] Recent transactions
- [x] Income vs expense chart
- [x] Expense by category chart

---

# Phase 6 — Vehicles

- [x] Vehicle migration
- [x] Vehicle model
- [x] Vehicle CRUD
- [x] Odometer
- [x] Vehicle detail page
- [x] Vehicle dashboard
- [x] Vehicle tests

---

# Phase 7 — Fuel

- [x] Fuel record migration
- [x] Fuel model
- [x] Fuel CRUD
- [x] Fuel calculation
- [x] Financial transaction integration
- [x] Fuel history
- [x] Fuel statistics
- [x] KM/L calculation
- [x] Cost/km calculation
- [x] Fuel tests

---

# Phase 8 — Service

- [x] Service record migration
- [x] Service model
- [x] Service CRUD
- [x] Labor cost
- [x] Parts cost
- [x] Total cost
- [x] Financial transaction integration
- [x] Service history
- [x] Next service date
- [x] Next service odometer
- [x] Service tests

---

# Phase 9 — Reminder

- [x] Service date reminder
- [x] Service mileage reminder
- [x] Dashboard reminder
- [x] Reminder status

---

# Phase 10 — Reports

- [x] Financial report
- [x] Income report
- [x] Expense report
- [x] Cashflow report
- [x] Category report
- [x] Vehicle cost report
- [x] Fuel report
- [x] Service report
- [x] Date range filter

---

# Phase 11 — Attachments

- [x] Attachment migration
- [x] Upload transaction receipt
- [x] Upload fuel receipt
- [x] Upload service receipt
- [x] File validation
- [x] File preview
- [x] File deletion

---

# Phase 12 — Export

- [x] Export transactions to Excel
- [x] Export financial report
- [x] Export vehicle report
- [x] Export fuel report
- [x] Export service report
- [x] PDF report

---

# Phase 13 — Finalization

- [x] Security review
- [x] Authorization review
- [x] Database index review
- [x] N+1 query review
- [x] Validation review
- [x] Test review
- [x] UI responsive review
- [x] Error handling review
- [x] Production configuration
- [x] Backup strategy
- [x] Deployment documentation

---

# Phase 14 — Pengingat Dokumen & Berlangganan

- [x] Migration `documents` & `subscriptions` + model + factory
- [x] `ExpiryReminderService` (status overdue/due_soon/ok, threshold per baris)
- [x] Kartu pengingat dokumen/berlangganan di dashboard
- [x] CRUD dokumen (route, policy, FormRequest, views)
- [x] CRUD berlangganan (route, policy, FormRequest, views)
- [x] Halaman `/reminders` (servis + dokumen + berlangganan, filter status)
- [x] Menu sidebar Pengingat
- [x] Navigasi (sidebar Pengingat, tombol tambah di `/reminders`)
- [x] Perpanjangan subscription (siklus monthly/quarterly/yearly, riwayat `subscription_renewals`, expense opsional via `TransactionService`)
- [x] UI perpanjangan (modal akun/nominal di `/subscriptions`, tabel riwayat di form edit, validasi akun/nominal)
- [x] Tombol Perpanjang di halaman Pengingat `/reminders` (modal sama, redirect kembali ke asal)
- [x] Test (33 reminder + 2 navigasi + 13 perpanjangan + 3 tombol di /reminders — total 177)
- [x] Dokumentasi (database, business-rules §21–23, README)


---

# Phase 15 — Full Migration Livewire

Konversi seluruh halaman interaktif ke komponen Livewire v4 (SFC) — 11 tahap commit granular, selesai 2026-10-09. Keputusan & konsekuensi: `docs/decisions/0003-full-migration-livewire.md`.

- [x] Tahap 0 — Update dependensi (Livewire 4.4.7, Laravel 13.35, audit npm)
- [x] Tahap 1 — Komponen transaksi (index, filter, pagination)
- [x] Tahap 2 — Form transaksi (create/edit) + trait `ValidatesTransactionData`
- [x] Tahap 3 — Akun & kategori (6 komponen + 2 trait)
- [x] Tahap 4 — Kendaraan (index/create/edit + trait `ValidatesVehicleData`)
- [x] Tahap 5 — Fuel & service records (4 form + 2 index + 4 trait/FormRequest)
- [x] Tahap 6 — Dokumen & berlangganan + modal perpanjang di komponen
- [x] Tahap 7 — Pengingat `/reminders` + dashboard (modal renew inline, chart inline script)
- [x] Tahap 8 — Profil (update info, ganti password, hapus akun + modal); auth Breeze tetap Blade
- [x] Tahap 9 — Laporan (finance/vehicle/fuel/service) & admin (dashboard, users, settings)
- [x] Tahap 10 — Cleanup dead view (`modal.blade.php`) + dokumentasi (ADR 0003, ARCHITECTURE, README)
- [x] Test 259 hijau; route write (POST/PATCH/DELETE) dipertahankan sebagai guard regresi
