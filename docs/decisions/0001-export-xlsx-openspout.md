# 0001 — Export Excel (.xlsx) via openspout

## Status

Accepted (2026-10-08)

## Konteks

TASKS Phase 12 mensyaratkan "Export transactions to Excel". Label tombol sudah menulis "Export Excel" tetapi output sebenarnya CSV (`text/csv`), sehingga menyesatkan. Opsi:

1. CSV + BOM UTF-8, rename label — tanpa dependensi.
2. `openspout/openspout` — penulis XLSX streaming, ringan.
3. `phpoffice/phpspreadsheet` — fitur formatting lengkap tetapi berat.

## Keputusan

Pakai **openspout ^5** untuk menulis `.xlsx` via `openToFile('php://output')` di dalam `streamDownload` (header Content-Type dipegang Laravel, bukan library). Endpoint: `export/{transactions,finance,vehicle,fuel,service}/excel`.

CSV lama tetap tersedia (dengan BOM UTF-8 + paginasi id) untuk kompatibilitas dan test.

## Konsekuensi

- Tombol "Export Excel" kini benar-benar menghasilkan file `.xlsx`.
- Test memverifikasi isi file dengan membuka zip XLSX (`ZipArchive`) — bukan sekadar header.
