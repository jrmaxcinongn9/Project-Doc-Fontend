<x-layouts.app title="Classroom Detail">

<div class="min-h-screen flex bg-slate-50 text-slate-800">

    <x-teacher-sidebar />

    <main class="flex-1 p-8 md:p-10 lg:p-12">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight">
                    {{ $classroom->name }}
                </h2>
                <p class="text-slate-500 mt-1 text-sm">
                    {{ $classroom->major }} <span class="mx-1 text-slate-300">|</span> ปีการศึกษา {{ $classroom->academicYear }}
                </p>
            </div>

            <a href="/teacher/classroom"
               class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-600 shadow-sm hover:bg-slate-50 hover:text-slate-800 transition-all text-sm font-medium flex items-center gap-1">
                ← กลับหน้าหลัก
            </a>
        </div>

   

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden mb-10">
            
            <div class="p-6 md:p-8 border-b border-slate-100 bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-[#FA4616] rounded-full inline-block"></span>
                    รายชื่อนักเรียนในห้องเรียน
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-sm uppercase tracking-wider bg-white">
                            <th class="p-5 font-semibold">รหัสนักศึกษา</th>
                            <th class="p-5 font-semibold">ชื่อ-นามสกุล</th>
                            <th class="p-5 font-semibold text-right">การจัดการ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-50">
                        @forelse($classroom->students as $s)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <td class="p-5 text-sm font-medium text-slate-600">
                                    {{ $s->student_id }}
                                </td>
                                <td class="p-5 font-semibold text-slate-700">
                                    {{ $s->name }}
                                </td>
                                <td class="p-5 text-right">
                                    <span class="text-slate-400 text-xs bg-slate-100 px-2.5 py-1 rounded-md font-medium">
                                        ไม่มีการดำเนินการ
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center py-4">
                                        <p class="text-base font-medium text-slate-500">ยังไม่มีรายชื่อนักเรียนในห้องนี้</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden mb-10">
            
            <div class="p-6 md:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-[#FA4616] rounded-full inline-block"></span>
                    มอบหมายงาน
                </h3>
                
                <button onclick="openAssignmentModal()"
                    class="px-4 py-2 bg-[#FA4616] text-white text-sm font-medium rounded-lg 
                           shadow-md shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 
                           hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 
                           flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    สร้างงานใหม่
                </button>
            </div>

            <div class="p-6 md:p-8 space-y-3">
                @forelse($classroom->assignments as $a)
                    <div class="p-4 border border-slate-100 rounded-xl flex flex-col sm:flex-row justify-between sm:items-center bg-white hover:border-[#FA4616]/30 hover:bg-slate-50/80 transition shadow-sm group gap-4">
                        <div>
                            <span class="text-slate-700 font-semibold group-hover:text-[#FA4616] transition block mb-1">
                                {{ $a['assignment_name'] }}
                            </span>
                            {{-- 📅 แสดงเวลาที่ระบบสร้างชิ้นงาน และกำหนดส่ง --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium text-slate-400">
                                <span>สั่งเมื่อ: <span class="text-slate-600 font-semibold">{{ isset($a['created_at']) ? \Carbon\Carbon::parse($a['created_at'])->format('d/m/Y H:i') : (isset($a['publish_date']) ? \Carbon\Carbon::parse($a['publish_date'])->format('d/m/Y H:i') : '-') }}</span></span>
                                <span class="text-slate-200">|</span>
                                <span>กำหนดส่ง: <span class="text-rose-600 font-bold">{{ isset($a['due_date']) ? \Carbon\Carbon::parse($a['due_date'])->format('d/m/Y H:i') : 'ไม่มีกำหนดส่ง' }}</span></span>
                            </div>
                        </div>

                        <div class="inline-flex items-center justify-end gap-2 opacity-95 transition">
                            <a href="/teacher/assignment-view/{{ $a['id'] }}" 
                               class="px-3 py-1.5 rounded-lg bg-white text-[#FA4616] border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-[#FA4616]/40 hover:shadow transition-all font-semibold text-sm flex items-center">
                                ดูงาน
                            </a>

                            <button type="button" 
                                    onclick="openEditModal('{{ $a['id'] }}', '{{ $a['assignment_name'] }}', '{{ isset($a['due_date']) ? \Carbon\Carbon::parse($a['due_date'])->format('Y-m-d\TH:i') : '' }}')"
                                    class="px-3 py-1.5 rounded-lg bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:shadow transition-all font-medium text-sm flex items-center">
                                แก้ไข
                            </button>

                            <form action="/teacher/assignment/{{ $a['id'] }}" method="POST" 
                                  onsubmit="return confirm('ยืนยันการลบงานนี้หรือไม่?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-red-50 text-red-700 border border-red-200 shadow-sm hover:bg-red-100 hover:border-red-300 hover:shadow transition-all font-medium text-sm flex items-center">
                                    ลบ
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 border-2 border-dashed border-slate-100 rounded-2xl bg-slate-50/30">
                        <p class="text-slate-400 text-sm font-medium">ยังไม่มีรายการสั่งงานในห้องเรียนนี้</p>
                    </div>
                @endforelse
            </div>
        </div>

    </main>
</div>

<div id="assignmentModal"
     class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-opacity">

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all animate-[scaleIn_.2s_ease-out]">

        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-slate-100 text-[#FA4616] flex items-center justify-center font-bold">
                    +
                </span>
                สร้างภาระงานใหม่
            </h3>
            <button onclick="closeAssignmentModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition">
                ✕
            </button>
        </div>

        <form action="/teacher/classroom/{{ $classroom->id }}/assignment" method="POST">
            @csrf

            <div class="p-6 space-y-5">
                <div>
                    <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ชื่องาน</label>
                    <input name="assignment_name" required placeholder="เช่น การบ้านบทที่ 1"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                </div>

                <div>
                    <label class="text-sm font-semibold text-rose-600 mb-1.5 block">วันสิ้นสุดกำหนดส่ง (Due Date)</label>
                    <input type="datetime-local" name="due_date" required
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:bg-white outline-none transition">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-600 mb-1.5 block">รายละเอียดงาน</label>
                    <textarea name="description" placeholder="ระบุข้อกำหนด เงื่อนไข หรือคำอธิบายเพิ่มเติม..." rows="3"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition"></textarea>
                </div>

                <input type="hidden" name="classroom" value="{{ $classroom->id }}">
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" onclick="closeAssignmentModal()"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-800 transition">
                    ยกเลิก
                </button>

                <button type="submit"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl bg-[#FA4616] text-white shadow-lg shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 hover:-translate-y-0.5 transition">
                    มอบหมายงาน
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-opacity">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all animate-[scaleIn_.2s_ease-out]">
        
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-slate-100 text-[#FA4616] flex items-center justify-center">
                    ✎
                </span>
                แก้ไขข้อมูลงาน
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition">✕</button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT') 
            
            <div class="p-6 space-y-5">
                <div>
                    <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ชื่องาน</label>
                    <input id="editName" name="assignment_name" required
                           class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                </div>

                <div>
                    <label class="text-sm font-semibold text-rose-600 mb-1.5 block">วันสิ้นสุดกำหนดส่ง (Due Date)</label>
                    <input id="editDueDate" type="datetime-local" name="due_date" required
                           class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 focus:bg-white outline-none transition">
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-800 transition">ยกเลิก</button>
                <button type="submit" class="px-5 py-2.5 text-sm font-medium rounded-xl bg-[#FA4616] text-white shadow-lg shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 hover:-translate-y-0.5 transition">บันทึกการแก้ไข</button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<script>
// 💡 ถอดตัวแปรข้อมูลวันเริ่มออก เหลือรับเฉพาะค่ากำหนดส่ง (dueDate)
function openEditModal(id, name, dueDate) {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editName').value = name;
    document.getElementById('editDueDate').value = dueDate || '';
    document.getElementById('editForm').action = '/teacher/assignment/' + id;
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function openAssignmentModal() {
    document.getElementById('assignmentModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeAssignmentModal() {
    document.getElementById('assignmentModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAssignmentModal();
        closeEditModal();
    }
});
</script>
</x-layouts.app>