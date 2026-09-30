{{-- ==================== MOBILE TOPBAR ==================== --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 flex items-center justify-between px-4 h-14 shadow-sm">
    <button onclick="openTeacherSidebar()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <a href="/teacher/classroom">
        <img src="{{ asset('images/logoo.png') }}" alt="Logo" class="h-8 w-auto object-contain">
    </a>
    <a href="/teacher/profile" class="p-1">
        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75zM6 20.25v-.75A6.75 6.75 0 0112.75 12h.5A6.75 6.75 0 0120 19.5v.75H6z"/>
            </svg>
        </div>
    </a>
</div>

{{-- ==================== OVERLAY ==================== --}}
<div id="teacher-sidebar-overlay"
     onclick="closeTeacherSidebar()"
     class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm hidden lg:hidden transition-opacity">
</div>

{{-- ==================== SIDEBAR ==================== --}}
<aside id="teacher-sidebar"
       class="fixed top-0 left-0 h-full z-50 w-64 bg-white border-r border-slate-200 flex flex-col
              transform -translate-x-full transition-transform duration-300 ease-in-out
              lg:translate-x-0 lg:relative lg:flex lg:sticky lg:top-0 lg:h-screen">

    {{-- Logo Header --}}
    <div class="pt-8 pb-6 px-6 border-b border-slate-100 flex justify-center items-center">
        <a href="/teacher/classroom" class="block transition-transform hover:scale-105 duration-300">
            <img src="{{ asset('images/logoo.png') }}" alt="KMUTT Logo"
                 class="h-16 md:h-18 w-auto object-contain drop-shadow-sm">
        </a>
    </div>

    {{-- Close button (mobile only) --}}
    <button onclick="closeTeacherSidebar()"
            class="lg:hidden absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Nav Items --}}
    <div class="p-4 flex-1 overflow-y-auto">
        <p class="px-4 text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 mt-2">Teacher Menu</p>
        <ul class="space-y-1.5">
            <li>
                <a href="/teacher/classroom"
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          {{ request()->is('teacher/classroom*')
                             ? 'bg-orange-50 text-[#F26522] font-semibold'
                             : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900 font-medium' }}">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-5 h-5 transition-colors {{ request()->is('teacher/classroom*') ? 'text-[#F26522]' : 'text-slate-400' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9.75L12 3l9 6.75V21a.75.75 0 01-.75.75H3.75A.75.75 0 013 21V9.75z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 21V12h6v9" />
                    </svg>
                    <span class="text-[14px]">จัดการห้องเรียน</span>
                </a>
            </li>
        </ul>
    </div>

    {{-- Logout --}}
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

<script>
function openTeacherSidebar() {
    document.getElementById('teacher-sidebar').classList.remove('-translate-x-full');
    document.getElementById('teacher-sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeTeacherSidebar() {
    document.getElementById('teacher-sidebar').classList.add('-translate-x-full');
    document.getElementById('teacher-sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>