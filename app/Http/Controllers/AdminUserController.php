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

                // หน้า dashboard ย้ายไป AdminDashboardController แล้ว
                // กิ่งเดิมที่นี่คืนตัวเลขเอกสารเป็น 0 ตายตัว และไม่มีเส้นไหนเรียกถึงแล้ว

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
     *
     * เดิมกดบันทึกแล้วไม่เกิดอะไร เพราะ
     * - ฟอร์มไม่มีช่อง category แต่ API บังคับ จึงถูกปฏิเสธทุกครั้ง
     * - ส่ง academicYear ที่ API ไม่รับแล้ว ถูกตีกลับทั้งคำขอ
     * - ครูถูกส่งไปเส้นของนักศึกษา ซึ่งบังคับรหัสนักศึกษาและสาขาวิชา
     * - error จาก API เก็บไว้ใต้ชื่อ api ซึ่งหน้าเว็บไม่ได้แสดง
     *
     * error ทั้งหมดเก็บใน bag ชื่อ create เพื่อเปิดหน้าต่างเพิ่มผู้ใช้ค้างไว้พร้อมข้อความ
     */
    public function store(Request $request)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        // ตัวพิมพ์ใหญ่และช่องว่างทำให้กฎโดเมนอีเมลตกโดยไม่จำเป็น
        $request->merge([
            'email'      => strtolower(trim((string) $request->input('email'))),
            'student_id' => preg_replace('/\D/', '', (string) $request->input('student_id')),
        ]);

        $request->validateWithBag('create', [
            'name'       => 'required|string|min:4|max:255',
            'email'      => 'required|email|ends_with:@mail.kmutt.ac.th',
            'password'   => 'required|string|min:6',
            'role'       => 'required|in:USER,TEACHER',
            'category'   => 'required|in:' . implode(',', self::CATEGORIES),
            'student_id' => 'exclude_unless:role,USER|required|digits:11',
            'major'      => 'exclude_unless:role,USER|required|string|max:255',
        ], [
            'name.required'       => 'กรุณากรอกชื่อ',
            'name.min'            => 'ชื่อต้องยาวอย่างน้อย 4 ตัวอักษร',
            'email.required'      => 'กรุณากรอกอีเมล',
            'email.email'         => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.ends_with'     => 'อีเมลต้องลงท้ายด้วย @mail.kmutt.ac.th',
            'password.required'   => 'กรุณากำหนดรหัสผ่าน',
            'password.min'        => 'รหัสผ่านต้องยาวอย่างน้อย 6 ตัวอักษร',
            'category.required'   => 'กรุณาเลือกสาขาที่ใช้จับคู่',
            'category.in'         => 'สาขาที่ใช้จับคู่ไม่ถูกต้อง',
            'student_id.required' => 'นักศึกษาต้องมีรหัสนักศึกษา',
            'student_id.digits'   => 'รหัสนักศึกษาต้องเป็นตัวเลข 11 หลัก',
            'major.required'      => 'กรุณาเลือกสาขาวิชา',
        ]);

        $isTeacher = $request->input('role') === 'TEACHER';

        // ครูมีเส้นสร้างของตัวเองที่ admin เท่านั้นเรียกได้ ไม่ต้องมีรหัสนักศึกษา
        $endpoint = $isTeacher ? '/teacher/register' : '/user/register';
        $payload = $isTeacher
            ? $request->only(['name', 'email', 'password', 'category'])
            : $request->only(['name', 'email', 'password', 'role', 'category', 'student_id', 'major']);

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(60)
                ->post($this->apiUrl . $endpoint, $payload);

            if ($response->successful()) {
                return back()->with('success', 'เพิ่มผู้ใช้ ' . $request->input('name') . ' เรียบร้อย ใช้งานได้ทันที');
            }

            return back()->withErrors(['api' => $this->apiMessage($response->json())], 'create')->withInput();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง'], 'create')->withInput();
        }
    }

    private const CATEGORIES = ['SOFTWARE', 'NETWORK', 'DATA', 'HARDWARE', 'DESIGN', 'BUSINESS', 'OTHER'];

    /**
     * แปลงข้อความ error จาก API ให้ admin อ่านเข้าใจ
     * Nest ส่ง message มาเป็นข้อความเดียวหรือเป็นอาเรย์ก็ได้
     */
    private function apiMessage($json): string
    {
        $raw = $json['message'] ?? 'เพิ่มผู้ใช้ไม่สำเร็จ';
        $messages = is_array($raw) ? $raw : [$raw];

        $known = [
            'Email already exists'      => 'อีเมลนี้ถูกใช้งานแล้ว',
            'Student ID already exists' => 'รหัสนักศึกษานี้ถูกใช้งานแล้ว',
        ];

        return collect($messages)
            ->map(fn ($m) => $known[$m] ?? $m)
            ->implode(' / ');
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
            // ฟอร์มแก้ไขมีตัวเลือก ADMIN และมีบัญชี ADMIN ในตาราง เดิมบันทึกบัญชีเหล่านี้ไม่ได้เลย
            'role'       => 'required|in:USER,TEACHER,ADMIN',
            'student_id' => 'nullable|digits:11',
            'category'   => 'nullable|in:SOFTWARE,NETWORK,DATA,HARDWARE,DESIGN,BUSINESS,OTHER',
        ], [
            'student_id.digits' => 'รหัสนักศึกษาต้องเป็นตัวเลข 11 หลัก',
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

            // สาขาที่ใช้จับคู่ครูกับนักศึกษา เดิมค่านี้ถูกทิ้งไปทั้งก้อน
            // ครูจึงไม่มีสาขาและไม่เห็นนักศึกษาสักคน แก้จากหน้าเว็บก็ไม่ได้
            if (!empty($request->category)) $payload['category'] = $request->category;

            $response = Http::withToken($token)->acceptJson()->patch($this->apiUrl . '/user/' . $id, $payload);

            if ($response->successful()) {
                return back()->with('success', 'อัปเดตสำเร็จ');
            }

            return back()->withErrors(['api' => $this->apiError($response, 'บันทึกไม่สำเร็จ')])->withInput();

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