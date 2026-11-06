@props(['title' => null])

<div class="flex justify-between items-center mb-8 w-full">

    <!-- Title -->
    <h1 class="text-2xl font-semibold text-gray-700">
        {{ $title ?? '' }}
    </h1>

    <!-- Profile (โค้ดของคุณ) -->
    <a href="/teacher/profile" class="flex items-center gap-3 cursor-pointer hover:opacity-80">

        <div class="text-right leading-tight">
            <p class="text-sm font-medium text-gray-700">
                {{ session('user.name') ?? '-' }}
            </p>
            <p class="text-xs text-gray-500">
                {{ session('user.role') ?? 'Teacher' }}
            </p>
        </div>

        <x-avatar />

    </a>
</div>
