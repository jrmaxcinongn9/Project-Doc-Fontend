<x-layouts.app title="Documents">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <x-admin-sidebar />

    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <x-topbar-profile title="📄 {{ $assignmentName }}" />

        @php
            $totalSubmitted = count($documents);
            $totalAll = $totalStudents ?? $totalSubmitted;
            $notSubmitted = $totalAll - $totalSubmitted;
        @endphp

        <div class="flex items-center gap-4 mb-6">
            <div class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-sm shadow">
                ทั้งหมด: {{ $totalAll }}
            </div>
            <div class="px-4 py-2 bg-green-50 text-green-700 rounded-full text-sm shadow">
                ส่งแล้ว: {{ $totalSubmitted }}
            </div>
            <div class="px-4 py-2 bg-red-50 text-red-600 rounded-full text-sm shadow">
                ค้างส่ง: {{ $notSubmitted > 0 ? $notSubmitted : 0 }}
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-sm border-b">
                        <th class="p-4">ไฟล์</th>
                        <th class="p-4">ผู้ส่ง</th>
                        <th class="p-4">Assignment</th>
                        <th class="p-4">วันที่</th>
                        <th class="p-4 text-center">สถานะ</th>
                        <th class="p-4 text-right">จัดการ</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($documents as $doc)
                    <tr class="border-b hover:bg-gray-50 transition">

                        <td class="p-4">
                            <div class="font-medium text-gray-800 text-sm">
                                {{ $doc->name ?? $doc->original_name }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ isset($doc->size) ? number_format($doc->size / 1024, 2) . ' KB' : 'N/A' }}
                            </div>
                        </td>

                        <td class="p-4 text-sm">
                            <div class="font-medium text-gray-800">{{ $doc->user ?? 'ไม่ระบุชื่อ' }}</div>
                            <div class="text-gray-400 text-xs">{{ $doc->email ?? $doc->student_id }}</div>
                        </td>

                        <td class="p-4 text-sm text-blue-600">
                            {{ $doc->assignment_name ?? $assignmentName }}
                        </td>

                        <td class="p-4 text-xs text-gray-500">
                            {{ isset($doc->date) ? \Carbon\Carbon::parse($doc->date)->format('d M Y H:i') : '-' }}
                        </td>

                        <td class="p-4 text-center">
                            <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-600">
                                ส่งแล้ว
                            </span>
                        </td>

                        <td class="p-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                {{-- ปุ่มดาวน์โหลด --}}
                                @if(isset($doc->id))
                                    <a href="{{ route('assignments.download', $doc->id) }}" 
                                       class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition flex items-center gap-1 text-xs"
                                       title="ดาวน์โหลดไฟล์">
                                        📥 โหลด
                                    </a>
                                @endif

                    
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 text-gray-400 italic">
                            ยังไม่มีการส่งเอกสารในงานนี้
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>




</x-layouts.app>