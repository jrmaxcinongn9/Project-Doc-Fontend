<x-layouts.app title="Student Dashboard">

@php
    // ปรับปรุงการคำนวณเวลาให้ทำงานบน Timezone ของประเทศไทย
    $today = \Carbon\Carbon::now('Asia/Bangkok')->startOfDay();
    $assignmentsList = $assignments ?? [];

    $total = count($assignmentsList);
    $submittedCount = collect($assignmentsList)->where('submitted', true)->count();
    $notSubmittedCount = $total - $submittedCount;

    $dueSoonCount = collect($assignmentsList)->filter(function ($a) use ($today) {
        $deadline = $a['deadline'] ? \Carbon\Carbon::parse($a['deadline'])->timezone('Asia/Bangkok')->startOfDay() : null;
        if (!$deadline) return false;
        $daysLeft = $today->diffInDays($deadline, false);
        return !$a['submitted'] && $daysLeft >= 0 && $daysLeft <= 7;
    })->count();
@endphp

<div class="min-h-screen flex bg-[#f9fafb]">

    <x-student-sidebar />

    <main class="flex-1 relative pb-10">
        
        {{-- 🌟 ส่วน Topbar / Header 🌟 --}}
        <div class="flex justify-between items-start px-6 md:px-10 py-6">
            <div>
                @if($classroom)
                    <h1 class="text-[22px] font-bold text-[#3f4b5b]">ห้องเรียน: {{ $classroom->name }} ({{ $classroom->major }})</h1>
                @else
                    <h1 class="text-[22px] font-bold text-[#3f4b5b]">ยังไม่พบห้องเรียน</h1>
                @endif
            </div>

            <a href="{{ url('/student/profile') }}" class="flex items-center gap-3 cursor-pointer hover:opacity-80 transition">
                <div class="text-right">
                    <div class="text-sm font-semibold text-[#3f4b5b]">{{ Auth::user()->name ?? 'student1' }}</div>
                    <div class="text-xs text-gray-500">โปรไฟล์</div>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#e2e8f0] flex items-center justify-center text-gray-500 shadow-sm border border-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
            </a>
        </div>

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

    @if ($classroom)

        <section class="overflow-hidden rounded-3xl border border-slate-200 border-t-4 border-t-[#FA4616] bg-white shadow-sm shadow-slate-200/70">

            {{-- Header --}}
            <div class="flex flex-col gap-4 border-b border-slate-200 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#3C4043]">
                        รายการงานที่ได้รับมอบหมาย
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        ตรวจสอบกำหนดส่งและสถานะการส่งงานของคุณ
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-[#FA4616]/20 bg-white px-4 py-2 text-sm font-semibold text-[#FA4616] shadow-sm shadow-slate-200/70">
                    <span class="h-2 w-2 rounded-full bg-[#FA4616]"></span>
                    {{ count($assignmentsList) }} รายการ
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] border-collapse text-left">

                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-[#7B8189]">
                        <tr>
                            <th class="px-6 py-4">Assessment</th>
                            <th class="px-6 py-4">Due Date</th>
                            <th class="px-6 py-4">Submission</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @forelse ($assignmentsList as $a)

                            @php
                                $publish = !empty($a['publish_date'])
                                    ? \Carbon\Carbon::parse($a['publish_date'])->timezone('Asia/Bangkok')
                                    : null;

                                $deadline = !empty($a['deadline'])
                                    ? \Carbon\Carbon::parse($a['deadline'])->timezone('Asia/Bangkok')
                                    : null;

                                $status = $a['status'] ?? '';

                                $hasSubmitted =
                                    ($a['submitted'] ?? false) ||
                                    in_array($status, ['Submitted', 'Late Submitted']);

                                $isLateSubmitted = $status === 'Late Submitted';

                                $isOverdue =
                                    $deadline &&
                                    $deadline->isPast() &&
                                    !$hasSubmitted;
                            @endphp

                            <tr class="group bg-white transition duration-200 hover:bg-slate-50/80">

                                {{-- Assignment information --}}
                                <td class="w-[42%] px-6 py-5 align-top">
                                    <div class="flex items-start gap-4">

                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#FA4616] to-[#FFC72C] text-white shadow-sm shadow-orange-200 transition group-hover:scale-105">
                                            <svg
                                                class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                                />
                                            </svg>
                                        </div>

                                        <div class="min-w-0">
                                            <h3 class="line-clamp-2 text-base font-bold text-slate-800">
                                                {{ $a['title'] }}
                                            </h3>

                                            <p class="mt-1 text-sm text-slate-500">
                                                โดย {{ $a['teacher_name'] ?? 'อาจารย์ผู้สอน' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                                <span class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-[#C73A10] ring-1 ring-inset ring-slate-200">
                                                    {{ $a['work_type'] ?? 'งานทั่วไป' }}
                                                </span>

                                                @if ($publish)
                                                    <span class="text-xs text-slate-400">
                                                        เผยแพร่ {{ $publish->format('d M Y, H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Deadline --}}
                                <td class="px-6 py-5 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                            {{ $isOverdue ? 'bg-red-50 text-red-500' : 'bg-slate-100 text-[#FA4616]' }}">

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                                />
                                            </svg>
                                        </div>

                                        <div>
                                            @if ($deadline)
                                                <p class="text-sm font-semibold {{ $isOverdue ? 'text-red-500' : 'text-slate-700' }}">
                                                    {{ $deadline->format('d M Y') }}
                                                </p>

                                                <p class="mt-0.5 text-xs {{ $isOverdue ? 'text-red-400' : 'text-slate-400' }}">
                                                    เวลา {{ $deadline->format('H:i') }} น.
                                                </p>

                                                @if ($isOverdue)
                                                    <p class="mt-1 text-xs font-semibold text-red-500">
                                                        เลยกำหนดส่งแล้ว
                                                    </p>
                                                @endif
                                            @else
                                                <p class="text-sm font-medium text-slate-500">
                                                    ไม่มีกำหนดส่ง
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Submission status --}}
                                <td class="px-6 py-5 align-top">
                                    @if ($isLateSubmitted)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-600 ring-1 ring-inset ring-amber-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                            Late Submitted
                                        </span>
                                    @elseif ($hasSubmitted)
                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-600 ring-1 ring-inset ring-emerald-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Submitted
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 rounded-full bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-500 ring-1 ring-inset ring-slate-200">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Not Submitted
                                        </span>
                                    @endif
                                </td>

                                {{-- Action --}}
                                <td class="px-6 py-5 text-right align-top">
                                    <div class="flex justify-end">
                                        <button
                                            type="button"
                                            data-assignment-id="{{ $a['id'] }}"
                                            data-assignment-title="{{ $a['title'] }}"
                                            data-submitted="{{ $hasSubmitted ? 'true' : 'false' }}"
                                            data-files='@json($a['files'] ?? [])'
                                            onclick="openSubmitModal(
                                                this.dataset.assignmentId,
                                                this.dataset.assignmentTitle,
                                                this.dataset.submitted === 'true',
                                                this
                                            )"
                                            class="inline-flex min-w-[130px] items-center justify-center gap-2 rounded-xl bg-[#FA4616] px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-orange-200 transition hover:-translate-y-0.5 hover:bg-[#D83A0D] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#FA4616] focus:ring-offset-2"
                                        >
                                            @if ($hasSubmitted)
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-7.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                                    />
                                                </svg>

                                                แก้ไขงาน
                                            @else
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M12 4v16m8-8H4"
                                                    />
                                                </svg>

                                                ส่งงาน
                                            @endif
                                        </button>
                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-[#FA4616] ring-1 ring-inset ring-slate-200">
                                        <svg
                                            class="h-8 w-8"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="mt-4 font-bold text-slate-700">
                                        ยังไม่มีรายการงาน
                                    </h3>

                                    <p class="mt-1 text-sm text-slate-500">
                                        งานที่ได้รับมอบหมายจะแสดงในบริเวณนี้
                                    </p>
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </section>

    @else

        <div class="rounded-3xl border border-slate-200 border-t-4 border-t-[#FA4616] bg-white px-6 py-16 text-center shadow-sm shadow-slate-200/70">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-[#FA4616] ring-1 ring-inset ring-slate-200">
                <svg
                    class="h-8 w-8"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.5"
                        d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422A12.083 12.083 0 0118 14.5c0 1.91.445 3.62 1.16 4.752M12 14l-6.16-3.422A12.083 12.083 0 006 14.5c0 1.91-.445 3.62-1.16 4.752"
                    />
                </svg>
            </div>

            <h3 class="mt-4 text-lg font-bold text-slate-800">
                ไม่พบข้อมูลห้องเรียน
            </h3>

            <p class="mt-2 text-sm text-slate-500">
                ยังไม่พบข้อมูลห้องเรียนของคุณในขณะนี้
            </p>
        </div>

    @endif

</div>

{{-- 📝 Modal (รองรับหลายไฟล์ + ดาวน์โหลด/ลบ) --}}
@if($classroom)
<div id="submitModal" class="hidden fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-opacity duration-300">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300 flex flex-col max-h-[90vh]" id="modalContent">
        
        <form id="submitForm" action="{{ route('student.assignment.submit') }}" method="POST" enctype="multipart/form-data" class="flex flex-col overflow-hidden h-full">
            @csrf
            <input type="hidden" name="assignment_id" id="modalAssignmentId" value="">
            
            {{-- Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 flex justify-between items-start shrink-0">
                <div>
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-800">ส่งงาน</h3>
                    <p id="modalAssignmentName" class="text-sm text-gray-500 mt-1 line-clamp-1 font-medium"></p>
                </div>
                <button type="button" onclick="closeSubmitModal()" class="text-gray-400 hover:text-red-500 transition-colors rounded-full p-1 hover:bg-red-50 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-4 overflow-y-auto">
                
                {{-- ส่วนแสดงรายการไฟล์เดิมที่ส่งแล้ว --}}
                <div id="previousFileSection" class="hidden flex flex-col gap-2">
                    <span class="text-xs font-semibold text-[#FA4616] uppercase tracking-wide">ไฟล์ที่ส่งแล้ว</span>
                    <div id="fileListContainer" class="flex flex-col gap-2">
                        {{-- JS จะ Render DOM ลงที่นี่ --}}
                    </div>
                </div>

                {{-- ส่วนอัปโหลดไฟล์ใหม่ --}}
                <div>
                    <div class="flex items-center justify-center w-full">
                        <label for="dropzone-file" class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 hover:border-[#FA4616] transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                <div class="p-2.5 bg-white rounded-full shadow-sm mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-[#FA4616]" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg> 
                                </div> 
                                <p class="mb-1 text-sm text-gray-600"><span class="font-semibold text-[#FA4616]">คลิกเพื่อเลือกไฟล์</span> (เลือกได้หลายไฟล์)</p> 
                                <p class="text-xs text-gray-400 mt-1" id="selectedFileName">แนบไฟล์เพิ่ม (สูงสุด 10MB)</p> 
                            </div> 
                            <input id="dropzone-file" type="file" name="files[]" multiple class="hidden" onchange="updateFileName(this)" /> 
                        </label> 
                    </div> 
                </div> 
            </div> 
 
            {{-- Footer --}} 
            <div class="px-6 py-4 bg-gray-50 flex justify-end gap-3 border-t border-gray-100 rounded-b-2xl shrink-0"> 
                <button type="button" onclick="closeSubmitModal()" class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-100 transition-colors focus:ring-2 focus:ring-gray-200 focus:outline-none"> 
                    ยกเลิก 
                </button> 
                <button type="submit" id="submitBtn" class="px-5 py-2.5 text-sm font-medium text-white bg-[#FA4616] rounded-lg hover:bg-[#D83A0D] transition-colors shadow-sm focus:ring-2 focus:ring-[#FA4616]/30 focus:outline-none flex items-center gap-2"> 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg> 
                    <span id="submitBtnText">ยืนยันการส่งงาน</span> 
                </button> 
            </div> 
        </form> 
         
    </div> 
</div>

<script>
    function openSubmitModal(id, title, isSubmitted, btnElement) {
        const modal = document.getElementById('submitModal');
        const modalContent = document.getElementById('modalContent');
        
        document.getElementById('modalTitle').innerText = isSubmitted ? 'แก้ไขการส่งงาน' : 'ส่งงานใหม่';
        document.getElementById('modalAssignmentName').innerText = title;
        document.getElementById('modalAssignmentId').value = id;

        const fileInput = document.getElementById('dropzone-file');
        const prevSection = document.getElementById('previousFileSection');
        const fileListContainer = document.getElementById('fileListContainer');
        const btnText = document.getElementById('submitBtnText');

        let files = [];
        if (btnElement && btnElement.dataset.files) {
            try { files = JSON.parse(btnElement.dataset.files); } catch(e) {}
        }

        fileListContainer.innerHTML = '';

        if (files.length > 0) {
            prevSection.classList.remove('hidden');
            fileInput.removeAttribute('required'); 
            btnText.innerText = 'อัปเดตไฟล์งาน (ส่งเพิ่ม)';

            files.forEach(file => {
                const fileHtml = `
                    <div class="flex items-center justify-between bg-[#f8fafc] border border-blue-100 p-3 rounded-xl transition hover:border-blue-200" id="file-item-${file.id}">
                        <div class="flex items-center gap-3 overflow-hidden min-w-0 flex-1">
                            <svg class="w-5 h-5 text-blue-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                            <span class="text-sm text-gray-700 font-medium truncate" title="${file.name}">${file.name}</span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0 ml-2">
                            <a href="${file.download_url}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="ดาวน์โหลด">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </a>
                            <button type="button" onclick="deleteExistingFile('${file.id}')" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="ลบไฟล์">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                `;
                fileListContainer.insertAdjacentHTML('beforeend', fileHtml);
            });
        } else {
            prevSection.classList.add('hidden');
            fileInput.setAttribute('required', 'required');
            btnText.innerText = 'ยืนยันการส่งงาน';
        }

        document.getElementById('selectedFileName').innerText = 'แนบไฟล์เพิ่ม (สูงสุด 10MB)';
        fileInput.value = '';

        modal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function closeSubmitModal() {
        const modal = document.getElementById('submitModal');
        const modalContent = document.getElementById('modalContent');
        
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 300);
    }

    function updateFileName(input) {
        const textElement = document.getElementById('selectedFileName');
        if (input.files && input.files.length > 0) {
            if (input.files.length === 1) {
                textElement.innerHTML = `<span class="text-blue-600 font-semibold">${input.files[0].name}</span>`;
            } else {
                textElement.innerHTML = `<span class="text-blue-600 font-semibold">เลือกไฟล์ทั้งหมด ${input.files.length} ไฟล์</span>`;
            }
        } else {
            textElement.innerText = 'แนบไฟล์เพิ่ม (สูงสุด 10MB)';
        }
    }

    async function deleteExistingFile(fileId) {
        if(confirm('คุณแน่ใจหรือไม่ว่าต้องการลบไฟล์นี้?')) {
            const fileItem = document.getElementById(`file-item-${fileId}`);
            if(fileItem) fileItem.style.opacity = '0.5';

            try {
                const response = await fetch(`/student/files/delete/${fileId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();
                if(data.success) {
                    fileItem.remove();
                    if(document.getElementById('fileListContainer').children.length === 0) {
                        document.getElementById('previousFileSection').classList.add('hidden');
                        document.getElementById('dropzone-file').setAttribute('required', 'required');
                        document.getElementById('submitBtnText').innerText = 'ยืนยันการส่งงาน';
                    }
                } else {
                    alert('ลบไฟล์ไม่สำเร็จ: ' + data.message);
                    if(fileItem) fileItem.style.opacity = '1';
                }
            } catch (error) {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                if(fileItem) fileItem.style.opacity = '1';
            }
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSubmitModal();
    });
</script>
@endif

</x-layouts.app>