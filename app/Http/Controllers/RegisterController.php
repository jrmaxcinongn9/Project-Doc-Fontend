<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // อีเมลไม่สนใจตัวพิมพ์ใหญ่-เล็ก แต่ ends_with สนใจ จึงแปลงเป็นตัวพิมพ์เล็กก่อนตรวจ
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        // 1. Validation: เพิ่มการตรวจสอบ student_id และปรับแต่งข้อความแจ้งเตือน
        $request->validate([
            'name'         => 'required|string|min:4|max:255', 
            'student_id'   => 'required|numeric|digits:11',   // เพิ่ม: ต้องเป็นตัวเลข 11 หลัก
            'email'        => 'required|email|ends_with:@mail.kmutt.ac.th',
            'password'     => 'required|string|min:6|confirmed',
            'category'     => 'required|string|in:SOFTWARE,NETWORK,DATA,HARDWARE,DESIGN,BUSINESS,OTHER',
            'major'        => 'required|string',             
        ], [
            // ข้อความแจ้งเตือนภาษาไทย
            'name.min'              => 'ชื่อ-นามสกุล ต้องมีความยาวอย่างน้อย 4 ตัวอักษร',
            'student_id.required'   => 'กรุณากรอกรหัสนักศึกษา',
            'student_id.numeric'    => 'รหัสนักศึกษาต้องเป็นตัวเลขเท่านั้น',
            'student_id.digits'     => 'รหัสนักศึกษาต้องมีความยาว 11 หลัก',
            'email.ends_with'       => 'ต้องสมัครด้วยอีเมล @mail.kmutt.ac.th เท่านั้น',
            'category.required'     => 'กรุณาเลือกสาขาของสถานประกอบการที่ไปฝึก',
            'category.in'           => 'สาขาที่เลือกไม่ถูกต้อง',
            'major.required'        => 'กรุณาเลือกสาขาวิชา',
            'password.min'          => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร',
            'password.confirmed'    => 'ยืนยันรหัสผ่านไม่ตรงกับรหัสผ่าน',
        ]);

        /** * 🔥 API URL จาก Config
         */
        $apiUrl = config('services.api_url') . "/user/register";

        /**
         * 🚀 ส่งข้อมูลไป API
         */
        try {
            // Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกจึงต้องรอ
            // เครื่องตื่นก่อน ซึ่งกินเวลาได้ถึงนาทีกว่า ค่า timeout ปริยายของ
            // Laravel คือ 30 วินาที จึงตัดทิ้งทั้งที่ฝั่ง API กำลังจะพร้อม
            $response = Http::withoutVerifying()
                ->connectTimeout(30)
                ->timeout(120)
                ->post($apiUrl, [
                    'name'       => $request->name,
                    'student_id' => $request->student_id, // เพิ่ม: ส่งรหัสนักศึกษาไปที่ API
                    'email'      => $request->email,
                    'password'   => $request->password,
                    'category'   => $request->category,
                    'major'      => $request->major,
                    'role'       => 'USER',
                ]);

            /**
             * ✔ กรณีสมัครสำเร็จ
             */
            if ($response->successful()) {
                // API คืนลิงก์ยืนยันมาด้วยเฉพาะตอนที่ตั้ง EXPOSE_VERIFY_LINK ไว้
                // ซึ่งเปิดไว้ช่วงที่ระบบส่งเมลยังใช้งานไม่ได้
                // เดิมค่านี้ถูกทิ้งไปทั้งก้อน ผู้สมัครจึงไม่มีทางยืนยันอีเมลเลย
                $verifyLink = $response->json('verifyLink');

                $redirect = redirect('/login/student')->with(
                    'success',
                    'สมัครสำเร็จแล้ว กรุณากดลิงก์ยืนยันในอีเมลของคุณ จากนั้นรอผู้ดูแลระบบอนุมัติก่อนเข้าใช้งาน'
                );

                if ($verifyLink) {
                    $redirect->with('verify_link', $verifyLink);
                }

                return $redirect;
            }

            /**
             * ❌ กรณี API ปฏิเสธ (เช่น Email ซ้ำ หรือ Student ID ซ้ำ)
             */
            $body = $response->json();
            $errorMessage = $body['message'] ?? 'สมัครสมาชิกไม่สำเร็จ กรุณาลองใหม่อีกครั้ง';
            
            return back()
                ->withErrors(['register' => $errorMessage])
                ->withInput();

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // ต่อไม่ติดหรือรอจนหมดเวลา ส่วนใหญ่คือเซิร์ฟเวอร์กำลังตื่นจากการพัก
            // บอกให้ผู้ใช้กดใหม่ ดีกว่าบอกว่าระบบล่มซึ่งไม่ตรงกับความจริง
            return back()
                ->withErrors([
                    'register' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วกดสมัครอีกครั้ง',
                ])
                ->withInput();

        } catch (\Exception $e) {
            // กรณีอื่นที่ไม่คาดคิด
            return back()
                ->withErrors(['register' => 'ระบบขัดข้อง กรุณาลองใหม่อีกครั้ง'])
                ->withInput();
        }
    }
}