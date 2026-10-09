# 0003 — Full Migration ke Livewire 4

## Status

Accepted (2026-10-09)

## Konteks

Seluruh halaman aplikasi sebelumnya Blade + controller penuh (form POST biasa, GET query untuk filter/pagination). Livewire v4 sudah terpasang sejak awal proyek tetapi belum digunakan. Konversi ke Livewire diminta agar state halaman (filter, modal, live search) ditangani server-side tanpa full page reload, dengan batasan:

- Tidak boleh menurunkan cakupan test (240 test hijau saat mulai).
- Locale `id` / `Asia/Jakarta` dan seluruh perilaku bisnis harus tetap.

## Keputusan

**Pola island** — 11 tahap commit granular:

1. Route GET, controller, policy, dan FormRequest **tetap**; view berubah jadi wrapper (`<x-app-layout>` + `<livewire:nama />`); controller index/create disederhanakan, edit tetap melempar model.
2. Komponen memakai **Single-File Component (SFC)** di `resources/views/components/*.blade.php` (scaffold `php artisan make:livewire`, `config/livewire.php` dengan `make_command.emoji = false`).
3. **Satu sumber validasi**: trait static di `app/Http/Requests/Concerns/*`; komponen memanggil method statis FormRequest via `Validator::make` — rule tidak pernah diduplikasi.
4. **Route write (POST/PATCH/DELETE) dipertahankan** meski UI tak lagi memakainya — diuji test HTTP sebagai guard/regresi (lihat Konsekuensi).
5. Halaman **auth Breeze tetap Blade** (login/register/reset dsb) — logic throttling/broker tidak diduplikasi ke komponen; hanya profil yang dikonversi.
6. Laporan & admin: controller tetap menghitung data (menjaga `viewData` test) sambil komponen menghitung sendiri untuk render interaktif.
7. Test baru `Livewire*Test.php` per domain; test lama yang menguji view dirombak minimal (`viewData()` pengganti `assertViewHas`, `assertSee` pengganti assert terhadap view tertentu).

## Konsekuensi

- Satu root element per komponen (Livewire menolak multi-root — wajib satu pembungkus `<div>`).
- `@push('scripts')` tidak sampai dari dalam komponen → script Chart.js pakai `<script>` inline biasa.
- Segmen komponen tidak boleh mencampur `@php(expr)` dengan blok `@php…@endphp` dalam satu view (pitfall `BladeCompiler` memasangkan `@php` pertama dengan `@endphp` pertama).
- Duplikasi ringan data laporan (controller + komponen menghitung) — diterima demi test guard tanpa rombak.
- Route write menjadi API/backward-compat layer; logika tersimpan di dua tempat (controller & komponen) — perhatian saat mengubah aturan bisnis di kemudian hari.
