<?php

namespace App\Libraries;

use App\Models\MediaModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

/**
 * Stores uploads under public/uploads/media/original/{folder}/ (untouched, full quality)
 * and generates optimised WebP renditions under lg/ md/ sm/ for fast page loads.
 */
class MediaLibrary
{
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    public const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    public const MAX_BYTES    = 8 * 1024 * 1024;
    public const VARIANTS     = ['lg' => 1600, 'md' => 900, 'sm' => 480];
    public const FOLDERS      = ['products', 'banners', 'pages', 'team', 'blog', 'certifications', 'brand', 'general'];

    private string $base;

    public function __construct()
    {
        $this->base = rtrim(FCPATH, '/\\') . '/uploads/media/';
    }

    /** Validates and stores an uploaded file; returns the media row. */
    public function store(UploadedFile $file, string $folder = 'general', ?string $alt = null): array
    {
        if (! $file->isValid() || $file->hasMoved()) {
            throw new RuntimeException($file->getErrorString() ?: 'The upload failed.');
        }
        if ($file->getSize() > self::MAX_BYTES) {
            throw new RuntimeException('Images must be 8 MB or smaller.');
        }
        $ext  = strtolower($file->getClientExtension());
        $mime = $file->getMimeType(); // detected from file contents, not the client header
        if (! in_array($ext, self::ALLOWED_EXT, true) || ! in_array($mime, self::ALLOWED_MIME, true)) {
            throw new RuntimeException('Only JPG, PNG, WebP, GIF and SVG images can be uploaded.');
        }
        if ($ext === 'svg') {
            $svg = (string) file_get_contents($file->getTempName());
            if (preg_match('/<script|on[a-z]+\s*=|javascript:|<foreignObject/i', $svg)) {
                throw new RuntimeException('This SVG contains scripts and cannot be uploaded.');
            }
        } elseif (@getimagesize($file->getTempName()) === false) {
            throw new RuntimeException('The file is not a valid image.');
        }

        $folder = in_array($folder, self::FOLDERS, true) ? $folder : 'general';
        $name   = $this->uniqueName($folder, pathinfo($file->getClientName(), PATHINFO_FILENAME), $ext);
        $dir    = $this->base . 'original/' . $folder;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $file->move($dir, $name);

        return $this->register($folder . '/' . $name, $alt);
    }

    /** Copies a local file into the library (used by the content migration seeder). */
    public function import(string $sourceFile, string $folder, ?string $alt = null, ?string $sourceUrl = null): ?array
    {
        if (! is_file($sourceFile) || filesize($sourceFile) === 0) {
            return null;
        }
        $ext  = strtolower(pathinfo($sourceFile, PATHINFO_EXTENSION));
        $name = slugify(pathinfo($sourceFile, PATHINFO_FILENAME)) . '.' . $ext;
        $path = $folder . '/' . $name;
        $dest = $this->base . 'original/' . $path;

        $existing = model(MediaModel::class)->where('path', $path)->first();
        if ($existing && is_file($dest)) {
            return $existing;
        }
        if (! is_dir(dirname($dest))) {
            mkdir(dirname($dest), 0755, true);
        }
        copy($sourceFile, $dest);

        return $this->register($path, $alt, $sourceUrl);
    }

    /** Creates the WebP renditions and the database row for a stored original. */
    public function register(string $path, ?string $alt = null, ?string $sourceUrl = null): array
    {
        $file = $this->base . 'original/' . $path;
        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $size = ($ext === 'svg') ? null : @getimagesize($file);
        if ($size) {
            $this->makeVariants($path, $size[0], $size[1]);
        }

        $model = model(MediaModel::class);
        $row   = [
            'folder'     => explode('/', $path)[0],
            'file_name'  => basename($path),
            'path'       => $path,
            'mime'       => $size['mime'] ?? ($ext === 'svg' ? 'image/svg+xml' : 'application/octet-stream'),
            'size_bytes' => filesize($file),
            'width'      => $size[0] ?? null,
            'height'     => $size[1] ?? null,
            'alt_text'   => $alt,
            'source_url' => $sourceUrl,
        ];
        $existing = $model->where('path', $path)->first();
        if ($existing) {
            $model->update($existing['id'], $row);

            return $model->find($existing['id']);
        }

        return $model->find($model->insert($row, true));
    }

    private function makeVariants(string $path, int $width, int $height): void
    {
        if (! function_exists('imagewebp')) {
            return;
        }
        // Guard against exhausting memory on very large sources (4 bytes per pixel + overhead).
        if ($width * $height > 40_000_000) {
            return;
        }
        $source = $this->base . 'original/' . $path;

        foreach (self::VARIANTS as $key => $max) {
            $target = $this->base . $key . '/' . preg_replace('/\.[a-z0-9]+$/i', '.webp', $path);
            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0755, true);
            }

            try {
                $image = service('image', 'gd')->withFile($source);
                if ($width > $max) {
                    $image->resize($max, (int) round($height * $max / $width), true, 'width');
                }
                $image->convert(IMAGETYPE_WEBP)->save($target, 84);
            } catch (\Throwable $e) {
                log_message('warning', 'Media variant failed for {path}: {msg}', ['path' => $path, 'msg' => $e->getMessage()]);
            }
        }
    }

    /** Removes the original, its renditions and the row. */
    public function delete(array $media): void
    {
        @unlink($this->base . 'original/' . $media['path']);
        foreach (array_keys(self::VARIANTS) as $key) {
            @unlink($this->base . $key . '/' . preg_replace('/\.[a-z0-9]+$/i', '.webp', $media['path']));
        }
        model(MediaModel::class)->delete($media['id']);
    }

    /** Lists every place a media path is referenced, so in-use files are not deleted by accident. */
    public function usages(string $path): array
    {
        $db   = db_connect();
        $used = [];
        $map  = [
            'product_images' => ['path', 'Product gallery'], 'product_categories' => ['image', 'Category'],
            'banners' => ['image', 'Home banner'], 'pages' => ['hero_image', 'Page'], 'page_sections' => ['image', 'Page section'],
            'blog_posts' => ['image', 'Blog post'], 'team_members' => ['image', 'Team member'],
            'testimonials' => ['image', 'Testimonial'], 'certifications' => ['image', 'Certification'],
        ];
        foreach ($map as $table => [$col, $label]) {
            $n = $db->table($table)->where($col, $path)->countAllResults();
            if ($n) {
                $used[] = "$label ($n)";
            }
        }
        if ($db->table('page_sections')->like('items', str_replace('/', '\\/', $path))->orLike('items', $path)->countAllResults()) {
            $used[] = 'Page section list';
        }
        if ($db->table('settings')->where('value', $path)->countAllResults()) {
            $used[] = 'Site settings';
        }

        return $used;
    }

    private function uniqueName(string $folder, string $base, string $ext): string
    {
        $base = slugify($base);
        $name = $base . '.' . $ext;
        $i    = 2;
        while (is_file($this->base . 'original/' . $folder . '/' . $name)) {
            $name = $base . '-' . $i++ . '.' . $ext;
        }

        return $name;
    }
}
