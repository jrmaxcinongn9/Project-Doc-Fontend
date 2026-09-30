{{-- ==================== MOBILE TOPBAR ==================== --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-white border-b border-slate-200 flex items-center justify-between px-4 h-14 shadow-sm">
    <button id="admin-sidebar-open" onclick="openAdminSidebar()" class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
    <a href="/admin/dashboard">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto object-contain">
    </a>
    <a href="/admin/profile" class="p-1">
        <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75zM6 20.25v-.75A6.75 6.75 0 0112.75 12h.5A6.75 6.75 0 0120 19.5v.75H6z"/>
            </svg>
        </div>
    </a>
</div>

{{-- ==================== OVERLAY ==================== --}}
<div id="admin-sidebar-overlay"
     onclick="closeAdminSidebar()"
     class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm hidden lg:hidden transition-opacity">
</div>

{{-- ==================== SIDEBAR ==================== --}}
<aside id="admin-sidebar"
       class="fixed top-0 left-0 h-full z-50 w-64 bg-white border-r border-slate-200 flex flex-col
              transform -translate-x-full transition-transform duration-300 ease-in-out
              lg:translate-x-0 lg:relative lg:flex lg:sticky lg:top-0 lg:h-screen">

    {{-- Logo Header --}}
    <div class="pt-8 pb-6 px-6 border-b border-slate-100 flex flex-col items-center justify-center">
        <a href="/admin/dashboard" class="block transition-transform hover:scale-105 duration-300 mb-3">
            <img src="{{ asset('images/logo.png') }}" alt="Admin Logo"
                 class="h-14 md:h-16 w-auto object-contain drop-shadow-sm">
        </a>
        <div class="px-3 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold uppercase tracking-widest rounded-full">
            Admin Panel
        </div>
    </div>

    {{-- Close button (mobile only) --}}
    <button onclick="closeAdminSidebar()"
            class="lg:hidden absolute top-4 right-4 p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Nav Items --}}
    <div class="p-4 flex-1 overflow-y-auto">
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
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span class="text-[14px]">ออกจากระบบ</span>
            </button>
        </form>
    </div>
</aside>

<script>
function openAdminSidebar() {
    document.getElementById('admin-sidebar').classList.remove('-translate-x-full');
    document.getElementById('admin-sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeAdminSidebar() {
    document.getElementById('admin-sidebar').classList.add('-translate-x-full');
    document.getElementById('admin-sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>