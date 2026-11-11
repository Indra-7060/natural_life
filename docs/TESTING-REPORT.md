# Final testing report

Tested on 2026-11-11 · Windows 10 · PHP 8.3.33 · MariaDB 10.4.32 (XAMPP) · CodeIgniter 4.7.6 · `php spark serve` on `http://localhost:8080`.

Only things that were actually run are marked as tested. Anything that could not be exercised is listed under **Not tested**.

## 1. Summary

| Suite | How | Result |
|---|---|---|
| Public URL smoke test | `docs/tools/smoke.sh` — 48 URLs requested, status and PHP-error scan | **48 / 48 as expected** (41 × 200, 3 × 301, 3 × 302 guard redirects, 1 × intended 404) |
| Admin screen smoke test | `docs/tools/admin-smoke.sh` — signed in, 51 admin screens opened | **51 / 51** returned 200 with no errors |
| End-to-end functional test | `docs/tools/functional-test.php` — real HTTP requests with sessions and CSRF | **127 / 127 checks passed** |
| Content verification | Database compared field-by-field with the crawl dataset | **40 / 40 match**, 0 mismatches |
| PHP lint | `php -l` on every file under `app/` | No syntax errors |
| Browser checks | In-app browser at 1440 px and 375 px | See §4 |

The first functional run found one real defect (below) and several mistakes in the test script itself; both were fixed and the suite re-run clean.

## 2. Defects found and fixed during testing

| # | Defect | Fix |
|---|---|---|
| 1 | Every cart change failed with "There is no data to update" (the cart timestamp was being saved through a model that did not allow that column). | `CartService::touch()` now updates the row directly. |
| 2 | The framework's stock `public/robots.txt` was being served instead of the generated one. | Static file removed; `/robots.txt` is now generated. |
| 3 | Legacy multi-segment URLs (`/category/blog`, `/product-tag/suger`) returned 404 instead of redirecting. | Added a catch-all route so the redirect map is consulted. |
| 4 | Searching "jag" listed Honey before Jaggery (category-name match outranked the product-name match). | Product-name matches are now ranked first. |
| 5 | Header icons wrapped onto a second row below 1200 px. | Header grid corrected. |

## 3. What the functional test covers (127 checks)

**Catalogue (11)** — home page and structured data; shop lists all 7 products; sale badge (−13 % on the sachet pack); out-of-stock badge; search and ranking; price filter; in-stock filter; sort; category page; live search suggestions.

**Product detail and variations (5)** — product structured data; all 7 sugar pack sizes offered; pack prices equal the source (1 kg ₹250 … 15 kg ₹3,250); nutrition and ingredients shown; quick view returns both atta packs.

**Cart (13)** — variable product refused without a pack; correct pack price charged; out-of-stock product refused; unknown variation refused; remove; ₹40 shipping below ₹500; free-shipping meter; shipping free at ₹520; quantity capped at 20; invalid coupon rejected; percentage coupon maths; cart page renders; POST without CSRF token blocked.

**Checkout (9)** — page loads; validation rejects bad input; order created; totals recalculated on the server; new order is *pending / unpaid*; cart emptied; payment page states nothing was charged when the gateway is not configured; order page needs the access key; confirmation e-mail produced.

**CCAvenue callback (6)** — encrypted request to the test endpoint with the key never exposed; undecryptable callback rejected; tampered amount does **not** mark the order paid; failure sets *failed*; a valid matching callback marks *paid* and *processing*; every response logged with its verified flag. *(Run with a dummy working key — see Not tested.)*

**Customer account (20)** — guest redirected to sign-in; weak password refused; password stored hashed; dashboard; wishlist add and list; address book with default; checkout pre-filled; order linked to the customer; order history and detail; another customer's order returns 404; wrong password refused; login; reset token stored hashed; reset form; reset link single-use; login with the new password.

**Forms (6)** — contact validation; enquiry stored; honeypot drops bot submissions; newsletter validation; subscription stored; no duplicate rows.

**SEO and redirects (8)** — sitemap contents; robots.txt; `/products` → `/shop`; old blog URL → `/blog/…`; old tag URL; FAQ structured data with 12 questions; cart is `noindex`; unknown URL is 404.

**Admin (49)** — sign-in guard; wrong password refused; login; forced password change for the seeded account; dashboard; FAQ create / validate / edit / toggle / delete and their effect on the public page; rich text sanitised on save; product create with pack sizes and specifications; derived price range; product live on the storefront; image upload keeps the original and generates WebP sizes; a disguised non-image upload is rejected; in-use media cannot be deleted; editing variations; hiding and soft-deleting a product; unpaid order cannot be shipped; offline payment recording; shipping and tracking; status history; customer notification e-mail; tracking visible to the customer; order and customer filters; home-section edit changes the live page; section list rows; shipping settings change the cart calculation (then restored to ₹40 / ₹500); reserved URL refused for new pages; new CMS page published; structural pages protected from deletion; redirect added in admin works immediately; newsletter CSV export; enquiry content escaped and marked read; Content Editor role can open Pages but is blocked from Orders, Users and Settings; sign-out.

The test creates its own customer, orders, product, coupon and redirect and deletes them afterwards. The database was left with 0 orders and 0 customers.

## 4. Browser checks (manual, in-app browser)

| Check | Result |
|---|---|
| Home, shop, product, Our Profile at 1440 × 900 | Rendered correctly; no console errors |
| Home and shop at 375 × 812 (mobile) | Rendered correctly; no horizontal scrolling |
| Selecting the 5 kg pack on Low GI Sugar | Price changed to ₹1,200, "₹240 per kg" shown, hidden variation id updated |
| Add to cart | Cart drawer opened with "Low GI Sugar · 5kg · ₹1,200", free-shipping message, badge count 1 |
| Remove from the drawer | Cart count returned to 0 |
| Hero slideshow | Rotates; pause control present |

### Admin panel (visual review, signed in with a temporary account that was removed afterwards)

| Check | Result |
|---|---|
| Sign-in page, dashboard (KPI cards, sales and status charts), products list, product editor, order detail, media library, settings, home-page sections at 1440 × 900 | Rendered correctly; no console errors |
| Products list at 375 × 812 | No horizontal scrolling; sidebar hidden and opens from the menu button |
| Product editor: add / remove a pack-size row; switch between "Single pack" and "Several pack sizes" | Rows added and removed; the right price fields show for each type |
| Rich-text editor (FAQ answer): bold, heading, back to paragraph | Each command applied and synced to the saved field; content unchanged after undoing |
| Image picker: open, filter by folder, choose, remove | 41 images listed; folder filter returned 5 team photos; chosen image previewed; remove cleared it |
| Image picker "Upload new" | Uploaded a generated PNG, which was stored, selected and previewed (then deleted) |
| Section list editor: move row down / up, add row | Order changed and restored; new row added with correct field names |
| Order screen with a real test order (Honey, ₹600) | Items, totals, payment panel, status form, shipping form, customer panel and history displayed; order deleted afterwards |

| **All remaining admin screens** — 53 screens (categories, coupons, banners, pages, navigation and menu items, FAQs, testimonials, team, certifications, posts, blog categories, enquiries, newsletter, customers, orders, SEO, redirects, admin users, search, profile, every settings tab; list, add and edit views) each rendered at 1440 px and 390 px and checked for errors, sideways overflow, unlabelled fields and unnamed buttons | 105 of 106 renders clean. One defect: the Navigation table overflowed at phone width — fixed and re-checked. Six of these screens were also reviewed by eye. |
| Rich-text **link** button: internal link, external link, remove link, cancel | Each produced the expected link or no change. A `javascript:` address is refused in the editor and neutralised again on the server. |
| **Paste** of formatted content (Word-style markup with inline styles, a script tag and an image with an event handler) | Only the plain text was inserted, as clean paragraphs; no styles, scripts or images came through. |

Defects found in this review and fixed: the mobile menu button was showing on desktop; both scripts looked for the security token under the wrong meta-tag name (they worked only because every page also carries the token in a form — now they read both); the empty sales chart showed fractional rupee ticks.

### Homepage redesign and motion (second design pass)

Changes: the hero is now one wide banner with the headline over the open side of the photograph (photo above text on phones) and previous / next / pause buttons; the featured block was rebuilt because its large tile overlapped the product cards; product cards, benefits and the comparison became soft panels; scroll-reveal animations run in both directions; images zoom gently while scrolling and on hover; a scroll-progress bar and back-to-top button were added.

| Check | Result |
|---|---|
| Adjacent homepage sections overlapping, at 1536 px and 375 px | None (measured for all 13 sections) |
| Featured tile vs product cards | No overlap |
| Slider previous / next / pause; picture and headline in step | Working |
| Reveal on scrolling down, reset after leaving, replay on scrolling back up | Working |
| Image zoom value changes while scrolling | Working (1.13 to 1.07 across the story image) |
| Any animated element still invisible while on screen | 0 |
| Sideways scrolling at 1536 px and 375 px | None |
| Functional suite and smoke test after the redesign | 127 / 127 and 48 / 48 |

Not verified: the feel of the animations at full frame rate. The review browser pauses animation while its pane is in the background, so timing and smoothness were checked by logic and measurements rather than by watching them play. Inner pages reuse the same reveal classes; only Home and Our Profile were re-checked after this change.

### Inner pages — same design and motion (third design pass)

Changes: every inner page header is now a rounded forest banner with a staggered entrance; the scroll-reveal, staggered lists, hover effects and scroll zoom from the home page are applied automatically to all inner-page blocks (story chapters, team, certifications, education pages, product stories, FAQ, contact, legal, blog, shop, product, cart, account).

| Check | Result |
|---|---|
| 25 inner pages, each at 1536 px and 390 px (50 renders): sections overlapping, sideways scrolling, animated content left invisible while on screen, header text colour, PHP errors | 49 clean on the first run. One defect: a long reference link on the diet-comparison page pushed the page sideways on a phone — fixed and re-checked (also re-checked refund policy, sugar story, product and checkout pages at phone width). |
| Product page after the change | Pack selection still updates the price; gallery stays pinned while scrolling |
| Functional suite and smoke test | 127 / 127 and 48 / 48 |

Not verified: as before, the animations were checked by measurement, not watched at full frame rate. Signed-in pages (account dashboard, orders, addresses, checkout with items) use the same classes but were not included in this 50-render audit.

### Signed-in account pages and checkout (design check)

Checked with a temporary customer (one saved address, a wishlist item, two products in the cart) and a real test order given tracking details; all of it was removed afterwards.

| Check | Result |
|---|---|
| Dashboard, orders, addresses, address edit, profile, wishlist, cart with items, checkout — at 1536 px and 390 px (16 renders): overlaps, sideways scrolling, hidden content, clipped panels, unlabelled fields, errors | 16 / 16 clean |
| Order confirmation, order tracking page, account order detail, orders list, dashboard with an order, emptied cart — at both widths (12 renders) | 12 / 12 clean; tracking number, status and totals shown |
| Checkout form | Saved address pre-fills name, address, city, state, PIN, e-mail and phone; billing fields appear when the same-as-shipping box is unticked and hide again when ticked; order summary stays pinned |
| Placing the order from the browser | Order created (Honey + 2 Jaggery, total 860, free shipping) and the not-charged notice shown |
| Seen by eye, missed by the measurements | On a phone the My orders table was squeezing until the order number and status broke across lines. Fixed: table text stays on one line and the table scrolls inside its panel. |
| Functional suite and smoke test afterwards | 127 / 127 and 48 / 48 |

### Admin panel — same design language

Changes: page headings are forest banners with the leaf motif; panels, cards and tables are softly rounded with shadows; buttons are pills; the sidebar has a gradient and an active marker; the sales card is a dark accent card; the sign-in page has a banner side and a card form. Motion: sidebar and page blocks rise in on load, blocks below the fold reveal the first time they scroll into view, and cards, rows, thumbnails and buttons respond on hover. The scroll animation is two-way, as on the storefront: a block resets once it has fully left the screen and animates again from whichever side it returns. A block is never hidden while it holds keyboard focus or while a dialog is open.

| Check | Result |
|---|---|
| 43 admin screens at 1536 px and 390 px: banner present and readable, content spilling out of the banner, sideways scrolling, error text | Clean after one fix: the product editor pack-size rows overflowed on a phone — they now stack in two columns |
| Scroll reveal on 10 long screens at both widths | 113 held-back blocks all revealed on scroll; none left invisible while on screen |
| Seen by eye (animations switched off for the screenshot): sign-in, dashboard, products list, product editor at desktop; dashboard and product editor at phone width | Correct |
| Admin smoke test (51 screens), functional suite, public smoke test | 52 / 52 responses OK, 127 / 127, 48 / 48 |

| Two-way reveal on the product editor: appears on entering, resets after leaving, replays on scrolling back up, focused block stays visible, link to #gallery lands on a visible block | All working |
| 14 admin screens at 1536 px and 390 px, stepped down and back up: any block invisible while on screen, top blocks restored, sideways scrolling, save bar still pinned | Clean |
| Image picker dialog opened from a form | Form stays visible behind the dialog |

Not verified: entrance and hover animations playing at full frame rate (the review browser throttles them in the background). The save bar staying pinned was confirmed on the generic edit form only.

### Home page: later design changes

Changes made after the first redesign, each at the owner's request: hero play / pause, arrows and slide numbers removed (progress lines only); hero slides now move sideways and advance on their own every 6.5 s; category rows keep their hover thumbnail inside the row; story image in a 440 px square frame; benefits block stacked full width; journal rebuilt as one panel (forest "Latest article" side, numbered "More to read" list).

| Check | Result |
|---|---|
| Journal panel at 1536 px: both sides equal height, text beside the photo and not over it, two list rows, duplicate "All articles" link in the heading hidden, sideways scrolling | Clean |
| Journal panel at 375 px: stacked, photo above text, sideways scrolling | Clean |
| Functional suite and public smoke test after the change | 127 / 127, 48 / 48 |

Not verified: the hero auto-advance timing, hover effects and reveal animations at full frame rate (the review browser throttles them in the background). The hero, category, story and benefits changes were checked by measurement and screenshot with animations switched off.

## 5. Content verification against natural-life.in

| Item | Source | New site |
|---|---|---|
| Products | 7 | 7 — names, price ranges, sale price, stock status and image counts all match |
| Pack-size variations | 9 | 9 — labels, prices and stock match |
| Product images | 23 | 23, original files, none missing on disk |
| Blog posts | 6 | 6 — titles and body text identical |
| FAQs | 12 | 12 |
| Testimonials | 6 | 6 — text identical, including the Hindi review |
| Team members | 5 | 5 |
| Content pages | 22 with content | 15 CMS pages + home, shop, blog, cart, checkout, account, wishlist routes. `/home2121` and `/demo` (abandoned drafts) intentionally not migrated and redirected to `/` |
| Changed URLs | 12 | 12 redirects, all 301 |
| Shipping rule | ₹40 flat, free from ₹500 | Same, verified by cart tests |
| Payment method | CCAvenue only | CCAvenue only |

## 6. Not tested

| Item | Why |
|---|---|
| A real CCAvenue transaction | Needs the client's merchant credentials. The request/response handling was tested with a dummy key only. |
| Real e-mail delivery | No SMTP credentials. E-mails were generated and inspected as files, not delivered to a mailbox. |
| Running under XAMPP **Apache** | Tested with `php spark serve` only. The virtual-host configuration in `SETUP.md` is the standard CodeIgniter one but was not exercised on this machine (XAMPP's PHP there has `intl` disabled). |
| Browsers other than the in-app Chromium browser | Safari, Firefox and real phones not checked. |
| Inner content pages at mobile width | Only home and shop were checked at 375 px. |
| Load / performance testing, Lighthouse scores | Not run. |
| Accessibility audit with assistive technology | Semantic markup, labels, focus styles and reduced-motion support are in place but no screen-reader pass was done. |

## 7. Known limitations

- Product reviews are not implemented (the original had the feature enabled with zero reviews).
- Stock quantities were not available from the original site; products use in-stock / out-of-stock status until quantities are entered.
- Fonts load from Google Fonts; without internet access the site falls back to system serif / sans-serif fonts.
- Some short interface labels (button text such as "Add to cart", form labels) live in the view files rather than the database.
- `docs/tools/functional-test.php` is a development tool and must not be run against a live site.
