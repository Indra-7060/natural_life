# 06 · Content inventory

What exists on the source site, where it was captured, and where it lives in the new database. Full extracted text of every page is in `docs/source-crawl/text/*.txt`; the cleaned migration dataset is `app/Database/Seeds/data/source.json`.

| Content | Source | Volume | Captured | New location (table) |
|---|---|---|---|---|
| Company description | Footer | 1 paragraph | Verbatim | `settings.footer_about` |
| Our Story | `/about-naturalife/` | 6 chapters (~1,150 words) | Verbatim | `page_sections` (page `about-naturalife`, keys `story-1…6`) |
| The Sugarlif Journey | `/about-naturalife/` | Arthur Ashe quote + 6 paragraphs incl. the Diabliss partnership history (2011–2017) and 2015 Natural Life partnership | Verbatim | `page_sections` (`journey`) |
| USPs — "Global Disruption" | `/about-naturalife/` | 4 statements + icons | Verbatim | `page_sections` (`usps`) |
| Team | `/team/` | Malik A Mulla (Chairman), Ajay K George (CEO), Dr. C. K. Nandgopalan (Director – Research & Development), Vikas D. Dangat, Sureddy V. Reddy — 5 portraits | Verbatim | `team_members` |
| Certifications | `/certification/` + About + product copy | One sentence on the page ("For Certificates and detailed reports, please mail us at info@natural-life.in"). Named bodies in other copy: GI Labs (Canada), Whistler Center for Carbohydrate Research (USA), evaluations in Australia and India, BRC Global Standard for Food Safety, US FDA (product copy) | Verbatim names only, no documents | `certifications` (name + source statement; no images) |
| Glycemic Index education | `/glycemic-index/` | Definition, High/Moderate/Low bands, How GI affects you, GI vs GL (4 points), why low GI, What is Glycemic Load · 5 diagrams | Verbatim | `page_sections` |
| Health benefits | `/health-benefits-of-low-gi/` | 12 benefits, High vs Low diagram, mechanism of action (2 paragraphs) · 3 diagrams | Verbatim | `page_sections` |
| Diet comparison | `/low-gi-diet-vs-other-diet/` | Keto study results + conclusions + reference link, chart | Verbatim | `page_sections` |
| Low GI Diet Sugar story | `/low-gi-diet-sugar/` | 5 paragraphs, 6 benefits, 6 outcomes, "v/s Ordinary Sugar" (7 rows), "v/s Other Sweeteners" (6 rows) | Verbatim | `page_sections` |
| Whole Wheat Atta story | `/sugarlif-low-gi-whole-wheat-atta/` | 2 paragraphs, 7 benefits | Verbatim | `page_sections` |
| Low GI Rice story | `/sugarlif-low-gi-rice-atta/` | 2 paragraphs, 5 varieties, 8 benefits | Verbatim | `page_sections` |
| Other Applications | `/other-applications/` | 4 statements | Verbatim | `page_sections` |
| Home copy | `/` | 3 banner headlines, "For Your Healthy Beginning", Low GI intro, What is GI / GL, 6 "Benefits of Low GI Foods", Sugarlif vs High GI foods (8 + 8 rows) | Verbatim, re-arranged into the new homepage | `banners`, `page_sections` (page `home`) |
| FAQs | `/faq/` | 12 Q&As (6 also shown on home) | Verbatim | `faqs` |
| Testimonials | `/` | 6 (all 5★, "Amazon Customer") incl. one in Hindi | Verbatim, unedited | `testimonials` |
| Blog | `/blog/` | 6 articles (≈ 2,000–4,000 characters each) | Verbatim HTML | `blog_posts`, `blog_categories` |
| Products | `/product/*` | 7 products, 9 variations — see 03 & 04 | Verbatim | `products`, `product_variations`, `product_attributes`, `product_images` |
| Contact details | `/contact-us/`, header | info@natural-life.in · sales@natural-life.in · +91 800 729 1595 (call / WhatsApp, bulk orders) · F-7, Sacred World Mall, Wanwadi, Pune 411040, Maharashtra | Verbatim | `settings` |
| Marketplace links | Footer | Flipkart, Amazon, JioMart, 1mg | URLs verbatim | `settings` |
| Innovator link | Footer | CKN Innovations — `https://ckninnovations.com/` | Verbatim | `settings` |
| Privacy Policy | `/privacy-policy/` | Effective 1 April 2022 | Verbatim HTML | `pages.body` |
| Refund & Returns + Shipping Policy | `/refund_returns/` | Refund policy (30 days) + Shipping Policy for Sugarlif.com (India), effective 1 April 2022 | Verbatim HTML (incl. unfilled `{email address}` / `{physical address}` placeholders — flagged) | `pages.body` |
| Terms & Conditions | `/terms-and-conditions/` | **Empty on source** | Nothing to capture | `pages` (published with a neutral notice; awaiting client text) |

## Editorial changes made during migration

Presentation only — no factual changes:

- Obvious heading typos in *page furniture* corrected ("Frequently Asked Quesions" → "Questions"). Body copy, product copy, testimonials and blog text are untouched, including their original spelling.
- Needlessly nested bullet lists in product short descriptions flattened.
- Cloudflare-obfuscated e-mail addresses decoded to the real addresses.
- Duplicate carousel copies (desktop/mobile duplicates of the same 6 testimonials, repeated Low GI intro block) de-duplicated.
- Health statements are reproduced as the client published them and attributed to the brand's own material; nothing was added or strengthened. The FAQ's own disclaimers ("Sugarlif is NOT a medication…") are preserved and surfaced on the education pages.
