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
    $this->apiUrl = rtrim(config('services.api_url'), '/');
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

    // 3. หา Classroom ของงานนี้ เพื่อดูว่ามีนักเรียนคนไหนบ้าง
    $classroomId = $currentAssignment['classroom'] ?? null;
    if (is_array($classroomId)) {
        $classroomId = $classroomId['$oid'] ?? null;
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

    // 6. 🔥 เลือกหน้า View (Admin ไปหน้า admin / Teacher ไปหน้า teacher)
    $viewPath = (session('user_role') === 'ADMIN') ? 'admin.assignment-documents' : 'teacher.assignment-view';

    return view($viewPath, compact(
        'documents',
        'submitted',
        'notSubmitted',
        'totalStudents',
        'assignmentName'
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
        $apiError = $response->json()['message'] ?? $response->body() ?? 'Unknown Error';
        return back()->withErrors("ลบไม่สำเร็จ ({$response->status()}): {$apiError}");

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
            return back()->withErrors($response->body());
        }

        return back()->with('success', 'อัปโหลดสำเร็จ');

    } catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
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
        ->get($this->apiUrl . "/files/download/{$cleanId}");

        if ($response->successful()) {
            return response($response->body(), 200, [
                'Content-Type' => $response->header('Content-Type') ?? 'application/octet-stream',
                'Content-Disposition' => $response->header('Content-Disposition') ?? "attachment; filename=\"file-{$cleanId}\"",
            ]);
        }

        return back()->withErrors(['api_error' => 'API ปฏิเสธการดาวน์โหลด (401/404): ' . $response->body()]);

    } catch (\Exception $e) {
        return back()->withErrors(['api_error' => 'การเชื่อมต่อล้มเหลว: ' . $e->getMessage()]);
    }
}
}