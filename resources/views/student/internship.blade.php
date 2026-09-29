<x-layouts.app title="Internship Documents">
<div class="min-h-screen flex bg-[#f5f7fa]">

    <!-- Sidebar -->
    <x-student-sidebar />

    <!-- Main -->
    <main class="flex-1 p-6 md:p-10">

        <!-- Topbar -->
        <x-student-topbar-profile title="เอกสารฝึกงาน" />

        @php
            $today = \Carbon\Carbon::today();
        @endphp

        <div class="max-w-5xl mx-auto mt-6">

        

            <!-- List -->
            <div class="space-y-4">

            @foreach ([
                [
                    'title' => 'Week 1 - รายงานประจำสัปดาห์',
                    'submitted' => true,
                    'submitted_at' => '2025-11-25',
                    'deadline' => '2025-11-28'
                ],
                [
                    'title' => 'Week 2 - รายงานประจำสัปดาห์',
                    'submitted' => true,
                    'submitted_at' => '2025-12-06',
                    'deadline' => '2025-12-05'
                ],
                [
                    'title' => 'Week 3 - รายงานประจำสัปดาห์',
                    'submitted' => false,
                    'submitted_at' => null,
                    'deadline' => '2025-12-12'
                ],
            ] as $week => $doc)

                @php
                    $deadlineDate = \Carbon\Carbon::parse($doc['deadline']);
                    $daysLeft = $today->diffInDays($deadlineDate, false); // ติดลบเมื่อเลยกำหนด

                    // สี deadline
                    if ($today->equalTo($deadlineDate)) {
                        $deadlineColor = 'text-yellow-700';
                    } elseif ($today->greaterThan($deadlineDate)) {
                        $deadlineColor = 'text-red-600';
                    } else {
                        $deadlineColor = 'text-gray-600';
                    }

                    // badge เดดไลน์
                    if ($daysLeft < 0) {
                        $badgeText = 'เลยกำหนด '.abs($daysLeft).' วัน';
                        $badgeClass = 'bg-red-50 text-red-700 border-red-100';
                    } elseif ($daysLeft === 0) {
                        $badgeText = 'ครบกำหนดวันนี้';
                        $badgeClass = 'bg-yellow-50 text-yellow-800 border-yellow-100';
                    } elseif ($daysLeft <= 7) {
                        $badgeText = 'เหลือ '.$daysLeft.' วัน';
                        $badgeClass = 'bg-yellow-50 text-yellow-800 border-yellow-100';
                    } else {
                        $badgeText = 'เหลือ '.$daysLeft.' วัน';
                        $badgeClass = 'bg-gray-50 text-gray-700 border-gray-200';
                    }

                    // สีวันที่ส่ง
                    $submittedColor = '';
                    if ($doc['submitted']) {
                        $submitDate = \Carbon\Carbon::parse($doc['submitted_at']);
                        $submittedColor = $submitDate->lessThanOrEqualTo($deadlineDate)
                            ? 'text-green-700'
                            : 'text-yellow-700';
                    }
                @endphp

                <div class="bg-white border border-gray-200 rounded-2xl p-5 md:p-6 shadow-sm hover:shadow-md transition">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                        <!-- LEFT -->
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-lg font-semibold text-gray-900 truncate">
                                    {{ $doc['title'] }}
                                </p>

                                <span class="text-xs px-2 py-1 rounded-full border {{ $badgeClass }}">
                                    {{ $badgeText }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1 text-sm">
                                <p class="{{ $deadlineColor }}">
                                    กำหนดส่ง: <span class="font-medium">{{ $deadlineDate->format('d/m/Y') }}</span>
                                </p>

                                @if ($doc['submitted'])
                                    <p class="{{ $submittedColor }}">
                                        วันที่ส่ง: <span class="font-medium">{{ $submitDate->format('d/m/Y') }}</span>
                                    </p>
                                @else
                                    <p class="text-gray-400">
                                        วันที่ส่ง: <span class="font-medium">-</span>
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- RIGHT (Actions) -->
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($doc['submitted'])
                                <a href="/student/internship/edit?week={{ $week + 1 }}"
                                   class="px-4 py-2 rounded-xl bg-yellow-500 text-white hover:bg-yellow-600 text-sm shadow-sm">
                                   แก้ไขงาน
                                </a>
                            @else
                                <a href="/student/internship/upload?week={{ $week + 1 }}"
                                   class="px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm shadow-sm">
                                   ส่งงาน
                                </a>
                            @endif

                            <a href="/student/internship/view?week={{ $week + 1 }}"
                               class="px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm shadow-sm">
                               ดูรายละเอียด
                            </a>
                        </div>

                    </div>
                </div>

            @endforeach
            </div>

        </div>

    </main>
</div>
</x-layouts.app>
