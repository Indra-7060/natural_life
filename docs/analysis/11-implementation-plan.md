# 11 · Implementation plan

## Stack
CodeIgniter 4.7 (PHP 8.2+) · MySQL/MariaDB (XAMPP) · Bootstrap 5.3 grid/utilities + custom design system (`public/assets/css/site.css`) · vanilla JS with `fetch` for AJAX · Chart.js in admin. No WordPress, no other PHP framework.

## Design direction
| Token | Value | Use |
|---|---|---|
| Forest (primary) | `#1F3A2B` | Headers, primary buttons, dark bands |
| Deep forest | `#142A1E` | Footer, announcement bar |
| Sage | `#A9B8A0` / light `#DCE4D5` | Secondary surfaces, tags, rules |
| Cream | `#F3ECDD` | Section backgrounds |
| Ivory | `#FBF8F1` | Page background |
| Clay (accent) | `#B4633A` | Sale badges, links on hover, key accents |
| Ochre (accent) | `#C89B3C` | Stars, small highlights |
| Ink | `#1E241F` / muted `#5E6A60` | Text |

Typography: **Fraunces** (variable serif, headings/quotes) + **DM Sans** (body/UI). Scale: 12 / 14 / 16 / 18 / 22 / 28 / 40 / 56 / 76. Radius 4 px (crisp, editorial); hairline rules instead of card shadows; motion limited to 200 ms fades/slides and respects `prefers-reduced-motion`.

## Folder structure
```
app/
  Config/            Routes, Filters, Sugarlif.php (shop defaults)
  Controllers/       Home, Shop, Product, Cart, Checkout, Payment, Account, Wishlist, Blog, Page, Contact, Newsletter, Seo
  Controllers/Admin/ Auth, Dashboard, Products, Categories, Orders, Customers, Pages, Banners, Menus, Posts, BlogCategories,
                     Faqs, Testimonials, Team, Certifications, Enquiries, Subscribers, Media, Coupons, Seo, Redirects, Settings, Users
  Database/Migrations, Database/Seeds (+ Seeds/data/source.json)
  Filters/           AdminAuth, CustomerAuth, Redirects, SecureHeaders
  Helpers/           site_helper (settings, money, media urls, SEO), admin_helper
  Libraries/         CartService, ShippingCalculator, OrderService, CcavenueGateway, Mailer, MediaLibrary, SeoBuilder
  Models/            one per table
  Views/
    layouts/         site.php, admin.php, email.php
    partials/        header, mega menu, footer, product_card, filters, breadcrumbs, pagination, flash, cart drawer, form fields
    home/, shop/, pages/ (one view per template), blog/, cart/, checkout/, account/, emails/, admin/**
public/
  assets/css|js|img|vendor     uploads/media/{original,lg,md,sm}
docs/                analysis reports, source crawl archive, setup + testing docs
```

## Database (38 tables)
- **Access:** `roles`, `permissions`, `role_permissions`, `admin_users`
- **Customers:** `customers`, `customer_addresses`, `password_resets`
- **Catalogue:** `product_categories`, `products`, `product_images`, `product_variations`, `product_attributes`, `stock_movements`
- **Sales:** `carts`, `cart_items`, `orders`, `order_items`, `order_status_history`, `payments`, `coupons`, `wishlists`
- **Content:** `pages`, `page_sections`, `banners`, `menus`, `menu_items`, `blog_categories`, `blog_posts`, `faqs`, `testimonials`, `team_members`, `certifications`
- **Engagement:** `contact_enquiries`, `newsletter_subscribers`
- **System:** `settings`, `media`, `seo_meta`, `redirects`

Foreign keys with cascade rules on every child table; indexes on slugs, status flags, foreign keys and order lookups. Prices are stored per product (simple) or per variation (variable); stock is a status with optional quantity tracking plus an audit log (`stock_movements`).

## Modules
**Storefront:** home · catalogue (filter/sort/search/paginate/quick view) · product detail with variations · cart (AJAX) · shipping rule · coupons · checkout (guest/customer) · CCAvenue payment · order confirmation · customer auth + account · wishlist · blog · CMS pages (12 templates) · FAQ · contact · newsletter · sitemap/robots/structured data · 301 redirects.

**Admin:** dashboard (KPIs + sales chart) · Catalogue (products, variations, images, attributes, categories, coupons) · Sales (orders, customers) · Content (pages & sections, home banners, navigation, blog, FAQs, testimonials, team, certifications) · Inbox (enquiries, subscribers) · Library (media) · Configuration (settings, SEO, redirects, admin users & roles).

## Business rules carried over (verified)
- Shipping ₹40 flat below ₹500 subtotal, free at ₹500 and above; no tax line.
- CCAvenue is the only payment method. An order is created as *pending payment* and becomes *paid* only on a decrypted, validated gateway response whose order number and amount match.
- Out-of-stock products are visible but cannot be added to the cart.

## Functionality checklist
Tracked to completion in `docs/TESTING-REPORT.md`.
