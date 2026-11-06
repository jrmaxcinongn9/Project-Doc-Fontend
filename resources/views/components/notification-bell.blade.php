{{-- กระดิ่งแจ้งเตือน ใช้แทนอีเมลที่ยังส่งไม่ได้
     โหลดรายการทีหลังด้วย fetch ไม่ให้หน้าเว็บช้ารอ API --}}
<div class="relative px-4 mb-4" id="notifBox">
    <button type="button" id="notifBtn"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium transition-all"
            aria-haspopup="true" aria-expanded="false">
        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
        </svg>
        <span class="text-[14px]">แจ้งเตือน</span>
        <span id="notifCount" class="hidden ml-auto min-w-[20px] h-5 px-1.5 rounded-full bg-[#F26522] text-white text-[11px] font-bold flex items-center justify-center"></span>
    </button>

    <div id="notifPanel" class="hidden absolute left-4 right-4 mt-1 z-50 bg-white border border-slate-200 rounded-2xl shadow-xl max-h-[60vh] overflow-y-auto">
        <div id="notifList" class="divide-y divide-slate-100 text-sm">
            <div class="px-4 py-6 text-center text-slate-400">กำลังโหลด...</div>
        </div>
    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('notifBtn');
    if (!btn || btn.dataset.ready) return;
    btn.dataset.ready = '1';

    const panel = document.getElementById('notifPanel');
    const list = document.getElementById('notifList');
    const badge = document.getElementById('notifCount');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function render(data) {
        const n = data.unread || 0;
        badge.textContent = n > 9 ? '9+' : n;
        badge.classList.toggle('hidden', n === 0);

        list.innerHTML = (data.items || []).length
            ? data.items.map(i =>
                '<a href="' + esc(i.url) + '" class="block px-4 py-3 hover:bg-slate-50' + (i.unread ? ' bg-orange-50/60' : '') + '">'
                + '<div class="text-slate-700">' + esc(i.text) + '</div>'
                + '<div class="text-[11px] text-slate-400 mt-0.5">' + esc(i.ago) + '</div></a>').join('')
            : '<div class="px-4 py-6 text-center text-slate-400">' + esc(data.error || 'ยังไม่มีแจ้งเตือน') + '</div>';
    }

    fetch('/notifications', { headers: { Accept: 'application/json' } })
        .then(r => r.json()).then(render).catch(() => render({ items: [], error: 'โหลดแจ้งเตือนไม่ได้' }));

    btn.addEventListener('click', function () {
        const open = panel.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', open);
        if (open && !badge.classList.contains('hidden')) {
            // เปิดดูแล้วถือว่าอ่านแล้ว
            fetch('/notifications/seen', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', Accept: 'application/json' } });
            badge.classList.add('hidden');
        }
    });

    document.addEventListener('click', e => {
        if (!document.getElementById('notifBox').contains(e.target)) panel.classList.add('hidden');
    });
})();
</script>
