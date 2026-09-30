<x-layouts.app title="สร้างงานใหม่">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- SIDEBAR -->
    <x-teacher-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-10 pt-[calc(3.5rem+2.5rem)] lg:pt-10">

        <!-- TOPBAR -->
        <x-topbar-profile title="สร้างงานใหม่" />

        <!-- DESCRIPTION -->
        <p class="text-sm text-gray-500 mb-8">
            เพิ่มงานใหม่ให้นักศึกษาในระบบ
        </p>

        <!-- FORM CARD -->
        <div class="bg-white p-8 rounded-2xl shadow max-w-2xl">

            <form action="/teacher/assignments" method="POST">
                @csrf

                <!-- ชื่อชิ้นงาน -->
                <div class="mb-6">
                    <label class="text-sm text-gray-700 font-medium mb-2 block">
                        ชื่อชิ้นงาน
                    </label>
                    <input type="text" name="title"
                           class="w-full border rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-400"
                           placeholder="เช่น Week 1 - รายงานประจำสัปดาห์" required>
                </div>

                <!-- วันกำหนดส่ง -->
                <div class="mb-6">
                    <label class="text-sm text-gray-700 font-medium mb-2 block">
                        วันกำหนดส่ง
                    </label>
                    <input type="date" name="due_date"
                           class="w-56 border rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-400"
                           required>
                </div>

                <!-- รายละเอียดงาน -->
                <div class="mb-8">
                    <label class="text-sm text-gray-700 font-medium mb-2 block">
                        รายละเอียดงาน
                    </label>
                    <textarea name="detail" rows="5"
                              class="w-full border rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-400"
                              placeholder="ระบุรายละเอียดงานที่ต้องการให้นักศึกษาทำ"></textarea>
                </div>


                <!-- BUTTONS -->
                <div class="flex justify-end gap-4">

                    <a href="/teacher/review"
                       class="px-5 py-2 border rounded-xl text-sm text-gray-600 hover:bg-gray-100">
                        ยกเลิก
                    </a>

                    <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white rounded-xl text-sm hover:bg-blue-700">
                        บันทึกงาน
                    </button>

                </div>

            </form>

        </div>

    </main>

</div>

</x-layouts.app>
