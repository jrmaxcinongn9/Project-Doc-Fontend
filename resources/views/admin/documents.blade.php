<x-layouts.app title="Document History">

<div class="min-h-screen flex bg-gradient-to-br from-slate-50 via-gray-50 to-orange-50">

    <x-admin-sidebar />

    <main class="flex-1 p-6 md:p-10">

        <x-admintopbar-profile title="ไฟล์งานทั้งหมดในระบบ" />

        <div class="mb-8">
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-3">
                <div class="w-2 h-8 bg-orange-500 rounded-full"></div>
                Document ทั้งหมด
            </h1>
            <p class="text-slate-500 text-sm mt-1 ml-5">จัดการและประวัติการส่งไฟล์งานในระบบ</p>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mb-8 gap-4">
            
            <div class="relative w-full sm:max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchInput" placeholder="ค้นหาชื่อไฟล์, ผู้ส่ง, หรือ Assignment..." 
                    class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 rounded-xl leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all shadow-sm sm:text-sm">
            </div>

            <button onclick="openUploadModal()"
                class="w-full sm:w-auto px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-xl shadow-md shadow-orange-200 transition-all duration-200 active:scale-95 flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                อัปโหลดไฟล์
            </button>
        </div>        

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 flex items-center gap-3 animate-fadeIn">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 flex items-center gap-3 animate-fadeIn">
                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                <span class="font-medium">{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="bg-white/90 backdrop-blur-xl rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ไฟล์</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ผู้ส่ง</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Assignment</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">ขนาด</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">วันที่</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">จัดการ</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-50">
                    @forelse($documents as $doc)
                        @php
                            $ext = pathinfo($doc->name, PATHINFO_EXTENSION);
                            $icon = match(strtolower($ext)) {
                                'pdf' => '📕',
                                'doc','docx' => '📘',
                                'xls','xlsx' => '📗',
                                'png','jpg','jpeg' => '🖼️',
                                default => '📄'
                            };
                        @endphp

                        <tr class="doc-row hover:bg-orange-50/50 transition duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 flex items-center justify-center bg-orange-100 text-orange-600 rounded-2xl text-xl shadow-sm border border-orange-50">
                                        {{ $icon }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-slate-800">{{ $doc->name }}</p>
                                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $doc->file }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">{{ $doc->user }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $doc->email }}</p>
                            </td>

                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-slate-100 border border-slate-200 text-slate-700 rounded-full text-xs font-bold tracking-wide">
                                    {{ $doc->assignment_name ?? $doc->assignment_id ?? 'ไม่ระบุ' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-slate-500">
                                {{ number_format($doc->size / 1024, 1) }} KB
                            </td>

                            <td class="px-6 py-4 text-sm font-medium text-slate-500">
                                {{ $doc->date ? \Carbon\Carbon::parse($doc->date)->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('file.download', $doc->id) }}"
                                       class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-gray-200 text-slate-600 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-200 transition shadow-sm flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        โหลด
                                    </a>

                                    <form action="/document/{{ $doc->id }}" method="POST"
                                          onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบไฟล์นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-white border border-gray-200 text-red-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition shadow-sm">
                                            ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 mb-4 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 text-3xl">📁</div>
                                    <p class="text-slate-500 font-medium">ยังไม่มีไฟล์ในระบบ</p>
                                    <p class="text-slate-400 text-sm mt-1">อัปโหลดไฟล์แรกของคุณเพื่อเริ่มต้น</p>
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

<div id="uploadModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center z-50 px-4 transition-opacity duration-300 opacity-0">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl transform transition-all scale-95 opacity-0 duration-300" id="uploadModalContent">
        
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-slate-50 rounded-t-2xl">
            <div>
                <h2 class="text-lg font-bold text-slate-800">ส่งงาน (Admin Test)</h2>
                <p class="text-xs text-slate-500 mt-0.5">อัปโหลดเอกสารเข้าสู่ระบบ</p>
            </div>
            <button onclick="closeUploadModal()" class="text-gray-400 hover:text-red-500 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form action="/admin/upload" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">เลือกไฟล์ <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="file" name="file" required
                        class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-600 hover:file:bg-orange-100 border border-gray-200 rounded-xl transition-all cursor-pointer">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Assignment ID</label>
                <input type="text" name="assignment_id"
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all placeholder-gray-400"
                    placeholder="ใส่ ID สำหรับเทส (ถ้ามี)">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeUploadModal()"
                    class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition-colors text-sm">
                    ยกเลิก
                </button>
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-orange-600 text-white font-semibold hover:bg-orange-700 transition-all shadow-md active:scale-95 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    อัปโหลด
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            let keyword = this.value.toLowerCase();
            document.querySelectorAll('.doc-row').forEach(row => {
                let text = row.innerText.toLowerCase();
                row.style.display = text.includes(keyword) ? '' : 'none';
            });
        });
    }

    // Modal UI Control (with soft transitions)
    const modal = document.getElementById('uploadModal');
    const modalContent = document.getElementById('uploadModalContent');

    function openUploadModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Trigger reflow
        void modal.offsetWidth;
        modal.classList.remove('opacity-0');
        modalContent.classList.remove('opacity-0', 'scale-95');
        modalContent.classList.add('opacity-100', 'scale-100');
    }

    function closeUploadModal() {
        modal.classList.add('opacity-0');
        modalContent.classList.remove('opacity-100', 'scale-100');
        modalContent.classList.add('opacity-0', 'scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300); // Wait for transition
    }

    // Close on clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeUploadModal();
    });
</script>

</x-layouts.app>