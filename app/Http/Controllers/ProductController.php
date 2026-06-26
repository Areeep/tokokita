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
}
