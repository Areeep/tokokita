<x-app-layout>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 px-2">
        <div class="mt-6 flex justify-between items-center">
            <h2 class="font-semibold text-xl">List Products</h2>

            <button class="bg-gray-100 px-10 py-2 rounded-md font-semibold">Tambah</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 mt-4 gap-6">
            @foreach ($products as $product)
                <div class="">
                    <img src="{{ url('storage/' . $product->foto) }}" alt="product">
                    <div class="my-2">
                        <p class="text-xl font-light">{{ $product->nama }}</p>
                        <p class="font-semibold text-gray-400">Rp {{ number_format($product->harga) }}</p>
                    </div>
                    <button class="bg-gray-100 px-10 py-2 rounded-md w-full font-semibold">Edit</button>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
