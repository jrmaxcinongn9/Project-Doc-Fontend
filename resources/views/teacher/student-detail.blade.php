<x-layouts.app title="ข้อมูลนักศึกษา">

@php
    // วันที่แบบไทย ปี พ.ศ. เวลาไทย เช่น 6 พ.ย. 2569 · 14:30
    $thDate = function ($d, bool $withTime = true) {
        if (!$d) return '-';
        $d = $d->copy()->timezone('Asia/Bangkok')->locale('th');
        return $d->translatedFormat('j M') . ' ' . ($d->year + 543) . ($withTime ? ' · ' . $d->format('H:i') : '');
    };

    // บอกระยะเวลาสั้น ๆ ให้เห็นทันทีว่าเร่งแค่ไหน
    $dueHint = function ($row) {
        if (!$row->due || in_array($row->status, ['submitted', 'late'])) return null;
        // นับเป็นวันตามปฏิทินเวลาไทย ไม่ใช่ทุก 24 ชั่วโมง
        $days = (int) now('Asia/Bangkok')->startOfDay()
            ->diffInDays($row->due->copy()->timezone('Asia/Bangkok')->startOfDay(), false);
        if ($row->status === 'overdue') return 'เลยกำหนดมา ' . max(1, abs($days)) . ' วัน';
        if ($days <= 0) return 'ครบกำหนดวันนี้';
        return 'เหลืออีก ' . $days . ' วัน';
    };

    $badges = [
        'submitted' => ['ส่งแล้ว',     'bg-emerald-50 text-emerald-700 ring-emerald-200'],
        'late'      => ['ส่งช้า',      'bg-amber-50 text-amber-700 ring-amber-200'],
        'pending'   => ['ยังไม่ส่ง',   'bg-slate-100 text-slate-600 ring-slate-200'],
        'overdue'   => ['เลยกำหนด',   'bg-rose-50 text-rose-700 ring-rose-200'],
    ];

    $categoryNames = [
        'SOFTWARE' => 'ซอฟต์แวร์ / พัฒนาโปรแกรม', 'NETWORK' => 'เครือข่ายและระบบ',
        'DATA' => 'ข้อมูลและวิเคราะห์', 'HARDWARE' => 'ฮาร์ดแวร์และอิเล็กทรอนิกส์',
        'DESIGN' => 'ออกแบบ', 'BUSINESS' => 'ธุรกิจและบริการ', 'OTHER' => 'อื่น ๆ',
    ];
@endphp

<div class="min-h-screen flex bg-[#f5f7fa]">

    <x-teacher-sidebar />

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-10">

        <a href="/teacher/students" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-[#FA4616] mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            นักศึกษาของฉัน
        </a>

        <x-topbar-profile title="ข้อมูลนักศึกษา" />

        {{-- ข้อมูลนักศึกษา --}}
        <section class="bg-white rounded-2xl border border-slate-200 p-6 mb-6 flex flex-col sm:flex-row sm:items-center gap-5">
            <div class="w-16 h-16 rounded-full bg-orange-50 text-[#FA4616] flex items-center justify-center text-2xl font-bold shrink-0">
                {{ mb_substr($student['name'] ?? '?', 0, 1) }}
            </div>
            <div class="min-w-0">
                <h2 class="text-2xl font-bold text-slate-800">{{ $student['name'] ?? '-' }}</h2>
                <div class="mt-1 flex flex-wrap gap-x-5 gap-y-1 text-sm text-slate-500">
                    <span>รหัส <span class="font-semibold text-slate-700 tracking-wide">{{ $student['student_id'] ?? '-' }}</span></span>
                    <span>{{ $student['email'] ?? '-' }}</span>
                    <span>{{ $student['major'] ?? '-' }}</span>
                    @if(!empty($student['category']))
                        <span>ฝึกงานสาขา {{ $categoryNames[$student['category']] ?? $student['category'] }}</span>
                    @endif
                </div>
            </div>
        </section>

        {{-- ภาพรวม --}}
        <section class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            @foreach([
                ['งานทั้งหมด', $stats->total, 'text-slate-800'],
                ['ส่งแล้ว', $stats->submitted, 'text-emerald-600'],
                ['ยังไม่ส่ง', $stats->pending, 'text-slate-600'],
                ['เลยกำหนด', $stats->overdue, 'text-rose-600'],
            ] as [$label, $value, $color])
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="text-sm text-slate-500">{{ $label }}</div>
                    <div class="mt-1 text-3xl font-bold {{ $color }}">{{ $value }}</div>
                </div>
            @endforeach
            <div class="bg-white rounded-2xl border border-slate-200 p-5 col-span-2 lg:col-span-1">
                <div class="text-sm text-slate-500">คะแนนรวม (งานที่ตรวจแล้ว)</div>
                <div class="mt-1 text-3xl font-bold text-slate-800">
                    @if($stats->max > 0)
                        {{ $stats->score }}<span class="text-lg text-slate-400 font-semibold"> / {{ $stats->max }}</span>
                    @else
                        <span class="text-slate-400 text-xl">ยังไม่มี</span>
                    @endif
                </div>
                @if($stats->late > 0)
                    <div class="text-xs text-amber-600 mt-1">ส่งช้า {{ $stats->late }} งาน</div>
                @endif
            </div>
        </section>

        {{-- งานแยกตามห้อง --}}
        @forelse($groups as $g)
            <section class="bg-white rounded-2xl border border-slate-200 mb-6 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-baseline justify-between gap-2">
                    <h3 class="text-lg font-bold text-slate-800">{{ $g->name }}</h3>
                    <div class="text-sm text-slate-500">{{ $g->major }} · อาจารย์ {{ $g->teacher }}</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="stack-on-mobile w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-6 py-3 font-semibold">งาน</th>
                                <th class="px-4 py-3 font-semibold">กำหนดส่ง</th>
                                <th class="px-4 py-3 font-semibold">สถานะ</th>
                                <th class="px-4 py-3 font-semibold">ไฟล์ที่ส่ง</th>
                                <th class="px-4 py-3 font-semibold text-center">คะแนน</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($g->rows as $r)
                                @php [$label, $cls] = $badges[$r->status]; $hint = $dueHint($r); @endphp
                                <tr class="{{ $r->status === 'overdue' ? 'bg-rose-50/40' : '' }} align-top">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">{{ $r->title }}</div>
                                        @if($r->feedback)
                                            <div class="mt-1 text-xs text-slate-500 line-clamp-2" title="{{ $r->feedback }}">ความเห็น: {{ $r->feedback }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <div class="text-slate-700">{{ $thDate($r->due) }}</div>
                                        @if($hint)
                                            <div class="text-xs mt-0.5 {{ $r->status === 'overdue' ? 'text-rose-600' : 'text-slate-400' }}">{{ $hint }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold ring-1 ring-inset {{ $cls }}">{{ $label }}</span>
                                        @if($r->sent_at)
                                            <div class="text-xs text-slate-400 mt-1">ส่งเมื่อ {{ $thDate($r->sent_at) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        @forelse($r->files as $f)
                                            <a href="{{ $f->previewable ? '/teacher/view/' . $f->id : '/teacher/download/' . $f->id }}"
                                               @if($f->previewable) target="_blank" rel="noopener" @endif
                                               class="flex items-center gap-1.5 text-[#FA4616] hover:underline max-w-[220px]">
                                                <span class="text-xs shrink-0 text-slate-400">{{ $f->previewable ? 'เปิดดู' : 'โหลด' }}</span>
                                                <span class="truncate">{{ $f->name }}</span>
                                            </a>
                                        @empty
                                            <span class="text-slate-300">-</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-4 text-center whitespace-nowrap">
                                        @if($r->score !== null)
                                            <span class="font-bold text-slate-800">{{ $r->score }}</span><span class="text-slate-400">/{{ $r->max ?? '-' }}</span>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <a href="/teacher/assignment-view/{{ $r->id }}"
                                           class="inline-block px-3 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:border-[#FA4616]/40 hover:text-[#FA4616] text-xs font-semibold">
                                            {{ $r->score !== null ? 'แก้คะแนน' : 'ให้คะแนน' }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400">ห้องนี้ยังไม่มีงาน</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="bg-white rounded-2xl border border-slate-200 px-6 py-14 text-center">
                <div class="font-bold text-slate-700">นักศึกษาคนนี้ยังไม่อยู่ในห้องเรียนไหน</div>
                <div class="text-sm text-slate-500 mt-1">ห้องที่สาขาวิชาตรงกับนักศึกษาจะเพิ่มให้อัตโนมัติ</div>
            </div>
        @endforelse

    </main>
</div>

</x-layouts.app>
