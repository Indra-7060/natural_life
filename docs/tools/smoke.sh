#!/usr/bin/env bash
# Smoke test: requests every public URL and reports HTTP status + any PHP error text.
# Usage: bash docs/tools/smoke.sh [base-url]
BASE="${1:-http://localhost:8080}"
TMP="$(mktemp)"
URLS=(
  "" "shop" "shop?q=sugar&sort=price-asc" "shop?min=300&max=700&stock=1" "shop?sale=1" "product-category/sugar"
  "product/sugarlif-low-gi-diet-sugar" "product/sugarlif-honey" "product/sugarlif-low-gi-wheat-flour-atta" "product/sugarlif-low-gi-jaggery"
  "product/sugarlif-low-gi-sugar-150-sachets-pack"
  "product/sugarlif-gulab-jamun-1-kg-pack-of-1-made-from-desi-ghee-sweetened-with-sugarlif-low-gi-diet-sugar"
  "product/sugarlif-rasgollas-1-kg-pack-of-1-sweetened-with-sugarlif-low-gi-diet-sugar"
  "shop/quick-view/sugarlif-low-gi-wheat-flour-atta" "shop/suggest?q=att" "cart" "cart/drawer" "checkout" "wishlist"
  "blog" "blog/if-sugar-had-a-passport" "blog/category/blog" "about-naturalife" "team" "certification" "glycemic-index"
  "health-benefits-of-low-gi" "low-gi-diet-vs-other-diet" "low-gi-diet-sugar" "sugarlif-low-gi-whole-wheat-atta"
  "sugarlif-low-gi-rice-atta" "other-applications" "faq" "contact-us" "privacy-policy" "refund_returns" "terms-and-conditions"
  "my-account/login" "my-account/forgot-password" "my-account" "sitemap.xml" "robots.txt"
  "products" "if-sugar-had-a-passport" "category/blog" "admin" "admin/login" "this-page-does-not-exist"
)
fail=0
for u in "${URLS[@]}"; do
  out=$(curl -s -o "$TMP" -w "%{http_code} %{redirect_url}" "$BASE/$u")
  err=$(grep -ciE "ErrorException|Fatal error|Warning</b>|Notice</b>|Undefined (variable|index|array key|property)|Call to (undefined|a member)|TypeError|DatabaseException|ParseError" "$TMP")
  printf '%s | err=%s | /%s\n' "$out" "$err" "$u"
  if [ "$err" != "0" ]; then
    fail=1
    sed -e 's/<[^>]*>/ /g' "$TMP" | grep -iE "Exception|Undefined|Call to|TypeError|APPPATH|line [0-9]+" | tr -s ' ' | head -6
  fi
done
rm -f "$TMP"
exit $fail
