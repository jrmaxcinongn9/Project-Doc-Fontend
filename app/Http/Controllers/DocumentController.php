<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_url') ?? 'http://localhost:3000';
    }

    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('Session หมดอายุ');
        }

        try {
            // 🔥 ดึงข้อมูลทั้งหมด
            $fileResponse = Http::withToken($token)->timeout(10)->get($this->apiUrl . '/files');
            $assignResponse = Http::withToken($token)->timeout(10)->get($this->apiUrl . '/assignment');
            $classResponse = Http::withToken($token)->timeout(10)->get($this->apiUrl . '/classroom');
            $userResponse = Http::withToken($token)->timeout(10)->get($this->apiUrl . '/user');

            if (!$fileResponse->successful()) {
                return back()->withErrors('โหลดไฟล์ไม่สำเร็จ');
            }

            // 🔥 data
            $rawDocs = $fileResponse->json()['files'] ?? $fileResponse->json() ?? [];
            $assignments = collect($assignResponse->json() ?? []);
            $classrooms = collect($classResponse->json() ?? []);
            $users = collect($userResponse->json() ?? []);

            // 🔥 mapping
            $documents = collect($rawDocs)->map(function ($doc) use ($assignments, $classrooms, $users) {

                $docAssignId = $doc['assignment_id'] ?? null;

                // 🔹 assignment
                $assign = $assignments->first(function ($a) use ($docAssignId) {
                    $id = $a['_id']['$oid'] ?? $a['_id'] ?? null;
                    return $id == $docAssignId;
                });

                // 🔹 classroom
                $classroomId = $assign['classroom'] ?? null;

                $classroom = $classrooms->first(function ($c) use ($classroomId) {
                    $id = $c['_id']['$oid'] ?? $c['_id'] ?? null;
                    return $id == $classroomId;
                });

                // 🔹 teacher
                $teacherId = $classroom['createdBy'] ?? null;

                $teacher = $users->first(function ($u) use ($teacherId) {
                    $id = $u['_id']['$oid'] ?? $u['_id'] ?? null;
                    return $id == $teacherId;
                });

                return (object)[
    'id' => $doc['_id'] ?? null,

    'name' => $doc['original_name'] ?? 'ไม่มีชื่อ',
    'file' => $doc['file_name'] ?? '',
    'file_path' => $doc['file_path'] ?? null, // 🔥 เพิ่มตรงนี้

    'size' => $doc['file_size'] ?? 0,

    'user' => $doc['uploaded_by_name'] ?? '-',
    'email' => $doc['uploaded_by_email'] ?? '-',

    'date' => $doc['createdAt'] ?? null,

    'assignment_id' => $doc['assignment_id'] ?? null, // 🔥 เผื่อใช้ต่อ

    'assignment_name' => $assign['assignment_name'] ?? 'ไม่พบงาน',
    'classroom_name' => $classroom['name'] ?? 'ไม่พบคลาส',
    'teacher_name' => $teacher['name'] ?? 'ไม่พบอาจารย์',
];
            });

            return view('admin.documents', compact('documents'));

        } catch (\Exception $e) {

            Log::error('Document Controller Exception', [
                'message' => $e->getMessage()
            ]);

            return back()->withErrors('ระบบมีปัญหา: ' . $e->getMessage());
        }
    }
public function byAssignment($id)
{
    $token = session('api_token');

    // 1. ดึงข้อมูลทั้งหมดจาก API
    $fileResponse = Http::withToken($token)->get($this->apiUrl . '/files');
    $assignResponse = Http::withToken($token)->get($this->apiUrl . '/assignment');
    $classResponse = Http::withToken($token)->get($this->apiUrl . '/classroom');
    $userResponse = Http::withToken($token)->get($this->apiUrl . '/user');

    $rawDocs = collect($fileResponse->json()['files'] ?? $fileResponse->json() ?? []);
    $assignments = collect($assignResponse->json() ?? []);
    $classrooms = collect($classResponse->json() ?? []);
    $users = collect($userResponse->json() ?? []);

    // 2. หา Assignment ปัจจุบันที่กำลังดู
    $currentAssignment = $assignments->first(function ($a) use ($id) {
        $assignId = is_array($a['_id']) ? ($a['_id']['$oid'] ?? $a['_id']) : $a['_id'];
        return (string)$assignId === (string)$id;
    });

    // API คืนเฉพาะงานในห้องของครูคนนี้ ไม่เจอคือไม่ใช่งานของเขา หรือไม่มีงานนี้
    abort_unless($currentAssignment, 404);

    // 3. หา Classroom ของงานนี้ เพื่อดูว่ามีนักเรียนคนไหนบ้าง
    // GET /assignment populate classroom มาเป็น object ทั้งก้อน ไม่ใช่ id
    // เดิมอ่านแค่ $oid จึงหาห้องไม่เจอ รายชื่อในห้องว่าง
    // และตัวเลขทั้งหมดกี่คนไปนับนักศึกษาทุกคนในระบบแทน
    $classroomId = $currentAssignment['classroom'] ?? null;
    if (is_array($classroomId)) {
        $classroomId = $classroomId['_id'] ?? ($classroomId['$oid'] ?? null);
        if (is_array($classroomId)) {
            $classroomId = $classroomId['$oid'] ?? null;
        }
    }

    $targetClassroom = $classrooms->first(function ($c) use ($classroomId) {
        $cId = is_array($c['_id']) ? ($c['_id']['$oid'] ?? $c['_id']) : $c['_id'];
        return (string)$cId === (string)$classroomId;
    });

    // ดึง ID นักเรียนที่ลงทะเบียนในคลาสนี้ (ตรวจสอบชื่อ field 'students' ใน DB ของคุณด้วย)
    $studentIdsInClass = collect($targetClassroom['students'] ?? []);

    // 4. กรองและ Mapping ข้อมูลไฟล์ที่ส่งมา (Documents)
    $documents = $rawDocs
        ->filter(function ($doc) use ($id) {
            return trim((string)($doc['assignment_id'] ?? '')) === trim((string)$id);
        })
        ->map(function ($doc) use ($currentAssignment, $targetClassroom, $users) {
            // ดึงข้อมูลอาจารย์
            $teacherId = $targetClassroom['createdBy'] ?? null;
            $teacher = $users->first(function ($u) use ($teacherId) {
                $uId = is_array($u['_id']) ? ($u['_id']['$oid'] ?? $u['_id']) : $u['_id'];
                return (string)$uId === (string)$teacherId;
            });

            return (object)[
                'id' => $doc['_id'] ?? null,
                'name' => $doc['original_name'] ?? 'ไม่มีชื่อ',
                'file' => $doc['file_name'] ?? '',
                'file_path' => $doc['file_path'] ?? null,
                'mimetype' => $doc['mimetype'] ?? null,
                // เปิดดูในเบราว์เซอร์ได้ไหม ชนิดอื่นต้องดาวน์โหลด
                'previewable' => self::isPreviewable($doc['mimetype'] ?? null),
                'size' => $doc['file_size'] ?? 0,
                'user' => $doc['uploaded_by_name'] ?? '-',
                'email' => $doc['uploaded_by_email'] ?? '-',
                'date' => $doc['createdAt'] ?? null,
                'assignment_name' => $currentAssignment['assignment_name'] ?? 'ไม่พบงาน',
                'classroom_name' => $targetClassroom['name'] ?? 'ไม่พบคลาส',
                'teacher_name' => $teacher['name'] ?? 'ไม่พบอาจารย์',
            ];
        })
        ->values();

    // 5. 🔥 คำนวณจำนวนคน (นับเฉพาะนักเรียนที่มีชื่ออยู่ในห้องนี้)
    $classStudents = $users->filter(function ($u) use ($studentIdsInClass) {
        $uId = is_array($u['_id']) ? ($u['_id']['$oid'] ?? $u['_id']) : $u['_id'];
        return $studentIdsInClass->contains((string)$uId);
    });

    // หากใน Classroom ไม่มีรายชื่อนักเรียนเลย (students เป็นค่าว่าง) ให้กลับไปนับแบบเดิมเพื่อป้องกันตัวเลขเป็น 0
    if ($classStudents->isEmpty()) {
        $classStudents = $users->where('role', 'USER');
    }

    $totalStudents = $classStudents->count(); // จำนวนนักเรียนในห้องจริง
    $submittedEmails = $documents->pluck('email')->unique();
    $submitted = $submittedEmails->count();
    $notSubmitted = $totalStudents - $submitted;

    $assignmentName = $currentAssignment['assignment_name'] ?? 'ไม่พบงาน';

    // 🔥 แถวให้คะแนน หนึ่งแถวต่อนักศึกษาหนึ่งคนในงานชิ้นนี้
    //
    // ใช้รายชื่อจาก assignment ไม่ใช่จากไฟล์ที่ส่ง เพราะครูต้องให้คะแนน
    // คนที่ยังไม่ส่งได้ด้วย และต้องรู้ student_id เพื่อยิงไปที่ API
    $assignmentId = $id;
    $maxScore = $currentAssignment['max_score'] ?? null;

    $gradeRows = collect($currentAssignment['students'] ?? [])->map(function ($row) use ($users, $documents) {
        $sid = $row['student_id'] ?? null;
        $sid = is_array($sid) ? ($sid['$oid'] ?? null) : $sid;

        $user = $users->first(function ($u) use ($sid) {
            $uId = is_array($u['_id']) ? ($u['_id']['$oid'] ?? $u['_id']) : $u['_id'];
            return (string)$uId === (string)$sid;
        });

        $email = $user['email'] ?? null;

        return (object)[
            'student_id'  => $sid,
            'name'        => $row['name'] ?? ($user['name'] ?? '-'),
            'email'       => $email ?? '-',
            'score'       => $row['score'] ?? null,
            'feedback'    => $row['feedback'] ?? null,
            'feedback_by' => $row['feedback_by'] ?? null,
            // ส่งงานแล้วหรือยัง ดูจากไฟล์ที่อัปโหลดมาในงานชิ้นนี้
            'submitted'   => $email ? $documents->contains('email', $email) : false,
            // ไฟล์ของคนนี้ ครูเปิดดูได้จากแถวให้คะแนนเลย ไม่ต้องเลื่อนขึ้นไปหาที่ตารางบน
            'files'       => $email ? $documents->where('email', $email)->values() : collect(),
        ];
    });

    // รายชื่อในงานถูกคัดลอกจากห้องครั้งเดียวตอนสร้างงาน
    // นักศึกษาที่เข้าห้องทีหลังจึงไม่มีแถว ครูมองไม่เห็นและให้คะแนนไม่ได้
    // เติมคนที่อยู่ในห้องแต่ยังไม่มีแถวเข้าไป API จะสร้างแถวให้ตอนบันทึก
    $idOf = function ($v) {
        if (is_array($v)) {
            $v = $v['_id'] ?? ($v['$oid'] ?? null);
            $v = is_array($v) ? ($v['$oid'] ?? null) : $v;
        }
        return $v === null ? null : (string) $v;
    };

    $existingIds = $gradeRows->pluck('student_id')->map(fn ($v) => (string) $v);

    $missingRows = $studentIdsInClass
        ->map($idOf)
        ->filter()
        ->unique()
        ->reject(fn ($sid) => $existingIds->contains($sid))
        ->map(function ($sid) use ($users, $documents, $idOf) {
            $user = $users->first(fn ($u) => $idOf($u['_id'] ?? null) === $sid);
            $email = $user['email'] ?? null;

            return (object)[
                'student_id'  => $sid,
                'name'        => $user['name'] ?? '-',
                'email'       => $email ?? '-',
                'score'       => null,
                'feedback'    => null,
                'feedback_by' => null,
                'submitted'   => $email ? $documents->contains('email', $email) : false,
                'files'       => $email ? $documents->where('email', $email)->values() : collect(),
            ];
        });

    $gradeRows = $gradeRows->concat($missingRows)->values();

    // 6. 🔥 เลือกหน้า View (Admin ไปหน้า admin / Teacher ไปหน้า teacher)
    // เดิมเลือก view ด้วย session('user_role') ซึ่งไม่มีใครตั้งค่า จึงได้หน้าของครูเสมอ
    // หน้าของครูมีครบทั้งให้คะแนน เปิดดูไฟล์ และบันทึกทั้งหมด ใช้หน้าเดียวกันทั้งครูและ admin
    $viewPath = 'teacher.assignment-view';

    return view($viewPath, compact(
        'documents',
        'submitted',
        'notSubmitted',
        'totalStudents',
        'assignmentName',
        'assignmentId',
        'maxScore',
        'gradeRows'
    ));
}
public function destroy($id)
{
    $token = session('api_token');

    if (!$token) {
        return redirect('/login/admin')->withErrors('Session หมดอายุ กรุณา Login ใหม่');
    }

    try {
        // ดึง Base URL จาก config ให้ชัวร์
        $baseUrl = rtrim(config('services.api_url'), '/');
        
        // ยิง API ลบ (ตรวจสอบ Path ให้ตรงกับ Node.js ของคุณ)
        $response = Http::withToken($token)
            ->delete($baseUrl . '/files/' . $id);

        if ($response->successful()) {
            return back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
        }

        // 🔍 ถ้าลบไม่สำเร็จ ให้ดึง Error จาก API มาโชว์
        return back()->withErrors($this->apiError($response, 'ลบไม่สำเร็จ'));

    } catch (\Exception $e) {
        return back()->withErrors('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $e->getMessage());
    }
}
public function upload(Request $request)
{
    $token = session('api_token');

    if (!$token) {
        return back()->withErrors('Session หมดอายุ');
    }

    if (!$request->hasFile('file')) {
        return back()->withErrors('ไม่มีไฟล์');
    }

    try {

        $file = $request->file('file');

        $response = Http::withToken($token)
            ->asMultipart()
            ->post($this->apiUrl . '/files/upload', [
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                ],
                [
                    'name' => 'assignmentId', // 🔥 แก้ตรงนี้
                    'contents' => (string)$request->assignment_id,
                ],
            ]);

        if (!$response->successful()) {
            return back()->withErrors($this->apiError($response, 'อัปโหลดไม่สำเร็จ'));
        }

        return back()->with('success', 'อัปโหลดสำเร็จ');

    } catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
    }
}
// ชนิดเดียวกับที่ API ยอมให้เปิดแบบ inline ไม่มีสคริปต์ฝังอยู่
private const PREVIEWABLE_TYPES = ['application/pdf', 'image/png', 'image/jpeg', 'image/gif', 'image/webp', 'text/plain'];

public static function isPreviewable(?string $mimetype): bool
{
    if (!$mimetype) return false;

    return in_array(strtolower(trim(explode(';', $mimetype)[0])), self::PREVIEWABLE_TYPES, true);
}

/**
 * ครูเปิดดูไฟล์ที่นักศึกษาส่งในแท็บใหม่ ไม่ต้องดาวน์โหลดก่อน
 * PDF รูปภาพ และข้อความเปิดในเบราว์เซอร์ได้ ชนิดอื่น API ส่งกลับมาเป็นดาวน์โหลด
 */
public function viewFile($id)
{
    $token = session('api_token');

    if (!$token) {
        return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
    }

    try {
        $response = Http::withToken($token)
            ->timeout(60)
            ->get($this->apiUrl . "/files/download/{$id}", ['inline' => 1]);

        if (!$response->successful()) {
            abort($response->status() === 404 ? 404 : 403, 'เปิดไฟล์ไม่ได้ ไฟล์อาจถูกลบไปแล้ว');
        }

        return response($response->body(), 200, [
            'Content-Type'           => $response->header('Content-Type') ?: 'application/octet-stream',
            'Content-Disposition'    => $response->header('Content-Disposition') ?: 'attachment',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    } catch (\Illuminate\Http\Client\ConnectionException $e) {
        abort(503, 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง');
    }
}

public function downloadFile($id)
{
    // ✅ ดึงให้ตรงกับที่เก็บใน AuthController
    $token = session('api_token'); 

    if (!$token) {
        return back()->withErrors(['api_error' => 'ไม่พบ Token ในเซสชัน กรุณาเข้าสู่ระบบใหม่']);
    }

    // ตรวจสอบ $id (เผื่อกรณี MongoDB)
    $cleanId = is_array($id) ? ($id['$oid'] ?? $id[0]) : $id;

    try {
        $response = Http::withToken($token)
            ->timeout(60)
            // เดิมเขียน http://localhost:3000 ไว้ตายตัว บน Vercel จึงดาวน์โหลดไม่ได้เลย
            ->get($this->apiUrl . "/files/download/{$cleanId}");

        if ($response->successful()) {
            return response($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                'Content-Disposition' => $response->header('Content-Disposition') ?? "attachment; filename=\"file-{$cleanId}\"",
            ]);
        }

        return back()->withErrors(['api_error' => $this->apiError($response, 'ดาวน์โหลดไม่สำเร็จ')]);

    } catch (\Exception $e) {
        return back()->withErrors(['api_error' => 'การเชื่อมต่อล้มเหลว: ' . $e->getMessage()]);
    }
}
}