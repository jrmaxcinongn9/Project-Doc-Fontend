<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StudentController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_url') ?? env('API_URL', 'https://project-doc-backend.onrender.com');
    }

    /**
     * รายการห้องเรียนของนักศึกษา แบบเดียวกับหน้าห้องเรียนของครู
     *
     * เดิมหน้านี้หาห้องจากสาขาวิชาและปีการศึกษาของนักศึกษา
     * แต่ปีการศึกษาถูกเอาออกจากบัญชีนักศึกษาแล้ว (ข้อ 4 ของอาจารย์)
     * จึงหาห้องไม่เจอ และรองรับได้ห้องเดียว
     * ตอนนี้ใช้รายชื่อนักศึกษาในห้องจริง ผ่าน GET /classroom/my
     */
    public function dashboard()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/student')->withErrors('กรุณาเข้าสู่ระบบ');
        }

        try {
            $rooms = $this->myClassrooms($token);
            $myFiles = $this->myFiles($token);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return view('student.classrooms', ['classrooms' => []])
                ->withErrors('เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารีเฟรชอีกครั้ง');
        }

        $submittedIds = $myFiles->pluck('assignment_id')->map(fn ($v) => (string) $v)->unique();

        $classrooms = $rooms->map(function ($r) use ($submittedIds) {
            $assignments = collect($r['assignments'] ?? []);
            $done = $assignments->filter(fn ($a) => $submittedIds->contains((string) $a['_id']))->count();

            return (object) [
                'id'           => (string) $r['_id'],
                'name'         => $r['name'] ?? 'ไม่มีชื่อห้องเรียน',
                'major'        => $r['major'] ?? '-',
                'academicYear' => $r['academicYear'] ?? '-',
                'teacher_name' => $r['teacher']['name'] ?? '-',
                'total'        => $assignments->count(),
                'submitted'    => $done,
                'pending'      => $assignments->count() - $done,
            ];
        })->values();

        return view('student.classrooms', compact('classrooms'));
    }

    /**
     * ในห้องเรียน: รายการงาน ส่งงาน เพิ่มไฟล์ และลบไฟล์ที่ตัวเองส่ง
     */
    public function classroom(string $id)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/student')->withErrors('กรุณาเข้าสู่ระบบ');
        }

        try {
            $rooms = $this->myClassrooms($token);
            $myFiles = $this->myFiles($token);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return redirect('/student/dashboard')->withErrors('เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง');
        }

        // เปิดได้เฉพาะห้องที่ตัวเองอยู่ ห้องอื่นถือว่าไม่มี
        $room = $rooms->first(fn ($r) => (string) $r['_id'] === $id);
        abort_unless($room, 404);

        $teacherName = $room['teacher']['name'] ?? '-';

        $classroom = (object) [
            'id'           => (string) $room['_id'],
            'name'         => $room['name'] ?? 'ไม่มีชื่อห้องเรียน',
            'major'        => $room['major'] ?? '-',
            'academicYear' => $room['academicYear'] ?? '-',
            'teacher_name' => $teacherName,
        ];

        $assignments = collect($room['assignments'] ?? [])
            ->map(function ($a) use ($myFiles, $teacherName) {
                $assignmentId = (string) $a['_id'];

                // ไฟล์จาก /files/my เป็นของนักศึกษาคนนี้เท่านั้นอยู่แล้ว
                $docs = $myFiles
                    ->filter(fn ($d) => (string) ($d['assignment_id'] ?? '') === $assignmentId)
                    ->sortBy(fn ($d) => $d['createdAt'] ?? '')
                    ->values();

                $due = !empty($a['due_date']) ? Carbon::parse($a['due_date']) : null;

                $status = 'Not Submitted';
                if ($docs->isNotEmpty()) {
                    $last = $docs->last();
                    $at = !empty($last['createdAt']) ? Carbon::parse($last['createdAt']) : Carbon::now();
                    $status = ($due && $at->greaterThan($due)) ? 'Late Submitted' : 'Submitted';
                }

                return [
                    'id'               => $assignmentId,
                    'title'            => $a['assignment_name'] ?? 'ไม่มีชื่องาน',
                    'teacher_name'     => $teacherName,
                    'publish_date'     => $a['publish_date'] ?? null,
                    'deadline'         => $a['due_date'] ?? null,
                    'status'           => $status,
                    'submitted'        => $docs->isNotEmpty(),
                    'files'            => $docs->map(fn ($d) => [
                        'id'           => (string) $d['_id'],
                        'name'         => $d['original_name'] ?? $d['file_name'] ?? 'ไม่มีชื่อไฟล์',
                        'download_url' => url('/student/files/download/' . $d['_id']),
                    ])->all(),
                    'work_type'        => 'Individual',
                    'attachment_count' => 1,
                ];
            })
            ->values()
            ->all();

        return view('student.dashboard', compact('classroom', 'assignments'));
    }

    // ห้องเรียนที่นักศึกษาอยู่ API คืนเฉพาะข้อมูลที่นักศึกษาควรเห็น
    private function myClassrooms(string $token)
    {
        $res = Http::withToken($token)->acceptJson()->timeout(60)->get($this->apiUrl . '/classroom/my');

        return $res->successful() ? collect($res->json()) : collect();
    }

    // ไฟล์ที่นักศึกษาคนนี้ส่ง เดิมดึงไฟล์ทั้งระบบมากรองเอง
    private function myFiles(string $token)
    {
        $res = Http::withToken($token)->acceptJson()->timeout(60)->get($this->apiUrl . '/files/my');

        return $res->successful() ? collect($res->json()) : collect();
    }

    /**
     * อัปโหลดไฟล์ส่งงาน (รองรับ Multiple Files)
     */
    public function submitAssignment(Request $request)
    {
        $token = session('api_token');

        if (!$token) return back()->withErrors('Session หมดอายุ กรุณาเข้าสู่ระบบใหม่');
        if (!$request->hasFile('files')) return back()->withErrors('กรุณาแนบไฟล์เอกสาร');
        if (empty($request->assignment_id)) return back()->withErrors('เกิดข้อผิดพลาด: ไม่พบรหัสงาน (Assignment ID)');

        try {
            $files = $request->file('files');
            
            // 🌟 วนลูปส่งไฟล์ทีละไฟล์ไปยัง Backend Node.js
            foreach($files as $file) {
                $response = Http::withToken($token)
                    ->asMultipart()
                    ->post($this->apiUrl . '/files/upload', [
                        [
                            'name' => 'file',
                            'contents' => fopen($file->getRealPath(), 'r'),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name' => 'assignmentId',
                            'contents' => (string)$request->assignment_id,
                        ],
                    ]);

                if (!$response->successful()) {
                    $msg = $response->json('message');
                    $msg = is_array($msg) ? implode(' / ', $msg) : ($msg ?: 'อัปโหลดไม่สำเร็จ');

                    return back()->withErrors('ส่งไฟล์ ' . $file->getClientOriginalName() . ' ไม่สำเร็จ: ' . $msg);
                }
            }

            return back()->with('success', 'อัปโหลดไฟล์งานสำเร็จ!');

        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage());
        }
    }

    /**
     * ลบไฟล์ (เรียกผ่าน AJAX)
     */
    public function deleteFile($id)
    {
        $token = session('api_token');
        if (!$token) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        try {
            // 🌟 ยิง API ลบไฟล์ไปที่ Node.js
            $response = Http::withToken($token)->delete($this->apiUrl . "/files/{$id}");

            if ($response->successful()) {
                return response()->json(['success' => true]);
            }
            return response()->json(['success' => false, 'message' => 'ลบไม่สำเร็จ'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ดาวน์โหลดไฟล์ (คงเดิม)
     */
    public function downloadFile($id)
    {
        $token = session('api_token'); 
        if (!$token) return back()->withErrors('ไม่พบ Token ในเซสชัน กรุณาเข้าสู่ระบบใหม่');

        $cleanId = is_array($id) ? ($id['$oid'] ?? $id[0]) : $id;

        try {
            $response = Http::withToken($token)
                ->timeout(60)
                ->get($this->apiUrl . "/files/download/{$cleanId}");

            if ($response->successful()) {
                return response($response->body(), 200, [
                    'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                    'Content-Disposition' => $response->header('Content-Disposition') ?? "attachment; filename=\"file-{$cleanId}\"",
                ]);
            }
            return back()->withErrors('ไม่สามารถดาวน์โหลดไฟล์ได้ (401/404)');
        } catch (\Exception $e) {
            return back()->withErrors('การเชื่อมต่อล้มเหลว: ' . $e->getMessage());
        }
    }
}