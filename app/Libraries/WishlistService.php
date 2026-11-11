<?php

namespace App\Libraries;

/** Wishlist for guests (session) and customers (database); merged at sign-in. */
class WishlistService
{
    private ?array $ids = null;

    public function ids(): array
    {
        if ($this->ids !== null) {
            return $this->ids;
        }
        if ($customerId = session('customer_id')) {
            $rows = db_connect()->table('wishlists')->select('product_id')->where('customer_id', $customerId)->get()->getResultArray();

            return $this->ids = array_map('intval', array_column($rows, 'product_id'));
        }

        return $this->ids = array_map('intval', session('wishlist') ?? []);
    }

    /** Adds or removes a product; returns true when it is now in the wishlist. */
    public function toggle(int $productId): bool
    {
        $ids    = $this->ids();
        $exists = in_array($productId, $ids, true);
        $db     = db_connect();

        if ($customerId = session('customer_id')) {
            if ($exists) {
                $db->table('wishlists')->where(['customer_id' => $customerId, 'product_id' => $productId])->delete();
            } else {
                $db->table('wishlists')->ignore(true)->insert(['customer_id' => $customerId, 'product_id' => $productId, 'created_at' => date('Y-m-d H:i:s')]);
            }
        } else {
            $ids = $exists ? array_values(array_diff($ids, [$productId])) : array_merge($ids, [$productId]);
            session()->set('wishlist', $ids);
        }
        $this->ids = null;

        return ! $exists;
    }

    public function attachCustomer(int $customerId): void
    {
        $db = db_connect();
        foreach (array_map('intval', session('wishlist') ?? []) as $pid) {
            $db->table('wishlists')->ignore(true)->insert(['customer_id' => $customerId, 'product_id' => $pid, 'created_at' => date('Y-m-d H:i:s')]);
        }
        session()->remove('wishlist');
        $this->ids = null;
    }

    public function products(): array
    {
        $ids = $this->ids();

        return $ids ? model('ProductModel')->catalogue()->whereIn('products.id', $ids)->findAll() : [];
    }
}
