<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เปิดดูไฟล์</title>
    {{-- เปิด PDF ด้วย PDF.js ในหน้าเว็บเอง ไม่พึ่งตัวเปิด PDF ของเบราว์เซอร์
         บางเบราว์เซอร์ตั้งค่าให้ดาวน์โหลด PDF เสมอ หรือไม่มีตัวเปิด PDF เลย --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <style>
        :root { --bg: #e2e8f0; --bar: #ffffff; --text: #334155; --muted: #94a3b8; --accent: #FA4616; }
        body { margin: 0; background: var(--bg); color: var(--text); font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif; }
        .bar { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; gap: 8px; padding: 10px 16px;
               background: var(--bar); border-bottom: 1px solid #cbd5e1; flex-wrap: wrap; }
        .name { font-weight: 600; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .btn { border: 1px solid #cbd5e1; background: #fff; color: var(--text); border-radius: 8px; padding: 6px 12px;
               font-size: 14px; cursor: pointer; text-decoration: none; }
        .btn.primary { background: var(--accent); color: #fff; border-color: var(--accent); }
        .muted { color: var(--muted); font-size: 13px; }
        #pages { display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 16px; }
        #pages canvas, #pages img { background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.15); max-width: 100%; height: auto; }
        #pages pre { background: #fff; padding: 16px; width: min(900px, 100%); box-sizing: border-box; white-space: pre-wrap; word-break: break-word; }
        .msg { padding: 48px 16px; text-align: center; }
    </style>
</head>
<body>
    <div class="bar">
        <span class="name" id="name">กำลังโหลด...</span>
        <span class="muted" id="info"></span>
        <button class="btn" id="zoomOut" type="button" hidden>−</button>
        <button class="btn" id="zoomIn" type="button" hidden>+</button>
        <a class="btn primary" href="/teacher/download/{{ $id }}">ดาวน์โหลด</a>
    </div>
    <div id="pages"><div class="msg muted">กำลังโหลดไฟล์...</div></div>

<script>
(async function () {
    const pages = document.getElementById('pages');
    const fail = text => { pages.innerHTML = '<div class="msg">' + text + '</div>'; };

    let res;
    try {
        res = await fetch('/teacher/view/{{ $id }}/raw');
    } catch (e) {
        return fail('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้');
    }
    if (!res.ok) return fail(res.status === 404 ? 'ไม่พบไฟล์ หรือไฟล์ถูกลบไปแล้ว' : 'เปิดไฟล์นี้ไม่ได้');

    // ชื่อไฟล์จาก Content-Disposition ใช้ filename* ที่รองรับภาษาไทยก่อน
    const cd = res.headers.get('Content-Disposition') || '';
    const star = cd.match(/filename\*=UTF-8''([^;]+)/i);
    const plain = cd.match(/filename="([^"]+)"/i);
    const name = star ? decodeURIComponent(star[1]) : (plain ? plain[1] : 'ไฟล์');
    document.getElementById('name').textContent = name;
    document.title = name;

    const type = (res.headers.get('Content-Type') || '').split(';')[0].trim();
    const blob = await res.blob();

    if (type.startsWith('image/')) {
        pages.innerHTML = '';
        const img = new Image();
        img.alt = name;
        img.src = URL.createObjectURL(blob);
        pages.appendChild(img);
        return;
    }

    if (type === 'text/plain') {
        pages.innerHTML = '';
        const pre = document.createElement('pre');
        pre.textContent = await blob.text();
        pages.appendChild(pre);
        return;
    }

    if (type !== 'application/pdf' || !window.pdfjsLib) {
        return fail('ไฟล์ชนิดนี้เปิดในหน้าเว็บไม่ได้ กดปุ่มดาวน์โหลดด้านบน');
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    let pdf;
    try {
        pdf = await pdfjsLib.getDocument({ data: await blob.arrayBuffer() }).promise;
    } catch (e) {
        return fail('ไฟล์ PDF นี้เสียหรือเปิดไม่ได้ กดปุ่มดาวน์โหลดด้านบน');
    }

    document.getElementById('info').textContent = pdf.numPages + ' หน้า';
    let scale = Math.min(1.5, Math.max(0.6, (window.innerWidth - 32) / 612));

    async function render() {
        pages.innerHTML = '';
        const ratio = window.devicePixelRatio || 1;
        for (let n = 1; n <= pdf.numPages; n++) {
            const page = await pdf.getPage(n);
            const vp = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            canvas.width = vp.width * ratio;
            canvas.height = vp.height * ratio;
            canvas.style.width = vp.width + 'px';
            pages.appendChild(canvas);
            await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp, transform: [ratio, 0, 0, ratio, 0, 0] }).promise;
        }
    }

    const zin = document.getElementById('zoomIn'), zout = document.getElementById('zoomOut');
    zin.hidden = zout.hidden = false;
    zin.onclick = () => { scale = Math.min(3, scale + 0.25); render(); };
    zout.onclick = () => { scale = Math.max(0.5, scale - 0.25); render(); };

    render();
})();
</script>
</body>
</html>
