<x-layouts.app title="จัดการห้องเรียน">
    <div class="min-h-screen flex bg-slate-50">
        <x-admin-sidebar />

        <main class="flex-1 overflow-y-auto pt-14 lg:pt-0">
            <div class="p-6 sm:p-10 max-w-7xl mx-auto space-y-6">
                
                {{-- Topbar --}}
                <x-admintopbar-profile title="จัดการห้องเรียน" />

                {{-- Alert Messages --}}
                @if(session('success'))
                    <div class="p-4 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-xl font-medium text-sm shadow-sm flex justify-between items-center animate-[fadeIn_.3s_ease]">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            {{ session('success') }}
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 transition">✕</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-100 text-red-700 rounded-xl font-medium text-sm shadow-sm animate-[fadeIn_.3s_ease]">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                            <div>
                                <p class="font-bold text-red-800">พบข้อผิดพลาด:</p>
                                <ul class="list-disc list-inside mt-1 text-red-600">
                                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

               {{-- Toolbar (Add Button Only) --}}
                <div class="flex justify-end">
                    <button onclick="openCreateModal()"
                        class="w-full sm:w-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-semibold shadow-sm shadow-orange-500/20 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        สร้างห้องเรียน
                    </button>
                </div>

                {{-- Table Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap">
                            {{-- HEADER --}}
                            <thead class="bg-slate-50/80 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ห้องเรียน</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">สาขา</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">ปีการศึกษา</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">นักศึกษา</th>
                                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">การจัดการ</th>
                                </tr>
                            </thead>

                            {{-- BODY --}}
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @forelse($classrooms as $class)
                                <tr class="hover:bg-slate-50/80 transition-colors duration-200 group">
                                    {{-- ห้องเรียน --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm border border-blue-100">
                                                {{ substr($class->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ $class->name }}</div>
                                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">ID: #{{ $class->id }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- สาขา --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-semibold border border-indigo-100/50">
                                            {{ $class->major }}
                                        </span>
                                    </td>

                                    {{-- ปีการศึกษา --}}
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-slate-700 font-semibold">{{ $class->academicYear }}</div>
                                    </td>

                                    {{-- นักศึกษา --}}
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 {{ $class->studentCount > 0 ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-slate-100 text-slate-600 border-slate-200' }} rounded-full text-xs font-semibold border">
                                            {{ $class->studentCount }} คน
                                        </span>
                                    </td>

                                    {{-- ACTION --}}
                                  <td class="px-6 py-4">
    <div class="flex justify-end items-center gap-2">

        {{-- 🔍 ดูรายละเอียด --}}
        <a href="{{ route('classroom.show', $class->id) }}" 
           class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 transition-all text-xs font-semibold shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
            </svg>
            ดู
        </a>

        {{-- ✏️ แก้ไข --}}
        <button data-class='@json($class)' onclick="openEditModal(JSON.parse(this.dataset.class))"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-amber-500 hover:border-amber-300 hover:bg-amber-50 transition-all text-xs font-semibold shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
            แก้ไข
        </button>

        {{-- 🗑️ ลบ --}}
        <form action="{{ route('classroom.destroy', $class->id) }}" method="POST" 
              onsubmit="return confirm('⚠️ ยืนยันการลบห้องเรียนนี้?\nข้อมูลนักศึกษาในห้องอาจได้รับผลกระทบ')" 
              class="inline-block">
            @csrf 
            @method('DELETE')
            <button type="submit" 
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-red-600 hover:border-red-300 hover:bg-red-50 transition-all text-xs font-semibold shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                ลบ
            </button>
        </form>

    </div>
</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-24 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <svg class="w-12 h-12 mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            <p class="text-base font-medium text-slate-500">ยังไม่มีข้อมูลห้องเรียน</p>
                                            <p class="text-sm mt-1">คลิกปุ่ม "สร้างห้องเรียน" เพื่อเริ่มต้นเพิ่มข้อมูล</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </main>
    </div>

    {{-- Shared Form Variables --}}
    @php
        $majors = [
            'ครุศาสตร์เครื่องกล',
            'ครุศาสตร์ไฟฟ้า',
            'ครุศาสตร์โยธา',
            'ครุศาสตร์อุตสาหการ',
        ];
    @endphp

    {{-- Modal: สร้างใหม่ --}}
    <div id="createModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-all">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-[fadeIn_.2s_ease-out]">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h3 class="text-lg font-bold text-slate-800">สร้างห้องเรียนใหม่</h3>
                <button onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 transition bg-slate-50 hover:bg-slate-100 p-1.5 rounded-lg">✕</button>
            </div>

            <form action="{{ route('classroom.store') }}" method="POST" class="p-6 space-y-5">
                @csrf
                
                {{-- ป้องกัน Error: level must not be less than 1 --}}
                <input type="hidden" name="level" value="1">

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">ชื่อห้องเรียน <span class="text-red-500">*</span></label>
                    <input name="name" required placeholder="เช่น EE64-1"
                        class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">สาขา <span class="text-red-500">*</span></label>
                    <select name="major" required class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="" disabled selected>เลือกสาขา</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}">{{ $major }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">ปีการศึกษา <span class="text-red-500">*</span></label>
                    <input type="text" name="academicYear" value="{{ date('Y') + 543 }}" required
                        class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeCreateModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm shadow-sm shadow-orange-500/20 transition-colors">
                        สร้างห้องเรียน
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: แก้ไข --}}
    <div id="editModal" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex justify-center items-center px-4 transition-all">
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-[fadeIn_.2s_ease-out]">
            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-white">
                <h3 class="text-lg font-bold text-slate-800">แก้ไขห้องเรียน</h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition bg-slate-50 hover:bg-slate-100 p-1.5 rounded-lg">✕</button>
            </div>

            <form id="editForm" method="POST" class="p-6 space-y-5">
                @csrf
                @method('PATCH')
                
                {{-- ป้องกัน Error: level must not be less than 1 --}}
                <input type="hidden" name="level" id="edit_hidden_level" value="1">
                
                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">ชื่อห้องเรียน <span class="text-red-500">*</span></label>
                    <input name="name" id="edit_name" required
                        class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">สาขา <span class="text-red-500">*</span></label>
                    <select name="major" id="edit_major" required class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="" disabled selected>เลือกสาขา</option>
                        @foreach($majors as $major)
                            <option value="{{ $major }}">{{ $major }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700 mb-1.5 block">ปีการศึกษา <span class="text-red-500">*</span></label>
                    <input type="text" name="academicYear" id="edit_academicYear" required
                        class="w-full bg-slate-50 border border-slate-200 p-2.5 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <div class="flex gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditModal()"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-sm transition-colors">
                        ยกเลิก
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl text-sm shadow-sm shadow-orange-500/20 transition-colors">
                        บันทึกการเปลี่ยนแปลง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>

    <script>
    function openCreateModal() { 
        document.getElementById('createModal').classList.remove('hidden'); 
        document.body.style.overflow = 'hidden';
    }
    function closeCreateModal() { 
        document.getElementById('createModal').classList.add('hidden'); 
        document.body.style.overflow = 'auto';
    }

    function openEditModal(data) {
        const form = document.getElementById('editForm');
        const classroomId = data._id || data.id; 
        
        form.action = `/admin/classroom/${classroomId}`; 
        document.getElementById('edit_name').value = data.name;
        document.getElementById('edit_major').value = data.major;
        document.getElementById('edit_academicYear').value = data.academicYear;

        // นำค่า level เดิมมาใส่ (ถ้ามี) หรือกำหนดเป็น 1 เพื่อป้องกัน Error Backend
        if(document.getElementById('edit_hidden_level')) {
            document.getElementById('edit_hidden_level').value = data.level || 1;
        }

        document.getElementById('editModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeEditModal() { 
        document.getElementById('editModal').classList.add('hidden'); 
        document.body.style.overflow = 'auto';
    }

    // ฟังก์ชันค้นหาข้อมูลในตาราง
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            let val = this.value.toLowerCase().trim();
            document.querySelectorAll('tbody tr:not(.empty-row)').forEach(row => {
                if(row.innerText.toLowerCase().includes(val)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // ปิด Modal เมื่อคลิกพื้นที่ว่าง
    window.onclick = function(e) {
        if (e.target.id === 'createModal') closeCreateModal();
        if (e.target.id === 'editModal') closeEditModal();
    }
    </script>
</x-layouts.app>