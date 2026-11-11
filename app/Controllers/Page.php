<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * CMS pages. Each page row names a template; every template has its own view and layout
 * in app/Views/pages/. Section content comes from `page_sections`, keyed by section_key.
 */
class Page extends BaseController
{
    public const TEMPLATES = [
        'about'           => 'Company story (Our Profile)',
        'team'            => 'Team',
        'certifications'  => 'Certifications',
        'glycemic-index'  => 'Education — Glycemic Index',
        'health-benefits' => 'Education — Health benefits',
        'diet-comparison' => 'Education — Diet comparison',
        'product-story'   => 'Product story',
        'applications'    => 'Applications',
        'faq'             => 'FAQ',
        'contact'         => 'Contact',
        'legal'           => 'Legal / policy',
        'default'         => 'Simple text page',
    ];

    public function show(string $slug): string
    {
        $page = model('PageModel')->where(['slug' => $slug, 'is_published' => 1])->first();
        if (! $page || $page['slug'] === 'home') {
            throw PageNotFoundException::forPageNotFound();
        }

        $sections = [];
        foreach (model('PageSectionModel')->where(['page_id' => $page['id'], 'is_active' => 1])->orderBy('sort_order')->findAll() as $s) {
            $sections[$s['section_key']] = $s;
        }

        $template = isset(self::TEMPLATES[$page['template']]) ? $page['template'] : 'default';
        $data     = ['page' => $page, 's' => $sections, 'breadcrumbs' => [['Home', site_url('/')], [$page['title'], null]]];
        $jsonld   = [];

        switch ($template) {
            case 'team':
                $data['members'] = model('TeamMemberModel')->where('is_active', 1)->orderBy('sort_order')->findAll();
                break;

            case 'certifications':
            case 'about':
                $data['certs'] = model('CertificationModel')->where('is_active', 1)->orderBy('sort_order')->findAll();
                break;

            case 'faq':
                $data['faqs'] = model('FaqModel')->where('is_active', 1)->orderBy('sort_order')->findAll();
                $jsonld[]     = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(static fn ($f) => [
                    '@type' => 'Question', 'name' => $f['question'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => plain($f['answer'])],
                ], $data['faqs'])];
                break;

            case 'product-story':
            case 'applications':
                // "Shop this product" block: the section's CTA URL points at a product.
                $shop = $sections['shop'] ?? null;
                if ($shop && preg_match('#/product/([a-z0-9\-]+)#', (string) $shop['cta_url'], $m)) {
                    $data['product'] = model('ProductModel')->findBySlug($m[1]);
                }
                break;
        }

        return $this->render('pages/' . $template, $data, $this->seo([
            'title'       => $page['meta_title'] ?: $page['title'],
            'description' => $page['meta_description'] ?: plain($page['intro'] ?: $page['body'], 160),
            'canonical'   => site_url($page['slug']),
            'og_image'    => $page['og_image'] ? media_url($page['og_image'], 'lg') : null,
            'breadcrumbs' => $data['breadcrumbs'],
            'jsonld'      => $jsonld,
        ]));
    }

    public function notFound(): string
    {
        throw PageNotFoundException::forPageNotFound();
    }
}
