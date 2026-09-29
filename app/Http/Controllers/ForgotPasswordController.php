<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ForgotPasswordController extends Controller
{
    /** -------------------------------------------------
     * STEP 1 — ส่ง OTP ไปอีเมล
     * ------------------------------------------------- */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $apiUrl = config('services.api_url') . "/auth/forgot-password";

        $response = Http::post($apiUrl, [
            'email' => $request->email
        ]);

        if ($response->successful()) {

            // เก็บ email ไว้แสดงช่อง OTP ต่อทันที
            return back()
                ->with('success', 'ส่งรหัส OTP ไปยังอีเมลของคุณแล้ว')
                ->with('otp_stage', true)
                ->with('email', $request->email);
        }

        return back()->withErrors([
            'email' => 'ไม่พบอีเมลในระบบ'
        ]);
    }




    /** -------------------------------------------------
     * STEP 2 — ยืนยัน OTP แล้วไปหน้า Reset Password
     * ------------------------------------------------- */
   
    public function verifyOtp(Request $request)
{
    // 1. ตรวจสอบเบื้องต้นว่ากรอกข้อมูลมาครบไหม
    $request->validate([
        'otp'   => 'required',
        'email' => 'required|email'
    ]);

    // 2. เตรียม URL สำหรับตรวจสอบ OTP (เช็คกับ API หลังบ้านของคุณ)
    // สมมติว่า API ของคุณมี Endpoint สำหรับเช็ค OTP เช่น /auth/verify-otp
    $apiUrl = config('services.api_url') . "/auth/verify-otp";

    // 3. ยิงข้อมูลไปถาม API ว่า OTP นี้ใช้ได้กับ Email นี้จริงไหม
    $response = Http::withoutVerifying()->post($apiUrl, [
        'email' => $request->email,
        'otp'   => $request->otp
    ]);

    // 4. ตรวจสอบคำตอบจาก API
    if ($response->successful()) {
        // ✅ ถ้า API ตอบว่าผ่าน (Success) ถึงจะเก็บลง Session และให้ไปต่อ
        session([
            'verified_email' => $request->email,
            'verified_otp'   => $request->otp
        ]);

        return redirect('/reset-password');
    }

    // ❌ ถ้า API ตอบว่าไม่ผ่าน (เช่น OTP ผิด หรือหมดอายุ) ให้เด้งกลับไปหน้าเดิมพร้อมแจ้งเตือน
    return back()
        ->with('otp_stage', true) // ให้ยังค้างอยู่ที่หน้ากรอก OTP เหมือนเดิม
        ->with('email', $request->email) // ส่ง email กลับไปใส่ hidden input เหมือนเดิม
        ->withErrors([
            'otp' => 'รหัส OTP ไม่ถูกต้องหรือหมดอายุแล้ว กรุณาลองใหม่อีกครั้ง'
        ]);

}

}
