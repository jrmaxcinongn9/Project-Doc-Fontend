<x-layouts.app title="My Profile | KMUTT">

    {{-- กำหนดค่าสีส้ม มจธ. ไว้ใน Tailwind Config หรือใช้ Arbitrary Value แบบนี้ --}}
    {{-- สีส้มหลัก: #F58220, สีส้มเข้ม: #E06B00 --}}

    <div class="min-h-screen flex bg-[#f8fafc]"> {{-- เปลี่ยนพื้นหลังให้นุ่มนวลขึ้น --}}

        <x-admin-sidebar />

        <main class="flex-1 p-6 md:p-10 pt-[calc(3.5rem+1.5rem)] lg:pt-6"> {{-- เพิ่ม Responsive Padding --}}

            <x-admintopbar-profile title="โปรไฟล์ผู้ดูแลระบบ" />

            <div class="max-w-2xl mx-auto mt-8">

                <div class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden">
                    
                    {{-- แถบสีส้มด้านบนเพิ่มความโดดเด่น --}}
                    <div class="h-2 bg-[#F58220]"></div>

                    <div class="p-8 md:p-10">
                        <div class="flex flex-col sm:flex-row items-center gap-6 border-b border-slate-100 pb-8 mb-8">
                            
                            {{-- Profile Image Container --}}
                            <div class="relative">
                                <div class="bg-slate-100 rounded-full p-1 w-32 h-32 flex items-center justify-center border-4 border-white shadow-inner">
                                    {{-- หากมีรูปจริงให้ใช้ tag img, อันนี้ใช้ SVG เดิมแต่ปรับสี --}}
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                         class="w-16 h-16 text-slate-400"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                               d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75zM6 20.25v-.75A6.75 6.75 0 0112.75 12h.5A6.75 6.75 0 0120 19.5v.75H6z" />
                                    </svg>
                                </div>
                                {{-- สถานะ Online (Optional) --}}
                                <span class="absolute bottom-2 right-2 block h-4 w-4 rounded-full ring-2 ring-white bg-green-500"></span>
                            </div>

                            <div class="flex-1 text-center sm:text-left">
                                <div class="flex items-center justify-center sm:justify-start gap-3 mb-1">
                                    <h2 class="text-3xl font-bold text-slate-950 tracker-tight">
                                        {{ session('user.name') ?? 'Administrator' }}
                                    </h2>
                                    {{-- Badge แบบใหม่ --}}
                                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-orange-50 text-[#E06B00] border border-orange-100">
                                        {{ session('user.role') ?? 'ADMIN' }}
                                    </span>
                                </div>
                                
                                <p class="text-slate-600 flex items-center justify-center sm:justify-start gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    {{ session('user.email') ?? 'admin@example.com' }}
                                </p>
                            </div>

                           

                        </div> {{-- End Header --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">

                            {{-- ข้อมูลช่องแรก --}}
                            <div class="p-6 rounded-2xl bg-slate-50 shadow-inner border border-slate-100">
                                <label class="text-sm text-slate-500 font-semibold mb-2 block">ชื่อ - นามสกุล</label>
                                <p class="font-semibold text-lg text-slate-900 border-l-4 border-[#F58220] pl-3">
                                    {{ session('user.name') ?? '-' }}
                                </p>
                            </div>

                            {{-- ข้อมูลช่องสอง --}}
                            <div class="p-6 rounded-2xl bg-slate-50 shadow-inner border border-slate-100">
                                <label class="text-sm text-slate-500 font-semibold mb-2 block">อีเมล</label>
                                <p class="font-semibold text-lg text-slate-900 border-l-4 border-[#F58220] pl-3">
                                    {{ session('user.email') ?? '-' }}
                                </p>
                            </div>

                            {{-- ข้อมูลเพิ่มเติม (Optional) --}}
                            <div class="p-6 rounded-2xl bg-slate-50 shadow-inner border border-slate-100">
                                <label class="text-sm text-slate-500 font-semibold mb-2 block">รหัสประจำตัว</label>
                                <p class="font-semibold text-lg text-slate-900 border-l-4 border-slate-300 pl-3">
                                    {{ session('user.student_id') ?? 'N/A' }}
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</x-layouts.app>