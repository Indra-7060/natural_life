<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Product extends BaseController
{
    public function show(string $slug): string
    {
        $model   = model('ProductModel');
        $product = $model->findBySlug($slug);
        if (! $product) {
            throw PageNotFoundException::forPageNotFound();
        }
        $product = $model->withDetails($product);

        $crumb = [['Home', site_url('/')], ['Shop', site_url('shop')]];
        if ($product['category_slug']) {
            $crumb[] = [$product['category_name'], site_url('product-category/' . $product['category_slug'])];
        }
        $crumb[] = [$product['name'], null];

        // Product structured data — offers reflect the live price list.
        $url    = site_url('product/' . $product['slug']);
        $offers = [];
        $active = array_values(array_filter($product['variations'], static fn ($v) => $v['is_active']));
        if ($product['type'] === 'variable' && $active) {
            foreach ($active as $v) {
                $offers[] = [
                    '@type' => 'Offer', 'name' => $v['label'], 'priceCurrency' => 'INR',
                    'price' => number_format((float) ($v['sale_price'] ?? $v['regular_price']), 2, '.', ''),
                    'availability' => 'https://schema.org/' . ($v['stock_status'] === 'in_stock' ? 'InStock' : 'OutOfStock'), 'url' => $url,
                ];
            }
        } else {
            $offers = [
                '@type' => 'Offer', 'priceCurrency' => 'INR',
                'price' => number_format((float) ($product['sale_price'] ?? $product['regular_price']), 2, '.', ''),
                'availability' => 'https://schema.org/' . ($product['stock_status'] === 'in_stock' ? 'InStock' : 'OutOfStock'), 'url' => $url,
            ];
        }

        $seo = $this->seo([
            'title'       => $product['meta_title'] ?: $product['name'],
            'description' => $product['meta_description'] ?: plain($product['description'] ?: $product['short_description'], 160),
            'canonical'   => $url,
            'og_image'    => $product['image'] ? media_url($product['image'], 'lg') : null,
            'og_type'     => 'product',
            'breadcrumbs' => $crumb,
            'jsonld'      => [[
                '@context' => 'https://schema.org', '@type' => 'Product', 'name' => $product['name'],
                'description' => plain($product['description'] ?: $product['short_description'], 500),
                'image' => array_map(static fn ($i) => media_url($i['path'], 'lg'), $product['images']),
                'brand' => ['@type' => 'Brand', 'name' => setting('site_name', 'Sugarlif')],
                'category' => $product['category_name'], 'offers' => $offers,
            ]],
        ]);

        return $this->render('shop/product', [
            'product'     => $product,
            'related'     => $model->related($product, 4),
            'breadcrumbs' => $crumb,
            'inWishlist'  => in_array((int) $product['id'], $this->wishlist->ids(), true),
        ], $seo);
    }
}
