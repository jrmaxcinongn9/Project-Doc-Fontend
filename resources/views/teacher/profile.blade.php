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

                <x-avatar-uploader />

                <h2 class="text-xl font-semibold text-gray-800">
                    {{ session('user.name') ?? '-' }}
                </h2>

                <p class="text-gray-500 text-sm">
                    {{ session('user.email') ?? '-' }}
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
