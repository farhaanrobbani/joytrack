# Security Review — Phase 13

## Auth & Authorization
- All business tables have `user_id` FK + Policies (`AccountPolicy`, `CategoryPolicy`, `TransactionPolicy`, `VehiclePolicy`, `FuelRecordPolicy`, `ServiceRecordPolicy`, `AttachmentPolicy`) checked via `$this->authorize()` or FormRequest `can()`. Verified via 98 tests (IDOR 403).
- Routes under `auth` middleware. Dashboard/report/export require auth.

## Validation
- All inputs via Form Requests with `required`, `exists:*,id` + ownership `withValidator` checks (prevent IDOR via other user's account/category/vehicle). Amount `gt:0`, file `mimes:jpg,jpeg,png,webp,pdf` `max:5120`.

## Mass Assignment
- Models use `$fillable` whitelist only.

## SQL Injection
- No raw concatenation; Eloquent/Query Builder with bindings. Only `DB::raw('SUM...')` with static strings.

## XSS/CSRF
- Blade auto-escapes `{{ }}`. `@csrf` on all forms, `csrf_token` meta. `POST`/`PATCH`/`DELETE` protected.

## File Upload
- `mimes` whitelist, `max:5MB`, stored via `Storage::disk('public')` (metadata in DB, not BLOB), path `attachments/{type}/{id}`, deletion via model event + controller. No executable allowed.

## Money
- `DECIMAL(15,2)` for all amounts, `DECIMAL(8,2)` liters, `DECIMAL(10,2)` price/L — no FLOAT/DOUBLE.

## Transactions
- `DB::transaction()` for create/update/delete affecting multiple tables (TransactionService → AccountBalanceService). Revert-apply on update.

## Other
- `.env` not committed, `AGENTS.md §5` secrets via env.
- `APP_DEBUG` false in production, generic error messages (no SQL trace).
