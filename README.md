# Sugarlif / Natural Life — website redevelopment

A complete rebuild of [natural-life.in](https://natural-life.in/) on **CodeIgniter 4** and **MySQL**, with a new visual identity, a database-driven storefront and a purpose-built admin panel. The original site is the source of all content, products and business rules — not of the design.

## Quick start (XAMPP)

```
copy .env.example .env          (then set the database and admin.default* values)
php spark key:generate
php spark migrate
php spark db:seed DatabaseSeeder
php spark serve --port 8080
```
Website: http://localhost:8080/ · Admin: http://localhost:8080/admin

Full instructions, including XAMPP's `intl` extension and Apache virtual host: **[docs/SETUP.md](docs/SETUP.md)**.

## What is included

| Area | Details |
|---|---|
| Storefront | Home (17 art-directed sections), shop with filters / sort / search / quick view, product pages with live pack-size pricing, cart drawer, coupons, checkout (guest or account), order tracking page |
| Accounts | Register, sign in, password reset, dashboard, orders, addresses, profile, wishlist |
| Content | 15 CMS pages on 12 dedicated layouts, blog, FAQ, testimonials, team, certifications |
| Admin | Dashboard with charts; products, variations, gallery, categories, coupons; orders and customers; pages and sections, home banners, navigation, blog; enquiries and newsletter; media library; settings, SEO, redirects; admin users with three roles |
| Commerce rules | ₹40 shipping, free from ₹500 (verified on the original store); CCAvenue payment integration ready for credentials |
| SEO | Editable titles and descriptions, canonical URLs, Open Graph, JSON-LD (Organization, Product, Breadcrumb, FAQ, BlogPosting), XML sitemap, robots.txt, 301 redirect map |
| Security | CSRF on every form, validation, escaped output, bcrypt password hashing, role-based admin, login throttling, verified uploads, secrets in `.env` |

## Documentation

| Document | Purpose |
|---|---|
| [docs/SETUP.md](docs/SETUP.md) | Installation, database, XAMPP, admin login, go-live checklist |
| [docs/TESTING-REPORT.md](docs/TESTING-REPORT.md) | What was tested, results, and what was not tested |
| [docs/MISSING-CONFIGURATION.md](docs/MISSING-CONFIGURATION.md) | Credentials and settings still needed from the client |
| [docs/redirect-map.md](docs/redirect-map.md) | Original URL → new URL |
| [docs/analysis/](docs/analysis/) | The eleven analysis and planning reports written before development |
| [docs/source-crawl/](docs/source-crawl/) | Raw crawl of the original site, extracted text, original images and the scripts that produced the migration dataset |

## Project layout

```
app/Config           Routes, filters, security settings
app/Controllers      Storefront controllers; Admin/ holds the admin panel
app/Database         Migrations (38 tables) and seeders (+ Seeds/data/source.json)
app/Filters          AdminAuth, CustomerAuth, Redirects, SiteSecurity
app/Libraries        CartService, ShippingCalculator, OrderService, CcavenueGateway, Mailer, MediaLibrary, WishlistService
app/Models           One model per table
app/Views            layouts/, partials/, home/, shop/, pages/, blog/, cart/, checkout/, account/, emails/, admin/
public/assets        css/site.css, css/admin.css, js/site.js, js/admin.js, vendor/
public/uploads/media original/ (untouched uploads) and lg/ md/ sm/ (generated WebP)
docs/                Reports, setup guide, test tools
```
