<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        $products = model('ProductModel');
        $page     = model('PageModel')->where('slug', 'home')->first();
        $sections = [];
        if ($page) {
            foreach (model('PageSectionModel')->where(['page_id' => $page['id'], 'is_active' => 1])->orderBy('sort_order')->findAll() as $s) {
                $sections[$s['section_key']] = $s;
            }
        }

        $data = [
            'banners'      => model('BannerModel')->where('is_active', 1)->orderBy('sort_order')->findAll(),
            's'            => $sections,
            'featured'     => $products->catalogue()->where('products.is_featured', 1)->findAll(4),
            'bestsellers'  => $products->catalogue(['sort' => 'featured'])->where('products.is_bestseller', 1)->findAll(4),
            'categories'   => db_connect()->table('product_categories c')
                ->select('c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_visible = 1 AND p.deleted_at IS NULL) AS product_count,
                    (SELECT pi.path FROM products p JOIN product_images pi ON pi.product_id = p.id WHERE p.category_id = c.id AND p.is_visible = 1 AND p.deleted_at IS NULL
                     ORDER BY p.sort_order, pi.is_primary DESC, pi.sort_order LIMIT 1) AS cover', false)
                ->where('c.is_active', 1)->orderBy('c.sort_order')->get()->getResultArray(),
            'testimonials' => model('TestimonialModel')->where('is_active', 1)->orderBy('sort_order')->findAll(),
            'posts'        => model('BlogPostModel')->where('is_published', 1)->orderBy('published_at', 'DESC')->orderBy('id', 'DESC')->findAll(3),
            'faqs'         => model('FaqModel')->where(['is_active' => 1, 'show_on_home' => 1])->orderBy('sort_order')->findAll(5),
            'certs'        => model('CertificationModel')->where('is_active', 1)->orderBy('sort_order')->findAll(),
        ];

        $seo = $this->seo([
            'title'       => $page['meta_title'] ?? 'Sugarlif — Natural Low GI Foods',
            'description' => $page['meta_description'] ?? '',
            'canonical'   => site_url('/'),
            'jsonld'      => [[
                '@context' => 'https://schema.org', '@type' => 'Organization',
                'name' => setting('company_name', 'Natural Life Speciality Pvt. Ltd.'), 'brand' => setting('site_name', 'Sugarlif'),
                'url' => site_url('/'), 'logo' => media_url(setting('logo'), 'original'),
                'email' => setting('contact_email_primary'), 'telephone' => setting('contact_phone'),
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => setting('address_line1'), 'addressLocality' => setting('address_city'),
                    'postalCode' => setting('address_postcode'), 'addressRegion' => setting('address_state'), 'addressCountry' => 'IN'],
            ], [
                '@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => setting('site_name', 'Sugarlif'), 'url' => site_url('/'),
                'potentialAction' => ['@type' => 'SearchAction', 'target' => site_url('shop') . '?q={search_term_string}', 'query-input' => 'required name=search_term_string'],
            ]],
        ], 'home');

        return $this->render('home/index', $data, $seo);
    }
}
