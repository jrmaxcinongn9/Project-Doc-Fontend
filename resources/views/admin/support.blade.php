<x-layouts.app title="เรื่องที่แจ้ง">

<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 overflow-y-auto">
        <div class="p-8 md:p-10 max-w-5xl mx-auto w-full">

            <div class="flex justify-between items-end mb-6 pb-6 border-b border-slate-200/70">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">เรื่องที่แจ้ง</h2>
                    <p class="text-slate-500 mt-1 text-sm">ปัญหาที่ผู้ใช้แจ้งผ่านหน้าช่วยเหลือ ตอบกลับทางอีเมลได้จากที่นี่</p>
                </div>
                <x-admintopbar-profile />
            </div>

            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any() || $loadError)
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">{{ $errors->first() ?: $loadError }}</div>
            @endif

            <div class="flex gap-2 mb-5 text-sm">
                @foreach(['OPEN' => 'ยังไม่ปิด', 'RESOLVED' => 'ปิดแล้ว', 'ALL' => 'ทั้งหมด'] as $key => $label)
                    <a href="/admin/support?status={{ $key }}"
                       class="px-4 py-2 rounded-xl border {{ $status === $key ? 'bg-[#F26522] text-white border-[#F26522] font-semibold' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300' }}">{{ $label }}</a>
                @endforeach
            </div>

            <div class="space-y-4">
                @forelse($tickets as $t)
                    @php
                        $id = $t['_id'] ?? '';
                        $open = ($t['status'] ?? 'OPEN') === 'OPEN';
                        $at = !empty($t['createdAt']) ? \Carbon\Carbon::parse($t['createdAt'])->timezone('Asia/Bangkok') : null;
                    @endphp
                    <article class="bg-white border border-slate-200 rounded-2xl p-5">
                        <div class="flex flex-wrap justify-between gap-2">
                            <div>
                                <div class="font-semibold text-slate-800">{{ $t['name'] ?? '-' }}
                                    <span class="font-normal text-slate-500 text-sm">· {{ $t['email'] ?? '-' }}</span>
                                    @if(!empty($t['user_role']))
                                        <span class="ml-1 text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">login เป็น {{ $t['user_role'] }}</span>
                                    @endif
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $topics[$t['topic'] ?? 'OTHER'] ?? ($t['topic'] ?? '-') }}
                                    @if($at) · {{ $at->locale('th')->translatedFormat('j M') }} {{ $at->year + 543 }} {{ $at->format('H:i') }} @endif
                                </div>
                            </div>
                            <span class="h-fit text-xs font-semibold px-3 py-1 rounded-full {{ $open ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700' }}">
                                {{ $open ? 'ยังไม่ปิด' : 'ปิดแล้ว' }}
                            </span>
                        </div>

                        <p class="mt-3 text-sm text-slate-700 whitespace-pre-line">{{ $t['message'] ?? '' }}</p>

                        @foreach($t['replies'] ?? [] as $r)
                            <div class="mt-3 ml-4 pl-4 border-l-2 border-orange-200 text-sm">
                                <div class="text-xs text-slate-400">ตอบโดย {{ $r['by'] ?? '-' }}{{ empty($r['mailed']) ? ' · ส่งเมลไม่สำเร็จ' : '' }}</div>
                                <p class="text-slate-700 whitespace-pre-line">{{ $r['message'] ?? '' }}</p>
                            </div>
                        @endforeach

                        <div class="mt-4 flex flex-wrap items-end gap-3">
                            <form method="POST" action="/admin/support/{{ $id }}/reply" class="flex-1 min-w-[260px]">
                                @csrf
                                <textarea name="message" rows="2" required placeholder="พิมพ์คำตอบ จะส่งไปที่ {{ $t['email'] ?? '' }}"
                                          class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522]"></textarea>
                                <div class="flex items-center justify-between mt-2">
                                    <label class="flex items-center gap-2 text-xs text-slate-500">
                                        <input type="checkbox" name="resolve" value="1" {{ $open ? 'checked' : '' }}> ปิดเรื่องหลังตอบ
                                    </label>
                                    <button class="px-4 py-2 rounded-xl bg-[#F26522] hover:bg-[#d8561b] text-white text-sm font-semibold">ตอบทางอีเมล</button>
                                </div>
                            </form>
                            <form method="POST" action="/admin/support/{{ $id }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $open ? 'RESOLVED' : 'OPEN' }}">
                                <button class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm">
                                    {{ $open ? 'ปิดเรื่องโดยไม่ตอบ' : 'เปิดเรื่องอีกครั้ง' }}
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="bg-white border border-slate-200 rounded-2xl px-6 py-14 text-center text-slate-400">
                        {{ $status === 'OPEN' ? 'ไม่มีเรื่องที่ค้างอยู่' : 'ยังไม่มีเรื่องที่แจ้ง' }}
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</div>

</x-layouts.app>
