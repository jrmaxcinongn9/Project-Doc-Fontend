<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class StudentController extends Controller
{
    private $apiUrl = 'http://localhost:3000';

    public function dashboard()
    {
        $user = session('user');
        $token = session('api_token');

        if (!$user || !$token) {
            return redirect('/login')->withErrors('กรุณาเข้าสู่ระบบ');
        }

        $studentMajor = trim($user['major'] ?? '');
        $studentYear = (string)($user['academicYear'] ?? '');
        
        $rawStudentId = $user['_id'] ?? $user['id'] ?? null;
        $studentId = is_array($rawStudentId) ? ($rawStudentId['$oid'] ?? '') : (string)$rawStudentId;
        $studentEmail = trim($user['email'] ?? ''); 

        try {
            $resClass  = Http::withToken($token)->get($this->apiUrl . '/classroom');
            $resUser   = Http::withToken($token)->get($this->apiUrl . '/user');
            $resAssign = Http::withToken($token)->get($this->apiUrl . '/assignment');
            $resFiles  = Http::withToken($token)->get($this->apiUrl . '/files'); 

            if (!$resClass->successful()) {
                return back()->withErrors('ไม่สามารถดึงข้อมูลห้องเรียนได้');
            }

            $allClassrooms = collect($resClass->json());
            $allUsers      = $resUser->successful() ? collect($resUser->json()) : collect([]);
            $allAssigns    = $resAssign->successful() ? collect($resAssign->json()) : collect([]);
            $allDocs       = $resFiles->successful() ? collect($resFiles->json()) : collect([]);

            $myClassRaw = $allClassrooms->first(function ($c) use ($studentMajor, $studentYear) {
                return trim($c['major'] ?? '') === $studentMajor &&
                       (string)($c['academicYear'] ?? '') === $studentYear;
            });

            if (!$myClassRaw) {
                return view('student.dashboard', [
                    'classroom' => null,
                    'assignments' => []
                ])->withErrors('ยังไม่มีห้องเรียนสำหรับสาขาและปีการศึกษาของคุณ');
            }

            $teacherId = $myClassRaw['createdBy'] ?? null;
            $teacher = $allUsers->first(function ($u) use ($teacherId) {
                $id = $u['_id']['$oid'] ?? $u['_id'] ?? null;
                return (string)$id === (string)$teacherId;
            });
            $teacherName = $teacher['name'] ?? 'SUTHANEE MALIPHAN';

            $classroomId = $myClassRaw['_id']['$oid'] ?? $myClassRaw['_id'] ?? null;
            $classroom = (object) [
                'id'           => $classroomId,
                'name'         => $myClassRaw['name'] ?? 'ไม่มีชื่อห้องเรียน',
                'major'        => $myClassRaw['major'] ?? '-',
                'academicYear' => $myClassRaw['academicYear'] ?? '-',
                'teacher_name' => $teacherName
            ];

            // 🌟 ปรับปรุง: การ Map งานกับไฟล์หลายไฟล์
            $mapAssignmentData = function ($assignRaw) use ($allDocs, $studentId, $studentEmail, $teacherName) {
                $targetIdRaw = $assignRaw['_id'] ?? null;
                $targetId = is_array($targetIdRaw) ? ($targetIdRaw['$oid'] ?? '') : (string)$targetIdRaw;

                // 🔍 ค้นหาไฟล์ *ทั้งหมด* ที่นักศึกษาส่งในงานนี้
                $myDocs = $allDocs->filter(function ($d) use ($targetId, $studentId, $studentEmail) {
                    $docAssignId  = (string)($d['assignment_id'] ?? '');
                    $docStudentId = (string)($d['uploaded_by_id'] ?? '');
                    $docEmail     = (string)($d['uploaded_by_email'] ?? $d['uploaded_by'] ?? '');

                    return $docAssignId === $targetId && 
                           ($docStudentId === $studentId || ($studentEmail !== '' && $docEmail === $studentEmail));
                })->values(); // Reset keys

                // 📦 จัดรูป Array ของไฟล์ที่เคยส่งไปแล้ว เพื่อส่งให้ View
                $submittedFiles = $myDocs->map(function($doc) {
                    $fId = $doc['_id']['$oid'] ?? $doc['_id'] ?? null;
                    return [
                        'id' => $fId,
                        'name' => $doc['original_name'] ?? $doc['file_name'] ?? 'ไม่มีชื่อไฟล์',
                        // กำหนด URL สำหรับปุ่มดาวน์โหลด (ต้องตั้งชื่อ Route นี้ใน web.php)
                        'download_url' => url('/student/files/download/' . $fId) 
                    ];
                })->toArray();

                $dueDateStr = $assignRaw['due_date'] ?? null;
                $publishDateStr = $assignRaw['publish_date'] ?? $assignRaw['created_at'] ?? null;
                $dueDate = $dueDateStr ? \Carbon\Carbon::parse($dueDateStr) : null;
                
                $status = 'Not Submitted';
                if ($myDocs->isNotEmpty()) {
                    // เช็คไฟล์ล่าสุดเพื่อหาสถานะ Late/On Time
                    $lastDoc = $myDocs->last();
                    $submittedAtStr = $lastDoc['createdAt'] ?? $lastDoc['created_at'] ?? null;
                    $submittedAt = $submittedAtStr ? \Carbon\Carbon::parse($submittedAtStr) : \Carbon\Carbon::now();
                    
                    if ($dueDate && $submittedAt->greaterThan($dueDate)) {
                        $status = 'Late Submitted';
                    } else {
                        $status = 'Submitted';
                    }
                }

                return [
                    'id'              => $targetId,
                    'title'           => $assignRaw['assignment_name'] ?? 'ไม่มีชื่องาน',
                    'teacher_name'    => $teacherName,
                    'publish_date'    => $publishDateStr,
                    'deadline'        => $dueDateStr,
                    'status'          => $status,
                    'submitted'       => $myDocs->isNotEmpty(),
                    'files'           => $submittedFiles, // ส่ง Array ของไฟล์ไป
                    'work_type'       => $assignRaw['work_type'] ?? 'Individual',
                    'attachment_count'=> $assignRaw['attachment_count'] ?? 1,
                ];
            };

            $assignIdsInClass = $myClassRaw['assignments'] ?? [];
            $assignments = collect($assignIdsInClass)
                ->map(function ($aId) use ($allAssigns, $mapAssignmentData) {
                    $idToFind = is_array($aId) ? ($aId['$oid'] ?? '') : (string)$aId;
                    $found = $allAssigns->first(function($a) use ($idToFind) {
                        $strId = is_array($a['_id']) ? ($a['_id']['$oid'] ?? '') : (string)$a['_id'];
                        return $strId === $idToFind;
                    });
                    return $found ? $mapAssignmentData($found) : null;
                })
                ->filter()->values()->toArray();

            if (empty($assignments) && $classroomId) {
                $assignments = $allAssigns
                    ->filter(function($a) use ($classroomId) {
                        $aClassId = is_array($a['classroom']) ? ($a['classroom']['$oid'] ?? '') : (string)$a['classroom'];
                        return $aClassId === (string)$classroomId;
                    })
                    ->map(fn($assign) => $mapAssignmentData($assign))
                    ->values()->toArray();
            }

            return view('student.dashboard', compact('classroom', 'assignments'));

        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage());
        }
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
                    return back()->withErrors('อัปโหลดไฟล์บางส่วนไม่สำเร็จ: ' . $response->body());
                }
            }

            sleep(1);
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