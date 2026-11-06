{{-- ปุ่มรูปตาเปิดหรือปิดการแสดงรหัสผ่าน ใส่ให้ทุกช่องรหัสผ่านในทุกหน้าที่ใช้ layout นี้
     ไม่ต้องแก้ทีละหน้า ช่องที่ไม่ต้องการให้ใส่ data-no-toggle --}}
<script>
(function () {
    var EYE = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
    var EYE_OFF = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 10.6a3 3 0 004.2 4.2"/><path d="M9.9 5.2A9.8 9.8 0 0112 5c6.4 0 10 7 10 7a17 17 0 01-3.2 4.1M6.6 6.6C3.9 8.3 2 12 2 12s3.6 7 10 7c1.6 0 3-.4 4.3-1"/></svg>';

    function attach(input) {
        if (input.dataset.toggleReady || input.hasAttribute('data-no-toggle')) return;
        input.dataset.toggleReady = '1';

        // ห่อช่องไว้ให้ปุ่มวางทับขอบขวาได้ ย้าย margin ของช่องไปที่ตัวห่อ ปุ่มจะได้อยู่กลางช่องพอดี
        var style = getComputedStyle(input);
        var wrap = document.createElement('span');
        wrap.style.cssText = 'position:relative;display:block;margin-top:' + style.marginTop + ';margin-bottom:' + style.marginBottom;
        input.style.marginTop = '0';
        input.style.marginBottom = '0';
        input.style.paddingRight = '44px';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-toggle';
        btn.setAttribute('aria-label', 'แสดงรหัสผ่าน');
        btn.setAttribute('aria-pressed', 'false');
        btn.style.cssText = 'position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:0;padding:4px;cursor:pointer;line-height:0;opacity:.65;color:' + style.color;
        btn.innerHTML = EYE;
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? EYE_OFF : EYE;
            btn.setAttribute('aria-label', show ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            input.focus();
        });
        wrap.appendChild(btn);

        // ส่งฟอร์มแล้วซ่อนกลับ เบราว์เซอร์จะได้ไม่จำรหัสผ่านเป็นข้อความธรรมดา
        if (input.form) {
            input.form.addEventListener('submit', function () { input.type = 'password'; });
        }
    }

    function scan() {
        document.querySelectorAll('input[type="password"]').forEach(attach);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', scan);
    } else {
        scan();
    }
})();
</script>
