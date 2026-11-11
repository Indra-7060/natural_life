<?php

/**
 * Storefront + admin view helpers: settings, money, media URLs, menus, safe rich text.
 */

use App\Models\SettingModel;

if (! function_exists('setting')) {
    /** Reads a value from the `settings` table (cached). */
    function setting(string $key, ?string $default = ''): ?string
    {
        $all = model(SettingModel::class)->allKeyed();

        return (isset($all[$key]) && $all[$key] !== '') ? $all[$key] : $default;
    }
}

if (! function_exists('money')) {
    /** Indian-format rupee amount: 1200 -> ₹1,200 ; 99.5 -> ₹99.50 */
    function money($amount): string
    {
        $amount = (float) $amount;
        $dec    = (floor($amount) == $amount) ? 0 : 2;
        $neg    = $amount < 0 ? '-' : '';
        [$int, $frac] = array_pad(explode('.', number_format(abs($amount), $dec, '.', '')), 2, '');
        // Indian grouping: last three digits, then groups of two.
        if (strlen($int) > 3) {
            $last = substr($int, -3);
            $rest = substr($int, 0, -3);
            $int  = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest) . ',' . $last;
        }

        return $neg . '₹' . $int . ($dec ? '.' . $frac : '');
    }
}

if (! function_exists('media_url')) {
    /**
     * URL of a media-library image. $size: original | lg (1600) | md (900) | sm (480).
     * Optimised WebP variants are generated on upload; the untouched original is always kept.
     */
    function media_url(?string $path, string $size = 'md'): string
    {
        if (! $path) {
            return base_url('assets/img/placeholder.svg');
        }
        if (preg_match('#^(https?:)?//#', $path)) {
            return $path;
        }
        $path = ltrim($path, '/');
        if ($size !== 'original' && ! preg_match('/\.(svg|gif)$/i', $path)) {
            $variant = 'uploads/media/' . $size . '/' . preg_replace('/\.[a-z0-9]+$/i', '.webp', $path);
            if (is_file(FCPATH . $variant)) {
                return base_url($variant);
            }
        }

        return base_url('uploads/media/original/' . $path);
    }
}

if (! function_exists('asset_url')) {
    /** Versioned static asset URL (cache-busts on file change). */
    function asset_url(string $path): string
    {
        $file = FCPATH . 'assets/' . ltrim($path, '/');
        $v    = is_file($file) ? filemtime($file) : '1';

        return base_url('assets/' . ltrim($path, '/')) . '?v=' . $v;
    }
}

if (! function_exists('rich')) {
    /**
     * Outputs CMS rich text. Content is authored by administrators, but is still reduced to a
     * safe tag whitelist with event handlers and script URLs removed before it is printed.
     */
    function rich(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }
        $html = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><h5><a><blockquote><table><thead><tbody><tr><td><th><span><sup><sub><hr>');
        $html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
        $html = preg_replace('/\s+style\s*=\s*("[^"]*"|\'[^\']*\')/i', '', $html);
        $html = preg_replace('/(href|src)\s*=\s*(["\'])\s*(javascript|data|vbscript):[^"\']*\2/i', '$1="#"', $html);

        return $html;
    }
}

if (! function_exists('plain')) {
    /** Plain-text excerpt of rich text. */
    function plain(?string $html, int $limit = 0): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES, 'UTF-8')));
        if ($limit > 0 && mb_strlen($text) > $limit) {
            $text = rtrim(mb_substr($text, 0, $limit - 1), " ,.;:-") . '…';
        }

        return $text;
    }
}

if (! function_exists('section_items')) {
    /** Decodes the JSON `items` list of a page section into an array of rows. */
    function section_items(?array $section): array
    {
        if (! $section || empty($section['items'])) {
            return [];
        }
        $items = json_decode($section['items'], true);

        return is_array($items) ? $items : [];
    }
}

if (! function_exists('site_menu')) {
    /** Returns a navigation menu as a tree: each top-level item carries a `children` list. */
    function site_menu(string $location): array
    {
        static $loaded = [];
        if (isset($loaded[$location])) {
            return $loaded[$location];
        }
        $tree = cache('menu_' . $location);
        if (! is_array($tree)) {
            $tree = [];

            try {
                $rows = db_connect()->table('menu_items mi')
                    ->select('mi.*')
                    ->join('menus m', 'm.id = mi.menu_id')
                    ->where('m.location', $location)->where('mi.is_active', 1)
                    ->orderBy('mi.sort_order')->orderBy('mi.id')->get()->getResultArray();
                $byParent = [];
                foreach ($rows as $r) {
                    $byParent[(int) $r['parent_id']][] = $r;
                }
                foreach ($byParent[0] ?? [] as $top) {
                    $top['children'] = $byParent[(int) $top['id']] ?? [];
                    $tree[]          = $top;
                }
                cache()->save('menu_' . $location, $tree, 600);
            } catch (\Throwable $e) {
                $tree = [];
            }
        }

        return $loaded[$location] = $tree;
    }
}

if (! function_exists('menu_href')) {
    /** Menu URLs are stored site-relative ("/shop") or absolute. */
    function menu_href(string $url): string
    {
        if ($url === '' || $url === '#') {
            return '#';
        }

        return preg_match('#^(https?:|mailto:|tel:)#', $url) ? $url : site_url(ltrim($url, '/'));
    }
}

if (! function_exists('is_active_path')) {
    function is_active_path(string $url): bool
    {
        $current = '/' . trim(service('uri')->getPath(), '/');
        $target  = '/' . trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        return $target !== '/' ? str_starts_with($current . '/', $target . '/') : $current === '/';
    }
}

if (! function_exists('product_price_html')) {
    /** Price block for cards and detail pages; handles ranges and sale prices. */
    function product_price_html(array $p): string
    {
        if ($p['type'] === 'variable') {
            if ((float) $p['price_min'] === (float) $p['price_max']) {
                return '<span class="price__now">' . money($p['price_min']) . '</span>';
            }

            return '<span class="price__from">From</span> <span class="price__now">' . money($p['price_min']) . '</span>';
        }
        if ($p['sale_price'] !== null && (float) $p['sale_price'] < (float) $p['regular_price']) {
            return '<span class="price__now">' . money($p['sale_price']) . '</span> <del class="price__was">' . money($p['regular_price']) . '</del>';
        }

        return '<span class="price__now">' . money($p['regular_price']) . '</span>';
    }
}

if (! function_exists('discount_percent')) {
    function discount_percent($regular, $sale): int
    {
        $regular = (float) $regular;
        $sale    = (float) $sale;

        return ($regular > 0 && $sale > 0 && $sale < $regular) ? (int) round((1 - $sale / $regular) * 100) : 0;
    }
}

if (! function_exists('status_badge')) {
    function status_badge(string $status): string
    {
        $map = [
            'pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'completed' => 'success',
            'cancelled' => 'secondary', 'refunded' => 'dark', 'unpaid' => 'warning', 'paid' => 'success', 'failed' => 'danger',
            'new' => 'warning', 'read' => 'info', 'replied' => 'success', 'closed' => 'secondary',
            'in_stock' => 'success', 'out_of_stock' => 'danger',
        ];
        $label = ucfirst(str_replace('_', ' ', $status));

        return '<span class="badge-status badge-status--' . ($map[$status] ?? 'secondary') . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text), '-'));

        return $text !== '' ? $text : 'item';
    }
}

if (! function_exists('indian_states')) {
    function indian_states(): array
    {
        return ['Andaman and Nicobar Islands', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chandigarh', 'Chhattisgarh',
            'Dadra and Nagar Haveli and Daman and Diu', 'Delhi', 'Goa', 'Gujarat', 'Haryana', 'Himachal Pradesh', 'Jammu and Kashmir',
            'Jharkhand', 'Karnataka', 'Kerala', 'Ladakh', 'Lakshadweep', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya',
            'Mizoram', 'Nagaland', 'Odisha', 'Puducherry', 'Punjab', 'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura',
            'Uttar Pradesh', 'Uttarakhand', 'West Bengal'];
    }
}

if (! function_exists('admin_can')) {
    /** True when the signed-in administrator's role includes the given module. */
    function admin_can(string $module): bool
    {
        $perms = session('admin_permissions') ?? [];

        return in_array('*', $perms, true) || in_array($module, $perms, true);
    }
}
