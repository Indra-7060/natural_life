# 10 · Proposed redesigned website structure

## Sitemap

```
/                                   Home (art-directed landing page)
│
├─ /shop                            Catalogue: filters (category, price, availability), sort, search, pagination, quick view
│   ├─ /product-category/{slug}     Category listing
│   └─ /product/{slug}              Product detail (gallery, variations, specs, ingredients, nutrition, shipping, related)
│
├─ About Us
│   ├─ /about-naturalife            Our Profile — long-form editorial story + journey timeline + USPs
│   ├─ /team                        Team — leadership portraits
│   └─ /certification               Certifications — validation bodies + request-a-report
│
├─ Low GI Education
│   ├─ /glycemic-index              GI explained — scale visual, GI vs GL
│   ├─ /health-benefits-of-low-gi   Benefits — numbered benefit index, mechanism
│   ├─ /low-gi-diet-vs-other-diet   Study comparison — data-led layout
│   ├─ /low-gi-diet-sugar           Product story — sugar (comparison tables)
│   ├─ /sugarlif-low-gi-whole-wheat-atta   Product story — atta
│   ├─ /sugarlif-low-gi-rice-atta   Product story — rice
│   └─ /other-applications          Applications
│
├─ /blog                            Journal listing (featured + list)
│   └─ /blog/{slug}                 Article
│
├─ /faq                             FAQ (searchable accordion)
├─ /contact-us                      Contact (details + enquiry form)
│
├─ Commerce
│   ├─ /cart
│   ├─ /checkout  →  /checkout/payment/{order}  →  /order/{number}/thank-you
│   ├─ /wishlist
│   └─ /my-account                  login · register · forgot/reset password · dashboard · orders · order detail · addresses · profile
│
├─ Legal:  /privacy-policy · /refund_returns · /terms-and-conditions
├─ SEO:    /sitemap.xml · /robots.txt
└─ /admin                           CMS (separate layout, role-protected)
```

## Homepage section order (new)

| # | Section | Layout treatment | Content source |
|---|---|---|---|
| 1 | Announcement bar | Slim forest-green strip | Free-shipping rule (verified) |
| 2 | Header | Logo left, centred nav with mega menu, icon utilities right | Navigation (DB) |
| 3 | Hero | Editorial split: oversized serif headline + rotating product-banner frame, proof strip beneath | 3 source banner headlines + banner artwork |
| 4 | Proof strip | 4 typographic facts in one ruled row | About / product copy |
| 5 | Featured collection | Asymmetric grid: one large feature tile + product cards | Products flagged *featured* |
| 6 | Shop by category | Horizontal ruled list with hover image | Categories |
| 7 | Our Story | Split layout: image with offset frame + pull quote ("Can you make my son eat sweets again?") | About, chapter 1 |
| 8 | What makes Sugarlif different | Typography-led numbered statements on dark green | 4 USPs |
| 9 | Low GI education | Interactive GI scale (Low / Moderate / High) + two definition panels | Home + Glycemic Index copy |
| 10 | Benefits vs High GI | Two-column comparison ledger | Home comparison (8 + 8 rows) |
| 11 | Research & certification | Full-width band naming the validation bodies | About, chapter 5 |
| 12 | Visual banner | Full-bleed product-range artwork with caption | Source banners |
| 13 | Bestsellers | Product row | Products flagged *bestseller* |
| 14 | Testimonials | Large-quote editorial slider | 6 source testimonials |
| 15 | Journal | 1 lead article + 2 text links | 3 latest posts |
| 16 | FAQ teaser | 4 questions accordion + link | FAQs flagged for home |
| 17 | Newsletter | Cream panel, single field | Footer copy |
| 18 | Footer | 5 columns + marketplaces strip + legal bar | Footer + settings |

## What changes versus the source

| Area | Source | New |
|---|---|---|
| Visual language | Stock Astra/Elementor theme, bright green + orange, system font, boxed banners | Deep forest green / sage / cream / ivory, earthy accents, Fraunces serif headings + DM Sans body, generous whitespace, ruled editorial grid |
| Home | Slider → product grid → repeated icon boxes | 18 distinct, differently composed sections (above) |
| Navigation | 6 top-level items, three small dropdowns | 6 items with a shop mega menu and a grouped education menu; rice page no longer orphaned |
| Shop | Unfiltered grid | Filters, sort, search, quick view, sale / out-of-stock badges |
| Product page | Tabs | Sticky buy panel, pack-size chips with live price, accordion details, shipping facts |
| Inner pages | One hero-banner template everywhere | Dedicated template per page type (story, team, certification, education ×3, product story, applications, FAQ, contact, legal) |
| Admin | WordPress | Purpose-built CMS, role-based |
