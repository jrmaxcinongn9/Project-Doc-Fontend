<x-layouts.app title="จัดการผู้ใช้">

<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 overflow-y-auto">
        <div class="p-8 md:p-10 max-w-7xl mx-auto w-full">
            
            <div class="flex justify-between items-end mb-8 pb-6 border-b border-slate-200/70">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">User Management</h2>
                    <p class="text-slate-500 mt-1 text-sm font-medium">จัดการข้อมูลผู้ใช้งานทั้งหมดในระบบ</p>
                </div>
                <div class="mb-1">
                    <x-admintopbar-profile />
                </div>
            </div>

            {{-- 🟢 SUCCESS TOAST --}}
            @if(session('success'))
            <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl shadow-sm animate-fade-in-down">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-sm">{{ session('success') }}</span>
            </div>
            @endif

            {{-- 🔴 ERROR TOAST --}}
            @if($errors->has('delete'))
            <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl shadow-sm animate-fade-in-down">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium text-sm">{{ $errors->first('delete') }}</span>
            </div>
            @endif

            {{-- 🔍 TOOLBAR (SEARCH + ADD) --}}
            <div class="flex justify-between items-center mb-6">
                <div class="text-sm font-medium text-slate-400">
                    จำนวนผู้ใช้ทั้งหมด <span class="text-slate-700 font-bold">{{ count($users) }}</span> บัญชี
                </div>
                
                <button onclick="openCreateModal()"
                    class="group flex items-center gap-2 px-5 py-2.5 bg-[#F26522] hover:bg-[#d8561b] text-white font-medium rounded-xl transition-all duration-300 shadow-[0_4px_12px_rgba(242,101,34,0.25)] hover:shadow-[0_6px_16px_rgba(242,101,34,0.35)] hover:-translate-y-0.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    เพิ่มผู้ใช้ใหม่
                </button>
            </div>

            {{-- 📋 TABLE --}}
            <div class="bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-slate-50/80 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ข้อมูลผู้ใช้</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">สาขาวิชา</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">บทบาท</th>
                                <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider text-right">การจัดการ</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors duration-200">
                                
                                {{-- 👤 Name Column --}}
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-lg border border-slate-200">
                                            {{ mb_substr($u['name'], 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-[15px]">{{ $u['name'] }}</div>
                                            <div class="text-xs font-medium text-slate-400 mt-0.5">{{ $u['student_id'] ?? 'ไม่มีรหัสนักศึกษา' }}</div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 📚 Major Column --}}
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-700">{{ $u['major'] ?? '-' }}</div>
                                    @if(!empty($u['academicYear']))
                                        <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[11px] font-bold">
                                            ปีการศึกษา {{ $u['academicYear'] }}
                                        </div>
                                    @endif
                                </td>

                                {{-- 🏷️ Role Column --}}
                                <td class="px-6 py-4">
                                    @if($u['role'] === 'ADMIN')
                                        <span class="px-3 py-1 rounded-full bg-purple-50 text-purple-600 border border-purple-100 text-xs font-bold tracking-wide">ADMIN</span>
                                    @elseif($u['role'] === 'TEACHER')
                                        <span class="px-3 py-1 rounded-full bg-orange-50 text-[#F26522] border border-orange-100 text-xs font-bold tracking-wide">TEACHER</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 border border-slate-200 text-xs font-bold tracking-wide">USER</span>
                                    @endif
                                </td>

                                {{-- ⚙️ Action Column --}}
                                <td class="px-6 py-4">
                                    <div class="flex justify-end items-center gap-2">
                                        
                                        {{-- ✏️ แก้ไข --}}
                                        <button data-user='@json($u)' onclick="openEditModalSafe(JSON.parse(this.dataset.user))"
                                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-amber-500 hover:border-amber-300 hover:bg-amber-50 transition-all text-xs font-semibold shadow-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                            แก้ไข
                                        </button>

                                        {{-- 🗑️ ลบ --}}
                                        @if($u['role'] !== 'ADMIN')
                                        <form action="/admin/users/{{ $u['id'] }}" method="POST"
                                              onsubmit="return confirm('⚠️ คุณแน่ใจหรือไม่ที่จะลบผู้ใช้: {{ $u['name'] }} ?\nการกระทำนี้ไม่สามารถย้อนกลับได้')"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition-all text-xs font-semibold shadow-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                ลบ
                                            </button>
                                        </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

{{-- ================= CREATE MODAL ================= --}}
<div id="createModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white w-full max-w-md rounded-[24px] shadow-2xl flex flex-col max-h-[90vh] transform scale-95 transition-transform duration-300" id="createModalContent">
        
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-[24px]">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-[#F26522]/10 text-[#F26522] flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                </div>
                เพิ่มผู้ใช้ใหม่
            </h3>
            <button onclick="closeCreateModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors">✕</button>
        </div>

        <form method="POST" action="/admin/users" class="flex flex-col h-full overflow-hidden">
            @csrf
            <div class="p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
                
                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">ชื่อ - นามสกุล <span class="text-red-500">*</span></label>
                    <input name="name" required placeholder="กรอกชื่อและนามสกุล" value="{{ old('name') }}"
                           class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] outline-none transition-all text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">รหัสนักศึกษา</label>
                        <input name="student_id" placeholder="รหัส 11 หลัก" value="{{ old('student_id') }}"
                               class="w-full border {{ $errors->has('student_id') ? 'border-red-400 focus:ring-red-100 focus:border-red-500' : 'border-slate-200 focus:ring-[#F26522]/20 focus:border-[#F26522]' }} px-4 py-2.5 rounded-xl focus:ring-2 outline-none transition-all text-sm">
                        @error('student_id') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">ปีการศึกษา</label>
                        <select name="academicYear" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] outline-none transition-all text-sm bg-white">
                            <option value="">เลือกปี</option>
                            @for ($i = 2569; $i >= 2560; $i--)
                                <option value="{{ $i }}" {{ old('academicYear') == $i ? 'selected' : '' }}>{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">สาขาวิชา</label>
                    <select id="editMajor" name="major" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm bg-white">
                        <option value="">เลือกสาขาวิชา</option>
                        <optgroup label="ภาควิชาครุศาสตร์อุตสาหกรรม">
                            <option value="ครุศาสตร์เครื่องกล">ครุศาสตร์เครื่องกล</option>
                            <option value="ครุศาสตร์ไฟฟ้า">ครุศาสตร์ไฟฟ้า</option>
                            <option value="ครุศาสตร์โยธา">ครุศาสตร์โยธา</option>
                            <option value="ครุศาสตร์อุตสาหการ">ครุศาสตร์อุตสาหการ</option>
                            <option value="บุคลากร">บุคลากร</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">สิทธิ์การใช้งาน (Role)</label>
                    <select name="role" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] outline-none transition-all text-sm bg-white font-medium text-slate-700">
                        <option value="USER">USER (นักศึกษาทั่วไป)</option>
                        <option value="TEACHER">TEACHER (อาจารย์)</option>
                    </select>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">อีเมลเข้าระบบ <span class="text-red-500">*</span></label>
                    <input name="email" type="email" required placeholder="example@kmutt.ac.th" value="{{ old('email') }}"
                           class="w-full border {{ $errors->has('email') ? 'border-red-400 focus:ring-red-100 focus:border-red-500' : 'border-slate-200 focus:ring-[#F26522]/20 focus:border-[#F26522]' }} px-4 py-2.5 rounded-xl focus:ring-2 outline-none transition-all text-sm">
                    @error('email') <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">รหัสผ่าน <span class="text-red-500">*</span></label>
                    <input name="password" type="password" required placeholder="กำหนดรหัสผ่านอย่างน้อย 8 ตัวอักษร"
                           class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] outline-none transition-all text-sm">
                </div>

            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50/50 rounded-b-[24px] flex gap-3">
                <button type="button" onclick="closeCreateModal()" class="w-1/3 px-4 py-2.5 rounded-xl font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                    ยกเลิก
                </button>
                <button type="submit" class="w-2/3 px-4 py-2.5 rounded-xl font-semibold text-white bg-[#F26522] hover:bg-[#d8561b] shadow-md shadow-[#F26522]/20 transition-colors">
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div id="editModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm flex items-center justify-center z-50 p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white w-full max-w-md rounded-[24px] shadow-2xl flex flex-col max-h-[90vh] transform scale-95 transition-transform duration-300" id="editModalContent">
        
        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-[24px]">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                </div>
                แก้ไขข้อมูลผู้ใช้
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-red-500 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center transition-colors">✕</button>
        </div>

        <form id="editForm" method="POST" class="flex flex-col h-full overflow-hidden">
            @csrf
            @method('PATCH')
            <div class="p-6 space-y-4 overflow-y-auto flex-1 custom-scrollbar">
                
                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">ชื่อ - นามสกุล</label>
                    <input id="editName" name="name" required class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">รหัสนักศึกษา</label>
                        <input id="editStudentId" name="student_id" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm">
                    </div>
                    <div>
                        <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">ปีการศึกษา</label>
                        <select id="editYear" name="academicYear" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm bg-white">
                            <option value="">เลือกปี</option>
                            @for ($i = 2569; $i >= 2560; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div>
    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">สาขาวิชา</label>
    {{-- ลบ id="editMajor" ออก --}}
    <select id="editMajor" name="major" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]/20 focus:border-[#F26522] outline-none transition-all text-sm bg-white">
        <option value="">เลือกสาขาวิชา</option>
                        <optgroup label="ภาควิชาครุศาสตร์อุตสาหกรรม">
                            <option value="ครุศาสตร์เครื่องกล">ครุศาสตร์เครื่องกล</option>
                            <option value="ครุศาสตร์ไฟฟ้า">ครุศาสตร์ไฟฟ้า</option>
                            <option value="ครุศาสตร์โยธา">ครุศาสตร์โยธา</option>
                            <option value="ครุศาสตร์อุตสาหการ">ครุศาสตร์อุตสาหการ</option>
                            <option value="บุคลากร">บุคลากร</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">สิทธิ์การใช้งาน (Role)</label>
                    <select id="editRole" name="role" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm bg-white font-medium text-slate-700">
                        <option value="USER">USER</option>
                        <option value="TEACHER">TEACHER</option>
                        <option value="ADMIN">ADMIN</option>
                    </select>
                </div>

                <div class="border-t border-slate-100 pt-4 mt-2">
                    <label class="block text-[13px] font-semibold text-slate-600 mb-1.5 ml-1">อีเมลเข้าระบบ</label>
                    <input id="editEmail" name="email" type="email" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-all text-sm bg-slate-50 text-slate-500">
                    <p class="text-[11px] text-slate-400 mt-1 ml-1">* หากต้องการเปลี่ยนรหัสผ่าน ให้ไปที่ระบบรีเซ็ตรหัส</p>
                </div>

            </div>

            <div class="p-6 border-t border-slate-100 bg-slate-50/50 rounded-b-[24px] flex gap-3">
                <button type="button" onclick="closeEditModal()" class="w-1/3 px-4 py-2.5 rounded-xl font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-colors">
                    ยกเลิก
                </button>
                <button type="submit" class="w-2/3 px-4 py-2.5 rounded-xl font-semibold text-white bg-amber-500 hover:bg-amber-600 shadow-md shadow-amber-500/20 transition-colors">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* ซ่อน Scrollbar ที่น่าเกลียดใน Modal แต่ยัง Scroll ได้ */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar:hover::-webkit-scrollbar-thumb { background: #94a3b8; }
    /* Animation สำหรับ Toast */
    @keyframes fadeInDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in-down { animation: fadeInDown 0.4s ease-out forwards; }
</style>

{{-- ================= SCRIPT ================= --}}
<script>
function toggleModal(id, contentId, show) {
    const modal = document.getElementById(id);
    const content = document.getElementById(contentId);
    
    if (show) {
        modal.classList.remove('hidden');
        // ให้เวลา display:block ทำงานนิดนึงก่อนค่อยเล่น transition opacity
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            content.classList.remove('scale-95');
        }, 10);
        document.body.style.overflow = 'hidden';
    } else {
        modal.classList.add('opacity-0');
        content.classList.add('scale-95');
        // รอให้ Animation จบก่อนค่อยซ่อน element (300ms ตรงกับ duration-300)
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }, 300);
    }
}

function openCreateModal(){ toggleModal('createModal', 'createModalContent', true); }
function closeCreateModal(){ toggleModal('createModal', 'createModalContent', false); }
function closeEditModal(){ toggleModal('editModal', 'editModalContent', false); }

// 🔥 bind element
const editName = document.getElementById('editName');
const editEmail = document.getElementById('editEmail');
const editRole = document.getElementById('editRole');
const editStudentId = document.getElementById('editStudentId');
const editYear = document.getElementById('editYear');

/* 🔥 FIX COMPLETE EDIT */
function openEditModalSafe(user) {
    console.log('USER:', user); // debug

    editName.value = user.name || '';
    editEmail.value = user.email || '';
    editRole.value = user.role || '';
    editStudentId.value = user.student_id || '';
    editYear.value = user.academicYear || '';

    const majorSelect = document.getElementById('editMajor');
    majorSelect.value = "";

    [...majorSelect.options].forEach(opt => {
        if (opt.dataset.dynamic === "true") opt.remove();
    });

    let found = false;
    for (let opt of majorSelect.options) {
        if (opt.value === user.major) {
            opt.selected = true;
            found = true;
        }
    }

    if (!found && user.major) {
        let opt = document.createElement('option');
        opt.value = user.major;
        opt.text = user.major + ' (เดิม)';
        opt.selected = true;
        opt.dataset.dynamic = "true"; 
        majorSelect.appendChild(opt);
    }

    if (!user.id) {
        alert('❌ ไม่พบ user id');
        return;
    }

    document.getElementById('editForm').action = '/admin/users/' + user.id;
    toggleModal('editModal', 'editModalContent', true);
}

// ปิด modal เมื่อคลิกพื้นหลัง
window.onclick = e => {
    if (e.target.id === 'createModal') closeCreateModal();
    if (e.target.id === 'editModal') closeEditModal();
};

@if($errors->any())
    // หน่วงเวลาเล็กน้อยให้หน้าจอโหลดเสร็จก่อนค่อยเด้ง Modal
    setTimeout(() => openCreateModal(), 100);
@endif
</script>
</x-layouts.app>