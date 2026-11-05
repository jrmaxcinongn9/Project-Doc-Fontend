<x-layouts.app title="บัญชีรออนุมัติ">

<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 overflow-y-auto">
        <div class="p-8 md:p-10 max-w-7xl mx-auto w-full">

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

            <div class="bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ข้อมูลผู้ใช้</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">บทบาท</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ยืนยันอีเมล</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($users as $u)
                            <tr class="hover:bg-slate-50/60 transition-colors">
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
                                <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
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

</x-layouts.app>
