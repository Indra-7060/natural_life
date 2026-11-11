<?php
/**
 * Announcement bar, sticky header with mega menu, search panel and mobile navigation.
 * Menu items come from Admin > Navigation; the Shop panel is built from live categories and featured products.
 */
$menu = site_menu('primary');
$nav  = cache('nav_shop');
if (! is_array($nav)) {
    $nav = [
        'categories' => db_connect()->table('product_categories')->where('is_active', 1)->orderBy('sort_order')->get()->getResultArray(),
        'products'   => model('ProductModel')->catalogue()->where('products.is_featured', 1)->findAll(4),
    ];
    cache()->save('nav_shop', $nav, 600);
}
$announce = setting('announcement_text');
?>
<?php if ($announce): ?>
<div class="announce">
    <div class="shell announce__inner">
        <div><i class="bi bi-truck me-2" aria-hidden="true"></i><?= esc($announce) ?>
            <?php if (setting('announcement_link_label')): ?> · <a href="<?= menu_href(setting('announcement_link_url', '/shop')) ?>"><?= esc(setting('announcement_link_label')) ?></a><?php endif ?>
        </div>
        <div class="announce__links">
            <a href="<?= site_url('faq') ?>">FAQ</a>
            <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', setting('contact_phone')), 'attr') ?>"><i class="bi bi-telephone me-1" aria-hidden="true"></i><?= esc(setting('contact_phone')) ?></a>
        </div>
    </div>
</div>
<?php endif ?>

<header class="site-header" id="siteHeader">
    <div class="shell site-header__inner">
        <button class="tool-btn nav-toggle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileNav" aria-controls="mobileNav" aria-label="Open menu"><i class="bi bi-list"></i></button>

        <a class="brand" href="<?= site_url('/') ?>" aria-label="<?= esc(setting('site_name', 'Sugarlif'), 'attr') ?> — home">
            <img src="<?= media_url(setting('logo'), 'original') ?>" alt="<?= esc(setting('site_name', 'Sugarlif'), 'attr') ?>" width="140" height="44">
        </a>

        <nav aria-label="Main">
            <ul class="main-nav">
                <?php foreach ($menu as $item):
                    $isShop  = rtrim($item['url'], '/') === '/shop';
                    $hasKids = $isShop || ! empty($item['children']);
                    $active  = is_active_path($item['url']) || ($isShop && (is_active_path('/product') || is_active_path('/product-category')));
                    foreach ($item['children'] as $c) { $active = $active || is_active_path($c['url']); }
                ?>
                <li class="<?= $hasKids ? 'has-panel' : '' ?>">
                    <a class="main-nav__link <?= $active ? 'is-active' : '' ?>" href="<?= menu_href($item['url']) ?>" <?= $hasKids ? 'aria-haspopup="true"' : '' ?>>
                        <?= esc($item['label']) ?><?php if ($hasKids): ?> <i class="bi bi-chevron-down" aria-hidden="true"></i><?php endif ?>
                    </a>
                    <?php if ($isShop): ?>
                        <div class="nav-panel">
                            <div class="shell nav-panel__inner nav-panel__inner--shop">
                                <div>
                                    <h6>Shop by category</h6>
                                    <ul class="nav-list">
                                        <li><a href="<?= site_url('shop') ?>"><strong>All products</strong></a></li>
                                        <?php foreach ($nav['categories'] as $c): ?>
                                            <li><a href="<?= site_url('product-category/' . $c['slug']) ?>"><strong><?= esc($c['name']) ?></strong></a></li>
                                        <?php endforeach ?>
                                    </ul>
                                </div>
                                <div>
                                    <h6>Featured</h6>
                                    <div class="nav-products">
                                        <?php foreach ($nav['products'] as $p): ?>
                                            <a class="nav-product" href="<?= site_url('product/' . $p['slug']) ?>">
                                                <div class="nav-product__img"><img src="<?= media_url($p['image'], 'sm') ?>" alt="<?= esc($p['name'], 'attr') ?>" loading="lazy" width="300" height="300"></div>
                                                <strong><?= esc($p['name']) ?></strong>
                                                <span><?= strip_tags(product_price_html($p)) ?></span>
                                            </a>
                                        <?php endforeach ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php elseif ($hasKids):
                        $groups = [];
                        foreach ($item['children'] as $c) { $groups[$c['group_label'] ?: $item['label']][] = $c; }
                    ?>
                        <div class="nav-panel">
                            <div class="shell nav-panel__inner nav-panel__inner--cols">
                                <?php foreach ($groups as $label => $children): ?>
                                    <div>
                                        <h6><?= esc($label) ?></h6>
                                        <ul class="nav-list">
                                            <?php foreach ($children as $c): ?>
                                                <li><a href="<?= menu_href($c['url']) ?>" <?= $c['open_new_tab'] ? 'target="_blank" rel="noopener"' : '' ?>>
                                                    <strong><?= esc($c['label']) ?></strong><?php if ($c['description']): ?><span><?= esc($c['description']) ?></span><?php endif ?>
                                                </a></li>
                                            <?php endforeach ?>
                                        </ul>
                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    <?php endif ?>
                </li>
                <?php endforeach ?>
            </ul>
        </nav>

        <div class="header-tools">
            <button class="tool-btn" type="button" id="searchToggle" aria-label="Search" aria-expanded="false" aria-controls="searchPanel"><i class="bi bi-search"></i></button>
            <a class="tool-btn tool-btn--account" href="<?= site_url($customer ? 'my-account' : 'my-account/login') ?>" aria-label="<?= $customer ? 'My account' : 'Sign in' ?>" title="<?= $customer ? esc('Hi, ' . $customer['first_name'], 'attr') : 'Sign in' ?>"><i class="bi bi-person<?= $customer ? '-check' : '' ?>"></i></a>
            <a class="tool-btn" href="<?= site_url('wishlist') ?>" aria-label="Wishlist"><i class="bi bi-heart"></i><span class="tool-btn__count" data-wishlist-count data-count="<?= (int) $wishlistCount ?>"><?= (int) $wishlistCount ?></span></a>
            <button class="tool-btn" type="button" data-cart-open aria-label="Open cart"><i class="bi bi-bag"></i><span class="tool-btn__count" data-cart-count data-count="<?= (int) $cartCount ?>"><?= (int) $cartCount ?></span></button>
        </div>
    </div>

    <div class="search-panel" id="searchPanel">
        <div class="shell">
            <div class="search-panel__inner">
                <form class="search-form" action="<?= site_url('shop') ?>" method="get" role="search">
                    <label class="visually-hidden" for="siteSearch">Search products</label>
                    <input type="search" id="siteSearch" name="q" placeholder="Search sugar, atta, honey…" autocomplete="off" maxlength="60">
                    <button type="submit" aria-label="Search"><i class="bi bi-arrow-right"></i></button>
                </form>
                <div class="search-results" id="searchResults"></div>
                <p class="search-hint">Popular: <a href="<?= site_url('shop') ?>?q=sugar">Sugar</a> · <a href="<?= site_url('shop') ?>?q=atta">Atta</a> · <a href="<?= site_url('shop') ?>?q=honey">Honey</a> · <a href="<?= site_url('shop') ?>?q=jaggery">Jaggery</a></p>
            </div>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-start mobile-nav" tabindex="-1" id="mobileNav" aria-label="Menu">
    <div class="offcanvas-header">
        <img src="<?= media_url(setting('logo'), 'original') ?>" alt="<?= esc(setting('site_name', 'Sugarlif'), 'attr') ?>" height="36">
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close menu"></button>
    </div>
    <div class="offcanvas-body">
        <?php foreach ($menu as $i => $item):
            $isShop = rtrim($item['url'], '/') === '/shop';
        ?>
            <div class="mobile-nav__item">
                <?php if ($isShop || $item['children']): ?>
                    <button class="mobile-nav__link" type="button" data-bs-toggle="collapse" data-bs-target="#mnav<?= $i ?>" aria-expanded="false"><?= esc($item['label']) ?> <i class="bi bi-plus-lg"></i></button>
                    <ul class="mobile-nav__sub collapse" id="mnav<?= $i ?>">
                        <?php if ($isShop): ?>
                            <li><a href="<?= site_url('shop') ?>">All products</a></li>
                            <?php foreach ($nav['categories'] as $c): ?><li><a href="<?= site_url('product-category/' . $c['slug']) ?>"><?= esc($c['name']) ?></a></li><?php endforeach ?>
                        <?php else: $last = null; foreach ($item['children'] as $c): ?>
                            <?php if ($c['group_label'] && $c['group_label'] !== $last): $last = $c['group_label']; ?><li class="mobile-nav__group"><?= esc($last) ?></li><?php endif ?>
                            <li><a href="<?= menu_href($c['url']) ?>"><?= esc($c['label']) ?></a></li>
                        <?php endforeach; endif ?>
                    </ul>
                <?php else: ?>
                    <a class="mobile-nav__link" href="<?= menu_href($item['url']) ?>"><?= esc($item['label']) ?></a>
                <?php endif ?>
            </div>
        <?php endforeach ?>
        <div class="d-grid gap-2 mt-4">
            <a class="btn btn-forest" href="<?= site_url($customer ? 'my-account' : 'my-account/login') ?>"><i class="bi bi-person"></i> <?= $customer ? 'My account' : 'Sign in / Register' ?></a>
            <a class="btn btn-line" href="<?= site_url('faq') ?>">FAQ</a>
        </div>
        <p class="muted small mt-4 mb-0"><i class="bi bi-telephone me-1"></i> <?= esc(setting('contact_phone')) ?><br><i class="bi bi-envelope me-1"></i> <?= esc(setting('contact_email_primary')) ?></p>
    </div>
</div>
