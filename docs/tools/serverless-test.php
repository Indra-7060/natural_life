<?php
/**
 * Checks the "read-only host" mode (the mode used on Vercel) on this machine.
 *
 *   php docs/tools/serverless-test.php
 *
 * Starts a second copy of the site on port 8092 with APP_SERVERLESS=1, sends every request
 * through api/index.php the way vercel.json does, and verifies that sessions, cache and
 * uploaded images are kept in the database and that nothing is written into the project.
 * It cannot check Vercel itself. MySQL must be running. Development tool only: it signs in
 * as the first admin with the password from .env and removes everything it creates.
 */

$root = str_replace('\\', '/', dirname(__DIR__, 2)) . '/';
$base = 'http://127.0.0.1:8092';
$pass = 0;
$fail = 0;

function check(string $label, bool $ok, string $detail = ''): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail++;
    echo ($ok ? '  PASS  ' : '  FAIL  ') . $label . ($detail !== '' ? '  [' . $detail . ']' : '') . "\n";
}

/** Newest modification time and file count under a folder: any write changes one of them. */
function footprint(string $dir): string
{
    $n = 0;
    $latest = 0;
    if (is_dir($dir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $f) {
            $n++;
            $latest = max($latest, $f->getMTime());
        }
    }

    return $n . ':' . $latest;
}

$jar = tempnam(sys_get_temp_dir(), 'jar');
function http(string $method, string $url, array $post = [], array $headers = [], bool $multipart = false): array
{
    global $jar;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR => $jar, CURLOPT_COOKIEFILE => $jar, CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => array_merge(['Accept: text/html'], $headers),
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart ? $post : http_build_query($post));
    }
    $raw  = (string) curl_exec($ch);
    $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $out  = ['code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 'head' => substr($raw, 0, $size), 'body' => substr($raw, $size)];
    curl_close($ch);

    return $out;
}
function token(string $html): string
{
    return preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m) ? $m[1] : '';
}

// ---- settings from .env (values are used, never printed) --------------------------------
$env = [];
foreach (file($root . '.env', FILE_IGNORE_NEW_LINES) as $line) {
    if (preg_match('/^\s*([A-Za-z0-9_.]+)\s*=\s*(.*?)\s*(?:#.*)?$/', $line, $m)) {
        $env[$m[1]] = trim($m[2], "'\"");
    }
}
$db = new mysqli($env['database.default.hostname'] ?? 'localhost', $env['database.default.username'] ?? 'root', $env['database.default.password'] ?? '', $env['database.default.database'] ?? 'sugarlif_db');
$count = fn (string $sql): int => (int) $db->query($sql)->fetch_row()[0];

// ---- start the site in read-only-host mode ----------------------------------------------
$db->query('DELETE FROM ci_sessions');
$db->query('DELETE FROM cache_store');
$before = ['writable' => footprint($root . 'writable'), 'uploads' => footprint($root . 'public/uploads')];

$vars = getenv();
$vars['APP_SERVERLESS'] = '1';
$vars['app_baseURL']    = $base . '/';
$server = proc_open(
    [PHP_BINARY, '-S', '127.0.0.1:8092', '-t', $root . 'public', $root . 'docs/tools/vercel-router.php'],
    [['pipe', 'r'], ['file', sys_get_temp_dir() . '/serverless-test.log', 'w'], ['file', sys_get_temp_dir() . '/serverless-test.log', 'a']],
    $pipes, $root, $vars,
);
register_shutdown_function(static function () use (&$server): void {
    if (is_resource($server)) {
        $pid = proc_get_status($server)['pid'];
        stripos(PHP_OS, 'WIN') === 0 ? exec("taskkill /F /T /PID $pid 2>NUL") : proc_terminate($server);
    }
});
for ($i = 0; $i < 40; $i++) {
    if (@fsockopen('127.0.0.1', 8092, $e1, $e2, 0.5)) {
        break;
    }
    usleep(250000);
}

echo "Pages through api/index.php\n";
$home = http('GET', $base . '/');
check('home page loads', $home['code'] === 200 && strlen($home['body']) > 20000, $home['code'] . ', ' . strlen($home['body']) . ' bytes');
check('links use the address given in app_baseURL', str_contains($home['body'], $base . '/shop') && ! str_contains($home['body'], 'localhost:8080'));
foreach (['/shop', '/blog', '/contact-us', '/cart', '/sitemap.xml', '/admin/login'] as $u) {
    $r = http('GET', $base . $u);
    check("GET $u", $r['code'] === 200, (string) $r['code']);
}
$r = http('GET', $base . '/assets/css/site-v2.css');
check('static file served without PHP', $r['code'] === 200 && ! str_contains(strtolower($r['head']), 'set-cookie'));
$r = http('GET', $base . '/no-such-page-here');
check('unknown address gives the styled 404 page', $r['code'] === 404 && str_contains($r['body'], 'no longer exists'));
$r = http('GET', $base . '/index.php');
check('/index.php is run, not shown as source', ! str_contains($r['body'], 'bootWeb'));

echo "Sessions in the database\n";
$product = $db->query("SELECT p.id, p.name FROM products p LEFT JOIN product_variations v ON v.product_id = p.id WHERE v.id IS NULL AND p.stock_status = 'instock' AND p.is_visible = 1 AND p.deleted_at IS NULL LIMIT 1");
$product = $product ? $product->fetch_assoc() : null;
if (! $product) {
    $product = $db->query("SELECT p.id, p.name FROM products p LEFT JOIN product_variations v ON v.product_id = p.id WHERE v.id IS NULL LIMIT 1")->fetch_assoc();
}
$r = http('POST', $base . '/cart/add', ['csrf_token' => token($home['body']), 'product_id' => $product['id'], 'quantity' => 1], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json']);
check('add to cart accepted (CSRF token from the session)', $r['code'] === 200, $r['code'] . ' ' . substr($r['body'], 0, 80));
$r = http('GET', $base . '/cart');
check('cart still holds the product on the next request', str_contains(html_entity_decode($r['body']), $product['name']));
check('session rows exist in ci_sessions', $count('SELECT COUNT(*) FROM ci_sessions') > 0, $count('SELECT COUNT(*) FROM ci_sessions') . ' rows');

echo "Cache in the database\n";
check('settings and menus cached in cache_store', $count("SELECT COUNT(*) FROM cache_store WHERE cache_key LIKE '%site_settings%' OR cache_key LIKE '%menu_%'") >= 2, $count('SELECT COUNT(*) FROM cache_store') . ' rows');

echo "Sign-in limit shared through the database\n";
$blocked = false;
for ($i = 0; $i < 8 && ! $blocked; $i++) {
    $page = http('GET', $base . '/admin/login');
    $r    = http('POST', $base . '/admin/login', ['csrf_token' => token($page['body']), 'email' => 'nobody@example.com', 'password' => 'wrong-' . $i]);
    $next = in_array($r['code'], [302, 303], true) ? http('GET', $base . '/admin/login') : $r;
    $blocked = (bool) preg_match('/too many|try again/i', $next['body']);
}
check('repeated wrong admin passwords get blocked', $blocked, 'after ' . $i . ' attempts');
$db->query("DELETE FROM cache_store WHERE cache_key LIKE '%throttler%' OR cache_key LIKE '%admin_login%'");

echo "Uploads in the database\n";
$admin = $db->query('SELECT id, must_change_password FROM admin_users ORDER BY id LIMIT 1')->fetch_assoc();
$db->query('UPDATE admin_users SET must_change_password = 0 WHERE id = ' . (int) $admin['id']);
$mediaId = null;
try {
    $page = http('GET', $base . '/admin/login');
    $r    = http('POST', $base . '/admin/login', ['csrf_token' => token($page['body']), 'email' => $env['admin.defaultEmail'] ?? '', 'password' => $env['admin.defaultPassword'] ?? '']);
    $page = http('GET', $base . '/admin/media');
    check('admin sign-in works with a database session', $page['code'] === 200 && str_contains($page['body'], 'csrf_token'), (string) $page['code']);

    // a 1400 x 900 test photo, large enough to need more than one stored piece
    $png = sys_get_temp_dir() . '/serverless-test-image.png';
    $img = imagecreatetruecolor(1400, 900);
    // random pixels do not compress, so the file is a few megabytes
    for ($y = 0; $y < 900; $y++) {
        for ($x = 0; $x < 1400; $x += 2) {
            imagesetpixel($img, $x, $y, random_int(0, 0xFFFFFF));
        }
    }
    imagepng($img, $png);
    $bytes = filesize($png);

    $r = http('POST', $base . '/admin/media', ['csrf_token' => token($page['body']), 'folder' => 'general', 'files[]' => new CURLFile($png, 'image/png', 'serverless-test-image.png')], ['X-Requested-With: XMLHttpRequest', 'Accept: application/json'], true);
    $json = json_decode($r['body'], true);
    check('image upload accepted', $r['code'] === 200 && ! empty($json['ok']), $r['code'] . ' ' . substr($r['body'], 0, 120));
    $path = $json['items'][0]['path'] ?? 'general/serverless-test-image.png';
    $row  = $db->query("SELECT id FROM media WHERE path = '" . $db->real_escape_string($path) . "'")->fetch_assoc();
    $mediaId = $row['id'] ?? null;

    $pieces = $count("SELECT COUNT(*) FROM media_files WHERE path = 'original/" . $db->real_escape_string($path) . "'");
    check('original stored in media_files', $pieces > 0, $pieces . ' pieces for ' . round($bytes / 1024) . ' KB');
    $r = http('GET', $base . '/uploads/media/original/' . $path);
    check('original served back identical', $r['code'] === 200 && $r['body'] === file_get_contents($png) && str_contains(strtolower($r['head']), 'image/png'), $r['code'] . ', ' . strlen($r['body']) . ' bytes');
    $webp = preg_replace('/\.png$/', '.webp', $path);
    $r    = http('GET', $base . '/uploads/media/md/' . $webp);
    check('optimised rendition stored and served (needs the gd extension, which Vercel lacks)', $r['code'] === 200 && str_contains(strtolower($r['head']), 'image/webp'), (string) $r['code']);
    $thumb = (string) ($json['items'][0]['thumb'] ?? '');
    check('media_url() points at the stored rendition', str_contains($thumb, '/uploads/media/sm/' . $webp), $thumb);
    $r = http('GET', $base . '/uploads/media/original/../../../.env');
    check('path tricks are refused', $r['code'] === 404 || $r['code'] === 400, (string) $r['code']);

    if ($mediaId) {
        $page = http('GET', $base . '/admin/media');
        $r    = http('POST', $base . '/admin/media/' . $mediaId . '/delete', ['csrf_token' => token($page['body'])]);
        check('deleting the image removes its stored pieces', $count("SELECT COUNT(*) FROM media_files WHERE path LIKE '%" . $db->real_escape_string(pathinfo($path, PATHINFO_FILENAME)) . "%'") === 0, (string) $r['code']);
        $r = http('GET', $base . '/uploads/media/original/' . $path);
        check('deleted image is gone', $r['code'] === 404, (string) $r['code']);
    }
} finally {
    $db->query('UPDATE admin_users SET must_change_password = ' . (int) $admin['must_change_password'] . ' WHERE id = ' . (int) $admin['id']);
    $db->query("DELETE FROM media_files WHERE path LIKE '%serverless-test-image%'");
    $db->query("DELETE FROM media WHERE path LIKE '%serverless-test-image%'");
    @unlink($png ?? '');
}

echo "Nothing written into the project\n";
check('writable/ untouched', footprint($root . 'writable') === $before['writable']);
check('public/uploads/ untouched', footprint($root . 'public/uploads') === $before['uploads']);

// ---- tidy up ---------------------------------------------------------------------------
$db->query('DELETE FROM ci_sessions');
$db->query('DELETE FROM cache_store');
$db->query("DELETE FROM carts WHERE created_at >= '" . date('Y-m-d H:i:s', time() - 600) . "' AND customer_id IS NULL");
@unlink($jar);

echo "\n==============================\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
