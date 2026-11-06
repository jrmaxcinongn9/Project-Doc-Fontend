<x-mobile-nav profile="/admin/profile" title="ผู้ดูแลระบบ" />

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

    
    <div class="pt-8 pb-6 px-6 border-b border-slate-100 flex flex-col items-center justify-center">
        <a href="/admin/dashboard" class="block transition-transform hover:scale-105 duration-300 mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Admin Logo" 
                 class="h-14 md:h-16 w-auto object-contain drop-shadow-sm">
        </a>
        <div class="px-3 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold uppercase tracking-widest rounded-full">
            Admin Panel
        </div>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <x-notification-bell />
        <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 mt-2">Menu</p>
        <ul class="space-y-1.5">
            
            <li>
                <a href="/admin/dashboard" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/dashboard') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('admin/dashboard') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0h6" />
                    </svg>
                    <span class="text-[14px]">Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/admin/pending"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/pending*')
                             ? 'bg-orange-50 text-[#F26522] font-semibold'
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 transition-colors {{ request()->is('admin/pending*') ? 'text-[#F26522]' : 'text-slate-400' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-[14px]">บัญชีรออนุมัติ</span>
                </a>
            </li>

            <li>
                <a href="/admin/users" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/users*') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('admin/users*') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.121 17.804A4 4 0 018 17h8a4 4 0 013 1m-6-10a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-[14px]">User Management</span>
                </a>
            </li>

          <!--   <li>
                <a href="/admin/classroom" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/classroom*') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('admin/classroom*') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                    </svg>
                    <span class="text-[14px]">Classroom Management</span>
                </a>
            </li> -->

          <!--   <li>
                <a href="/admin/documents"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/documents*') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('admin/documents*') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-[14px]">Document</span>
                </a>
            </li> -->
            <li>
                <a href="/admin/support"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('admin/support*')
                             ? 'bg-orange-50 text-[#F26522] font-semibold'
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 {{ request()->is('admin/support*') ? 'text-[#F26522]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13h4l2 3h6l2-3h4M5 5h14l2 8v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6l2-8z" />
                    </svg>
                    <span class="text-[14px]">เรื่องที่แจ้ง</span>
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
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="text-[14px]">ออกจากระบบ</span>
            </button>
        </form>
    </div>

</aside>