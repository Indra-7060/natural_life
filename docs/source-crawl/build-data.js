// Builds the content-migration dataset + generated inventories from the raw crawl.
// Input : docs/source-crawl/raw/**   (WordPress REST / WooCommerce Store API JSON + page HTML)
// Output: app/Database/Seeds/data/source.json   (consumed by ContentSeeder)
//         docs/analysis/*.md (generated inventories)
// Usage : node docs/source-crawl/build-data.js
const fs = require('fs');
const path = require('path');
const ROOT = path.join(__dirname, '..', '..');
const RAW = path.join(__dirname, 'raw');
const MEDIA = path.join(__dirname, 'media');
const OUT = path.join(ROOT, 'app', 'Database', 'Seeds', 'data');
const DOCS = path.join(ROOT, 'docs', 'analysis');
fs.mkdirSync(OUT, { recursive: true });
fs.mkdirSync(DOCS, { recursive: true });

const J = (f) => JSON.parse(fs.readFileSync(path.join(RAW, f), 'utf8'));
const H = (f) => fs.readFileSync(path.join(RAW, 'html', f + '.html'), 'utf8');
const UP = 'https://natural-life.in/wp-content/uploads/';

const decode = (s) => String(s)
  .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n))
  .replace(/&#x([0-9a-f]+);/gi, (_, n) => String.fromCodePoint(parseInt(n, 16)))
  .replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&hellip;/g, '…')
  .replace(/&rsquo;/g, '’').replace(/&lsquo;/g, '‘').replace(/&ldquo;/g, '“').replace(/&rdquo;/g, '”')
  .replace(/&ndash;/g, '–').replace(/&mdash;/g, '—');
const text = (h) => decode(String(h).replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
const cfDecode = (hex) => { const k = parseInt(hex.slice(0, 2), 16); let s = ''; for (let i = 2; i < hex.length; i += 2) s += String.fromCharCode(parseInt(hex.slice(i, i + 2), 16) ^ k); return s; };

// Whitelist sanitiser: keeps semantic tags only, drops all attributes except a[href].
const KEEP = new Set(['h2', 'h3', 'h4', 'h5', 'p', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'a', 'br', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'blockquote']);
function sanitize(html) {
  let h = String(html)
    .replace(/<(script|style|noscript|svg|form|nav|button)[\s\S]*?<\/\1>/gi, '')
    .replace(/<!--[\s\S]*?-->/g, '')
    // Cloudflare e-mail obfuscation -> plain address
    .replace(/<a[^>]*data-cfemail="([0-9a-f]+)"[^>]*>[\s\S]*?<\/a>/gi, (_, x) => cfDecode(x))
    .replace(/<span[^>]*data-cfemail="([0-9a-f]+)"[^>]*>[\s\S]*?<\/span>/gi, (_, x) => cfDecode(x));
  h = h.replace(/<h1\b/gi, '<h2').replace(/<\/h1>/gi, '</h2>');
  h = h.replace(/<(\/?)([a-z0-9]+)\b([^>]*)>/gi, (m, close, tag, attrs) => {
    tag = tag.toLowerCase();
    if (!KEEP.has(tag)) return /^(div|section|article|figure)$/.test(tag) ? '\n' : '';
    if (close) return `</${tag}>`;
    if (tag === 'a') {
      const href = (attrs.match(/href="([^"]*)"/i) || [])[1];
      if (!href || /email-protection/.test(href)) return '<a>';
      const ext = /^https?:/.test(href) && !/natural-life\.in/.test(href);
      return `<a href="${href}"${ext ? ' rel="noopener" target="_blank"' : ''}>`;
    }
    if (tag === 'b') return '<strong>';
    return `<${tag}>`;
  }).replace(/<\/b>/g, '</strong>').replace(/<a>([\s\S]*?)<\/a>/g, '$1');
  // tidy
  h = decode(h).replace(/&(?!(amp|lt|gt|quot);)/g, '&amp;');
  let prev;
  do { prev = h; h = h.replace(/<(p|h2|h3|h4|h5|li|ul|ol|strong|em|blockquote)>\s*<\/\1>/g, ''); } while (h !== prev);
  h = h.split('\n').map((l) => l.trim()).filter(Boolean).join('\n').trim();
  // flatten the needlessly nested lists WooCommerce short descriptions contain
  return h.replace(/<ul>\s*<li>\s*<ul>/g, '<ul>').replace(/<\/ul>\s*<\/li>\s*<\/ul>/g, '</ul>');
}

// Elementor page -> ordered widget list
function widgets(html) {
  return html.split(/data-widget_type="/).slice(1).map((p) => {
    const type = p.slice(0, p.indexOf('"')).replace('.default', '');
    let body = p.slice(p.indexOf('>') + 1);
    const cut = body.search(/<div class="elementor-element elementor-element-[0-9a-f]+[^"]*elementor-widget|<section class="elementor-section|<div class="elementor-element elementor-element-[0-9a-f]+ e-|<\/section>/);
    if (cut > 0) body = body.slice(0, cut);
    return { type, html: body, text: text(body), img: (body.match(/<img[^>]*?(?:data-src|src)="(https:[^"]+)"/) || [])[1] || null };
  });
}
const rel = (url) => (url || '').replace(UP, '').replace(/-\d+x\d+(\.[a-z]+)$/i, '$1');

// ---------------------------------------------------------------- products
const storeProducts = J('api_wc_store_v1_products_per_page_100.json');
const products = storeProducts.map((p) => {
  const page = H('product_' + p.slug);
  const variations = p.variations.map((v) => {
    const d = J(`variation_${v.id}.json`);
    return {
      wp_id: v.id, label: v.attributes[0].value, attribute: v.attributes[0].name,
      regular_price: +d.prices.regular_price, sale_price: d.on_sale ? +d.prices.sale_price : null,
      sku: d.sku || '', in_stock: d.is_in_stock,
    };
  }).sort((a, b) => parseFloat(a.label) - parseFloat(b.label));
  const attributes = p.attributes.filter((a) => !a.has_variations || !a.taxonomy)
    .filter((a) => a.taxonomy === null)
    .map((a) => ({ name: a.name, value: a.terms.map((t) => t.name).join(', ') }));
  return {
    wp_id: p.id, name: decode(p.name), slug: p.slug, type: p.type, sku: p.sku || '',
    regular_price: +p.prices.regular_price, sale_price: p.on_sale && p.type === 'simple' ? +p.prices.sale_price : null,
    price_min: p.prices.price_range ? +p.prices.price_range.min_amount : +p.prices.price,
    price_max: p.prices.price_range ? +p.prices.price_range.max_amount : +p.prices.price,
    in_stock: p.is_in_stock, on_sale: p.on_sale,
    source_categories: p.categories.map((c) => c.name), source_tags: p.tags.map((t) => t.name),
    short_description: sanitize(p.short_description), description: sanitize(p.description),
    images: p.images.map((i) => rel(i.src)), image_alts: p.images.map((i) => decode(i.alt || '')),
    attributes, variations,
    seo_title: decode((page.match(/<title>([\s\S]*?)<\/title>/) || [])[1] || ''),
    source_url: p.permalink,
  };
}).sort((a, b) => a.wp_id - b.wp_id);

// ---------------------------------------------------------------- blog posts
const posts = J('api_wp_v2_posts_per_page_100.json').map((p) => ({
  wp_id: p.id, slug: p.slug, title: decode(p.title.rendered), published_at: p.date.replace('T', ' '),
  excerpt: text(p.excerpt.rendered).replace(/\s*\[…\]$/, '…'), content: sanitize(p.content.rendered), source_url: p.link,
})).sort((a, b) => a.published_at.localeCompare(b.published_at));

// ---------------------------------------------------------------- FAQs
const faqHtml = H('faq');
const faqs = [];
{
  const re = /<a class="elementor-accordion-title"[^>]*>([\s\S]*?)<\/a>[\s\S]*?<div[^>]*class="elementor-tab-content[^"]*"[^>]*>([\s\S]*?)<\/div>\s*<\/div>/g;
  let m; while ((m = re.exec(faqHtml))) faqs.push({ question: text(m[1]), answer: sanitize(m[2]) });
}

// ---------------------------------------------------------------- testimonials
const home = H('home');
const testimonials = [];
{
  const seen = new Set();
  for (const block of home.split('class="elementor-testimonial"').slice(1)) {
    const end = block.indexOf('elementor-testimonial__footer');
    const w = widgets(block.slice(0, end > 0 ? end : block.length));
    const heads = w.filter((x) => x.type === 'heading');
    const texts = w.filter((x) => x.type === 'text-editor');
    const rating = (block.match(/Rated (\d) out of 5/) || [])[1];
    const t = { name: heads[0]?.text, rating: +rating || 5, meta: texts[0]?.text, title: (heads[1]?.text || '').replace(/^["“]/, '').trim(), text: texts.slice(1).map((x) => x.text).join('\n') };
    const key = t.name + t.text.slice(0, 20);
    if (t.name && !seen.has(key)) { seen.add(key); testimonials.push(t); }
  }
}

// ---------------------------------------------------------------- about page
const aboutW = widgets(H('about-naturalife')).filter((w) => /heading|text-editor|image/.test(w.type));
const about = { story: [], journey: [], usps: [] };
{
  const iStory = aboutW.findIndex((w) => w.type === 'heading' && w.text === 'Our Story' && aboutW.indexOf(w) > 3);
  const iJourney = aboutW.findIndex((w) => w.text === 'The Sugarlif Journey');
  const iUsp = aboutW.findIndex((w) => w.text === 'Global Disruption');
  const iFooter = aboutW.findIndex((w) => w.img && /logo-white/.test(w.img));
  let cur = null;
  for (const w of aboutW.slice(iStory + 1, iJourney - 1)) {
    if (w.type !== 'text-editor') continue;
    const m = w.html.match(/^\s*(?:<div[^>]*>\s*)*<p>\s*<b>([\s\S]*?)<\/b>\s*<\/p>/);
    if (m) { cur = { heading: text(m[1]), html: sanitize(w.html.replace(m[0], '')) }; about.story.push(cur); }
    else if (cur) cur.html += '\n' + sanitize(/<p/.test(w.html) ? w.html : `<p>${w.html}</p>`);
  }
  for (const w of aboutW.slice(iJourney + 1, iUsp - 1)) if (w.type === 'text-editor') about.journey.push(w.text);
  for (const w of aboutW.slice(iUsp + 1, iFooter)) if (w.type === 'image-box') about.usps.push({ icon: rel(w.img), text: w.text });
}

// ---------------------------------------------------------------- legal pages
const pages = J('api_wp_v2_pages_per_page_100.json');
const pageBody = (slug) => {
  const c = pages.find((p) => p.slug === slug).content.rendered;
  // drop the hero (title + breadcrumb) section that precedes the real content
  const i = c.indexOf('</nav>');
  return sanitize(i > 0 ? c.slice(i + 6) : c);
};
const legal = { privacy: pageBody('privacy-policy'), refund: pageBody('refund_returns') };

// ---------------------------------------------------------------- SEO meta of every crawled URL
const meta = JSON.parse(fs.readFileSync(path.join(__dirname, 'text', '_meta.json'), 'utf8'));

const data = { generated_from: 'https://natural-life.in/', crawled_on: '2026-11-11', products, posts, faqs, testimonials, about, legal };
fs.writeFileSync(path.join(OUT, 'source.json'), JSON.stringify(data, null, 1));

// ================================================================ generated docs
const inr = (n) => '₹' + Number(n).toLocaleString('en-IN');
let md = `# 03 · Product inventory\n\nSource: WooCommerce Store API (\`/wp-json/wc/store/v1/products\`) + product pages, crawled 2026-11-11. ${products.length} products.\n\n| # | WP ID | Product name (exact) | Type | Price | Stock | Source category | Images | Original URL |\n|---|---|---|---|---|---|---|---|---|\n`;
products.forEach((p, i) => {
  const price = p.type === 'variable' ? `${inr(p.price_min)} – ${inr(p.price_max)}` : (p.sale_price ? `~~${inr(p.regular_price)}~~ **${inr(p.sale_price)}** (sale)` : inr(p.regular_price));
  md += `| ${i + 1} | ${p.wp_id} | ${p.name} | ${p.type} | ${price} | ${p.in_stock ? 'In stock' : '**Out of stock**'} | ${p.source_categories.join(', ') || 'Uncategorized'} | ${p.images.length} | ${p.source_url} |\n`;
});
md += `\n## Descriptions & specifications\n`;
for (const p of products) {
  md += `\n### ${p.name}\n\n- **Slug:** \`${p.slug}\`\n- **Tags:** ${p.source_tags.join(', ') || '—'}\n- **Reviews on source:** 0 (reviews enabled, none published)\n`;
  md += `- **Specifications:** ${p.attributes.length ? p.attributes.map((a) => `${a.name}: ${a.value.replace(/\n/g, '; ')}`).join(' · ') : '— (none published)'}\n`;
  md += `- **Short description:** ${text(p.short_description)}\n- **Description:** ${text(p.description)}\n- **Images:** ${p.images.map((x) => '`' + x + '`').join(', ')}\n`;
}
fs.writeFileSync(path.join(DOCS, '03-product-inventory.md'), md);

md = `# 04 · Product variations & pricing inventory\n\nEvery price below was read from the live store API on 2026-11-11 (currency INR, prices tax-inclusive, no tax line at checkout).\n\n| Product | Variation | Regular price | Sale price | SKU on source | Stock |\n|---|---|---|---|---|---|\n`;
for (const p of products) {
  if (p.variations.length) for (const v of p.variations) md += `| ${p.name} | Pack Weight: ${v.label} | ${inr(v.regular_price)} | ${v.sale_price ? inr(v.sale_price) : '—'} | ${v.sku || '—'} | ${v.in_stock ? 'In stock' : 'Out of stock'} |\n`;
  else md += `| ${p.name} | — (single pack) | ${inr(p.regular_price)} | ${p.sale_price ? inr(p.sale_price) : '—'} | ${p.sku || '—'} | ${p.in_stock ? 'In stock' : '**Out of stock**'} |\n`;
}
md += `\n**Notes**\n\n- Only one product is on sale: *Sugarlif Low GI Sugar 150 Sachets Pack* (${inr(400)} → ${inr(350)}).\n- *Rasgollas 1 Kg* is the only out-of-stock product.\n- The source store does not expose stock quantities (stock management is by status only), so quantities are not migrated — see \`09-missing-information.md\`.\n- Source SKUs are placeholders (\`1\`, \`2\`, \`15\` …) rather than real SKUs; they are recorded here but not used as identifiers.\n`;
fs.writeFileSync(path.join(DOCS, '04-variations-pricing-inventory.md'), md);

// image inventory
const mediaList = [...J('api_wp_v2_media_per_page_100.json'), ...J('api_media_p2.json')];
const used = new Set();
for (const f of fs.readdirSync(path.join(RAW, 'html'))) {
  if (/^(home2121|demo)\.html$/.test(f)) continue;
  const h = fs.readFileSync(path.join(RAW, 'html', f), 'utf8');
  for (const m of h.matchAll(/wp-content\/uploads\/([^"'\s)\\]+\.(?:png|jpe?g|webp|svg))/gi)) used.add(m[1].replace(/-\d+x\d+(\.[a-z]+)$/i, '$1'));
}
for (const m of fs.readFileSync(path.join(RAW, 'post-7.css'), 'utf8').matchAll(/wp-content\/uploads\/([^"')]+)/g)) used.add(m[1]);
md = `# 05 · Image inventory\n\n${mediaList.length} items in the WordPress media library. Originals (full resolution, never re-compressed) are archived in \`docs/source-crawl/media/\` keeping the original folder structure. "Used" = referenced by a live page, product or stylesheet at crawl time.\n\n| WP ID | File | Dimensions | Size | Used on live site | Downloaded |\n|---|---|---|---|---|---|\n`;
let got = 0, usedN = 0;
for (const m of mediaList) {
  const r = m.source_url.replace(UP, ''); const d = m.media_details || {};
  const f = path.join(MEDIA, r); const ok = fs.existsSync(f) && fs.statSync(f).size > 0; if (ok) got++;
  const u = used.has(r); if (u) usedN++;
  md += `| ${m.id} | \`${r}\` | ${d.width || '?'}×${d.height || '?'} | ${ok ? Math.round(fs.statSync(f).size / 1024) + ' KB' : '—'} | ${u ? 'Yes' : 'No'} | ${ok ? 'Yes' : '**No**'} |\n`;
}
md += `\n**Totals:** ${mediaList.length} library items · ${got} downloaded · ${usedN} referenced by live pages.\n`;
fs.writeFileSync(path.join(DOCS, '05-image-inventory.md'), md);

md = `# 08 · SEO information inventory\n\nRead from the \`<head>\` of every crawled URL. The source site has **no SEO plugin**: no meta descriptions, no Open Graph tags, and only WooCommerce's default JSON-LD.\n\n| URL file | <title> | Meta description | Canonical | Open Graph | JSON-LD blocks | Robots |\n|---|---|---|---|---|---|---|\n`;
for (const m of meta) md += `| ${m.file.replace('.html', '')} | ${m.title.replace(/\|/g, '\\|')} | ${m.description || '— none'} | ${m.canonical ? 'yes' : '— none'} | ${Object.keys(m.og).length || '— none'} | ${m.jsonld.length} | ${m.robots} |\n`;
md += `\n**Findings**\n\n- Titles follow the pattern \`{Page} – Sugarlif\`; the home title is a long keyword list.\n- No page has a meta description or social-sharing metadata — the new site adds editable title/description/OG fields for every page, product and post, seeded with accurate descriptions drawn from the page's own content.\n- \`cart\`, \`checkout\`, \`my-account\` are \`noindex\` on the source and stay \`noindex\`.\n- Sitemap on source: \`/wp-sitemap.xml\` (posts, pages, products, categories, product tags). New site serves \`/sitemap.xml\`.\n`;
fs.writeFileSync(path.join(DOCS, '08-seo-inventory.md'), md);

console.log(JSON.stringify({ products: products.length, variations: products.reduce((n, p) => n + p.variations.length, 0), posts: posts.length, faqs: faqs.length, testimonials: testimonials.length, story: about.story.map((s) => s.heading), journey: about.journey.length, usps: about.usps.length, privacyLen: legal.privacy.length, refundLen: legal.refund.length, mediaDownloaded: got, mediaUsed: usedN }, null, 1));
