<x-layouts.app title="Assignment Detail">

<div class="min-h-screen flex bg-slate-50 text-slate-800">

    <x-teacher-sidebar />

    <main class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">

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
                <table class="stack-on-mobile w-full text-left border-collapse">
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

                                            <a href="{{ !empty($doc->previewable) ? '/teacher/view/' . $doc->id : '/teacher/download/' . $doc->id }}"
                                               @if(!empty($doc->previewable)) target="_blank" rel="noopener" @endif
                                               title="{{ !empty($doc->previewable) ? 'เปิดดูในแท็บใหม่' : 'ไฟล์ชนิดนี้เปิดในเบราว์เซอร์ไม่ได้ ต้องดาวน์โหลด' }}"
                                               class="text-[#FA4616] text-sm font-semibold hover:text-[#D83A0D] hover:underline truncate max-w-[200px]">
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
                                            {{-- เปิดดูได้เฉพาะ PDF รูปภาพ และข้อความ ไฟล์ Word หรือ PowerPoint ต้องดาวน์โหลด --}}
                                            @if(!empty($doc->previewable))
                                            <a href="/teacher/view/{{ $doc->id }}" target="_blank" rel="noopener"
                                               class="mr-2 px-3 py-1.5 rounded-lg bg-[#FA4616] text-white shadow-sm hover:bg-[#D83A0D] transition-all font-semibold text-sm flex items-center gap-1.5">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12s-3.75 6.75-9.75 6.75S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                                                เปิดดู
                                            </a>
                                            @endif
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

        {{-- ✍️ ให้คะแนนและเขียนความเห็น
             อาจารย์บอกว่าครูต้องให้ feedback กลับได้ ไม่ใช่รับงานเฉย ๆ
             และกรอกคะแนนเต็มตอนสร้างงาน แล้วให้คะแนนทีหลัง --}}
        <div class="bg-white p-6 rounded-xl shadow mt-8">

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">ให้คะแนนและความเห็น</h3>
                    <p class="text-sm text-slate-500 mt-0.5">
                        @if($maxScore)
                            คะแนนเต็มของงานชิ้นนี้ {{ $maxScore }} คะแนน
                        @else
                            งานชิ้นนี้ยังไม่ได้ตั้งคะแนนเต็ม ตั้งที่ช่องด้านขวาก่อนจึงจะใส่คะแนนได้
                        @endif
                    </p>
                </div>

                <form method="POST" action="/teacher/assignment/{{ $assignmentId }}/max-score"
                      class="flex items-end gap-2">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-[12px] text-slate-500 mb-1">
                            {{ $maxScore ? 'แก้คะแนนเต็ม' : 'ตั้งคะแนนเต็ม' }}
                        </label>
                        <input type="number" name="max_score" min="1" max="1000" required
                               value="{{ old('max_score', $maxScore) }}"
                               class="w-24 border border-slate-200 px-3 py-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522]">
                    </div>
                    <button type="submit"
                            class="px-4 py-2 rounded-xl border border-[#F26522] text-[#F26522] hover:bg-[#F26522] hover:text-white text-sm font-semibold transition-colors">
                        บันทึก
                    </button>
                </form>
            </div>

            @if($errors->has('max_score'))
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
                {{ $errors->first('max_score') }}
            </div>
            @endif

            @if(session('success'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl text-sm">
                {{ session('success') }}
            </div>
            @endif

            @if($errors->has('grade') || $errors->has('score'))
            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
                {{ $errors->first('grade') ?: $errors->first('score') }}
            </div>
            @endif

            <div class="space-y-3">
                {{-- ฟอร์มเดียวครอบทุกแถว กดบันทึกครั้งเดียวได้ทุกคน
                     เดิมแต่ละคนเป็นฟอร์มแยก กดบันทึกแถวหนึ่ง หน้าโหลดใหม่ คะแนนที่กรอกในแถวอื่นหายหมด --}}
                <form id="gradeAllForm" method="POST" action="/teacher/assignment/{{ $assignmentId }}/grades" class="space-y-3">
                @csrf
                @method('PATCH')

                @forelse($gradeRows ?? [] as $row)
                <div class="grade-row border border-slate-200 rounded-2xl p-4 hover:bg-slate-50/50 transition-colors"
                     data-student="{{ $row->student_id }}">

                    {{-- ค่าเดิม ใช้เทียบว่าแถวไหนถูกแก้ จะได้ส่งเฉพาะแถวนั้น --}}
                    <input type="hidden" name="rows[{{ $row->student_id }}][orig_score]" value="{{ $row->score }}">
                    <input type="hidden" name="rows[{{ $row->student_id }}][orig_feedback]" value="{{ $row->feedback }}">

                    <div class="flex flex-wrap items-start gap-4">

                        <div class="min-w-[200px] flex-1">
                            <div class="font-semibold text-slate-800">{{ $row->name }}</div>
                            <div class="text-xs text-slate-500">{{ $row->email }}</div>
                            @if($row->submitted)
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-semibold">ส่งแล้ว</span>
                                {{-- เปิดไฟล์ของคนนี้ได้จากแถวให้คะแนนเลย --}}
                                <div class="mt-2 space-y-1">
                                    @foreach($row->files ?? [] as $f)
                                        <a href="{{ !empty($f->previewable) ? '/teacher/view/' . $f->id : '/teacher/download/' . $f->id }}"
                                           @if(!empty($f->previewable)) target="_blank" rel="noopener" @endif
                                           class="flex items-center gap-1.5 text-xs text-[#FA4616] hover:underline max-w-[220px]">
                                            <span class="shrink-0">{{ !empty($f->previewable) ? 'เปิดดู' : 'ดาวน์โหลด' }}:</span>
                                            <span class="truncate">{{ $f->name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-semibold">ยังไม่ส่ง</span>
                            @endif
                        </div>

                        <div class="w-28">
                            <label class="block text-[12px] text-slate-500 mb-1">คะแนน</label>
                            <input type="number" name="rows[{{ $row->student_id }}][score]" min="0"
                                   @if($maxScore) max="{{ $maxScore }}" @else disabled @endif
                                   value="{{ old('rows.' . $row->student_id . '.score', $row->score) }}"
                                   placeholder="{{ $maxScore ? '0-' . $maxScore : 'ยังไม่มี' }}"
                                   class="grade-input w-full border border-slate-200 px-3 py-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] disabled:bg-slate-100 disabled:text-slate-400">
                        </div>

                        <div class="flex-[2] min-w-[240px]">
                            <label class="block text-[12px] text-slate-500 mb-1">ความเห็นถึงนักศึกษา</label>
                            <textarea name="rows[{{ $row->student_id }}][feedback]" rows="2"
                                      class="grade-input w-full border border-slate-200 px-3 py-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522]">{{ old('rows.' . $row->student_id . '.feedback', $row->feedback) }}</textarea>
                        </div>

                    </div>

                    <div class="flex justify-between mt-2 text-[11px]">
                        <span class="text-slate-400">@if($row->feedback_by) ให้โดย {{ $row->feedback_by }} @endif</span>
                        <span class="row-dirty hidden font-semibold text-amber-600">ยังไม่ได้บันทึก</span>
                    </div>
                </div>
                @empty
                <div class="text-center text-slate-400 text-sm py-10">
                    ยังไม่มีรายชื่อนักศึกษาในงานชิ้นนี้
                </div>
                @endforelse

                @if(count($gradeRows ?? []))
                <div class="sticky bottom-4 flex items-center justify-end gap-3 bg-white/90 backdrop-blur border border-slate-200 rounded-2xl px-4 py-3 shadow-sm">
                    <span id="dirtyCount" class="text-sm text-slate-500">ยังไม่มีการแก้ไข</span>
                    <button type="submit" id="saveAllBtn"
                            class="px-6 py-2.5 rounded-xl bg-[#F26522] hover:bg-[#d8561b] text-white text-sm font-semibold transition-colors disabled:opacity-50">
                        บันทึกทั้งหมด
                    </button>
                </div>
                @endif
                </form>

                <script>
                (function () {
                    const form = document.getElementById('gradeAllForm');
                    if (!form) return;
                    let saving = false;

                    // แถวที่ค่าไม่ตรงกับค่าเดิม
                    function dirtyRows() {
                        return [...form.querySelectorAll('.grade-row')].filter(function (row) {
                            const sid = row.dataset.student;
                            const get = n => (form.querySelector('[name="rows[' + sid + '][' + n + ']"]') || {}).value ?? '';
                            return get('score') !== get('orig_score') || get('feedback') !== get('orig_feedback');
                        });
                    }

                    function refresh() {
                        const dirty = dirtyRows();
                        form.querySelectorAll('.grade-row').forEach(r => r.querySelector('.row-dirty').classList.toggle('hidden', !dirty.includes(r)));
                        document.getElementById('dirtyCount').textContent =
                            dirty.length ? 'แก้ไขแล้ว ' + dirty.length + ' คน ยังไม่ได้บันทึก' : 'ยังไม่มีการแก้ไข';
                    }

                    form.addEventListener('input', refresh);
                    form.addEventListener('submit', function () { saving = true; });

                    // เตือนก่อนออกจากหน้า ถ้ายังมีคะแนนที่ไม่ได้บันทึก
                    window.addEventListener('beforeunload', function (e) {
                        if (!saving && dirtyRows().length) { e.preventDefault(); e.returnValue = ''; }
                    });

                    refresh();
                })();
                </script>
            </div>

        </div>

    </main>
</div>

</x-layouts.app>