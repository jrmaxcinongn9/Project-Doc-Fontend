<x-layouts.app title="All Documents">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- Sidebar → Component -->
    <x-student-sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10">

        <!-- Topbar → Component -->
        <x-student-topbar-profile title="เอกสารทั้งหมด" />

        <!-- Search + Filter -->
        <div class="flex items-center gap-4 mb-6">

            <!-- Search -->
            <div class="flex items-center bg-white px-4 py-2 rounded-lg shadow w-80 border">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-5 h-5 text-gray-500" fill="none" 
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                        d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z" />
                </svg>
                <input type="text" placeholder="ค้นหาเอกสาร..." 
                       class="ml-3 w-full outline-none text-sm">
            </div>

            <!-- Filter -->
            <button 
                class="flex items-center gap-2 px-4 py-2 bg-white border rounded-lg shadow hover:bg-gray-100">
                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-5 h-5 text-gray-600" fill="none" 
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                        d="M3 6h18M6 12h12M10 18h4" />
                </svg>
                <span class="text-sm">ตัวกรอง</span>
            </button>

        </div>


        <!-- Documents Table -->
        <div class="bg-white p-6 rounded-xl shadow">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-500 text-sm bg-gray-50">
                        <th class="p-3">ชื่อเอกสาร</th>
                        <th class="p-3">ประเภท</th>
                        <th class="p-3">สถานะ</th>
                        <th class="p-3">วันที่ส่ง</th>
                        <th class="p-3 text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody>

                    <tr class="border-b">
                        <td class="p-3">Week 1 Report</td>
                        <td class="p-3">PDF</td>
                        <td class="p-3 text-green-600 font-medium">ส่งเเล้ว</td>
                        <td class="p-3">2025-11-20</td>
                        <td class="p-3 text-right">
                            <a class="text-blue-600 hover:underline cursor-pointer">ดู</a>
                        </td>
                    </tr>

                    <tr class="border-b">
                        <td class="p-3">Week 2 Report</td>
                        <td class="p-3">DOCX</td>
                        <td class="p-3 text-yellow-600 font-medium">รอตรวจ</td>
                        <td class="p-3">2025-11-27</td>
                        <td class="p-3 text-right">
                            <a class="text-blue-600 hover:underline cursor-pointer">ดู</a>
                        </td>
                    </tr>

                    <tr>
                        <td class="p-3">Week 3 Report</td>
                        <td class="p-3">PDF</td>
                        <td class="p-3 text-red-500 font-medium">ถูกส่งกลับ</td>
                        <td class="p-3">2025-12-01</td>
                        <td class="p-3 text-right">
                            <a class="text-blue-600 hover:underline cursor-pointer">ดู</a>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

    </main>

</div>

</x-layouts.app>
