# Design System

## 1. Design Direction

Gaya visual:

**Modern Financial Dashboard**

Karakter:

- Clean
- Minimal
- Professional
- Modern
- Mudah dibaca
- Fokus pada angka
- Tidak terlalu banyak dekorasi

---

# 2. Layout

Desktop:

```text
┌─────────────────────────────────────────────┐
│ Topbar                                      │
├────────────┬────────────────────────────────┤
│            │                                │
│ Sidebar    │ Main Content                   │
│            │                                │
│ Dashboard  │                                │
│ Keuangan   │                                │
│ Kendaraan  │                                │
│ Laporan    │                                │
│ Settings   │                                │
│            │                                │
└────────────┴────────────────────────────────┘
```

Sidebar desktop sekitar 240px.

Mobile menggunakan:

- responsive sidebar
- hamburger menu
- bottom navigation jika diperlukan

---

# 3. Navigation

## Dashboard

- Overview

## Keuangan

- Transaksi
- Pemasukan
- Pengeluaran
- Transfer
- Akun
- Kategori

## Kendaraan

- Kendaraan
- Bahan Bakar
- Servis
- Pengingat

## Laporan

- Keuangan
- Cashflow
- Pengeluaran
- Kendaraan
- BBM
- Servis

## Pengaturan

- Profil
- Pengaturan aplikasi

---

# 4. Dashboard

Dashboard harus menampilkan informasi terpenting terlebih dahulu.

Urutan:

```text
Total Saldo
Income
Expense
Net Cashflow
```

Kemudian:

```text
Cashflow Chart
```

Kemudian:

```text
Expense by Category
```

Kemudian:

```text
Recent Transactions
```

Kemudian:

```text
Vehicle Summary
Service Reminder
```

---

# 5. Financial Cards

Contoh:

```text
┌──────────────────────┐
│ Total Saldo          │
│                      │
│ Rp 12.500.000        │
│                      │
└──────────────────────┘
```

Gunakan angka besar dan label jelas.

---

# 6. Currency

Format mata uang:

```text
Rp 1.250.000
```

Gunakan format Indonesia.

---

# 7. Transaction Colors

Gunakan warna semantik:

Income:

```text
Success
```

Expense:

```text
Danger
```

Transfer:

```text
Neutral
```

Warna tidak boleh menjadi satu-satunya indikator. Selalu sertakan icon/text.

---

# 8. Forms

Form harus sederhana.

Contoh expense:

```text
Nominal
Akun
Kategori
Tanggal
Deskripsi
Catatan
Attachment
```

Jangan menampilkan field yang tidak relevan.

---

# 9. Vehicle Dashboard

Tampilkan:

```text
Honda Vario 150

N 1234 ABC

45.200 km
```

Kemudian:

```text
Total biaya
BBM
Servis
```

Kemudian:

```text
Servis terakhir
Servis berikutnya
```

---

# 10. Tables

Table harus:

- Responsive
- Sortable jika diperlukan
- Searchable
- Filterable
- Pagination

Mobile dapat menggunakan card/list view.

---

# 11. Modal

Gunakan modal untuk operasi sederhana:

- Delete confirmation
- Quick add transaction
- Quick add fuel
- Quick add service

Form kompleks sebaiknya menggunakan halaman sendiri.

---

# 12. Empty State

Setiap halaman harus memiliki empty state.

Contoh:

```text
Belum ada transaksi

Mulai catat pemasukan atau pengeluaran
untuk melihat kondisi keuangan Anda.

[ + Tambah Transaksi ]
```

---

# 13. Loading State

Gunakan:

- skeleton
- spinner
- disabled button

Jangan membuat user bingung apakah request sedang berjalan.

---

# 14. Error State

Error harus jelas dan actionable.

Contoh:

```text
Transaksi gagal disimpan.

Periksa kembali nominal dan akun yang dipilih.
```

Jangan menampilkan error teknis database kepada user.

---

# 15. Responsive

Prioritas:

```text
Mobile
Tablet
Desktop
```

Aplikasi harus nyaman digunakan dari HP karena pencatatan transaksi sering dilakukan langsung setelah melakukan pembayaran.