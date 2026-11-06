<x-layouts.app title="ภาพรวมงาน">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <x-teacher-sidebar />

    <main class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">

        <x-topbar-profile title="ภาพรวมงาน" />

        @if($errors->any())
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- สรุปรวมทุกงาน --}}
        <div class="mt-6 mb-8 grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">งานทั้งหมด</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $totals->assignments }}</div>
            </div>

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">ส่งแล้ว</div>
                <div class="text-2xl font-black text-emerald-600 mt-1">{{ $totals->submittedPercent }}%</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ $totals->submitted }} จาก {{ $totals->total }} ชิ้นงาน</div>
            </div>

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">ให้คะแนนแล้ว</div>
                <div class="text-2xl font-black text-[#F26522] mt-1">{{ $totals->gradedPercent }}%</div>
                <div class="text-[11px] text-slate-400 mt-1">{{ $totals->graded }} จาก {{ $totals->total }} ชิ้นงาน</div>
            </div>

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">รอตรวจ</div>
                <div class="text-2xl font-black text-slate-800 mt-1">{{ $totals->submitted - $totals->graded > 0 ? $totals->submitted - $totals->graded : 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-1">ส่งแล้วแต่ยังไม่ให้คะแนน</div>
            </div>

        </div>

        {{-- รายงานต่อชิ้นงาน ตามที่อาจารย์ขอ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="stack-on-mobile w-full text-left border-collapse">
                    <thead class="bg-slate-50/80 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">งาน</th>
                            <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ส่งแล้ว</th>
                            <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ยังไม่ส่ง</th>
                            <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ให้คะแนนแล้ว</th>
                            <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rows as $r)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800">{{ $r->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    @if($r->due_date)
                                        กำหนดส่ง {{ \Carbon\Carbon::parse($r->due_date)->format('d/m/Y') }}
                                    @else
                                        ไม่กำหนดวันส่ง
                                    @endif
                                    @if($r->max_score)
                                        · เต็ม {{ $r->max_score }} คะแนน
                                    @else
                                        · ยังไม่ตั้งคะแนนเต็ม
                                    @endif
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-700">{{ $r->submitted }} / {{ $r->total }} คน</div>
                                <div class="w-28 h-1.5 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                                    <div class="h-full bg-emerald-500" style="width: {{ $r->submittedPercent }}%"></div>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">{{ $r->submittedPercent }}%</div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    {{ $r->notSubmitted === 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $r->notSubmitted }} คน
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-700">{{ $r->graded }} / {{ $r->total }} คน</div>
                                <div class="w-28 h-1.5 bg-slate-100 rounded-full mt-1.5 overflow-hidden">
                                    <div class="h-full bg-[#F26522]" style="width: {{ $r->gradedPercent }}%"></div>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-1">{{ $r->gradedPercent }}%</div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="/teacher/assignment-view/{{ $r->id }}"
                                   class="text-sm text-blue-600 hover:underline">ให้คะแนน</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                ยังไม่มีงานที่คุณสร้างไว้
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

</x-layouts.app>
