<x-layouts.app title="Teacher Login">

<div class="w-full min-h-screen flex flex-col">

    <!-- Topbar -->
    <div class="flex items-center gap-2 px-10 py-6">
        <span class="text-2xl font-medium">DOC</span>
        <div class="ml-6 text-gray-500">
            Login &nbsp; > &nbsp; <span class="text-black">Teacher</span>
        </div>
    </div>

    <div class="flex-1 flex justify-center">
        <div class="w-full max-w-md mt-20">

            @if ($errors->any())
                <div class="mb-5 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <!-- 🔥 สำคัญมาก ต้องมี -->
                <input type="hidden" name="role" value="TEACHER">

                <h2 class="text-xl font-semibold text-gray-700 mb-6">
                    Teacher Login
                </h2>

                <!-- Email -->
                <label class="block text-sm mb-1">Email</label>
                <input type="email"
                       name="email"
                       class="w-full border border-gray-300 rounded p-2 mb-4"
                       value="{{ old('email') }}">

                <!-- Password -->
                <label class="block text-sm mb-1">Password</label>
                <input type="password"
                       name="password"
                       class="w-full border border-gray-300 rounded p-2 mb-4">

                <!-- Submit -->
                <button class="w-full bg-black text-white rounded py-2 hover:bg-gray-900">
                    Login
                </button>
            </form>

        </div>
    </div>

</div>

</x-layouts.app>
