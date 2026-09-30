<x-layouts.app title="Assignment Detail">

<div class="min-h-screen flex bg-slate-50 text-slate-800">

    <x-teacher-sidebar />

    <main class="flex-1 p-8 md:p-10 lg:p-12 pt-[calc(3.5rem+2rem)] lg:pt-12">

        {{-- แสดงชื่อ Assignment ที่ส่งมาจาก Controller --}}
        <div class="mb-10">
            <x-topbar-profile title="งาน — {{ $assignmentName ?? ($assignment['assignment_name'] ?? 'รายละเอียดงาน') }}" />

            <div class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">รายละเอียดการส่งงาน</h2>
                    <p class="text-slate-500 mt-1 text-sm">ตรวจสอบสถานะการส่งภาระงานและดาวน์โหลดไฟล์ของนักศึกษา</p>
                </div>
            </div>
        </div>

        {{-- สรุปสถานะ --}}
        <div class="flex flex-wrap items-center gap-4 mb-8">
            <div class="px-5 py-2.5 bg-emerald-50 text-emerald-700 rounded-xl text-sm font-semibold shadow-sm border border-emerald-200/60 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75l2.25 2.25L15 9.75m6 2.25a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ส่งแล้ว: {{ $submitted ?? 0 }} คน
            </div>

            <div class="px-5 py-2.5 bg-rose-50 text-rose-600 rounded-xl text-sm font-semibold shadow-sm border border-rose-200/60 flex items-center gap-2">
                <svg class="w-5 h-5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m5-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ค้างส่ง: {{ $notSubmitted ?? 0 }} / ทั้งหมด {{ $totalStudents ?? 0 }} คน
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden mb-10">

            <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-[#FA4616] rounded-full inline-block"></span>
                    สถานะการส่งรายบุคคล
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-sm uppercase tracking-wider bg-white">
                            <th class="p-5 font-semibold">รหัสนักศึกษา</th>
                            <th class="p-5 font-semibold">ชื่อนักศึกษา</th>
                            <th class="p-5 font-semibold">ไฟล์งาน</th>
                            <th class="p-5 font-semibold">วันที่ส่ง</th>
                            <th class="p-5 font-semibold text-right">การจัดการ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-50">
                        @forelse($documents ?? ($assignment['students'] ?? []) as $doc)
                            @php
                                // ยืดหยุ่นรองรับกรณีที่ตัวแปรส่งมาเป็น Object หรือ Array
                                $doc = (object) $doc;
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition duration-150">

                                {{-- รหัสนักศึกษา --}}
                                <td class="p-5 text-sm font-medium text-slate-600">
                                    {{ $doc->student_id ?? 'N/A' }}
                                </td>

                                {{-- ชื่อนักศึกษา --}}
                                <td class="p-5 font-semibold text-slate-700">
                                    {{ $doc->user ?? ($doc->name ?? 'ไม่ระบุชื่อ') }}
                                </td>

                                {{-- ไฟล์งาน --}}
                                <td class="p-5">
                                    @if(!empty($doc->file))
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-[#FA4616] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.25 2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V7.5m-5.25-5.25L19.5 7.5m-5.25-5.25V7.5h5.25M12 10.5v6m0 0l-2.25-2.25M12 16.5l2.25-2.25" />
                                            </svg>

                                            <a href="/teacher/download/{{ $doc->id }}" class="text-[#FA4616] text-sm font-semibold hover:text-[#D83A0D] hover:underline truncate max-w-[200px]">
                                                {{ $doc->name ?? 'ดาวน์โหลดไฟล์' }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-600 border border-rose-100">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                            </svg>
                                            ยังไม่ส่ง
                                        </span>
                                    @endif
                                </td>

                                {{-- วันที่ส่ง --}}
                                <td class="p-5 text-sm text-slate-500 font-medium">
                                    {{ !empty($doc->date) ? \Carbon\Carbon::parse($doc->date)->format('d/m/Y H:i') : '-' }}
                                </td>

                                {{-- การจัดการ --}}
                                <td class="p-5 text-right">
                                    <div class="inline-flex items-center justify-end">
                                        @if(!empty($doc->file))
                                            <a href="/teacher/download/{{ $doc->id }}"
                                               class="px-3 py-1.5 rounded-lg bg-white text-[#FA4616] border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-[#FA4616]/40 hover:shadow transition-all font-semibold text-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M5 21h14" />
                                                </svg>
                                                ดาวน์โหลดไฟล์
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs bg-slate-100 px-3 py-1.5 rounded-lg font-medium border border-slate-200/50 cursor-not-allowed flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                                ไม่มีไฟล์ให้โหลด
                                            </span>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <svg class="w-16 h-16 mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <p class="text-base font-medium text-slate-500">ไม่มีรายชื่อนักศึกษาในขณะนี้</p>
                                    </div>
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