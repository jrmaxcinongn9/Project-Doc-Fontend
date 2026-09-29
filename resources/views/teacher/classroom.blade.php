<x-layouts.app title="Teacher Classroom">

<div class="min-h-screen flex bg-slate-50 text-slate-800">

    <x-teacher-sidebar />

    <main class="flex-1 p-8 md:p-10 lg:p-12">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Classroom Management</h2>
                <p class="text-slate-500 mt-1 text-sm">จัดการห้องเรียนและข้อมูลนักศึกษาของคุณ</p>
            </div>
            
            <div class="flex items-center gap-4">
                <button onclick="openCreateModal()"
                    class="px-4 py-2 bg-[#FA4616] text-white text-sm font-medium rounded-lg 
                           shadow-md shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 
                           hover:-translate-y-0.5 active:translate-y-0 transition-all duration-200 
                           flex items-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    สร้าง
                </button>
                <x-topbar-profile />
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden mb-10">
            
            <div class="p-6 md:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="text-xl font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2 h-6 bg-[#FA4616] rounded-full inline-block"></span>
                    ห้องเรียนของคุณ
                </h3>
                
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-sm uppercase tracking-wider bg-white">
                            <th class="p-5 font-semibold">ชื่อห้องเรียน</th>
                            <th class="p-5 font-semibold">สาขาวิชา</th>
                            <th class="p-5 font-semibold">ปีการศึกษา</th>
                            <th class="p-5 font-semibold text-right">การจัดการ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-50">
                        @forelse($classrooms as $c)
                            @php
                                $classId = $c->id ?? ''; 
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition duration-150 group">
                                <td class="p-5">
                                    <div class="font-bold text-slate-700">{{ $c->name ?? 'ไม่มีชื่อห้องเรียน' }}</div>
                                </td>
                                <td class="p-5">
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-sm font-medium">
                                        {{ $c->major ?? '-' }}
                                    </span>
                                </td>
                                <td class="p-5 text-slate-600 font-medium">
                                    {{ $c->academicYear ?? '-' }}
                                </td>
<td class="p-5 text-right">
    <div class="inline-flex items-center justify-end gap-2 opacity-80 group-hover:opacity-100 transition">
        
        <!-- ปุ่มจัดการ: เด่นที่สุดด้วยขนาด (px-4) และความหนาตัวอักษร (font-semibold) -->
        <a href="/teacher/classroom/{{ $c->id }}"
           class="px-4 py-2 rounded-lg bg-white text-[#FA4616] border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-[#FA4616]/40 hover:shadow transition-all font-semibold text-sm flex items-center gap-1">
            จัดการ
        </a>
        
        <!-- ปุ่มแก้ไข: ใช้ดีไซน์แบบมีขอบธีมสีเหลือง/น้ำตาล (Amber) ตัวหนังสือหนาปานกลาง (font-medium) -->
        <button type="button"
            onclick="openEditModal('{{ $c->id }}', '{{ $c->name }}', '{{ $c->major }}', '{{ $c->academicYear }}')"
            class="px-3 py-2 rounded-lg bg-white text-slate-600 border border-slate-200 shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:shadow transition-all font-medium text-sm flex items-center gap-1">
            แก้ไข
        </button>
        
        <!-- ปุ่มลบ: ใช้ดีไซน์แบบมีขอบธีมสีแดง (Red) ตัวหนังสือหนาปานกลาง (font-medium) -->
        <a href="/teacher/classroom/{{ $classId }}/delete"
            onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบห้องเรียนนี้? ข้อมูลทั้งหมดที่เกี่ยวข้องจะหายไป');"
            class="px-3 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 shadow-sm hover:bg-red-100 hover:border-red-300 hover:shadow transition-all font-medium text-sm flex items-center gap-1">
            ลบ
        </a>
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-16 h-16 mb-4 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        <p class="text-lg font-medium text-slate-500">ยังไม่มีข้อมูลห้องเรียน</p>
                                        <p class="text-sm mt-1">คลิกที่ปุ่ม "สร้างห้องเรียน" เพื่อเริ่มต้น</p>
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

{{-- 🟠 Modal: สร้างใหม่ --}}
<div id="createModal"
     class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-opacity">

    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all animate-[scaleIn_.2s_ease-out]">

        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-slate-100 text-[#FA4616] flex items-center justify-center">
                    +
                </span>
                สร้างห้องเรียนใหม่
            </h3>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition">
                ✕
            </button>
        </div>

        <form action="/teacher/classroom" method="POST">
            @csrf
            <div class="p-6 space-y-5">
                {{-- NAME --}}
                <div>
                    <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ชื่อห้องเรียน</label>
                    <input name="name" required placeholder="เช่น วิชาโปรแกรมมิ่ง 101"
                        class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm
                               text-slate-700 placeholder-slate-400
                               focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                </div>

                {{-- GRID --}}
                <div class="grid grid-cols-2 gap-4">
                    {{-- MAJOR --}}
                    <div>
                        <label class="text-sm font-semibold text-slate-600 mb-1.5 block">สาขาวิชา</label>
                        <select name="major" required
                            class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700
                                   focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                            <option value="" disabled selected>เลือกสาขา</option>
                            @foreach(['TE' => 'ครุศาสตร์เครื่องกล', 'EE' => 'ครุศาสตร์ไฟฟ้า', 'CE' => 'ครุศาสตร์โยธา', 'PE' => 'ครุศาสตร์อุตสาหการ'] as $code => $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- YEAR --}}
                    <div>
                        <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ปีการศึกษา</label>
                        <input name="academicYear" required placeholder="เช่น 2569"
                            class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700
                                   focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                    </div>
                </div>
            </div>

            {{-- ACTION --}}
            <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-800 transition">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-5 py-2.5 text-sm font-medium rounded-xl bg-[#FA4616] text-white shadow-lg shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 hover:-translate-y-0.5 transition">
                    สร้างห้องเรียน
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 🟡 Modal: แก้ไขห้องเรียน --}}
<div id="editClassroomModal"
     class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-opacity">
    
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all animate-[scaleIn_.2s_ease-out]">
        
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
            <h3 class="text-lg font-bold text-slate-700 flex items-center gap-2">
                <span class="w-8 h-8 rounded-full bg-slate-100 text-[#FA4616] flex items-center justify-center">
                    ✎
                </span>
                แก้ไขข้อมูลห้องเรียน
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition">✕</button>
        </div>

        <form id="editClassroomForm" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="p-6 space-y-5">
                <div>
                    <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ชื่อห้องเรียน</label>
                    <input name="name" id="edit_name" required 
                           class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-slate-600 mb-1.5 block">สาขาวิชา</label>
                        <select name="major" id="edit_major" required 
                                class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                            <option value="ครุศาสตร์เครื่องกล">ครุศาสตร์เครื่องกล</option>
                            <option value="ครุศาสตร์ไฟฟ้า">ครุศาสตร์ไฟฟ้า</option>
                            <option value="ครุศาสตร์โยธา">ครุศาสตร์โยธา</option>
                            <option value="ครุศาสตร์อุตสาหการ">ครุศาสตร์อุตสาหการ</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-600 mb-1.5 block">ปีการศึกษา</label>
                        <input name="academicYear" id="edit_year" required 
                               class="w-full bg-slate-50 border border-slate-200 p-3 rounded-xl text-sm text-slate-700 focus:ring-2 focus:ring-[#FA4616]/20 focus:border-[#FA4616] focus:bg-white outline-none transition">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" 
                        class="px-5 py-2.5 text-sm font-medium rounded-xl border border-slate-300 text-slate-600 bg-white hover:bg-slate-50 hover:text-slate-800 transition">
                    ยกเลิก
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 text-sm font-medium rounded-xl bg-[#FA4616] text-white shadow-lg shadow-[#FA4616]/30 hover:bg-[#D83A0D] hover:shadow-[#FA4616]/50 hover:-translate-y-0.5 transition">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* เพิ่ม Animation เด้งขึ้นมาแบบนุ่มนวลให้ Modal */
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>

<script>
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });

    // อัปเดตพารามิเตอร์ลบตัวแปร level ออก
    function openEditModal(id, name, major, year) {
        const modal = document.getElementById('editClassroomModal');
        const form = document.getElementById('editClassroomForm');
        
        form.action = `/teacher/classroom/${id}`;
        
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_major').value = major;
        document.getElementById('edit_year').value = year;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editClassroomModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
</script>
</x-layouts.app>