# 09 · Missing information report

Nothing below was guessed. Each item is either absent from the source site or private to the client, and is needed before go-live.

## A. Needs client input

| # | Item | What was found | What is needed | Where to enter it |
|---|---|---|---|---|
| 1 | **CCAvenue credentials** | Gateway identified as the only payment method | Merchant ID, Access Code, Working Key (and whether the live or test endpoint is used) | `.env` → `ccavenue.*` |
| 2 | **SMTP credentials** | Not discoverable | Host, port, user, password, from-address | `.env` → `email.*` |
| 3 | **Terms & Conditions text** | Page exists on source but is empty | Full terms text | Admin → Pages → Terms and Conditions |
| 4 | **Certificates** | Certification page only says to e-mail info@natural-life.in. No certificate scans published. | Certificate images / PDFs and issue details for GI Labs, Whistler Center, BRC, US FDA etc. | Admin → Certifications |
| 5 | **Refund-policy placeholders** | Source text still contains `{email address}`, `{physical address}`, `{email}` template placeholders, and generic clauses about books, CDs, gift cards | Confirmed policy wording | Admin → Pages → Refund and Returns Policy |
| 6 | **Stock quantities** | Source exposes in-stock / out-of-stock only | Quantities if stock should be tracked numerically | Admin → Products (stock tracking is optional per product) |
| 7 | **Real SKUs** | Source SKUs are placeholders (`1`, `2`, `15`) or blank | Actual SKU codes | Admin → Products |
| 8 | **Product categories** | 5 of 7 products sit in WooCommerce's default "Uncategorized"; only "Sugar" is a real category | Confirmation of the proposed categories: *Sugar* (existing), *Honey & Jaggery*, *Flour (Atta)*, *Indian Sweets* | Admin → Categories |
| 9 | **Team designations & bios** | Vikas D. Dangat and Sureddy V. Reddy have no designation; nobody has a bio | Optional | Admin → Team |
| 10 | **Social-media links** | None on source | Optional | Admin → Settings |
| 11 | **GST number / FSSAI licence** | Not shown on source | Usually required on Indian food e-commerce invoices and footers | Admin → Settings |
| 12 | **Low GI Rice availability** | Page describes 5 rice varieties; no rice product is sold | Whether rice should be listed as a product | Admin → Products |
| 13 | **Sachet pack details** | Product image is a screenshot (543×482); description copied from Low GI Sugar | Proper pack photograph, sachet weight | Admin → Products |
| 14 | **Lifestyle photography** | Source has banner artwork only | Brand photography if available. Until then the redesign uses the client's own banner/product imagery plus original vector illustration — no stock photos or AI packaging were introduced | Admin → Media / Home sections |

## B. Source inconsistencies (migrated as published, flagged for review)

| # | Observation |
|---|---|
| 1 | Certification body is written as "GI Labs, Canada", "GI LAB, Canada" and "Whistler Center for Carbohydrate Research" / "Whistler Carbohydrate Research Centre". |
| 2 | The R&D lead's name is spelt "Nandagopalan" (About), "Nandgopalan" (Team) and "Nandagoplan" (product copy). |
| 3 | The About story says the breakthrough took "ten years"; the Journey section says "4 years". Both are kept in their own sections. |
| 4 | FAQ answer "Can Alcoholics also take Sugarlif sugar?" begins mid-sentence on source ("Sugarlif Sugar to alcoholics, not because…") — the opening words are missing. Published as-is. |
| 5 | FAQ says GI "less than 50"; the Atta page states GI 36, Rice page GI 40; no GI value is published for sugar, honey or jaggery. No value was added. |
| 6 | Glycemic Index page says Low GI is "Below 55" and later "≤55". |
| 7 | WooCommerce "Weight" shipping fields (Atta: 1 kg, Honey: 0.5 kg) and Honey's "Package: Package" attribute are data-entry artefacts and were not shown as specifications. |
| 8 | Product tag is misspelt "Suger". Not migrated as a public tag. |
| 9 | Footer copyright reads "© 2023". New footer prints the current year automatically. |
| 10 | Policy pages refer to "Sugarlif.com" while the site runs on natural-life.in. |
| 11 | Several health statements (e.g. "Reduce breast cancer risk", "Slows down the aging process") are strong claims. They are reproduced only where the client published them, with the client's own "not a medication" FAQ disclaimer shown alongside. A regulatory review (FSSAI advertising & claims regulations) is recommended before launch. |

## C. Could not be extracted

| Item | Reason |
|---|---|
| Existing customer accounts & order history | Private data behind WordPress admin — needs a database export from the client if history must be retained |
| Shipping zones beyond the flat/free rule | Only the customer-facing result is observable (₹40 / free ≥ ₹500, identical for two states tested) |
| Coupon codes | Not public |
| 2 media-library entries (`Sugar-1-01.jpg` metadata, an Elementor screenshot) | Listed without dimensions; files downloaded where a URL resolved — see `05-image-inventory.md` |
