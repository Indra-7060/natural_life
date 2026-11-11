<?php
/**
 * Imitates vercel.json for PHP's built-in server: a file that exists in public/ is served
 * as it is, everything else goes to api/index.php. Used only by serverless-test.php.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = dirname(__DIR__, 2) . '/public' . $path;
if ($path !== '/' && ! preg_match('#^/(index\.php|\.htaccess)#', $path) && is_file($file)) {
    return false;
}
require dirname(__DIR__, 2) . '/api/index.php';
