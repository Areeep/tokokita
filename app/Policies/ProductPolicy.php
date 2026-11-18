<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Admin boleh mengubah produk apa pun; penjual hanya produk miliknya.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->isAdmin() || $product->user_id === $user->id;
    }

    /**
     * Aturan hapus sama dengan aturan ubah.
     */
    public function delete(User $user, Product $product): bool
    {
        return $this->update($user, $product);
    }
}
