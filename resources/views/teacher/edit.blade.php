<x-layouts.app title="Edit Assignment">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- Sidebar -->
    <x-teacher-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-10">

        <!-- Topbar -->
        <x-topbar-profile title="แก้ไขงาน" />

        <!-- Card -->
        <div class="bg-white p-8 rounded-xl shadow max-w-2xl">

            <h2 class="text-xl font-semibold mb-6 text-gray-700">
                แก้ไขงาน — Week 1 รายงานประจำสัปดาห์
            </h2>

            {{-- ฟอร์ม --}}
            <form action="/teacher/review/update" method="POST" class="space-y-6">
                @csrf

                <!-- ชื่องาน -->
                <div>
                    <label class="text-sm text-gray-600">ชื่องาน</label>
                    <input type="text" 
                           name="title"
                           value="Week 1 - รายงานประจำสัปดาห์"
                           class="w-full border rounded-lg px-3 py-2 text-sm mt-1 focus:ring focus:ring-blue-200">
                </div>

                <!-- กำหนดส่ง -->
                <div>
                    <label class="text-sm text-gray-600">กำหนดส่ง</label>
                    <input type="date" 
                           name="deadline"
                           value="2025-11-10"
                           class="w-full border rounded-lg px-3 py-2 text-sm mt-1 focus:ring focus:ring-blue-200">
                </div>

                <!-- รายละเอียดงาน -->
                <div>
                    <label class="text-sm text-gray-600">รายละเอียดงาน</label>
                    <textarea name="description" rows="4"
                              class="w-full border rounded-lg px-3 py-2 text-sm mt-1 focus:ring focus:ring-blue-200"
                              placeholder="รายละเอียดที่ต้องให้นักศึกษาส่ง เช่น รูปเล่ม รายงาน PowerPoint ฯลฯ"></textarea>
                </div>

                <div class="flex justify-between pt-6">

                    <!-- Back -->
                    <a href="/teacher/review"
                       class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 text-sm">
                        ยกเลิก
                    </a>

                    <!-- Save -->
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 shadow text-sm">
                        บันทึกการเปลี่ยนแปลง
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</x-layouts.app>
