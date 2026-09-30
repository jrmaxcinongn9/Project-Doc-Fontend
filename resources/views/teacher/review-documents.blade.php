<x-layouts.app title="Review Documents">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- Sidebar -->
    <x-teacher-sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <!-- Topbar -->
        <x-topbar-profile title="ตรวจเอกสารนักศึกษา" />

        <!-- Search Bar -->
        <div class="flex justify-between items-center mb-6">

            <div class="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm w-80 border border-gray-200
                        focus-within:ring-2 focus-within:ring-blue-200 transition">
                <svg xmlns="http://www.w3.org/2000/svg"
                     class="w-5 h-5 text-gray-500" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                           d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z" />
                </svg>

                <input type="text" placeholder="ค้นหาเอกสาร..."
                       class="ml-3 w-full outline-none text-sm text-gray-700 placeholder:text-gray-400 bg-transparent">
            </div>

            <a href="/teacher/create"
               class="px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700
                      shadow-sm hover:shadow-md transition text-sm">
                + สร้างงาน
            </a>
        </div>


        @php
            $today = \Carbon\Carbon::today();

            $works = [
                ['title' => 'Week 1 - รายงานประจำสัปดาห์', 'deadline' => '2025-11-10'],
                ['title' => 'Week 2 - รายงานประจำสัปดาห์', 'deadline' => '2025-11-30'],
                ['title' => 'Week 3 - รายงานประจำสัปดาห์', 'deadline' => '2025-12-30'],
            ];
        @endphp


        <!-- TABLE -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50">
                    <tr class="border-b text-gray-500 text-sm">
                        <th class="p-4 font-medium">ชื่องาน</th>
                        <th class="p-4 font-medium">กำหนดส่ง</th>
                        <th class="p-4 font-medium text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">

                @foreach($works as $work)

                    @php
                        $deadline = \Carbon\Carbon::parse($work['deadline']);

                        $color = $today->lessThan($deadline)
                            ? 'text-green-700 font-medium'
                            : 'text-red-600 font-medium';
                    @endphp

                    <tr class="hover:bg-gray-50 transition">

                        <!-- ชื่องาน -->
                        <td class="p-4">
                            <div class="text-sm font-semibold text-gray-900">
                                {{ $work['title'] }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                เอกสารรายสัปดาห์
                            </div>
                        </td>

                        <!-- กำหนดส่ง -->
                        <td class="p-4 text-sm {{ $color }}">
                            {{ $deadline->format('d/m/Y') }}
                        </td>

                        <!-- Action -->
                        <td class="p-4 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="/teacher/assignment-view"
                                   class="px-3 py-1.5 rounded-lg bg-white border border-gray-300
                                          text-gray-700 hover:bg-gray-50 transition text-sm">
                                    ดู
                                </a>

                                <a href="/teacher/edit"
                                   class="px-3 py-1.5 rounded-lg bg-yellow-500 text-white
                                          hover:bg-yellow-600 transition text-sm">
                                    แก้ไข
                                </a>
                            </div>
                        </td>

                    </tr>

                @endforeach

                </tbody>
            </table>
        </div>

    </main>

</div>

</x-layouts.app>
