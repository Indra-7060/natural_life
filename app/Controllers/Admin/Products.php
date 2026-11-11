<?php

namespace App\Controllers\Admin;

use App\Libraries\MediaLibrary;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Product management: details, pricing, variations (pack sizes), specifications, gallery, stock, SEO. */
class Products extends AdminController
{
    protected string $module = 'catalogue';

    public function index(): string
    {
        $model = model('ProductModel');
        $q     = trim((string) $this->request->getGet('q'));
        $cat   = (int) $this->request->getGet('category');
        $stock = (string) $this->request->getGet('stock');

        $model->select('products.*, product_categories.name AS category_name,
            (SELECT path FROM product_images pi WHERE pi.product_id = products.id ORDER BY pi.is_primary DESC, pi.sort_order ASC, pi.id ASC LIMIT 1) AS image,
            (SELECT COUNT(*) FROM product_variations pv WHERE pv.product_id = products.id) AS variation_count', false)
            ->join('product_categories', 'product_categories.id = products.category_id', 'left');
        if ($q !== '') {
            $model->groupStart()->like('products.name', $q)->orLike('products.sku', $q)->groupEnd();
        }
        if ($cat) {
            $model->where('products.category_id', $cat);
        }
        if (in_array($stock, ['in_stock', 'out_of_stock'], true)) {
            $model->where('products.stock_status', $stock);
        }

        return $this->render('admin/products/index', [
            'title'       => 'Products',
            'breadcrumbs' => [['Catalogue', null], ['Products', null]],
            'rows'        => $model->orderBy('products.sort_order')->orderBy('products.id', 'DESC')->paginate(20, 'admin'),
            'pager'       => $model->pager,
            'categories'  => model('ProductCategoryModel')->orderBy('sort_order')->findAll(),
            'q'           => $q, 'cat' => $cat, 'stock' => $stock,
        ]);
    }

    public function create(): string
    {
        return $this->form(null);
    }

    public function edit(int $id): string
    {
        return $this->form(model('ProductModel')->withDetails($this->find($id)));
    }

    private function form(?array $product): string
    {
        return $this->render('admin/products/form', [
            'title'       => $product ? 'Edit product' : 'Add product',
            'breadcrumbs' => [['Catalogue', null], ['Products', site_url('admin/products')], [$product ? $product['name'] : 'Add', null]],
            'product'     => $product,
            'categories'  => model('ProductCategoryModel')->orderBy('sort_order')->findAll(),
        ]);
    }

    public function store()
    {
        return $this->save(null);
    }

    public function update(int $id)
    {
        $this->find($id);

        return $this->save($id);
    }

    private function save(?int $id)
    {
        $type  = $this->request->getPost('type') === 'variable' ? 'variable' : 'simple';
        $rules = [
            'name'             => ['label' => 'Name', 'rules' => 'required|max_length[190]'],
            'slug'             => ['label' => 'URL slug', 'rules' => 'permit_empty|max_length[220]|is_unique[products.slug' . ($id ? ',id,' . $id : '') . ']'],
            'category_id'      => ['label' => 'Category', 'rules' => 'permit_empty|is_natural_no_zero'],
            'tagline'          => ['label' => 'Tagline', 'rules' => 'permit_empty|max_length[190]'],
            'sku'              => ['label' => 'SKU', 'rules' => 'permit_empty|max_length[60]'],
            'stock_qty'        => ['label' => 'Stock quantity', 'rules' => 'permit_empty|is_natural'],
            'sort_order'       => ['label' => 'Display order', 'rules' => 'permit_empty|integer'],
            'meta_title'       => ['label' => 'SEO title', 'rules' => 'permit_empty|max_length[190]'],
            'meta_description' => ['label' => 'SEO description', 'rules' => 'permit_empty|max_length[320]'],
        ];
        if ($type === 'simple') {
            $rules['regular_price'] = ['label' => 'Regular price', 'rules' => 'required|decimal|greater_than[0]'];
            $rules['sale_price']    = ['label' => 'Sale price', 'rules' => 'permit_empty|decimal|greater_than[0]'];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $n    = static fn ($v) => ($v === '' || $v === null) ? null : $v;

        // Variations (pack sizes) — validated by hand because they arrive as arrays.
        $variations = [];
        if ($type === 'variable') {
            foreach ((array) ($post['v_label'] ?? []) as $i => $label) {
                $label = trim((string) $label);
                if ($label === '') {
                    continue;
                }
                $regular = (float) ($post['v_regular'][$i] ?? 0);
                $sale    = $n($post['v_sale'][$i] ?? null);
                if ($regular <= 0) {
                    return redirect()->back()->withInput()->with('error', "Enter a regular price for the “{$label}” pack.");
                }
                if ($sale !== null && ((float) $sale <= 0 || (float) $sale >= $regular)) {
                    return redirect()->back()->withInput()->with('error', "The sale price for “{$label}” must be lower than its regular price.");
                }
                $qty          = $n($post['v_qty'][$i] ?? null);
                $variations[] = [
                    'id'            => (int) ($post['v_id'][$i] ?? 0),
                    'label'         => mb_substr($label, 0, 80),
                    'sku'           => $n(trim((string) ($post['v_sku'][$i] ?? ''))),
                    'regular_price' => $regular,
                    'sale_price'    => $sale,
                    'stock_status'  => ($post['v_stock'][$i] ?? 'in_stock') === 'out_of_stock' ? 'out_of_stock' : 'in_stock',
                    'stock_qty'     => $qty === null ? null : max(0, (int) $qty),
                    'is_active'     => 1,
                    'sort_order'    => count($variations),
                ];
            }
            if ($variations === []) {
                return redirect()->back()->withInput()->with('error', 'Add at least one pack size, or switch the product type to “Single pack”.');
            }
        }
        if ($type === 'simple' && $n($post['sale_price'] ?? null) !== null && (float) $post['sale_price'] >= (float) $post['regular_price']) {
            return redirect()->back()->withInput()->with('error', 'The sale price must be lower than the regular price.');
        }

        $data = [
            'category_id'       => $n($post['category_id'] ?? null),
            'name'              => trim($post['name']),
            'slug'              => slugify(trim((string) ($post['slug'] ?? '')) ?: $post['name']),
            'sku'               => $n(trim((string) ($post['sku'] ?? ''))),
            'type'              => $type,
            'tagline'           => $n(trim((string) ($post['tagline'] ?? ''))),
            'short_description' => rich($post['short_description'] ?? ''),
            'description'       => rich($post['description'] ?? ''),
            'ingredients'       => $n(trim((string) ($post['ingredients'] ?? ''))),
            'nutrition'         => $n(trim((string) ($post['nutrition'] ?? ''))),
            'pack_label'        => $n(trim((string) ($post['pack_label'] ?? ''))),
            'variation_label'   => $n(trim((string) ($post['variation_label'] ?? ''))),
            'manage_stock'      => empty($post['manage_stock']) ? 0 : 1,
            'stock_qty'         => $n($post['stock_qty'] ?? null),
            'is_featured'       => empty($post['is_featured']) ? 0 : 1,
            'is_bestseller'     => empty($post['is_bestseller']) ? 0 : 1,
            'is_visible'        => empty($post['is_visible']) ? 0 : 1,
            'sort_order'        => (int) ($post['sort_order'] ?? 0),
            'meta_title'        => $n(trim((string) ($post['meta_title'] ?? ''))),
            'meta_description'  => $n(trim((string) ($post['meta_description'] ?? ''))),
        ];
        if ($type === 'simple') {
            $data['regular_price'] = (float) $post['regular_price'];
            $data['sale_price']    = $n($post['sale_price'] ?? null);
            $data['stock_status']  = ($post['stock_status'] ?? 'in_stock') === 'out_of_stock' ? 'out_of_stock' : 'in_stock';
            if ($data['manage_stock'] && $data['stock_qty'] !== null) {
                $data['stock_status'] = (int) $data['stock_qty'] > 0 ? 'in_stock' : 'out_of_stock';
            }
        }

        $db    = db_connect();
        $model = model('ProductModel');
        $db->transStart();

        if ($id) {
            $before = $model->find($id);
            $model->update($id, $data);
            if ($type === 'simple' && $data['manage_stock'] && (int) $data['stock_qty'] !== (int) $before['stock_qty']) {
                $db->table('stock_movements')->insert(['product_id' => $id, 'change_qty' => (int) $data['stock_qty'] - (int) $before['stock_qty'], 'reason' => 'Manual adjustment by ' . $this->admin['name'], 'created_at' => date('Y-m-d H:i:s')]);
            }
        } else {
            $id = (int) $model->insert($data, true);
        }

        // Variations: update kept rows, insert new ones, remove the rest.
        $vModel = model('ProductVariationModel');
        $keep   = [];
        foreach ($variations as $v) {
            $vid = $v['id'];
            unset($v['id']);
            if ($vid && $vModel->where(['id' => $vid, 'product_id' => $id])->first()) {
                $vModel->update($vid, $v);
            } else {
                $vid = (int) $vModel->insert($v + ['product_id' => $id], true);
            }
            $keep[] = $vid;
        }
        $del = $db->table('product_variations')->where('product_id', $id);
        if ($keep) {
            $del->whereNotIn('id', $keep);
        }
        $del->delete();

        // Specifications
        $db->table('product_attributes')->where('product_id', $id)->delete();
        $order = 0;
        foreach ((array) ($post['a_name'] ?? []) as $i => $name) {
            $name  = trim((string) $name);
            $value = trim((string) ($post['a_value'][$i] ?? ''));
            if ($name !== '' && $value !== '') {
                $db->table('product_attributes')->insert(['product_id' => $id, 'name' => mb_substr($name, 0, 120), 'value' => $value, 'sort_order' => $order++]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'The product could not be saved. Please try again.');
        }

        $model->syncDerived($id);
        $this->flushCache();

        return redirect()->to(site_url('admin/products/' . $id . '/edit'))->with('success', 'Product saved.');
    }

    public function delete(int $id)
    {
        $this->find($id);
        model('ProductModel')->delete($id); // soft delete: past orders keep their line items
        $this->flushCache();

        return $this->back('admin/products', 'Product deleted.');
    }

    public function toggle(int $id, string $field)
    {
        $product = $this->find($id);
        if (! in_array($field, ['is_visible', 'is_featured', 'is_bestseller'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
        model('ProductModel')->update($id, [$field => $product[$field] ? 0 : 1]);
        $this->flushCache();

        return redirect()->back()->with('success', 'Product updated.');
    }

    // ------------------------------------------------------------------ gallery

    public function uploadImages(int $id)
    {
        $product = $this->find($id);
        $files   = $this->request->getFileMultiple('images') ?? [];
        $library = new MediaLibrary();
        $db      = db_connect();
        $count   = $db->table('product_images')->where('product_id', $id)->countAllResults();
        $added   = 0;
        $errors  = [];

        foreach ($files as $file) {
            if (! $file || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            try {
                $media = $library->store($file, 'products', $product['name']);
                $db->table('product_images')->insert(['product_id' => $id, 'path' => $media['path'], 'alt_text' => $product['name'], 'is_primary' => $count === 0 ? 1 : 0, 'sort_order' => $count]);
                $count++;
                $added++;
            } catch (\RuntimeException $e) {
                $errors[] = $file->getClientName() . ': ' . $e->getMessage();
            }
        }
        $this->flushCache();
        $redirect = redirect()->to(site_url('admin/products/' . $id . '/edit') . '#gallery');

        return $errors ? $redirect->with('error', implode(' ', $errors)) : $redirect->with('success', $added . ' image(s) added.');
    }

    /** Adds an existing media-library image to the gallery. */
    public function attachImage(int $id)
    {
        $product = $this->find($id);
        $path    = trim((string) $this->request->getPost('path'));
        if (! model('MediaModel')->where('path', $path)->first()) {
            return redirect()->back()->with('error', 'Choose an image from the media library.');
        }
        $db    = db_connect();
        $count = $db->table('product_images')->where('product_id', $id)->countAllResults();
        $db->table('product_images')->insert(['product_id' => $id, 'path' => $path, 'alt_text' => $product['name'], 'is_primary' => $count === 0 ? 1 : 0, 'sort_order' => $count]);
        $this->flushCache();

        return redirect()->to(site_url('admin/products/' . $id . '/edit') . '#gallery')->with('success', 'Image added.');
    }

    public function primaryImage(int $imageId)
    {
        $db    = db_connect();
        $image = $db->table('product_images')->where('id', $imageId)->get()->getRowArray();
        if (! $image) {
            throw PageNotFoundException::forPageNotFound();
        }
        $db->table('product_images')->where('product_id', $image['product_id'])->update(['is_primary' => 0]);
        $db->table('product_images')->where('id', $imageId)->update(['is_primary' => 1]);
        $this->flushCache();

        return redirect()->to(site_url('admin/products/' . $image['product_id'] . '/edit') . '#gallery')->with('success', 'Featured image updated.');
    }

    /** Removes an image from the gallery. The file stays in the media library. */
    public function deleteImage(int $imageId)
    {
        $db    = db_connect();
        $image = $db->table('product_images')->where('id', $imageId)->get()->getRowArray();
        if (! $image) {
            throw PageNotFoundException::forPageNotFound();
        }
        $db->table('product_images')->where('id', $imageId)->delete();
        if ($image['is_primary']) {
            $next = $db->table('product_images')->where('product_id', $image['product_id'])->orderBy('sort_order')->get()->getRowArray();
            if ($next) {
                $db->table('product_images')->where('id', $next['id'])->update(['is_primary' => 1]);
            }
        }
        $this->flushCache();

        return redirect()->to(site_url('admin/products/' . $image['product_id'] . '/edit') . '#gallery')->with('success', 'Image removed from this product.');
    }

    private function find(int $id): array
    {
        $product = model('ProductModel')->find($id);
        if (! $product) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $product;
    }
}
