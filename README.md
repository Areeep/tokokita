# Tokokita

Aplikasi manajemen produk toko berbasis Laravel 12. Pengguna yang sudah login
dapat menambah, mengubah, dan menghapus produk beserta foto dan kategorinya.
Admin dapat mengelola semua produk; penjual hanya produk miliknya.

## Fitur

- Autentikasi (Laravel Breeze): daftar, login, reset kata sandi, profil.
- CRUD produk dengan upload foto, validasi, dan paginasi 12 item.
- Kategori produk (foreign key `products.category_id`).
- Peran `admin` dan `user` (kolom `users.role`), dibatasi lewat `ProductPolicy`.
- Mask input harga dengan Alpine.js.

## Struktur

| Lokasi | Isi |
|---|---|
| `app/Http/Controllers/ProductController.php` | CRUD produk, otorisasi, pencatatan galat upload |
| `app/Models` | `Product`, `Category`, `User` |
| `app/Policies/ProductPolicy.php` | Aturan ubah dan hapus (admin atau pemilik) |
| `database/migrations` | Skema tabel, foreign key, dan indeks |
| `database/factories`, `database/seeders` | Data contoh dan data uji |
| `resources/views/products` | Tampilan daftar, tambah, ubah, hapus |
| `tests` | Test fitur, unit, dan basis data |
| `docs/dependensi.md` | Daftar dependensi, lisensi, dan prosedur pembaruan |

## Menjalankan

Prasyarat: PHP 8.2+, Composer, Node.js, MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# atur DB_DATABASE, DB_USERNAME, DB_PASSWORD di .env, buat databasenya
php artisan migrate --seed
php artisan storage:link
npm run build        # atau: npm run dev
php artisan serve
```

Akun contoh dari seeder (kata sandi `password`): `admin@example.com` (admin)
dan dua penjual dengan email acak (lihat tabel `users`).

## Koneksi database dan keamanan

- Kredensial hanya di `.env` (tidak di-commit). Gunakan user MySQL khusus dengan
  hak akses terbatas pada database aplikasi.
- Koneksi dienkripsi dengan TLS bila `MYSQL_ATTR_SSL_CA` diisi path sertifikat CA
  (dibaca oleh `config/database.php`).
- Data session dienkripsi (`SESSION_ENCRYPT=true`).
- Query lewat Eloquent memakai parameter terikat (parameterized), sehingga
  aman dari SQL injection.
- Hak akses per peran: `ProductPolicy` (admin atau pemilik produk).

## Pengujian

```bash
php artisan test
```

Test memakai SQLite in-memory (lihat `phpunit.xml`). Bila PHP tidak punya
ekstensi `pdo_sqlite`, jalankan dengan MySQL:

```bash
DB_CONNECTION=mysql DB_DATABASE=test_tokokita DB_USERNAME=... php artisan test
```

Cakupan: CRUD produk, validasi negatif, otorisasi pemilik/admin, penghapusan
foto, foreign key `nullOnDelete`, seeder, jumlah query konstan (tanpa N+1), dan
batas waktu eksekusi query (`tests/Feature/ProductDatabaseTest.php`).

## Debugging

| Alat | Cara pakai |
|---|---|
| Log aplikasi | `storage/logs/laravel.log`; galat upload foto dicatat dengan `Log::error` |
| Laravel Pail | `php artisan pail` untuk melihat log secara langsung |
| Laravel Telescope | `/telescope` di environment `local`, hanya untuk admin (gate `viewTelescope`) |

## Dokumentasi lain

- Riwayat perubahan: [`CHANGELOG.md`](CHANGELOG.md)
- Dependensi dan lisensi: [`docs/dependensi.md`](docs/dependensi.md)

## Lisensi

MIT (lihat `composer.json`).
