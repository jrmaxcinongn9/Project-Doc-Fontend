<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TeacherClassroomController extends Controller
{
    protected $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_url') ?? env('API_URL', 'http://localhost:3000');
    }

    /**
     * Helper ฟังก์ชันสำหรับหา Prefix ของ View ตาม Role
     */
    private function getViewPrefix()
    {
        $role = session('user.role') ?? 'TEACHER'; 
        return strtolower($role) === 'admin' ? 'admin' : 'teacher';
    }

    function getUserIdFromToken($token)
    {
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);

        return $payload['sub'] ?? null;
    }

    /**
     * 1. แสดงรายการห้องเรียนทั้งหมด (Index)
     */
    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบใหม่');
        }

        $viewPrefix = $this->getViewPrefix();

        try {
            $response = Http::withToken($token)->get($this->apiUrl . '/classroom');

            if ($response->successful()) {
                $userId = $this->getUserIdFromToken($token);

                $classrooms = collect($response->json())
                ->filter(function ($c) use ($userId) {
                    $ownerId = is_array($c['createdBy'] ?? null)
                        ? ($c['createdBy']['$oid'] ?? null)
                        : ($c['createdBy'] ?? null);

                    return $ownerId == $userId;
                })
                ->map(function ($c) {
                    return (object) [
                        'id' => isset($c['_id'])
                            ? (is_array($c['_id']) ? ($c['_id']['$oid'] ?? null) : $c['_id'])
                            : null,
                        'name'         => $c['name'] ?? 'ไม่ได้ระบุชื่อ',
                        'major'        => $c['major'] ?? '-',
                        'academicYear' => $c['academicYear'] ?? '-',
                        'level'        => $c['level'] ?? '-',
                        'studentCount' => isset($c['students']) ? count($c['students']) : 0,
                    ];
                })
                ->values();

                return view("{$viewPrefix}.classroom", compact('classrooms'));
            }

            return view("{$viewPrefix}.classroom", ['classrooms' => collect([])])
                ->withErrors('ดึงข้อมูลไม่สำเร็จ');

        } catch (\Exception $e) {
            return view("{$viewPrefix}.classroom", ['classrooms' => collect([])])
                ->withErrors($e->getMessage());
        }
    }

    /**
     * 2. แสดงรายละเอียดห้องเรียน (Show) - 🇹🇭 แปลงเวลา UTC จากมอนโกกลับมาเป็นเวลาไทย (+7)
     */
    public function show($id)
    {
        $token = session('api_token');
        $viewPrefix = $this->getViewPrefix();

        try {
            $resClass = Http::withToken($token)->get($this->apiUrl . '/classroom/' . $id);

            // ครูเปิดได้เฉพาะห้องของตัวเอง API ตอบ 403 ถ้าเป็นห้องของครูคนอื่น
            abort_if(in_array($resClass->status(), [403, 404], true), 404);
            $resUser = Http::withToken($token)->get($this->apiUrl . '/user');
            
            $resAssign = Http::withToken($token)->get($this->apiUrl . '/assignment');
            if (!$resAssign->successful()) {
                $resAssign = Http::withToken($token)->get($this->apiUrl . '/doc');
            }

            if ($resClass->successful()) {
                $classData = $resClass->json();
                $allUsers = collect($resUser->json() ?? []);
                $allAssigns = collect($resAssign->json() ?? []);

                $students = collect($classData['students'] ?? [])->map(function ($sId) use ($allUsers) {
                    $targetId = is_array($sId) ? ($sId['$oid'] ?? $sId['_id'] ?? null) : $sId;
                    $user = $allUsers->first(fn($u) => ($u['_id']['$oid'] ?? $u['_id']) == $targetId);

                    return (object) [
                        'id'         => $targetId,
                        'student_id' => $user['student_id'] ?? '-',
                        'name'       => $user['name'] ?? 'ไม่พบชื่อ (ID: '.substr($targetId,-4).')',
                    ];
                });

                $assignments = collect($classData['assignments'] ?? [])->map(function ($aId) use ($allAssigns) {
                    $targetId = is_array($aId) ? ($aId['$oid'] ?? $aId['_id'] ?? null) : $aId;
                    $assign = $allAssigns->first(fn($a) => ($a['_id']['$oid'] ?? $a['_id']) == $targetId);

                    if (!$assign) return null;

                    $rawDueDate = $assign['due_date'] ?? $assign['dueDate'] ?? null;
                    if (is_array($rawDueDate) && isset($rawDueDate['$date'])) {
                        $rawDueDate = $rawDueDate['$date'];
                    }

                    $rawCreatedAt = $assign['created_at'] ?? $assign['createdAt'] ?? null;
                    if (is_array($rawCreatedAt) && isset($rawCreatedAt['$date'])) {
                        $rawCreatedAt = $rawCreatedAt['$date'];
                    }

                    // 🇹🇭 แปลงข้อมูลเวลาจากหลังบ้าน (UTC) มาเป็นรูปแบบภูมิภาคไทยให้แสดงผลตรงตาคุณครู
                    $createdAtFormatted = null;
                    if ($rawCreatedAt) {
                        $createdAtFormatted = \Carbon\Carbon::parse($rawCreatedAt)
                            ->timezone('Asia/Bangkok')
                            ->toIso8601String();
                    }

                    $dueDateFormatted = null;
                    if ($rawDueDate) {
                        $dueDateFormatted = \Carbon\Carbon::parse($rawDueDate)
                            ->timezone('Asia/Bangkok')
                            ->toIso8601String();
                    }

                    return [
                        'id'              => $targetId, 
                        'assignment_name' => $assign['assignment_name'] ?? $assign['assignmentName'] ?? '-',
                        'created_at'      => $createdAtFormatted, 
                        'due_date'        => $dueDateFormatted,   
                        // คะแนนเต็ม ต้องส่งมาด้วย ไม่งั้นฟอร์มแก้งานจะเติมค่าเดิมไม่ได้
                        // แล้วการแก้ชื่องานจะล้างคะแนนเต็มทิ้งโดยไม่ตั้งใจ
                        'max_score'       => $assign['max_score'] ?? null,
                    ];
                })->filter()->values();
                
                $classroom = (object) [
                    'id'           => $id,
                    'name'         => $classData['name'] ?? '-',
                    'major'        => $classData['major'] ?? '-',
                    'academicYear' => $classData['academicYear'] ?? '-',
                    'students'     => $students,
                    'assignments'  => $assignments, 
                ];

                return view("{$viewPrefix}.classroom-detail", compact('classroom'));
            }

            return back()->withErrors('ไม่พบข้อมูลห้องเรียน');

        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // ให้ 404 ของห้องคนอื่นออกไปถึงผู้ใช้ ไม่ถูกกลืนเป็น "ระบบขัดข้อง"
            throw $e;
        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $token = session('api_token');

        try {
            $response = Http::withToken($token)->post($this->apiUrl . '/classroom', [
                'name'         => (string) $request->name,
                'major'        => (string) $request->major,
                'academicYear' => (string) $request->academicYear,
                'level'        => (int) ($request->level ?? 3),
            ]);

            if (!$response->successful()) {
                dd($response->status(), $response->body());
            }

            return redirect('/teacher/classroom')->with('success', 'สร้างห้องเรียนสำเร็จ');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors($e->getMessage());
        }
    }

    /**
     * 📅 มอบหมายงานใหม่: 🇹🇭 แก้ไขให้แปลงเวลาไทยกลับเป็น UTC ก่อนส่งลงฐานข้อมูลมอนโก
     */
    public function storeAssignment(Request $request, $id)
    {
        $token = session('api_token');

        try {
            // อ่านค่าที่เลือกจากหน้าฟอร์ม (ซึ่งมองเป็น Asia/Bangkok) แล้วย้ายตรรกะกลับไปที่ UTC สากล
            $dueDateFormatted = $request->due_date 
                ? \Carbon\Carbon::parse($request->due_date, 'Asia/Bangkok')->setTimezone('UTC')->toIso8601String() 
                : null;

            $payload = [
                'assignment_name' => $request->assignment_name,
                'classroom'       => $id, 
                'due_date'        => $dueDateFormatted,
            ];

            // คะแนนเต็ม ครูกรอกตอนสร้างงาน
            // ถ้าไม่ส่งค่านี้ไป งานจะไม่มีคะแนนเต็ม และให้คะแนนรายคนไม่ได้เลย
            // ช่องว่างคืองานที่ไม่คิดคะแนน ซึ่งยังเขียนความเห็นได้
            if ($request->filled('max_score')) {
                $payload['max_score'] = (int) $request->max_score;
            }

            $response = Http::withToken($token)->post($this->apiUrl . '/assignment', $payload);
            if (!$response->successful() && $response->status() == 404) {
                $response = Http::withToken($token)->post($this->apiUrl . '/doc', $payload);
            }

            if (!$response->successful()) {
                dd($response->status(), $response->body());
            }

            return back()->with('success', 'เพิ่มงานสำเร็จ!');

        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * 📅 แก้ไขข้อมูลงาน: 🇹🇭 ปรับโครงสร้างแบบเดียวกัน เปลี่ยนเป็นเวลาสากลก่อนส่งอัปเดต
     */
    public function updateAssignment(Request $request, $id)
    {
        $token = session('api_token');

        try {
            // อ่านค่าที่อัปเดตใหม่แปลงกลับไปเป็น UTC ป้องกันปัญหาวันที่ผิดเพี้ยน
            $dueDateFormatted = $request->due_date 
                ? \Carbon\Carbon::parse($request->due_date, 'Asia/Bangkok')->setTimezone('UTC')->toIso8601String() 
                : null;

            // ส่งเฉพาะช่องที่ฟอร์มส่งมาจริง
            // เดิมส่ง assignment_name และ due_date เป็น null เสมอถ้าไม่ได้กรอก API จึงลบชื่องานและวันส่งทิ้ง
            $payload = [];
            if ($request->filled('assignment_name')) {
                $payload['assignment_name'] = $request->assignment_name;
            }
            if ($request->has('due_date')) {
                $payload['due_date'] = $dueDateFormatted;
            }

            // ตั้งคะแนนเต็มทีหลังได้ด้วย งานเก่าที่สร้างก่อนมีฟิลด์นี้จึงยังแก้ได้
            if ($request->filled('max_score')) {
                $payload['max_score'] = (int) $request->max_score;
            }

            $response = Http::withToken($token)->put($this->apiUrl . '/assignment/' . $id, $payload);
            if (!$response->successful() && $response->status() == 404) {
                $response = Http::withToken($token)->put($this->apiUrl . '/doc/' . $id, $payload);
            }

            if ($request->expectsJson()) {
                if ($response->successful()) {
                    return response()->json(['success' => true, 'message' => 'แก้ไขสำเร็จ']);
                }
                return response()->json(['success' => false, 'message' => $this->apiError($response, 'แก้ไขไม่สำเร็จ')], 400);
            }

            if ($response->successful()) {
                return back()->with('success', 'แก้ไขงานสำเร็จ');
            }

            return back()->withErrors($this->apiError($response, 'แก้ไขไม่สำเร็จ'));

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
            $response = Http::withToken($token)->delete($this->apiUrl . '/assignment/' . $id);
            if (!$response->successful() && $response->status() == 404) {
                $response = Http::withToken($token)->delete($this->apiUrl . '/doc/' . $id);
            }

            if ($response->successful()) {
                return back()->with('success', 'ลบงานสำเร็จ');
            }

            return back()->withErrors($this->apiError($response, 'ลบไม่สำเร็จ'));

        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * แสดงรายละเอียดของ Assignment และรายชื่อนักเรียนที่ส่งงาน
     */
    public function assignmentView($id)
    {
        $token = session('api_token');
        
        try {
            $response = Http::withToken($token)->get($this->apiUrl . '/assignment/' . $id);
            if (!$response->successful() && $response->status() == 404) {
                $response = Http::withToken($token)->get($this->apiUrl . '/doc/' . $id);
            }

            if ($response->successful()) {
                $assignment = $response->json();
                return view('teacher.assignment-view', compact('assignment'));
            }

            return back()->withErrors('ไม่พบข้อมูลงานชิ้นนี้');

        } catch (\Exception $e) {
            return back()->withErrors('เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * 8. ลบ Classroom (Delete)
     */
    public function destroy($id)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $response = Http::withToken($token)->delete($this->apiUrl . '/classroom/' . $id);

            if ($response->successful()) {
                return redirect('/teacher/classroom')->with('success', 'ลบห้องเรียนสำเร็จ');
            }

            return back()->withErrors($this->apiError($response, 'ลบไม่สำเร็จ'));

        } catch (\Exception $e) {
            return back()->withErrors('เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $token = session('api_token');
        
        if (!$token) return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบใหม่');

        try {
            $response = Http::withToken($token)->patch($this->apiUrl . '/classroom/' . $id, [
                'name'         => (string) $request->name,
                'major'        => (string) $request->major,
                'academicYear' => (string) $request->academicYear,
                'level'        => (int) $request->level,
            ]);

            if ($response->successful()) {
                return back()->with('success', 'อัปเดตข้อมูลห้องเรียนสำเร็จ');
            }

            return back()->withErrors($this->apiError($response, 'อัปเดตไม่สำเร็จ'));

        } catch (\Exception $e) {
            return back()->withErrors('เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
}