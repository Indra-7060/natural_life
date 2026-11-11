<?php

namespace App\Database\Seeds;

use App\Libraries\MediaLibrary;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\Seeder;

/**
 * Migrates the catalogue, blog, FAQs, testimonials, team, certifications, navigation,
 * SEO defaults and redirects captured from natural-life.in (see docs/analysis/).
 *
 * Product names, prices, variations, stock status, descriptions, testimonials and blog
 * text come unmodified from app/Database/Seeds/data/source.json, which is generated from
 * the crawl by docs/source-crawl/build-data.js.
 */
class ContentSeeder extends Seeder
{
    private MediaLibrary $media;
    private string $archive;

    public function run(): void
    {
        helper('site');
        if ($this->db->table('products')->countAllResults() > 0) {
            CLI::write('Catalogue already present - content seeding skipped (run "php spark migrate:refresh" first for a clean re-import).', 'yellow');

            return;
        }

        $src           = json_decode(file_get_contents(__DIR__ . '/data/source.json'), true);
        $this->media   = new MediaLibrary();
        $this->archive = ROOTPATH . 'docs/source-crawl/media/';
        $now           = date('Y-m-d H:i:s');
        $db            = $this->db;

        // ------------------------------------------------------------ brand assets
        foreach (['2023/07/sugarlife-logo-1.svg', '2023/07/sugarlife-logo-white-1.svg', '2023/07/cropped-sugarlife-logo-1.jpg', '2023/07/JioMart_logo.svg'] as $f) {
            $this->img($f, 'brand', 'Sugarlif');
        }

        // ------------------------------------------------------------ categories
        // "Sugar" exists on the source store. The other three are proposed groupings for the five
        // products the source left in WooCommerce's default "Uncategorized" (see docs/analysis/09).
        $categories = [
            'sugar'          => ['Sugar', 'Natural cane sugar made Low GI — in family packs and single-serve sachets.'],
            'flour-atta'     => ['Flour (Atta)', 'Low GI whole wheat atta made with 100% natural wheat grain.'],
            'honey-jaggery'  => ['Honey & Jaggery', 'Low GI forest honey and granulated jaggery.'],
            'indian-sweets'  => ['Indian Sweets', 'Traditional sweets sweetened with Sugarlif Low GI Sugar.'],
        ];
        $catId = [];
        $i     = 0;
        foreach ($categories as $slug => [$name, $desc]) {
            $db->table('product_categories')->insert(['name' => $name, 'slug' => $slug, 'description' => $desc, 'is_active' => 1, 'sort_order' => ++$i, 'created_at' => $now, 'updated_at' => $now]);
            $catId[$slug] = $db->insertID();
        }

        // ------------------------------------------------------------ products
        // Presentation extras per product. Taglines and pack labels restate facts already in the source copy.
        $extra = [
            'sugarlif-low-gi-diet-sugar' => ['cat' => 'sugar', 'order' => 1, 'featured' => 1, 'best' => 0, 'tagline' => 'Natural cane sugar with a low Glycemic Index', 'variation_label' => 'Pack Weight',
                'ingredients' => 'Cane sugar and Water based extracts of Fenugreek, Turmeric, Black Pepper, Ginger, Cinnamon, Gooseberry and Pomegranate.'],
            'sugarlif-low-gi-wheat-flour-atta' => ['cat' => 'flour-atta', 'order' => 2, 'featured' => 1, 'best' => 0, 'tagline' => '100% whole wheat grain, made Low GI', 'variation_label' => 'Pack Weight'],
            'sugarlif-honey' => ['cat' => 'honey-jaggery', 'order' => 3, 'featured' => 1, 'best' => 0, 'tagline' => 'Low GI wildflower forest honey', 'pack' => '500 Grams'],
            'sugarlif-low-gi-jaggery' => ['cat' => 'honey-jaggery', 'order' => 4, 'featured' => 1, 'best' => 1, 'tagline' => 'Granulated jaggery with a low Glycemic Index', 'pack' => '500 gm'],
            'sugarlif-low-gi-sugar-150-sachets-pack' => ['cat' => 'sugar', 'order' => 5, 'featured' => 0, 'best' => 1, 'tagline' => 'Low GI Sugar in single-serve sachets', 'pack' => '150 sachets',
                'ingredients' => 'Cane sugar and Water based extracts of Fenugreek, Turmeric, Black Pepper, Ginger, Cinnamon, Gooseberry and Pomegranate.'],
            'sugarlif-gulab-jamun-1-kg-pack-of-1-made-from-desi-ghee-sweetened-with-sugarlif-low-gi-diet-sugar' => ['cat' => 'indian-sweets', 'order' => 6, 'featured' => 0, 'best' => 1, 'tagline' => 'Made from desi ghee, sweetened with Sugarlif Low GI Sugar', 'pack' => '1 Kg'],
            'sugarlif-rasgollas-1-kg-pack-of-1-sweetened-with-sugarlif-low-gi-diet-sugar' => ['cat' => 'indian-sweets', 'order' => 7, 'featured' => 0, 'best' => 1, 'tagline' => 'Sweetened with Sugarlif Low GI Sugar', 'pack' => '1 Kg'],
        ];

        foreach ($src['products'] as $p) {
            $x     = $extra[$p['slug']];
            $short = preg_replace('#<p><strong>INGREDIENTS</strong>.*?</p>#s', '', $p['short_description']);
            $attrs = [];
            $nutrition = null;
            foreach ($p['attributes'] as $a) {
                if ($a['name'] === 'Nutritional Facts') {
                    $nutrition = $a['value'];
                } elseif ($a['name'] !== 'Package') { // "Package: Package" is a data-entry artefact on the source
                    $attrs[] = $a;
                }
            }

            $db->table('products')->insert([
                'category_id'       => $catId[$x['cat']],
                'name'              => $p['name'],
                'slug'              => $p['slug'],
                'sku'               => null,
                'type'              => $p['type'],
                'tagline'           => $x['tagline'],
                'short_description' => trim($short),
                'description'       => $p['description'],
                'ingredients'       => $x['ingredients'] ?? null,
                'nutrition'         => $nutrition,
                'pack_label'        => $x['pack'] ?? null,
                'variation_label'   => $x['variation_label'] ?? null,
                'regular_price'     => $p['regular_price'],
                'sale_price'        => $p['sale_price'],
                'price_min'         => $p['price_min'],
                'price_max'         => $p['price_max'],
                'stock_status'      => $p['in_stock'] ? 'in_stock' : 'out_of_stock',
                'manage_stock'      => 0,
                'is_featured'       => $x['featured'],
                'is_bestseller'     => $x['best'],
                'is_visible'        => 1,
                'sort_order'        => $x['order'],
                'meta_title'        => $p['name'] . ' — Sugarlif',
                'meta_description'  => plain($p['description'], 158),
                'source_id'         => $p['wp_id'],
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $pid = $db->insertID();

            foreach ($p['images'] as $n => $rel) {
                $m = $this->img($rel, 'products', $p['name'] . ($n ? ' — image ' . ($n + 1) : ''));
                if ($m) {
                    $db->table('product_images')->insert(['product_id' => $pid, 'path' => $m, 'alt_text' => $p['name'] . ($n ? ' — image ' . ($n + 1) : ''), 'is_primary' => $n === 0 ? 1 : 0, 'sort_order' => $n]);
                }
            }
            foreach ($p['variations'] as $n => $v) {
                $db->table('product_variations')->insert([
                    'product_id' => $pid, 'label' => $v['label'], 'sku' => null, 'regular_price' => $v['regular_price'], 'sale_price' => $v['sale_price'],
                    'stock_status' => $v['in_stock'] ? 'in_stock' : 'out_of_stock', 'is_active' => 1, 'sort_order' => $n, 'source_id' => $v['wp_id'],
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            foreach ($attrs as $n => $a) {
                $db->table('product_attributes')->insert(['product_id' => $pid, 'name' => $a['name'], 'value' => $a['value'], 'sort_order' => $n]);
            }
        }
        CLI::write(count($src['products']) . ' products imported.', 'green');

        // ------------------------------------------------------------ home banners (headlines verbatim from the source slider)
        $banners = [
            ['Low GI Sugar', 'Finally, Natural Sugar that is Good for your Health', '2026/06/Sugar-banner-1.png', '/product/sugarlif-low-gi-diet-sugar', 'Sugarlif Low GI Sugar'],
            ['Low GI Whole Wheat Atta', 'Breads, that Taste Better and are Nutritious', '2026/06/atta-banner.png', '/product/sugarlif-low-gi-wheat-flour-atta', 'Sugarlif Low GI Whole Wheat Atta'],
            ['Gulab Jamun & Rasgollas', 'Enjoy all Delicacies Without Worries', '2026/06/jamun-and-rasgolla-banner-1.png', '/product-category/indian-sweets', 'Sugarlif Gulab Jamun and Rasgollas'],
        ];
        foreach ($banners as $n => [$eyebrow, $heading, $rel, $url, $alt]) {
            $db->table('banners')->insert([
                'eyebrow' => $eyebrow, 'heading' => $heading, 'subheading' => null, 'image' => $this->img($rel, 'banners', $alt), 'image_alt' => $alt,
                'cta_label' => 'Shop Now', 'cta_url' => $url, 'is_active' => 1, 'sort_order' => $n + 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // ------------------------------------------------------------ blog
        $db->table('blog_categories')->insert(['name' => 'Blog', 'slug' => 'blog', 'created_at' => $now, 'updated_at' => $now]);
        $blogCat = $db->insertID();
        foreach ($src['posts'] as $post) {
            $db->table('blog_posts')->insert([
                'category_id' => $blogCat, 'title' => $post['title'], 'slug' => $post['slug'], 'excerpt' => $post['excerpt'], 'content' => $post['content'],
                'image' => null, 'author_name' => 'Sugarlif', 'is_published' => 1, 'published_at' => $post['published_at'],
                'meta_title' => $post['title'] . ' — Sugarlif Journal', 'meta_description' => plain($post['excerpt'], 158), 'source_id' => $post['wp_id'],
                'created_at' => $post['published_at'], 'updated_at' => $post['published_at'],
            ]);
        }
        // The one article image in the source media library belongs to the GI Labs post.
        if ($hero = $this->img('2026/07/GI-LABS-Blog-Image.png', 'blog', 'GI Labs Canada certification')) {
            $db->table('blog_posts')->where('slug', 'why-gi-labs-canada-certification-matters-more-than-marketing-claims')->update(['image' => $hero]);
        }

        // ------------------------------------------------------------ FAQs (first six are the ones the source shows on its home page)
        foreach ($src['faqs'] as $n => $f) {
            $db->table('faqs')->insert(['question' => $f['question'], 'answer' => $f['answer'], 'show_on_home' => $n < 6 && $n !== 3 ? 1 : 0, 'is_active' => 1, 'sort_order' => $n + 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ------------------------------------------------------------ testimonials (verbatim, unedited)
        foreach ($src['testimonials'] as $n => $t) {
            $db->table('testimonials')->insert(['name' => $t['name'], 'meta' => $t['meta'], 'title' => $t['title'], 'content' => $t['text'], 'rating' => $t['rating'], 'is_active' => 1, 'sort_order' => $n + 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ------------------------------------------------------------ team (names and designations exactly as published)
        $team = [
            ['Malik A Mulla', 'Chairman', 'Key People', '2024/07/Malik-A-Mulla.png'],
            ['Ajay K George', 'CEO', 'Key People', '2024/07/Ajay-K-George.png'],
            ['Dr. C. K. Nandgopalan', 'Director - Research & Development', 'Other core members of the team', '2024/07/Dr-C-K-Nandgopalan.png'],
            ['Vikas D. Dangat', null, 'Other core members of the team', '2024/07/Vikas-D-Dangat.png'],
            ['Sureddy V. Reddy', null, 'Other core members of the team', '2024/07/Sureddy-Venkat-Reddy.png'],
        ];
        foreach ($team as $n => [$name, $role, $group, $rel]) {
            $db->table('team_members')->insert(['name' => $name, 'designation' => $role, 'group' => $group, 'image' => $this->img($rel, 'team', $name), 'is_active' => 1, 'sort_order' => $n + 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ------------------------------------------------------------ certifications
        // The source publishes no certificate documents; only bodies it names in its own copy are listed,
        // each with the sentence it appears in. Images/documents can be added in the admin.
        $certs = [
            ['GI Labs', 'Canada', 'Sugarlif has been certified as LOW GI by GI LAB, Canada. (GI LAB is the world’s number 1 Glycemic Index Lab)'],
            ['Whistler Center for Carbohydrate Research', 'USA', 'Sugarlif’s Low GI technology has been evaluated by internationally recognised institutions, including GI Labs, Canada and the Whistler Center for Carbohydrate Research, USA, together with evaluations in Australia and India.'],
            ['BRC Global Standard for Food Safety', 'Manufacturing facility, Maharashtra, India', 'Our manufacturing facility is certified to the BRC Global Standard for Food Safety, reflecting our commitment to producing food that meets some of the world’s most demanding quality standards.'],
            ['US FDA', 'Manufacturing unit', 'Sugarlif Low GI Sugar is produced in a state-of-the-art manufacturing unit with BRC (British Retail Consortium) certification, US FDA certification and many more, guaranteeing top-tier quality, purity, and hygiene.'],
        ];
        foreach ($certs as $n => [$name, $where, $desc]) {
            $db->table('certifications')->insert(['name' => $name, 'issuer_location' => $where, 'description' => $desc, 'is_active' => 1, 'sort_order' => $n + 1, 'created_at' => $now, 'updated_at' => $now]);
        }

        // ------------------------------------------------------------ navigation
        $menus = [
            'primary' => ['Main navigation', [
                ['Home', '/'],
                ['About Us', '/about-naturalife', [
                    ['Our Profile', '/about-naturalife', null, 'The story behind Sugarlif'],
                    ['Team', '/team', null, 'The people leading Natural Life'],
                    ['Certifications', '/certification', null, 'Independent validation'],
                ]],
                ['Shop', '/shop'],
                ['Low GI Education', '/glycemic-index', [
                    ['Glycemic Index', '/glycemic-index', 'Understand Low GI', 'What GI and Glycemic Load mean'],
                    ['Health Benefits of Low GI', '/health-benefits-of-low-gi', 'Understand Low GI', 'Why low GI foods matter'],
                    ['Low GI Diet Vs Other Diet', '/low-gi-diet-vs-other-diet', 'Understand Low GI', 'What the research compared'],
                    ['Low GI Diet Sugar', '/low-gi-diet-sugar', 'Our innovations', 'Cane sugar, made Low GI'],
                    ['Low GI Whole Wheat Atta', '/sugarlif-low-gi-whole-wheat-atta', 'Our innovations', 'Whole wheat with a GI of 36'],
                    ['Low GI Rice', '/sugarlif-low-gi-rice-atta', 'Our innovations', 'Five varieties, GI of 40'],
                    ['Other Applications', '/other-applications', 'Our innovations', 'Where the technology goes next'],
                ]],
                ['Blog', '/blog'],
                ['Contact', '/contact-us'],
            ]],
            'footer_company' => ['Footer — Company', [['Our Profile', '/about-naturalife'], ['Team', '/team'], ['Certifications', '/certification'], ['Blog', '/blog'], ['Contact Us', '/contact-us']]],
            'footer_learn'   => ['Footer — Low GI', [['Glycemic Index', '/glycemic-index'], ['Health Benefits of Low GI', '/health-benefits-of-low-gi'], ['Low GI Diet Vs Other Diet', '/low-gi-diet-vs-other-diet'], ['Other Applications', '/other-applications'], ['FAQ', '/faq']]],
            'footer_shop'    => ['Footer — Shop', [['All products', '/shop'], ['Sugarlif - Low GI Diet Sugar', '/product/sugarlif-low-gi-diet-sugar'], ['Sugarlif - Low GI Wheat Flour (Atta)', '/product/sugarlif-low-gi-wheat-flour-atta'], ['Sugarlif - Honey', '/product/sugarlif-honey'], ['My account', '/my-account']]],
            'legal'          => ['Footer — Legal', [['Refund and Returns Policy', '/refund_returns'], ['Privacy Policy', '/privacy-policy'], ['Terms and Conditions', '/terms-and-conditions']]],
        ];
        foreach ($menus as $location => [$name, $items]) {
            $db->table('menus')->insert(['name' => $name, 'location' => $location]);
            $menuId = $db->insertID();
            foreach ($items as $n => $item) {
                $db->table('menu_items')->insert(['menu_id' => $menuId, 'parent_id' => null, 'label' => $item[0], 'url' => $item[1], 'sort_order' => $n + 1, 'is_active' => 1]);
                $parent = $db->insertID();
                foreach ($item[2] ?? [] as $k => $child) {
                    $db->table('menu_items')->insert(['menu_id' => $menuId, 'parent_id' => $parent, 'label' => $child[0], 'url' => $child[1], 'group_label' => $child[2] ?? null, 'description' => $child[3] ?? null, 'sort_order' => $k + 1, 'is_active' => 1]);
                }
            }
        }

        // ------------------------------------------------------------ SEO defaults for non-CMS routes
        $seo = [
            ['home', 'Home page', 'Sugarlif — Natural Low GI Sugar, Atta, Honey & Jaggery', 'Sugarlif Low GI foods are made by a revolutionary yet traditional method where natural sugar, wheat flour (atta), rice and others are organically re-engineered to be Low GI. Free shipping on orders of ₹500 or more.'],
            ['shop', 'Shop', 'Shop Sugarlif Low GI Foods', 'Buy Sugarlif Low GI Sugar, Whole Wheat Atta, Honey, Jaggery, Gulab Jamun and Rasgollas online. Free shipping on orders of ₹500 or more.'],
            ['blog', 'Blog listing', 'The Sugarlif Journal — Low GI explained', 'Stories and plain-English explainers on sugar, the Glycemic Index and the independent testing behind Sugarlif.'],
        ];
        foreach ($seo as [$key, $label, $title, $desc]) {
            $db->table('seo_meta')->insert(['route_key' => $key, 'label' => $label, 'meta_title' => $title, 'meta_description' => $desc, 'updated_at' => $now]);
        }

        // ------------------------------------------------------------ 301 redirects for original URLs that changed
        $redirects = [
            ['/products', '/shop', 'WooCommerce shop archive'],
            ['/product-category/uncategorized', '/shop', 'Default WooCommerce category'],
            ['/product-tag/suger', '/shop', 'Misspelt product tag'],
            ['/category/blog', '/blog', 'WordPress category archive'],
            ['/home2121', '/', 'Abandoned homepage draft'],
            ['/demo', '/', 'Empty test page'],
        ];
        foreach ($src['posts'] as $post) {
            $redirects[] = ['/' . $post['slug'], '/blog/' . $post['slug'], 'Blog post moved under /blog'];
        }
        foreach ($redirects as [$from, $to, $note]) {
            $db->table('redirects')->insert(['from_path' => $from, 'to_path' => $to, 'status_code' => 301, 'is_active' => 1, 'note' => $note, 'created_at' => $now, 'updated_at' => $now]);
        }

        CLI::write('Blog, FAQs, testimonials, team, certifications, navigation, SEO and redirects imported.', 'green');
    }

    /** Imports one archived source image into the media library and returns its library path. */
    private function img(string $rel, string $folder, ?string $alt = null): ?string
    {
        $m = $this->media->import($this->archive . $rel, $folder, $alt, 'https://natural-life.in/wp-content/uploads/' . $rel);
        if (! $m) {
            CLI::write("  missing source image: {$rel}", 'yellow');

            return null;
        }

        return $m['path'];
    }
}
