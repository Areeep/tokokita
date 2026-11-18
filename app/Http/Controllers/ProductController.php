<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Mengelola CRUD produk toko.
 *
 * Semua route di controller ini berada di balik middleware `auth`.
 * Ubah dan hapus dibatasi oleh ProductPolicy: admin atau pemilik produk.
 */
class ProductController extends Controller
{
    /** Foto bawaan untuk produk tanpa gambar; tidak boleh dihapus dari disk. */
    private const DEFAULT_PHOTO = 'products/noimage.png';

    /**
     * Menampilkan daftar produk terbaru dengan paginasi 12 item.
     * Kategori di-eager-load untuk menghindari query N+1.
     */
    public function index(): View
    {
        $products = Product::with('category')->latest()->paginate(12);

        return view('products.index', compact('products'));
    }

    /**
     * Menampilkan form tambah produk.
     */
    public function create(): View
    {
        $categories = Category::orderBy('nama')->get();

        return view('products.create', compact('categories'));
    }

    /**
     * Menyimpan produk baru milik pengguna yang sedang login.
     *
     * Aturan validasi: nama wajib, harga wajib numerik, foto wajib
     * (jpeg/png/jpg), kategori opsional tetapi harus ada di tabel categories.
     * Bila penyimpanan foto gagal, kesalahan dicatat ke log dan pengguna
     * dikembalikan ke form dengan pesan galat.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,png,jpg',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        try {
            $path = $request->file('foto')->store('products', 'public');
        } catch (Throwable $e) {
            return $this->photoFailed($request, $e);
        }

        Product::create([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'foto' => $path,
            'user_id' => $request->user()->id,
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('products.index')->with('success', 'New Product Added!');
    }

    /**
     * Menampilkan form ubah produk.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException bila bukan pemilik atau admin.
     */
    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $categories = Category::orderBy('nama')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Memperbarui produk. Titik pada harga dibuang karena input memakai mask
     * pemisah ribuan. Foto lama dihapus dari disk saat diganti, kecuali foto bawaan.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException bila bukan pemilik atau admin.
     */
    public function update(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $product->nama = $request->nama;
        $product->harga = str_replace('.', '', $request->harga);
        $product->deskripsi = $request->deskripsi;
        $product->category_id = $request->category_id;

        if ($request->file('foto')) {
            try {
                $path = $request->file('foto')->store('products', 'public');
            } catch (Throwable $e) {
                return $this->photoFailed($request, $e);
            }

            if ($product->foto !== self::DEFAULT_PHOTO) {
                Storage::disk('public')->delete($product->foto);
            }
            $product->foto = $path;
        }

        $product->update();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product Updated!');
    }

    /**
     * Menghapus produk beserta fotonya (kecuali foto bawaan).
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException bila bukan pemilik atau admin.
     */
    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        if ($product->foto !== self::DEFAULT_PHOTO) {
            Storage::disk('public')->delete($product->foto);
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product Deleted!');
    }

    /**
     * Mencatat galat penyimpanan foto ke storage/logs/laravel.log lalu
     * mengembalikan pengguna ke form dengan input lama.
     */
    private function photoFailed(Request $request, Throwable $e): RedirectResponse
    {
        Log::error('Gagal menyimpan foto produk', [
            'user_id' => $request->user()?->id,
            'exception' => $e,
        ]);

        return back()
            ->withInput()
            ->withErrors(['foto' => 'Foto gagal disimpan, silakan coba lagi.']);
    }
}
