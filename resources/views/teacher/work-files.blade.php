<x-layouts.app title="Work Files">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- SIDEBAR -->
    <x-teacher-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-10">

        <!-- TOPBAR -->
        <x-topbar-profile title="ไฟล์เอกสาร" />

        @php
            /** mock ตัวงาน */
            $work = $work ?? [
                'title'  => 'Week 1 - รายงานประจำสัปดาห์',
                'detail' => 'เปลี่ยนมอเตอร์ Yaskawa ที่ EDP Line และตรวจ PM Press 1000T BP28'
            ];

            /** mock files */
            $files = [
                [
                    'student' => 'Somchai',
                    'file'    => 'week1-report.pdf',
                    'date'    => '2025-12-02'
                ],
                [
                    'student' => 'Somchai',
                    'file'    => 'week1-photo.jpg',
                    'date'    => '2025-12-02'
                ],
            ];
        @endphp


        <!-- HEADER -->
        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-gray-700">
                {{ $work['title'] }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                รายการไฟล์ที่นักศึกษาส่งในงานนี้
            </p>
        </div>


        <!-- DETAIL -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg mb-6">
            <p class="text-sm text-gray-600">
                <span class="font-semibold text-gray-700">
                    รายละเอียดงาน:
                </span>
                {{ $work['detail'] }}
            </p>
        </div>


        <!-- TABLE -->
        <div class="bg-white p-6 rounded-xl shadow">

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-500 text-sm bg-gray-50">
                        <th class="p-3">นักศึกษา</th>
                        <th class="p-3">ไฟล์</th>
                        <th class="p-3">วันที่ส่ง</th>
                        <th class="p-3 text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($files as $file)

                    <tr class="border-b">
                        <td class="p-3">
                            {{ $file['student'] }}
                        </td>

                        <td class="p-3 text-sm flex items-center gap-2">

    <svg xmlns="http://www.w3.org/2000/svg" 
         class="w-5 h-5 text-gray-500" fill="none" 
         viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
              stroke-width="1.8"
              d="M8 16h8M8 12h8m-6 8H6a2 2 0 01-2-2V6a2 2 0 012-2h8l6 6v10a2 2 0 01-2 2h-6z" />
    </svg>

    {{ $file['file'] }}

</td>


                        <td class="p-3 text-sm text-gray-600">
                            {{ $file['date'] }}
                        </td>

                        <td class="p-3 text-right">
                            <a href="#" class="text-blue-600 text-sm hover:underline">
                                ดาวน์โหลด
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
