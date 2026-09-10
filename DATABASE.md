# Database Specification

## users

Laravel default users table.

Fields:

```text
id
name
email
password
remember_token
created_at
updated_at
```

---

# accounts

```text
id
user_id
name
type
initial_balance
current_balance
description
is_active
created_at
updated_at
```

Type:

```text
bank
cash
ewallet
savings
other
```

---

# categories

```text
id
user_id
name
type
icon
is_active
created_at
updated_at
```

Type:

```text
income
expense
```

---

# transactions

```text
id
user_id
account_id
category_id
vehicle_id
fuel_record_id
service_record_id
type
amount
transaction_date
description
notes
created_at
updated_at
```

Type:

```text
income
expense
transfer
```

Untuk transfer tambahkan:

```text
transfer_group_id
destination_account_id
```

---

# vehicles

```text
id
user_id
name
license_plate
brand
model
year
color
vehicle_type
chassis_number
engine_number
purchase_date
purchase_price
current_odometer
notes
is_active
created_at
updated_at
```

---

# fuel_records

```text
id
user_id
vehicle_id
transaction_id
account_id
fuel_date
odometer
fuel_type
liters
price_per_liter
total_cost
station
notes
created_at
updated_at
```

---

# service_records

```text
id
user_id
vehicle_id
transaction_id
account_id
service_date
odometer
service_type
workshop
labor_cost
parts_cost
total_cost
next_service_date
next_service_odometer
notes
created_at
updated_at
```

---

# attachments

```text
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

Polymorphic relation:

```text
attachable
```

dapat menunjuk:

```text
Transaction
FuelRecord
ServiceRecord
```

---

# Indexes

Minimal index:

```text
accounts.user_id

categories.user_id

transactions.user_id
transactions.account_id
transactions.category_id
transactions.vehicle_id
transactions.transaction_date

vehicles.user_id

fuel_records.user_id
fuel_records.vehicle_id
fuel_records.fuel_date

service_records.user_id
service_records.vehicle_id
service_records.service_date

attachments.user_id
```

---

# Money

Semua nilai uang menggunakan:

```text
DECIMAL(15,2)
```

Jangan menggunakan FLOAT atau DOUBLE untuk nilai uang.