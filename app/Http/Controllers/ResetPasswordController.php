<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
        // 🟦 Validate password input
        $request->validate([
            'password' => 'required|min:6'
        ]);

        // 🟦 Get verified data from session
        $email = session('verified_email');
        $otp   = session('verified_otp');

        // 🟦 Session missing -> forced restart
        if (!$email || !$otp) {
            return redirect('/forgot-password')->withErrors([
                'otp' => 'Session expired. Please request OTP again.'
            ]);
        }

        // 🟦 API URL from env
        $apiUrl = config('services.api_url') . "/auth/reset-password";

        // 🟦 POST request
        $response = Http::withoutVerifying()->post($apiUrl, [
            'email'    => $email,
            'otp'      => $otp,
            'password' => $request->password
        ]);

        // 🟦 Success
        if ($response->successful()) {

            // Clear session data
            session()->forget(['verified_email', 'verified_otp']);

            return redirect('/login/student')->with('success', 'เปลี่ยนรหัสผ่านสำเร็จ กรุณาล็อกอิน');
        }

        // 🟦 Error (API มีข้อความตอบกลับ)
        $body = $response->json();

        return back()->withErrors([
            'error' => $body['message'] ?? 'ไม่สามารถรีเซ็ตรหัสผ่านได้'
        ]);
    }
}
