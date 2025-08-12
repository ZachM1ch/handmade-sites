<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PDF Flipbook</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin: 0; padding: 0; }
        #pdf-container { display: flex; flex-direction: column; align-items: center; }
        canvas { border: 1px solid #ccc; margin: 10px 0; }
        button { padding: 8px 12px; margin: 5px; }
    </style>
</head>
<body>

<h1>PDF Flipbook</h1>
<input type="file" id="file-input" accept="application/pdf">
<div id="pdf-container">
    <canvas id="pdf-canvas"></canvas>
    <div>
        <button id="prev">Previous</button>
        <span id="page-num">1</span> / <span id="page-count">0</span>
        <button id="next">Next</button>
    </div>
</div>

<script type="module">
    import * as pdfjsLib from './pdfjs/pdf.mjs';

    pdfjsLib.GlobalWorkerOptions.workerSrc = './pdfjs/pdf.worker.mjs';

    const fileInput = document.getElementById('file-input');
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');

    let pdfDoc = null;
    let pageNum = 1;

    async function renderPage(num) {
        const page = await pdfDoc.getPage(num);
        const viewport = page.getViewport({ scale: 1.5 });
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        await page.render({
            canvasContext: ctx,
            viewport: viewport
        }).promise;

        document.getElementById('page-num').textContent = num;
        document.getElementById('page-count').textContent = pdfDoc.numPages;
    }

    document.getElementById('prev').addEventListener('click', () => {
        if (pageNum <= 1) return;
        pageNum--;
        renderPage(pageNum);
    });

    document.getElementById('next').addEventListener('click', () => {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        renderPage(pageNum);
    });

    fileInput.addEventListener('change', async (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const arrayBuffer = await file.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        pageNum = 1;
        renderPage(pageNum);
    });
</script>

</body>
</html>
