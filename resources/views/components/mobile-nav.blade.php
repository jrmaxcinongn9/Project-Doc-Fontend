@props(['profile' => '/', 'title' => 'Document Internship'])
{{-- แถบบนสำหรับจอเล็กกว่า 1024px (มือถือและ iPad แนวตั้ง)
     เมนูด้านข้างกว้าง 256px ตายตัว บนมือถือกินจอไปเกือบครึ่ง เนื้อหาเหลือที่แค่ราว 120px
     บนจอเล็กจึงซ่อนเมนูไว้ กดปุ่ม ☰ แล้วเมนูเลื่อนออกมาทับเนื้อหา --}}
<div class="mobile-topbar lg:hidden fixed top-0 inset-x-0 z-30 h-14 bg-white border-b border-slate-200 flex items-center justify-between px-3">
    <button type="button" id="sidebarOpen" aria-controls="appSidebar" aria-expanded="false" aria-label="เปิดเมนู"
            class="w-10 h-10 rounded-xl flex items-center justify-center text-slate-600 hover:bg-slate-100">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <span class="font-semibold text-slate-700 text-sm truncate px-2">{{ $title }}</span>
    <a href="{{ $profile }}" aria-label="โปรไฟล์"><x-avatar size="w-9 h-9" text="text-sm" /></a>
</div>

<div id="sidebarOverlay" class="hidden lg:hidden fixed inset-0 z-40 bg-slate-900/40"></div>

<style>
    /* เว้นที่ให้แถบบน เฉพาะหน้าที่มีแถบนี้และเฉพาะจอเล็ก */
    @media (max-width: 1023.98px) { body { padding-top: 3.5rem; } }
</style>

<script>
(function () {
    if (window.__sidebarReady) return;
    window.__sidebarReady = true;

    document.addEventListener('DOMContentLoaded', function () {
        var aside = document.getElementById('appSidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var openBtn = document.getElementById('sidebarOpen');
        if (!aside || !overlay || !openBtn) return;

        function set(open) {
            aside.classList.toggle('-translate-x-full', !open);
            overlay.classList.toggle('hidden', !open);
            openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.style.overflow = open ? 'hidden' : '';
        }

        openBtn.addEventListener('click', function () { set(true); });
        overlay.addEventListener('click', function () { set(false); });
        document.querySelectorAll('[data-sidebar-close]').forEach(function (b) {
            b.addEventListener('click', function () { set(false); });
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') set(false); });
        // กดเมนูแล้วปิด ไม่ให้ค้างทับหน้าใหม่ตอนกดย้อนกลับ
        aside.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { set(false); }); });
        // หมุนจอเป็นแนวนอนจนจอกว้างพอ เมนูแสดงตามปกติ ไม่ต้องล็อกการเลื่อนหน้าอีก
        window.matchMedia('(min-width: 1024px)').addEventListener('change', function (m) { if (m.matches) set(false); });
    });
})();
</script>
