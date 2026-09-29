<x-layouts.app title="Admin Dashboard">
<div class="min-h-screen flex bg-slate-50">
    <x-admin-sidebar />

    <main class="flex-1 p-8 md:p-10">
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
                        'trend' => '+4%'
                    ],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($displayCards as $card)
                    <div class="group bg-white rounded-3xl shadow-[0_2px_12px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden flex flex-col relative transition-all duration-300 hover:shadow-[0_8px_24px_rgba(0,0,0,0.08)] hover:-translate-y-1 h-full cursor-default">
                        
                        <div class="p-6 pb-2 flex justify-between items-start">
                            <div class="w-12 h-12 rounded-2xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110" 
                                 style="background-color: {{ $card['bg'] }}; color: {{ $card['color'] }}; box-shadow: 0 4px 12px {{ $card['color'] }}20;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-6 h-6">
                                    {!! $card['icon'] !!}
                                </svg>
                            </div>
                            
                            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-sm text-xs font-bold">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                                </svg>
                                {{ $card['trend'] }}
                            </div>
                        </div>

                        <div class="p-6 pt-4 pb-0 z-10">
                            <h3 class="text-[32px] md:text-[40px] font-black text-slate-800 tracking-tight leading-none mb-1">
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

        </div>
    </main>
</div>
</x-layouts.app>