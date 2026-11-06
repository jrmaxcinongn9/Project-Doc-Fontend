<x-layouts.app title="ห้องเรียนของฉัน">

{{-- รายการห้องเรียนของนักศึกษา แบบเดียวกับหน้าห้องเรียนของครู
     นักศึกษาดูได้อย่างเดียว ไม่มีปุ่มสร้าง แก้ไข หรือลบห้อง --}}
<div class="min-h-screen flex bg-[#f9fafb]">
    <x-student-sidebar />

    <main class="flex-1 relative pb-10 min-w-0">

        <div class="flex justify-between items-start px-6 md:px-10 py-6">
            <div>
                <h1 class="text-[22px] font-bold text-[#3f4b5b]">ห้องเรียนของฉัน</h1>
                <p class="text-sm text-slate-500 mt-1">เลือกห้องเพื่อดูงานและส่งงาน</p>
            </div>

            <a href="{{ url('/student/profile') }}" class="hidden lg:flex items-center gap-3 hover:opacity-80 transition">
                <div class="text-right">
                    <div class="text-sm font-semibold text-[#3f4b5b]">{{ session('user.name') ?? 'นักศึกษา' }}</div>
                    <div class="text-xs text-gray-500">โปรไฟล์</div>
                </div>
                <x-avatar />
            </a>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            @if (count($classrooms))
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($classrooms as $c)
                        <a href="/student/classrooms/{{ $c->id }}"
                           class="group block rounded-3xl border border-slate-200 border-t-4 border-t-[#FA4616] bg-white p-6 shadow-sm shadow-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-md">

                            <div class="text-lg font-bold text-slate-800 group-hover:text-[#FA4616] transition-colors">{{ $c->name }}</div>
                            <div class="mt-1 text-sm text-slate-500">{{ $c->major }} · ปีการศึกษา {{ $c->academicYear }}</div>
                            <div class="mt-1 text-sm text-slate-500">อาจารย์ {{ $c->teacher_name }}</div>

                            <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">งานทั้งหมด {{ $c->total }}</span>
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-600">ส่งแล้ว {{ $c->submitted }}</span>
                                @if ($c->pending > 0)
                                    <span class="rounded-full bg-amber-50 px-3 py-1 text-amber-600">ยังไม่ส่ง {{ $c->pending }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-slate-200 border-t-4 border-t-[#FA4616] bg-white px-6 py-16 text-center shadow-sm shadow-slate-200/70">
                    <h3 class="text-lg font-bold text-slate-800">ยังไม่มีห้องเรียน</h3>
                    <p class="mt-2 text-sm text-slate-500">เมื่ออาจารย์เพิ่มคุณเข้าห้องเรียน ห้องจะแสดงที่นี่</p>
                </div>
            @endif

        </div>
    </main>
</div>

</x-layouts.app>
