// Converts crawled HTML (raw/html/*.html) into readable text + metadata.
// Usage: node extract.js
const fs = require('fs');
const path = require('path');
const RAW = path.join(__dirname, 'raw', 'html');
const OUT = path.join(__dirname, 'text');
fs.mkdirSync(OUT, { recursive: true });

const decode = (s) => s
  .replace(/&#(\d+);/g, (_, n) => String.fromCodePoint(+n))
  .replace(/&#x([0-9a-f]+);/gi, (_, n) => String.fromCodePoint(parseInt(n, 16)))
  .replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>')
  .replace(/&quot;/g, '"').replace(/&rsquo;/g, '’').replace(/&lsquo;/g, '‘').replace(/&ldquo;/g, '“')
  .replace(/&rdquo;/g, '”').replace(/&ndash;/g, '–').replace(/&mdash;/g, '—').replace(/&hellip;/g, '…');

function toText(html) {
  let h = html
    .replace(/<script[\s\S]*?<\/script>/gi, '')
    .replace(/<style[\s\S]*?<\/style>/gi, '')
    .replace(/<noscript[\s\S]*?<\/noscript>/gi, '')
    .replace(/<svg[\s\S]*?<\/svg>/gi, '')
    .replace(/<!--[\s\S]*?-->/g, '');
  h = h.replace(/<img\b[^>]*>/gi, (m) => {
    const src = (m.match(/\bdata-src="([^"]+)"/i) || m.match(/\bsrc="([^"]+)"/i) || [])[1] || '';
    const alt = (m.match(/\balt="([^"]*)"/i) || [])[1] || '';
    if (!src || src.startsWith('data:')) return '';
    return `\n[IMG ${src}${alt ? ' | alt: ' + alt : ''}]\n`;
  });
  h = h.replace(/<a\b[^>]*href="([^"]*)"[^>]*>([\s\S]*?)<\/a>/gi, (m, href, inner) => {
    const t = inner.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    const imgs = (inner.match(/\[IMG [^\]]+\]/g) || []).join('\n');
    if (!t && !imgs) return '';
    return `${imgs ? '\n' + imgs + '\n' : ''}${t ? `${t} <${href}>` : ''}`;
  });
  h = h.replace(/<h([1-6])\b[^>]*>/gi, (m, n) => `\n\n${'#'.repeat(+n)} `)
    .replace(/<\/h[1-6]>/gi, '\n\n')
    .replace(/<li\b[^>]*>/gi, '\n- ')
    .replace(/<(br|hr)\b[^>]*>/gi, '\n')
    .replace(/<\/(p|div|section|tr|ul|ol|table|blockquote|figure|article|header|footer|nav|form|details|summary)>/gi, '\n')
    .replace(/<(td|th)\b[^>]*>/gi, ' | ')
    .replace(/<(input|textarea|select|button)\b([^>]*)>/gi, (m, tag, attrs) => {
      const name = (attrs.match(/\bname="([^"]*)"/) || [])[1];
      const ph = (attrs.match(/\bplaceholder="([^"]*)"/) || [])[1];
      const type = (attrs.match(/\btype="([^"]*)"/) || [])[1];
      if (type === 'hidden') return '';
      return name || ph ? `\n[FIELD ${tag} name=${name || ''} type=${type || ''} placeholder=${ph || ''}]\n` : '';
    })
    .replace(/<[^>]+>/g, '');
  return decode(h).split('\n').map((l) => l.replace(/[ \t]+/g, ' ').trim()).filter((l, i, a) => l || a[i - 1]).join('\n').replace(/\n{3,}/g, '\n\n');
}

const metaOut = [];
for (const f of fs.readdirSync(RAW).filter((x) => x.endsWith('.html'))) {
  const html = fs.readFileSync(path.join(RAW, f), 'utf8');
  const meta = {
    file: f,
    title: decode((html.match(/<title>([\s\S]*?)<\/title>/i) || [])[1] || ''),
    description: decode((html.match(/<meta name="description" content="([^"]*)"/i) || [])[1] || ''),
    canonical: (html.match(/<link rel="canonical" href="([^"]*)"/i) || [])[1] || '',
    og: Object.fromEntries([...html.matchAll(/<meta property="(og:[^"]+)" content="([^"]*)"/gi)].map((m) => [m[1], decode(m[2])])),
    robots: (html.match(/<meta name='robots' content='([^']*)'/i) || [])[1] || '',
    jsonld: [...html.matchAll(/<script type="application\/ld\+json"[^>]*>([\s\S]*?)<\/script>/gi)].map((m) => m[1].trim().slice(0, 4000)),
  };
  metaOut.push(meta);
  // split header / main / footer
  const headerEnd = html.search(/<\/header>/i);
  const footerStart = html.search(/<footer\b/i);
  const bodyStart = html.search(/<body\b/i);
  const header = html.slice(bodyStart, headerEnd > 0 ? headerEnd : bodyStart);
  const main = html.slice(headerEnd > 0 ? headerEnd : bodyStart, footerStart > 0 ? footerStart : html.length);
  const footer = footerStart > 0 ? html.slice(footerStart) : '';
  fs.writeFileSync(path.join(OUT, f.replace('.html', '.txt')), toText(main));
  if (f === 'home.html') {
    fs.writeFileSync(path.join(OUT, '_header.txt'), toText(header));
    fs.writeFileSync(path.join(OUT, '_footer.txt'), toText(footer));
  }
}
fs.writeFileSync(path.join(OUT, '_meta.json'), JSON.stringify(metaOut, null, 2));
console.log('extracted', metaOut.length);
