<?php
// index.php — Self-hosted PDF flipbook
// Optional: set a default PDF file to load (in same directory)
$defaultPdf = "sample.pdf";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>My PDF Flipbook</title>
</head>
<body>
  <div id="flipbook-root" class="flipbook-root">
    <header class="flipbook-header">
      <input id="pdf-file" type="file" accept="application/pdf" />
      <input id="pdf-url" type="text" placeholder="Or enter a PDF URL and press Load" />
      <button id="load-url">Load</button>
      <div class="controls">
        <button id="prev">◀ Prev</button>
        <span id="page-indicator">— / —</span>
        <button id="next">Next ▶</button>
      </div>
    </header>

    <main class="flipbook-stage" id="stage" tabindex="0">
      <div class="page left" id="page-left">
        <canvas id="canvas-left"></canvas>
      </div>
      <div class="page right" id="page-right">
        <canvas id="canvas-right"></canvas>
      </div>
      <div id="flipper" class="flipper" aria-hidden="true">
        <canvas id="canvas-flip"></canvas>
      </div>
    </main>

    <footer class="flipbook-footer">
      Tip: Click the right/left edges, use arrow keys, or swipe on mobile.
    </footer>
  </div>

  <!-- pdf.js (self-hosted) -->
  <script src="libs/pdfjs/pdf.mjs"></script>
  <script>
    if (window.pdfjsLib) {
      pdfjsLib.GlobalWorkerOptions.workerSrc = 'libs/pdfjs/pdf.worker.js';
    }
  </script>

  <script src="libs/pdfjs/app.js"></script>
  <script>
    // Auto-load the default PDF if set
    <?php if ($defaultPdf): ?>
    loadPdfFromUrl("<?php echo $defaultPdf; ?>");
    <?php endif; ?>
  </script>
</body>
</html>