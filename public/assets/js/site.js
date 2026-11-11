/* Sugarlif storefront interactions — vanilla JS, no build step. */
(function () {
  'use strict';

  const base = document.body.dataset.base || '';
  const csrfName = document.body.dataset.csrfName || 'csrf_token';
  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const money = (n) => '₹' + Number(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });

  // ------------------------------------------------------------------ CSRF + fetch helper
  let csrf = ($('input[name="' + csrfName + '"]') || {}).value || ($('meta[name="X-CSRF-TOKEN"]') || {}).content || '';
  function setCsrf(token) {
    if (!token) return;
    csrf = token;
    $$('input[name="' + csrfName + '"]').forEach((i) => { i.value = token; });
  }
  async function post(path, data) {
    const body = data instanceof FormData ? data : new URLSearchParams(data || {});
    body.set(csrfName, csrf);
    const res = await fetch(base + path, {
      method: 'POST', body, credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    });
    let json = {};
    try { json = await res.json(); } catch (e) { json = { ok: false, message: 'Something went wrong. Please refresh the page and try again.' }; }
    setCsrf(json.csrf);
    if (!res.ok && json.ok !== false) json.ok = false;
    return json;
  }

  // ------------------------------------------------------------------ toasts
  function toast(message, type) {
    const stack = $('#toasts');
    if (!stack || !message) return;
    const el = document.createElement('div');
    el.className = 'toast-msg' + (type === 'error' ? ' toast-msg--error' : '');
    el.setAttribute('role', type === 'error' ? 'alert' : 'status');
    el.innerHTML = '<i class="bi bi-' + (type === 'error' ? 'exclamation-circle' : 'check2-circle') + '"></i><span></span>';
    el.querySelector('span').textContent = message;
    stack.appendChild(el);
    setTimeout(() => { el.classList.add('is-leaving'); setTimeout(() => el.remove(), 320); }, 3600);
  }

  // ------------------------------------------------------------------ header: sticky shadow, search, keyboard mega menu
  const header = $('#siteHeader');
  if (header) {
    const onScroll = () => header.classList.toggle('is-stuck', window.scrollY > 8);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    $$('.has-panel > .main-nav__link', header).forEach((link) => {
      link.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === ' ') { e.preventDefault(); link.parentElement.classList.add('is-open'); const first = $('.nav-panel a', link.parentElement); if (first) first.focus(); }
      });
      link.parentElement.addEventListener('keydown', (e) => { if (e.key === 'Escape') { link.parentElement.classList.remove('is-open'); link.focus(); } });
      link.parentElement.addEventListener('focusout', (e) => { if (!link.parentElement.contains(e.relatedTarget)) link.parentElement.classList.remove('is-open'); });
    });
  }

  const searchToggle = $('#searchToggle');
  const searchPanel = $('#searchPanel');
  if (searchToggle && searchPanel) {
    const input = $('#siteSearch');
    const results = $('#searchResults');
    const open = (state) => {
      searchPanel.classList.toggle('is-open', state);
      searchToggle.setAttribute('aria-expanded', state ? 'true' : 'false');
      if (state) setTimeout(() => input.focus(), 30);
    };
    searchToggle.addEventListener('click', () => open(!searchPanel.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') open(false); });
    document.addEventListener('click', (e) => { if (!searchPanel.contains(e.target) && !searchToggle.contains(e.target)) open(false); });

    let timer; let seq = 0;
    input.addEventListener('input', () => {
      clearTimeout(timer);
      const q = input.value.trim();
      if (q.length < 2) { results.innerHTML = ''; return; }
      timer = setTimeout(async () => {
        const mine = ++seq;
        try {
          const res = await fetch(base + '/shop/suggest?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const json = await res.json();
          if (mine !== seq) return;
          results.innerHTML = '';
          if (!json.items.length) {
            const p = document.createElement('p'); p.className = 'search-hint'; p.textContent = 'No products match “' + q + '”.'; results.appendChild(p); return;
          }
          json.items.forEach((item) => {
            const a = document.createElement('a'); a.className = 'search-result'; a.href = item.url;
            const img = document.createElement('img'); img.src = item.image; img.alt = '';
            const box = document.createElement('div');
            const strong = document.createElement('strong'); strong.textContent = item.name;
            const span = document.createElement('span'); span.textContent = item.price;
            box.append(strong, span); a.append(img, box); results.appendChild(a);
          });
        } catch (e) { /* network hiccup: leave the previous results */ }
      }, 180);
    });
  }

  // ------------------------------------------------------------------ cart
  const drawerEl = $('#cartDrawer');
  const drawerBody = $('#cartDrawerBody');
  let drawer;
  const getDrawer = () => drawer || (drawer = window.bootstrap ? new bootstrap.Offcanvas(drawerEl) : null);

  function applyCart(json) {
    if (!json) return;
    if (typeof json.count !== 'undefined') {
      $$('[data-cart-count]').forEach((el) => { el.textContent = json.count; el.dataset.count = json.count; });
    }
    if (json.drawer && drawerBody) drawerBody.innerHTML = json.drawer;
  }
  async function refreshDrawer() {
    try {
      const res = await fetch(base + '/cart/drawer', { headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }, credentials: 'same-origin' });
      const json = await res.json(); setCsrf(json.csrf); applyCart(json);
    } catch (e) { /* ignore */ }
  }
  function openDrawer() { const d = getDrawer(); if (d) d.show(); }
  const onCartPage = !!$('[data-cart-page]');

  document.addEventListener('click', (e) => {
    const opener = e.target.closest('[data-cart-open]');
    if (opener) {
      if (onCartPage) return;
      e.preventDefault(); refreshDrawer(); openDrawer();
    }
  });

  document.addEventListener('submit', async (e) => {
    const form = e.target.closest('[data-add-to-cart]');
    if (!form) return;
    e.preventDefault();
    const btn = $('[type="submit"]', form);
    if (btn) btn.disabled = true;
    const json = await post('/cart/add', new FormData(form));
    if (btn) btn.disabled = false;
    if (json.ok) {
      applyCart(json);
      const modal = form.closest('.modal');
      if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).hide();
      if (onCartPage) { window.location.reload(); } else { openDrawer(); }
    } else {
      toast(json.message || 'Could not add this item.', 'error');
    }
  });

  async function changeQty(itemId, qty) {
    const json = await post('/cart/update', { item_id: itemId, quantity: qty });
    if (onCartPage) { window.location.reload(); return; }
    applyCart(json);
  }
  document.addEventListener('click', async (e) => {
    const step = e.target.closest('[data-qty-step]');
    if (step) {
      const wrap = step.closest('[data-qty]'); const input = $('input', wrap);
      const next = Math.max(wrap.dataset.item ? 0 : 1, Math.min(20, (parseInt(input.value, 10) || 1) + parseInt(step.dataset.qtyStep, 10)));
      input.value = Math.max(1, next);
      if (wrap.dataset.item) changeQty(wrap.dataset.item, next);
      return;
    }
    const remove = e.target.closest('[data-cart-remove]');
    if (remove) {
      const json = await post('/cart/remove', { item_id: remove.dataset.cartRemove });
      if (onCartPage) { window.location.reload(); return; }
      applyCart(json);
    }
  });
  document.addEventListener('change', (e) => {
    const input = e.target.closest('[data-qty] input');
    if (!input) return;
    const wrap = input.closest('[data-qty]');
    const val = Math.max(1, Math.min(20, parseInt(input.value, 10) || 1));
    input.value = val;
    if (wrap.dataset.item) changeQty(wrap.dataset.item, val);
  });

  // ------------------------------------------------------------------ wishlist
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-wishlist]');
    if (!btn) return;
    e.preventDefault();
    const json = await post('/wishlist/toggle', { product_id: btn.dataset.wishlist });
    if (!json.ok) { toast(json.message || 'Could not update your wishlist.', 'error'); return; }
    $$('[data-wishlist="' + btn.dataset.wishlist + '"]').forEach((b) => {
      b.classList.toggle('is-on', json.added); b.setAttribute('aria-pressed', json.added ? 'true' : 'false');
      const label = $('[data-wishlist-label]', b); if (label) label.textContent = json.added ? 'Saved' : 'Save';
    });
    $$('[data-wishlist-count]').forEach((el) => { el.textContent = json.count; el.dataset.count = json.count; });
    toast(json.message);
    if (btn.closest('[data-wishlist-page]') && !json.added) { const card = btn.closest('[data-wishlist-item]'); if (card) card.remove(); if (!$('[data-wishlist-item]')) window.location.reload(); }
  });

  // ------------------------------------------------------------------ quick view
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-quick-view]');
    if (!btn || !window.bootstrap) return;
    e.preventDefault();
    const body = $('#quickViewBody');
    body.innerHTML = '<div class="empty-state"><div class="spinner-border text-secondary" role="status"></div></div>';
    bootstrap.Modal.getOrCreateInstance($('#quickView')).show();
    try {
      const res = await fetch(base + '/shop/quick-view/' + encodeURIComponent(btn.dataset.quickView), { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
      body.innerHTML = res.ok ? await res.text() : '<div class="empty-state"><p>This product could not be loaded.</p></div>';
      setCsrf(csrf);
      initBuyBoxes(body);
    } catch (err) { body.innerHTML = '<div class="empty-state"><p>This product could not be loaded.</p></div>'; }
  });

  // ------------------------------------------------------------------ product buy box: variations + gallery
  function initBuyBoxes(root) {
    $$('[data-buybox]', root || document).forEach((box) => {
      if (box.dataset.ready) return; box.dataset.ready = '1';
      const priceEl = $('[data-price]', box); const wasEl = $('[data-price-was]', box); const saveEl = $('[data-price-save]', box);
      const labelEl = $('[data-selected-label]', box); const hidden = $('input[name="variation_id"]', box);
      const submit = $('[data-buy-submit]', box); const stockEl = $('[data-stock]', box); const unitEl = $('[data-unit-note]', box);
      const radios = $$('input[data-variation]', box);
      const select = (r) => {
        const d = r.dataset; const price = parseFloat(d.price); const regular = parseFloat(d.regular);
        if (hidden) hidden.value = d.variation;
        if (priceEl) priceEl.textContent = money(price);
        if (wasEl) { wasEl.textContent = regular > price ? money(regular) : ''; wasEl.hidden = !(regular > price); }
        if (saveEl) { saveEl.textContent = regular > price ? 'Save ' + Math.round((1 - price / regular) * 100) + '%' : ''; saveEl.hidden = !(regular > price); }
        if (labelEl) labelEl.textContent = d.label;
        if (unitEl) { const kg = parseFloat(d.label); unitEl.textContent = (kg > 0 && /kg/i.test(d.label)) ? money(Math.round(price / kg * 100) / 100) + ' per kg' : ''; }
        const inStock = d.stock === 'in_stock';
        if (stockEl) { stockEl.textContent = inStock ? 'In stock' : 'Out of stock'; stockEl.classList.toggle('stock--out', !inStock); }
        if (submit) { submit.disabled = !inStock; $('[data-buy-label]', submit).textContent = inStock ? 'Add to cart' : 'Out of stock'; }
      };
      radios.forEach((r) => r.addEventListener('change', () => select(r)));
      const checked = radios.find((r) => r.checked); if (checked) select(checked);
    });
    $$('[data-gallery]', root || document).forEach((g) => {
      if (g.dataset.ready) return; g.dataset.ready = '1';
      const main = $('[data-gallery-main]', g);
      $$('[data-gallery-thumb]', g).forEach((t) => t.addEventListener('click', () => {
        main.src = t.dataset.full; main.alt = t.dataset.alt || '';
        $$('[data-gallery-thumb]', g).forEach((x) => { x.classList.toggle('is-active', x === t); x.setAttribute('aria-current', x === t ? 'true' : 'false'); });
      }));
    });
  }
  initBuyBoxes();

  // ------------------------------------------------------------------ hero carousel
  $$('[data-hero]').forEach((hero) => {
    const slides = $$('[data-hero-slide]', hero); const imgs = $$('[data-hero-img]', hero); const dots = $$('[data-hero-dot]', hero);
    const current = $('[data-hero-current]', hero); const pauseBtn = $('[data-hero-pause]', hero);
    if (slides.length < 2) return;
    let i = 0; let timer = null; let paused = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    // Slides scroll sideways: forward moves right-to-left, going back moves left-to-right.
    // CSS positions: default = waiting on the right, .is-left = parked on the left, .is-active = in view.
    const place = (el, side) => {                       // put a waiting slide on one side without animating
      el.classList.add('no-anim');
      el.classList.toggle('is-left', side === 'left');
      void el.offsetWidth;
      el.classList.remove('no-anim');
    };
    const show = (n) => {
      const from = i;
      const to = (n + slides.length) % slides.length;
      if (to === from) return;
      const back = n < from;                             // previous / earlier line clicked
      i = to;
      [slides, imgs].forEach((set) => {
        set.forEach((el, k) => { if (k !== from && k !== to) place(el, 'right'); });
        place(set[to], back ? 'left' : 'right');         // entering slide starts on the side it comes from
        set[to].classList.add('is-active');
        set[from].classList.remove('is-active');
        set[from].classList.toggle('is-left', !back);    // leaving slide exits on the opposite side
      });
      slides.forEach((s, k) => { s.toggleAttribute('aria-hidden', k !== i); $$('a,button', s).forEach((a) => { a.tabIndex = k === i ? 0 : -1; }); });
      dots.forEach((d, k) => { d.classList.remove('is-active'); d.setAttribute('aria-selected', k === i ? 'true' : 'false'); });
      // restart the progress line on the next frame so the CSS transition replays
      requestAnimationFrame(() => requestAnimationFrame(() => dots[i].classList.add('is-active')));
      if (current) current.textContent = String(i + 1).padStart(2, '0');
    };
    const play = () => { clearInterval(timer); if (!paused) timer = setInterval(() => show(i + 1), 6500); hero.classList.toggle('is-paused', paused); };
    dots.forEach((d) => d.addEventListener('click', () => { show(parseInt(d.dataset.heroDot, 10)); play(); }));
    const prevBtn = $('[data-hero-prev]', hero); const nextBtn = $('[data-hero-next]', hero);
    if (prevBtn) prevBtn.addEventListener('click', () => { show(i - 1); play(); });
    if (nextBtn) nextBtn.addEventListener('click', () => { show(i + 1); play(); });
    // swipe on touch screens
    let touchX = null;
    hero.addEventListener('touchstart', (e) => { touchX = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', (e) => { if (touchX === null) return; const dx = e.changedTouches[0].clientX - touchX; touchX = null; if (Math.abs(dx) > 50) { show(i + (dx < 0 ? 1 : -1)); play(); } }, { passive: true });
    if (pauseBtn) {
      const sync = () => { pauseBtn.innerHTML = '<i class="bi bi-' + (paused ? 'play' : 'pause') + '"></i>'; pauseBtn.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow'); };
      pauseBtn.addEventListener('click', () => { paused = !paused; sync(); play(); }); sync();
    }
    // The slideshow keeps scrolling on its own; it only holds still while keyboard focus is inside it
    // (so a link being tabbed to does not slide away).
    const hold = (state) => { if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return; paused = state; play(); };
    hero.addEventListener('focusin', () => hold(true));
    hero.addEventListener('focusout', () => hold(false));
    document.addEventListener('visibilitychange', () => { if (document.hidden) clearInterval(timer); else play(); });
    play();
  });

  // ------------------------------------------------------------------ testimonials
  $$('[data-quotes]').forEach((wrap) => {
    const quotes = $$('[data-quote]', wrap); const current = $('[data-quotes-current]', wrap);
    if (quotes.length < 2) return;
    let i = 0;
    const show = (n) => {
      i = (n + quotes.length) % quotes.length;
      quotes.forEach((q, k) => { q.classList.toggle('is-active', k === i); q.toggleAttribute('aria-hidden', k !== i); });
      if (current) current.textContent = i + 1;
    };
    const prev = $('[data-quotes-prev]', wrap); const next = $('[data-quotes-next]', wrap);
    if (prev) prev.addEventListener('click', () => show(i - 1));
    if (next) next.addEventListener('click', () => show(i + 1));
  });

  // ------------------------------------------------------------------ GI scale
  $$('[data-gi]').forEach((gi) => {
    const note = $('[data-gi-note]', gi);
    $$('[data-gi-band]', gi).forEach((band) => {
      const activate = () => {
        $$('[data-gi-band]', gi).forEach((b) => { b.classList.toggle('is-active', b === band); b.setAttribute('aria-pressed', b === band ? 'true' : 'false'); });
        $$('[data-gi-curve]', gi).forEach((c) => c.classList.toggle('is-active', c.dataset.giCurve === band.dataset.giBand));
        if (note) note.textContent = band.dataset.giText;
      };
      band.addEventListener('click', activate); band.addEventListener('mouseenter', activate); band.addEventListener('focus', activate);
    });
  });

  // ------------------------------------------------------------------ newsletter
  $$('[data-newsletter]').forEach((form) => {
    const note = form.parentElement.querySelector('[data-newsletter-note]');
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const json = await post('/newsletter/subscribe', new FormData(form));
      if (note) { note.textContent = json.message || ''; note.className = 'form-note ' + (json.ok ? 'is-ok' : 'is-err'); }
      if (json.ok) form.reset();
    });
  });

  // ------------------------------------------------------------------ shop filters
  $$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));
  const filterToggle = $('[data-filter-toggle]');
  if (filterToggle) filterToggle.addEventListener('click', () => { const f = $('.filters'); const open = f.classList.toggle('is-open'); filterToggle.setAttribute('aria-expanded', open ? 'true' : 'false'); });

  // ------------------------------------------------------------------ FAQ search
  const faqSearch = $('[data-faq-search]');
  if (faqSearch) {
    const empty = $('[data-faq-empty]');
    faqSearch.addEventListener('input', () => {
      const q = faqSearch.value.trim().toLowerCase(); let shown = 0;
      $$('[data-faq-item]').forEach((item) => { const hit = !q || item.textContent.toLowerCase().includes(q); item.hidden = !hit; if (hit) shown++; });
      if (empty) empty.hidden = shown > 0;
    });
  }

  // ------------------------------------------------------------------ checkout: saved address + billing toggle
  $$('[data-address-pick]').forEach((radio) => radio.addEventListener('change', () => {
    let data = {}; try { data = JSON.parse(radio.dataset.addressPick); } catch (e) { return; }
    Object.keys(data).forEach((k) => { const f = $('[name="' + k + '"]'); if (f) f.value = data[k]; });
  }));
  const billingSame = $('#billingSame');
  if (billingSame) {
    const block = $('#billingFields');
    const sync = () => { block.hidden = billingSame.checked; $$('input,select', block).forEach((f) => { f.disabled = billingSame.checked; }); };
    billingSame.addEventListener('change', sync); sync();
  }

  // ------------------------------------------------------------------ table of contents highlight
  const tocLinks = $$('[data-toc] a');
  if (tocLinks.length && 'IntersectionObserver' in window) {
    const map = new Map(tocLinks.map((a) => [a.getAttribute('href').slice(1), a]));
    const io = new IntersectionObserver((entries) => entries.forEach((en) => {
      if (en.isIntersecting) { tocLinks.forEach((a) => a.classList.remove('is-active')); const a = map.get(en.target.id); if (a) a.classList.add('is-active'); }
    }), { rootMargin: '-30% 0px -60% 0px' });
    map.forEach((a, id) => { const el = document.getElementById(id); if (el) io.observe(el); });
  }

  // ------------------------------------------------------------------ motion: reveal (both directions), scroll zoom, progress bar
  // Everything is driven from one scroll/resize handler, so content is revealed as soon as it is
  // on screen and animates again when it comes back from either direction.
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  // auto-tag: inner pages get the same motion as the home page without marking up every view.
  // Blocks become single reveals, lists become staggered groups, photographs get scroll zoom.
  // Anything already inside a reveal is left alone, and nothing is tagged when motion is reduced.
  if (!reduced) {
    const scope = $('#main');
    const free = (el) => !el.classList.contains('reveal') && !el.classList.contains('reveal-group') && !el.parentElement.closest('.reveal, .reveal-group');
    const groups = '.product-grid, .team-lead, .team-rest, .index-list, .app-list, .tick-list, .numbered, .benefit-cards, .blog-list, .contact-list, .stat-row, .cert-list, .assure, .faq, .cat-list, .kpi-row, .pair';
    const singles = '.section-head, .chapter, .edu-block > *, .figure-card, .form-card, .cta-band, .cta-product, .table-wrap, .ledger, .post-lead, .post-row, .article, .legal > article, .auth, .shop-bar, .cart-table .line, .step, .address-card, .kpi, .data-table, .timeline, .empty-state, .split > *, .contact-grid > *, .pdp > .buybox, .details, .framed';
    if (scope) {
      $$(groups, scope).forEach((el) => { if (free(el)) el.classList.add('reveal-group'); });
      $$(singles, scope).forEach((el, i) => {
        if (!free(el) || el.closest('.page-hero, .herox')) return;
        el.classList.add('reveal');
        // alternate the direction of the two halves of a split layout
        if (el.parentElement.classList.contains('split') || el.parentElement.classList.contains('contact-grid')) {
          el.classList.add(el === el.parentElement.firstElementChild ? 'reveal--left' : 'reveal--right');
        }
      });
      // photographs: gentle zoom while scrolling
      $$('.framed', scope).forEach((el) => {
        const img = $(':scope > img', el);
        if (img) { const clip = document.createElement('span'); clip.className = 'framed__clip'; img.replaceWith(clip); clip.appendChild(img); }
        el.setAttribute('data-zoom', '');
      });
      $$('.person__img, .gallery__main, .quick__media', scope).forEach((el) => el.setAttribute('data-zoom', ''));
    }
  }
  const reveals = $$('.reveal, .reveal-group');
  const zoomEls = $$('[data-zoom]');
  const bar = $('.scroll-progress');
  const toTop = $('[data-to-top]');
  if (reduced) reveals.forEach((el) => el.classList.add('is-in', 'is-done'));

  let ticking = false;
  function onFrame() {
    ticking = false;
    const vh = window.innerHeight;
    if (!reduced) {
      reveals.forEach((el) => {
        const r = el.getBoundingClientRect();
        if (r.width === 0 && r.height === 0) return;                      // hidden (e.g. inside a closed panel)
        const isIn = el.classList.contains('is-in');
        if (!isIn && r.top < vh * 0.9 && r.bottom > vh * 0.04) {
          el.classList.add('is-in');
          if (el.classList.contains('reveal-group')) setTimeout(() => { if (el.classList.contains('is-in')) el.classList.add('is-done'); }, 1500);
        } else if (isIn && (r.bottom < -80 || r.top > vh + 80)) {
          el.style.setProperty('--ry', r.bottom < 0 ? '-34px' : '34px');     // come back from the side it left
          el.classList.remove('is-in', 'is-done');
        }
      });
      zoomEls.forEach((el) => {
        const r = el.getBoundingClientRect();
        if (r.bottom < -80 || r.top > vh + 80) return;
        const p = Math.min(1, Math.max(0, (vh - r.top) / (vh + r.height)));      // 0 entering at the bottom, 1 leaving at the top
        const z = el.dataset.zoom === 'in' ? 1 + 0.14 * p : 1.14 - 0.14 * p;         // zoom in, or settle out
        el.style.setProperty('--z', z.toFixed(4));
      });
    }
    const max = document.documentElement.scrollHeight - vh;
    if (bar) bar.style.setProperty('--p', max > 0 ? (window.scrollY / max).toFixed(4) : 0);
    if (toTop) toTop.classList.toggle('is-on', window.scrollY > vh * 0.9);
  }
  // Normally runs on the next animation frame; the timer is a safety net for when the browser
  // pauses animation frames (background tab), so content can never be left hidden.
  const requestTick = () => {
    if (ticking) return;
    ticking = true;
    let ran = false;
    const run = () => { if (!ran) { ran = true; onFrame(); } };
    requestAnimationFrame(run);
    setTimeout(run, 150);
  };
  window.addEventListener('scroll', requestTick, { passive: true });
  window.addEventListener('resize', requestTick);
  window.addEventListener('load', onFrame);
  document.addEventListener('shown.bs.collapse', onFrame);
  onFrame();
  if (toTop) toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }));
})();

/* footer link groups: closed on phones (tap to open), always open on larger screens */
(() => {
  const groups = document.querySelectorAll('.footer-col');
  if (!groups.length) return;
  const phone = window.matchMedia('(max-width: 575.98px)');
  const sync = () => groups.forEach((g) => {
    g.open = !phone.matches;
    const s = g.querySelector('summary');
    if (s) s.tabIndex = phone.matches ? 0 : -1;
  });
  sync();
  phone.addEventListener('change', sync);
})();
