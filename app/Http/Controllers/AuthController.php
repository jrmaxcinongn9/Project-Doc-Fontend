<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'email'    => 'required|email',
        'password' => 'required|string',
        'role'     => 'nullable|string'
    ]);

    $role = $request->input('role', 'USER');
    $baseUrl = rtrim(config('services.api_url'), '/');

    $endpoint = match ($role) {
        'TEACHER' => '/auth/teacher-login',
        'ADMIN'   => '/auth/admin-login',
        default   => '/auth/login',
    };

    $apiUrl = $baseUrl . $endpoint;

    try {
        $response = Http::timeout(10)->post($apiUrl, [
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        /**
         * =========================
         * ✅ LOGIN SUCCESS
         * =========================
         */
        if ($response->successful()) {

            $data = $response->json();

            $user = array_merge([
                '_id' => null,
                'name' => '',
                'email' => '',
                'role' => $role,
                'major' => '',
                'academicYear' => '',
                'student_id' => null,
            ], $data['user'] ?? []);

            session([
                'api_token' => $data['access_token'] ?? null,
                'user'      => $user
            ]);

            return $this->redirectByRole($user['role']);
        }

        /**
         * ❌ LOGIN FAIL (API ตอบกลับมาแต่ไม่ผ่าน)
         */
        $message = $response->json()['message'] ?? 'Email หรือ Password ไม่ถูกต้อง';

        // ยังไม่ยืนยันอีเมลคือทางตันที่พบบ่อยที่สุด
        // ลิงก์ที่ได้ตอนสมัครโชว์รอบเดียว รีเฟรชแล้วหายถาวร
        // ตรงนี้มีทั้งอีเมลและรหัสผ่านอยู่ในมือแล้ว จึงขอลิงก์ใหม่ให้เลย
        // ผู้ใช้ไม่ต้องกรอกอะไรเพิ่ม และไม่ต้องรบกวนผู้ดูแลระบบ
        if ($response->status() === 403 && str_contains($message, 'ยืนยันอีเมล')) {
            $redirect = back()->withErrors(['email' => $message])->withInput();

            $link = $this->requestVerifyLink($baseUrl, $request->email, $request->password);

            return $link ? $redirect->with('verify_link', $link) : $redirect;
        }

        return back()->withErrors(['email' => $message])->withInput();

    } catch (\Exception $e) {

        /**
         * ❌ API ล่ม / เชื่อมไม่ได้
         */
        return back()->withErrors([
            'email' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้'
        ])->withInput();
    }
}


    /**
     * =========================
     * Redirect ตาม role
     * =========================
     */
    /**
     * เข้าสู่ระบบจากลิงก์ครั้งเดียวในเมล
     * ใช้ทั้งลิงก์ในเมลแจ้งอนุมัติ และหลังกดลิงก์ยืนยันอีเมลของบัญชีที่อนุมัติแล้ว
     */
    public function magic(Request $request)
    {
        return $this->loginWithToken((string) $request->query('token', ''));
    }

    public function loginWithToken(string $token)
    {
        if ($token === '') {
            return redirect('/login/student')->withErrors(['email' => 'ลิงก์เข้าสู่ระบบไม่ถูกต้อง']);
        }

        try {
            $response = Http::acceptJson()->connectTimeout(30)->timeout(60)
                ->post(rtrim(config('services.api_url'), '/') . '/auth/magic', ['token' => $token]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return redirect('/login/student')->withErrors(['email' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณากดลิงก์อีกครั้ง']);
        }

        if (!$response->successful()) {
            $msg = $response->json('message');
            return redirect('/login/student')->withErrors([
                'email' => is_string($msg) ? $msg : 'ลิงก์เข้าสู่ระบบใช้ไม่ได้ กรุณาเข้าสู่ระบบด้วยอีเมลและรหัสผ่าน',
            ]);
        }

        $data = $response->json();
        $user = array_merge([
            '_id' => null, 'name' => '', 'email' => '', 'role' => 'USER',
            'major' => '', 'academicYear' => '', 'student_id' => null,
        ], $data['user'] ?? []);

        // ล้าง session เก่าก่อน กันไม่ให้ค้างบัญชีคนอื่นที่เคย login ในเบราว์เซอร์นี้
        session()->invalidate();
        session()->regenerateToken();
        session(['api_token' => $data['access_token'] ?? null, 'user' => $user]);

        return $this->redirectByRole($user['role'])->with('success', 'เข้าสู่ระบบเรียบร้อย');
    }

    private function redirectByRole($role)
    {
        return match ($role) {
            'ADMIN'   => redirect('/admin/dashboard'),
            'TEACHER' => redirect('/teacher/classroom'),
            'USER'    => redirect('/student/dashboard'),
            default   => redirect('/student/dashboard'),
        };
    }


    /**
     * =========================
     * LOGOUT
     * =========================
     */
    public function logout()
    {
        session()->flush();
        return redirect('/login/student')->with('success', 'Logged out');
    }


    /**
     * =========================
     * UPDATE USER
     * =========================
     */
    public function update(Request $request, $id)
    {
        $token = session('api_token');

        $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email',
            'role'  => 'required|string',
        ]);

        try {
            $response = Http::withToken($token)
                ->put(config('services.api_url') . '/user/' . $id, [
                    'name'  => $request->name,
                    'email' => $request->email,
                    'role'  => $request->role,
                ]);

            if ($response->successful()) {
                return back()->with('success', 'อัปเดตสำเร็จ');
            }

            return back()->with('error',
                $response->json()['message'] ?? 'อัปเดตไม่สำเร็จ'
            )->withInput();

        } catch (\Exception $e) {
            return back()->with('error', 'API ล่ม')->withInput();
        }
    }

    /**
     * ขอลิงก์ยืนยันใหม่แทนผู้ใช้
     *
     * API ต้องการรหัสผ่านด้วย เพราะลิงก์ยืนยันคือกุญแจของบัญชีนั้น
     * ขอด้วยอีเมลอย่างเดียวจะกลายเป็นว่าใครก็ยืนยันแทนคนอื่นได้
     *
     * ล้มเหลวเมื่อไหร่ก็เงียบไป หน้า login ยังแสดงข้อความเดิมตามปกติ
     * ไม่ควรให้การขอลิงก์พังหน้า login ทั้งหน้า
     */
    private function requestVerifyLink(string $baseUrl, string $email, string $password): ?string
    {
        try {
            $response = Http::timeout(15)->post($baseUrl . '/user/resend-verify-mine', [
                'email'    => $email,
                'password' => $password,
            ]);

            return $response->successful() ? $response->json('verifyLink') : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
