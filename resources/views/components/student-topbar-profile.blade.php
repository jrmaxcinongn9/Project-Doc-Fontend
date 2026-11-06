@props(['title' => null])

<div class="flex justify-between items-center mb-10">

    <h1 class="text-2xl font-semibold text-gray-700">
        {{ $title ?? '' }}
    </h1>

    <a href="{{ url('/student/profile') }}" class="hidden lg:flex items-center gap-3 cursor-pointer text-right hover:opacity-80 transition">

        <div class="text-right">
            <p class="text-sm font-medium text-gray-700">
                {{ session('user.name') ?? '-' }}
            </p>
            <p class="text-xs text-gray-500">โปรไฟล์</p>
        </div>

        <x-avatar />

    </a>
</div>