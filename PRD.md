# Product Requirements Document

## 1. Nama Produk

**Manajemen Keuangan**

Aplikasi web untuk mencatat, mengelola, dan menganalisis:

1. Keuangan pribadi/keluarga
2. Pemasukan dan pengeluaran
3. Rekening/dompet
4. Transaksi antar rekening
5. Kendaraan
6. Pengisian bahan bakar
7. Servis kendaraan
8. Biaya perawatan kendaraan
9. Laporan keuangan
10. Laporan biaya kendaraan

---

# 2. Tujuan Produk

Aplikasi digunakan sebagai pusat pencatatan keuangan sehingga pengguna dapat mengetahui:

- Berapa uang yang dimiliki.
- Dari mana uang berasal.
- Ke mana uang digunakan.
- Berapa pengeluaran setiap bulan.
- Kategori pengeluaran terbesar.
- Berapa biaya kendaraan.
- Berapa biaya bahan bakar kendaraan.
- Kapan kendaraan terakhir diservis.
- Berapa total biaya servis kendaraan.
- Berapa biaya kendaraan dalam periode tertentu.

---

# 3. Target Pengguna

MVP difokuskan untuk penggunaan personal.

User dapat memiliki:

- Banyak rekening bank.
- Banyak dompet/cash.
- Banyak kendaraan.
- Banyak kategori transaksi.

---

# 4. Teknologi

## Backend

- Laravel
- PHP
- MySQL

## Frontend

Gunakan Laravel Blade sebagai fondasi.

Untuk interaksi dinamis dapat menggunakan:

- Livewire
- Alpine.js

Gunakan Tailwind CSS untuk styling.

## Database

MySQL.

## Authentication

Laravel authentication.

---

# 5. Modul Aplikasi

Aplikasi terdiri dari beberapa modul utama.

## Modul 1 — Dashboard

Dashboard menampilkan:

- Total saldo seluruh akun.
- Total pemasukan bulan berjalan.
- Total pengeluaran bulan berjalan.
- Selisih pemasukan dan pengeluaran.
- Grafik pemasukan vs pengeluaran.
- Pengeluaran berdasarkan kategori.
- Transaksi terbaru.
- Ringkasan biaya kendaraan.
- Pengingat servis kendaraan.

---

# 6. Modul Akun Keuangan

User dapat membuat akun keuangan.

Jenis akun:

- Bank
- Cash
- E-wallet
- Tabungan
- Lainnya

Data akun:

- Nama akun
- Jenis akun
- Saldo awal
- Tanggal saldo awal
- Catatan
- Status aktif/nonaktif

Contoh:

```text
BCA
Bank
Saldo: Rp5.000.000

BRI
Bank
Saldo: Rp2.500.000

Cash
Cash
Saldo: Rp750.000

DANA
E-wallet
Saldo: Rp300.000
```

---

# 7. Modul Kategori

Kategori dibagi menjadi:

## Pemasukan

Contoh:

- Gaji
- Honor
- Bonus
- Usaha
- Investasi
- Hadiah
- Lainnya

## Pengeluaran

Contoh:

- Makanan
- Transportasi
- Belanja
- Tagihan
- Listrik
- Internet
- Pendidikan
- Kesehatan
- Hiburan
- Kendaraan
- Servis
- Bahan Bakar
- Lainnya

Kategori harus dapat ditambahkan, diedit, dan dinonaktifkan oleh user.

---

# 8. Modul Transaksi

Jenis transaksi:

### Income

Pemasukan.

Contoh:

```text
Gaji
Rp5.000.000
BCA
```

### Expense

Pengeluaran.

Contoh:

```text
Makan
Rp50.000
Cash
```

### Transfer

Pemindahan uang antar akun.

Contoh:

```text
BCA → Cash
Rp500.000
```

Transfer tidak boleh dihitung sebagai pemasukan atau pengeluaran.

---

# 9. Data Transaksi

Setiap transaksi memiliki:

- ID
- User
- Akun
- Kategori
- Jenis transaksi
- Nominal
- Tanggal
- Deskripsi
- Catatan
- Attachment opsional
- Created at
- Updated at

Untuk transfer:

- Source account
- Destination account
- Amount

---

# 10. Modul Kendaraan

User dapat menyimpan banyak kendaraan.

Data kendaraan:

- Nama kendaraan
- Nomor polisi
- Merek
- Model
- Tahun
- Warna
- Jenis kendaraan
- Nomor rangka opsional
- Nomor mesin opsional
- Tanggal pembelian opsional
- Harga pembelian opsional
- Odometer saat ini
- Catatan
- Status aktif/nonaktif

Contoh:

```text
Honda Vario 150

Plat:
N 1234 ABC

Tahun:
2020

Odometer:
45.200 km
```

---

# 11. Modul Bahan Bakar

User dapat mencatat setiap pengisian bahan bakar.

Data:

- Kendaraan
- Tanggal
- Odometer
- Jenis bahan bakar
- Volume liter
- Harga per liter
- Total biaya
- SPBU/tempat pengisian
- Metode pembayaran
- Akun pembayaran
- Catatan

Total biaya dapat dihitung:

```text
liter × harga_per_liter
```

Tetapi user tetap dapat mengubah total jika diperlukan.

---

# 12. Statistik Bahan Bakar

Sistem menghitung:

- Total liter.
- Total biaya BBM.
- Rata-rata harga/liter.
- Rata-rata biaya per pengisian.
- Biaya BBM per bulan.
- Konsumsi BBM.
- Jarak tempuh.
- Estimasi biaya per kilometer.

Jika data odometer mencukupi:

```text
jarak = odometer_sekarang - odometer_sebelumnya

km_per_liter = jarak / liter

biaya_per_km = total_biaya / jarak
```

Jika data tidak mencukupi, statistik tersebut tidak ditampilkan.

---

# 13. Modul Servis Kendaraan

User dapat mencatat servis kendaraan.

Data:

- Kendaraan
- Tanggal servis
- Odometer
- Jenis servis
- Bengkel
- Biaya jasa
- Biaya sparepart
- Total biaya
- Catatan
- Attachment/foto nota
- Servis berikutnya berdasarkan tanggal
- Servis berikutnya berdasarkan kilometer

Contoh:

```text
Servis rutin

Tanggal:
10 September 2026

Odometer:
45.000 km

Bengkel:
Bengkel ABC

Jasa:
Rp100.000

Sparepart:
Rp250.000

Total:
Rp350.000
```

---

# 14. Jenis Servis

Default:

- Servis rutin
- Ganti oli
- Ganti filter oli
- Ganti filter udara
- Ganti busi
- Ganti kampas rem
- Ganti ban
- Servis CVT
- Servis mesin
- Servis AC
- Kelistrikan
- Perbaikan
- Sparepart
- Lainnya

User dapat membuat jenis servis sendiri.

---

# 15. Pengingat Servis

Sistem dapat memberikan reminder berdasarkan:

## Kilometer

Contoh:

```text
Servis berikutnya:
50.000 km
```

Jika odometer sekarang:

```text
49.500 km
```

maka sistem memberikan peringatan:

```text
Servis kendaraan akan segera jatuh tempo.
```

## Tanggal

Contoh:

```text
Servis berikutnya:
10 Oktober 2026
```

Sistem menampilkan reminder sebelum tanggal tersebut.

---

# 16. Integrasi Keuangan dan Kendaraan

Transaksi kendaraan dapat terhubung dengan transaksi keuangan.

Contoh pengisian BBM:

```text
BBM
Rp100.000
BCA
```

Aplikasi otomatis membuat transaksi pengeluaran:

```text
Jenis:
Expense

Kategori:
Bahan Bakar

Akun:
BCA

Nominal:
Rp100.000
```

Begitu juga dengan servis.

Contoh:

```text
Servis:
Rp500.000

Akun:
BCA
```

Sistem dapat membuat transaksi pengeluaran terkait servis.

Setiap transaksi kendaraan harus dapat dilacak ke sumber transaksi kendaraan.

---

# 17. Dashboard Kendaraan

Setiap kendaraan memiliki dashboard.

Informasi:

- Odometer terakhir.
- Total biaya kendaraan.
- Total biaya BBM.
- Total biaya servis.
- Total biaya perawatan.
- Jumlah pengisian BBM.
- Jumlah servis.
- Servis terakhir.
- Servis berikutnya.
- Biaya bulan berjalan.
- Grafik biaya kendaraan.

---

# 18. Laporan Keuangan

User dapat memilih periode:

- Hari ini
- Minggu ini
- Bulan ini
- Tahun ini
- Custom date range

Laporan:

### Ringkasan

- Total pemasukan
- Total pengeluaran
- Net cashflow
- Saldo

### Pengeluaran berdasarkan kategori

Contoh:

```text
Makanan       Rp1.200.000
Transportasi  Rp500.000
Tagihan       Rp750.000
BBM           Rp600.000
Servis        Rp300.000
```

### Cashflow

Grafik:

```text
Income vs Expense
```

---

# 19. Laporan Kendaraan

Per kendaraan:

- Total biaya.
- Total BBM.
- Total servis.
- Total sparepart.
- Total biaya per bulan.
- Total liter.
- Rata-rata harga BBM.
- Jarak tempuh.
- Biaya per kilometer.

---

# 20. Search dan Filter

Transaksi harus dapat difilter berdasarkan:

- Tanggal
- Jenis
- Akun
- Kategori
- Nominal
- Keyword

Servis:

- Kendaraan
- Tanggal
- Bengkel
- Jenis servis

BBM:

- Kendaraan
- Tanggal
- Jenis BBM
- SPBU

---

# 21. Attachment

User dapat melampirkan:

- Foto nota
- Foto servis
- Bukti transaksi
- Dokumen lainnya

File harus disimpan menggunakan Laravel Storage.

Jangan menyimpan file langsung di database.

---

# 22. Authentication

User dapat:

- Register
- Login
- Logout
- Reset password
- Mengubah profil
- Mengubah password

Setiap data harus terisolasi berdasarkan user.

User A tidak boleh melihat data User B.

---

# 23. Keamanan

Wajib menggunakan:

- Laravel authentication
- CSRF protection
- Form Request validation
- Authorization Policy
- Mass assignment protection
- SQL injection protection dari Eloquent
- Validasi upload file
- Validasi ownership data

User hanya dapat mengakses data miliknya sendiri.

---

# 24. Audit

Sistem menyimpan:

- created_at
- updated_at

Untuk data penting, dapat ditambahkan audit log pada fase berikutnya.

---

# 25. MVP

Prioritas MVP:

### Phase 1

- Authentication
- Dashboard
- Account
- Category
- Income
- Expense
- Transfer

### Phase 2

- Vehicle
- Fuel
- Service

### Phase 3

- Reports
- Charts
- Reminder

### Phase 4

- Attachment
- Export Excel/PDF
- Advanced analytics

---

# 26. Non-Goals MVP

Tidak perlu dibuat pada tahap pertama:

- Mobile application
- Multi-currency kompleks
- Investment portfolio
- Cryptocurrency
- AI financial advisor
- Integrasi bank otomatis
- Payment gateway
- Family multi-user

Fitur tersebut dapat dipertimbangkan pada versi berikutnya.

---

# 27. Acceptance Criteria

Aplikasi dianggap memenuhi MVP apabila:

1. User dapat login.
2. User dapat membuat akun keuangan.
3. User dapat mencatat pemasukan.
4. User dapat mencatat pengeluaran.
5. User dapat melakukan transfer.
6. Saldo akun berubah secara benar.
7. User dapat membuat kategori.
8. User dapat membuat kendaraan.
9. User dapat mencatat BBM.
10. User dapat mencatat servis.
11. Biaya BBM dan servis dapat terhubung dengan transaksi keuangan.
12. Dashboard menampilkan ringkasan keuangan.
13. User dapat melihat riwayat transaksi.
14. User dapat melihat riwayat kendaraan.
15. User dapat melihat laporan berdasarkan periode.
16. Data antar user tidak dapat saling diakses.
17. Validasi form berjalan dengan benar.
18. Test utama aplikasi berhasil.