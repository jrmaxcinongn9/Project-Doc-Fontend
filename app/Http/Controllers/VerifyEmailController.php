<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyEmailController extends Controller
{
    // ผู้ใช้กดลิงก์จากในอีเมลแล้วมาถึงหน้านี้
    public function verify(Request $request)
    {
        $token = (string) $request->query('token', '');

        if ($token === '') {
            return view('auth.verify-email', [
                'ok'      => false,
                'message' => 'ลิงก์ยืนยันไม่ถูกต้อง ไม่พบรหัสยืนยันใน URL',
            ]);
        }

        $apiUrl = rtrim(config('services.api_url'), '/') . '/user/verify-email';

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->get($apiUrl, ['token' => $token]);

            if ($response->successful()) {
                return view('auth.verify-email', [
                    'ok'      => true,
                    'message' => $response->json()['message']
                        ?? 'ยืนยันอีเมลเรียบร้อย บัญชีอยู่ระหว่างรอผู้ดูแลระบบอนุมัติ',
                ]);
            }

            return view('auth.verify-email', [
                'ok'      => false,
                'message' => $response->json()['message'] ?? 'ยืนยันอีเมลไม่สำเร็จ',
            ]);
        } catch (\Exception $e) {
            return view('auth.verify-email', [
                'ok'      => false,
                'message' => 'ระบบขัดข้อง: ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ในขณะนี้',
            ]);
        }
    }
}
