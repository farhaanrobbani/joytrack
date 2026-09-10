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