<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 * Original natural-life.in URLs are preserved wherever possible (/product/{slug},
 * /about-naturalife, /refund_returns ...). URLs that changed are 301-redirected by
 * the Redirects filter from the `redirects` table.
 */

// ---------------------------------------------------------------- storefront
$routes->get('/', 'Home::index');

$routes->get('shop', 'Shop::index');
$routes->get('shop/suggest', 'Shop::suggest');
$routes->get('shop/quick-view/(:segment)', 'Shop::quickView/$1');
$routes->get('product-category/(:segment)', 'Shop::category/$1');
$routes->get('product/(:segment)', 'Product::show/$1');

$routes->get('cart', 'Cart::index');
$routes->get('cart/drawer', 'Cart::drawer');
$routes->post('cart/add', 'Cart::add');
$routes->post('cart/update', 'Cart::update');
$routes->post('cart/remove', 'Cart::remove');
$routes->post('cart/coupon', 'Cart::applyCoupon');
$routes->post('cart/coupon/remove', 'Cart::removeCoupon');

$routes->get('checkout', 'Checkout::index');
$routes->post('checkout', 'Checkout::place');
$routes->get('checkout/payment/(:segment)', 'Checkout::payment/$1');
$routes->get('order/(:segment)', 'Checkout::order/$1');
$routes->post('payment/ccavenue/response', 'Payment::ccavenueResponse');
$routes->post('payment/ccavenue/cancel', 'Payment::ccavenueCancel');

$routes->get('wishlist', 'Wishlist::index');
$routes->post('wishlist/toggle', 'Wishlist::toggle');

$routes->get('blog', 'Blog::index');
$routes->get('blog/category/(:segment)', 'Blog::index/$1');
$routes->get('blog/(:segment)', 'Blog::show/$1');

$routes->post('contact-us', 'Contact::submit');
$routes->post('newsletter/subscribe', 'Contact::subscribe');

$routes->get('sitemap.xml', 'Seo::sitemap');
$routes->get('robots.txt', 'Seo::robots');

// ---------------------------------------------------------------- customer account
$routes->group('my-account', static function ($routes) {
    $routes->get('login', 'Account::login');
    $routes->post('login', 'Account::attemptLogin');
    $routes->post('register', 'Account::attemptRegister');
    $routes->get('forgot-password', 'Account::forgot');
    $routes->post('forgot-password', 'Account::sendReset');
    $routes->get('reset-password/(:segment)', 'Account::reset/$1');
    $routes->post('reset-password/(:segment)', 'Account::doReset/$1');
    $routes->get('logout', 'Account::logout');

    $routes->group('', ['filter' => 'customerauth'], static function ($routes) {
        $routes->get('/', 'Account::dashboard');
        $routes->get('orders', 'Account::orders');
        $routes->get('orders/(:segment)', 'Account::order/$1');
        $routes->get('addresses', 'Account::addresses');
        $routes->post('addresses', 'Account::saveAddress');
        $routes->post('addresses/delete/(:num)', 'Account::deleteAddress/$1');
        $routes->get('profile', 'Account::profile');
        $routes->post('profile', 'Account::updateProfile');
    });
});

// ---------------------------------------------------------------- admin panel
$routes->group('admin', ['namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attempt');
    $routes->get('logout', 'Auth::logout');

    $routes->group('', ['filter' => 'adminauth'], static function ($routes) {
        $routes->get('/', 'Dashboard::index');
        $routes->get('search', 'Dashboard::search');
        $routes->get('profile', 'Auth::profile');
        $routes->post('profile', 'Auth::updateProfile');

        // Config-driven CRUD modules: list, create, edit, delete, toggle.
        $crud = [
            'categories' => 'Categories', 'coupons' => 'Coupons', 'banners' => 'Banners', 'posts' => 'Posts',
            'blog-categories' => 'BlogCategories', 'faqs' => 'Faqs', 'testimonials' => 'Testimonials', 'team' => 'Team',
            'certifications' => 'Certifications', 'redirects' => 'Redirects', 'seo' => 'SeoMeta', 'users' => 'Users',
            'menu-items' => 'MenuItems',
        ];
        foreach ($crud as $path => $controller) {
            $routes->get($path, "$controller::index");
            $routes->get("$path/new", "$controller::create");
            $routes->post($path, "$controller::store");
            $routes->get("$path/(:num)/edit", "$controller::edit/$1");
            $routes->post("$path/(:num)", "$controller::update/$1");
            $routes->post("$path/(:num)/delete", "$controller::delete/$1");
            $routes->post("$path/(:num)/toggle/(:segment)", "$controller::toggle/$1/$2");
        }

        // Products
        $routes->get('products', 'Products::index');
        $routes->get('products/new', 'Products::create');
        $routes->post('products', 'Products::store');
        $routes->get('products/(:num)/edit', 'Products::edit/$1');
        $routes->post('products/(:num)', 'Products::update/$1');
        $routes->post('products/(:num)/delete', 'Products::delete/$1');
        $routes->post('products/(:num)/toggle/(:segment)', 'Products::toggle/$1/$2');
        $routes->post('products/(:num)/images', 'Products::uploadImages/$1');
        $routes->post('products/(:num)/images/attach', 'Products::attachImage/$1');
        $routes->post('products/images/(:num)/primary', 'Products::primaryImage/$1');
        $routes->post('products/images/(:num)/delete', 'Products::deleteImage/$1');

        // Orders and customers
        $routes->get('orders', 'Orders::index');
        $routes->get('orders/(:num)', 'Orders::show/$1');
        $routes->post('orders/(:num)/status', 'Orders::updateStatus/$1');
        $routes->post('orders/(:num)/shipping', 'Orders::updateShipping/$1');
        $routes->post('orders/(:num)/payment', 'Orders::recordPayment/$1');
        $routes->post('orders/(:num)/note', 'Orders::saveNote/$1');
        $routes->get('customers', 'Customers::index');
        $routes->get('customers/(:num)', 'Customers::show/$1');
        $routes->post('customers/(:num)/toggle', 'Customers::toggle/$1');

        // Pages and page sections
        $routes->get('pages', 'Pages::index');
        $routes->get('pages/new', 'Pages::create');
        $routes->post('pages', 'Pages::store');
        $routes->get('pages/(:num)/edit', 'Pages::edit/$1');
        $routes->post('pages/(:num)', 'Pages::update/$1');
        $routes->post('pages/(:num)/delete', 'Pages::delete/$1');
        $routes->get('pages/sections/(:num)/edit', 'Pages::editSection/$1');
        $routes->post('pages/sections/(:num)', 'Pages::updateSection/$1');
        $routes->post('pages/sections/(:num)/toggle', 'Pages::toggleSection/$1');
        $routes->get('home', 'Pages::home');

        // Navigation, inbox, media, settings
        $routes->get('menus', 'Menus::index');
        $routes->get('enquiries', 'Enquiries::index');
        $routes->get('enquiries/(:num)', 'Enquiries::show/$1');
        $routes->post('enquiries/(:num)', 'Enquiries::update/$1');
        $routes->post('enquiries/(:num)/delete', 'Enquiries::delete/$1');
        $routes->get('subscribers', 'Subscribers::index');
        $routes->get('subscribers/export', 'Subscribers::export');
        $routes->post('subscribers/(:num)/toggle', 'Subscribers::toggle/$1');
        $routes->post('subscribers/(:num)/delete', 'Subscribers::delete/$1');
        $routes->get('media', 'Media::index');
        $routes->get('media/picker', 'Media::picker');
        $routes->post('media', 'Media::upload');
        $routes->post('media/(:num)', 'Media::update/$1');
        $routes->post('media/(:num)/delete', 'Media::delete/$1');
        $routes->get('settings', 'Settings::index');
        $routes->get('settings/(:segment)', 'Settings::index/$1');
        $routes->post('settings/(:segment)', 'Settings::save/$1');
    });
});

// Media kept in the database (read-only hosts). Files present in public/uploads never reach this.
$routes->get('uploads/media/(:any)', 'MediaFile::show/$1');

// ---------------------------------------------------------------- CMS pages (must stay last)
// Legacy commerce URLs kept from the original site.
$routes->get('my-account', 'Account::dashboard', ['filter' => 'customerauth']);
$routes->get('(:segment)', 'Page::show/$1');
// Anything else: lets the Redirects filter serve multi-segment legacy URLs, otherwise a 404.
$routes->get('(:any)', 'Page::notFound');
