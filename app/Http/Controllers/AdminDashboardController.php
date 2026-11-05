<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * ภาพรวมทั้งระบบสำหรับ admin
 *
 * อาจารย์ขอให้เห็นทั้งระบบและแยกต่อสาขา ว่าสาขานี้มีงานกี่งาน ครูกี่คน นักเรียนกี่คน
 *
 * ของเดิมนับแค่จำนวนนักศึกษากับครู ส่วนช่องเอกสารและงานฝึกเขียนเลขศูนย์
 * ตายตัวไว้ในโค้ด และมีป้าย +12% กับ +4% ที่ไม่ได้มาจากข้อมูลจริงเลย
 */
class AdminDashboardController extends Controller
{
    private string $apiUrl;

    /** เรียงตามที่นักศึกษาเห็นในหน้าสมัคร */
    private const CATEGORIES = [
        'SOFTWARE' => 'ซอฟต์แวร์ / พัฒนาโปรแกรม',
        'NETWORK'  => 'เครือข่ายและระบบ',
        'DATA'     => 'ข้อมูลและวิเคราะห์',
        'HARDWARE' => 'ฮาร์ดแวร์และอิเล็กทรอนิกส์',
        'DESIGN'   => 'ออกแบบ',
        'BUSINESS' => 'ธุรกิจและบริการ',
        'OTHER'    => 'อื่น ๆ',
    ];

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $empty = [
            'stats' => ['students' => 0, 'teachers' => 0, 'assignments' => 0, 'submissions' => 0],
            'byCategory' => collect(),
        ];

        try {
            $client = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120);

            $userRes = $client->get($this->apiUrl . '/user');

            if (!$userRes->successful()) {
                return view('admin.dashboard', $empty)
                    ->withErrors(['api' => 'ดึงข้อมูลภาพรวมไม่สำเร็จ']);
            }

            $users = collect($userRes->json() ?? []);

            $assignRes = $client->get($this->apiUrl . '/assignment');
            $fileRes = $client->get($this->apiUrl . '/files');

            $assignments = collect($assignRes->successful() ? ($assignRes->json() ?? []) : []);
            $files = collect($fileRes->successful() ? ($fileRes->json()['files'] ?? $fileRes->json() ?? []) : []);

            $students = $users->where('role', 'USER');
            $teachers = $users->where('role', 'TEACHER');

            return view('admin.dashboard', [
                'stats' => [
                    'students' => $students->count(),
                    'teachers' => $teachers->count(),
                    'assignments' => $assignments->count(),
                    // นับหัวงานที่ส่ง ไม่ใช่จำนวนไฟล์ ส่งสามไฟล์ในงานเดียวนับเป็นหนึ่ง
                    'submissions' => $files
                        ->map(fn($f) => ($f['assignment_id'] ?? '') . '|' . ($f['uploaded_by_id'] ?? ''))
                        ->unique()
                        ->count(),
                ],
                'byCategory' => $this->breakdown($students, $teachers, $assignments),
            ]);
        } catch (ConnectionException $e) {
            return view('admin.dashboard', $empty)
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }

    /**
     * แยกตัวเลขตามสาขา
     *
     * งานผูกกับห้องเรียน ไม่ได้ผูกกับสาขาโดยตรง
     * จึงนับว่างานหนึ่งชิ้นอยู่ในสาขาใดก็ตามที่มีนักศึกษาของสาขานั้นอยู่ในงาน
     * งานที่มีนักศึกษาหลายสาขาจะถูกนับในทุกสาขาที่เกี่ยวข้อง
     */
    private function breakdown(Collection $students, Collection $teachers, Collection $assignments): Collection
    {
        $categoryOfStudent = $students->mapWithKeys(function ($u) {
            $id = is_array($u['_id'] ?? null) ? ($u['_id']['$oid'] ?? null) : ($u['_id'] ?? null);
            return [(string)$id => $u['category'] ?? null];
        });

        return collect(self::CATEGORIES)->map(function ($label, $key) use ($students, $teachers, $assignments, $categoryOfStudent) {
            $assignmentCount = $assignments->filter(function ($a) use ($key, $categoryOfStudent) {
                return collect($a['students'] ?? [])->contains(function ($row) use ($key, $categoryOfStudent) {
                    $sid = $row['student_id'] ?? null;
                    $sid = is_array($sid) ? ($sid['$oid'] ?? null) : $sid;
                    return ($categoryOfStudent[(string)$sid] ?? null) === $key;
                });
            })->count();

            $studentCount = $students->where('category', $key)->count();
            $teacherCount = $teachers->where('category', $key)->count();

            return (object)[
                'key' => $key,
                'label' => $label,
                'students' => $studentCount,
                'teachers' => $teacherCount,
                'assignments' => $assignmentCount,
                // นักศึกษาในสาขาที่ไม่มีครูเลย คือคนที่ไม่มีใครนิเทศ
                'uncovered' => $teacherCount === 0 && $studentCount > 0,
            ];
        })->values();
    }
}
