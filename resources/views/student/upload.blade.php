<x-layouts.app title="Upload Document">

<div class="min-h-screen flex bg-[#f5f7fa]">

   <!-- Sidebar -->
    <aside class="w-64 bg-white border-r shadow-sm">
        <div class="p-6 space-y-6">

            <h1 class="text-xl font-semibold text-gray-700 mb-4">Student Panel</h1>

            <ul class="space-y-4 text-gray-700">

                <!-- Home -->
                <li>
                    <a href="/student/dashboard" 
                       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-5 h-5 text-gray-600" fill="none" 
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                        </svg>
                        <span class="text-[15px]">หน้าหลัก</span>
                    </a>
                </li>

                <!-- All Documents -->
                <li>
                    <a href="/student/documents" 
                       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-5 h-5 text-gray-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M7.5 3h6l4.5 4.5V21H7.5V3z" />
                        </svg>
                        <span class="text-[15px]">เอกสารทั้งหมด</span>
                    </a>
                </li>

                <!-- Internship Docs -->
                <li>
                    <a href="/student/internship" 
                       class="flex items-center gap-3 p-2 rounded-lg bg-blue-50 text-blue-600 font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-5 h-5 text-blue-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                   d="M3 7.5h6l1.5 1.5h10.5v12H3V7.5z" />
                        </svg>
                        <span class="text-[15px]">เอกสารฝึกงาน</span>
                    </a>
                </li>

                <!-- Logout -->
                <li>
                    <a href="/logout"
                       class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="w-5 h-5 text-gray-600" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                   d="M15.75 9l3 3-3 3m3-3H9.75m6-6V5.25A2.25 2.25 0 0013.5 3H6A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15" />
                        </svg>
                        <span class="text-[15px]">ออกจากระบบ</span>
                    </a>
                </li>

            </ul>
        </div>
    </aside>


    <!-- Main -->
    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <!-- Topbar -->
        <div class="flex justify-between items-center mb-10">
            <h1 class="text-xl font-semibold text-gray-700">ส่งงานฝึกงาน</h1>

            <div class="flex items-center gap-3 cursor-pointer hover:opacity-80">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-700">Student User</p>
                    <p class="text-xs text-gray-500">โปรไฟล์</p>
                </div>

                <svg xmlns="http://www.w3.org/2000/svg" 
                     class="w-10 h-10 p-2 bg-gray-200 rounded-full text-gray-600"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                           d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75zM6 20.25v-.75A6.75 6.75 0 0112.75 12h.5A6.75 6.75 0 0120 19.5v.75H6z" />
                </svg>
            </div>
        </div>


        <!-- Upload Box -->
        <div class="bg-white p-8 rounded-xl shadow max-w-2xl">

            @php 
                $week = request('week'); 
            @endphp

            <!-- Show selected week -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 font-medium">กำลังส่งงาน</p>
                <p class="text-lg font-semibold text-gray-800">
                    Week {{ $week }} - รายงานประจำสัปดาห์
                </p>
            </div>

            <form>

                <!-- Upload File -->
                <label class="block text-sm text-gray-700 font-medium mb-2">แนบไฟล์</label>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center mb-6 relative">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                        class="w-12 h-12 mx-auto text-gray-400 mb-3" 
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M3 7v10a2 2 0 002 2h6m4-12h4a2 2 0 012 2v4m-6 8H5a2 2 0 01-2-2V7a2 2 0 012-2h7m4 16v-6m0 0l3 3m-3-3l-3 3" />
                    </svg>

                    <p class="text-gray-600 text-sm">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>

                    <input type="file" 
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                </div>

                <!-- Note -->
                <label class="block text-sm text-gray-700 font-medium mb-2">รายละเอียดเพิ่มเติม</label>
                <textarea rows="4"
                    class="w-full p-3 border rounded-lg mb-6 outline-blue-400"
                    placeholder="เขียนบันทึกหรือคำอธิบายเพิ่มเติม..."></textarea>


                <!-- Submit -->
                <button class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    ส่งงาน
                </button>

            </form>

        </div>

    </main>

</div>

</x-layouts.app>
