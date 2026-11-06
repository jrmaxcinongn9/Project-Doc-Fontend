<x-layouts.app title="บัญชีรออนุมัติ">

<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 overflow-y-auto min-w-0">
        <div class="p-4 sm:p-6 lg:p-10 max-w-7xl mx-auto w-full">

            <div class="flex justify-between items-end mb-8 pb-6 border-b border-slate-200/70">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">บัญชีรออนุมัติ</h2>
                    <p class="text-slate-500 mt-1 text-sm font-medium">
                        ยืนยันอีเมลแทนผู้ใช้ และอนุมัติให้เข้าใช้งานระบบ
                    </p>
                </div>
                <div class="mb-1">
                    <x-admintopbar-profile />
                </div>
            </div>

            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl shadow-sm">
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            @endif

            {{-- ลิงก์ยืนยันที่ขอใหม่ ต้องโชว์ให้ copy ได้ เพราะระบบส่งเมลยังใช้งานไม่ได้ --}}
            @if(session('verify_link'))
            <div class="mb-6 bg-amber-50 border border-amber-200 px-4 py-4 rounded-2xl shadow-sm">
                <p class="text-amber-800 text-sm font-medium mb-2">
                    ลิงก์ยืนยันใหม่ ส่งต่อให้ผู้ใช้กดเอง
                </p>
                <input type="text" readonly value="{{ session('verify_link') }}"
                    onclick="this.select()"
                    class="w-full px-3 py-2 text-xs font-mono bg-white border border-amber-200 rounded-lg text-slate-700">
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl shadow-sm">
                <span class="font-medium text-sm">{{ $errors->first() }}</span>
            </div>
            @endif

            <div class="text-sm font-medium text-slate-400 mb-6">
                รออนุมัติทั้งหมด <span class="text-slate-700 font-bold">{{ count($users) }}</span> บัญชี
            </div>

            {{-- อนุมัติหลายคนพร้อมกัน ต้นเทอมนักศึกษาสมัครเข้ามาทีละหลายสิบคน
                 checkbox ในตารางผูกกับฟอร์มนี้ด้วย form="bulkForm" เพราะซ้อนฟอร์มในฟอร์มไม่ได้ --}}
            @if(count($users))
            <form id="bulkForm" method="POST" action="/admin/pending/bulk"
                  onsubmit="return confirmBulk(event)"
                  class="mb-4 flex flex-wrap items-center gap-3 bg-white border border-slate-200 rounded-2xl px-4 py-3">
                @csrf
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" id="selectAll" class="w-4 h-4 accent-[#F26522]"> เลือกทั้งหมด
                </label>
                <span id="selectedCount" class="text-sm text-slate-400">ยังไม่ได้เลือก</span>
                <label class="flex items-center gap-2 text-sm text-slate-600 ml-auto">
                    <input type="checkbox" name="verify" value="1" checked class="w-4 h-4 accent-[#F26522]">
                    ยืนยันอีเมลให้ด้วย
                </label>
                <button type="submit" name="action" value="approve"
                        class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold disabled:opacity-40 bulk-btn" disabled>
                    อนุมัติที่เลือก
                </button>
                <button type="submit" name="action" value="reject"
                        class="px-4 py-2 rounded-xl bg-white border border-red-200 text-red-600 hover:bg-red-50 text-sm font-semibold disabled:opacity-40 bulk-btn" disabled>
                    ปฏิเสธที่เลือก
                </button>
            </form>
            @endif

            <div class="bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="stack-on-mobile w-full text-left border-collapse">
                        <thead class="bg-slate-50/80 border-b border-slate-100">
                            <tr>
                                <th class="pl-6 py-4 w-10"></th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ข้อมูลผู้ใช้</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">บทบาท</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ยืนยันอีเมล</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($users as $u)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="pl-6 py-4">
                                    <input type="checkbox" name="ids[]" value="{{ $u['id'] }}" form="bulkForm"
                                           class="bulk-pick w-4 h-4 accent-[#F26522]" aria-label="เลือก {{ $u['name'] ?? '' }}">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-800">{{ $u['name'] ?? '-' }}</div>
                                    <div class="text-sm text-slate-500">{{ $u['email'] ?? '-' }}</div>
                                    @if(!empty($u['student_id']))
                                    <div class="text-xs text-slate-400 mt-0.5">รหัส {{ $u['student_id'] }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">{{ $u['role'] ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    @if(!empty($u['isVerified']))
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-50 text-emerald-700">ยืนยันแล้ว</span>
                                    @else
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-700">ยังไม่ยืนยัน</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2 flex-wrap">

                                        @if(empty($u['isVerified']))
                                        <form method="POST" action="/admin/pending/{{ $u['id'] }}/verify-email">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                                ยืนยันอีเมลแทน
                                            </button>
                                        </form>

                                        <form method="POST" action="/admin/pending/{{ $u['id'] }}/resend-verify">
                                            @csrf
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
                                                ขอลิงก์ใหม่
                                            </button>
                                        </form>
                                        @endif

                                        <form method="POST" action="/admin/pending/{{ $u['id'] }}/approve">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-4 py-1.5 text-xs font-bold rounded-lg bg-[#F26522] hover:bg-[#d8561b] text-white transition-colors">
                                                อนุมัติ
                                            </button>
                                        </form>

                                        <form method="POST" action="/admin/pending/{{ $u['id'] }}/reject"
                                            onsubmit="return confirm('ปฏิเสธบัญชีนี้ใช่หรือไม่ ผู้ใช้จะเข้าระบบไม่ได้');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition-colors">
                                                ปฏิเสธ
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    ไม่มีบัญชีรออนุมัติ
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
(function () {
    const picks = () => [...document.querySelectorAll('.bulk-pick')];
    const all = document.getElementById('selectAll');
    if (!all) return;

    function refresh() {
        const n = picks().filter(p => p.checked).length;
        document.getElementById('selectedCount').textContent = n ? 'เลือก ' + n + ' คน' : 'ยังไม่ได้เลือก';
        document.querySelectorAll('.bulk-btn').forEach(b => b.disabled = n === 0);
        all.checked = n > 0 && n === picks().length;
        all.indeterminate = n > 0 && n < picks().length;
    }

    all.addEventListener('change', () => { picks().forEach(p => p.checked = all.checked); refresh(); });
    document.addEventListener('change', e => { if (e.target.classList.contains('bulk-pick')) refresh(); });

    window.confirmBulk = function (e) {
        const n = picks().filter(p => p.checked).length;
        const reject = e.submitter && e.submitter.value === 'reject';
        return confirm((reject ? 'ปฏิเสธ ' : 'อนุมัติ ') + n + ' บัญชี ใช่หรือไม่?');
    };

    refresh();
})();
</script>
</x-layouts.app>
