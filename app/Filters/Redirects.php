<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serves the 301 redirect map (old WordPress URLs to new URLs) managed in Admin > Redirects.
 * Only GET requests to storefront paths are checked; the map is cached for 10 minutes.
 */
class Redirects implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (is_cli() || strtolower($request->getMethod()) !== 'get') {
            return null;
        }
        $path = '/' . trim($request->getUri()->getPath(), '/');
        if ($path === '/' || str_starts_with($path, '/admin') || str_starts_with($path, '/assets') || str_starts_with($path, '/uploads')) {
            return null;
        }

        try {
            $map = cache('redirect_map');
            if ($map === null) {
                $map = [];
                foreach (db_connect()->table('redirects')->where('is_active', 1)->get()->getResultArray() as $r) {
                    $map['/' . trim($r['from_path'], '/')] = [$r['id'], $r['to_path'], (int) $r['status_code']];
                }
                cache()->save('redirect_map', $map, 600);
            }
        } catch (\Throwable $e) {
            return null; // database not installed yet
        }

        if (isset($map[$path])) {
            [$id, $to, $code] = $map[$path];
            db_connect()->table('redirects')->where('id', $id)->set('hits', 'hits + 1', false)->update();
            $target = preg_match('#^https?://#', $to) ? $to : site_url(ltrim($to, '/'));

            return redirect()->to($target, $code);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
