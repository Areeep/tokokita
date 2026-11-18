<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjadikan deskripsi opsional: form mengirim string kosong yang diubah
     * Laravel menjadi null, sebelumnya menyebabkan galat 500 saat disimpan.
     * Menambah foreign key pemilik (users) dan kategori (categories) pada
     * tabel products, serta indeks pada kolom nama untuk mempercepat pencarian.
     * Kedua foreign key nullable agar data produk lama tetap valid.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->index('nama');
            $table->string('deskripsi')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('category_id');
            $table->dropIndex(['nama']);
            $table->string('deskripsi')->nullable(false)->change();
        });
    }
};
