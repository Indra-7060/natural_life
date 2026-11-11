# 04 · Product variations & pricing inventory

Every price below was read from the live store API on 2026-11-11 (currency INR, prices tax-inclusive, no tax line at checkout).

| Product | Variation | Regular price | Sale price | SKU on source | Stock |
|---|---|---|---|---|---|
| Low GI Sugar | Pack Weight: 1kg | ₹250 | — | — | In stock |
| Low GI Sugar | Pack Weight: 2kg | ₹490 | — | 2 | In stock |
| Low GI Sugar | Pack Weight: 3kg | ₹725 | — | 3 | In stock |
| Low GI Sugar | Pack Weight: 4kg | ₹960 | — | 4 | In stock |
| Low GI Sugar | Pack Weight: 5kg | ₹1,200 | — | 5 | In stock |
| Low GI Sugar | Pack Weight: 6kg | ₹1,350 | — | 6 | In stock |
| Low GI Sugar | Pack Weight: 15kg | ₹3,250 | — | 15 | In stock |
| Honey | — (single pack) | ₹600 | — | — | In stock |
| Low GI Wheat Flour (Atta) | Pack Weight: 3kg | ₹450 | — | 1 | In stock |
| Low GI Wheat Flour (Atta) | Pack Weight: 5kg | ₹750 | — | 1 | In stock |
| Low GI Jaggery 500gm | — (single pack) | ₹130 | — | — | In stock |
| Gulab Jamun 1 Kg | — (single pack) | ₹360 | — | — | In stock |
| Rasgollas 1 Kg | — (single pack) | ₹325 | — | — | **Out of stock** |
| Sugarlif Low GI Sugar 150 Sachets Pack | — (single pack) | ₹400 | ₹350 | — | In stock |

**Notes**

- Only one product is on sale: *Sugarlif Low GI Sugar 150 Sachets Pack* (₹400 → ₹350).
- *Rasgollas 1 Kg* is the only out-of-stock product.
- The source store does not expose stock quantities (stock management is by status only), so quantities are not migrated — see `09-missing-information.md`.
- Source SKUs are placeholders (`1`, `2`, `15` …) rather than real SKUs; they are recorded here but not used as identifiers.
