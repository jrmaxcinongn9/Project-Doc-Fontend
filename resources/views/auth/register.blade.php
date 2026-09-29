<x-layouts.app title="Register | KMUTT">

<div class="relative min-h-screen flex flex-col text-white overflow-hidden bg-[#0f172a]">

    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-gradient-to-br 
                    from-[#0f172a] via-[#1e293b] to-[#020617]"></div>

        <div class="absolute top-[-100px] left-[-100px] w-[500px] h-[500px] 
                    bg-[#F58220]/20 blur-[130px] rounded-full"></div>

        <div class="absolute bottom-[-120px] right-[-100px] w-[500px] h-[500px] 
                    bg-orange-500/15 blur-[130px] rounded-full"></div>

        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image: linear-gradient(to right, white 1px, transparent 1px),
                    linear-gradient(to bottom, white 1px, transparent 1px);
                    background-size: 45px 45px;">
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
               class="px-6 py-2 rounded-xl text-white/80 border border-white/10 bg-white/5 backdrop-blur-xl hover:bg-white/10 hover:text-white transition-all">
                Sign In
            </a>
        </div>
    </div>

    <div class="relative z-10 flex-1 flex items-center justify-center px-4 py-10">

        <div class="w-full max-w-xl 
                    bg-white/5 backdrop-blur-3xl 
                    border border-white/10 
                    p-10 rounded-[2.5rem] 
                    shadow-[0_30px_120px_rgba(0,0,0,0.7)]
                    relative overflow-hidden">

            <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#F58220]/10 blur-3xl rounded-full"></div>

            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold tracking-wide">
                    <span class="bg-gradient-to-r from-orange-400 to-orange-600 bg-clip-text text-transparent">
                        CREATE ACCOUNT
                    </span>
                </h2>
                <p class="text-sm text-white/50 mt-2">Faculty of Industrial Education and Technology</p>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 text-red-300 rounded-2xl text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/register') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    <div class="md:col-span-2">
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:ring-2 focus:ring-[#F58220] focus:bg-white/10 transition-all outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Student ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" required maxlength="11"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white font-mono focus:ring-2 focus:ring-[#F58220] transition-all outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">KMUTT Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:ring-2 focus:ring-[#F58220] transition-all outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Academic Year</label>
                        <select name="academicYear" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white focus:ring-2 focus:ring-[#F58220] transition-all outline-none appearance-none cursor-pointer">
                            <option value="" disabled selected class="bg-[#1e293b]">Select Year</option>
                            @php $year = 2569; @endphp
                            @for ($i = 0; $i < 6; $i++)
                                <option value="{{ $year - $i }}" class="bg-[#1e293b]">{{ $year - $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Major</label>
                        <select name="major" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white focus:ring-2 focus:ring-[#F58220] transition-all outline-none appearance-none cursor-pointer">
                            <option value="" disabled selected class="bg-[#1e293b]">Select Major</option>
                            <option value="ครุศาสตร์ไฟฟ้า" class="bg-[#1e293b]">ครุศาสตร์ไฟฟ้า</option>
                            <option value="ครุศาสตร์เครื่องกล" class="bg-[#1e293b]">ครุศาสตร์เครื่องกล</option>
                            <option value="ครุศาสตร์โยธา" class="bg-[#1e293b]">ครุศาสตร์โยธา</option>
                            <option value="ครุศาสตร์อุตสาหการ" class="bg-[#1e293b]">ครุศาสตร์อุตสาหการ</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white focus:ring-2 focus:ring-[#F58220] transition-all outline-none">
                    </div>

                    <div>
                        <label class="text-xs text-white/60 mb-2 ml-1 block uppercase tracking-widest">Confirm Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-5 py-3 rounded-2xl bg-white/5 border border-white/10 text-white focus:ring-2 focus:ring-[#F58220] transition-all outline-none">
                    </div>

                </div>

                <button type="submit"
                    class="w-full py-4 mt-4 rounded-2xl font-bold text-white uppercase tracking-widest
                           bg-gradient-to-r from-[#F58220] to-[#E06B00]
                           hover:from-[#ff8c2a] hover:to-[#c75a00]
                           shadow-lg shadow-orange-500/20
                           hover:shadow-orange-500/40
                           hover:-translate-y-1 transition-all duration-300">
                    Register Now
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-white/40">
                    Already have an account? 
                    <a href="/login/student" class="text-orange-400 hover:text-orange-300 font-semibold transition">Sign In</a>
                </p>
            </div>

        </div>

    </div>

</div>

</x-layouts.app>