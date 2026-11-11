# Redirect map (original URL → new URL)

All are permanent (301) and are seeded into the `redirects` table. Manage them in **Admin → Redirects**. Every other original URL is unchanged.

| Original URL | New URL | Reason |
|---|---|---|
| `/products/` | `/shop` | WooCommerce shop archive |
| `/product-category/uncategorized/` | `/shop` | Default WooCommerce category |
| `/product-tag/suger/` | `/shop` | Misspelt product tag |
| `/category/blog/` | `/blog` | WordPress category archive |
| `/if-sugar-had-a-passport/` | `/blog/if-sugar-had-a-passport` | Blog posts moved under `/blog` |
| `/episode-2-the-day-sugar-went-to-whistler/` | `/blog/episode-2-the-day-sugar-went-to-whistler` | 〃 |
| `/episode-3-can-you-trust-a-food-label/` | `/blog/episode-3-can-you-trust-a-food-label` | 〃 |
| `/episode-4-the-secret-life-of-a-spoonful-of-sugar/` | `/blog/episode-4-the-secret-life-of-a-spoonful-of-sugar` | 〃 |
| `/episode-5-sugar-on-trial/` | `/blog/episode-5-sugar-on-trial` | 〃 |
| `/why-gi-labs-canada-certification-matters-more-than-marketing-claims/` | `/blog/why-gi-labs-canada-certification-matters-more-than-marketing-claims` | 〃 |
| `/home2121/` | `/` | Abandoned homepage draft |
| `/demo/` | `/` | Empty test page |

## URLs kept exactly as they were

`/` · `/about-naturalife` · `/team` · `/certification` · `/glycemic-index` · `/health-benefits-of-low-gi` · `/low-gi-diet-vs-other-diet` · `/low-gi-diet-sugar` · `/sugarlif-low-gi-whole-wheat-atta` · `/sugarlif-low-gi-rice-atta` · `/other-applications` · `/faq` · `/contact-us` · `/blog` · `/cart` · `/checkout` · `/my-account` · `/wishlist` · `/privacy-policy` · `/refund_returns` · `/terms-and-conditions` · `/product-category/sugar` · all seven `/product/{slug}` addresses.

Trailing slashes: the original used `/page/`; the new site answers on `/page` and treats `/page/` as the same address.
