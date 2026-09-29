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
        return back()->withErrors([
            'email' => $response->json()['message'] ?? 'Email หรือ Password ไม่ถูกต้อง'
        ])->withInput();

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
}