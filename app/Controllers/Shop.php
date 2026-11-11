<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Shop extends BaseController
{
    private const PER_PAGE = 9;

    public function index(): string
    {
        return $this->listing(null);
    }

    public function category(string $slug): string
    {
        $category = model('ProductCategoryModel')->where(['slug' => $slug, 'is_active' => 1])->first();
        if (! $category) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->listing($category);
    }

    private function listing(?array $category): string
    {
        $filters = [
            'category' => $category['slug'] ?? trim((string) $this->request->getGet('category')),
            'q'        => mb_substr(trim((string) $this->request->getGet('q')), 0, 80),
            'min'      => $this->request->getGet('min'),
            'max'      => $this->request->getGet('max'),
            'stock'    => $this->request->getGet('stock') ? 1 : 0,
            'sale'     => $this->request->getGet('sale') ? 1 : 0,
            'sort'     => in_array($this->request->getGet('sort'), ['featured', 'price-asc', 'price-desc', 'name', 'newest'], true) ? $this->request->getGet('sort') : 'featured',
        ];

        $model    = model('ProductModel');
        $products = $model->catalogue($filters)->paginate(self::PER_PAGE, 'shop');
        $db       = db_connect();
        $bounds   = $db->table('products')->select('MIN(price_min) AS lo, MAX(price_max) AS hi')->where(['is_visible' => 1, 'deleted_at' => null])->get()->getRowArray();

        $title = $category['name'] ?? ($filters['q'] !== '' ? 'Search: ' . $filters['q'] : 'Shop all products');
        $crumb = [['Home', site_url('/')], ['Shop', site_url('shop')]];
        if ($category) {
            $crumb[] = [$category['name'], null];
        }

        $seo = $this->seo([
            'title'       => $category['meta_title'] ?? ($category ? $category['name'] . ' — Low GI ' . $category['name'] : 'Shop Low GI Foods'),
            'description' => $category['meta_description'] ?? ($category['description'] ?? 'Shop the Sugarlif range of natural Low GI foods: sugar, whole wheat atta, honey, jaggery and Indian sweets.'),
            'canonical'   => $category ? site_url('product-category/' . $category['slug']) : site_url('shop'),
            'noindex'     => $filters['q'] !== '',
            'breadcrumbs' => $crumb,
        ], $category ? null : 'shop');

        return $this->render('shop/index', [
            'products'   => $products,
            'pager'      => $model->pager,
            'total'      => $model->pager->getTotal('shop'),
            'filters'    => $filters,
            'category'   => $category,
            'categories' => $db->table('product_categories c')
                ->select('c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_visible = 1 AND p.deleted_at IS NULL) AS product_count', false)
                ->where('c.is_active', 1)->orderBy('c.sort_order')->get()->getResultArray(),
            'bounds'     => ['lo' => (int) floor((float) ($bounds['lo'] ?? 0)), 'hi' => (int) ceil((float) ($bounds['hi'] ?? 0))],
            'title'      => $title,
            'breadcrumbs' => $crumb,
        ], $seo);
    }

    /** Live search suggestions for the header search box (JSON). */
    public function suggest()
    {
        $q = mb_substr(trim((string) $this->request->getGet('q')), 0, 60);
        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['items' => []]);
        }
        $items = [];
        foreach (model('ProductModel')->catalogue(['q' => $q])->findAll(6) as $p) {
            $items[] = [
                'name'  => $p['name'],
                'url'   => site_url('product/' . $p['slug']),
                'image' => media_url($p['image'], 'sm'),
                'price' => strip_tags(product_price_html($p)),
            ];
        }

        return $this->response->setJSON(['items' => $items, 'all' => site_url('shop') . '?q=' . rawurlencode($q)]);
    }

    /** Quick-view modal body (HTML fragment). */
    public function quickView(string $slug): string
    {
        $model   = model('ProductModel');
        $product = $model->findBySlug($slug);
        if (! $product) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('shop/quick_view', ['product' => $model->withDetails($product), 'wishlistIds' => $this->wishlist->ids()]);
    }
}
