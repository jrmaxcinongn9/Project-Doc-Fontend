@props(['title' => null])

<div class="hidden lg:flex justify-between items-center mb-8 w-full">

    <!-- Title -->
    <h1 class="text-2xl font-semibold text-gray-700">
        {{ $title ?? '' }}
    </h1>

    <!-- Admin Profile -->
    <a href="/admin/profile" class="flex items-center gap-3 cursor-pointer hover:opacity-80">

        <div class="text-right leading-tight">
            <p class="text-sm font-medium text-gray-700">
                {{ session('user.name') ?? 'Administrator' }}
            </p>
            <p class="text-xs text-gray-500">
                {{ session('user.role') ?? 'Admin' }}
            </p>
        </div>

        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-10 h-10 p-2 bg-gray-200 rounded-full text-gray-600"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M15.75 7.5A3.75 3.75 0 1112 3.75a3.75 3.75 0 013.75 3.75z
                     M6 20.25v-.75A6.75 6.75 0 0112.75 12h.5
                     A6.75 6.75 0 0120 19.5v.75H6z" />
        </svg>

    </a>
</div>
