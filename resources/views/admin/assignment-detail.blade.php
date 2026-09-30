<x-layouts.app title="รายละเอียดงาน">

<div class="min-h-screen flex bg-gradient-to-br from-slate-50 to-blue-50">

    <x-admin-sidebar />

    <main class="flex-1 p-8 pt-[calc(3.5rem+2rem)] lg:pt-8">

        <h1 class="text-2xl font-bold text-gray-800 mb-6">
            📄 {{ $assignment['assignment_name'] }}
        </h1>

        <div class="bg-white rounded-2xl shadow p-6">

            <h2 class="font-semibold mb-4 text-gray-700">
                👥 นักเรียนในห้อง
            </h2>

            <div class="space-y-3">

                @foreach($students as $s)
                <div class="flex justify-between items-center p-4 rounded-xl border hover:shadow">

                    <div>
                        <p class="font-semibold text-gray-800">
                            {{ $s->name }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $s->student_id }}
                        </p>
                    </div>

                    {{-- STATUS --}}
                    @if($s->submitted)
                        <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-600">
                            ส่งแล้ว
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-600">
                            ยังไม่ส่ง
                        </span>
                    @endif

                </div>
                @endforeach

            </div>

        </div>

    </main>

</div>

</x-layouts.app>