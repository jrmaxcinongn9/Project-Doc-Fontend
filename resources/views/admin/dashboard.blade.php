<x-layouts.app title="Admin Dashboard">
<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 p-4 sm:p-6 lg:p-10 min-w-0">
        <div class="max-w-7xl mx-auto w-full">
            
            <div class="flex justify-between items-end mb-10 pb-6 border-b border-slate-200/70">
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">Overview</h2>
                    <p class="text-slate-500 mt-1 text-sm font-medium">สรุปภาพรวมข้อมูลในระบบทั้งหมด</p>
                </div>
                <div class="mb-1">
                    <x-admintopbar-profile />
                </div>
            </div>

            @php
                // ปรับข้อมูลให้เก็บ SVG Path ของ Icon เข้าไปด้วย
                $displayCards = [
                    [
                        'label' => 'นักศึกษาทั้งหมด',
                        'value' => $stats['students'] ?? 0,
                        // Icon: Users
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
                        'color' => '#3b82f6', // Blue
                        'bg'    => '#eff6ff',
                        'trend' => '+12%'
                    ],
                    [
                        'label' => 'อาจารย์ทั้งหมด',
                        'value' => $stats['teachers'] ?? 0,
                        // Icon: Academic Cap / Teacher
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />',
                        'color' => '#F26522', // เปลี่ยนเป็นส้ม มจธ. ให้เข้าตีมระบบ
                        'bg'    => '#fff0e8',
                    ],
                    [
                        'label' => 'งานทั้งหมด',
                        'value' => $stats['assignments'] ?? 0,
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
                        'color' => '#8b5cf6',
                        'bg'    => '#f5f3ff',
                    ],
                    [
                        'label' => 'งานที่ส่งแล้ว',
                        'value' => $stats['submissions'] ?? 0,
                        'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                        'color' => '#10b981',
                        'bg'    => '#ecfdf5',
                    ],
                ];
            @endphp

            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl text-sm">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-6">
                @foreach($displayCards as $card)
                    <div class="group bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden flex flex-col relative transition-all duration-300 hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] hover:-translate-y-1 h-full cursor-default">
                        
                        <div class="p-4 sm:p-6 pb-2 flex justify-between items-start">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" 
                                 style="background-color: {{ $card['bg'] }}; color: {{ $card['color'] }}; box-shadow: 0 4px 12px {{ $card['color'] }}20;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                                    {!! $card['icon'] !!}
                                </svg>
                            </div>
                            
                            {{-- เดิมตรงนี้เป็นป้าย +12% กับ +4% ที่เขียนค่าไว้ตายตัว
                                 ไม่ได้มาจากข้อมูลจริงและไม่เคยเปลี่ยน จึงถอดออก
                                 ตัวเลขที่อ่านแล้วเชื่อไม่ได้ แย่กว่าไม่มีตัวเลข --}}
                        </div>

                        <div class="p-4 sm:p-6 pt-3 sm:pt-4 pb-0 z-10">
                            <h3 class="text-[26px] sm:text-[32px] md:text-[40px] font-black text-slate-800 tracking-tight leading-none mb-1">
                                {{ number_format($card['value']) }}
                            </h3>
                            <p class="text-slate-400 text-sm font-medium">{{ $card['label'] }}</p>
                        </div>

                        <div class="mt-auto w-full h-20 relative leading-[0] pt-4 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                            <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="w-full h-full drop-shadow-sm">
                                <defs>
                                    <linearGradient id="grad-{{ $loop->index }}" x1="0%" y1="0%" x2="0%" y2="100%">
                                        <stop offset="0%" style="stop-color:{{ $card['color'] }};stop-opacity:0.25" />
                                        <stop offset="100%" style="stop-color:{{ $card['color'] }};stop-opacity:0.01" />
                                    </linearGradient>
                                </defs>
                                <path d="M0 30 Q 15 30, 25 20 T 50 25 T 75 15 T 100 20 L 100 40 L 0 40 Z" fill="url(#grad-{{ $loop->index }})" />
                                <path d="M0 30 Q 15 30, 25 20 T 50 25 T 75 15 T 100 20" stroke="{{ $card['color'] }}" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" class="drop-shadow-md" />
                            </svg>
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- แยกต่อสาขา ตามที่อาจารย์ขอ --}}
            <div class="mt-10">
                <h3 class="text-lg font-bold text-slate-800 mb-1">แยกตามสาขา</h3>
                <p class="text-sm text-slate-500 mb-5">
                    สาขาคือค่าที่ใช้จับคู่นักศึกษากับครูนิเทศ
                </p>

                <div class="bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="stack-on-mobile w-full text-left border-collapse">
                            <thead class="bg-slate-50/80 border-b border-slate-100">
                                <tr>
                                    <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">สาขา</th>
                                    <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">นักศึกษา</th>
                                    <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">ครู</th>
                                    <th class="px-6 py-4 text-[13px] font-bold text-slate-500 uppercase tracking-wider">งาน</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($byCategory as $c)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-slate-800">{{ $c->label }}</div>
                                        {{-- นักศึกษาในสาขาที่ไม่มีครูเลย คือคนที่ไม่มีใครนิเทศ --}}
                                        @if($c->uncovered)
                                        <div class="text-[11px] text-amber-600 font-medium mt-0.5">
                                            ยังไม่มีครูในสาขานี้
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-slate-700 font-bold">{{ $c->students }}</td>
                                    <td class="px-6 py-4 text-slate-700 font-bold">{{ $c->teachers }}</td>
                                    <td class="px-6 py-4 text-slate-700 font-bold">{{ $c->assignments }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                                        ยังไม่มีข้อมูล
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
</x-layouts.app>