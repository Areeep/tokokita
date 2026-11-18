# Changelog

Semua perubahan penting pada Tokokita dicatat di sini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/id-ID/1.1.0/).

## [Unreleased]

### Ditambahkan
- Kategori produk (`categories`) dengan foreign key `products.category_id`.
- Foreign key pemilik `products.user_id` dan indeks `products.nama`.
- Peran `admin` dan `user` (`users.role`) serta `ProductPolicy`.
- `ProductFactory`, `CategoryFactory`, `UserFactory::admin()`, dan seeder data contoh.
- Test fitur, unit, dan basis data untuk produk (21 test baru).
- Laravel Telescope sebagai alat debugging (dependensi dev, hanya `local`).
- Pencatatan galat upload foto ke `storage/logs/laravel.log`.
- README, `docs/dependensi.md`, dan changelog ini.

### Diubah
- `.env.example` memakai MySQL dengan opsi TLS (`MYSQL_ATTR_SSL_CA`); session terenkripsi.
- `ProductController` memakai PHPDoc, otorisasi, dan eager loading kategori.
- Dependensi diperbarui dalam batas versi: Laravel 12.62 → 12.73, Pail 1.3, Pint 1.32,
  Alpine.js 3.17, axios 1.21, Vite 7.3.8.

### Diperbaiki
- Halaman `/profile` error 500 karena partial `delete-user-form` dan `delete-product`
  tertukar foldernya.
- Menyimpan produk dengan deskripsi kosong menyebabkan galat 500 (kolom `deskripsi`
  sebelumnya NOT NULL); kolom kini nullable.

## [0.1.0] - 2026-06-26

### Ditambahkan
- Inisialisasi aplikasi Laravel 12 dengan Breeze.
- Daftar produk dengan paginasi dan footer.
- Tambah, ubah, dan hapus produk dengan validasi, upload foto, dan modal konfirmasi.
