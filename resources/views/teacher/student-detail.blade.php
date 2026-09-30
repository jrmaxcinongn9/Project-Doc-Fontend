<x-layouts.app title="Student Detail">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- SIDEBAR -->
    <x-teacher-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <!-- Topbar -->
        <x-topbar-profile title="ข้อมูลนักศึกษา" />

        <!-- STUDENT INFO -->
        @php
            // ใช้ mock data ถ้าไม่มีข้อมูลมาจาก Controller
            $student = $student ?? [
                'name'    => 'ทดสอบ นักศึกษา',
                'email'   => 'student@example.com',
                'count'   => 3,
                'pending' => 2
            ];
        @endphp


        <!-- PROFILE SECTION -->
        <div class="bg-white p-6 rounded-xl shadow mb-8">

            <h2 class="text-xl font-semibold text-gray-700 mb-3">
                {{ $student['name'] }}
            </h2>

            <p class="text-sm text-gray-600 mb-6">
                อีเมล: {{ $student['email'] }}
            </p>

            <!-- สถานะเอกสาร -->
            <div class="flex gap-6">

                <div class="flex-1 bg-gray-50 border rounded-xl p-6 flex flex-col items-center shadow-sm">
                    <p class="text-gray-500 text-sm mb-1">งานที่ส่งแล้ว</p>
                    <p class="text-4xl font-bold text-green-600">
                        {{ $student['count'] }}
                    </p>
                </div>

                <div class="flex-1 bg-gray-50 border rounded-xl p-6 flex flex-col items-center shadow-sm">
                    <p class="text-gray-500 text-sm mb-1">งานที่ค้างส่ง</p>
                    <p class="text-4xl font-bold text-red-600">
                        {{ $student['pending'] }}
                    </p>
                </div>

            </div>

        </div>


        <!-- WORK TABLE -->
        <div class="bg-white p-6 rounded-xl shadow">

            <h3 class="text-lg font-semibold text-gray-700 mb-4">
                เอกสารที่นักศึกษาส่ง
            </h3>

            @php
                $works = $works ?? [
                    ['title'=>'Week 1 รายงาน','date'=>'2025-12-02'],
                    ['title'=>'Week 2 รายงาน','date'=>'2025-12-05'],
                    ['title'=>'Week 3 รายงาน','date'=>'2025-12-06'],
                ];
            @endphp

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-500 text-sm bg-gray-50">
                        <th class="p-3">ชื่องาน</th>
                        <th class="p-3">วันที่ส่ง</th>
                        <th class="p-3 text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($works as $work)
                    <tr class="border-b">
                        <td class="p-3">{{ $work['title'] }}</td>
                        <td class="p-3 text-sm text-gray-600">{{ $work['date'] }}</td>
                        <td class="p-3 text-right">
                            <a href="/teacher/work"
                               class="text-blue-600 text-sm hover:underline">
                                ดู
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
