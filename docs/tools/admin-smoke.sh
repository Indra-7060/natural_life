#!/usr/bin/env bash
# Admin smoke test: signs in with the credentials in .env and opens every admin screen.
# Usage: bash docs/tools/admin-smoke.sh [base-url] [password-override]
BASE="${1:-http://localhost:8080}"
ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
EMAIL=$(grep -E '^admin.defaultEmail' "$ROOT/.env" | sed -E "s/^[^=]+= *'?([^']*)'?.*/\1/")
PASS="${2:-$(grep -E '^admin.defaultPassword' "$ROOT/.env" | sed -E "s/^[^=]+= *'?([^']*)'?.*/\1/")}"
JAR="$(mktemp)"; TMP="$(mktemp)"
token() { grep -oE 'name="csrf_token" value="[^"]+"' "$TMP" | head -1 | sed -E 's/.*value="([^"]+)"/\1/'; }

curl -s -c "$JAR" -b "$JAR" -o "$TMP" "$BASE/admin/login"
code=$(curl -s -c "$JAR" -b "$JAR" -o "$TMP" -w "%{http_code} %{redirect_url}" --data-urlencode "csrf_token=$(token)" --data-urlencode "email=$EMAIL" --data-urlencode "password=$PASS" "$BASE/admin/login")
echo "login: $code"

URLS=(admin admin/profile "admin/search?q=sugar" admin/products admin/products/new admin/products/1/edit admin/products/3/edit
  admin/categories admin/categories/new admin/categories/1/edit admin/coupons admin/coupons/new admin/orders admin/customers
  admin/home admin/banners admin/banners/1/edit admin/pages admin/pages/new admin/pages/2/edit admin/pages/sections/1/edit admin/pages/sections/8/edit
  admin/menus "admin/menu-items?menu=1" "admin/menu-items/new?menu=1" "admin/menu-items/2/edit?menu=1"
  admin/faqs admin/faqs/1/edit admin/testimonials admin/testimonials/1/edit admin/team admin/team/1/edit admin/certifications admin/certifications/new
  admin/posts admin/posts/1/edit admin/blog-categories admin/enquiries admin/subscribers admin/media "admin/media/picker?folder=products"
  admin/settings admin/settings/shop admin/settings/contact admin/seo admin/seo/1/edit admin/redirects admin/redirects/new admin/users admin/users/1/edit admin/users/new)
fail=0
for u in "${URLS[@]}"; do
  out=$(curl -s -b "$JAR" -c "$JAR" -o "$TMP" -w "%{http_code} %{redirect_url}" "$BASE/$u")
  err=$(grep -ciE "ErrorException|Fatal error|Undefined (variable|index|array key|property)|Call to (undefined|a member)|TypeError|DatabaseException|ParseError|ArgumentCountError" "$TMP")
  printf '%s | err=%s | /%s\n' "$out" "$err" "$u"
  if [ "$err" != "0" ] || [ "${out:0:1}" = "5" ]; then
    fail=1
    sed -e 's/<[^>]*>/ /g' "$TMP" | grep -iE "Exception|Undefined|Call to|TypeError|APPPATH|SYSTEMPATH" | tr -s ' ' | head -6
  fi
done
rm -f "$JAR" "$TMP"
exit $fail
