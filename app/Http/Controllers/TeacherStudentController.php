<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * รายชื่อนักศึกษาที่ครูคนนี้ดูแล
 *
 * เดิมหน้านี้เป็นข้อมูลปลอมที่เขียนไว้ในไฟล์ view ตรง ๆ
 * ครูทุกคนเห็นชุดเดียวกันหมด และไม่ตรงกับฐานข้อมูลเลย
 * ตอนนี้ดึงจาก API ที่จับคู่ด้วย category ของครูคนที่ login
 */
class TeacherStudentController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function index()
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $empty = ['students' => collect(), 'category' => null, 'notice' => null];

        try {
            // Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกจึงต้องรอให้ตื่น
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->get($this->apiUrl . '/teacher/my-students');

            if (!$response->successful()) {
                return view('teacher.students', $empty)
                    ->withErrors(['api' => 'ดึงรายชื่อนักศึกษาไม่สำเร็จ']);
            }

            $body = $response->json();

            return view('teacher.students', [
                'students' => collect($body['students'] ?? [])->map(function ($s) {
                    $s['id'] = $s['_id'] ?? ($s['id'] ?? null);
                    return $s;
                })->values(),
                'category' => $body['category'] ?? null,
                // API บอกเองเมื่อบัญชีครูยังไม่ได้ตั้งสาขา
                // ต้องแยกให้ออกจากกรณีที่ตั้งแล้วแต่ยังไม่มีนักศึกษา
                'notice' => $body['message'] ?? null,
            ]);
        } catch (ConnectionException $e) {
            return view('teacher.students', $empty)
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }
}
