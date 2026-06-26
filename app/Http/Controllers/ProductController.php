<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index() {
        $products = Product::paginate(12);

        return view('products.index', compact('products'));
    }

    public function create() {
        return view('products.create');
    }

    public function store(Request $request) {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
            'foto' => 'required|image|mimes:jpeg,png,jpg'
        ]);

        $path = $request->file('foto')->store('products', 'public');

        // dd(
        //     $foto->hashName(),
        //     Storage::disk('public')->exists($foto->hashName())
        // );

        Product::create([
            'nama' => $request->nama,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'foto' => $path,
        ]);

        return redirect()->route('products.index')->with('success', 'New Product Added!');
    }

    public function edit(Product $product) {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product) {
        $request->validate([
            'nama' => 'required',
            'harga' => 'required|numeric',
        ]);

        $product->nama = $request->nama;
        $product->harga = str_replace(".", "", $request->harga);
        $product->deskripsi = $request->deskripsi;

        if($request->file('foto')) {
            if ($product->foto !== 'products/noimage.png') {
                Storage::disk('public')->delete($product->foto);
            }
            $path = $request->file('foto')->store('products', 'public');
            $product->foto = $path;
        }

        $product->update();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product Updated!');
    }
}
