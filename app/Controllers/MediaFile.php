<?php

namespace App\Controllers;

use App\Libraries\MediaStore;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serves media that is stored in the database (uploads made on a read-only host).
 * Files that exist in public/uploads are delivered by the web server and never reach this.
 */
class MediaFile extends BaseController
{
    public function show(string ...$parts): ResponseInterface
    {
        $path = implode('/', $parts);
        if (! preg_match('#^(original|lg|md|sm)/[a-z0-9_-]+/[A-Za-z0-9._-]+\.(jpe?g|png|webp|gif|svg)$#', $path) || str_contains($path, '..')) {
            throw PageNotFoundException::forPageNotFound();
        }
        $file = (new MediaStore())->get($path);
        if ($file === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('Content-Type', $file['mime'])
            ->setHeader('Cache-Control', 'public, max-age=604800')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            // an SVG opened on its own must not be able to run anything
            ->setHeader('Content-Security-Policy', "default-src 'none'; style-src 'unsafe-inline'; sandbox")
            ->setBody($file['data']);
    }
}
