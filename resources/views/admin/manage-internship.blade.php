<x-layouts.app title="Manage Internship Work">

<div class="min-h-screen flex bg-[#f5f7fa]">
    <x-admin-sidebar />

    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">
        <x-admintopbar-profile title="Assignment" />

        <div class="flex justify-between items-center mb-6">
            <div class="relative w-80 group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z" />
                    </svg>
                </div>
                <input type="text" placeholder="ค้นหางาน..." 
                       class="block w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm shadow-sm">
            </div>

            <a href="/admin/assignments/create"
               class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 text-sm shadow-lg shadow-blue-500/20 transition-all">
                + สร้างงานใหม่
            </a>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 text-gray-400 text-xs uppercase tracking-wider bg-gray-50/50">
                        <th class="p-4 font-bold">ชื่องาน</th>
                        <th class="p-4 font-bold">กำหนดส่ง</th>
                        <th class="p-4 font-bold text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-50">
                {{-- เปลี่ยนจาก $works mock เป็น $assignments จาก Controller --}}
                @forelse($assignments as $doc)
                    @php
                        // ดึง ID (เผื่อ API ใช้ _id)
                        $id = $doc['_id'] ?? $doc['id'];
                        $deadline = \Carbon\Carbon::parse($doc['due_date']);
                        $isOverdue = \Carbon\Carbon::now()->greaterThan($deadline);
                    @endphp

                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="p-4">
                            <div class="text-sm font-bold text-gray-700">{{ $doc['title'] }}</div>
                            <div class="text-xs text-gray-400 truncate w-64">{{ $doc['description'] ?? 'ไม่มีรายละเอียด' }}</div>
                        </td>

                        <td class="p-4">
                            <span class="text-sm {{ $isOverdue ? 'text-red-500 font-bold' : 'text-emerald-600 font-medium' }}">
                                {{ $deadline->format('d/m/Y') }}
                                @if($isOverdue) <span class="text-[10px] ml-1">(เลยกำหนด)</span> @endif
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            <div class="flex justify-end items-center gap-3">
                                <a href="/admin/assignments/{{ $id }}" class="text-blue-600 text-sm font-bold hover:text-blue-800 transition-colors">ดู</a>
                                
                                <a href="/admin/assignments/{{ $id }}/edit" class="text-amber-500 text-sm font-bold hover:text-amber-700 transition-colors">แก้ไข</a>

                                <form action="/admin/assignments/{{ $id }}" method="POST" onsubmit="return confirm('ยืนยันการลบงานนี้หรือไม่?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 text-sm font-bold hover:text-red-700 transition-colors">
                                        ลบ
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-10 text-center text-gray-400 text-sm">ยังไม่มีการมอบหมายงาน</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>

</x-layouts.app>