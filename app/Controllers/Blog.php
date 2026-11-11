<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Blog extends BaseController
{
    public function index(?string $categorySlug = null): string
    {
        $category = null;
        if ($categorySlug) {
            $category = model('BlogCategoryModel')->where('slug', $categorySlug)->first();
            if (! $category) {
                throw PageNotFoundException::forPageNotFound();
            }
        }

        $model = model('BlogPostModel');
        $model->select('blog_posts.*, blog_categories.name AS category_name, blog_categories.slug AS category_slug')
            ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
            ->where('blog_posts.is_published', 1)->where('blog_posts.published_at <=', date('Y-m-d H:i:s'));
        if ($category) {
            $model->where('blog_posts.category_id', $category['id']);
        }
        $posts = $model->orderBy('blog_posts.published_at', 'DESC')->orderBy('blog_posts.id', 'DESC')->paginate(7, 'blog');

        $crumb = [['Home', site_url('/')], ['Journal', $category ? site_url('blog') : null]];
        if ($category) {
            $crumb[] = [$category['name'], null];
        }

        return $this->render('blog/index', [
            'posts'       => $posts,
            'pager'       => $model->pager,
            'category'    => $category,
            'categories'  => model('BlogCategoryModel')->orderBy('name')->findAll(),
            'breadcrumbs' => $crumb,
        ], $this->seo([
            'title'       => $category ? $category['name'] . ' — Journal' : 'The Sugarlif Journal',
            'description' => 'Stories and plain-English explainers on sugar, the Glycemic Index and the science behind Sugarlif Low GI foods.',
            'breadcrumbs' => $crumb,
        ], $category ? null : 'blog'));
    }

    public function show(string $slug): string
    {
        $post = model('BlogPostModel')
            ->select('blog_posts.*, blog_categories.name AS category_name, blog_categories.slug AS category_slug')
            ->join('blog_categories', 'blog_categories.id = blog_posts.category_id', 'left')
            ->where(['blog_posts.slug' => $slug, 'blog_posts.is_published' => 1])->first();
        if (! $post) {
            throw PageNotFoundException::forPageNotFound();
        }

        $url   = site_url('blog/' . $post['slug']);
        $crumb = [['Home', site_url('/')], ['Journal', site_url('blog')], [$post['title'], null]];
        $more  = model('BlogPostModel')->where('is_published', 1)->where('id !=', $post['id'])->orderBy('published_at', 'DESC')->orderBy('id', 'DESC')->findAll(3);
        $words = str_word_count(strip_tags((string) $post['content']));

        return $this->render('blog/show', [
            'post' => $post, 'more' => $more, 'breadcrumbs' => $crumb, 'readMinutes' => max(1, (int) ceil($words / 220)),
        ], $this->seo([
            'title'       => $post['meta_title'] ?: $post['title'],
            'description' => $post['meta_description'] ?: plain($post['excerpt'] ?: $post['content'], 160),
            'canonical'   => $url,
            'og_type'     => 'article',
            'og_image'    => $post['image'] ? media_url($post['image'], 'lg') : null,
            'breadcrumbs' => $crumb,
            'jsonld'      => [[
                '@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post['title'],
                'datePublished' => date('c', strtotime((string) $post['published_at'])), 'dateModified' => date('c', strtotime((string) ($post['updated_at'] ?: $post['published_at']))),
                'author' => ['@type' => 'Organization', 'name' => $post['author_name'] ?: setting('site_name', 'Sugarlif')],
                'publisher' => ['@type' => 'Organization', 'name' => setting('company_name', 'Natural Life Speciality Pvt. Ltd.'), 'logo' => ['@type' => 'ImageObject', 'url' => media_url(setting('logo'), 'original')]],
                'mainEntityOfPage' => $url, 'description' => plain($post['excerpt'] ?: $post['content'], 200),
            ]],
        ]));
    }
}
