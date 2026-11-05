<x-layouts.app title="คะแนนและความเห็น">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <x-student-sidebar />

    <main class="flex-1 p-10">

        <x-student-topbar-profile title="คะแนนและความเห็น" />

        @if($errors->any())
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        {{-- ยอดรวมนับเฉพาะงานที่ตรวจแล้ว
             งานที่ครูยังไม่ตรวจไม่ควรถูกนับเป็นศูนย์
             ไม่อย่างนั้นยอดจะดูแย่กว่าความจริงตั้งแต่ต้นเทอม --}}
        <div class="mt-6 mb-6 flex flex-wrap gap-4">
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">คะแนนรวม</div>
                <div class="text-2xl font-black text-slate-800 mt-1">
                    {{ $totalScore }}<span class="text-base font-bold text-slate-400"> / {{ $totalMax }}</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">นับเฉพาะงานที่ตรวจแล้ว</div>
            </div>

            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-100">
                <div class="text-xs text-slate-400 font-medium">ตรวจแล้ว</div>
                <div class="text-2xl font-black text-slate-800 mt-1">
                    {{ $graded }}<span class="text-base font-bold text-slate-400"> / {{ count($scores) }}</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">งาน</div>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($scores as $s)
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">

                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-[220px]">
                        <div class="font-semibold text-slate-800">{{ $s['assignment_name'] ?? 'ไม่มีชื่องาน' }}</div>
                        @if(!empty($s['due_date']))
                        <div class="text-xs text-slate-400 mt-0.5">
                            กำหนดส่ง {{ \Carbon\Carbon::parse($s['due_date'])->format('d/m/Y') }}
                        </div>
                        @endif
                    </div>

                    <div class="text-right">
                        @if(($s['score'] ?? null) !== null)
                            <div class="text-2xl font-black text-[#F26522]">
                                {{ $s['score'] }}<span class="text-sm font-bold text-slate-400"> / {{ $s['max_score'] ?? '-' }}</span>
                            </div>
                        @else
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-xs font-semibold">
                                ยังไม่ตรวจ
                            </span>
                        @endif
                    </div>
                </div>

                {{-- ความเห็นจากครู คือสิ่งที่อาจารย์บอกว่าขาดทั้งระบบ --}}
                @if(!empty($s['feedback']))
                <div class="mt-4 bg-slate-50 border border-slate-100 rounded-xl p-4">
                    <div class="text-[12px] font-semibold text-slate-500 mb-1">ความเห็นจากอาจารย์</div>
                    <div class="text-sm text-slate-700 whitespace-pre-line">{{ $s['feedback'] }}</div>
                    @if(!empty($s['feedback_by']))
                    <div class="text-[11px] text-slate-400 mt-2">โดย {{ $s['feedback_by'] }}</div>
                    @endif
                </div>
                @endif

            </div>
            @empty
            <div class="bg-white p-12 rounded-2xl shadow-sm border border-slate-100 text-center text-slate-400 text-sm">
                ยังไม่มีงานที่ต้องส่ง
            </div>
            @endforelse
        </div>

    </main>
</div>

</x-layouts.app>
