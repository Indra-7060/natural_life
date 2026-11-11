<?php

namespace App\Controllers;

/** XML sitemap and robots.txt, generated from live content. */
class Seo extends BaseController
{
    public function sitemap()
    {
        $urls = [[site_url('/'), date('Y-m-d'), '1.0'], [site_url('shop'), date('Y-m-d'), '0.9'], [site_url('blog'), date('Y-m-d'), '0.7']];
        $db   = db_connect();
        $day  = static fn ($d) => $d ? date('Y-m-d', strtotime((string) $d)) : date('Y-m-d');

        foreach ($db->table('products')->where(['is_visible' => 1, 'deleted_at' => null])->get()->getResultArray() as $p) {
            $urls[] = [site_url('product/' . $p['slug']), $day($p['updated_at']), '0.8'];
        }
        foreach ($db->table('product_categories')->where('is_active', 1)->get()->getResultArray() as $c) {
            $urls[] = [site_url('product-category/' . $c['slug']), $day($c['updated_at']), '0.6'];
        }
        foreach ($db->table('pages')->where('is_published', 1)->where('slug !=', 'home')->get()->getResultArray() as $p) {
            $urls[] = [site_url($p['slug']), $day($p['updated_at']), in_array($p['template'], ['legal', 'default'], true) ? '0.3' : '0.6'];
        }
        foreach ($db->table('blog_posts')->where('is_published', 1)->get()->getResultArray() as $p) {
            $urls[] = [site_url('blog/' . $p['slug']), $day($p['updated_at'] ?: $p['published_at']), '0.5'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $lastmod, $priority]) {
            $xml .= '  <url><loc>' . htmlspecialchars($loc, ENT_XML1) . '</loc><lastmod>' . $lastmod . '</lastmod><priority>' . $priority . "</priority></url>\n";
        }
        $xml .= '</urlset>';

        return $this->response->setContentType('application/xml')->setBody($xml);
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin/',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /my-account',
            'Disallow: /wishlist',
            'Disallow: /order/',
            'Disallow: /payment/',
            'Disallow: /*?q=',
            '',
            'Sitemap: ' . site_url('sitemap.xml'),
        ];

        return $this->response->setContentType('text/plain')->setBody(implode("\n", $lines) . "\n");
    }
}
