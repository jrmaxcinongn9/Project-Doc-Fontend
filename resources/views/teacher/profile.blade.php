<x-layouts.app title="My Profile">

<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- Sidebar -->
    <x-teacher-sidebar />

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10">

        <!-- Topbar -->
        <x-topbar-profile title="โปรไฟล์ของฉัน" />

        <!-- Profile Card -->
        <div class="bg-white p-8 rounded-xl shadow max-w-xl mx-auto">

            <!-- Avatar -->
            <div class="flex flex-col items-center mb-6">

                <div class="bg-gray-200 rounded-full p-4 w-28 h-28 flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-16 h-16 text-gray-600"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75z
                            M6 20.25v-.75A6.75 6.75 0 0112.75 12h.5
                            A6.75 6.75 0 0120 19.5v.75H6z" />
                    </svg>
                </div>

                <h2 class="text-xl font-semibold text-gray-800">
                    {{ session('user.name') ?? 'Teacher User' }}
                </h2>

                <p class="text-gray-500 text-sm">
                    {{ session('user.email') ?? 'example@gmail.com' }}
                </p>

                <span class="mt-2 px-3 py-1 text-xs rounded-full 
                    {{ session('user.role') === 'ADMIN' ? 'bg-red-100 text-red-600' :
                    (session('user.role') === 'TEACHER' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600') }}">
                    {{ session('user.role') ?? 'TEACHER' }}
                </span>

            </div>

            <hr class="my-6">

            <!-- Info Section -->
            <div class="space-y-4 text-gray-700">

                <div>
                    <label class="text-sm text-gray-500">ชื่อ - นามสกุล</label>
                    <p class="font-medium">{{ session('user.name') }}</p>
                </div>

                <div>
                    <label class="text-sm text-gray-500">อีเมล</label>
                    <p class="font-medium">{{ session('user.email') }}</p>
                </div>

            </div>

           
           

        </div>

    </main>
</div>

</x-layouts.app>
