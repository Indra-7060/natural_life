# 01 · Website page inventory

Source: `https://natural-life.in/` (WordPress + WooCommerce + Elementor Pro, Astra theme), crawled 2026-11-11 via the public sitemap (`/wp-sitemap.xml`), the WordPress REST API and a full HTML download of every URL. Raw files: `docs/source-crawl/raw/`.

## Content pages (24 in sitemap)

| # | Title on source | Original URL | Type | Has content | New URL | Notes |
|---|---|---|---|---|---|---|
| 1 | Home | `/` | Landing | Yes | `/` | Slider (3 banners), product grid, Low GI intro, benefits, comparison, FAQ excerpt, testimonials |
| 2 | Our Profile | `/about-naturalife/` | Company | Yes | `/about-naturalife` (kept) | "Our Story" (6 chapters), "The Sugarlif Journey", 4 USPs |
| 3 | Team | `/team/` | Company | Yes | `/team` (kept) | 5 people: 2 key people, 3 core members |
| 4 | Certification | `/certification/` | Company | Minimal | `/certification` (kept) | Only one sentence: certificates on request by e-mail. **No certificate images published.** |
| 5 | Glycemic Index | `/glycemic-index/` | Education | Yes | `/glycemic-index` (kept) | GI definition, classification, GI vs GL |
| 6 | Health Benefits of Low GI | `/health-benefits-of-low-gi/` | Education | Yes | `/health-benefits-of-low-gi` (kept) | 12 benefits, high vs low GI, mechanism |
| 7 | Low GI Diet Vs Other Diet | `/low-gi-diet-vs-other-diet/` | Education | Yes | `/low-gi-diet-vs-other-diet` (kept) | Keto study results + BMC Nutrition reference |
| 8 | Low GI Diet Sugar | `/low-gi-diet-sugar/` | Product story | Yes | `/low-gi-diet-sugar` (kept) | What it is, benefits, 2 comparison tables |
| 9 | Sugarlif® – Low Gi Whole Wheat Atta | `/sugarlif-low-gi-whole-wheat-atta/` | Product story | Yes | `/sugarlif-low-gi-whole-wheat-atta` (kept) | GI value 36, 7 benefits |
| 10 | Sugarlif® – Low GI Rice | `/sugarlif-low-gi-rice-atta/` | Product story | Yes | `/sugarlif-low-gi-rice-atta` (kept) | GI 40, 5 rice varieties, 8 benefits. Not in the main menu on source. No rice product in the shop. |
| 11 | Other Applications | `/other-applications/` | Product story | Yes | `/other-applications` (kept) | 4 application statements |
| 12 | FAQ | `/faq/` | Support | Yes | `/faq` (kept) | 12 questions |
| 13 | Contact Us | `/contact-us/` | Support | Yes | `/contact-us` (kept) | 2 e-mails, phone, office address, enquiry form |
| 14 | Blog | `/blog/` | Blog listing | Yes | `/blog` (kept) | 6 posts, single category "Blog" |
| 15 | Products | `/products/` | Shop | Yes | `/shop` (301 from `/products`) | WooCommerce archive, 7 products |
| 16 | Cart | `/cart/` | Commerce | Yes | `/cart` (kept) | |
| 17 | Checkout | `/checkout/` | Commerce | Yes | `/checkout` (kept) | CCAvenue only |
| 18 | My account | `/my-account/` | Commerce | Yes | `/my-account` (kept) | Login / sign-up / lost password |
| 19 | Wishlist | `/wishlist/` | Commerce | Yes | `/wishlist` (kept) | "Smart Wishlist" plugin |
| 20 | Privacy policy | `/privacy-policy/` | Legal | Yes | `/privacy-policy` (kept) | Effective 1 April 2022 |
| 21 | Refund and Returns Policy | `/refund_returns/` | Legal | Yes | `/refund_returns` (kept) | Also contains the Shipping Policy |
| 22 | Terms and conditions | `/terms-and-conditions/` | Legal | **Empty** | `/terms-and-conditions` (kept) | Page exists but has no body text — see missing-information report |
| 23 | home2165161 | `/home2121/` | Abandoned draft | Old home draft | — (not migrated) | Unlinked earlier homepage draft; duplicates the home and inner pages |
| 24 | Demo | `/demo/` | Abandoned draft | Empty | — (not migrated) | Empty test page |

## Blog posts (6)

| Title | Original URL | Published | New URL |
|---|---|---|---|
| If Sugar Had a Passport… | `/if-sugar-had-a-passport/` | 2026-07-07 | `/blog/if-sugar-had-a-passport` |
| The Day Sugar Went to Whistler | `/episode-2-the-day-sugar-went-to-whistler/` | 2026-07-07 | `/blog/episode-2-the-day-sugar-went-to-whistler` |
| Can You Trust a Food Label? | `/episode-3-can-you-trust-a-food-label/` | 2026-07-07 | `/blog/episode-3-can-you-trust-a-food-label` |
| The Secret Life of a Spoonful of Sugar | `/episode-4-the-secret-life-of-a-spoonful-of-sugar/` | 2026-07-07 | `/blog/episode-4-the-secret-life-of-a-spoonful-of-sugar` |
| Sugar on Trial | `/episode-5-sugar-on-trial/` | 2026-07-07 | `/blog/episode-5-sugar-on-trial` |
| Why GI Labs Canada Certification Matters More Than Marketing Claims | `/why-gi-labs-canada-certification-matters-more-than-marketing-claims/` | 2026-07-07 | `/blog/why-gi-labs-canada-certification-matters-more-than-marketing-claims` |

No post has a featured image. Author shown as "adminsugarlif"; comments are open but none exist.

## Product & taxonomy URLs

| Original URL | New URL |
|---|---|
| `/product/{slug}/` × 7 (see `03-product-inventory.md`) | `/product/{slug}` (kept exactly) |
| `/product-category/sugar/` | `/product-category/sugar` (kept) |
| `/product-category/uncategorized/` | 301 → `/shop` |
| `/product-tag/suger/` | 301 → `/shop` (misspelt tag, 3 products) |
| `/category/blog/` | 301 → `/blog` |
| `/feed/`, `/comments/feed/` | not migrated (RSS not required) |

Every original URL that changes has a seeded 301 redirect — see `docs/redirect-map.md`.
