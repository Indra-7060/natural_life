/* Sugarlif admin interactions — vanilla JS. */
(function () {
  'use strict';
  const base = document.body.dataset.base || '';
  const csrfName = document.body.dataset.csrfName || 'csrf_token';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  let csrf = ($('input[name="' + csrfName + '"]') || {}).value || ($('meta[name="X-CSRF-TOKEN"]') || {}).content || '';
  const setCsrf = (t) => { if (t) { csrf = t; $$('input[name="' + csrfName + '"]').forEach((i) => { i.value = t; }); } };

  // ------------------------------------------------------------------ sidebar (mobile)
  const sideToggle = $('#sideToggle');
  if (sideToggle) {
    sideToggle.addEventListener('click', (e) => { e.stopPropagation(); document.body.classList.toggle('side-open'); });
    document.addEventListener('click', (e) => { if (document.body.classList.contains('side-open') && !e.target.closest('#adminSide')) document.body.classList.remove('side-open'); });
  }

  // ------------------------------------------------------------------ confirmation dialog for destructive forms
  const confirmEl = $('#confirmModal');
  if (confirmEl && window.bootstrap) {
    const modal = new bootstrap.Modal(confirmEl);
    let pending = null;
    document.addEventListener('submit', (e) => {
      const form = e.target.closest('form[data-confirm]');
      if (!form || form.dataset.confirmed) return;
      e.preventDefault();
      pending = form;
      $('#confirmText').textContent = form.dataset.confirm || 'This cannot be undone.';
      $('#confirmOk').textContent = form.dataset.confirmLabel || 'Delete';
      modal.show();
    });
    $('#confirmOk').addEventListener('click', () => { if (pending) { pending.dataset.confirmed = '1'; pending.submit(); } });
  }

  // ------------------------------------------------------------------ rich text editor
  $$('[data-rte]').forEach((rte) => {
    const area = $('.rte__area', rte); const store = $('textarea', rte);
    const sync = () => { store.value = area.innerHTML.replace(/<div><br><\/div>/g, '').trim(); };
    area.addEventListener('input', sync); area.addEventListener('blur', sync);
    // Paste as plain text so Word / web formatting never reaches the site.
    area.addEventListener('paste', (e) => {
      e.preventDefault();
      const text = (e.clipboardData || window.clipboardData).getData('text/plain');
      const html = text.split(/\n{2,}/).map((p) => p.trim()).filter(Boolean).map((p) => '<p>' + p.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\n/g, '<br>') + '</p>').join('');
      document.execCommand('insertHTML', false, html || text);
      sync();
    });
    $$('button', rte).forEach((btn) => btn.addEventListener('click', () => {
      area.focus();
      if (btn.dataset.cmd) document.execCommand(btn.dataset.cmd, false, null);
      if (btn.dataset.block) document.execCommand('formatBlock', false, btn.dataset.block);
      if (btn.hasAttribute('data-link')) {
        const url = window.prompt('Link address (https://… or /page):');
        if (url && /^(https?:\/\/|\/|mailto:|tel:)/i.test(url.trim())) document.execCommand('createLink', false, url.trim());
        else if (url) window.alert('Links must start with https://, /, mailto: or tel:');
      }
      sync();
    }));
    if (rte.closest('form')) rte.closest('form').addEventListener('submit', sync);
  });

  // ------------------------------------------------------------------ image fields + media picker
  let pickerTarget = null; let pickerCallback = null;
  const pickerEl = $('#mediaPicker');
  const picker = pickerEl && window.bootstrap ? new bootstrap.Modal(pickerEl) : null;
  const grid = $('#pickerGrid'); const statusEl = $('#pickerStatus');

  function setImage(field, path, thumb) {
    const input = $('[data-image-input]', field); const preview = $('[data-image-preview]', field);
    const name = $('[data-image-name]', field); const clear = $('[data-image-clear]', field);
    input.value = path || '';
    preview.innerHTML = path ? '' : '<i class="bi bi-image"></i>';
    if (path) { const img = document.createElement('img'); img.src = thumb; img.alt = ''; preview.appendChild(img); }
    if (name) name.textContent = path || 'No image selected';
    if (clear) clear.hidden = !path;
  }

  async function loadPicker() {
    if (!grid) return;
    statusEl.textContent = 'Loading…';
    const q = encodeURIComponent($('#pickerSearch').value.trim()); const folder = encodeURIComponent($('#pickerFolder').value);
    try {
      const res = await fetch(base + '/admin/media/picker?q=' + q + '&folder=' + folder, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
      const json = await res.json();
      grid.innerHTML = '';
      json.items.forEach((item) => {
        const b = document.createElement('button'); b.type = 'button'; b.className = 'picker-item'; b.title = item.name + (item.size ? ' · ' + item.size : '');
        const img = document.createElement('img'); img.src = item.thumb; img.alt = item.name; img.loading = 'lazy'; b.appendChild(img);
        b.addEventListener('click', () => choose(item));
        grid.appendChild(b);
      });
      statusEl.textContent = json.items.length ? json.items.length + ' image(s)' + (json.more ? ' shown — refine the search to see others.' : '.') : 'No images found.';
    } catch (e) { statusEl.textContent = 'Could not load the media library.'; }
  }
  function choose(item) {
    if (pickerCallback) pickerCallback(item);
    else if (pickerTarget) setImage(pickerTarget, item.path, item.thumb);
    picker.hide();
  }
  if (picker) {
    let t;
    $('#pickerSearch').addEventListener('input', () => { clearTimeout(t); t = setTimeout(loadPicker, 220); });
    $('#pickerFolder').addEventListener('change', loadPicker);
    $('#pickerUpload').addEventListener('change', async (e) => {
      const file = e.target.files[0]; if (!file) return;
      const body = new FormData(); body.append('files[]', file); body.append('folder', $('#pickerFolder').value || 'general'); body.append(csrfName, csrf);
      statusEl.textContent = 'Uploading…';
      try {
        const res = await fetch(base + '/admin/media', { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        const json = await res.json(); setCsrf(json.csrf);
        if (json.ok && json.items[0]) choose(json.items[0]); else statusEl.textContent = json.message || 'Upload failed.';
      } catch (err) { statusEl.textContent = 'Upload failed.'; }
      e.target.value = '';
    });
  }
  document.addEventListener('click', (e) => {
    const pick = e.target.closest('[data-media-pick]');
    if (pick && picker) {
      pickerTarget = pick.closest('[data-image-field]'); pickerCallback = null;
      if (pick.dataset.mediaPickForm) { const form = $(pick.dataset.mediaPickForm); pickerCallback = (item) => { $('input[name="path"]', form).value = item.path; form.submit(); }; }
      if (pick.dataset.mediaPickInput) { const input = $(pick.dataset.mediaPickInput) || pick.parentElement.querySelector('input'); pickerCallback = (item) => { input.value = item.path; }; }
      loadPicker(); picker.show();
    }
    const clear = e.target.closest('[data-image-clear]');
    if (clear) { const field = clear.closest('[data-image-field]'); setImage(field, '', ''); const up = $('[data-image-upload]', field); if (up) up.value = ''; }
  });
  document.addEventListener('change', (e) => {
    const up = e.target.closest('[data-image-upload]');
    if (!up || !up.files[0]) return;
    const field = up.closest('[data-image-field]');
    const preview = $('[data-image-preview]', field); const name = $('[data-image-name]', field);
    preview.innerHTML = ''; const img = document.createElement('img'); img.src = URL.createObjectURL(up.files[0]); preview.appendChild(img);
    if (name) name.textContent = up.files[0].name + ' — will be uploaded when you save';
  });

  // ------------------------------------------------------------------ repeaters (variations, specifications, section lists)
  $$('[data-repeater]').forEach((rep) => {
    const list = $('[data-rep-list]', rep); const tpl = $('template', rep);
    let n = $$('[data-rep-row]', list).length;
    const add = $('[data-rep-add]', rep);
    if (add) add.addEventListener('click', () => {
      const html = tpl.innerHTML.replace(/__i__/g, 'n' + (n++));
      const wrap = document.createElement('div'); wrap.innerHTML = html.trim();
      const row = wrap.firstElementChild; list.appendChild(row);
      const first = $('input,textarea,select', row); if (first) first.focus();
    });
    rep.addEventListener('click', (e) => {
      const rm = e.target.closest('[data-rep-remove]'); if (rm) rm.closest('[data-rep-row]').remove();
      const up = e.target.closest('[data-rep-up]'); if (up) { const row = up.closest('[data-rep-row]'); if (row.previousElementSibling) list.insertBefore(row, row.previousElementSibling); }
      const down = e.target.closest('[data-rep-down]'); if (down) { const row = down.closest('[data-rep-row]'); if (row.nextElementSibling) list.insertBefore(row.nextElementSibling, row); }
    });
  });

  // ------------------------------------------------------------------ product type switch (single pack / pack sizes)
  const typeSel = $('[data-product-type]');
  if (typeSel) {
    const sync = () => { $$('[data-show-type]').forEach((el) => { el.hidden = el.dataset.showType !== typeSel.value; }); };
    typeSel.addEventListener('change', sync); sync();
  }

  // ------------------------------------------------------------------ misc
  $$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));
  $$('[data-count-for]').forEach((el) => {
    const input = document.getElementById(el.dataset.countFor); if (!input) return;
    const sync = () => { el.textContent = input.value.length + ' characters'; };
    input.addEventListener('input', sync); sync();
  });
  // Warn before leaving a form with unsaved changes.
  $$('form[data-guard]').forEach((form) => {
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  });
  // ------------------------------------------------------------------ motion: staggered entrance + two-way reveal on scroll
  // Every panel, card and media tile fades and rises in when it enters the viewport and resets
  // once it has left completely, so it animates again from whichever side it comes back.
  // A block is never hidden while it holds keyboard focus or while a dialog is open, so a form
  // being filled in cannot disappear.
  (function () {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    $$('.a-nav > *').forEach((el, i) => el.style.setProperty('--i', i));
    if (reduced) return;
    const blocks = $$('.a-content .panel, .a-content .kpi, .a-content .media-card')
      .filter((el) => !el.closest('.modal') && !el.parentElement.closest('.panel'));
    if (!blocks.length) return;

    blocks.forEach((el) => el.classList.add('a-reveal'));
    void document.body.offsetWidth;                       // commit the hidden state so the first reveal animates

    let first = true;
    const check = () => {
      const vh = window.innerHeight;
      const busy = !!document.querySelector('.modal.show');
      let n = 0;
      blocks.forEach((el) => {
        const r = el.getBoundingClientRect();
        if (r.width === 0 && r.height === 0) return;
        const isIn = el.classList.contains('is-in');
        if (!isIn && r.top < vh * 0.94 && r.bottom > vh * 0.03) {
          if (first) { el.style.transitionDelay = Math.min(n++, 10) * 60 + 'ms'; setTimeout(() => { el.style.transitionDelay = ''; }, 1400); }
          el.classList.add('is-in');
        } else if (isIn && !busy && (r.bottom < -80 || r.top > vh + 80) && !el.contains(document.activeElement)) {
          el.style.setProperty('--ry', r.bottom < 0 ? '-26px' : '26px');   // come back from the side it left
          el.classList.remove('is-in');
        }
      });
      first = false;
    };
    window.addEventListener('scroll', check, { passive: true });
    window.addEventListener('resize', check);
    window.addEventListener('load', check);
    window.addEventListener('hashchange', () => setTimeout(check, 60));   // #gallery, #sections links
    document.addEventListener('focusin', check);                            // tabbing to a field in a hidden block shows it
    check();
  })();
})();
