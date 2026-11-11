<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table          = 'products';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = true;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'category_id', 'name', 'slug', 'sku', 'type', 'tagline', 'short_description', 'description', 'ingredients',
        'nutrition', 'pack_label', 'variation_label', 'regular_price', 'sale_price', 'price_min', 'price_max',
        'stock_status', 'manage_stock', 'stock_qty', 'is_featured', 'is_bestseller', 'is_visible', 'sort_order',
        'meta_title', 'meta_description', 'source_id',
    ];
    protected $useTimestamps = true;

    /** Columns every product card needs, including the two leading gallery images. */
    private const CARD_SELECT = 'products.*, product_categories.name AS category_name, product_categories.slug AS category_slug,
        (SELECT path FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS image,
        (SELECT path FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1 OFFSET 1) AS image_alt';

    /**
     * Storefront listing query. Supported filters: category (slug), q, min, max, stock, sale, sort.
     */
    public function catalogue(array $filters = []): static
    {
        $this->select(self::CARD_SELECT, false)
            ->join('product_categories', 'product_categories.id = products.category_id', 'left')
            ->where('products.is_visible', 1);

        if (! empty($filters['category'])) {
            $this->where('product_categories.slug', $filters['category']);
        }
        if (! empty($filters['q'])) {
            $term = trim((string) $filters['q']);
            $this->groupStart()
                ->like('products.name', $term)
                ->orLike('products.short_description', $term)
                ->orLike('products.tagline', $term)
                ->orLike('product_categories.name', $term)
                ->groupEnd();
        }
        if (isset($filters['min']) && $filters['min'] !== '' && is_numeric($filters['min'])) {
            $this->where('products.price_max >=', (float) $filters['min']);
        }
        if (isset($filters['max']) && $filters['max'] !== '' && is_numeric($filters['max'])) {
            $this->where('products.price_min <=', (float) $filters['max']);
        }
        if (! empty($filters['stock'])) {
            $this->where('products.stock_status', 'in_stock');
        }
        if (! empty($filters['sale'])) {
            $this->groupStart()
                ->where('products.sale_price IS NOT NULL', null, false)
                ->orWhere('EXISTS (SELECT 1 FROM product_variations pv WHERE pv.product_id = products.id AND pv.sale_price IS NOT NULL AND pv.is_active = 1)', null, false)
                ->groupEnd();
        }

        // Name matches rank above description / category matches in search results.
        if (! empty($filters['q'])) {
            $this->orderBy('(products.name LIKE ' . $this->db->escape('%' . $this->db->escapeLikeString(trim((string) $filters['q'])) . '%') . ')', 'DESC', false);
        }

        match ($filters['sort'] ?? 'featured') {
            'price-asc'  => $this->orderBy('products.price_min', 'ASC'),
            'price-desc' => $this->orderBy('products.price_min', 'DESC'),
            'name'       => $this->orderBy('products.name', 'ASC'),
            'newest'     => $this->orderBy('products.created_at', 'DESC')->orderBy('products.id', 'DESC'),
            default      => $this->orderBy('products.is_featured', 'DESC')->orderBy('products.sort_order', 'ASC'),
        };

        return $this;
    }

    public function findBySlug(string $slug, bool $onlyVisible = true): ?array
    {
        $b = $this->select(self::CARD_SELECT, false)
            ->join('product_categories', 'product_categories.id = products.category_id', 'left')
            ->where('products.slug', $slug);
        if ($onlyVisible) {
            $b->where('products.is_visible', 1);
        }

        return $b->first();
    }

    /** Adds gallery, variations and specification rows to a product row. */
    public function withDetails(array $product): array
    {
        $db = $this->db;
        $product['images'] = $db->table('product_images')->where('product_id', $product['id'])
            ->orderBy('is_primary', 'DESC')->orderBy('sort_order')->orderBy('id')->get()->getResultArray();
        $product['variations'] = $db->table('product_variations')->where('product_id', $product['id'])
            ->orderBy('sort_order')->orderBy('id')->get()->getResultArray();
        $product['attributes'] = $db->table('product_attributes')->where('product_id', $product['id'])
            ->orderBy('sort_order')->orderBy('id')->get()->getResultArray();

        return $product;
    }

    /**
     * Keeps the denormalised listing columns (price range, stock status) in step with the
     * product's own prices or, for variable products, its active variations.
     */
    public function syncDerived(int $productId): void
    {
        $p = $this->withDeleted()->find($productId);
        if (! $p) {
            return;
        }
        if ($p['type'] === 'variable') {
            $vars = $this->db->table('product_variations')->where(['product_id' => $productId, 'is_active' => 1])->get()->getResultArray();
            if ($vars === []) {
                return;
            }
            $prices  = array_map(static fn ($v) => (float) ($v['sale_price'] ?? $v['regular_price']), $vars);
            $inStock = array_filter($vars, static fn ($v) => $v['stock_status'] === 'in_stock');
            $this->protect(false)->update($productId, [
                'price_min'     => min($prices),
                'price_max'     => max($prices),
                'regular_price' => min(array_map(static fn ($v) => (float) $v['regular_price'], $vars)),
                'stock_status'  => $inStock === [] ? 'out_of_stock' : 'in_stock',
            ]);
            $this->protect(true);
        } else {
            $price = (float) ($p['sale_price'] ?? $p['regular_price']);
            $this->update($productId, ['price_min' => $price, 'price_max' => $price]);
        }
    }

    public function related(array $product, int $limit = 4): array
    {
        return $this->catalogue()
            ->where('products.id !=', $product['id'])
            ->orderBy('(products.category_id = ' . (int) ($product['category_id'] ?? 0) . ')', 'DESC', false)
            ->findAll($limit);
    }
}
