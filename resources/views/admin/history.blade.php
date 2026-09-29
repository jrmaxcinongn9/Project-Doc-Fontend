<x-layouts.app title="Document History">

<div class="min-h-screen flex bg-gradient-to-br from-slate-50 to-blue-50">

    <!-- SIDEBAR -->
    <x-admin-sidebar />

    <!-- MAIN -->
    <main class="flex-1 p-6 md:p-10">

        <!-- TOPBAR -->
        <x-admintopbar-profile title="ไฟล์งานทั้งหมดในระบบ" />

        <!-- HEADER -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <!-- SEARCH -->
            <div class="flex items-center bg-white px-4 py-2 rounded-xl shadow border w-full md:w-80">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M21 21l-4.35-4.35M10.5 18A7.5 7.5 0 1010.5 3a7.5 7.5 0 000 15z" />
                </svg>
                <input type="text" placeholder="ค้นหาไฟล์..."
                       class="ml-3 w-full outline-none text-sm">
            </div>

        </div>

        <!-- TABLE CARD -->
        <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-xl border border-white/40 overflow-hidden">

            <table class="w-full text-left">

                <!-- HEAD -->
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4">ไฟล์</th>
                        <th class="px-6 py-4">ผู้ส่ง</th>
                        <th class="px-6 py-4">ขนาด</th>
                        <th class="px-6 py-4">วันที่</th>
                        <th class="px-6 py-4 text-right">จัดการ</th>
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y">

                @forelse($documents as $doc)

                    <tr class="hover:bg-blue-50/40 transition">

                        <!-- FILE -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">

                                <div class="w-10 h-10 flex items-center justify-center 
                                            bg-blue-100 text-blue-600 rounded-xl text-sm font-bold">
                                    DOC
                                </div>

                                <div>
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ $doc->name }}
                                    </p>
                                    <p class="text-xs text-gray-400 font-mono">
                                        {{ $doc->file }}
                                    </p>
                                </div>

                            </div>
                        </td>

                        <!-- USER -->
                        <td class="px-6 py-4 text-sm">
                            <p class="text-gray-700 font-medium">{{ $doc->user }}</p>
                            <p class="text-xs text-gray-400">{{ $doc->email }}</p>
                        </td>

                        <!-- SIZE -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ number_format($doc->size / 1024, 1) }} KB
                        </td>

                        <!-- DATE -->
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($doc->date)->format('d/m/Y H:i') }}
                        </td>

                        <!-- ACTION -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">

                                <!-- VIEW -->
                                <a href="{{ $doc->file }}"
                                   class="px-3 py-1 text-xs rounded-lg bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition">
                                    ดู
                                </a>

                                <!-- DOWNLOAD -->
                                <a href="{{ $doc->file }}"
                                   class="px-3 py-1 text-xs rounded-lg bg-green-100 text-green-600 hover:bg-green-600 hover:text-white transition">
                                    ดาวน์โหลด
                                </a>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center py-10 text-gray-400">
                            ไม่มีไฟล์ในระบบ
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </main>

</div>

</x-layouts.app>