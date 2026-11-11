<?php

namespace App\Controllers\Admin;

use App\Controllers\Page;
use App\Libraries\MediaLibrary;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Pages and their content sections (headings, text, images, buttons, lists). */
class Pages extends AdminController
{
    protected string $module = 'content';

    public function index(): string
    {
        return $this->render('admin/pages/index', [
            'title' => 'Pages', 'breadcrumbs' => [['Website content', null], ['Pages', null]],
            'rows'  => db_connect()->table('pages p')->select('p.*, (SELECT COUNT(*) FROM page_sections s WHERE s.page_id = p.id) AS section_count', false)
                ->where('p.slug !=', 'home')->orderBy('p.title')->get()->getResultArray(),
            'templates' => Page::TEMPLATES,
        ]);
    }

    /** Shortcut: the home page's sections. */
    public function home()
    {
        $home = model('PageModel')->where('slug', 'home')->first();
        if (! $home) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->edit((int) $home['id']);
    }

    public function create(): string
    {
        return $this->render('admin/pages/form', [
            'title' => 'Add page', 'breadcrumbs' => [['Website content', null], ['Pages', site_url('admin/pages')], ['Add', null]],
            'page' => null, 'sections' => [], 'templates' => ['default' => Page::TEMPLATES['default'], 'legal' => Page::TEMPLATES['legal']],
        ]);
    }

    public function edit(int $id): string
    {
        $page   = $this->find($id);
        $isHome = $page['slug'] === 'home';

        return $this->render('admin/pages/form', [
            'title'       => $isHome ? 'Home page' : 'Edit page',
            'breadcrumbs' => [['Website content', null], $isHome ? ['Home page', null] : ['Pages', site_url('admin/pages')], [$isHome ? 'Sections' : $page['title'], null]],
            'page'        => $page,
            'sections'    => model('PageSectionModel')->where('page_id', $id)->orderBy('sort_order')->findAll(),
            'templates'   => Page::TEMPLATES,
        ]);
    }

    public function store()
    {
        return $this->save(null);
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    private function save(?int $id)
    {
        $page   = $id ? $this->find($id) : null;
        $isHome = $page && $page['slug'] === 'home';
        $rules  = [
            'title'            => ['label' => 'Title', 'rules' => 'required|max_length[190]'],
            'eyebrow'          => ['label' => 'Small label', 'rules' => 'permit_empty|max_length[120]'],
            'meta_title'       => ['label' => 'SEO title', 'rules' => 'permit_empty|max_length[190]'],
            'meta_description' => ['label' => 'SEO description', 'rules' => 'permit_empty|max_length[320]'],
        ];
        if (! $page) {
            $rules['slug'] = ['label' => 'URL slug', 'rules' => 'permit_empty|max_length[160]'];
        }
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();
        $n    = static fn ($v) => trim((string) $v) === '' ? null : trim((string) $v);
        $data = [
            'title'            => trim($post['title']),
            'eyebrow'          => $n($post['eyebrow'] ?? ''),
            'intro'            => $n($post['intro'] ?? ''),
            'body'             => rich($post['body'] ?? ''),
            'is_published'     => $isHome ? 1 : (empty($post['is_published']) ? 0 : 1),
            'meta_title'       => $n($post['meta_title'] ?? ''),
            'meta_description' => $n($post['meta_description'] ?? ''),
            'og_image'         => $n($post['og_image'] ?? ''),
        ];

        $file = $this->request->getFile('og_image_upload');
        if ($file && $file->isValid()) {
            try {
                $data['og_image'] = (new MediaLibrary())->store($file, 'pages')['path'];
            } catch (\RuntimeException $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $model = model('PageModel');
        if ($page) {
            $model->update($id, $data);
        } else {
            $slug = slugify($n($post['slug'] ?? '') ?: $data['title']);
            // New pages share the root URL space with the shop, blog and admin: keep them from colliding.
            $reserved = ['shop', 'cart', 'checkout', 'wishlist', 'blog', 'admin', 'product', 'product-category', 'my-account', 'order', 'payment', 'newsletter', 'assets', 'uploads', 'home'];
            if (in_array($slug, $reserved, true) || $model->where('slug', $slug)->first()) {
                return redirect()->back()->withInput()->with('error', 'That URL is already in use. Choose a different URL slug.');
            }
            $data['slug']     = $slug;
            $data['template'] = ($post['template'] ?? '') === 'legal' ? 'legal' : 'default';
            $id               = (int) $model->insert($data, true);
        }
        $this->flushCache();

        return redirect()->to(site_url($isHome ? 'admin/home' : 'admin/pages/' . $id . '/edit'))->with('success', 'Page saved.');
    }

    public function delete(int $id)
    {
        $page = $this->find($id);
        // Pages the storefront, navigation and checkout depend on can be unpublished but not deleted.
        if ($page['is_system'] || $page['template'] !== 'default') {
            return redirect()->back()->with('error', 'This page is part of the site structure. You can unpublish it instead of deleting it.');
        }
        model('PageModel')->delete($id);
        $this->flushCache();

        return $this->back('admin/pages', 'Page deleted.');
    }

    // ------------------------------------------------------------------ sections

    public function editSection(int $id): string
    {
        $section = $this->findSection($id);
        $page    = $this->find((int) $section['page_id']);
        $isHome  = $page['slug'] === 'home';

        return $this->render('admin/pages/section', [
            'title'       => $section['admin_label'],
            'breadcrumbs' => [['Website content', null], [$isHome ? 'Home page' : $page['title'], site_url($isHome ? 'admin/home' : 'admin/pages/' . $page['id'] . '/edit')], [$section['admin_label'], null]],
            'section'     => $section,
            'page'        => $page,
            'items'       => section_items($section),
        ]);
    }

    public function updateSection(int $id)
    {
        $section = $this->findSection($id);
        $page    = $this->find((int) $section['page_id']);
        $rules   = [
            'eyebrow'   => 'permit_empty|max_length[120]', 'heading' => 'permit_empty|max_length[255]', 'image_alt' => 'permit_empty|max_length[255]',
            'cta_label' => 'permit_empty|max_length[80]', 'cta_url' => 'permit_empty|max_length[255]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $post = $this->request->getPost();
        $n    = static fn ($v) => trim((string) $v) === '' ? null : trim((string) $v);

        $image = $n($post['image'] ?? '');
        $file  = $this->request->getFile('image_upload');
        if ($file && $file->isValid()) {
            try {
                $image = (new MediaLibrary())->store($file, 'pages')['path'];
            } catch (\RuntimeException $e) {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }

        $data = [
            'eyebrow' => $n($post['eyebrow'] ?? ''), 'heading' => $n($post['heading'] ?? ''), 'body' => rich($post['body'] ?? '') ?: null,
            'image' => $image, 'image_alt' => $n($post['image_alt'] ?? ''), 'cta_label' => $n($post['cta_label'] ?? ''), 'cta_url' => $n($post['cta_url'] ?? ''),
            'is_active' => empty($post['is_active']) ? 0 : 1,
        ];

        // List rows keep the keys the template expects (taken from the existing rows); empty rows are dropped.
        if ($section['items'] !== null || ! empty($post['items'])) {
            $keys  = array_keys(section_items($section)[0] ?? []);
            $items = [];
            foreach ((array) ($post['items'] ?? []) as $row) {
                $clean = [];
                foreach ($keys as $k) {
                    $clean[$k] = trim((string) ($row[$k] ?? ''));
                }
                if (implode('', $clean) !== '') {
                    $items[] = $clean;
                }
            }
            $data['items'] = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        model('PageSectionModel')->update($id, $data);
        $this->flushCache();

        return redirect()->to(site_url($page['slug'] === 'home' ? 'admin/home' : 'admin/pages/' . $page['id'] . '/edit') . '#sections')->with('success', 'Section saved.');
    }

    public function toggleSection(int $id)
    {
        $section = $this->findSection($id);
        model('PageSectionModel')->update($id, ['is_active' => $section['is_active'] ? 0 : 1]);
        $this->flushCache();

        return redirect()->back()->with('success', 'Section ' . ($section['is_active'] ? 'hidden' : 'shown') . '.');
    }

    private function find(int $id): array
    {
        $page = model('PageModel')->find($id);
        if (! $page) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $page;
    }

    private function findSection(int $id): array
    {
        $section = model('PageSectionModel')->find($id);
        if (! $section) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $section;
    }
}
