/* ==========================================================================
   Corkboard — a pin-and-string organization board
   Storage: uses api.php (server) when available, otherwise IndexedDB (browser).
   ========================================================================== */
(() => {
'use strict';
// Demo mode: set window.CORKBOARD_DEMO = true before this script to run without a server
// (no outbound previews, no downloads, in-page document viewer).
const DEMO = !!window.CORKBOARD_DEMO;

/* ---------------------------------------------------------------- utils */
const $ = (s, r = document) => r.querySelector(s);
const uid = () => Date.now().toString(36) + Math.random().toString(36).slice(2, 9);
const clamp = (v, a, b) => Math.min(b, Math.max(a, v));
const rand = (a, b) => a + Math.random() * (b - a);
const pick = a => a[Math.floor(Math.random() * a.length)];
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const cssColor = (c, d) => (typeof c === 'string' && /^#[0-9a-f]{3,8}$/i.test(c)) ? c : d;
function safeUrl(u, allowDataImage) {
  if (!u || typeof u !== 'string') return '';
  if (allowDataImage && /^data:image\/(png|jpe?g|gif|webp|avif);/i.test(u)) return u;
  try {
    const x = new URL(u, location.href);
    if (x.protocol === 'http:' || x.protocol === 'https:' || x.protocol === 'blob:') return x.href;
  } catch { /* ignore */ }
  return '';
}
function hostOf(u) { try { return new URL(u).hostname.replace(/^www\./, ''); } catch { return ''; } }
function fmtBytes(n) {
  if (!n && n !== 0) return '';
  const u = ['B', 'KB', 'MB', 'GB']; let i = 0;
  while (n >= 1024 && i < u.length - 1) { n /= 1024; i++; }
  return (i ? n.toFixed(n < 10 ? 1 : 0) : n) + ' ' + u[i];
}
function timeAgo(t) {
  if (!t) return '';
  const s = (Date.now() - t) / 1000;
  if (s < 60) return 'just now';
  if (s < 3600) return Math.floor(s / 60) + 'm ago';
  if (s < 86400) return Math.floor(s / 3600) + 'h ago';
  if (s < 86400 * 30) return Math.floor(s / 86400) + 'd ago';
  return new Date(t).toLocaleDateString();
}
async function fetchJSON(url, opts = {}, ms = 8000) {
  const ctl = new AbortController(); const timer = setTimeout(() => ctl.abort(), ms);
  try {
    const r = await fetch(url, { ...opts, signal: ctl.signal });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return await r.json();
  } finally { clearTimeout(timer); }
}
const blobToDataURL = b => new Promise((res, rej) => { const fr = new FileReader(); fr.onload = () => res(fr.result); fr.onerror = () => rej(fr.error); fr.readAsDataURL(b); });
const dataURLToBlob = async d => (await fetch(d)).blob();
const loadImg = src => new Promise((res, rej) => { const i = new Image(); i.onload = () => res(i); i.onerror = () => rej(new Error('Image failed to load')); i.src = src; });
const extOf = name => { const m = /\.([a-z0-9]{1,6})$/i.exec(name || ''); return m ? m[1].toLowerCase() : ''; };

/* ---------------------------------------------------------------- icons */
const ICONS = {
  menu: 'M4 6h16M4 12h16M4 18h16',
  note: 'M4 4h16v11l-5 5H4zM15 20v-5h5',
  label: 'M3 12V4h8l10 10-8 8zM7.5 7.5h.01',
  image: 'M3 5h18v14H3zM3 16l5-5 4 4 3-3 6 6M15.5 9.5h.01',
  doc: 'M6 2h9l5 5v15H6zM14 2v6h6M9 13h8M9 17h6',
  link: 'M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1',
  undo: 'M9 14L4 9l5-5M4 9h11a5 5 0 0 1 0 10h-3',
  redo: 'M15 14l5-5-5-5M20 9H9a5 5 0 0 0 0 10h3',
  minus: 'M5 12h14', plus: 'M12 5v14M5 12h14', x: 'M6 6l12 12M18 6L6 18',
  upload: 'M12 16V4M7 9l5-5 5 5M4 20h16', download: 'M12 4v12M7 11l5 5 5-5M4 20h16',
  dots: 'M5 12h.01M12 12h.01M19 12h.01',
  edit: 'M4 20h4L19 9l-4-4L4 16zM14 6l4 4',
  open: 'M14 4h6v6M20 4l-9 9M18 14v6H4V6h6',
  string: 'M5 7c3 13 11 13 14 0M5 7h.01M19 7h.01',
  front: 'M12 20V8M6 14l6-6 6 6M5 4h14', forward: 'M12 19V5M6 11l6-6 6 6',
  backward: 'M12 5v14M6 13l6 6 6-6', back: 'M12 4v12M6 10l6 6 6-6M5 20h14',
  straight: 'M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5',
  copy: 'M8 8h12v12H8zM16 8V4H4v12h4',
  trash: 'M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13',
  refresh: 'M20 11A8 8 0 0 0 6.3 6.3L4 8.5M4 4v4.5h4.5M4 13a8 8 0 0 0 13.7 4.7L20 15.5M20 20v-4.5h-4.5',
  caption: 'M4 6h16M4 12h16M4 18h9',
  play: 'M7 4l13 8-13 8z', stop: 'M6 6h12v12H6z', eye: 'M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zM12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6z',
};
const icon = n => `<svg class="ico" viewBox="0 0 24 24" aria-hidden="true"><path d="${ICONS[n] || ''}"/></svg>`;
function iconize(root = document) {
  root.querySelectorAll('[data-icon]').forEach(el => {
    if (!el.querySelector(':scope > svg.ico')) el.insertAdjacentHTML('afterbegin', icon(el.dataset.icon));
  });
}

/* ---------------------------------------------------------------- storage */
const summarize = b => ({ id: b.id, name: b.name, updated: b.updated || b.created || 0, count: (b.items || []).length });

const LocalStore = {
  kind: 'local', db: null, urls: new Map(),
  async init() {
    this.db = await new Promise((res, rej) => {
      const r = indexedDB.open('corkboard', 1);
      r.onupgradeneeded = () => {
        const db = r.result;
        if (!db.objectStoreNames.contains('boards')) db.createObjectStore('boards', { keyPath: 'id' });
        if (!db.objectStoreNames.contains('files')) db.createObjectStore('files', { keyPath: 'id' });
      };
      r.onsuccess = () => res(r.result);
      r.onerror = () => rej(r.error);
    });
  },
  tx(name, mode, fn) {
    return new Promise((res, rej) => {
      const t = this.db.transaction(name, mode); const s = t.objectStore(name); let out;
      const q = fn(s); if (q) q.onsuccess = () => { out = q.result; };
      t.oncomplete = () => res(out); t.onerror = () => rej(t.error); t.onabort = () => rej(t.error || new Error('Storage aborted (quota?)'));
    });
  },
  async listBoards() { return (await this.tx('boards', 'readonly', s => s.getAll())).map(summarize); },
  loadBoard(id) { return this.tx('boards', 'readonly', s => s.get(id)); },
  saveBoard(b) { const copy = JSON.parse(JSON.stringify(b)); return this.tx('boards', 'readwrite', s => s.put(copy)); },
  deleteBoard(id) { return this.tx('boards', 'readwrite', s => s.delete(id)); },
  async putFile(blob, name) {
    const id = uid();
    await this.tx('files', 'readwrite', s => s.put({ id, blob, name: name || '', type: blob.type || '' }));
    return 'idb:' + id;
  },
  async getBlob(ref) { const rec = await this.tx('files', 'readonly', s => s.get(ref.slice(4))); return rec ? rec.blob : null; },
  async fileUrl(ref) {
    if (this.urls.has(ref)) return this.urls.get(ref);
    const blob = await this.getBlob(ref); if (!blob) return '';
    const u = URL.createObjectURL(blob); this.urls.set(ref, u); return u;
  },
  async gc(liveRefs) {
    const keys = await this.tx('files', 'readonly', s => s.getAllKeys());
    const cutoff = Date.now() - 3600e3;
    for (const k of keys) {
      const born = parseInt(String(k).slice(0, -7), 36) || 0;
      if (!liveRefs.has('idb:' + k) && born < cutoff) await this.tx('files', 'readwrite', s => s.delete(k));
    }
  },
};

const ServerStore = {
  kind: 'server', api: 'api.php',
  async call(action, { method = 'GET', query = {}, json, form } = {}) {
    const q = new URLSearchParams({ action, ...query });
    const opts = { method, headers: { 'X-Corkboard': '1' }, cache: 'no-store' };
    if (json !== undefined) { opts.body = JSON.stringify(json); opts.headers['Content-Type'] = 'application/json'; }
    if (form) opts.body = form;
    const r = await fetch(this.api + '?' + q, opts);
    let data;
    try { data = await r.json(); } catch { throw new Error('Unexpected server response (' + r.status + ')'); }
    if (!r.ok || data.ok === false) throw new Error(data.error || 'HTTP ' + r.status);
    return data;
  },
  async listBoards() { return (await this.call('list')).boards; },
  async loadBoard(id) { return (await this.call('load', { query: { id } })).board; },
  saveBoard(b) { return this.call('save', { method: 'POST', json: b }); },
  deleteBoard(id) { return this.call('delete', { method: 'POST', query: { id } }); },
  async putFile(blob, name) {
    const f = new FormData(); f.append('file', blob, name || 'file');
    return 'srv:' + (await this.call('upload', { method: 'POST', form: f })).id;
  },
  fileUrl(ref, download) { return Promise.resolve(this.api + '?action=file&id=' + encodeURIComponent(ref.slice(4)) + (download ? '&dl=1' : '')); },
  async getBlob(ref) { const r = await fetch(await this.fileUrl(ref)); return r.ok ? r.blob() : null; },
  gc() { return this.call('gc', { method: 'POST' }); },
  async unfurl(url) { return (await this.call('unfurl', { query: { url } })).meta; },
};

/* last resort when the browser blocks IndexedDB (some private modes, sandboxed frames) */
const MemoryStore = {
  kind: 'memory', boards: new Map(), files: new Map(), urls: new Map(),
  async listBoards() { return [...this.boards.values()].map(summarize); },
  async loadBoard(id) { const b = this.boards.get(id); return b ? JSON.parse(JSON.stringify(b)) : undefined; },
  async saveBoard(b) { this.boards.set(b.id, JSON.parse(JSON.stringify(b))); },
  async deleteBoard(id) { this.boards.delete(id); },
  async putFile(blob) { const id = uid(); this.files.set(id, blob); return 'mem:' + id; },
  async getBlob(ref) { return this.files.get(ref.slice(4)) || null; },
  async fileUrl(ref) {
    if (this.urls.has(ref)) return this.urls.get(ref);
    const b = this.files.get(ref.slice(4)); if (!b) return '';
    const u = URL.createObjectURL(b); this.urls.set(ref, u); return u;
  },
  async gc() { /* nothing to clean */ },
};

let storageNote = '';
async function detectStore() {
  const forceLocal = DEMO || /[?&]local\b/.test(location.search);
  if (!forceLocal && location.protocol.startsWith('http')) {
    try {
      const d = await fetchJSON('api.php?action=ping', { cache: 'no-store', headers: { 'X-Corkboard': '1' } }, 4000);
      if (d && d.ok && d.storage === 'server') {
        if (d.writable) return ServerStore;
        storageNote = 'api.php is present but its data/ folder is not writable by the web server, so boards are being kept in this browser instead.';
      }
    } catch { /* no PHP: fall through */ }
  }
  try {
    await Promise.race([LocalStore.init(), new Promise((_, rej) => setTimeout(() => rej(new Error('timeout')), 3000))]);
    return LocalStore;
  } catch {
    storageNote = 'This browser blocked local storage, so boards only last until the page is closed.';
    return MemoryStore;
  }
}

/* ---------------------------------------------------------------- constants */
const NOTE_COLORS = ['#fff27a', '#ffc4d8', '#a9e4ff', '#b9f2a1', '#ffcf8a', '#e2c9ff', '#ffffff'];
const LABEL_STYLES = [['dymo', '#161616'], ['dymo-red', '#b7221b'], ['dymo-blue', '#1f4f9a'], ['kraft', '#d2b48c'], ['tape', '#f2ead2'], ['index', '#fffdf6']];
const PIN_COLORS = ['#d63a2f', '#2f6fd6', '#2fa84f', '#f2c230', '#8e44ad', '#ee7a22', '#e8e8e8', '#262626'];
const STRING_COLORS = ['#c62a22', '#1d1d1d', '#2453a8', '#2f8a3e', '#e3b21c', '#f5f5f5'];
const MIN = { note: [90, 90], label: [60, 32], image: [60, 60], doc: [120, 150], link: [180, 120] };
const PIN_Y = 11; // pin centre, px from the item's top edge
const TEXT_EXT = new Set(['txt', 'md', 'markdown', 'csv', 'tsv', 'json', 'xml', 'yml', 'yaml', 'ini', 'log', 'js', 'ts', 'py', 'cs', 'gd', 'html', 'css', 'php', 'c', 'cpp', 'h', 'java', 'rb', 'go', 'rs', 'sh', 'sql', 'toml']);
const BADGE = { pdf: '#c0392b', doc: '#2b579a', docx: '#2b579a', odt: '#2b579a', rtf: '#2b579a', xls: '#217346', xlsx: '#217346', ods: '#217346', csv: '#217346', ppt: '#d24726', pptx: '#d24726', odp: '#d24726', txt: '#555', md: '#555', zip: '#7a5c2e', rar: '#7a5c2e', '7z': '#7a5c2e', mp3: '#8e44ad', wav: '#8e44ad', mp4: '#16a085', mov: '#16a085', epub: '#d35400' };
const INLINE_MIME = /^(application\/pdf|image\/|text\/plain|video\/|audio\/)/;

/* ---------------------------------------------------------------- DOM refs */
const viewport = $('#viewport'), world = $('#world'), stringsG = $('#strings-g'), tempPath = $('#string-temp');
const inspector = $('#inspector'), hintEl = $('#hint'), dropOverlay = $('#drop-overlay');
const titleBtn = $('#board-title'), saveStatus = $('#save-status'), zoomLabel = $('#btn-zoom-fit');
const drawer = $('#drawer'), scrim = $('#scrim'), boardList = $('#board-list');
const dlg = $('#dialog'), dlgForm = $('#dialog-form'), dlgTitle = $('#dialog-title'), dlgBody = $('#dialog-body'), dlgOk = $('#dialog-ok');
const lightbox = $('#lightbox'), lbContent = $('#lb-content');

/* ---------------------------------------------------------------- state */
let store = null;
let board = null;
let boards = [];
let sel = null;            // { kind: 'item' | 'string', id }
let connectFrom = null;    // item id while in "connect" mode
let editing = null;        // { id, ta }
let lastStringColor = STRING_COLORS[0];
const playing = new Set(); // link items currently showing their embedded player
const pending = new Set(); // async preview jobs in flight
const els = new Map();     // item id -> { el, key }
const hist = { undo: [], redo: [] };

const getItem = id => board.items.find(i => i.id === id);
const getString = id => board.strings.find(s => s.id === id);

/* ---------------------------------------------------------------- feedback */
let hintTimer;
function hint(msg, ms = 2200) {
  hintEl.textContent = msg; hintEl.classList.add('show');
  clearTimeout(hintTimer); if (ms) hintTimer = setTimeout(() => hintEl.classList.remove('show'), ms);
}
function toast(msg, err) {
  const t = document.createElement('div'); t.className = 'toast' + (err ? ' err' : ''); t.textContent = msg;
  document.body.appendChild(t); setTimeout(() => t.remove(), err ? 5000 : 2600);
}
function setStatus(txt, err) { saveStatus.textContent = txt; saveStatus.classList.toggle('err', !!err); }

/* ---------------------------------------------------------------- saving & history */
let saveTimer = null, saving = Promise.resolve(), dirty = false;
function scheduleSave() {
  dirty = true; setStatus('Editing…');
  clearTimeout(saveTimer); saveTimer = setTimeout(saveNow, 600);
}
function saveNow() {
  clearTimeout(saveTimer); saveTimer = null;
  if (!dirty || !board) return saving;
  dirty = false;
  const b = board;
  setStatus('Saving…');
  saving = saving.then(() => store.saveBoard(b)).then(() => {
    if (!dirty) setStatus('Saved');
    const s = boards.find(x => x.id === b.id);
    if (s) Object.assign(s, summarize(b));
  }).catch(err => {
    dirty = true; setStatus('Not saved', true);
    toast('Saving failed: ' + err.message, true);
  });
  return saving;
}
function commit() { board.updated = Date.now(); scheduleSave(); updateUndoButtons(); }
const snap = () => JSON.stringify({ items: board.items, strings: board.strings });
function checkpoint() {
  hist.undo.push(snap()); if (hist.undo.length > 100) hist.undo.shift();
  hist.redo.length = 0; updateUndoButtons();
}
function restore(s) {
  const d = JSON.parse(s);
  board.items = d.items; board.strings = d.strings;
  if (sel && !(sel.kind === 'item' ? getItem(sel.id) : getString(sel.id))) sel = null;
  renderAll(); commit();
}
function undo() { finishEdit(); if (!hist.undo.length) return; hist.redo.push(snap()); restore(hist.undo.pop()); }
function redo() { finishEdit(); if (!hist.redo.length) return; hist.undo.push(snap()); restore(hist.redo.pop()); }
function updateUndoButtons() { $('#btn-undo').disabled = !hist.undo.length; $('#btn-redo').disabled = !hist.redo.length; }

/* ---------------------------------------------------------------- view */
function applyView() {
  const v = board.view;
  world.style.transform = `translate(${v.x}px, ${v.y}px) scale(${v.z})`;
  viewport.style.setProperty('--z', v.z);
  viewport.style.setProperty('--px', v.x + 'px');
  viewport.style.setProperty('--py', v.y + 'px');
  zoomLabel.textContent = Math.round(v.z * 100) + '%';
}
function toWorld(cx, cy) {
  const r = viewport.getBoundingClientRect(), v = board.view;
  return { x: (cx - r.left - v.x) / v.z, y: (cy - r.top - v.y) / v.z };
}
function viewCenter() { const r = viewport.getBoundingClientRect(); return toWorld(r.left + r.width / 2, r.top + r.height / 2); }
function zoomAt(cx, cy, nz) {
  const r = viewport.getBoundingClientRect(), v = board.view;
  nz = clamp(nz, 0.1, 4);
  const wx = (cx - r.left - v.x) / v.z, wy = (cy - r.top - v.y) / v.z;
  v.x = cx - r.left - wx * nz; v.y = cy - r.top - wy * nz; v.z = nz;
  applyView(); scheduleSave();
}
function anyVisible() {
  const r = viewport.getBoundingClientRect(), a = toWorld(r.left, r.top), b = toWorld(r.right, r.bottom);
  return board.items.some(it => it.x < b.x && it.x + it.w > a.x && it.y < b.y && it.y + it.h > a.y);
}
function zoomBy(f) { const r = viewport.getBoundingClientRect(); zoomAt(r.left + r.width / 2, r.top + r.height / 2, board.view.z * f); }
function fitAll() {
  const r = viewport.getBoundingClientRect();
  if (!board.items.length) { board.view = { x: 60, y: 60, z: 1 }; applyView(); scheduleSave(); return; }
  let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
  for (const it of board.items) { x0 = Math.min(x0, it.x); y0 = Math.min(y0, it.y - 30); x1 = Math.max(x1, it.x + it.w); y1 = Math.max(y1, it.y + it.h); }
  const pad = 50, bw = x1 - x0 + pad * 2, bh = y1 - y0 + pad * 2;
  const z = clamp(Math.min(r.width / bw, r.height / bh), 0.1, 1.5);
  board.view = { z, x: (r.width - (x1 - x0) * z) / 2 - x0 * z, y: (r.height - (y1 - y0) * z) / 2 - y0 * z };
  applyView(); scheduleSave();
}

/* ---------------------------------------------------------------- geometry */
const center = it => ({ x: it.x + it.w / 2, y: it.y + it.h / 2 });
function anchor(it) {
  const c = center(it), a = (it.rot || 0) * Math.PI / 180, oy = -it.h / 2 + PIN_Y;
  return { x: c.x - oy * Math.sin(a), y: c.y + oy * Math.cos(a) };
}
function yarnPath(p, q) {
  const d = Math.hypot(q.x - p.x, q.y - p.y), sag = Math.min(110, 12 + d * 0.16);
  const mx = (p.x + q.x) / 2, my = (p.y + q.y) / 2 + sag;
  return `M${p.x.toFixed(1)},${p.y.toFixed(1)} Q${mx.toFixed(1)},${my.toFixed(1)} ${q.x.toFixed(1)},${q.y.toFixed(1)}`;
}
const overlaps = (a, b) => !(a.x + a.w < b.x || b.x + b.w < a.x || a.y + a.h < b.y || b.y + b.h < a.y);
const nextZ = () => board.items.reduce((m, i) => Math.max(m, i.z || 0), 0) + 1;
const sortedItems = () => [...board.items].sort((a, b) => a.z - b.z);

/* ---------------------------------------------------------------- rendering */
function contentKey(it) {
  return JSON.stringify([it.type, it.text, it.color, it.style, it.fileRef, it.src, it.caption, it.name, it.size, it.url, it.meta, it.pin, playing.has(it.id)]);
}
function linkMediaHTML(it) {
  const m = it.meta || {};
  const fav = safeUrl(m.favicon) || (hostOf(it.url) ? `https://www.google.com/s2/favicons?domain=${encodeURIComponent(hostOf(it.url))}&sz=64` : '');
  if (m.demo) {
    let hue = 0; for (const ch of (m.site || '')) hue = (hue * 31 + ch.charCodeAt(0)) % 360;
    return m.kind === 'video'
      ? '<div class="link-media demo video"><span class="play-badge"></span><span class="demo-note">Plays on the card in your server copy</span></div>'
      : `<div class="link-media demo" style="--h:${hue}"><span class="ltr">${esc((m.site || '?').charAt(0).toUpperCase())}</span><span class="demo-note">Live preview in your server copy</span></div>`;
  }
  const embed = safeUrl(m.embed);
  if (embed && playing.has(it.id)) {
    return `<div class="link-media"><iframe src="${esc(embed)}" allow="autoplay; encrypted-media; picture-in-picture; fullscreen; clipboard-write" referrerpolicy="strict-origin-when-cross-origin" sandbox="allow-scripts allow-same-origin allow-presentation allow-popups allow-popups-to-escape-sandbox allow-forms"></iframe></div>`;
  }
  const img = safeUrl(m.image, true);
  if (img) {
    return `<div class="link-media"><img class="thumb" src="${esc(img)}" alt="" draggable="false" referrerpolicy="no-referrer" loading="lazy">${embed ? '<button class="play" aria-label="Play"></button>' : ''}</div>`;
  }
  if (m.loading) return '<div class="link-media empty loading"></div>';
  return `<div class="link-media empty">${fav ? `<img src="${esc(fav)}" alt="" draggable="false">` : ''}${embed ? '<button class="play" aria-label="Play"></button>' : ''}</div>`;
}
function itemHTML(it) {
  let inner = '';
  switch (it.type) {
    case 'note':
      inner = `<div class="body" style="--c:${cssColor(it.color, NOTE_COLORS[0])}"><div class="text">${esc(it.text)}</div></div>`;
      break;
    case 'label': {
      const st = LABEL_STYLES.some(s => s[0] === it.style) ? it.style : 'dymo';
      inner = `<div class="body lbl-${st}"><span class="text">${esc(it.text) || 'LABEL'}</span></div>`;
      break;
    }
    case 'image':
      inner = `<div class="body"><div class="photo"><img alt="" draggable="false" referrerpolicy="no-referrer"></div>${it.caption ? `<div class="caption">${esc(it.caption)}</div>` : ''}</div>`;
      break;
    case 'doc': {
      const ext = extOf(it.name) || (it.mime || '').split('/').pop().slice(0, 4) || 'file';
      const m = it.meta || {};
      const prev = m.thumb && safeUrl(m.thumb, true) ? `<img src="${esc(m.thumb)}" alt="" draggable="false">`
        : m.excerpt ? `<pre>${esc(m.excerpt)}</pre>`
        : `<div class="doc-icon">${icon('doc')}</div>`;
      inner = `<div class="body"><div class="doc-top"><span class="badge" style="--b:${BADGE[ext] || '#6b6257'}">${esc(ext)}</span><span class="doc-name">${esc(it.name)}</span></div><div class="doc-preview">${prev}</div><div class="doc-meta"><span>${esc(fmtBytes(it.size))}</span><span>double-click to open</span></div></div>`;
      break;
    }
    case 'link': {
      const m = it.meta || {};
      const host = m.site || hostOf(it.url);
      const fav = safeUrl(m.favicon) || `https://www.google.com/s2/favicons?domain=${encodeURIComponent(hostOf(it.url))}&sz=32`;
      inner = `<div class="body">${linkMediaHTML(it)}<div class="link-text"><div class="link-site"><img src="${esc(fav)}" alt="" draggable="false" onerror="this.remove()">${esc(host)}</div><div class="link-title">${esc(m.title || it.url)}</div>${m.description ? `<div class="link-desc">${esc(m.description)}</div>` : ''}</div></div>`;
      break;
    }
  }
  return inner + `<div class="pin" style="--pin:${cssColor(it.pin, PIN_COLORS[0])}"></div><div class="h h-resize" title="Resize"></div><div class="h h-rotate" title="Rotate"></div>`;
}
function placeItem(it, el) {
  const s = el.style;
  s.left = it.x + 'px'; s.top = it.y + 'px'; s.width = it.w + 'px'; s.height = it.h + 'px';
  s.transform = `rotate(${it.rot || 0}deg)`; s.zIndex = it.z;
}
function renderItem(it, force) {
  let rec = els.get(it.id);
  if (!rec) {
    const el = document.createElement('div'); el.dataset.id = it.id;
    world.insertBefore(el, $('#strings'));
    rec = { el, key: null }; els.set(it.id, rec);
  }
  const el = rec.el;
  el.className = `item type-${it.type}` + (it.caption ? ' has-caption' : '') +
    (sel && sel.kind === 'item' && sel.id === it.id ? ' selected' : '') + (connectFrom === it.id ? ' connect-source' : '');
  const key = contentKey(it);
  if (force || key !== rec.key) {
    if (editing && editing.id === it.id) return placeItem(it, el); // don't clobber the open editor
    el.innerHTML = itemHTML(it); rec.key = key;
    hydrate(it, el);
  }
  placeItem(it, el);
}
function renderAll() {
  const ids = new Set(board.items.map(i => i.id));
  for (const [id, rec] of els) if (!ids.has(id)) { rec.el.remove(); els.delete(id); }
  for (const it of board.items) renderItem(it);
  renderStrings(); updateInspector(); updateUndoButtons();
}
function renderStrings() {
  let html = '';
  for (const s of board.strings) {
    const a = getItem(s.a), b = getItem(s.b);
    if (!a || !b) continue;
    const p = anchor(a), q = anchor(b), d = yarnPath(p, q);
    const on = sel && sel.kind === 'string' && sel.id === s.id;
    html += `<g class="string${on ? ' selected' : ''}" data-id="${esc(s.id)}" style="--sc:${cssColor(s.color, STRING_COLORS[0])}"><path class="yarn-hit" d="${d}"/><path class="yarn" d="${d}"/><path class="yarn-hi" d="${d}"/><circle class="knot" cx="${p.x.toFixed(1)}" cy="${p.y.toFixed(1)}" r="3.4"/><circle class="knot" cx="${q.x.toFixed(1)}" cy="${q.y.toFixed(1)}" r="3.4"/></g>`;
  }
  stringsG.innerHTML = html;
}

/* async bits that fill in an item after it's in the DOM */
function hydrate(it, el) {
  if (it.type === 'image') {
    const img = el.querySelector('.photo img');
    const showBroken = () => { if (img.isConnected) img.insertAdjacentHTML('afterend', '<div class="broken">Image unavailable</div>'), img.remove(); };
    img.onerror = showBroken;
    if (it.fileRef) store.fileUrl(it.fileRef).then(u => { if (u) img.src = u; else showBroken(); }).catch(showBroken);
    else if (safeUrl(it.src, true)) img.src = safeUrl(it.src, true);
    else showBroken();
  }
  if (it.type === 'doc') {
    if (it.fileRef) store.fileUrl(it.fileRef).then(u => { el.dataset.url = u; }).catch(() => {});
    ensureDocPreview(it);
  }
  if (it.type === 'link') {
    const th = el.querySelector('img.thumb');
    if (th) th.onerror = () => { const m = th.parentElement; th.remove(); m.classList.add('empty'); };
    if (it.meta && it.meta.loading) fillLinkMeta(it.id);
  }
}

/* ---------------------------------------------------------------- selection & inspector */
function select(s) {
  if (editing && (!s || s.id !== editing.id)) finishEdit();
  sel = s;
  for (const [id, rec] of els) rec.el.classList.toggle('selected', !!s && s.kind === 'item' && s.id === id);
  renderStrings(); updateInspector();
}
function swatches(list, current, act) {
  return list.map(([val, col]) => `<button class="swatch${act === 'lstyle' ? ' lbl' : ''}${val === current ? ' on' : ''}" style="--s:${col}" data-act="${act}" data-arg="${esc(val)}" title="${esc(val)}"></button>`).join('');
}
const btn = (act, ic, label, extra = '') => `<button class="tb-btn ${extra}" data-act="${act}" title="${esc(label)}">${icon(ic)}<span>${esc(label)}</span></button>`;
function updateInspector() {
  if (!sel || !board) { inspector.hidden = true; return; }
  let h = '';
  if (sel.kind === 'string') {
    const s = getString(sel.id); if (!s) { inspector.hidden = true; return; }
    h = swatches(STRING_COLORS.map(c => [c, c]), s.color, 'scolor') + '<span class="sep"></span>' + btn('del', 'trash', 'Cut string', 'danger');
  } else {
    const it = getItem(sel.id); if (!it) { inspector.hidden = true; return; }
    if (it.type === 'note') h += btn('edit', 'edit', 'Edit') + swatches(NOTE_COLORS.map(c => [c, c]), it.color, 'color');
    if (it.type === 'label') h += btn('edit', 'edit', 'Edit') + swatches(LABEL_STYLES, it.style, 'lstyle');
    if (it.type === 'image') h += btn('open', 'eye', 'View') + btn('caption', 'caption', 'Caption');
    if (it.type === 'doc') h += btn('open', DEMO ? 'eye' : 'open', DEMO ? 'View' : 'Open') + (DEMO ? '' : btn('download', 'download', 'Download'));
    if (it.type === 'link') {
      if (it.meta && it.meta.embed) h += btn('play', playing.has(it.id) ? 'stop' : 'play', playing.has(it.id) ? 'Stop' : 'Play');
      const href = safeUrl(it.url);
      if (href) h += `<a class="tb-btn" href="${esc(href)}" target="_blank" rel="noopener noreferrer" title="Open link">${icon('open')}<span>Open</span></a>`;
      if (!DEMO) h += btn('refresh', 'refresh', 'Refresh');
    }
    h += '<span class="sep"></span>' + btn('connect', 'string', 'String') +
      '<span class="sep"></span>' + btn('front', 'front', 'Bring to front', 'icon-only') + btn('forward', 'forward', 'Bring forward', 'icon-only') + btn('backward', 'backward', 'Send backward', 'icon-only') + btn('back', 'back', 'Send to back', 'icon-only') +
      '<span class="sep"></span>' + btn('straighten', 'straight', 'Straighten', 'icon-only') + btn('dup', 'copy', 'Duplicate', 'icon-only') + btn('del', 'trash', 'Delete', 'danger icon-only');
  }
  inspector.innerHTML = h; inspector.hidden = false;
}
inspector.addEventListener('pointerdown', e => e.stopPropagation());
inspector.addEventListener('click', e => {
  const b = e.target.closest('[data-act]'); if (!b) return;
  act(b.dataset.act, b.dataset.arg);
});

function act(name, arg) {
  if (!sel) return;
  if (sel.kind === 'string') {
    const s = getString(sel.id); if (!s) return;
    if (name === 'scolor') { checkpoint(); s.color = arg; lastStringColor = arg; commit(); renderStrings(); updateInspector(); }
    if (name === 'del') deleteSelected();
    return;
  }
  const it = getItem(sel.id); if (!it) return;
  switch (name) {
    case 'edit': startEdit(it); break;
    case 'open': activate(it, true); break;
    case 'play': togglePlay(it); break;
    case 'color': checkpoint(); it.color = arg; commit(); renderItem(it); updateInspector(); break;
    case 'lstyle': checkpoint(); it.style = arg; commit(); renderItem(it); updateInspector(); break;
    case 'caption': editCaption(it); break;
    case 'download': downloadDoc(it); break;
    case 'refresh': refreshLink(it); break;
    case 'connect': startConnect(it); break;
    case 'front': case 'forward': case 'backward': case 'back': layer(it, name); break;
    case 'straighten': checkpoint(); it.rot = 0; commit(); renderItem(it); renderStrings(); break;
    case 'dup': duplicate(it); break;
    case 'del': deleteSelected(); break;
  }
}

/* ---------------------------------------------------------------- item operations */
/* look for an empty patch of cork near the middle of the screen */
function findSpot(w, h) {
  const r = viewport.getBoundingClientRect(), tl = toWorld(r.left, r.top), br = toWorld(r.right, r.bottom), c = viewCenter();
  const fits = (x, y) => x >= tl.x + 10 && y >= tl.y + 30 && x + w <= br.x - 10 && y + h <= br.y - 10 &&
    !board.items.some(o => overlaps({ x, y, w, h }, { x: o.x - 14, y: o.y - 14, w: o.w + 28, h: o.h + 28 }));
  for (let ring = 0; ring < 14; ring++) {
    const n = ring ? ring * 8 : 1;
    for (let k = 0; k < n; k++) {
      const a = (k / n) * Math.PI * 2 + ring * 0.7;
      const x = c.x - w / 2 + Math.cos(a) * ring * 55, y = c.y - h / 2 + Math.sin(a) * ring * 40;
      if (fits(x, y)) return { x: x + w / 2, y: y + h / 2 };
    }
  }
  return null;
}
function baseItem(type, w, h, at) {
  const spot = at || findSpot(w, h), c = spot || viewCenter(), j = spot ? 0 : 1;
  return {
    id: uid(), type, w: Math.round(w), h: Math.round(h),
    x: Math.round(c.x - w / 2 + rand(-40, 40) * j), y: Math.round(c.y - h / 2 + rand(-30, 30) * j),
    rot: +rand(-4, 4).toFixed(1), z: nextZ(), pin: pick(PIN_COLORS),
  };
}
function addItem(it, { edit } = {}) {
  checkpoint(); board.items.push(it); commit();
  renderItem(it); select({ kind: 'item', id: it.id });
  if (edit) startEdit(it);
  return it;
}
function addNote(at, text = '') { const it = baseItem('note', 200, 200, at); it.color = NOTE_COLORS[0]; it.text = text; return addItem(it, { edit: !text }); }
function addLabel(at) { const it = baseItem('label', 230, 54, at); it.style = 'dymo'; it.text = ''; it.rot = +rand(-2, 2).toFixed(1); return addItem(it, { edit: true }); }

function layer(it, how) {
  checkpoint();
  const s = sortedItems(), i = s.indexOf(it);
  if (how === 'front') { s.splice(i, 1); s.push(it); }
  else if (how === 'back') { s.splice(i, 1); s.unshift(it); }
  else if (how === 'forward') {
    let j = i + 1; while (j < s.length && !overlaps(it, s[j])) j++;
    if (j >= s.length) j = Math.min(i + 1, s.length - 1);
    s.splice(i, 1); s.splice(j, 0, it);
  } else {
    let j = i - 1; while (j >= 0 && !overlaps(it, s[j])) j--;
    if (j < 0) j = Math.max(i - 1, 0);
    s.splice(i, 1); s.splice(j, 0, it);
  }
  s.forEach((x, k) => { x.z = k + 1; });
  commit(); for (const x of board.items) renderItem(x);
  hint({ front: 'Brought to front', back: 'Sent to back', forward: 'Moved up a layer', backward: 'Moved down a layer' }[how], 1200);
}
function duplicate(it) {
  const c = JSON.parse(JSON.stringify(it));
  c.id = uid(); c.x += 26; c.y += 26; c.z = nextZ(); c.rot = +rand(-4, 4).toFixed(1);
  addItem(c);
}
function deleteSelected() {
  if (!sel) return;
  checkpoint();
  if (sel.kind === 'item') {
    board.items = board.items.filter(i => i.id !== sel.id);
    board.strings = board.strings.filter(s => s.a !== sel.id && s.b !== sel.id);
    playing.delete(sel.id);
  } else board.strings = board.strings.filter(s => s.id !== sel.id);
  sel = null; commit(); renderAll();
}
function addString(a, b) {
  if (a === b) return;
  if (board.strings.some(s => (s.a === a && s.b === b) || (s.a === b && s.b === a))) { hint('Those are already connected'); return; }
  checkpoint();
  const s = { id: uid(), a, b, color: lastStringColor };
  board.strings.push(s); commit();
  select({ kind: 'string', id: s.id });
}
function startConnect(it) {
  connectFrom = it.id; viewport.classList.add('connecting'); renderItem(it);
  hint('Now tap the item to tie the string to — Esc to cancel', 0);
}
function cancelConnect() {
  if (!connectFrom) return;
  const id = connectFrom; connectFrom = null; viewport.classList.remove('connecting');
  const it = getItem(id); if (it) renderItem(it);
  hintEl.classList.remove('show');
}

/* ---------------------------------------------------------------- text editing */
function startEdit(it) {
  if (!it || !(it.type === 'note' || it.type === 'label')) return;
  if (editing) { if (editing.id === it.id) return; finishEdit(); }
  if (!sel || sel.id !== it.id) select({ kind: 'item', id: it.id });
  const rec = els.get(it.id); if (!rec) return;
  const body = rec.el.querySelector('.body'), txt = body.querySelector('.text');
  const ta = document.createElement('textarea');
  ta.className = 'editor'; ta.value = it.text || ''; ta.spellcheck = true;
  ta.placeholder = it.type === 'note' ? 'Write something…' : 'LABEL';
  if (txt) txt.style.visibility = 'hidden';
  body.appendChild(ta);
  editing = { id: it.id, ta };
  ta.addEventListener('pointerdown', e => e.stopPropagation());
  ta.addEventListener('keydown', e => {
    e.stopPropagation();
    if (e.key === 'Escape' || (e.key === 'Enter' && (e.metaKey || e.ctrlKey || it.type === 'label'))) { e.preventDefault(); ta.blur(); }
  });
  ta.addEventListener('blur', () => finishEdit());
  ta.focus({ preventScroll: true });
  ta.setSelectionRange(ta.value.length, ta.value.length);
}
function finishEdit() {
  if (!editing) return;
  const { id, ta } = editing; editing = null;
  const it = getItem(id);
  if (it && ta.value !== (it.text || '')) { checkpoint(); it.text = ta.value; commit(); }
  ta.remove();
  if (it) renderItem(it, true);
}
async function editCaption(it) {
  const v = await ask({ title: 'Photo caption', fields: [{ name: 'c', label: 'Caption (leave blank for none)', value: it.caption || '' }] });
  if (!v) return;
  const cur = getItem(it.id); if (!cur) return;
  const had = !!cur.caption, has = !!v.c.trim();
  checkpoint(); cur.caption = v.c.trim();
  if (had !== has) cur.h += has ? 30 : -30;
  commit(); renderItem(cur); renderStrings();
}

/* ---------------------------------------------------------------- opening items */
function activate(it, fromButton) {
  if (!it) return;
  if (it.type === 'note' || it.type === 'label') return startEdit(it);
  if (it.type === 'image') return openLightbox(it);
  if (it.type === 'doc') return openDoc(it);
  if (it.type === 'link') {
    if (it.meta && it.meta.embed && !fromButton) return togglePlay(it);
    if (DEMO) return hint('Use “Open” in the bar below to visit this link');
    window.open(it.url, '_blank', 'noopener');
  }
}
function togglePlay(it) {
  if (playing.has(it.id)) playing.delete(it.id); else playing.add(it.id);
  renderItem(it); updateInspector();
}
async function openLightbox(it) {
  const src = it.fileRef ? await store.fileUrl(it.fileRef) : safeUrl(it.src, true);
  if (!src) return toast('Image unavailable', true);
  lbContent.innerHTML = `<img src="${esc(src)}" alt="" referrerpolicy="no-referrer">`;
  lightbox.hidden = false;
}
function closeLightbox() { lightbox.hidden = true; lbContent.innerHTML = ''; }
$('#lb-close').addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e => { if (e.target === lightbox || e.target === lbContent) closeLightbox(); });

async function openViewer(it) {
  lbContent.innerHTML = '<div class="viewer"><p class="viewer-msg">Opening…</p></div>'; lightbox.hidden = false;
  const box = lbContent.firstElementChild;
  try {
    const blob = await store.getBlob(it.fileRef); if (!blob) throw new Error('File unavailable');
    const ext = extOf(it.name), type = it.mime || blob.type || '';
    if (ext === 'pdf' || type === 'application/pdf') {
      const lib = await loadPdfJs();
      const pdf = await lib.getDocument({ data: new Uint8Array(await blob.arrayBuffer()) }).promise;
      box.innerHTML = '';
      const n = Math.min(pdf.numPages, 30), dpr = Math.min(2, window.devicePixelRatio || 1);
      for (let i = 1; i <= n && !lightbox.hidden; i++) {
        const page = await pdf.getPage(i), v1 = page.getViewport({ scale: 1 }), vp = page.getViewport({ scale: (900 * dpr) / v1.width });
        const c = document.createElement('canvas'); c.width = Math.ceil(vp.width); c.height = Math.ceil(vp.height);
        box.appendChild(c);
        await page.render({ canvasContext: c.getContext('2d'), viewport: vp }).promise;
      }
      if (pdf.numPages > n) box.insertAdjacentHTML('beforeend', `<p class="viewer-msg">Showing the first ${n} of ${pdf.numPages} pages.</p>`);
      pdf.destroy();
    } else if (/^image\//.test(type)) {
      box.innerHTML = `<img src="${esc(await store.fileUrl(it.fileRef))}" alt="">`;
    } else if (/^text\//.test(type) || TEXT_EXT.has(ext) || /json|xml/.test(type)) {
      const pre = document.createElement('pre'); pre.textContent = await blob.slice(0, 400000).text(); box.innerHTML = ''; box.appendChild(pre);
    } else {
      box.innerHTML = `<p class="viewer-msg">${esc(it.name)} (${esc(fmtBytes(it.size))}) can't be previewed in this demo. In your server copy, double-clicking opens or downloads it.</p>`;
    }
  } catch (err) { box.innerHTML = `<p class="viewer-msg">Couldn't open this file: ${esc(err.message)}</p>`; }
}
async function openDoc(it) {
  if (DEMO) return openViewer(it);
  const viewable = INLINE_MIME.test(it.mime || '') || ['pdf', 'txt', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'mp4', 'mp3'].includes(extOf(it.name));
  if (!viewable) return downloadDoc(it);
  const el = els.get(it.id)?.el;
  const url = el?.dataset.url || await store.fileUrl(it.fileRef);
  if (!url) return toast('File unavailable', true);
  const w = window.open(url, '_blank', 'noopener');
  if (!w && store.kind === 'local') downloadDoc(it); // popup blocked → fall back to a download
}
async function downloadDoc(it) {
  const url = store.kind === 'server' ? await store.fileUrl(it.fileRef, true) : await store.fileUrl(it.fileRef);
  if (!url) return toast('File unavailable', true);
  const a = document.createElement('a'); a.href = url; a.download = it.name || 'file'; a.rel = 'noopener';
  document.body.appendChild(a); a.click(); a.remove();
}

/* ---------------------------------------------------------------- images & documents */
async function prepImage(file) {
  const url = URL.createObjectURL(file);
  try {
    const img = await loadImg(url);
    let w = img.naturalWidth || 400, h = img.naturalHeight || 300, blob = file;
    const MAX = 2400;
    if (!/gif|svg/.test(file.type) && Math.max(w, h) > MAX) {
      const s = MAX / Math.max(w, h); w = Math.round(w * s); h = Math.round(h * s);
      const c = document.createElement('canvas'); c.width = w; c.height = h;
      c.getContext('2d').drawImage(img, 0, 0, w, h);
      blob = await new Promise(r => c.toBlob(r, 'image/webp', 0.88)) || await new Promise(r => c.toBlob(r, 'image/jpeg', 0.88)) || file;
    }
    return { blob, w, h };
  } finally { URL.revokeObjectURL(url); }
}
function fitSize(w, h, maxW = 260, maxH = 320) {
  const s = Math.min(maxW / w, maxH / h, 1.6);
  return { w: Math.max(60, Math.round(w * s)), h: Math.max(60, Math.round(h * s)) };
}
async function addImageFile(f, at) {
  const { blob, w, h } = await prepImage(f);
  const ref = await store.putFile(blob, f.name);
  const s = fitSize(w, h);
  const it = baseItem('image', s.w + 18, s.h + 18, at);
  Object.assign(it, { fileRef: ref, name: f.name, caption: '' });
  addItem(it);
}
async function addDocFile(f, at) {
  const ref = await store.putFile(f, f.name);
  const it = baseItem('doc', 200, 250, at);
  Object.assign(it, { fileRef: ref, name: f.name || 'document', size: f.size, mime: f.type || '', meta: {} });
  addItem(it);
}
async function addFiles(files, at) {
  let i = 0;
  if (files.length) hint(store.kind === 'server' ? 'Uploading…' : 'Adding…', 0);
  for (const f of files) {
    const pt = at ? { x: at.x + i * 28, y: at.y + i * 28 } : null; i++;
    try {
      if (/^image\//.test(f.type)) await addImageFile(f, pt);
      else await addDocFile(f, pt);
    } catch (err) { toast(`Couldn't add “${f.name}”: ${err.message}`, true); }
  }
  hintEl.classList.remove('show');
}

/* document previews: text excerpt, or first page of a PDF (pdf.js from CDN, lazily) */
let pdfjsP = null;
function loadPdfJs() {
  if (!pdfjsP) pdfjsP = new Promise((res, rej) => {
    const base = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/';
    const s = document.createElement('script'); s.src = base + 'pdf.min.js'; s.crossOrigin = 'anonymous';
    s.onload = () => { const lib = window.pdfjsLib; if (!lib) return rej(new Error('pdf.js missing')); lib.GlobalWorkerOptions.workerSrc = base + 'pdf.worker.min.js'; res(lib); };
    s.onerror = () => { pdfjsP = null; rej(new Error('pdf.js unavailable')); };
    document.head.appendChild(s);
  });
  return pdfjsP;
}
async function pdfThumb(blob) {
  const lib = await loadPdfJs();
  const pdf = await lib.getDocument({ data: new Uint8Array(await blob.arrayBuffer()) }).promise;
  try {
    const page = await pdf.getPage(1);
    const v1 = page.getViewport({ scale: 1 }), vp = page.getViewport({ scale: 420 / v1.width });
    const c = document.createElement('canvas'); c.width = Math.ceil(vp.width); c.height = Math.ceil(vp.height);
    const ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
    await page.render({ canvasContext: ctx, viewport: vp }).promise;
    return c.toDataURL('image/jpeg', 0.78);
  } finally { pdf.destroy(); }
}
async function ensureDocPreview(it) {
  const m = it.meta || (it.meta = {});
  if (m.thumb || m.excerpt || m.noPreview || !it.fileRef || pending.has(it.id)) return;
  const ext = extOf(it.name), isPdf = ext === 'pdf' || it.mime === 'application/pdf';
  const isText = /^text\//.test(it.mime || '') || TEXT_EXT.has(ext) || /json|xml/.test(it.mime || '');
  if (!isPdf && !isText) return;
  pending.add(it.id);
  try {
    const blob = await store.getBlob(it.fileRef); if (!blob) return;
    let patch;
    if (isPdf) patch = { thumb: await pdfThumb(blob) };
    else patch = { excerpt: (await blob.slice(0, 4000).text()).slice(0, 1500) };
    const cur = getItem(it.id); if (!cur) return;
    cur.meta = { ...(cur.meta || {}), ...patch };
    renderItem(cur); scheduleSave();
  } catch (err) {
    const cur = getItem(it.id); if (cur) { cur.meta = { ...(cur.meta || {}), noPreview: true }; }
    console.warn('Preview failed for', it.name, err);
  } finally { pending.delete(it.id); }
}

/* ---------------------------------------------------------------- links & previews */
function youTubeId(u) {
  try {
    const x = new URL(u), h = x.hostname.replace(/^(www|m|music)\./, '');
    let id = null;
    if (h === 'youtu.be') id = x.pathname.slice(1).split('/')[0];
    else if (h === 'youtube.com' || h === 'youtube-nocookie.com') {
      if (x.pathname === '/watch') id = x.searchParams.get('v');
      else { const m = x.pathname.match(/^\/(embed|shorts|live|v)\/([\w-]+)/); if (m) id = m[2]; }
    }
    return id && /^[\w-]{6,20}$/.test(id) ? id : null;
  } catch { return null; }
}
function ytStart(u) {
  try {
    const t = new URL(u).searchParams.get('t') || new URL(u).searchParams.get('start'); if (!t) return 0;
    if (/^\d+$/.test(t)) return +t;
    const m = t.match(/(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?/); return m ? (+m[1] || 0) * 3600 + (+m[2] || 0) * 60 + (+m[3] || 0) : 0;
  } catch { return 0; }
}
function knownEmbed(url) {
  const yt = youTubeId(url);
  if (yt) {
    const st = ytStart(url);
    return { embed: `https://www.youtube-nocookie.com/embed/${yt}?autoplay=1&rel=0${st ? '&start=' + st : ''}`, image: `https://i.ytimg.com/vi/${yt}/hqdefault.jpg`, site: 'youtube.com' };
  }
  let m = url.match(/^https?:\/\/(?:www\.|player\.)?vimeo\.com\/(?:video\/)?(\d+)/);
  if (m) return { embed: `https://player.vimeo.com/video/${m[1]}?autoplay=1` };
  m = url.match(/^https?:\/\/open\.spotify\.com\/(?:intl-[a-z]+\/)?(track|album|playlist|episode|show|artist)\/([A-Za-z0-9]+)/);
  if (m) return { embed: `https://open.spotify.com/embed/${m[1]}/${m[2]}` };
  return {};
}
async function viaNoembed(url) {
  try {
    const d = await fetchJSON('https://noembed.com/embed?url=' + encodeURIComponent(url), {}, 7000);
    if (!d || d.error) return null;
    let embed = '';
    if (d.html) { const m = /<iframe[^>]+src=["']([^"']+)["']/i.exec(d.html); if (m && /^https:\/\//.test(m[1])) embed = m[1].replace(/&amp;/g, '&'); }
    return { title: d.title, description: d.author_name ? 'by ' + d.author_name : '', image: d.thumbnail_url, siteName: d.provider_name, embed };
  } catch { return null; }
}
async function viaMicrolink(url) {
  try {
    const d = await fetchJSON('https://api.microlink.io/?url=' + encodeURIComponent(url), {}, 9000);
    if (!d || d.status !== 'success' || !d.data) return null;
    const x = d.data;
    return { title: x.title, description: x.description, image: x.image && x.image.url, siteName: x.publisher, favicon: x.logo && x.logo.url };
  } catch { return null; }
}
async function unfurl(url) {
  if (DEMO) {
    const yt = youTubeId(url), site = hostOf(url);
    let title = '';
    try { const u = new URL(url); title = decodeURIComponent(u.pathname.split('/').filter(Boolean).pop() || '').replace(/[-_]+/g, ' ').replace(/\.\w+$/, ''); } catch { /* ignore */ }
    return { demo: true, kind: (yt || knownEmbed(url).embed) ? 'video' : 'page', site, title: yt ? 'YouTube video' : (title ? title.charAt(0).toUpperCase() + title.slice(1) : site) };
  }
  const meta = { site: hostOf(url), ...knownEmbed(url) };
  const sources = [];
  if (store.unfurl) { try { sources.push(await store.unfurl(url)); } catch { /* ignore */ } }
  if (!sources.some(s => s && s.title)) sources.push(await viaNoembed(url));
  if (!sources.some(s => s && s.title && s.image) && !meta.embed) sources.push(await viaMicrolink(url));
  const first = k => { for (const s of sources) if (s && s[k]) return String(s[k]); return ''; };
  meta.title = first('title').slice(0, 300);
  meta.description = first('description').slice(0, 400);
  meta.image = meta.image || first('image');
  meta.favicon = first('favicon');
  if (!meta.embed && first('embed')) meta.embed = first('embed');
  const sn = first('siteName'); if (sn) meta.site = sn;
  for (const k of Object.keys(meta)) if (!meta[k]) delete meta[k];
  return meta;
}
async function fillLinkMeta(id) {
  if (pending.has(id)) return;
  const it = getItem(id); if (!it) return;
  pending.add(id);
  let meta;
  try { meta = await unfurl(it.url); } catch { meta = { site: hostOf(it.url) }; }
  pending.delete(id);
  const cur = getItem(id); if (!cur) return;
  cur.meta = meta;
  if (!meta.image && !meta.embed && !meta.demo && cur.h === 275) cur.h = 150;
  if ((meta.image || meta.embed) && cur.h === 150) cur.h = 275;
  renderItem(cur); renderStrings(); scheduleSave();
  if (sel && sel.id === id) updateInspector();
}
function refreshLink(it) { checkpoint(); it.meta = { loading: true, site: hostOf(it.url) }; commit(); renderItem(it); }
async function addLink(raw, at) {
  let url = String(raw || '').trim();
  if (!url) return;
  if (!/^[a-z][\w+.-]*:/i.test(url)) url = 'https://' + url;
  if (!/^https?:/i.test(url) || !safeUrl(url)) return toast('That doesn’t look like a web address', true);
  if (/\.(png|jpe?g|gif|webp|avif|svg)$/i.test(new URL(url).pathname)) {
    let s = { w: 260, h: 200 };
    try { const img = await loadImg(url); s = fitSize(img.naturalWidth, img.naturalHeight); } catch { /* keep default */ }
    const it = baseItem('image', s.w + 18, s.h + 18, at);
    Object.assign(it, { src: url, name: url.split('/').pop(), caption: '' });
    return addItem(it);
  }
  const it = baseItem('link', 320, 275, at);
  Object.assign(it, { url, meta: { loading: true, site: hostOf(url) } });
  addItem(it);
}
async function promptLink(at) {
  const v = await ask({ title: 'Pin a link', fields: [{ name: 'url', label: 'Web address (YouTube, Vimeo, articles, image URLs…)', type: 'url', placeholder: 'https://' }], ok: 'Pin it' });
  if (v && v.url.trim()) addLink(v.url, at);
}

/* ---------------------------------------------------------------- pointer interaction */
const pointers = new Map();
let g = null;
let lastTap = { id: null, t: 0 };

function startPinch() {
  if (g && g.moved && g.type !== 'pan' && g.type !== 'string') commit();
  tempPath.style.display = 'none';
  document.body.classList.remove('dragging-any');
  const [a, b] = [...pointers.values()];
  g = { type: 'pinch', d0: Math.hypot(a.x - b.x, a.y - b.y) || 1, c0: { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 }, v0: { ...board.view } };
}
function updatePinch() {
  const [a, b] = [...pointers.values()];
  const d = Math.hypot(a.x - b.x, a.y - b.y), c = { x: (a.x + b.x) / 2, y: (a.y + b.y) / 2 };
  const r = viewport.getBoundingClientRect(), v0 = g.v0;
  const nz = clamp(v0.z * d / g.d0, 0.1, 4);
  const wx = (g.c0.x - r.left - v0.x) / v0.z, wy = (g.c0.y - r.top - v0.y) / v0.z;
  board.view = { z: nz, x: c.x - r.left - wx * nz, y: c.y - r.top - wy * nz };
  applyView();
}

viewport.addEventListener('pointerdown', e => {
  if (!board) return;
  if (e.pointerType === 'mouse' && e.button !== 0 && e.button !== 1) return;
  const t = e.target;
  if (t.closest('.editor, iframe')) return;
  pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
  if (pointers.size === 2) { startPinch(); return; }
  if (pointers.size > 2) return;
  try { viewport.setPointerCapture(e.pointerId); } catch { /* ignore */ }
  const wp = toWorld(e.clientX, e.clientY);
  const base = { cx: e.clientX, cy: e.clientY, moved: false };

  if (e.button === 1) { e.preventDefault(); g = { ...base, type: 'pan', vx: board.view.x, vy: board.view.y }; return; }

  const stringEl = t.closest('.string');
  if (stringEl) { g = null; select({ kind: 'string', id: stringEl.dataset.id }); return; }

  const itemEl = t.closest('.item');
  if (itemEl) {
    const it = getItem(itemEl.dataset.id); if (!it) return;
    if (connectFrom) { const from = connectFrom; cancelConnect(); addString(from, it.id); g = null; return; }
    if (t.closest('.pin')) { g = { ...base, type: 'string', from: it }; return; }
    if (!sel || sel.id !== it.id) select({ kind: 'item', id: it.id });
    if (t.closest('.h-resize')) { g = { ...base, type: 'resize', it, sx: wp.x, sy: wp.y, w: it.w, h: it.h }; return; }
    if (t.closest('.h-rotate')) { const c = center(it); g = { ...base, type: 'rotate', it, c, a0: Math.atan2(wp.y - c.y, wp.x - c.x), r0: it.rot || 0 }; return; }
    g = { ...base, type: 'drag', it, sx: wp.x, sy: wp.y, ox: it.x, oy: it.y, target: t };
    return;
  }

  if (connectFrom) cancelConnect();
  if (sel) select(null);
  g = { ...base, type: 'pan', vx: board.view.x, vy: board.view.y };
});

viewport.addEventListener('pointermove', e => {
  if (!pointers.has(e.pointerId)) return;
  pointers.set(e.pointerId, { x: e.clientX, y: e.clientY });
  if (!g) return;
  if (g.type === 'pinch') { if (pointers.size >= 2) updatePinch(); return; }
  if (!g.moved) {
    if (Math.hypot(e.clientX - g.cx, e.clientY - g.cy) < 4) return;
    g.moved = true;
    if (g.type === 'drag' || g.type === 'resize' || g.type === 'rotate') { checkpoint(); document.body.classList.add('dragging-any'); }
    if (g.type === 'pan') viewport.classList.add('panning');
    if (g.type === 'string') { tempPath.style.display = 'block'; tempPath.style.setProperty('--sc', lastStringColor); document.body.classList.add('dragging-any'); }
  }
  const wp = toWorld(e.clientX, e.clientY), it = g.it;
  switch (g.type) {
    case 'pan':
      board.view.x = g.vx + e.clientX - g.cx; board.view.y = g.vy + e.clientY - g.cy; applyView(); break;
    case 'drag':
      it.x = Math.round(g.ox + wp.x - g.sx); it.y = Math.round(g.oy + wp.y - g.sy);
      placeItem(it, els.get(it.id).el); renderStrings(); break;
    case 'resize': {
      const a = -(it.rot || 0) * Math.PI / 180, dx = wp.x - g.sx, dy = wp.y - g.sy;
      const lx = dx * Math.cos(a) - dy * Math.sin(a), ly = dx * Math.sin(a) + dy * Math.cos(a);
      const [mw, mh] = MIN[it.type] || [60, 40];
      let w = Math.max(mw, g.w + lx), h = Math.max(mh, g.h + ly);
      if (it.type === 'image' && !e.shiftKey) { const ar = g.w / g.h; h = w / ar; if (h < mh) { h = mh; w = h * ar; } }
      it.w = Math.round(w); it.h = Math.round(h);
      placeItem(it, els.get(it.id).el); renderStrings(); break;
    }
    case 'rotate': {
      let r = g.r0 + (Math.atan2(wp.y - g.c.y, wp.x - g.c.x) - g.a0) * 180 / Math.PI;
      r = ((r + 540) % 360) - 180;
      if (e.shiftKey) r = Math.round(r / 15) * 15; else if (Math.abs(r) < 2) r = 0;
      it.rot = +r.toFixed(1);
      placeItem(it, els.get(it.id).el); renderStrings(); break;
    }
    case 'string':
      tempPath.setAttribute('d', yarnPath(anchor(g.from), wp)); break;
  }
});

function onPointerUp(e) {
  if (!pointers.has(e.pointerId)) return;
  pointers.delete(e.pointerId);
  if (g && g.type === 'pinch') { if (pointers.size < 2) { g = null; scheduleSave(); } return; }
  if (!g) return;
  const G = g; g = null;
  document.body.classList.remove('dragging-any'); viewport.classList.remove('panning');
  if (e.type === 'pointercancel' && G.type === 'string') { tempPath.style.display = 'none'; return; }
  switch (G.type) {
    case 'pan': if (G.moved) scheduleSave(); return;
    case 'string': {
      tempPath.style.display = 'none';
      if (!G.moved) { select({ kind: 'item', id: G.from.id }); return; }
      const hit = document.elementFromPoint(e.clientX, e.clientY);
      const el = hit && hit.closest('.item');
      if (el && el.dataset.id !== G.from.id) addString(G.from.id, el.dataset.id);
      else hint('Drop the string on another item to tie it');
      return;
    }
    default:
      if (G.moved) { commit(); return; }
      if (G.type === 'drag') {
        if (G.target.closest('.play')) { togglePlay(G.it); return; }
        const now = Date.now();
        if (lastTap.id === G.it.id && now - lastTap.t < 420) { lastTap = { id: null, t: 0 }; activate(G.it); }
        else lastTap = { id: G.it.id, t: now };
      }
  }
}
viewport.addEventListener('pointerup', onPointerUp);
viewport.addEventListener('pointercancel', onPointerUp);
viewport.addEventListener('lostpointercapture', e => { if (pointers.has(e.pointerId) && e.pointerType !== 'mouse') onPointerUp(e); });
viewport.addEventListener('contextmenu', e => { if (e.target.closest('.item, .string')) e.preventDefault(); });

viewport.addEventListener('wheel', e => {
  if (!board || e.target.closest('.editor')) return;
  e.preventDefault();
  const v = board.view;
  const dy = e.deltaMode === 1 ? e.deltaY * 33 : e.deltaY;
  const mouseWheel = !e.ctrlKey && (e.deltaMode === 1 || (e.deltaX === 0 && Math.abs(e.deltaY) >= 50));
  if (e.ctrlKey || e.metaKey || mouseWheel) zoomAt(e.clientX, e.clientY, v.z * Math.exp(-dy * (e.ctrlKey ? 0.01 : 0.0016)));
  else { v.x -= e.deltaX; v.y -= dy; applyView(); scheduleSave(); }
}, { passive: false });

/* ---------------------------------------------------------------- drag & drop, paste */
let dragDepth = 0;
const hasPayload = e => { const t = [...(e.dataTransfer?.types || [])]; return t.includes('Files') || t.includes('text/uri-list') || t.includes('text/plain'); };
viewport.addEventListener('dragenter', e => { if (!hasPayload(e)) return; e.preventDefault(); dragDepth++; dropOverlay.classList.add('show'); });
viewport.addEventListener('dragover', e => { if (!hasPayload(e)) return; e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; });
viewport.addEventListener('dragleave', () => { if (--dragDepth <= 0) { dragDepth = 0; dropOverlay.classList.remove('show'); } });
viewport.addEventListener('drop', e => {
  e.preventDefault(); dragDepth = 0; dropOverlay.classList.remove('show');
  if (!board) return;
  const at = toWorld(e.clientX, e.clientY), dt = e.dataTransfer;
  const files = [...(dt.files || [])];
  if (files.length) return addFiles(files, at);
  const uri = (dt.getData('text/uri-list') || '').split('\n').map(s => s.trim()).find(s => s && !s.startsWith('#'));
  const text = (dt.getData('text/plain') || '').trim();
  if (uri) addLink(uri, at);
  else if (/^https?:\/\/\S+$/i.test(text)) addLink(text, at);
  else if (text) addNote(at, text);
});
document.addEventListener('paste', e => {
  if (!board || e.target.closest?.('input, textarea, dialog')) return;
  const files = [...(e.clipboardData?.files || [])];
  if (files.length) { e.preventDefault(); addFiles(files); return; }
  const text = (e.clipboardData?.getData('text/plain') || '').trim();
  if (!text) return;
  e.preventDefault();
  if (/^(https?:\/\/|www\.)\S+$/i.test(text)) addLink(text); else addNote(null, text);
});

/* ---------------------------------------------------------------- keyboard */
document.addEventListener('keydown', e => {
  if (!board) return;
  if (!lightbox.hidden && e.key === 'Escape') { closeLightbox(); return; }
  if (e.target.closest?.('input, textarea, select, dialog, [contenteditable="true"]')) return;
  const mod = e.ctrlKey || e.metaKey, k = e.key.toLowerCase();
  if (mod && k === 'z') { e.preventDefault(); e.shiftKey ? redo() : undo(); return; }
  if (mod && k === 'y') { e.preventDefault(); redo(); return; }
  if (mod && k === 'd') { e.preventDefault(); if (sel?.kind === 'item') duplicate(getItem(sel.id)); return; }
  if (mod && (k === '=' || k === '+')) { e.preventDefault(); zoomBy(1.25); return; }
  if (mod && k === '-') { e.preventDefault(); zoomBy(0.8); return; }
  if (mod && k === '0') { e.preventDefault(); fitAll(); return; }
  if (mod) return;
  if (k === 'delete' || k === 'backspace') { if (sel) { e.preventDefault(); deleteSelected(); } return; }
  if (k === 'escape') { cancelConnect(); select(null); return; }
  if (k === 'enter' && sel?.kind === 'item') { e.preventDefault(); activate(getItem(sel.id)); return; }
  if (k === 'n') { e.preventDefault(); addNote(); return; }
  if (k === 'l') { e.preventDefault(); addLabel(); return; }
  if (sel?.kind === 'item' && (e.code === 'BracketLeft' || e.code === 'BracketRight')) {
    e.preventDefault();
    const it = getItem(sel.id);
    layer(it, e.code === 'BracketRight' ? (e.shiftKey ? 'front' : 'forward') : (e.shiftKey ? 'back' : 'backward'));
    return;
  }
  if (sel?.kind === 'item' && k.startsWith('arrow')) {
    e.preventDefault();
    const it = getItem(sel.id), d = e.shiftKey ? 20 : 2;
    if (!e.repeat) checkpoint();
    if (k === 'arrowleft') it.x -= d; if (k === 'arrowright') it.x += d; if (k === 'arrowup') it.y -= d; if (k === 'arrowdown') it.y += d;
    renderItem(it); renderStrings(); commit();
  }
});

/* ---------------------------------------------------------------- dialogs & menus */
function ask({ title, message = '', fields = [], ok = 'OK', danger = false }) {
  return new Promise(res => {
    dlgTitle.textContent = title;
    dlgBody.innerHTML = (message ? `<p>${esc(message)}</p>` : '') + fields.map(f =>
      `<label>${esc(f.label || '')}<input type="${f.type || 'text'}" name="${esc(f.name)}" value="${esc(f.value || '')}" placeholder="${esc(f.placeholder || '')}" autocomplete="off" ${f.type === 'url' ? 'inputmode="url"' : ''}></label>`).join('');
    dlgOk.textContent = ok; dlgOk.className = 'btn ' + (danger ? 'danger' : 'primary');
    const onClose = () => {
      dlg.removeEventListener('close', onClose);
      if (dlg.returnValue === 'ok') { const v = {}; fields.forEach(f => { v[f.name] = dlgForm.elements[f.name].value; }); res(v); }
      else res(null);
    };
    dlg.addEventListener('close', onClose);
    dlg.returnValue = '';
    dlg.showModal();
    const first = dlgBody.querySelector('input'); if (first) { first.focus(); first.select(); } else dlgOk.focus();
  });
}
$('#dialog-cancel').addEventListener('click', () => dlg.close('cancel'));
dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close('cancel'); });

function showMenu(anchorEl, entries) {
  closeMenu();
  const m = document.createElement('div'); m.className = 'menu'; m.id = 'menu';
  m.innerHTML = entries.map((en, i) => `<button data-i="${i}" class="${en.danger ? 'danger' : ''}">${icon(en.icon)}${esc(en.label)}</button>`).join('');
  document.body.appendChild(m);
  const r = anchorEl.getBoundingClientRect(), mr = m.getBoundingClientRect();
  m.style.left = clamp(r.right - mr.width, 8, innerWidth - mr.width - 8) + 'px';
  m.style.top = (r.bottom + mr.height + 8 > innerHeight ? r.top - mr.height - 4 : r.bottom + 4) + 'px';
  m.addEventListener('click', e => { const b = e.target.closest('button'); if (!b) return; closeMenu(); entries[+b.dataset.i].run(); });
  setTimeout(() => document.addEventListener('pointerdown', menuOutside, true), 0);
}
function menuOutside(e) { if (!e.target.closest('#menu')) closeMenu(); }
function closeMenu() { $('#menu')?.remove(); document.removeEventListener('pointerdown', menuOutside, true); }

/* ---------------------------------------------------------------- boards */
function newBoard(name) { const t = Date.now(); return { id: uid(), name: name || 'Untitled board', created: t, updated: t, items: [], strings: [], view: { x: 60, y: 60, z: 1 } }; }
function migrate(b) {
  b.items = Array.isArray(b.items) ? b.items : [];
  b.strings = Array.isArray(b.strings) ? b.strings : [];
  b.view = b.view && isFinite(b.view.z) ? b.view : { x: 60, y: 60, z: 1 };
  b.items.forEach((it, i) => { it.z = it.z || i + 1; it.rot = it.rot || 0; });
  return b;
}
function welcomeBoard() {
  const b = newBoard('My first board');
  const mk = (type, x, y, w, h, rot, extra) => { const it = { id: uid(), type, x, y, w, h, rot, z: b.items.length + 1, pin: PIN_COLORS[b.items.length % PIN_COLORS.length], ...extra }; b.items.push(it); return it; };
  mk('label', 110, 10, 260, 54, -1.5, { style: 'dymo', text: 'START HERE' });
  const a = mk('note', 80, 100, 220, 210, -3, { color: NOTE_COLORS[0], text: 'Welcome to your corkboard!\n\nDouble-click a post-it to write on it.' });
  const c = mk('note', 400, 70, 210, 200, 2.5, { color: NOTE_COLORS[1], text: 'Drag from a pin to another item to tie a red string.' });
  const d = mk('note', 360, 330, 230, 210, -1.2, { color: NOTE_COLORS[2], text: 'Drop images, PDFs and links (try a YouTube URL) anywhere on the board.' });
  mk('label', 640, 230, 250, 52, 3, { style: 'kraft', text: 'Layers: [ and ]' });
  b.strings.push({ id: uid(), a: a.id, b: c.id, color: STRING_COLORS[0] }, { id: uid(), a: c.id, b: d.id, color: STRING_COLORS[0] });
  if (DEMO) {
    const l = mk('link', 660, 330, 300, 250, 2, { url: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', meta: { loading: true, site: 'youtube.com' } });
    b.strings.push({ id: uid(), a: d.id, b: l.id, color: STRING_COLORS[2] });
  }
  return b;
}
function sortBoards() { boards.sort((a, b) => (b.updated || 0) - (a.updated || 0)); }
function renderBoardList() {
  sortBoards();
  boardList.innerHTML = boards.map(b => `<li class="${board && b.id === board.id ? 'current' : ''}" data-id="${esc(b.id)}">
    <button class="open" data-open="${esc(b.id)}"><span class="name">${esc(b.name)}</span><span class="meta">${b.count} item${b.count === 1 ? '' : 's'} · ${esc(timeAgo(b.updated))}</span></button>
    <button class="tb-btn" data-more="${esc(b.id)}" aria-label="Board options">${icon('dots')}</button></li>`).join('');
}
boardList.addEventListener('click', e => {
  const o = e.target.closest('[data-open]');
  if (o) { openBoard(o.dataset.open).then(closeDrawer); return; }
  const m = e.target.closest('[data-more]');
  if (m) {
    const id = m.dataset.more;
    showMenu(m, [
      { label: 'Rename', icon: 'edit', run: () => renameBoard(id) },
      { label: 'Duplicate', icon: 'copy', run: () => duplicateBoard(id) },
      ...(DEMO ? [] : [{ label: 'Export to file', icon: 'download', run: () => exportBoard(id) }]),
      { label: 'Delete', icon: 'trash', danger: true, run: () => deleteBoard(id) },
    ]);
  }
});

async function openBoard(id) {
  finishEdit(); cancelConnect();
  await saveNow();
  let b;
  try { b = await store.loadBoard(id); } catch (err) { toast('Could not open board: ' + err.message, true); return; }
  if (!b) { toast('That board no longer exists', true); return; }
  board = migrate(b);
  hist.undo = []; hist.redo = []; sel = null; playing.clear();
  for (const rec of els.values()) rec.el.remove();
  els.clear();
  applyView(); renderAll();
  if (board.items.length && !anyVisible()) fitAll(); // e.g. opened on a phone after arranging on a big screen
  titleBtn.textContent = board.name; document.title = board.name + ' · Corkboard';
  setStatus('Saved');
  try { localStorage.setItem('corkboard:last', id); } catch { /* ignore */ }
  if (!DEMO && location.hash !== '#' + id) history.replaceState(null, '', '#' + id);
  renderBoardList();
}
async function createBoard() {
  const v = await ask({ title: 'New board', fields: [{ name: 'name', label: 'Board name', placeholder: 'e.g. Campaign villains' }], ok: 'Create' });
  if (!v) return;
  const b = newBoard(v.name.trim() || 'Untitled board');
  try { await store.saveBoard(b); } catch (err) { return toast('Could not create board: ' + err.message, true); }
  boards.push(summarize(b));
  await openBoard(b.id); closeDrawer();
}
async function renameBoard(id) {
  const s = boards.find(b => b.id === id); if (!s) return;
  const v = await ask({ title: 'Rename board', fields: [{ name: 'name', label: 'Board name', value: s.name }], ok: 'Rename' });
  if (!v || !v.name.trim()) return;
  const name = v.name.trim().slice(0, 120);
  if (board && board.id === id) { board.name = name; titleBtn.textContent = name; document.title = name + ' · Corkboard'; commit(); await saveNow(); }
  else { const b = await store.loadBoard(id); b.name = name; await store.saveBoard(b); }
  s.name = name; renderBoardList();
}
async function duplicateBoard(id) {
  if (board && board.id === id) await saveNow();
  const b = JSON.parse(JSON.stringify(await store.loadBoard(id)));
  b.id = uid(); b.name += ' (copy)'; b.created = b.updated = Date.now();
  await store.saveBoard(b); boards.push(summarize(b)); renderBoardList(); toast('Board duplicated');
}
async function deleteBoard(id) {
  const s = boards.find(b => b.id === id); if (!s) return;
  const v = await ask({ title: 'Delete board?', message: `“${s.name}” and everything pinned to it will be removed. This can’t be undone.`, ok: 'Delete', danger: true });
  if (!v) return;
  try { await store.deleteBoard(id); } catch (err) { return toast('Delete failed: ' + err.message, true); }
  boards = boards.filter(b => b.id !== id);
  if (board && board.id === id) {
    board = null; dirty = false;
    if (!boards.length) { const nb = newBoard('Untitled board'); await store.saveBoard(nb); boards.push(summarize(nb)); }
    sortBoards(); await openBoard(boards[0].id);
  }
  renderBoardList();
}
function download(name, blob) {
  const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name;
  document.body.appendChild(a); a.click(); a.remove(); setTimeout(() => URL.revokeObjectURL(a.href), 4000);
}
async function exportBoard(id) {
  hint('Packing board…', 0);
  try {
    if (board && board.id === id) await saveNow();
    const b = await store.loadBoard(id), files = {};
    for (const it of b.items) if (it.fileRef && !files[it.fileRef]) {
      const blob = await store.getBlob(it.fileRef);
      if (blob) files[it.fileRef] = { name: it.name || '', data: await blobToDataURL(blob) };
    }
    const out = { format: 'corkboard', version: 1, exported: new Date().toISOString(), board: b, files };
    const slug = (b.name || 'board').replace(/[^\w\- ]+/g, '').trim().replace(/\s+/g, '-').slice(0, 60) || 'board';
    download(slug + '.corkboard.json', new Blob([JSON.stringify(out)], { type: 'application/json' }));
  } catch (err) { toast('Export failed: ' + err.message, true); }
  hintEl.classList.remove('show');
}
async function importBoard(file) {
  hint('Importing…', 0);
  try {
    const data = JSON.parse(await file.text());
    if (!data || data.format !== 'corkboard' || !data.board) throw new Error('Not a corkboard export file');
    const b = migrate(data.board), map = {};
    for (const [ref, f] of Object.entries(data.files || {})) {
      if (!f || typeof f.data !== 'string' || !f.data.startsWith('data:')) continue;
      map[ref] = await store.putFile(await dataURLToBlob(f.data), f.name);
    }
    b.items.forEach(it => { if (it.fileRef) it.fileRef = map[it.fileRef] || null; });
    b.items = b.items.filter(it => !((it.type === 'image' && !it.src || it.type === 'doc') && !it.fileRef));
    b.id = uid(); b.updated = Date.now();
    if (boards.some(x => x.name === b.name)) b.name += ' (imported)';
    await store.saveBoard(b); boards.push(summarize(b));
    await openBoard(b.id); closeDrawer(); toast('Board imported');
  } catch (err) { toast('Import failed: ' + err.message, true); }
  hintEl.classList.remove('show');
}
async function collectGarbage() {
  try {
    if (store.kind === 'server') { await store.gc(); return; }
    const refs = new Set();
    for (const s of boards) { const b = await store.loadBoard(s.id); for (const it of (b?.items || [])) if (it.fileRef) refs.add(it.fileRef); }
    for (const h of [...hist.undo, ...hist.redo]) for (const m of h.matchAll(/"(idb:[\w]+)"/g)) refs.add(m[1]);
    await store.gc(refs);
  } catch (err) { console.warn('cleanup skipped', err); }
}

/* ---------------------------------------------------------------- drawer & toolbar wiring */
function openDrawer() { renderBoardList(); drawer.classList.add('open'); drawer.setAttribute('aria-hidden', 'false'); scrim.classList.add('show'); }
function closeDrawer() { drawer.classList.remove('open'); drawer.setAttribute('aria-hidden', 'true'); scrim.classList.remove('show'); closeMenu(); }
$('#btn-boards').addEventListener('click', openDrawer);
$('#btn-drawer-close').addEventListener('click', closeDrawer);
scrim.addEventListener('click', closeDrawer);
$('#btn-new-board').addEventListener('click', createBoard);
$('#btn-import').addEventListener('click', () => $('#file-import').click());
$('#file-import').addEventListener('change', e => { const f = e.target.files[0]; e.target.value = ''; if (f) importBoard(f); });
titleBtn.addEventListener('click', () => board && renameBoard(board.id));
$('#btn-undo').addEventListener('click', undo);
$('#btn-redo').addEventListener('click', redo);
$('#btn-zoom-in').addEventListener('click', () => zoomBy(1.25));
$('#btn-zoom-out').addEventListener('click', () => zoomBy(0.8));
$('#btn-zoom-fit').addEventListener('click', fitAll);
$('#add-group').addEventListener('click', e => {
  const b = e.target.closest('[data-add]'); if (!b || !board) return;
  const kind = b.dataset.add;
  if (kind === 'note') addNote();
  if (kind === 'label') addLabel();
  if (kind === 'image') $('#file-image').click();
  if (kind === 'doc') $('#file-doc').click();
  if (kind === 'link') promptLink();
});
for (const id of ['#file-image', '#file-doc']) $(id).addEventListener('change', e => { const fs = [...e.target.files]; e.target.value = ''; addFiles(fs); });
window.addEventListener('hashchange', () => { const id = location.hash.slice(1); if (id && board && id !== board.id && boards.some(b => b.id === id)) openBoard(id); });
document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'hidden') saveNow(); });
if (!DEMO) window.addEventListener('beforeunload', e => { if (dirty) { saveNow(); e.preventDefault(); e.returnValue = ''; } });

function renderStorageMode() {
  if (DEMO) {
    $('#storage-mode').innerHTML = store.kind === 'memory'
      ? '<b>Demo version.</b> This viewer blocked browser storage, so boards only last until you close it. Link previews, video playback and export work in the copy on your server.'
      : '<b>Demo version.</b> Boards are kept in this browser only. Link previews, video playback and export work in the copy on your server.';
    return;
  }
  $('#storage-mode').innerHTML = store.kind === 'memory' ? `<b>Not saved.</b> ${esc(storageNote)}` : store.kind === 'server'
    ? '<b>Saved on your server</b> (in the <code>data/</code> folder next to api.php), so boards follow you between devices.'
    : `<b>Saved in this browser</b> (IndexedDB). ${esc(storageNote) || 'To keep boards on your server and use them from any device, serve this folder from a host with PHP so api.php can run.'} Use “Export to file” to back up a board.`;
}

/* ---------------------------------------------------------------- boot */
async function init() {
  iconize();
  try { store = await detectStore(); }
  catch (err) { toast('No storage available: ' + err.message, true); return; }
  renderStorageMode();
  try { boards = await store.listBoards(); } catch (err) { toast('Could not list boards: ' + err.message, true); boards = []; }
  if (!boards.length) { const b = welcomeBoard(); await store.saveBoard(b); boards = [summarize(b)]; }
  sortBoards();
  let want = location.hash.slice(1);
  if (!boards.some(b => b.id === want)) { try { want = localStorage.getItem('corkboard:last'); } catch { want = null; } }
  if (!boards.some(b => b.id === want)) want = boards[0].id;
  await openBoard(want);
  setTimeout(collectGarbage, 4000);
  if (DEMO) hint('Demo version: boards are kept in this browser only', 4500);
}
init();
})();
