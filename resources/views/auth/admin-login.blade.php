<x-layouts.app title="Admin Login">

    <div class="w-full min-h-screen flex flex-col">

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 px-10 py-6">
            <span class="text-2xl font-medium">DOC</span>
            <div class="ml-6 text-gray-500">
                Login &nbsp; > &nbsp; <span class="text-black">Admin</span>
            </div>
        </div>

        <!-- Login Form -->
        <div class="flex-1 flex justify-center">
            <div class="w-full max-w-md mt-20">

                {{-- Error --}}
                @if ($errors->any())
                    <div class="mb-5 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/login">
                    @csrf

                    <!-- สำคัญมาก ต้องมี -->
                    <input type="hidden" name="role" value="ADMIN">

                    <h2 class="text-xl font-semibold text-gray-700 mb-6">
                        Admin Login
                    </h2>

                    <!-- Email -->
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full border border-gray-300 rounded p-2 mb-4 focus:ring-2 focus:ring-red-500"
                    >

                    <!-- Password -->
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full border border-gray-300 rounded p-2 mb-6 focus:ring-2 focus:ring-red-500"
                    >

                    <!-- Button -->
                    <button
                        type="submit"
                        class="w-full bg-red-600 text-white rounded py-2 hover:bg-red-700"
                    >
                        Login Admin
                    </button>

                </form>

            </div>
        </div>

    </div>

</x-layouts.app>
