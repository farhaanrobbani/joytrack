# Business Rules

## 1. Income

Ketika income dibuat:

```text
Account balance += amount
```

---

# 2. Expense

Ketika expense dibuat:

```text
Account balance -= amount
```

Saldo tidak boleh menjadi negatif jika account menggunakan aturan saldo negatif disabled.

Aturan overdraft harus configurable pada fase berikutnya.

---

# 3. Transfer

Transfer:

```text
Source balance -= amount

Destination balance += amount
```

Transfer tidak dihitung sebagai:

```text
Income
Expense
```

---

# 4. Transaction Update

Jika transaksi diubah:

1. Revert efek transaksi lama.
2. Validasi transaksi baru.
3. Terapkan efek transaksi baru.

Semua dilakukan dalam database transaction.

---

# 5. Transaction Delete

Jika transaksi dihapus:

1. Revert efek transaksi.
2. Hapus transaksi.

Tidak boleh menyebabkan saldo menjadi salah.

---

# 6. Fuel

Total:

```text
total_cost = liters × price_per_liter
```

User boleh mengubah total jika ada perbedaan pembulatan atau kondisi khusus.

---

# 7. Fuel Financial Transaction

Jika user memilih:

```text
Create financial transaction = YES
```

maka sistem membuat:

```text
Expense
```

dengan kategori:

```text
Bahan Bakar
```

dan mengurangi saldo account.

---

# 8. Service

Total:

```text
total_cost =
labor_cost + parts_cost
```

---

# 9. Service Financial Transaction

Jika user memilih:

```text
Create financial transaction = YES
```

sistem membuat:

```text
Expense
```

dengan kategori:

```text
Servis Kendaraan
```

---

# 10. Vehicle Odometer

Odometer baru tidak boleh lebih kecil dari odometer sebelumnya kecuali user memiliki permission khusus atau melakukan koreksi data.

Default:

```text
new_odometer >= previous_odometer
```

---

# 11. Fuel Efficiency

Jika tersedia minimal dua pengisian dengan odometer:

```text
distance =
current_odometer - previous_odometer
```

Kemudian:

```text
km_per_liter =
distance / current_liters
```

Jika distance <= 0:

```text
efficiency = null
```

---

# 12. Cost per Kilometer

Jika distance > 0:

```text
cost_per_km =
total_cost / distance
```

---

# 13. Vehicle Expense

Biaya kendaraan dapat berasal dari:

```text
Fuel
Service
Other Vehicle Expense
```

---

# 14. Ownership

Semua resource harus dimiliki user.

Contoh:

```text
user A
    ↓
vehicle A
    ↓
fuel A
```

User B tidak boleh mengakses:

```text
vehicle A
fuel A
```

meskipun mengetahui ID.

---

# 15. Date

Semua tanggal transaksi menggunakan timezone aplikasi.

Default timezone:

```text
Asia/Jakarta
```

Tanggal harus ditampilkan dalam format Indonesia:

```text
10 September 2026
```

---

# 16. Deletion

Untuk data penting, pertimbangkan soft delete.

Prioritas:

- Transactions
- Vehicles
- Service records
- Fuel records

Jangan langsung menghapus data historis secara permanen kecuali diperlukan.

---

# 17. Categories

User dapat membuat kategori custom.

Kategori default dibuat menggunakan seeder.

Kategori default tidak boleh hilang ketika user menghapus kategori custom.

---

# 18. Reports

Income:

```text
SUM(transactions.amount)
WHERE type = income
```

Expense:

```text
SUM(transactions.amount)
WHERE type = expense
```

Transfer tidak masuk cashflow.

---

# 19. Monthly Cashflow

```text
Net Cashflow =
Total Income - Total Expense
```

---

# 20. Vehicle Monthly Cost

```text
Vehicle Cost =
Fuel Cost
+ Service Cost
+ Other Vehicle Expense
```

---

# 21. Document Expiry Reminder

Berlaku untuk dokumen dengan `is_active = true` milik user yang bersangkutan.

```text
days = expiry_date - hari ini (Asia/Jakarta)

overdue  : days < 0
due_soon : 0 <= days <= reminder_days (per baris, default 7)
ok       : days > reminder_days
```

- Item `ok` tidak ditampilkan di dashboard maupun halaman Pengingat.
- Urutan tampil: `overdue` dulu, lalu `due_soon` dengan sisa hari terkecil.
- Threshold mengikuti `reminder_days` per dokumen (1–90 hari), bukan global.

---

# 22. Subscription Renewal Reminder

Sama seperti aturan dokumen, dengan tanggal `next_renewal_date`:

```text
days = next_renewal_date - hari ini (Asia/Jakarta)

overdue  : days < 0
due_soon : 0 <= days <= reminder_days (per baris, default 7)
ok       : days > reminder_days
```

- Nominal `amount` hanya informatif — pengingat tidak membuat transaksi otomatis;
  transaksi expense hanya dibuat saat perpanjangan manual (lihat §23).
- Pengingat servis (tanggal + odometer) tetap memakai `ServiceReminderService`
  dengan ambang global 7 hari / 500 km, dan digabung di halaman `/reminders`.

---

# 23. Subscription Renewal (Perpanjangan)

- Siklus perpanjangan (`renewal_cycle`): `monthly` (1 bulan), `quarterly` (3 bulan),
  `yearly` (12 bulan); default `monthly`.
- Tanggal baru = base + siklus memakai `addMonthsNoOverflow`
  (mis. 31 Jan → 28/29 Feb, bukan 3 Mar).
- Base perhitungan: `next_renewal_date` jika masih akan datang; jika sudah lewat
  (overdue), dihitung mulai dari hari ini (Asia/Jakarta).
- Setiap perpanjangan mencatat riwayat di `subscription_renewals`
  (tanggal lama, tanggal baru, nominal, catatan) dan menggeser `next_renewal_date`.
- Expense opsional: jika `create_transaction` aktif, dibuat transaksi `expense`
  ber tanggal hari ini lewat `TransactionService` (saldo akun ikut berkurang).
  Kategori `Langganan` (expense) dibuat otomatis bila belum ada; nominal default
  dari `subscriptions.amount`.
- Validasi: `account_id` wajib & harus milik user yang sama saat `create_transaction`;
  `amount` wajib diisi bila `subscriptions.amount` kosong.
- Riwayat ditampilkan di form edit berlangganan; tombol perpanjang (modal) tersedia
  di `/subscriptions` dan di halaman Pengingat `/reminders`, memakai aturan
  otorisasi `update` (policy).
- Setelah sukses, redirect kembali ke halaman asal (`back=reminders` → `/reminders`,
  selain itu → `/subscriptions`) dengan pesan sukses.

---

# 24. Credit Account (Kartu Kredit / Paylater)

- Akun `type = credit` boleh punya `credit_limit`, `billing_day` (tgl cetak
  tagihan), dan `due_day` (jatuh tempo), semuanya nullable; `initial_balance`
  wajib ≥ 0 (mulai dari 0).
- Belanja dicatat sebagai transaksi `expense` ke akun credit →
  `current_balance` menjadi negatif. Saldo negatif = tagihan yang belum dibayar:

  ```text
  utang   = max(0, -current_balance)
  sisa    = credit_limit - utang (null bila limit tidak diatur)
  ```

- Membayar tagihan = transaksi `transfer` dari akun bank/cash ke akun credit
  (saldo credit naik menuju 0); transfer tidak dihitung income/expense (§3).
- Batas limit tidak diblokir di aplikasi (fase lanjut: statement/cicilan/
  min-payment).
- Pengingat jatuh tempo (`ExpiryReminderService::getCreditReminders`, tampil di
  `/reminders` bagian "Tagihan Kartu" dan dashboard):

  ```text
  Syarat : akun aktif, type = credit, due_day terisi, current_balance < 0
  days   = due_day bulan berjalan (diklaim ke hari terakhir bulan) - hari ini
  ok (>7 hari) tidak ditampilkan; threshold global 7 hari
  overdue : days < 0 (lewat tanggal jatuh tempo bulan ini, masih berutang)
  ```