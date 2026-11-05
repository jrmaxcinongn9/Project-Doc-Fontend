<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * ภาพรวมงานของครู
 *
 * อาจารย์ขอตัวเลขสามอย่างต่อหนึ่งงาน ส่งกี่คน ยังไม่ส่งกี่คน คิดเป็นกี่เปอร์เซ็นต์
 * และให้คะแนนแล้วกี่คน รวมถึงบอกว่าเมนูครูควรมีสองเมนู ไม่ใช่เมนูเดียว
 *
 * หมายเหตุสำคัญ ฟิลด์ students[].status ในฐานข้อมูลตั้งเป็น PENDING ตอนสร้างงาน
 * แล้วไม่เคยถูกเขียนทับเลยตอนนักศึกษาส่งไฟล์ ใช้เป็นตัวนับไม่ได้
 * จึงนับการส่งจากไฟล์ที่ผูกกับงานชิ้นนั้นแทน ซึ่งเป็นข้อมูลที่เกิดขึ้นจริง
 */
class TeacherDashboardController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $empty = ['rows' => collect(), 'totals' => $this->totalsOf(collect())];

        try {
            $client = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120);

            $assignRes = $client->get($this->apiUrl . '/assignment/me');
            $fileRes = $client->get($this->apiUrl . '/files');

            if (!$assignRes->successful()) {
                return view('teacher.dashboard', $empty)
                    ->withErrors(['api' => 'ดึงรายการงานไม่สำเร็จ']);
            }

            $files = collect($fileRes->successful() ? ($fileRes->json()['files'] ?? $fileRes->json() ?? []) : []);

            $rows = collect($assignRes->json() ?? [])
                ->map(fn($a) => $this->summarise($a, $files))
                ->values();

            return view('teacher.dashboard', [
                'rows' => $rows,
                'totals' => $this->totalsOf($rows),
            ]);
        } catch (ConnectionException $e) {
            return view('teacher.dashboard', $empty)
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }

    /** สรุปตัวเลขของงานหนึ่งชิ้น */
    private function summarise(array $assignment, Collection $files): object
    {
        $id = $assignment['_id'] ?? null;
        $id = is_array($id) ? ($id['$oid'] ?? null) : $id;

        $students = collect($assignment['students'] ?? []);
        $total = $students->count();

        // นับหัวคนที่ส่ง ไม่ใช่นับไฟล์ ส่งสามไฟล์ก็ยังเป็นคนเดียว
        $submitted = $files
            ->filter(fn($f) => (string)($f['assignment_id'] ?? '') === (string)$id)
            ->pluck('uploaded_by_id')
            ->filter()
            ->unique()
            ->count();

        // ส่งเกินจำนวนคนในงานไม่ได้ กันตัวเลขเพี้ยนถ้ามีไฟล์ค้างจากคนที่ถูกถอดออก
        $submitted = min($submitted, $total);

        $graded = $students->filter(fn($s) => ($s['score'] ?? null) !== null)->count();

        return (object)[
            'id' => $id,
            'name' => $assignment['assignment_name'] ?? 'ไม่มีชื่องาน',
            'due_date' => $assignment['due_date'] ?? null,
            'max_score' => $assignment['max_score'] ?? null,
            'total' => $total,
            'submitted' => $submitted,
            'notSubmitted' => max($total - $submitted, 0),
            'graded' => $graded,
            'notGraded' => max($total - $graded, 0),
            'submittedPercent' => $this->percent($submitted, $total),
            'gradedPercent' => $this->percent($graded, $total),
        ];
    }

    private function totalsOf(Collection $rows): object
    {
        $total = $rows->sum('total');

        return (object)[
            'assignments' => $rows->count(),
            'total' => $total,
            'submitted' => $rows->sum('submitted'),
            'graded' => $rows->sum('graded'),
            'submittedPercent' => $this->percent($rows->sum('submitted'), $total),
            'gradedPercent' => $this->percent($rows->sum('graded'), $total),
        ];
    }

    /** งานที่ยังไม่มีนักศึกษาเลย ต้องได้ 0 ไม่ใช่หารด้วยศูนย์ */
    private function percent(int $part, int $whole): int
    {
        return $whole > 0 ? (int) round($part * 100 / $whole) : 0;
    }
}
