<?php
/**
 * End-to-end functional test against a running local server.
 *   php docs/tools/functional-test.php [base-url]
 *
 * Exercises the storefront (catalogue, variations, cart, coupon, checkout, payment callback,
 * accounts, wishlist, forms) and the admin panel (auth, CRUD, product editor, uploads,
 * order workflow, settings) with real HTTP requests, then removes the data it created.
 * It temporarily adds dummy CCAvenue test keys to .env to exercise the callback verification
 * and restores the file afterwards. Local development use only.
 */
$base = rtrim($argv[1] ?? 'http://localhost:8080', '/');
$root = dirname(__DIR__, 2);
$env  = file_get_contents($root . '/.env');
$get  = static fn (string $k) => preg_match('/^' . preg_quote($k, '/') . "[ \t]*=[ \t]*'?([^'\r\n]*)'?/m", $env, $m) ? trim($m[1]) : '';
$db   = new mysqli($get('database.default.hostname') ?: 'localhost', $get('database.default.username'), $get('database.default.password'), $get('database.default.database'));
$db->set_charset('utf8mb4');

$pass = $fail = 0;
$failures = [];
function check(string $name, bool $ok, string $detail = ''): void
{
    global $pass, $fail, $failures;
    if ($ok) { $pass++; echo "  PASS  $name\n"; } else { $fail++; $failures[] = $name; echo "  FAIL  $name" . ($detail ? "  [$detail]" : '') . "\n"; }
}
function section(string $t): void { echo "\n== $t\n"; }

/** Minimal browser: cookie jar + CSRF token tracking. */
class Client
{
    public string $body = ''; public int $code = 0; public string $location = ''; public string $csrf = '';
    private string $jar;
    public function __construct(private string $base) { $this->jar = tempnam(sys_get_temp_dir(), 'jar'); }
    public function get(string $path, bool $ajax = false): self { return $this->req('GET', $path, null, $ajax); }
    public function post(string $path, array $data = [], bool $ajax = false, array $files = []): self
    {
        $data['csrf_token'] = $this->csrf;
        foreach ($files as $k => $f) { $data[$k] = new CURLFile($f[0], $f[1], basename($f[0])); }
        return $this->req('POST', $path, $files ? $data : http_build_query($data), $ajax);
    }
    public function raw(string $path, array $data): self { return $this->req('POST', $path, http_build_query($data), false); }
    private function req(string $method, string $path, $body, bool $ajax): self
    {
        $ch = curl_init($this->base . '/' . ltrim($path, '/'));
        $headers = $ajax ? ['X-Requested-With: XMLHttpRequest', 'Accept: application/json'] : [];
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_COOKIEJAR => $this->jar, CURLOPT_COOKIEFILE => $this->jar,
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_HTTPHEADER => $headers, CURLOPT_TIMEOUT => 60]);
        if ($method === 'POST') { curl_setopt($ch, CURLOPT_POST, true); curl_setopt($ch, CURLOPT_POSTFIELDS, $body); }
        $res = curl_exec($ch); $size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $this->code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
        $head = substr((string) $res, 0, $size); $this->body = substr((string) $res, $size);
        $this->location = preg_match('/^Location:\s*(.+)$/mi', $head, $m) ? trim($m[1]) : '';
        if (preg_match('/name="csrf_token" (?:value|content)="([^"]+)"/', $this->body, $m)) { $this->csrf = $m[1]; }
        $j = json_decode($this->body, true); if (is_array($j) && ! empty($j['csrf'])) { $this->csrf = $j['csrf']; }
        return $this;
    }
    public function follow(): self { return $this->get(str_replace($this->base, '', $this->location)); }
    public function json(): array { return json_decode($this->body, true) ?: []; }
    public function has(string $s): bool { return str_contains($this->body, $s); }
}
$one = static fn (string $sql) => ($r = $db->query($sql)) ? ($r->fetch_row()[0] ?? null) : null;
$tag = 'ft' . substr(md5((string) microtime(true)), 0, 6);
$email = "$tag@example.test";

// ============================================================ STOREFRONT
section('Catalogue');
$c = new Client($base);
$c->get('/');
check('Home page loads with hero and featured products', $c->code === 200 && $c->has('data-hero') && $c->has('Low GI Sugar'));
check('Home page has Organization structured data', $c->has('"@type":"Organization"'));
$c->get('/shop');
check('Shop lists all 7 products', $c->code === 200 && substr_count($c->body, 'class="pcard ') === 7, 'count=' . substr_count($c->body, 'class="pcard '));
check('Sale badge shown for the sachet pack', (bool) preg_match('/Sale −13%/', $c->body));
check('Out-of-stock badge shown for Rasgollas', $c->has('badge-tag--out'));
$c->get('/shop?q=honey');
check('Search ranks the name match (Honey) first', (bool) preg_match('/class="pcard .*?pcard__name"><a[^>]*>Honey</s', $c->body) && substr_count($c->body, 'class="pcard ') <= 2);
$c->get('/shop?min=300&max=400');
check('Price filter 300-400 returns priced-in-range products', substr_count($c->body, 'class="pcard ') === 4 && ! preg_match('/pcard__name"><a[^>]*>(Honey|Low GI Jaggery 500gm|Low GI Wheat Flour \(Atta\))</', $c->body));
$c->get('/shop?stock=1');
check('In-stock filter hides Rasgollas', ! $c->has('Rasgollas 1 Kg'));
$c->get('/shop?sort=price-desc');
check('Sort by price (high to low) puts Honey first', (bool) preg_match('/class="pcard .*?pcard__name"><a[^>]*>Honey</s', $c->body));
$c->get('/product-category/sugar');
check('Category page shows 2 sugar products', substr_count($c->body, 'class="pcard ') === 2);
$c->get('/shop/suggest?q=jag', true);
check('Live search suggestions return Jaggery', ($c->json()['items'][0]['name'] ?? '') === 'Low GI Jaggery 500gm');

section('Product detail and variations');
$c->get('/product/sugarlif-low-gi-diet-sugar');
check('Product page loads with Product structured data', $c->code === 200 && $c->has('"@type":"Product"'));
check('All 7 pack sizes are offered', substr_count($c->body, 'data-variation=') === 7);
check('Pack prices match the source (1kg 250, 5kg 1200, 15kg 3250)', $c->has('data-label="1kg" data-price="250"') && $c->has('data-label="5kg" data-price="1200"') && $c->has('data-label="15kg" data-price="3250"'));
check('Nutritional facts and ingredients are shown', $c->has('19.6kcal') && $c->has('Fenugreek'));
preg_match('/data-variation="(\d+)" data-label="5kg"/', $c->body, $m); $var5 = (int) ($m[1] ?? 0);
preg_match('/name="product_id" value="(\d+)"/', $c->body, $m); $sugarId = (int) ($m[1] ?? 0);
$c->get('/shop/quick-view/sugarlif-low-gi-wheat-flour-atta');
check('Quick view returns the atta buy box with 2 packs', $c->code === 200 && substr_count($c->body, 'data-variation=') === 2);

section('Cart');
$c->post('/cart/add', ['product_id' => $sugarId, 'quantity' => 1], true);
check('Variable product cannot be added without a pack size', $c->code === 422 && str_contains($c->json()['message'] ?? '', 'pack size'));
$c->post('/cart/add', ['product_id' => $sugarId, 'variation_id' => $var5, 'quantity' => 1], true);
$j = $c->json();
check('Add 5kg sugar: cart total uses the 5kg price (1200) with free shipping', ($j['ok'] ?? false) && (float) $j['totals']['subtotal'] === 1200.0 && (float) $j['totals']['shipping'] === 0.0);
$rasId = (int) $one("select id from products where slug like 'sugarlif-rasgollas%'");
$c->post('/cart/add', ['product_id' => $rasId, 'quantity' => 1], true);
check('Out-of-stock product is refused', $c->code === 422);
$c->post('/cart/add', ['product_id' => $sugarId, 'variation_id' => 999999, 'quantity' => 1], true);
check('Unknown variation id is refused', $c->code === 422);
$lineId = (int) $one('select id from cart_items order by id desc limit 1');
$c->post('/cart/remove', ['item_id' => $lineId], true);
check('Remove line empties the cart', ($c->json()['count'] ?? -1) === 0);
$jagId = (int) $one("select id from products where slug='sugarlif-low-gi-jaggery'");
$c->post('/cart/add', ['product_id' => $jagId, 'quantity' => 1], true);
$j = $c->json();
check('Jaggery 130: shipping is 40, total 170', (float) $j['totals']['shipping'] === 40.0 && (float) $j['totals']['total'] === 170.0);
check('Free-shipping meter shows 370 remaining', (float) $j['totals']['free_remaining'] === 370.0);
$lineId = (int) $one('select id from cart_items order by id desc limit 1');
$c->post('/cart/update', ['item_id' => $lineId, 'quantity' => 4], true);
$j = $c->json();
check('Quantity 4 (520): shipping becomes free', (float) $j['totals']['subtotal'] === 520.0 && (float) $j['totals']['shipping'] === 0.0);
$c->post('/cart/update', ['item_id' => $lineId, 'quantity' => 500], true);
check('Quantity is capped at 20', ($c->json()['count'] ?? 0) === 20);
$c->post('/cart/update', ['item_id' => $lineId, 'quantity' => 3], true);
$db->query("insert into coupons (code,type,value,min_subtotal,is_active,created_at,updated_at) values ('" . strtoupper($tag) . "','percent',10,300,1,now(),now())");
$c->post('/cart/coupon', ['code' => 'NOPE'], true);
check('Invalid coupon is rejected', $c->code === 422);
$c->post('/cart/coupon', ['code' => $tag], true);
$j = $c->json();
check('10% coupon on 390: discount 39, shipping 40, total 391', (float) $j['totals']['discount'] === 39.0 && (float) $j['totals']['total'] === 391.0, json_encode($j['totals'] ?? []));
$c->get('/cart');
check('Cart page renders lines and summary', $c->code === 200 && $c->has('Order summary') && $c->has('Low GI Jaggery 500gm'));
$c->post('/cart/add', ['product_id' => $jagId, 'quantity' => 1]);
check('Cart POST without a CSRF token is blocked', true === (function () use ($base, $jagId) { $x = new Client($base); $x->raw('/cart/add', ['product_id' => $jagId]); return $x->code !== 200 || ! str_contains($x->body, '"ok":true'); })());

section('Checkout (guest)');
$c->get('/checkout');
check('Checkout page loads', $c->code === 200 && $c->has('Shipping address'));
$c->post('/checkout', ['email' => 'bad', 'phone' => '12', 'shipping_name' => '', 'shipping_postcode' => 'abc']);
$c->follow();
check('Checkout validation rejects bad input', $c->has('Please check the following') || $c->has('field-error'));
$order = ['email' => $email, 'phone' => '9876543210', 'shipping_name' => 'Test Buyer', 'shipping_line1' => '12 Test Street', 'shipping_line2' => '', 'shipping_city' => 'Pune',
    'shipping_state' => 'Maharashtra', 'shipping_postcode' => '411040', 'billing_same' => 1, 'accept_terms' => 1, 'customer_note' => 'functional test'];
$before = (int) $one('select count(*) from orders');
$c->post('/checkout', $order);
$placed = (int) $one('select count(*) from orders') === $before + 1;
check('Order is created', $placed && str_contains($c->location, '/checkout/payment/'), $c->location);
$o = $db->query('select * from orders order by id desc limit 1')->fetch_assoc();
check('Order totals recalculated server-side (subtotal 520, discount 52, total 468)', (float) $o['subtotal'] === 520.0 && (float) $o['discount_total'] === 52.0 && (float) $o['grand_total'] === 468.0, "{$o['subtotal']}/{$o['discount_total']}/{$o['shipping_total']}/{$o['grand_total']}");
check('New order is pending and unpaid', $o['status'] === 'pending' && $o['payment_status'] === 'unpaid');
check('Cart is emptied after ordering', (int) $one("select count(*) from cart_items ci join carts c on c.id=ci.cart_id where c.id=(select max(id) from carts)") === 0);
$c->follow();
check('Payment page explains gateway is not configured (no auto-paid)', $c->has('pending payment') && $c->has('Nothing has been charged'));
$c->get('/order/' . $o['order_number']);
check('Order page is not accessible without the access key', $c->code === 404);
$c->get('/order/' . $o['order_number'] . '?key=' . $o['access_token']);
check('Order page opens with the access key', $c->code === 200 && $c->has($o['order_number']));
check('Order confirmation e-mail was written (SMTP not configured)', count(glob($root . '/writable/emails/*order-confirmation*')) > 0);

section('CCAvenue callback verification');
$testKey = 'TESTWORKINGKEY0123456789ABCDEF00';
file_put_contents($root . '/.env', $env . "\nccavenue.merchantId = 999999\nccavenue.accessCode = TESTACCESS\nccavenue.workingKey = {$testKey}\n");
$enc = static function (array $p) use ($testKey) { return bin2hex(openssl_encrypt(http_build_query($p), 'AES-128-CBC', hex2bin(md5($testKey)), OPENSSL_RAW_DATA, pack('C*', 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15))); };
try {
    $c->get('/checkout/payment/' . $o['order_number'] . '?key=' . $o['access_token']);
    check('With keys set, payment page posts an encrypted request to CCAvenue test endpoint', $c->has('test.ccavenue.com') && $c->has('name="encRequest"') && ! $c->has($testKey));
    $g = new Client($base);
    $g->raw('/payment/ccavenue/response', ['encResp' => 'deadbeef']);
    check('Garbage callback is rejected', (string) $one("select payment_status from orders where id={$o['id']}") === 'unpaid');
    $g->raw('/payment/ccavenue/response', ['encResp' => $enc(['order_id' => $o['order_number'], 'order_status' => 'Success', 'amount' => '1.00', 'currency' => 'INR', 'tracking_id' => 'T1'])]);
    check('Callback with a tampered amount does NOT mark the order paid', (string) $one("select payment_status from orders where id={$o['id']}") !== 'paid');
    $g->raw('/payment/ccavenue/response', ['encResp' => $enc(['order_id' => $o['order_number'], 'order_status' => 'Failure', 'amount' => $o['grand_total'], 'currency' => 'INR', 'tracking_id' => 'T2'])]);
    check('Failed payment sets payment status to failed', (string) $one("select payment_status from orders where id={$o['id']}") === 'failed');
    $g->raw('/payment/ccavenue/response', ['encResp' => $enc(['order_id' => $o['order_number'], 'order_status' => 'Success', 'amount' => $o['grand_total'], 'currency' => 'INR', 'tracking_id' => 'T3', 'bank_ref_no' => 'B3', 'payment_mode' => 'UPI'])]);
    $after = $db->query("select status,payment_status,paid_at from orders where id={$o['id']}")->fetch_assoc();
    check('Valid callback (matching order + amount) marks the order paid and processing', $after['payment_status'] === 'paid' && $after['status'] === 'processing' && $after['paid_at'] !== null);
    check('Each gateway response is logged with its verified flag', (int) $one("select count(*) from payments where order_id={$o['id']}") === 3 && (int) $one("select count(*) from payments where order_id={$o['id']} and verified=1") === 1);
} finally {
    file_put_contents($root . '/.env', $env);
}

section('Customer account');
$u = new Client($base);
$u->get('/my-account');
check('Account area redirects guests to sign-in', $u->code >= 300 && str_contains($u->location, 'my-account/login'));
$u->get('/my-account/login');
$u->post('/my-account/register', ['first_name' => 'Test', 'last_name' => 'Buyer', 'reg_email' => $email, 'reg_password' => 'short', 'password_confirm' => 'short', 'accept_terms' => 1]);
check('Registration rejects a short password', (int) $one("select count(*) from customers where email='$email'") === 0);
$u->get('/my-account/login');
$u->post('/my-account/register', ['first_name' => 'Test', 'last_name' => 'Buyer', 'reg_email' => $email, 'reg_password' => 'Correct-Horse-9', 'password_confirm' => 'Correct-Horse-9', 'accept_terms' => 1]);
$hash = (string) $one("select password_hash from customers where email='$email'");
check('Registration creates the customer with a hashed password', $hash !== '' && str_starts_with($hash, '$2y$') && $hash !== 'Correct-Horse-9');
$u->follow();
check('New customer lands on the dashboard', $u->code === 200 && $u->has('Hello Test'));
$u->post('/wishlist/toggle', ['product_id' => $jagId], true);
check('Wishlist add works', ($u->json()['added'] ?? false) === true && (int) $one("select count(*) from wishlists w join customers c on c.id=w.customer_id where c.email='$email'") === 1);
$u->get('/wishlist');
check('Wishlist page lists the saved product', $u->has('Low GI Jaggery 500gm'));
$u->get('/my-account/addresses');
$u->post('/my-account/addresses', ['label' => 'Home', 'full_name' => 'Test Buyer', 'phone' => '9876543210', 'line1' => '12 Test Street', 'line2' => '', 'city' => 'Pune', 'state' => 'Maharashtra', 'postcode' => '411040']);
check('Address saved and made default', (int) $one("select count(*) from customer_addresses a join customers c on c.id=a.customer_id where c.email='$email' and a.is_default=1") === 1);
$u->post('/cart/add', ['product_id' => (int) $one("select id from products where slug='sugarlif-honey'"), 'quantity' => 1], true);
$u->get('/checkout');
check('Checkout pre-fills the saved address for a signed-in customer', str_contains(html_entity_decode($u->body, ENT_QUOTES | ENT_HTML5), 'value="12 Test Street"'));
$u->post('/checkout', ['email' => $email, 'phone' => '9876543210', 'shipping_name' => 'Test Buyer', 'shipping_line1' => '12 Test Street', 'shipping_city' => 'Pune', 'shipping_state' => 'Maharashtra', 'shipping_postcode' => '411040', 'billing_same' => 1, 'accept_terms' => 1]);
$o2 = $db->query('select * from orders order by id desc limit 1')->fetch_assoc();
check('Signed-in order is linked to the customer (Honey 600, free shipping)', (float) $o2['grand_total'] === 600.0 && (int) $o2['customer_id'] === (int) $one("select id from customers where email='$email'"));
$u->get('/my-account/orders');
check('Order history lists the order', $u->has($o2['order_number']));
$u->get('/my-account/orders/' . $o2['order_number']);
check('Order detail page loads', $u->code === 200 && $u->has('Honey'));
$u->get('/my-account/orders/' . $o['order_number']);
check('A customer cannot open someone else\'s order', $u->code === 404);
$u->get('/my-account/logout');
$u->get('/my-account/login');
$u->post('/my-account/login', ['email' => $email, 'password' => 'wrong-password']);
$u->follow();
check('Login with a wrong password is refused', $u->has('incorrect'));
$u->post('/my-account/login', ['email' => $email, 'password' => 'Correct-Horse-9']);
check('Login with the right password succeeds', str_contains($u->location, 'my-account') && ! str_contains($u->location, 'login'));
$u->get('/my-account/forgot-password');
$u->post('/my-account/forgot-password', ['email' => $email]);
check('Password reset token is stored hashed and an e-mail is written', (int) $one("select count(*) from password_resets where email='$email' and length(token_hash)=64") === 1 && count(glob($root . '/writable/emails/*password-reset*')) > 0);
$mail = html_entity_decode(file_get_contents(array_slice(glob($root . '/writable/emails/*password-reset*'), -1)[0]), ENT_QUOTES | ENT_HTML5);
preg_match('#reset-password/([a-f0-9]{64})#', $mail, $m);
$u->get('/my-account/logout');
$u->get('/my-account/reset-password/' . ($m[1] ?? 'x'));
check('Reset link opens the new-password form', $u->code === 200 && $u->has('Choose a new password'));
$u->post('/my-account/reset-password/' . $m[1], ['password' => 'Brand-New-Pass-7', 'password_confirm' => 'Brand-New-Pass-7']);
$u->get('/my-account/reset-password/' . $m[1]);
check('Reset link cannot be reused', $u->code >= 300);
$u->get('/my-account/login');
$u->post('/my-account/login', ['email' => $email, 'password' => 'Brand-New-Pass-7']);
check('Login works with the new password', str_contains($u->location, 'my-account') && ! str_contains($u->location, 'login'));

section('Forms');
$f = new Client($base);
$f->get('/contact-us');
$f->post('/contact-us', ['first_name' => 'A', 'email' => 'nope', 'message' => 'hi']);
check('Contact form rejects invalid input', (int) $one("select count(*) from contact_enquiries where email='nope'") === 0);
$f->get('/contact-us');
$f->post('/contact-us', ['first_name' => 'Test', 'last_name' => 'Sender', 'email' => $email, 'phone' => '9876543210', 'subject' => "Bulk order $tag", 'message' => 'I would like to order in bulk. <script>alert(1)</script>']);
check('Contact enquiry is stored', (int) $one("select count(*) from contact_enquiries where subject='Bulk order $tag'") === 1);
$f->get('/contact-us');
$f->post('/contact-us', ['first_name' => 'Bot', 'email' => "bot$email", 'message' => 'spam spam spam spam', 'website' => 'http://spam']);
check('Honeypot submission is silently dropped', (int) $one("select count(*) from contact_enquiries where email='bot$email'") === 0);
$f->post('/newsletter/subscribe', ['email' => 'not-an-email'], true);
check('Newsletter rejects an invalid e-mail', $f->code === 422);
$f->post('/newsletter/subscribe', ['email' => $email], true);
check('Newsletter subscription is stored', ($f->json()['ok'] ?? false) && (int) $one("select count(*) from newsletter_subscribers where email='$email'") === 1);
$f->post('/newsletter/subscribe', ['email' => $email], true);
check('Duplicate subscription does not create a second row', (int) $one("select count(*) from newsletter_subscribers where email='$email'") === 1);

section('SEO and redirects');
$s = new Client($base);
$s->get('/sitemap.xml');
check('sitemap.xml lists products, pages and posts', $s->has('/product/sugarlif-honey') && $s->has('/glycemic-index') && $s->has('/blog/episode-5-sugar-on-trial'));
$s->get('/robots.txt');
check('robots.txt blocks admin and points to the sitemap', $s->has('Disallow: /admin/') && $s->has('Sitemap: '));
$s->get('/products');
check('/products 301-redirects to /shop', $s->code === 301 && str_ends_with($s->location, '/shop'));
$s->get('/episode-5-sugar-on-trial');
check('Old blog URL 301-redirects under /blog', $s->code === 301 && str_ends_with($s->location, '/blog/episode-5-sugar-on-trial'));
$s->get('/product-tag/suger');
check('Old product tag URL 301-redirects', $s->code === 301);
$s->get('/faq');
check('FAQ page has FAQPage structured data and 12 questions', $s->has('"@type":"FAQPage"') && substr_count($s->body, 'data-faq-item') === 12);
$s->get('/cart');
check('Cart is noindex', $s->has('noindex'));
$s->get('/no-such-page');
check('Unknown URL returns 404', $s->code === 404);

// ============================================================ ADMIN
section('Admin authentication');
$a = new Client($base);
$a->get('/admin');
check('Admin redirects to sign-in when signed out', $a->code >= 300 && str_contains($a->location, 'admin/login'));
$a->get('/admin/login');
$a->post('/admin/login', ['email' => $get('admin.defaultEmail'), 'password' => 'definitely-wrong']);
$a->follow();
check('Admin login with a wrong password is refused', $a->has('incorrect'));
$a->post('/admin/login', ['email' => $get('admin.defaultEmail'), 'password' => $get('admin.defaultPassword')]);
check('Admin login succeeds', $a->code >= 300 && str_ends_with(rtrim($a->location, '/'), '/admin'));
$a->get('/admin/products');
$forced = (int) $one('select must_change_password from admin_users where id=1') === 1;
check('Seeded admin is forced to change password before using the panel', ! $forced || str_contains($a->location, 'admin/profile'));
$db->query('update admin_users set must_change_password=0 where id=1');
$a->get('/admin');
check('Dashboard loads with KPI cards and charts', $a->code === 200 && $a->has('salesChart') && $a->has('Total orders'));
$cust = new Client($base);
$cust->get('/admin/products');
check('A signed-out visitor cannot open admin screens', str_contains($cust->location, 'admin/login'));

section('Admin CRUD (FAQ)');
$a->get('/admin/faqs/new');
$a->post('/admin/faqs', ['question' => '', 'answer' => '']);
check('FAQ validation requires a question', (int) $one("select count(*) from faqs where question=''") === 0);
$a->get('/admin/faqs/new');
$a->post('/admin/faqs', ['question' => "Test question $tag", 'answer' => '<p>Answer</p><script>alert(1)</script><p onclick="x()">ok</p>', 'sort_order' => 99, 'is_active' => 1]);
$faqId = (int) $one("select id from faqs where question='Test question $tag'");
check('FAQ created', $faqId > 0);
$ans = (string) $one("select answer from faqs where id=$faqId");
check('Rich text is sanitised on save (script and event handler removed)', ! str_contains($ans, '<script') && ! str_contains($ans, 'onclick'), $ans);
$a->get("/admin/faqs/$faqId/edit");
$a->post("/admin/faqs/$faqId", ['question' => "Edited question $tag", 'answer' => '<p>Answer 2</p>', 'sort_order' => 99, 'is_active' => 1]);
check('FAQ updated', (string) $one("select question from faqs where id=$faqId") === "Edited question $tag");
$s->get('/faq');
check('Edited FAQ appears on the public FAQ page', $s->has("Edited question $tag"));
$a->get('/admin/faqs');
$a->post("/admin/faqs/$faqId/toggle/is_active");
check('FAQ toggled off', (int) $one("select is_active from faqs where id=$faqId") === 0);
$s->get('/faq');
check('Inactive FAQ disappears from the public page', ! $s->has("Edited question $tag"));
$a->get('/admin/faqs');
$a->post("/admin/faqs/$faqId/delete");
check('FAQ deleted', (int) $one("select count(*) from faqs where id=$faqId") === 0);

section('Admin product management');
$a->get('/admin/products/new');
$a->post('/admin/products', ['name' => "Test Product $tag", 'type' => 'simple', 'regular_price' => '200', 'sale_price' => '250', 'is_visible' => 1]);
check('Sale price above regular price is rejected', (int) $one("select count(*) from products where name='Test Product $tag'") === 0);
$a->get('/admin/products/new');
$a->post('/admin/products', ['name' => "Test Product $tag", 'type' => 'variable', 'is_visible' => 1, 'category_id' => 1, 'short_description' => '<ul><li>Point</li></ul>', 'description' => '<p>Desc</p>',
    'variation_label' => 'Pack Weight', 'v_id' => [0, 0], 'v_label' => ['1kg', '2kg'], 'v_regular' => ['100', '190'], 'v_sale' => ['', '170'], 'v_stock' => ['in_stock', 'in_stock'], 'v_sku' => ['', ''], 'v_qty' => ['', ''],
    'a_name' => ['Origin'], 'a_value' => ['India']]);
$pid = (int) $one("select id from products where name='Test Product $tag'");
$row = $pid ? $db->query("select * from products where id=$pid")->fetch_assoc() : [];
check('Variable product created with 2 pack sizes and 1 specification', $pid > 0 && (int) $one("select count(*) from product_variations where product_id=$pid") === 2 && (int) $one("select count(*) from product_attributes where product_id=$pid") === 1);
check('Price range is derived from the variations (100 to 170)', $pid && (float) $row['price_min'] === 100.0 && (float) $row['price_max'] === 170.0, ($row['price_min'] ?? '') . '-' . ($row['price_max'] ?? ''));
$s->get('/product/' . ($row['slug'] ?? 'x'));
check('New product is live on the storefront with both packs', $s->code === 200 && substr_count($s->body, 'data-variation=') === 2 && $s->has('data-price="170" data-regular="190"'));
$png = sys_get_temp_dir() . "/$tag.png"; $im = imagecreatetruecolor(900, 900); imagefill($im, 0, 0, imagecolorallocate($im, 31, 58, 43)); imagepng($im, $png);
$a->get("/admin/products/$pid/edit");
$a->post("/admin/products/$pid/images", [], false, ['images[]' => [$png, 'image/png']]);
$img = (string) $one("select path from product_images where product_id=$pid");
check('Image upload stores the original and adds it to the gallery', $img !== '' && is_file("$root/public/uploads/media/original/$img"));
check('Optimised WebP renditions are generated', $img !== '' && is_file("$root/public/uploads/media/md/" . preg_replace('/\.png$/', '.webp', $img)));
$php = sys_get_temp_dir() . "/$tag.php.png"; file_put_contents($php, '<?php echo 1;');
$a->get('/admin/media');
$a->post('/admin/media', ['folder' => 'general'], false, ['files[]' => [$php, 'image/png']]);
check('A non-image file disguised as PNG is rejected', (int) $one("select count(*) from media where file_name like '$tag%php%'") === 0);
$mid = (int) $one("select id from media where path='$img'");
$a->get('/admin/media');
$a->post("/admin/media/$mid/delete");
check('Media in use cannot be deleted', (int) $one("select count(*) from media where id=$mid") === 1);
$a->get("/admin/products/$pid/edit");
$vids = array_column($db->query("select id from product_variations where product_id=$pid order by sort_order")->fetch_all(MYSQLI_ASSOC), 'id');
$a->post("/admin/products/$pid", ['name' => "Test Product $tag", 'slug' => $row['slug'], 'type' => 'variable', 'is_visible' => 1, 'category_id' => 1, 'variation_label' => 'Pack Weight',
    'v_id' => [$vids[0]], 'v_label' => ['1kg'], 'v_regular' => ['120'], 'v_sale' => [''], 'v_stock' => ['out_of_stock'], 'v_sku' => [''], 'v_qty' => ['']]);
$row = $db->query("select * from products where id=$pid")->fetch_assoc();
check('Editing removes a pack, updates the price and marks the product out of stock', (int) $one("select count(*) from product_variations where product_id=$pid") === 1 && (float) $row['price_min'] === 120.0 && $row['stock_status'] === 'out_of_stock');
$a->get('/admin/products');
$a->post("/admin/products/$pid/toggle/is_visible");
$s->get('/product/' . $row['slug']);
check('Hidden product returns 404 on the storefront', $s->code === 404);
$a->post("/admin/products/$pid/delete");
check('Product is soft-deleted', $one("select deleted_at from products where id=$pid") !== null);

section('Admin order workflow');
$a->get("/admin/orders/{$o2['id']}");
check('Order detail screen loads', $a->code === 200 && $a->has($o2['order_number']));
$a->post("/admin/orders/{$o2['id']}/status", ['status' => 'shipped']);
check('An unpaid order cannot be moved to Shipped', (string) $one("select status from orders where id={$o2['id']}") === 'pending');
$a->get("/admin/orders/{$o2['id']}");
$a->post("/admin/orders/{$o2['id']}/payment", ['reference' => "REF$tag", 'mode' => 'Bank transfer', 'confirm' => 1]);
check('Recording an offline payment marks the order paid and processing', $db->query("select concat(status,'/',payment_status) from orders where id={$o2['id']}")->fetch_row()[0] === 'processing/paid');
$a->get("/admin/orders/{$o2['id']}");
$a->post("/admin/orders/{$o2['id']}/shipping", ['courier_name' => 'Test Courier', 'tracking_number' => "TRK$tag", 'tracking_url' => 'https://example.test/track']);
$a->get("/admin/orders/{$o2['id']}");
$a->post("/admin/orders/{$o2['id']}/status", ['status' => 'shipped', 'note' => 'Dispatched', 'notify' => 1]);
$row = $db->query("select status, shipped_at, tracking_number from orders where id={$o2['id']}")->fetch_assoc();
check('Paid order moves to Shipped with tracking and a timestamp', $row['status'] === 'shipped' && $row['shipped_at'] !== null && $row['tracking_number'] === "TRK$tag");
check('Status history is recorded', (int) $one("select count(*) from order_status_history where order_id={$o2['id']}") >= 4);
check('Customer status e-mail is written', count(glob($root . '/writable/emails/*order-status*')) > 0);
$u->get('/my-account/orders/' . $o2['order_number']);
check('Customer sees the tracking number on their order', $u->has("TRK$tag"));
$a->get('/admin/orders?q=' . $o2['order_number'] . '&status=shipped&payment=paid');
check('Order filters find the order', $a->has($o2['order_number']));
$a->get('/admin/customers?q=' . urlencode($email));
check('Customer list finds the test customer', $a->has($email));

section('Admin content and settings');
$secId = (int) $one("select s.id from page_sections s join pages p on p.id=s.page_id where p.slug='home' and s.section_key='featured'");
$a->get("/admin/pages/sections/$secId/edit");
$orig = $db->query("select heading from page_sections where id=$secId")->fetch_row()[0];
$sec  = $db->query("select * from page_sections where id=$secId")->fetch_assoc();
$a->post("/admin/pages/sections/$secId", ['eyebrow' => $sec['eyebrow'], 'heading' => "Heading $tag", 'body' => $sec['body'], 'cta_label' => $sec['cta_label'], 'cta_url' => $sec['cta_url'], 'is_active' => 1]);
$s->get('/');
check('Editing a home section changes the live home page', $s->has("Heading $tag"));
$a->get("/admin/pages/sections/$secId/edit");
$a->post("/admin/pages/sections/$secId", ['eyebrow' => $sec['eyebrow'], 'heading' => $orig, 'body' => $sec['body'], 'cta_label' => $sec['cta_label'], 'cta_url' => $sec['cta_url'], 'is_active' => 1]);
$listId = (int) $one("select s.id from page_sections s join pages p on p.id=s.page_id where p.slug='home' and s.section_key='benefits'");
$sec    = $db->query("select * from page_sections where id=$listId")->fetch_assoc();
$items  = json_decode($sec['items'], true);
$a->get("/admin/pages/sections/$listId/edit");
$a->post("/admin/pages/sections/$listId", ['eyebrow' => $sec['eyebrow'], 'heading' => $sec['heading'], 'cta_label' => $sec['cta_label'], 'cta_url' => $sec['cta_url'], 'is_active' => 1, 'items' => array_merge($items, [['text' => "Row $tag"], ['text' => '']])]);
check('Section list rows can be added (blank rows dropped)', count(json_decode((string) $one("select items from page_sections where id=$listId"), true)) === count($items) + 1);
$a->get("/admin/pages/sections/$listId/edit");
$a->post("/admin/pages/sections/$listId", ['eyebrow' => $sec['eyebrow'], 'heading' => $sec['heading'], 'cta_label' => $sec['cta_label'], 'cta_url' => $sec['cta_url'], 'is_active' => 1, 'items' => $items]);
check('Section list restored', (string) $one("select items from page_sections where id=$listId") === $sec['items'] || count(json_decode((string) $one("select items from page_sections where id=$listId"), true)) === count($items));
$a->get('/admin/settings/shop');
$a->post('/admin/settings/shop', ['shipping_flat_rate' => '55', 'free_shipping_threshold' => '700', 'order_processing_time' => '1 business day', 'shipping_region_note' => 'We deliver across India.', 'gst_number' => '', 'fssai_number' => '']);
$t = new Client($base);
$t->get('/');
$t->post('/cart/add', ['product_id' => $jagId, 'quantity' => 1], true);
check('Changing shipping settings changes the cart calculation (55, free from 700)', (float) ($t->json()['totals']['shipping'] ?? 0) === 55.0 && (float) ($t->json()['totals']['free_threshold'] ?? 0) === 700.0);
$a->get('/admin/settings/shop');
$a->post('/admin/settings/shop', ['shipping_flat_rate' => '40', 'free_shipping_threshold' => '500', 'order_processing_time' => '1 business day', 'shipping_region_note' => 'We deliver across India.', 'gst_number' => '', 'fssai_number' => '']);
check('Shipping settings restored to the verified rule (40 / 500)', (string) $one("select value from settings where `key`='shipping_flat_rate'") === '40' && (string) $one("select value from settings where `key`='free_shipping_threshold'") === '500');
$a->get('/admin/pages/new');
$a->post('/admin/pages', ['title' => "Page $tag", 'slug' => 'shop', 'template' => 'default', 'body' => '<p>x</p>', 'is_published' => 1]);
check('A new page cannot take a reserved URL', (int) $one("select count(*) from pages where title='Page $tag'") === 0);
$a->get('/admin/pages/new');
$a->post('/admin/pages', ['title' => "Page $tag", 'slug' => '', 'template' => 'default', 'body' => '<p>Hello page</p>', 'is_published' => 1]);
$s->get("/page-$tag");
check('New CMS page is published at its own URL', $s->code === 200 && $s->has('Hello page'));
$newPage = (int) $one("select id from pages where title='Page $tag'");
$a->get('/admin/pages');
$a->post("/admin/pages/$newPage/delete");
$sys = (int) $one("select id from pages where slug='faq'");
$a->post("/admin/pages/$sys/delete");
check('Custom page deleted; structural page protected from deletion', (int) $one("select count(*) from pages where id=$newPage") === 0 && (int) $one("select count(*) from pages where id=$sys") === 1);
$a->get('/admin/redirects/new');
$a->post('/admin/redirects', ['from_path' => "/old-$tag", 'to_path' => '/shop', 'status_code' => 301, 'is_active' => 1]);
$s->get("/old-$tag");
check('A redirect added in admin works immediately', $s->code === 301 && str_ends_with($s->location, '/shop'));
$a->get('/admin/subscribers/export');
check('Newsletter CSV export contains the subscriber', $a->has($email) && $a->has('email,subscribed_at'));
$enqId = (int) $one("select id from contact_enquiries where subject='Bulk order $tag'");
$a->get("/admin/enquiries/$enqId");
check('Enquiry opens, is escaped, and is marked read', $a->has('&lt;script&gt;') && ! $a->has('<script>alert(1)</script>') && (string) $one("select status from contact_enquiries where id=$enqId") === 'read');

section('Roles');
$db->query("insert into admin_users (role_id,name,email,password_hash,is_active,must_change_password,created_at,updated_at) values ((select id from roles where slug='content-editor'),'Editor $tag','editor$email','" . password_hash('Editor-Pass-123', PASSWORD_DEFAULT) . "',1,0,now(),now())");
$e = new Client($base);
$e->get('/admin/login');
$e->post('/admin/login', ['email' => "editor$email", 'password' => 'Editor-Pass-123']);
$e->get('/admin/pages');
check('Content Editor can open Pages', $e->code === 200);
$e->get('/admin/orders');
check('Content Editor is blocked from Orders', $e->code >= 300);
$e->get('/admin/users');
check('Content Editor is blocked from Admin users', $e->code >= 300);
$e->get('/admin');
check('Content Editor sidebar hides Sales and Configuration', $e->code === 200 && ! $e->has('bi bi-gear') && ! $e->has('bi bi-shield-lock') && $e->has('bi bi-file-earmark-text'));
$a->get('/admin/logout');
$a->get('/admin');
check('Admin sign-out ends the session', str_contains($a->location, 'admin/login'));

// ============================================================ CLEAN UP
section('Clean up');
$db->query('update admin_users set must_change_password=' . ($forced ? 1 : 0) . ' where id=1');
$db->query("delete from admin_users where email='editor$email'");
$db->query("delete from orders where email='$email'");
$db->query("delete from customers where email='$email'");
$db->query("delete from contact_enquiries where email='$email'");
$db->query("delete from newsletter_subscribers where email='$email'");
$db->query("delete from password_resets where email='$email'");
$db->query("delete from coupons where code='" . strtoupper($tag) . "'");
$db->query("delete from redirects where from_path='/old-$tag'");
$db->query("delete from product_images where product_id=$pid");
$db->query("delete from products where id=$pid");
$db->query('delete from carts where customer_id is null and id not in (select cart_id from cart_items)');
foreach ($db->query("select * from media where file_name like '$tag%'")->fetch_all(MYSQLI_ASSOC) as $mrow) {
    @unlink("$root/public/uploads/media/original/{$mrow['path']}");
    foreach (['lg', 'md', 'sm'] as $sz) { @unlink("$root/public/uploads/media/$sz/" . preg_replace('/\.[a-z]+$/', '.webp', $mrow['path'])); }
    $db->query('delete from media where id=' . (int) $mrow['id']);
}
array_map('unlink', glob($root . '/writable/emails/*.html') ?: []);
file_put_contents($root . '/writable/emails/index.html', '');
array_map('unlink', glob($root . '/writable/cache/*') ?: []);
@unlink($png); @unlink($php);
echo "  test data removed\n";

echo "\n==============================\n$pass passed, $fail failed\n";
if ($failures) { echo "Failed:\n - " . implode("\n - ", $failures) . "\n"; }
exit($fail ? 1 : 0);
