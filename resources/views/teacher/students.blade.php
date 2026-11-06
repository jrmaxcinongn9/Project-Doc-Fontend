<x-layouts.app title="นักศึกษาของฉัน">

@php
    // ชื่อไทยของสาขา ใช้ค่าเดียวกับที่นักศึกษาเลือกตอนสมัคร
    $categoryNames = [
        'SOFTWARE'  => 'ซอฟต์แวร์ / พัฒนาโปรแกรม',
        'NETWORK'   => 'เครือข่ายและระบบ',
        'DATA'      => 'ข้อมูลและวิเคราะห์',
        'HARDWARE'  => 'ฮาร์ดแวร์และอิเล็กทรอนิกส์',
        'DESIGN'    => 'ออกแบบ',
        'BUSINESS'  => 'ธุรกิจและบริการ',
        'OTHER'     => 'อื่น ๆ',
    ];
@endphp

<div class="min-h-screen flex bg-[#f5f7fa]">

    <x-teacher-sidebar />

    <main class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">

        <x-topbar-profile title="นักศึกษาของฉัน" />

        {{-- บอกให้ชัดว่ารายชื่อนี้มาจากไหน ไม่ใช่นักศึกษาทั้งระบบ --}}
        @if($category)
        <div class="mt-6 mb-6 text-sm text-slate-500">
            จับคู่จากสาขา
            <span class="font-semibold text-slate-700">
                {{ $categoryNames[$category] ?? $category }}
            </span>
            ซึ่งเป็นความเชี่ยวชาญของบัญชีคุณ
            <span class="ml-2 text-slate-400">ทั้งหมด {{ count($students) }} คน</span>
        </div>
        @endif

        {{-- ครูที่ยังไม่ได้ตั้งสาขา ต้องรู้ว่าว่างเพราะอะไร
             ไม่ใช่เข้าใจผิดว่าไม่มีใครสมัคร --}}
        @if($notice)
        <div class="mt-6 mb-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl text-sm">
            {{ $notice }}
        </div>
        @endif

        @if($errors->any())
        <div class="mt-6 mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="bg-white p-4 sm:p-6 rounded-xl shadow">

            {{-- บนมือถือเลื่อนตารางซ้ายขวาได้ ไม่ให้ทั้งหน้ากว้างเกินจอ --}}
            <div class="overflow-x-auto -mx-2 px-2">
            <table class="stack-on-mobile w-full text-left border-collapse">
                <thead>
                    <tr class="border-b text-gray-500 text-sm bg-gray-50">
                        <th class="p-3">รหัสนักศึกษา</th>
                        <th class="p-3">ชื่อนักศึกษา</th>
                        <th class="p-3">สาขาวิชา</th>
                        <th class="p-3">อีเมล</th>
                        <th class="p-3 text-right">การจัดการ</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($students as $s)
                    <tr class="border-b hover:bg-slate-50/60 transition-colors">
                        <td class="p-3 font-medium text-gray-700">{{ $s['student_id'] ?? '-' }}</td>
                        <td class="p-3">{{ $s['name'] ?? '-' }}</td>
                        <td class="p-3 text-sm text-gray-600">{{ $s['major'] ?? '-' }}</td>
                        <td class="p-3">{{ $s['email'] ?? '-' }}</td>
                        <td class="p-3 text-right">
                            <a href="/teacher/students/{{ $s['id'] }}" class="text-blue-600 text-sm hover:underline">
                                ดูรายละเอียด
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-10 text-center text-slate-400 text-sm">
                            @if($notice)
                                ยังไม่มีรายชื่อ เพราะบัญชีนี้ยังไม่ได้ตั้งสาขา
                            @else
                                ยังไม่มีนักศึกษาในสาขานี้ที่ผ่านการอนุมัติ
                            @endif
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
            </div>

        </div>

    </main>

</div>

</x-layouts.app>
