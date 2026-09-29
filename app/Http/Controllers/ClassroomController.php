<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClassroomController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_url') ?? 'http://localhost:3000';
    }

    /**
     * 1. แสดงรายการห้องเรียนทั้งหมด (Index)
     */
    public function index()
    {
        $token = session('api_token');
        if (!$token) return redirect('/login/admin')->with('error', 'กรุณาเข้าสู่ระบบใหม่');

        try {
            $response = Http::withToken($token)->get($this->apiUrl . '/classroom');

            if ($response->successful()) {
                $classrooms = collect($response->json())->map(function ($c) {
                    
                    // 🔥 แก้ไขจุดนี้: ตรวจสอบโครงสร้าง ID ให้แม่นยำ
                    $rawId = $c['_id'] ?? null;
                    $cleanId = is_array($rawId) ? ($rawId['$oid'] ?? null) : $rawId;

                    return (object) [
                        'id'           => $cleanId, // ส่งค่า id ที่เป็น String ออกไป
                        'name'         => $c['name'] ?? 'ไม่ได้ระบุชื่อ',
                        'major'        => $c['major'] ?? '-',
                        'academicYear' => $c['academicYear'] ?? '-',
                        'level'        => $c['level'] ?? '-',
                        'studentCount' => isset($c['students']) ? count($c['students']) : 0,
                    ];
                });
                return view('admin.classroom', compact('classrooms'));
            }
            
            return view('admin.classroom', ['classrooms' => collect([])])
                ->withErrors('ดึงข้อมูลไม่สำเร็จ');
                
        } catch (\Exception $e) {
            return view('admin.classroom', ['classrooms' => collect([])])
                ->withErrors($e->getMessage());
        }
    }

    /**
     * 2. แสดงรายละเอียดห้องเรียน (Show) - ตัวชูโรง
     * ดึง Classroom + ดึง Assignment รายตัวมาโชว์ชื่อ
     */
    public function show($id)
    {
        $token = session('api_token');

        try {
            // ดึงข้อมูลห้องเรียน และ ข้อมูล User ทั้งหมดมาเพื่อ Match ชื่อนักศึกษา
            $resClass = Http::withToken($token)->get($this->apiUrl . '/classroom/' . $id);
            $resUser = Http::withToken($token)->get($this->apiUrl . '/user');
            
            // ดึง Assignment ทั้งหมดมาเพื่อ Match ชื่อ (เพราะในห้องเรียนมีแค่ ID)
            $resAssign = Http::withToken($token)->get($this->apiUrl . '/assignment');

            if ($resClass->successful()) {
                $classData = $resClass->json();
                $allUsers = collect($resUser->json() ?? []);
                $allAssigns = collect($resAssign->json() ?? []);

                // Mapping นักศึกษา (ID -> Full Info)
                $students = collect($classData['students'] ?? [])->map(function ($sId) use ($allUsers) {
                    $targetId = is_array($sId) ? ($sId['$oid'] ?? $sId['_id'] ?? null) : $sId;
                    $user = $allUsers->first(fn($u) => ($u['_id']['$oid'] ?? $u['_id']) == $targetId);

                    return (object) [
                        'id'         => $targetId,
                        'student_id' => $user['student_id'] ?? '-',
                        'name'       => $user['name'] ?? 'ไม่พบชื่อ (ID: '.substr($targetId,-4).')',
                    ];
                });

                // Mapping Assignments (ID -> Full Info)
              // Mapping Assignments (ID -> Full Info)
$assignments = collect($classData['assignments'] ?? [])
    ->map(function ($aId) use ($allAssigns) {
        $targetId = is_array($aId) 
            ? ($aId['$oid'] ?? $aId['_id'] ?? null) 
            : $aId;

        $assign = $allAssigns->first(
            fn($a) => ($a['_id']['$oid'] ?? $a['_id']) == $targetId
        );

        if (!$assign) return null;

        // 🔥 แก้ตรงนี้: ส่งกลับเป็น Object และใช้ Key ชื่อ 'id' ตรงๆ
        return (object) [
            'id'              => $targetId,
            'assignment_name' => $assign['assignment_name'] ?? 'ไม่มีชื่อชื่อ',
        ];
    })
    ->filter()
    ->values();

                $classroom = (object) [
                    'id'           => $id,
                    'name'         => $classData['name'] ?? '-',
                    'major'        => $classData['major'] ?? '-',
                    'academicYear' => $classData['academicYear'] ?? '-',
                    'students'     => $students,
                    'assignments'  => $assignments, 
                ];

                return view('admin.classroom-detail', compact('classroom'));
            }
            return back()->withErrors('ไม่พบข้อมูลห้องเรียน');
        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage());
        }
    }

    /**
     * 3. สร้างห้องเรียน (Store)
     */
    public function store(Request $request)
    {
        $token = session('api_token');
        $userId = session('user._id') ?? session('user.id');

        try {
            $response = Http::withToken($token)->post($this->apiUrl . '/classroom', [
                'name'         => (string) $request->name,
                'major'        => (string) $request->major,
                'academicYear' => (string) $request->academicYear,
                'level'        => (int) $request->level,
                
            ]);

            return $response->successful() 
                ? back()->with('success', 'สร้างห้องเรียนสำเร็จ') 
                : back()->withInput()->withErrors('สร้างไม่สำเร็จ: ' . $response->body());
        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    /**
     * 4. อัปเดตห้องเรียน (Update)
     */
    public function update(Request $request, $id)
    {
        $token = session('api_token');
        try {
            $response = Http::withToken($token)->patch($this->apiUrl . '/classroom/' . $id, [
                'name'         => (string) $request->name,
                'major'        => (string) $request->major,
                'academicYear' => (string) $request->academicYear,
                'level'        => (int) $request->level,
            ]);

            return $response->successful() 
                ? back()->with('success', 'อัปเดตสำเร็จ') 
                : back()->withErrors('อัปเดตไม่สำเร็จ');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * 5. เพิ่ม Assignment (StoreAssignment)
     */
    public function storeAssignment(Request $request)
    {
        $token = session('api_token');
        try {
            $response = Http::withToken($token)->post($this->apiUrl . '/assignment', [
                'assignment_name' => $request->assignment_name,
                'classroom'       => $request->classroom_id, // ส่ง ID ห้องเรียนไปผูก
            ]);

            if ($response->successful()) {
                return back()->with('success', 'เพิ่มงานสำเร็จ!');
            }
            return back()->withErrors('บันทึกงานไม่สำเร็จ');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
   public function updateAssignment(Request $request, $id)
{
    $token = session('api_token');

    try {
        $response = Http::withToken($token)->put(
            $this->apiUrl . '/assignment/' . $id,
            [
                'assignment_name' => $request->assignment_name,
            ]
        );

        // 🔥 AJAX
        if ($request->expectsJson()) {
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'แก้ไขสำเร็จ'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->body()
            ], 400);
        }

        // 🔥 FORM ปกติ
        if ($response->successful()) {
            return back()->with('success', 'แก้ไขงานสำเร็จ');
        }

        return back()->withErrors('แก้ไขไม่สำเร็จ: ' . $response->body());

    } catch (\Exception $e) {

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }

        return back()->withErrors($e->getMessage());
    }
}
/**
 * 7. ลบ Assignment (Delete)
 */
public function destroyAssignment($id)
{
    $token = session('api_token');

    try {
        $response = Http::withToken($token)
            ->delete($this->apiUrl . '/assignment/' . $id);

        if ($response->successful()) {
            return back()->with('success', 'ลบงานสำเร็จ');
        }

        return back()->withErrors('ลบไม่สำเร็จ: ' . $response->body());

    } catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
    }
}
public function showAssignment($id)
{
    $token = session('api_token');

    try {
        // ดึง assignment
        $resAssign = Http::withToken($token)->get($this->apiUrl . '/assignment/' . $id);

        // ดึง classroom (เพื่อรู้ว่านักเรียนมีใครบ้าง)
        $resClass = Http::withToken($token)->get($this->apiUrl . '/classroom');

        // ดึง users
        $resUser = Http::withToken($token)->get($this->apiUrl . '/user');

        if ($resAssign->successful()) {

            $assign = $resAssign->json();
            $allUsers = collect($resUser->json() ?? []);

            // สมมติ assignment มี classroom id
            $classroomId = $assign['classroom'] ?? null;

            $classroom = collect($resClass->json() ?? [])
                ->first(fn($c) => ($c['_id']['$oid'] ?? $c['_id']) == $classroomId);

            $students = collect($classroom['students'] ?? [])
                ->map(function ($sId) use ($allUsers, $assign) {

                    $targetId = is_array($sId) ? ($sId['$oid'] ?? $sId['_id']) : $sId;

                    $user = $allUsers->first(
                        fn($u) => ($u['_id']['$oid'] ?? $u['_id']) == $targetId
                    );

                    // 🔥 สมมติ assignment มี submissions
                    $submitted = collect($assign['submissions'] ?? [])
                        ->contains(fn($sub) => ($sub['student'] ?? null) == $targetId);

                    return (object) [
                        'name' => $user['name'] ?? 'ไม่พบชื่อ',
                        'student_id' => $user['student_id'] ?? '-',
                        'submitted' => $submitted
                    ];
                });

            return view('admin.assignment-detail', [
                'assignment' => $assign,
                'students' => $students
            ]);
        }

        return back()->withErrors('ไม่พบข้อมูล');

    } catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
    }
}
public function destroy($id)
{
    $token = session('api_token');

    try {
        $response = Http::withToken($token)
            ->delete($this->apiUrl . '/classroom/' . $id);

        if ($response->successful()) {
            return back()->with('success', 'ลบห้องเรียนสำเร็จ');
        }

        return back()->withErrors('ลบไม่สำเร็จ: ' . $response->body());

    } catch (\Exception $e) {
        return back()->withErrors($e->getMessage());
    }
}
}