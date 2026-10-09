# Architecture

## 1. Overview

Aplikasi menggunakan monolithic Laravel architecture.

```text
Browser
   ↓
Laravel
   ├── Routes
   ├── Controllers
   ├── Form Requests + Validation Traits
   ├── Policies
   ├── Services
   ├── Models
   ├── Views (Blade) + Livewire Components (SFC)
   └── Alpine/Chart.js (client-side murni)
          ↓
       MySQL
```

Frontend dan backend berada dalam satu aplikasi Laravel.

---

# 2. Application Structure

Struktur utama:

```text
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
│
├── Models/
│
├── Policies/
│
├── Services/
│
├── Actions/
│
└── Notifications/

resources/
├── views/
│   ├── layouts/
│   ├── components/          # UI kit (x-card, x-input, ...) + SFC Livewire
│   ├── dashboard/
│   ├── accounts/
│   ├── transactions/
│   ├── vehicles/
│   ├── fuel/
│   ├── services/
│   └── reports/
│   # view lain = wrapper tipis: layout + <livewire:nama />
│
├── js/
└── css/

database/
├── migrations/
├── seeders/
└── factories/

tests/
├── Feature/
└── Unit/
```

---

# 3. Layer Responsibilities

## Controller

Controller bertanggung jawab untuk:

- menerima request
- authorization
- memanggil service
- mengembalikan response

Controller tidak boleh berisi business logic kompleks.

---

# 4. Form Request

Semua validasi input kompleks menggunakan Form Request.

Contoh:

```text
StoreTransactionRequest
UpdateTransactionRequest
StoreFuelRecordRequest
StoreServiceRecordRequest
StoreVehicleRequest
```

Rule yang dipakai komponen Livewire di-ekstrak ke trait static:

```text
app/Http/Requests/Concerns/
├── ValidatesTransactionData
├── ValidatesAccountData
├── ValidatesCategoryData
├── ValidatesVehicleData
├── ValidatesFuelRecordData
├── ValidatesServiceRecordData
├── ValidatesDocumentData
├── ValidatesSubscriptionData
└── ValidatesProfileData
```

FormRequest `rules()` memanggil trait, komponen Livewire memanggil method
static yang sama via `Validator::make` — satu sumber rule.

---

# 4b. Livewire Components (Frontend Layer)

Sejak Phase 15 (migrasi full Livewire, 2026-10-09) seluruh halaman interaktif
dirender komponen Livewire v4 **Single-File Component** di
`resources/views/components/<nama>.blade.php`.

Konvensi yang harus diikuti:

- **Pola island**: route GET & controller tetap; view = wrapper
  (`<x-app-layout>` + `<livewire:nama ... />`). Route write (POST/PATCH/DELETE)
  dipertahankan sebagai guard regresi test (ADR 0003).
- **Satu root element** per komponen — bungkus konten multi-blok dalam satu
  `<div>` (Livewire menolak multi-root).
- Segmen PHP komponen tidak boleh mencampur `@php(expr)` dengan blok
  `@php…@endphp` dalam satu view (pitfall BladeCompiler).
- `render()` menyediakan `$this->view($data)` — jangan ditulis manual di SFC.
- Form: properti `snake_case` + `wire:submit="save"`; filter/index: properti
  camelCase + `#[Url(as: 'snake_case')]`.
- Model untuk edit: `#[Locked] public Model $m` + `mount()` + `Gate::authorize`
  di `mount` dan `save`.
- Validasi via trait static `App\Http\Requests\Concerns` (lihat §4).
- Sukses: `session()->flash('status', ...)` + `$this->redirect(route(...))`.
- Loop: `wire:key` wajib; live search: `wire:model.live.debounce.400ms`.
- `@push('scripts')` tidak berlaku di dalam komponen → Chart.js memakai
  `<script>` inline biasa; Alpine hanya untuk hal client-side murni
  (fade toast, escape handler).
- Modal: render server-side `@if($open)` + overlay fixed, tutup via
  `$wire.close()`.
- Test: `Livewire::test('nama', $mountArgs)`, assert data view via
  `->viewData($key)` (Livewire tidak punya `assertViewHas`),
  `->assertHasErrors([...])`, exception di `mount` perlu
  `$this->withoutExceptionHandling()`.

Halaman yang tetap Blade murni: `auth/*` (Breeze), `errors/*`,
`reports/pdf/*`, `offline`, `welcome`, `layouts/*`, UI kit `components/*`.

---

# 5. Service Layer

Business logic kompleks berada di Service.

Contoh:

```text
TransactionService
AccountBalanceService
FuelRecordService
VehicleService
ServiceRecordService
ReportService
```

---

# 6. Models

Model utama:

```text
User
Account
Category
Transaction
Vehicle
FuelRecord
ServiceRecord
Attachment
```

---

# 7. Relationships

## User

Has many:

```text
accounts
categories
transactions
vehicles
```

## Account

Belongs to:

```text
user
```

Has many:

```text
transactions
```

## Category

Belongs to:

```text
user
```

Has many:

```text
transactions
```

## Transaction

Belongs to:

```text
user
account
category
```

May belong to:

```text
vehicle
fuel_record
service_record
```

## Vehicle

Belongs to:

```text
user
```

Has many:

```text
fuel_records
service_records
```

## FuelRecord

Belongs to:

```text
vehicle
account
transaction
```

## ServiceRecord

Belongs to:

```text
vehicle
account
transaction
```

---

# 8. Transaction Architecture

Semua perubahan saldo harus melalui satu mekanisme terpusat.

Contoh:

```text
TransactionService
       ↓
AccountBalanceService
       ↓
Account
```

Jangan melakukan perubahan saldo secara berbeda-beda di setiap controller.

---

# 9. Transfer

Transfer memiliki dua sisi:

```text
Source Account
      ↓
   - amount
      ↓
Destination Account
      ↑
   + amount
```

Transfer tidak dihitung sebagai:

```text
income
expense
```

dalam laporan cashflow.

---

# 10. Vehicle Expense

Fuel dan service dapat menghasilkan financial transaction.

Contoh:

```text
FuelRecord
     ↓
Transaction
     ↓
Expense
     ↓
Account balance decreases
```

Contoh:

```text
ServiceRecord
     ↓
Transaction
     ↓
Expense
     ↓
Account balance decreases
```

---

# 11. Database Transactions

Gunakan:

```php
DB::transaction()
```

untuk operasi yang mengubah beberapa tabel.

Contoh:

```text
Create Fuel Record
       ↓
Create Financial Transaction
       ↓
Update Account Balance
```

Ketiganya harus berhasil atau semuanya dibatalkan.

---

# 12. File Storage

Attachment menggunakan:

```text
storage/app/public
```

Gunakan Laravel Storage API.

Database hanya menyimpan metadata file.

Contoh:

```text
attachments

id
user_id
attachable_type
attachable_id
file_name
file_path
mime_type
file_size
created_at
updated_at
```

---

# 13. Reporting

Laporan tidak menyimpan hasil agregasi sebagai data utama.

Hitung berdasarkan transaction records kecuali ada kebutuhan performa yang jelas.

Contoh:

```text
transactions
     ↓
SUM(amount)
     ↓
monthly report
```

---

# 14. Performance

Gunakan index pada:

```text
user_id
account_id
category_id
vehicle_id
transaction_date
fuel_date
service_date
```

Gunakan eager loading jika diperlukan untuk mencegah N+1 queries.

---

# 15. Security

Setiap request harus memastikan:

```text
Authenticated User
       ↓
Owns Resource
       ↓
Authorized Action
       ↓
Execute
```

Jangan percaya ID dari request tanpa authorization.

---

# 16. API

Untuk MVP, aplikasi dapat menggunakan Laravel web routes + Blade/Livewire.

API dapat ditambahkan kemudian jika aplikasi membutuhkan:

- Mobile app
- External integration
- Third-party clients

Jangan membuat REST API lengkap jika belum diperlukan.