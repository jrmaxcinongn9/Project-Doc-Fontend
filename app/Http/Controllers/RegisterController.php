<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // 1. Validation: เพิ่มการตรวจสอบ student_id และปรับแต่งข้อความแจ้งเตือน
        $request->validate([
            'name'         => 'required|string|min:4|max:255', 
            'student_id'   => 'required|numeric|digits:11',   // เพิ่ม: ต้องเป็นตัวเลข 11 หลัก
            'email'        => 'required|email',              
            'password'     => 'required|string|min:6',        
            'academicYear' => 'required|digits:4',           
            'major'        => 'required|string',             
        ], [
            // ข้อความแจ้งเตือนภาษาไทย
            'name.min'              => 'ชื่อ-นามสกุล ต้องมีความยาวอย่างน้อย 4 ตัวอักษร',
            'student_id.required'   => 'กรุณากรอกรหัสนักศึกษา',
            'student_id.numeric'    => 'รหัสนักศึกษาต้องเป็นตัวเลขเท่านั้น',
            'student_id.digits'     => 'รหัสนักศึกษาต้องมีความยาว 11 หลัก',
            'academicYear.required' => 'กรุณาเลือกปีการศึกษา',
            'major.required'        => 'กรุณาเลือกสาขาวิชา',
            'password.min'          => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
        ]);

        /** * 🔥 API URL จาก Config
         */
        $apiUrl = config('services.api_url') . "/user/register";

        /**
         * 🚀 ส่งข้อมูลไป API
         */
        try {
            $response = Http::withoutVerifying()->post($apiUrl, [
                'name'         => $request->name,
                'student_id'   => $request->student_id, // เพิ่ม: ส่งรหัสนักศึกษาไปที่ API
                'email'        => $request->email,
                'password'     => $request->password,
                'academicYear' => (string) $request->academicYear, 
                'major'        => $request->major,
                'role'         => 'USER' 
            ]);

            /**
             * ✔ กรณีสมัครสำเร็จ
             */
            if ($response->successful()) {
                return redirect('/login/student')->with('success', 'สมัครสมาชิกสำเร็จแล้ว! กรุณาเข้าสู่ระบบ');
            }

            /**
             * ❌ กรณี API ปฏิเสธ (เช่น Email ซ้ำ หรือ Student ID ซ้ำ)
             */
            $body = $response->json();
            $errorMessage = $body['message'] ?? 'สมัครสมาชิกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง';
            
            return back()
                ->withErrors(['register' => $errorMessage])
                ->withInput();

        } catch (\Exception $e) {
            // กรณี Server API ล่ม
            return back()
                ->withErrors(['register' => 'ระบบขัดข้อง: ไม่สามารถเชื่อมต่อฐานข้อมูลได้ในขณะนี้'])
                ->withInput();
        }
    }
}