# Catatan Debugging

Galat yang ditemukan lewat test dan log, analisis, perbaikan, dan verifikasinya.
Perintah verifikasi: `php artisan test` (lihat README).

## 1. Halaman `/profile` error 500

**Kode kesalahan** (dari `php artisan test`, `ProfileTest > profile page is displayed`):

```
Expected response status code [200] but received 500.
InvalidArgumentException: View [profile.partials.delete-user-form] not found.
```

**Analisis.** `resources/views/profile/edit.blade.php` memakai
`@include('profile.partials.delete-user-form')`, tetapi file itu berada di
`resources/views/products/partials/`. Sebaliknya `products/edit.blade.php`
memanggil `profile.partials.delete-product`, padahal berkas ada di folder
`profile/partials/`. Dua partial tertukar foldernya.

**Perbaikan.** `git mv` kedua berkas ke folder yang sesuai dan perbarui satu
`@include` di `products/edit.blade.php` (commit `fix(views)`).

**Verifikasi.** Test naik dari 24 lulus dan 1 gagal menjadi 25 lulus.

## 2. Simpan produk dengan deskripsi kosong menyebabkan 500

**Kode kesalahan** (dari test baru `test_owner_can_update_product...`):

```
SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'deskripsi' cannot be null
(SQL: update `products` set `nama` = Nama Baru, `harga` = 125000, `deskripsi` = ?, ...)
```

**Analisis.** Form mengirim textarea kosong sebagai `""`; middleware
`ConvertEmptyStringsToNull` mengubahnya menjadi `null`. Kolom `deskripsi`
dibuat `NOT NULL` di migrasi awal, sedangkan validasi tidak mewajibkannya,
sehingga INSERT/UPDATE gagal.

**Perbaikan.** Migrasi `add_relations_to_products_table` menjadikan kolom
`nullable`. Ditambah test `test_description_is_optional_on_create_and_update`.

**Verifikasi.** 46 test lulus.

## 3. Test gagal di lingkungan tanpa aset Vite

**Kode kesalahan.** Enam test "screen can be rendered" gagal dengan status 500
karena `public/build/manifest.json` belum ada.

**Perbaikan.** Jalankan `npm run build` sebelum `php artisan test`.
Catatan ini ditambahkan ke README.

## Alat bantu debugging

| Alat | Kegunaan |
|---|---|
| `storage/logs/laravel.log` | log aplikasi; galat upload foto dicatat dengan `Log::error` di `ProductController::photoFailed` |
| `php artisan pail` | melihat log secara langsung |
| Laravel Telescope (`/telescope`, local, admin) | memeriksa request, query, exception |
| `php artisan test --filter=NamaTest` | menjalankan satu test untuk mengisolasi galat |
