<x-layouts.app title="Students">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- SIDEBAR -->
    <x-teacher-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <!-- TOPBAR -->
        <x-topbar-profile title="รายชื่อนักศึกษา" />

        @php
            $students = [
                [
                    'student_id' => '65309010101',
                    'name'      => 'Somchai',
                    'major'     => 'ไฟฟ้ากำลัง',
                    'email'     => 'somchai@demo.com',
                    'count'     => 10,
                    'pending'   => 0,
                ],
                [
                    'student_id' => '65309010102',
                    'name'      => 'Anucha',
                    'major'     => 'ไฟฟ้ากำลัง',
                    'email'     => 'anucha@demo.com',
                    'count'     => 8,
                    'pending'   => 2,
                ],
                [
                    'student_id' => '65309010103',
                    'name'      => 'Pranee',
                    'major'     => 'เครื่องกล',
                    'email'     => 'pranee@demo.com',
                    'count'     => 0,
                    'pending'   => 10,
                ],
            ];
        @endphp


        <!-- SEARCH & FILTER -->
        <div class="flex items-center gap-4 mb-6">

            <div class="flex items-center bg-white px-4 py-2 rounded-lg shadow w-80 border">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-5 h-5 text-gray-500" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                           d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z" />
                </svg>
                <input type="text" placeholder="ค้นหานักศึกษา..." 
                       class="ml-3 w-full outline-none text-sm">
            </div>

            <select class="px-4 py-2 bg-white border rounded-lg shadow text-sm">
                <option value="">ทุกสาขา</option>
                <option value="ไฟฟ้ากำลัง">ไฟฟ้ากำลัง</option>
                <option value="เครื่องกล">เครื่องกล</option>
                <option value="ช่างยนต์">ช่างยนต์</option>
                <option value="แมคคาทรอนิกส์">แมคคาทรอนิกส์</option>
            </select>

        </div>


        <!-- TABLE -->
        <div class="bg-white p-6 rounded-xl shadow">

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-500 text-sm bg-gray-50">
                        <th class="p-3">รหัสนักศึกษา</th>
                        <th class="p-3">ชื่อนักศึกษา</th>
                        <th class="p-3">สาขา</th>
                        <th class="p-3">อีเมล</th>
                        <th class="p-3">จำนวนงานที่ส่ง</th>
                        <th class="p-3">งานที่ค้างส่ง</th>
                        <th class="p-3 text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($students as $s)

                    @php
                        if (($s['pending'] ?? 0) == 0) {
                            $pendingColor = "bg-green-100 text-green-600";
                        } elseif (($s['pending'] ?? 0) <= 3) {
                            $pendingColor = "bg-yellow-100 text-yellow-600";
                        } else {
                            $pendingColor = "bg-red-100 text-red-600";
                        }
                    @endphp

                    <tr class="border-b">

                        <td class="p-3 font-medium text-gray-700">
                            {{ $s['student_id'] }}
                        </td>

                        <td class="p-3">
                            {{ $s['name'] }}
                        </td>

                        <td class="p-3 text-sm text-gray-600">
                            {{ $s['major'] }}
                        </td>

                        <td class="p-3">
                            {{ $s['email'] }}
                        </td>

                        <td class="p-3">
                            {{ $s['count'] }}
                        </td>

                        <td class="p-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $pendingColor }}">
                                {{ $s['pending'] }} งาน
                            </span>
                        </td>

                        <td class="p-3 text-right">
                            <a href="/teacher/detail"
                               class="text-blue-600 text-sm hover:underline">
                                ดูรายละเอียด
                            </a>
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>

        </div>

    </main>

</div>

</x-layouts.app>
