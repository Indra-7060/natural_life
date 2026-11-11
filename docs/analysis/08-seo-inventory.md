# 08 · SEO information inventory

Read from the `<head>` of every crawled URL. The source site has **no SEO plugin**: no meta descriptions, no Open Graph tags, and only WooCommerce's default JSON-LD.

| URL file | <title> | Meta description | Canonical | Open Graph | JSON-LD blocks | Robots |
|---|---|---|---|---|---|---|
| about-naturalife | Our Profile – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| blog | Blog – Sugarlif | — none | — none | — none | 0 | max-image-preview:large |
| cart | Cart – Sugarlif | — none | yes | — none | 0 | max-image-preview:large, noindex, follow |
| category_blog | Blog – Sugarlif | — none | — none | — none | 0 | max-image-preview:large |
| certification | Certification – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| checkout | Cart – Sugarlif | — none | yes | — none | 0 | max-image-preview:large, noindex, follow |
| contact-us | Contact Us – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| demo | Demo – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| episode-2-the-day-sugar-went-to-whistler | The Day Sugar Went to Whistler – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| episode-3-can-you-trust-a-food-label | Can You Trust a Food Label? – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| episode-4-the-secret-life-of-a-spoonful-of-sugar | The Secret Life of a Spoonful of Sugar – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| episode-5-sugar-on-trial | Sugar on Trial – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| faq | FAQ – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| glycemic-index | Glycemic Index – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| health-benefits-of-low-gi | Health Benefits of Low GI – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| home | Sugarlif – Natural Low GI Products \| Low GI Sugar \| Low GI Premix Tea \| Low GI Jam \| Low GI Sauces \| GI Labs \| Low GI Revolution \| Low GI Products India \| Low Glycemic Index \| Alternate for Sugar \| Wellness | — none | yes | — none | 0 | max-image-preview:large |
| home2121 | home2165161 – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| if-sugar-had-a-passport | If Sugar Had a Passport… – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| low-gi-diet-sugar | Low GI Diet Sugar – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| low-gi-diet-vs-other-diet | Low GI Diet Vs Other Diet – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| my-account | My account – Sugarlif | — none | yes | — none | 0 | max-image-preview:large, noindex, follow |
| other-applications | Other Applications – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| privacy-policy | Privacy policy – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| product-category_sugar | Sugar – Sugarlif | — none | — none | — none | 1 | max-image-preview:large |
| product-category_uncategorized | Uncategorized – Sugarlif | — none | — none | — none | 1 | max-image-preview:large |
| product-tag_suger | Suger – Sugarlif | — none | — none | — none | 1 | max-image-preview:large |
| products | Products – Sugarlif | — none | — none | — none | 1 | max-image-preview:large |
| product_sugarlif-gulab-jamun-1-kg-pack-of-1-made-from-desi-ghee-sweetened-with-sugarlif-low-gi-diet-sugar | Gulab Jamun 1 Kg – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-honey | Honey – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-low-gi-diet-sugar | Low GI Sugar – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-low-gi-jaggery | Low GI Jaggery 500gm – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-low-gi-sugar-150-sachets-pack | Sugarlif Low GI Sugar 150 Sachets Pack – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-low-gi-wheat-flour-atta | Low GI Wheat Flour (Atta) – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| product_sugarlif-rasgollas-1-kg-pack-of-1-sweetened-with-sugarlif-low-gi-diet-sugar | Rasgollas 1 Kg – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| refund_returns | Refund and Returns Policy – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| sugarlif-low-gi-rice-atta | Sugarlif® – Low GI Rice – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| sugarlif-low-gi-whole-wheat-atta | Sugarlif® – Low Gi Whole Wheat Atta – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| team | Team – Sugarlif | — none | yes | — none | 1 | max-image-preview:large |
| terms-and-conditions | Terms and conditions – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| why-gi-labs-canada-certification-matters-more-than-marketing-claims | Why GI Labs Canada Certification Matters More Than Marketing Claims – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |
| wishlist | Wishlist – Sugarlif | — none | yes | — none | 0 | max-image-preview:large |

**Findings**

- Titles follow the pattern `{Page} – Sugarlif`; the home title is a long keyword list.
- No page has a meta description or social-sharing metadata — the new site adds editable title/description/OG fields for every page, product and post, seeded with accurate descriptions drawn from the page's own content.
- `cart`, `checkout`, `my-account` are `noindex` on the source and stay `noindex`.
- Sitemap on source: `/wp-sitemap.xml` (posts, pages, products, categories, product tags). New site serves `/sitemap.xml`.
