<x-layouts.app title="Student Login | KMUTT">

<div class="relative min-h-screen flex flex-col text-white overflow-hidden bg-[#0f172a]">

    <!-- 🔥 BACKGROUND (NO IMAGE) -->
    <div class="absolute inset-0">

        <!-- Gradient Base -->
        <div class="absolute inset-0 bg-gradient-to-br 
                    from-[#0f172a] via-[#1e293b] to-[#020617]"></div>

        <!-- Glow KMUTT -->
        <div class="absolute top-[-100px] left-[-100px] w-[400px] h-[400px] 
                    bg-[#F58220]/30 blur-[120px] rounded-full"></div>

        <div class="absolute bottom-[-120px] right-[-100px] w-[400px] h-[400px] 
                    bg-orange-500/20 blur-[120px] rounded-full"></div>

        <!-- Soft Grid Effect -->
        <div class="absolute inset-0 opacity-[0.05]"
             style="background-image: linear-gradient(to right, white 1px, transparent 1px),
                    linear-gradient(to bottom, white 1px, transparent 1px);
                    background-size: 40px 40px;">
        </div>

    </div>

    <!-- 🔝 NAVBAR -->
    <div class="relative z-10 flex justify-between items-center px-10 py-6">

        <a href="/" class="flex items-center gap-3">
            <img src="{{ asset('images/logoo.png') }}" 
                 class="h-14 drop-shadow-lg">
            <span class="text-lg font-semibold tracking-wide text-white/80">
                INTERNSHIP SYSTEM
            </span>
        </a>

        <div class="flex items-center gap-3">

            <button onclick="openSupport()"
                class="px-5 py-2 rounded-xl text-white/80
                       border border-white/10 
                       bg-white/5 backdrop-blur-xl
                       hover:bg-white/10 hover:text-white
                       transition-all">
                Support
            </button>

            <a href="/register"
               class="px-5 py-2 rounded-xl text-white font-semibold
                      bg-gradient-to-r from-[#F58220] to-[#E06B00]
                      shadow-lg shadow-orange-500/30
                      hover:shadow-orange-500/60
                      hover:-translate-y-0.5 transition-all">
                Sign Up
            </a>

        </div>
    </div>

    <!-- 🧾 LOGIN -->
    <div class="relative z-10 flex-1 flex items-center justify-center px-4">

        <div class="w-full max-w-md 
                    bg-white/5 backdrop-blur-2xl 
                    border border-white/10 
                    p-10 rounded-3xl 
                    shadow-[0_30px_120px_rgba(0,0,0,0.9)]
                    relative overflow-hidden">

            <!-- Glow inside -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#F58220]/20 blur-3xl rounded-full"></div>

            <!-- Title -->
            <h2 class="text-3xl font-bold mb-8 text-center tracking-wide">
                <span class="bg-gradient-to-r from-orange-400 to-orange-600 bg-clip-text text-transparent">
                    KMUTT LOGIN
                </span>
            </h2>

            <!-- ALERT -->
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-500/10 border border-red-400/30 text-red-300 rounded-xl text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <!-- FORM -->
            <form method="POST" action="/login" class="space-y-6">
                @csrf

                <!-- EMAIL -->
                <div>
                    <label class="text-sm text-white/70 mb-2 block">Email</label>
                    <input type="email" name="email"
                        class="w-full px-5 py-3 rounded-2xl 
                               bg-white/5 border border-white/10 
                               text-white placeholder-gray-400
                               focus:ring-2 focus:ring-[#F58220]
                               focus:bg-white/10
                               transition-all">
                </div>

                <!-- PASSWORD -->
                <div>
                    <label class="text-sm text-white/70 mb-2 block">Password</label>
                    <input type="password" name="password"
                        class="w-full px-5 py-3 rounded-2xl 
                               bg-white/5 border border-white/10 
                               text-white placeholder-gray-400
                               focus:ring-2 focus:ring-[#F58220]
                               focus:bg-white/10
                               transition-all">
                </div>

                <!-- FORGOT -->
                <div class="flex justify-end">
                    <a href="/forgot-password"
                       class="text-sm text-white/50 hover:text-[#F58220] transition">
                        Forgot Password?
                    </a>
                </div>

                <!-- BUTTON -->
                <button type="submit"
                    class="w-full py-3 rounded-2xl font-semibold text-white
                           bg-gradient-to-r from-[#F58220] to-[#E06B00]
                           hover:from-[#ff8c2a] hover:to-[#c75a00]
                           shadow-lg shadow-orange-500/30
                           hover:shadow-orange-500/60
                           hover:-translate-y-0.5
                           transition-all duration-200">

                    Login
                </button>
            </form>

        </div>

    </div>

</div>

</x-layouts.app>