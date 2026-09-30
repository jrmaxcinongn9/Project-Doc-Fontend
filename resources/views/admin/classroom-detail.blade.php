<x-layouts.app title="รายละเอียดห้องเรียน">

    {{-- เรียก Alpine.js เฉพาะกรณีที่ใน layout หลักยังไม่มี --}}
    @once
        <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @endonce

    <div class="flex min-h-screen bg-gray-50" x-data="assignmentApp()">

        {{-- SIDEBAR --}}
        <x-admin-sidebar />

        {{-- MAIN CONTENT --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            
            <main class="flex-1 overflow-y-auto p-6 pt-[calc(3.5rem+1.5rem)] lg:pt-6">
                <div class="max-w-7xl mx-auto">

                    {{-- HEADER --}}
                    <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 flex items-center gap-3">
                                {{ $classroom->name }}
                            </h1>
                            <div class="flex gap-2 mt-3">
                                {{-- เปลี่ยนป้ายสาขาเป็นสีส้ม --}}
                                <span class="px-3 py-1 bg-orange-600 text-white text-xs font-bold rounded-full shadow-sm">สาขา {{ $classroom->major ?? '-' }}</span>
                                <span class="px-3 py-1 bg-gray-200 text-gray-700 text-xs font-bold rounded-full shadow-sm">(รุ่น {{ $classroom->academicYear ?? '-' }})</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                        {{-- LEFT COLUMN: STUDENTS --}}
                        <div class="lg:col-span-2">
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 bg-white flex justify-between items-center">
                                    <h2 class="text-lg font-bold text-gray-800">👥 รายชื่อนักศึกษา</h2>
                                    <span class="text-sm font-semibold text-gray-400">{{ count($classroom->students) }} รายการ</span>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left border-collapse">
                                        <thead>
                                            <tr class="bg-gray-50 text-gray-500 text-[11px] uppercase tracking-widest font-bold">
                                                <th class="px-6 py-4">#</th>
                                                <th class="px-6 py-4">รหัสนักศึกษา</th>
                                                <th class="px-6 py-4">ชื่อ-นามสกุล</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            @forelse($classroom->students as $index => $student)
                                            {{-- เปลี่ยน Hover เป็นสีส้มอ่อน --}}
                                            <tr class="hover:bg-orange-50/50 transition duration-150">
                                                <td class="px-6 py-4 text-gray-400 text-sm">{{ $index + 1 }}</td>
                                                {{-- เปลี่ยนรหัสนักศึกษาเป็นสีส้ม --}}
                                                <td class="px-6 py-4 font-mono text-sm font-bold text-orange-600">{{ $student->student_id ?? '-' }}</td>
                                                <td class="px-6 py-4 text-sm font-medium text-gray-700">{{ $student->name ?? 'ไม่ระบุชื่อ' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">ไม่มีข้อมูลนักศึกษา</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- RIGHT COLUMN: ASSIGNMENTS --}}
                        <div class="space-y-6">
                            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                    <h2 class="text-lg font-bold text-gray-800">📌 งานที่มอบหมาย</h2>
                                    {{-- ปุ่มเพิ่มงาน (สีส้ม) --}}
                                    <button @click="openModal = true" 
                                        class="p-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-all duration-200 active:scale-95 shadow-md shadow-orange-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                                <div class="p-4 space-y-4">
                                    @forelse($classroom->assignments as $a)
                                        <div class="p-4 bg-white border border-gray-200 rounded-xl flex justify-between items-center hover:shadow-md transition">
                                            <div>
                                                <span class="font-bold text-gray-800" 
                                                      x-text="assignments['{{ $a->id }}'] || '{{ $a->assignment_name }}'">
                                                </span>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                {{-- ปุ่มดูรายละเอียด --}}
                                                <a href="/admin/assignment/{{ $a->id }}" 
                                                   class="px-3 py-1.5 bg-white text-orange-600 hover:bg-orange-50 rounded-lg border border-gray-200 hover:border-orange-200 transition shadow-sm text-xs font-semibold">
                                                    ดู
                                                </a>

                                                {{-- ปุ่มแก้ไข --}}
                                                <button type="button" 
                                                        @click="openEditModal('{{ $a->id }}', '{{ $a->assignment_name }}')"
                                                        class="px-3 py-1.5 bg-white text-gray-600 hover:bg-gray-100 rounded-lg border border-gray-200 transition shadow-sm text-xs font-semibold">
                                                    แก้ไข
                                                </button>

                                                {{-- ปุ่มลบ --}}
                                                <form action="/admin/assignment/{{ $a->id }}" method="POST" 
                                                      onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบงานนี้?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="px-3 py-1.5 bg-white text-red-600 hover:bg-red-50 rounded-lg border border-gray-200 hover:border-red-200 transition shadow-sm text-xs font-semibold">
                                                        ลบ
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="py-10 text-center text-gray-400 italic text-sm">
                                            ยังไม่มีงานที่มอบหมาย
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>

        {{-- MODAL สร้างงานใหม่ --}}
        <template x-teleport="body">
            <div x-show="openModal"
                 class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
                 x-cloak>

                {{-- BACKDROP --}}
                <div x-show="openModal"
                     x-transition
                     class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                     @click="openModal = false"></div>

                {{-- MODAL --}}
                <div x-show="openModal"
                     x-transition
                     class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden"
                     @click.stop>

                    {{-- HEADER --}}
                    <div class="px-5 py-4 border-b flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">สร้างงานใหม่</h3>
                            <p class="text-[10px] text-orange-600 mt-0.5 font-semibold uppercase tracking-wider">New Assignment</p>
                        </div>
                        <button @click="openModal = false"
                                class="text-gray-400 hover:text-red-500 text-lg transition-colors">✕</button>
                    </div>

                    {{-- FORM --}}
                    <form action="{{ route('assignment.store') }}" method="POST" class="p-5 space-y-4">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">

                        {{-- INPUT --}}
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">
                                ชื่องาน / หัวข้อ
                            </label>
                            <input type="text"
                                   name="assignment_name"
                                   required
                                   placeholder="เช่น แบบฝึกหัดบทที่ 1"
                                   class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all">
                        </div>

                        {{-- FOOTER --}}
                        <div class="flex gap-2 pt-3">
                            <button type="button"
                                @click="openModal = false"
                                class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition-colors">
                                ยกเลิก
                            </button>

                            <button type="submit"
                                class="flex-1 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg text-sm shadow-md shadow-orange-200 transition-all active:scale-95">
                                สร้างงาน
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- EDIT MODAL --}}
        <template x-teleport="body">
            <div x-show="editOpen"
                 class="fixed inset-0 z-[9999] flex items-center justify-center px-4"
                 x-cloak>

                {{-- BACKDROP --}}
                <div x-show="editOpen"
                     x-transition.opacity
                     class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                     @click="editOpen=false"></div>

                {{-- MODAL BOX --}}
                <div x-show="editOpen"
                     x-transition
                     class="relative bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden"
                     @click.stop>

                    {{-- HEADER --}}
                    <div class="px-5 py-4 border-b flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="text-base font-semibold text-gray-800">แก้ไขงาน</h3>
                            <p class="text-[10px] text-orange-600 mt-0.5 font-semibold uppercase tracking-wider">Edit Assignment</p>
                        </div>
                        <button @click="editOpen = false" class="text-gray-400 hover:text-red-500 text-lg transition-colors">✕</button>
                    </div>

                    {{-- FORM CONTENT --}}
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 mb-1 block">ชื่องาน / หัวข้อ</label>
                            <input type="text"
                                   x-model="editName"
                                   class="w-full border border-gray-300 p-2.5 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all">
                        </div>

                        {{-- FOOTER --}}
                        <div class="flex gap-2 pt-3">
                            <button type="button" @click="editOpen = false"
                                    class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-sm transition-colors">
                                ยกเลิก
                            </button>

                            <button type="button" @click="updateAssignment()"
                                    class="flex-1 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg text-sm shadow-md shadow-orange-200 transition-all active:scale-95">
                                บันทึกการแก้ไข
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </template>

    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
    function assignmentApp() {
        return {
            openModal: false,

            editOpen: false,
            editId: null,
            editName: '',

            assignments: {},

            openEditModal(id, name) {
                this.editId = id
                this.editName = name
                this.editOpen = true
            },
            
            async updateAssignment(){
                try {
                    let res = await fetch(`/admin/assignment/${this.editId}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            assignment_name: this.editName
                        })
                    })

                    if (!res.ok) {
                        let text = await res.text()
                        throw new Error(text)
                    }

                    let data = await res.json()

                    if(data.success){
                        this.assignments[this.editId] = this.editName
                        this.editOpen = false
                    } else {
                        alert(data.message)
                    }

                } catch (err) {
                    console.error(err)
                    alert('เกิดข้อผิดพลาด')
                }
            }
        }
    }
    </script>
</x-layouts.app>