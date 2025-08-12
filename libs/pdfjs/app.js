// app.js - Self-hosted PDF flipbook
// requires pdfjsLib (pdf.js) loaded earlier

const stage = document.getElementById('stage');
const fileInput = document.getElementById('pdf-file');
const urlInput = document.getElementById('pdf-url');
const loadUrlBtn = document.getElementById('load-url');
const prevBtn = document.getElementById('prev');
const nextBtn = document.getElementById('next');
const pageIndicator = document.getElementById('page-indicator');

const canvasLeft = document.getElementById('canvas-left');
const canvasRight = document.getElementById('canvas-right');
const canvasFlip = document.getElementById('canvas-flip');
const flipper = document.getElementById('flipper');

let pdfDoc = null;
let totalPages = 0;
let currentSpread = 0; // spread index: 0 => pages 1(left?) & 2(right) etc.
const cache = {}; // page canvas cache to avoid re-rendering

// helper: render a single page into an offscreen canvas then draw to a target canvas
async function renderPageToCanvas(pageNumber, targetCanvas) {
  if (!pdfDoc) return;
  // cache key
  if (cache[pageNumber]) {
    // draw cached image onto target canvas sized to fit
    drawImageOnCanvas(cache[pageNumber], targetCanvas);
    return;
  }

  const page = await pdfDoc.getPage(pageNumber);
  // pick a scale based on stage height and page's viewport
  const viewport = page.getViewport({ scale: 1.0 });
  const targetHeight = stage.clientHeight;
  const scale = (targetHeight * 0.9) / viewport.height; // some padding
  const scaledViewport = page.getViewport({ scale });

  const off = document.createElement('canvas');
  off.width = Math.round(scaledViewport.width);
  off.height = Math.round(scaledViewport.height);
  const ctx = off.getContext('2d');

  const renderContext = {
    canvasContext: ctx,
    viewport: scaledViewport
  };

  await page.render(renderContext).promise;

  // cache as an ImageBitmap if available for faster draws
  if ('createImageBitmap' in window) {
    cache[pageNumber] = await createImageBitmap(off);
  } else {
    const img = new Image();
    img.src = off.toDataURL();
    await new Promise(r => img.onload = r);
    cache[pageNumber] = img;
  }

  drawImageOnCanvas(cache[pageNumber], targetCanvas);
}

function drawImageOnCanvas(img, canvas) {
  // size canvas to match the displayed element area and draw preserving aspect
  const rect = canvas.parentElement.getBoundingClientRect();
  canvas.width = Math.max(1, Math.floor(rect.width * devicePixelRatio));
  canvas.height = Math.max(1, Math.floor(rect.height * devicePixelRatio));
  canvas.style.width = rect.width + 'px';
  canvas.style.height = rect.height + 'px';

  const ctx = canvas.getContext('2d');
  ctx.setTransform(devicePixelRatio,0,0,devicePixelRatio,0,0);
  ctx.clearRect(0,0,canvas.width,canvas.height);

  // draw image centered and scaled to cover
  const imgRatio = img.width / img.height;
  const canvasRatio = canvas.width / canvas.height;
  let drawW, drawH;
  if (imgRatio > canvasRatio) {
    drawW = canvas.width / devicePixelRatio;
    drawH = drawW / imgRatio;
  } else {
    drawH = canvas.height / devicePixelRatio;
    drawW = drawH * imgRatio;
  }
  const dx = (canvas.width / devicePixelRatio - drawW) / 2;
  const dy = (canvas.height / devicePixelRatio - drawH) / 2;
  ctx.drawImage(img, dx, dy, drawW, drawH);
}

// compute spread -> page numbers (1-based)
function spreadToPages(spread) {
  // we'll show left = (2*spread + 1) and right = (2*spread + 2)
  const left = 2 * spread + 1;
  const right = 2 * spread + 2;
  return { left, right };
}

async function renderSpread(spread) {
  if (!pdfDoc) return;
  const { left, right } = spreadToPages(spread);
  // left may not exist if at first page (optional blank)
  if (left <= totalPages) {
    await renderPageToCanvas(left, canvasLeft);
  } else {
    clearCanvas(canvasLeft);
  }
  if (right <= totalPages) {
    await renderPageToCanvas(right, canvasRight);
  } else {
    clearCanvas(canvasRight);
  }
  pageIndicator.textContent = `Spread ${spread + 1} — pages ${left}${right <= totalPages ? ' / ' + right : ''} (of ${totalPages})`;
}

function clearCanvas(c) {
  const ctx = c.getContext('2d');
  ctx && ctx.clearRect(0,0,c.width,c.height);
  c.style.background = '#eee';
}

// Flip animation: direction = 'next' or 'prev'.
// We copy the page that will flip into canvasFlip and animate rotateY from 0 -> -180 (or 0->180)
async function flip(direction) {
  if (!pdfDoc) return;
  // determine targets
  let targetSpread = currentSpread + (direction === 'next' ? 1 : -1);
  if (targetSpread < 0 || (2*targetSpread +1) > totalPages) return;

  // For next: flip the right page over to reveal the next spread.
  // For prev: flip the left page backward to reveal the prev spread.
  const { left: curLeft, right: curRight } = spreadToPages(currentSpread);
  const { left: nextLeft, right: nextRight } = spreadToPages(targetSpread);

  // which side flips?
  let flippingPageNumber, flipOriginLeft;
  if (direction === 'next') {
    flippingPageNumber = curRight <= totalPages ? curRight : null;
    flipOriginLeft = true; // origin at left edge of flipper
  } else {
    flippingPageNumber = curLeft <= totalPages ? curLeft : null;
    flipOriginLeft = false;
  }

  // if flipping page missing (e.g., end), just jump
  if (!flippingPageNumber) {
    currentSpread = targetSpread;
    await renderSpread(currentSpread);
    return;
  }

  // render the flipping page into the flipper canvas
  await renderPageToCanvas(flippingPageNumber, canvasFlip);

  // position flipper depending on direction
  if (direction === 'next') {
    flipper.style.left = '50%';
    flipper.style.transformOrigin = 'left center';
  } else {
    flipper.style.left = '0';
    flipper.style.transformOrigin = 'right center';
  }

  flipper.style.pointerEvents = 'none';
  flipper.style.display = 'block';
  flipper.style.backfaceVisibility = 'hidden';

  // perform CSS animation via JS using requestAnimationFrame for smoother timing
  const duration = 520; // ms
  const start = performance.now();

  return new Promise(resolve => {
    function tick(now) {
      let t = Math.min(1, (now - start) / duration);
      // ease
      const ease = (--t)*t*t+1;
      const deg = direction === 'next' ? -180 * ease : 180 * ease;
      flipper.style.transform = `rotateY(${deg}deg)`;
      if (now - start < duration) {
        requestAnimationFrame(tick);
      } else {
        // finish
        flipper.style.transform = '';
        flipper.style.display = 'none';
        currentSpread = targetSpread;
        renderSpread(currentSpread).then(() => resolve());
      }
    }
    requestAnimationFrame(tick);
  });
}

// load PDF from URL or File
async function loadPdfFromUrl(url) {
  // reset caches
  Object.keys(cache).forEach(k => delete cache[k]);
  const loadingTask = pdfjsLib.getDocument({ url });
  pdfDoc = await loadingTask.promise;
  totalPages = pdfDoc.numPages;
  currentSpread = 0;
  await renderSpread(currentSpread);
}

async function loadPdfFromFile(file) {
  const arrayBuffer = await file.arrayBuffer();
  Object.keys(cache).forEach(k => delete cache[k]);
  const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
  pdfDoc = await loadingTask.promise;
  totalPages = pdfDoc.numPages;
  currentSpread = 0;
  await renderSpread(currentSpread);
}

// event wiring
fileInput.addEventListener('change', async (e) => {
  const f = e.target.files && e.target.files[0];
  if (!f) return;
  try {
    await loadPdfFromFile(f);
  } catch (err) {
    alert('Error loading PDF: ' + err.message);
    console.error(err);
  }
});

loadUrlBtn.addEventListener('click', async () => {
  const url = urlInput.value.trim();
  if (!url) return;
  try {
    await loadPdfFromUrl(url);
  } catch (err) {
    alert('Error loading PDF from URL: ' + err.message);
    console.error(err);
  }
});

prevBtn.addEventListener('click', () => flip('prev'));
nextBtn.addEventListener('click', () => flip('next'));

// keyboard
stage.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowRight') flip('next');
  if (e.key === 'ArrowLeft') flip('prev');
});

// click edges to flip
stage.addEventListener('click', (e) => {
  const rect = stage.getBoundingClientRect();
  const x = e.clientX - rect.left;
  if (x > rect.width * 0.66) flip('next');
  else if (x < rect.width * 0.34) flip('prev');
});

// simple touch swipe for mobile
let touchStartX = 0;
stage.addEventListener('touchstart', (e) => {
  touchStartX = e.changedTouches[0].clientX;
});
stage.addEventListener('touchend', (e) => {
  const dx = e.changedTouches[0].clientX - touchStartX;
  if (dx < -40) flip('next');
  else if (dx > 40) flip('prev');
});

// initialize: optional URL param ?file=...
(function init() {
  // If you want to automatically load a PDF via ?file=some.pdf
  const params = new URLSearchParams(location.search);
  if (params.has('file')) {
    const url = params.get('file');
    urlInput.value = url;
    loadPdfFromUrl(url).catch(err => console.error(err));
  }
})();
