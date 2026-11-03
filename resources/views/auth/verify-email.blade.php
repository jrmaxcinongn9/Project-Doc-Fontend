<x-layouts.app title="ยืนยันอีเมล | KMUTT">

<div class="relative min-h-screen flex items-center justify-center p-4 text-white bg-[#0f172a]">

    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br
                    from-[#0f172a] via-[#1e293b] to-[#020617]"></div>
        <div class="absolute top-[-100px] left-[-100px] w-[500px] h-[500px]
                    bg-[#F58220]/20 blur-[130px] rounded-full"></div>
    </div>

    <div class="relative z-10 w-full max-w-md rounded-3xl bg-white/5 border border-white/10 p-10 text-center">

        @if ($ok)
            <p class="text-5xl mb-4 text-[#F58220]">&#10003;</p>
            <h1 class="text-2xl font-semibold mb-3">ยืนยันอีเมลสำเร็จ</h1>
        @else
            <p class="text-5xl mb-4 text-red-400">&#10007;</p>
            <h1 class="text-2xl font-semibold mb-3">ยืนยันอีเมลไม่สำเร็จ</h1>
        @endif

        <p class="text-white/70 mb-8">{{ $message }}</p>

        <a href="/login/student"
           class="inline-block px-6 py-3 rounded-2xl bg-[#F58220] text-white hover:bg-[#d96f16] transition-all">
            ไปหน้าเข้าสู่ระบบ
        </a>
    </div>
</div>

</x-layouts.app>
