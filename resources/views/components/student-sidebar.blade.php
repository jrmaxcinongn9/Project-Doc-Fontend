<x-mobile-nav profile="/student/profile" title="ห้องเรียนของฉัน" />

{{-- จอใหญ่: เมนูติดด้านซ้ายเหมือนเดิม จอเล็ก: ซ่อนไว้ทางซ้าย กด ☰ แล้วเลื่อนออกมา --}}
<aside id="appSidebar"
       class="w-64 shrink-0 bg-white border-r border-slate-200 h-screen flex flex-col
              fixed inset-y-0 left-0 z-50 -translate-x-full transition-transform duration-200
              lg:sticky lg:top-0 lg:z-auto lg:translate-x-0">
    {{-- ปิดเมนูบนจอเล็ก --}}
    <button type="button" data-sidebar-close aria-label="ปิดเมนู"
            class="lg:hidden absolute top-3 right-3 w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-700">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    
    <div class="pt-8 pb-6 px-6 border-b border-slate-100 flex justify-center items-center">
        <a href="/" class="block transition-transform hover:scale-105 duration-300">
            <img src="{{ asset('images/logoo.png') }}" alt="Logo" 
                 class="h-16 md:h-18 w-auto object-contain drop-shadow-sm">
        </a>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <x-notification-bell />
        <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 mt-2">Menu</p>
        <ul class="space-y-1.5">
            <li>
                <a href="/student/dashboard"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('student/dashboard', 'student/classrooms*') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('student/dashboard', 'student/classrooms*') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                              d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                              d="M9 21V12h6v9" />
                    </svg>

                    <span class="text-[14px]">ห้องเรียน</span>
                </a>
            </li>
            <li>
                <a href="/student/scores"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('student/scores*')
                             ? 'bg-orange-50 text-[#F26522] font-semibold'
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 transition-colors {{ request()->is('student/scores*') ? 'text-[#F26522]' : 'text-slate-400' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>

                    <span class="text-[14px]">คะแนนและความเห็น</span>
                </a>
            </li>

            <li>
                <a href="/support"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('support*')
                             ? 'bg-orange-50 text-[#F26522] font-semibold'
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ request()->is('support*') ? 'text-[#F26522]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.2 9a3.8 3.8 0 017.3 1.4c0 2.6-3.5 3-3.5 5.1M12 18h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[14px]">ช่วยเหลือ</span>
                </a>
            </li>
            </ul>
    </div>

    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
        <form action="/logout" method="POST">
            @csrf
            <button type="submit"
                class="group flex items-center gap-3 px-4 py-3 rounded-xl w-full text-left transition-all duration-200 hover:bg-red-50 text-slate-500 hover:text-red-600 font-medium">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-slate-400 group-hover:text-red-500 transition-colors"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15.75 9l3 3-3 3m3-3H9.75m6-6V5.25A2.25 2.25 0 0013.5 3H6A2.25 2.25 0 003.75 5.25v13.5A2.25 2.25 0 006 21h7.5a2.25 2.25 0 002.25-2.25V15" />
                </svg>

                <span class="text-[14px]">ออกจากระบบ</span>
            </button>
        </form>
    </div>

</aside>