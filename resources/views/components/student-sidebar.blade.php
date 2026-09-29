<aside class="w-64 bg-white border-r border-slate-200 h-screen flex flex-col sticky top-0">
    
    <div class="pt-8 pb-6 px-6 border-b border-slate-100 flex justify-center items-center">
        <a href="/" class="block transition-transform hover:scale-105 duration-300">
            <img src="{{ asset('images/logoo.png') }}" alt="Logo" 
                 class="h-16 md:h-18 w-auto object-contain drop-shadow-sm">
        </a>
    </div>

    <div class="p-4 flex-1 overflow-y-auto">
        <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 mt-2">Menu</p>
        <ul class="space-y-1.5">
            <li>
                <a href="/student/dashboard"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('student/dashboard') 
                             ? 'bg-orange-50 text-[#F26522] font-semibold' 
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="w-5 h-5 transition-colors {{ request()->is('student/dashboard') ? 'text-[#F26522]' : 'text-slate-400' }}" 
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                              d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" 
                              d="M9 21V12h6v9" />
                    </svg>

                    <span class="text-[14px]">ห้องเรียน</span>
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