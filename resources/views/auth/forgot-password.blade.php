<x-layouts.app title="Forgot Password | KMUTT">

<div class="relative min-h-screen flex flex-col text-white overflow-hidden bg-[#0f172a]">

    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br 
                    from-[#0f172a] via-[#1e293b] to-[#020617]"></div>

        <div class="absolute top-[-100px] left-[-100px] w-[450px] h-[450px] 
                    bg-[#F58220]/25 blur-[120px] rounded-full"></div>

        <div class="absolute bottom-[-120px] right-[-100px] w-[450px] h-[450px] 
                    bg-orange-500/15 blur-[120px] rounded-full"></div>

        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(to right, white 1px, transparent 1px),
                    linear-gradient(to bottom, white 1px, transparent 1px);
                    background-size: 40px 40px;">
        </div>
    </div>

    <div class="relative z-10 flex justify-between items-center px-10 py-6">
        <a href="/" class="flex items-center gap-3 group">
            <img src="{{ asset('images/logoo.png') }}" 
                 class="h-14 drop-shadow-lg group-hover:scale-105 transition-transform">
            <span class="text-lg font-semibold tracking-wide text-white/80">
                INTERNSHIP SYSTEM
            </span>
        </a>

        <div class="flex items-center gap-3">
            <a href="/login/student"
               class="px-5 py-2 rounded-xl text-white/80
                      border border-white/10 bg-white/5 backdrop-blur-xl
                      hover:bg-white/10 hover:text-white transition-all">
                Back to Login
            </a>
        </div>
    </div>

    <div class="relative z-10 flex-1 flex items-center justify-center px-4">

        <div class="w-full max-w-md 
                    bg-white/5 backdrop-blur-2xl 
                    border border-white/10 
                    p-10 rounded-[2.5rem] 
                    shadow-[0_30px_120px_rgba(0,0,0,0.8)]
                    relative overflow-hidden">

            <div class="absolute -top-10 -right-10 w-32 h-32 bg-[#F58220]/10 blur-3xl rounded-full"></div>

            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold tracking-wide">
                    <span class="bg-gradient-to-r from-orange-400 to-orange-600 bg-clip-text text-transparent">
                        {{ session('otp_stage') ? 'VERIFY OTP' : 'RESET PASSWORD' }}
                    </span>
                </h2>
                <p class="text-sm text-white/50 mt-3 px-4">
                    @if(!session('otp_stage'))
                        Enter your email and we'll send you an OTP code to reset your password.
                    @else
                        Code has been sent to your email. Please enter the 6-digit OTP below.
                    @endif
                </p>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 text-green-300 rounded-2xl text-sm text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-300 rounded-2xl text-sm text-center">
                    {{ $errors->first() }}
                </div>
            @endif


            {{-- ============================
                STEP 1 : SEND OTP
            ============================= --}}
            @if(!session('otp_stage'))
            <form method="POST" action="/forgot-password/send" class="space-y-6">
                @csrf

                <div>
                    <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">KMUTT Email</label>
                    <input type="email" name="email" required
                           placeholder="student.name@mail.kmutt.ac.th"
                           class="w-full px-5 py-3.5 rounded-2xl bg-white/5 border border-white/10 
                                  text-white placeholder-gray-500 outline-none
                                  focus:ring-2 focus:ring-[#F58220] focus:bg-white/10 transition-all">
                </div>

                <button type="submit"
                    class="group w-full py-4 rounded-2xl font-bold text-white uppercase tracking-widest
                           bg-gradient-to-r from-[#F58220] to-[#E06B00]
                           hover:from-[#ff8c2a] hover:to-[#c75a00]
                           shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50
                           hover:-translate-y-1 active:scale-[0.98] transition-all duration-300
                           flex items-center justify-center gap-2">
                    <span>Send Reset Code</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
            </form>
            @endif


            {{-- ============================
                STEP 2 : VERIFY OTP
            ============================= --}}
            @if(session('otp_stage'))
            <form method="POST" action="/forgot-password/verify-otp" class="space-y-6">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">

                <div>
                    <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest text-center">Enter OTP Code</label>
                    <input type="text" name="otp" required maxlength="6"
                           placeholder="0 0 0 0 0 0"
                           class="w-full px-5 py-4 rounded-2xl bg-white/5 border border-white/10 
                                  text-white text-center text-2xl font-mono tracking-[0.5em] outline-none
                                  focus:ring-2 focus:ring-orange-500 focus:bg-white/10 transition-all">
                </div>

                <button type="submit"
                    class="w-full py-4 rounded-2xl font-bold text-white uppercase tracking-widest
                           bg-gradient-to-r from-[#F58220] to-[#E06B00]
                           hover:from-[#ff8c2a] hover:to-[#c75a00]
                           shadow-lg shadow-orange-500/30 hover:shadow-orange-500/50
                           hover:-translate-y-1 transition-all duration-300">
                    Verify Code
                </button>
            </form>
            @endif

            <div class="text-center mt-8">
                <p class="text-sm text-white/40">
                    Remembered it? 
                    <a href="/login/student" class="text-orange-400 hover:text-orange-300 font-semibold transition underline underline-offset-4">
                        Return to Login
                    </a>
                </p>
            </div>

        </div>
    </div>

</div>

</x-layouts.app>