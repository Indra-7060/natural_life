# 07 · Existing functionality inventory

Verified on the live site on 2026-11-11 (page HTML, loaded plugin assets, and an anonymous cart session against the public WooCommerce Store API — no order was placed).

| # | Function | How the source does it | Verified facts | New implementation |
|---|---|---|---|---|
| 1 | Product catalogue | WooCommerce archive `/products/` | 7 products, default sort, no filters | `/shop` — category + price filters, sort, search, pagination |
| 2 | Product detail | WooCommerce single product, tabs: Description / Additional information / Reviews (0) | Gallery, short description, variations dropdown + swatches (`woo-variation-swatches`) | `/product/{slug}` — gallery, variation chips with live price, specs, ingredients, nutrition, shipping, related |
| 3 | Variable products | Attribute "Pack Weight" | Low GI Sugar: 7 packs (1–6 kg, 15 kg); Atta: 2 packs (3 kg, 5 kg) | `product_variations` |
| 4 | Product search | `ajax-search-for-woocommerce` in the header | Live suggestions | Header search with live suggestions + `/shop?q=` |
| 5 | Cart | WooCommerce cart + Elementor mini-cart | Quantity update, remove, totals | Session + DB cart, AJAX mini-cart drawer, `/cart` |
| 6 | Shipping | WooCommerce zones + `conditional-shipping-for-woocommerce` | **Flat rate ₹40** when subtotal < ₹500; **Free shipping** when subtotal ≥ ₹500 (tested: ₹130 cart → ₹40; ₹730 cart → ₹0; same result for Pune and Delhi) | Same rule, both numbers editable in Admin → Settings |
| 7 | Tax | — | No tax line; prices are final | Prices treated as tax-inclusive |
| 8 | Checkout | WooCommerce checkout (requires account or guest details) | Single payment method | `/checkout` — guest or logged-in |
| 9 | Payment | **CCAvenue** (`payment_method_ccavenue`) — the only gateway offered. No COD. | Gateway identified; merchant credentials are private | CCAvenue integration (AES request/response), keys in `.env`; orders stay *pending payment* until a verified gateway response |
| 10 | Coupons | WooCommerce coupons enabled (coupon field on cart) | No public coupon codes | `coupons` module (none seeded) |
| 11 | Customer accounts | `easy-login-woocommerce` pop-up: login, sign-up (e-mail, first name, last name, password, accept terms), lost password | — | Register / login / logout / forgot + reset password, dashboard, orders, addresses, profile |
| 12 | Wishlist | `smart-wishlist-for-more-convert` at `/wishlist/` | Works for guests | Wishlist for guests (session) and customers (DB) |
| 13 | Order tracking | `wc-shipment-tracking` / Advanced Shipment Tracking REST namespaces present | Policy text promises a tracking number | Courier + tracking number on the order, shown to the customer |
| 14 | Product reviews | WooCommerce reviews enabled | 0 reviews on every product | Not rebuilt as a public form (nothing to migrate); customer testimonials module covers social proof. Documented as an optional later addition. |
| 15 | Contact form | Elementor form: First name, Last name, Email, Phone No., Subject, Message | — | Same fields, stored in `contact_enquiries`, e-mailed to admin |
| 16 | Newsletter | Elementor form in the footer (e-mail only) | — | `newsletter_subscribers`, CSV export in admin |
| 17 | Blog | WordPress posts, one category, comments open (none) | — | Blog listing + detail, categories; comments not rebuilt (unused) |
| 18 | E-mail notifications | WooCommerce transactional mail | — | Order confirmation, status update, enquiry notification, password reset — SMTP via `.env` |
| 19 | Caching / security | LiteSpeed Cache, Wordfence | — | CI4 page/query caching hooks, CSRF, role-based admin |
| 20 | Marketplace links | Footer icons | Flipkart, Amazon, JioMart, 1mg | "Also available on" strip, editable |

## Not present on the source (and therefore not invented)
Cash on delivery · GST/tax breakdown · pin-code serviceability check · product reviews content · social-media links · certificate downloads · loyalty/referral features.
