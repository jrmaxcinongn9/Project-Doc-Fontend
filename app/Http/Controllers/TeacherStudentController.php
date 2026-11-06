<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * รายชื่อนักศึกษาที่ครูคนนี้ดูแล
 *
 * เดิมหน้านี้เป็นข้อมูลปลอมที่เขียนไว้ในไฟล์ view ตรง ๆ
 * ครูทุกคนเห็นชุดเดียวกันหมด และไม่ตรงกับฐานข้อมูลเลย
 * ตอนนี้ดึงจาก API ที่จับคู่ด้วย category ของครูคนที่ login
 */
class TeacherStudentController extends Controller
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

        $empty = ['students' => collect(), 'category' => null, 'notice' => null];

        try {
            // Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกจึงต้องรอให้ตื่น
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->get($this->apiUrl . '/teacher/my-students');

            if (!$response->successful()) {
                return view('teacher.students', $empty)
                    ->withErrors(['api' => 'ดึงรายชื่อนักศึกษาไม่สำเร็จ']);
            }

            $body = $response->json();

            return view('teacher.students', [
                'students' => collect($body['students'] ?? [])->map(function ($s) {
                    $s['id'] = $s['_id'] ?? ($s['id'] ?? null);
                    return $s;
                })->values(),
                'category' => $body['category'] ?? null,
                // API บอกเองเมื่อบัญชีครูยังไม่ได้ตั้งสาขา
                // ต้องแยกให้ออกจากกรณีที่ตั้งแล้วแต่ยังไม่มีนักศึกษา
                'notice' => $body['message'] ?? null,
            ]);
        } catch (ConnectionException $e) {
            return view('teacher.students', $empty)
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }

    /**
     * รายละเอียดนักศึกษาหนึ่งคน: ห้องที่อยู่ งานทุกชิ้น สถานะ ไฟล์ที่ส่ง และคะแนน
     *
     * เดิมเป็นหน้า mock ที่เขียนไว้ใน view ตรง ๆ ปุ่มดูรายละเอียดของทุกคนพามาที่เดียวกัน
     * และเห็นชื่อ "ทดสอบ นักศึกษา" กับงาน Week 1-3 ปี 2025 เหมือนกันหมด
     */
    public function show(string $id)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $get = fn (string $path) => Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->get($this->apiUrl . $path);

            $users = collect($get('/user')->json() ?? []);
            $rooms = collect($get('/classroom')->json() ?? []);
            $assigns = collect($get('/assignment')->json() ?? []);
            $filesRes = $get('/files')->json() ?? [];
        } catch (ConnectionException $e) {
            return redirect('/teacher/students')
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }

        $student = $users->first(fn ($u) => self::idOf($u['_id'] ?? null) === $id && ($u['role'] ?? '') === 'USER');
        abort_unless($student, 404);

        $teacherNames = $users->mapWithKeys(fn ($u) => [self::idOf($u['_id'] ?? null) => $u['name'] ?? '-']);

        // ห้องที่นักศึกษาคนนี้อยู่
        $myRooms = $rooms->filter(fn ($r) => collect($r['students'] ?? [])
            ->map(fn ($s) => self::idOf($s))
            ->contains($id))
            ->values();

        $files = collect($filesRes['files'] ?? $filesRes)
            ->filter(fn ($f) => (string) ($f['uploaded_by_id'] ?? '') === $id)
            ->values();

        $now = Carbon::now();

        $groups = $myRooms->map(function ($room) use ($assigns, $files, $id, $now, $teacherNames) {
            $roomId = self::idOf($room['_id'] ?? null);

            $rows = $assigns
                ->filter(fn ($a) => self::idOf($a['classroom'] ?? null) === $roomId)
                ->map(function ($a) use ($files, $id, $now) {
                    $aid = self::idOf($a['_id'] ?? null);
                    $due = !empty($a['due_date']) ? Carbon::parse($a['due_date']) : null;

                    $mine = $files
                        ->filter(fn ($f) => (string) ($f['assignment_id'] ?? '') === $aid)
                        ->sortBy(fn ($f) => $f['createdAt'] ?? '')
                        ->values();

                    $sentAt = $mine->isNotEmpty() && !empty($mine->last()['createdAt'])
                        ? Carbon::parse($mine->last()['createdAt'])
                        : null;

                    $status = match (true) {
                        $mine->isNotEmpty() && $due && $sentAt && $sentAt->greaterThan($due) => 'late',
                        $mine->isNotEmpty() => 'submitted',
                        $due && $now->greaterThan($due) => 'overdue',
                        default => 'pending',
                    };

                    $row = collect($a['students'] ?? [])->first(fn ($s) => self::idOf($s['student_id'] ?? null) === $id);

                    return (object) [
                        'id'       => $aid,
                        'title'    => $a['assignment_name'] ?? 'ไม่มีชื่องาน',
                        'due'      => $due,
                        'sent_at'  => $sentAt,
                        'status'   => $status,
                        'score'    => $row['score'] ?? null,
                        'max'      => $a['max_score'] ?? null,
                        'feedback' => $row['feedback'] ?? null,
                        'files'    => $mine->map(fn ($f) => (object) [
                            'id'          => self::idOf($f['_id'] ?? null),
                            'name'        => $f['original_name'] ?? $f['file_name'] ?? 'ไฟล์',
                            'previewable' => DocumentController::isPreviewable($f['mimetype'] ?? null),
                        ]),
                    ];
                })
                // งานที่ใกล้กำหนดส่งอยู่บน งานที่ไม่มีกำหนดส่งอยู่ท้าย
                ->sortBy(fn ($r) => $r->due ? $r->due->timestamp : PHP_INT_MAX)
                ->values();

            return (object) [
                'id'      => $roomId,
                'name'    => $room['name'] ?? 'ไม่มีชื่อห้องเรียน',
                'major'   => $room['major'] ?? '-',
                'teacher' => $teacherNames[self::idOf($room['createdBy'] ?? null)] ?? '-',
                'rows'    => $rows,
            ];
        });

        $all = $groups->flatMap(fn ($g) => $g->rows);
        $graded = $all->filter(fn ($r) => $r->score !== null && $r->max);

        $stats = (object) [
            'total'     => $all->count(),
            'submitted' => $all->whereIn('status', ['submitted', 'late'])->count(),
            'late'      => $all->where('status', 'late')->count(),
            'pending'   => $all->where('status', 'pending')->count(),
            'overdue'   => $all->where('status', 'overdue')->count(),
            'score'     => $graded->sum('score'),
            'max'       => $graded->sum('max'),
        ];

        return view('teacher.student-detail', compact('student', 'groups', 'stats'));
    }

    // id จาก API มาได้หลายแบบ: ข้อความ, {"$oid": ...} หรือ object ที่ populate แล้ว
    private static function idOf($v): ?string
    {
        if (is_array($v)) {
            $v = $v['_id'] ?? ($v['$oid'] ?? null);
            if (is_array($v)) {
                $v = $v['$oid'] ?? null;
            }
        }

        return $v === null ? null : (string) $v;
    }
}
