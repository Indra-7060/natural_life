<?php

namespace App\Controllers;

use App\Libraries\CartService;
use App\Libraries\WishlistService;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Shared storefront behaviour: session, cart, signed-in customer, SEO defaults and
 * a render() helper that wraps every page in the site layout.
 */
abstract class BaseController extends Controller
{
    protected $helpers = ['url', 'form', 'text', 'site'];

    protected CartService $cart;
    protected WishlistService $wishlist;
    protected ?array $customer = null;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->cart     = new CartService();
        $this->wishlist = new WishlistService();

        if ($id = session('customer_id')) {
            $this->customer = model('CustomerModel')->find($id);
            if (! $this->customer || ! $this->customer['is_active']) {
                session()->remove('customer_id');
                $this->customer = null;
            }
        }
    }

    /**
     * Builds the SEO block for a page. Admin overrides (seo_meta table, by route key)
     * win over the defaults supplied by the controller.
     */
    protected function seo(array $defaults, ?string $routeKey = null): array
    {
        $seo = array_merge([
            'title'       => setting('site_name', 'Sugarlif'),
            'description' => setting('default_meta_description', ''),
            'canonical'   => current_url(),
            'og_image'    => null,
            'og_type'     => 'website',
            'noindex'     => false,
            'breadcrumbs' => [],
            'jsonld'      => [],
        ], $defaults);

        if ($routeKey) {
            $row = model('SeoMetaModel')->where('route_key', $routeKey)->first();
            if ($row) {
                $seo['title']       = $row['meta_title'] ?: $seo['title'];
                $seo['description'] = $row['meta_description'] ?: $seo['description'];
                $seo['og_image']    = $row['og_image'] ?: $seo['og_image'];
                $seo['noindex']     = $seo['noindex'] || (bool) $row['noindex'];
            }
        }

        $suffix = ' | ' . setting('site_name', 'Sugarlif');
        if (empty($defaults['raw_title']) && ! str_contains($seo['title'], setting('site_name', 'Sugarlif'))) {
            $seo['title'] .= $suffix;
        }
        $seo['description'] = plain($seo['description'], 300);

        if ($seo['breadcrumbs']) {
            $list = [];
            foreach ($seo['breadcrumbs'] as $i => [$label, $url]) {
                $list[] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $label, 'item' => $url ?: current_url()];
            }
            $seo['jsonld'][] = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $list];
        }

        return $seo;
    }

    protected function render(string $view, array $data = [], array $seo = []): string
    {
        $data['seo']           = $seo ?: $this->seo([]);
        $data['customer']      = $this->customer;
        $data['cartCount']     = $this->cart->count();
        $data['wishlistIds']   = $this->wishlist->ids();
        $data['wishlistCount'] = count($data['wishlistIds']);

        return view($view, $data);
    }

    protected function isAjax(): bool
    {
        return $this->request->isAJAX() || str_contains($this->request->getHeaderLine('Accept'), 'application/json');
    }

    /** JSON payload shared by every cart endpoint so the drawer and badges stay in sync. */
    protected function cartPayload(array $extra = []): array
    {
        return array_merge([
            'ok'     => true,
            'count'  => $this->cart->count(),
            'totals' => $this->cart->totals(),
            'drawer' => view('partials/cart_drawer_body', ['lines' => $this->cart->lines(), 'totals' => $this->cart->totals()]),
            'csrf'   => csrf_hash(),
        ], $extra);
    }
}
