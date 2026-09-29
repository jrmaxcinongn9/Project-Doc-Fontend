<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AdminUserController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.api_url');
    }

    /**
     * แสดงหน้า Dashboard หรือ หน้าจัดการผู้ใช้
     */
    public function index(Request $request)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login')->with('error', 'กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            // ดึงข้อมูล User ทั้งหมดจาก API
            $response = Http::withToken($token)->get($this->apiUrl . '/user');

            if ($response->successful()) {
                $allUsers = collect($response->json());

                // --- 1. สำหรับหน้า Dashboard ---
                if ($request->is('admin/dashboard')) {
                    $stats = [
                        'students'  => $allUsers->where('role', 'USER')->count(),
                        'teachers'  => $allUsers->where('role', 'TEACHER')->count(),
                        'documents' => 0, // รอเชื่อมต่อ API เอกสารในอนาคต
                        'works'     => 0, // รอเชื่อมต่อ API งานฝึกงานในอนาคต
                    ];
                    return view('admin.dashboard', compact('stats'));
                }

                // --- 2. สำหรับหน้า Manage Users ---
                $users = $allUsers->map(function ($u) {
                    $u['id'] = $u['_id'] ?? ($u['id'] ?? null);
                    return $u;
                })->filter(function ($u) {
                    // กรองเฉพาะบทบาทที่ต้องการแสดงในตาราง
                    return in_array($u['role'], ['USER', 'TEACHER', 'ADMIN']);
                });

                // ค้นหาข้อมูล (Search)
                if ($request->has('search')) {
                    $search = $request->query('search');
                    $users = $users->filter(function($u) use ($search) {
                        return str_contains(strtolower($u['name'] ?? ''), strtolower($search)) || 
                               str_contains(strtolower($u['email'] ?? ''), strtolower($search)) ||
                               str_contains(strtolower($u['student_id'] ?? ''), strtolower($search));
                    });
                }

                return view('admin.manage-users', ['users' => $users]);
            }

            return back()->withErrors('ไม่สามารถดึงข้อมูลจาก API ได้');

        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage());
        }
    }

    /**
     * เพิ่มผู้ใช้ใหม่
     */
    public function store(Request $request)
    {
        $token = session('api_token');

        $request->validate([
            'name'        => 'required|string|max:255',
            'student_id'  => 'nullable|string|max:11',
            'email'       => 'required|email',
            'password'    => 'required|min:6',
            'role'        => 'required|in:USER,TEACHER',
        ]);

        try {
            // เช็คข้อมูลซ้ำก่อนยิง Register
            $checkUsers = Http::withToken($token)->get($this->apiUrl . '/user');
            if ($checkUsers->successful()) {
                $users = collect($checkUsers->json());

                if ($users->contains(fn($u) => strtolower($u['email']) === strtolower($request->email))) {
                    return back()->withErrors(['email' => '❌ อีเมลนี้ถูกใช้งานแล้ว'])->withInput();
                }

                if (!empty($request->student_id)) {
                    if ($users->contains(fn($u) => ($u['student_id'] ?? '') === $request->student_id)) {
                        return back()->withErrors(['student_id' => '❌ รหัสนักศึกษานี้ถูกใช้งานแล้ว'])->withInput();
                    }
                }
            }

            $payload = $request->only(['name','email','password','role','student_id','academicYear','major']);
            $payload = array_filter($payload, fn($v) => !is_null($v) && $v !== '');

            $response = Http::withToken($token)->acceptJson()->post($this->apiUrl . '/user/register', $payload);

            if ($response->successful()) {
                return back()->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
            }

            return back()->withErrors(['api' => $response->body()])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors('ระบบขัดข้อง: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * อัปเดตข้อมูลผู้ใช้
     */
    public function update(Request $request, $id)
    {
        $token = session('api_token');

        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email',
            'role'       => 'required|in:USER,TEACHER',
            'student_id' => 'nullable|string|max:11',
        ]);

        try {
            $payload = [
                'name'  => $request->name,
                'email' => $request->email,
                'role'  => $request->role,
            ];

            if (!empty($request->student_id)) $payload['student_id'] = $request->student_id;
            if (!empty($request->academicYear)) $payload['academicYear'] = $request->academicYear;
            if (!empty($request->major)) $payload['major'] = $request->major ?? null;

            $response = Http::withToken($token)->acceptJson()->patch($this->apiUrl . '/user/' . $id, $payload);

            if ($response->successful()) {
                return back()->with('success', 'อัปเดตสำเร็จ');
            }

            return back()->withErrors(['api' => $response->body()])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['system' => $e->getMessage()])->withInput();
        }
    }

    /**
     * ลบผู้ใช้
     */
    public function destroy($id)
    {
        $token = session('api_token');
        try {
            $response = Http::withToken($token)->delete($this->apiUrl . '/user/' . $id);
            if ($response->successful()) {
                return back()->with('success', 'ลบผู้ใช้สำเร็จ');
            }
            return back()->withErrors('ไม่สามารถลบข้อมูลได้');
        } catch (\Exception $e) {
            return back()->withErrors('เกิดข้อผิดพลาดในการลบ');
        }
    }
}