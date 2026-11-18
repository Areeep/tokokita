# Dependensi, Lisensi, dan Pembaruan

Dokumen ini mencatat library pre-existing yang dipakai Tokokita, lisensinya,
cara memperbarui, dan perkiraan efisiensi pemakaiannya. Data per 2026-11-18.

## 1. Dependensi utama dan ketergantungannya

| Library | Dipakai untuk | Dipakai di | Lisensi |
|---|---|---|---|
| `laravel/framework` ^12 | routing, Eloquent ORM, validasi, session, storage | seluruh `app/`, `routes/` | MIT |
| `laravel/breeze` (dev) | scaffolding autentikasi | `app/Http/Controllers/Auth`, `resources/views/auth` | MIT |
| `laravel/telescope` (dev) | debugging request, query, exception | `app/Providers/TelescopeServiceProvider.php` | MIT |
| `laravel/pail` (dev) | tail log | `php artisan pail` | MIT |
| `pestphp/pest` + `pest-plugin-laravel` (dev) | runner test | `tests/` | MIT |
| `alpinejs`, `@alpinejs/mask` | interaksi UI dan mask harga | `resources/js/app.js`, `products/edit.blade.php` | MIT |
| `tailwindcss` 3 + `@tailwindcss/forms` | styling | `resources/css/app.css`, `tailwind.config.js` | MIT |
| `vite` + `laravel-vite-plugin` | bundler aset | `vite.config.js` | MIT |
| `axios` | HTTP client frontend | `resources/js/bootstrap.js` | MIT |

Ketergantungan antar unit:
`ProductController` → `Product` (Eloquent) → database; `ProductController` →
`ProductPolicy` → `User::isAdmin()`; view → Vite → Tailwind/Alpine.
Daftar lengkap dan turunannya ada di `composer.lock` dan `package-lock.json`.

## 2. Pemeriksaan lisensi

Hasil `composer licenses --no-dev` (77 paket produksi):

| Lisensi | Jumlah |
|---|---|
| MIT | 69 |
| BSD-3-Clause | 5 |
| Apache-2.0 (`phpoption/phpoption`) | 1 |
| BSD-3-Clause / GPL-2.0 / GPL-3.0 (dual, `nette/schema`, `nette/utils`) | 2 |

Semua lisensi permisif dan mengizinkan pemakaian, modifikasi, dan distribusi
ulang. Paket dual-license dipakai di bawah opsi BSD-3-Clause. Seluruh paket npm
yang dipakai langsung berlisensi MIT. Tidak ada paket berlisensi copyleft
kuat yang dipakai di bawah GPL, sehingga tidak ada kewajiban membuka source
aplikasi. Pemeriksaan ulang: jalankan `composer licenses` dan `npx license-checker`.

## 3. Menghindari komponen usang

- Versi PHP `^8.2` dan Laravel `^12` (didukung). Cek paket usang:
  `composer outdated --direct` dan `npm outdated`.
- `composer audit` dijalankan otomatis setiap `composer require/update`
  (hasil terakhir: tidak ada advisory keamanan).
- Major baru yang sengaja ditunda: Laravel 13, Pest 5, Tailwind 4, Vite 8,
  laravel-vite-plugin 3, karena membutuhkan perubahan kode (breaking changes).

## 4. Cara memperbarui library

```bash
composer outdated --direct        # lihat yang perlu diperbarui
composer update                   # perbarui dalam batas versi composer.json
npm outdated && npm update        # idem untuk npm
npm run build && php artisan test # verifikasi
```

Pembaruan 2026-11-18: 98 paket composer dan 8 paket npm diperbarui
(Laravel 12.62 → 12.73, Pail 1.2 → 1.3, Pint 1.29 → 1.32, Alpine 3.15 → 3.17,
axios 1.18 → 1.21, Vite 7.3.6 → 7.3.8). Build berhasil dan 46 test lulus
sebelum dan sesudah pembaruan (commit `chore(deps)`).

Untuk major upgrade: baca upgrade guide di dokumentasi resmi, buat branch,
perbarui `composer.json`, jalankan test, lalu perbaiki breaking changes.

## 5. Perhitungan efisiensi pemanfaatan komponen reuse

Perbandingan baris kode (ukuran 2026-11-18):

| Bagian | Baris PHP/JS |
|---|---|
| Kode buatan sendiri (`app`, `routes`, `database`, `tests`, view produk, `resources/js`) | 2.413 |
| `laravel/framework/src` yang dipakai sebagai library | 254.574 |

Aplikasi memakai kode framework sekitar **105 kali** lebih banyak dari kode
yang ditulis sendiri. Contoh penghematan nyata:

- Validasi, upload file, paginasi, dan otorisasi `ProductController` hanya
  172 baris karena memanggil `validate()`, `store()`, `paginate()`, dan `Gate`
  milik framework; menulisnya manual (parsing multipart, escape query,
  penomoran halaman, pengelolaan session) memerlukan ratusan baris tambahan.
- Autentikasi lengkap (9 controller, 6 view, request, test) dihasilkan satu
  perintah Breeze, dengan 25 test bawaan.
