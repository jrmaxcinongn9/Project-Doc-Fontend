<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * ครูให้คะแนนและเขียน feedback ของนักศึกษารายคน
 *
 * ฟิลด์พวกนี้มีในฐานข้อมูลมานานแล้ว แต่ไม่มีหน้าจอไหนเขียนลงไปได้
 * ครูจึงรับงานได้อย่างเดียว ตอบกลับหรือให้คะแนนไม่ได้เลย
 */
class TeacherGradeController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function store(Request $request, string $assignmentId, string $studentId)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $request->validate([
            'score'    => 'nullable|integer|min:0',
            'feedback' => 'nullable|string|max:5000',
        ], [
            'score.integer' => 'คะแนนต้องเป็นตัวเลขจำนวนเต็ม',
            'score.min'     => 'คะแนนต้องไม่ติดลบ',
            'feedback.max'  => 'ข้อความยาวเกินไป',
        ]);

        // ช่องว่างคือไม่ให้คะแนน ต่างจากเลขศูนย์ซึ่งคือให้ศูนย์คะแนน
        // ส่ง null ไปให้ API ตีความว่าลบคะแนนเดิมทิ้ง
        $payload = [
            'score'    => $request->input('score') === null || $request->input('score') === ''
                ? null
                : (int) $request->input('score'),
            'feedback' => $request->input('feedback') ?: null,
        ];

        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->patch(
                    $this->apiUrl . "/assignment/{$assignmentId}/students/{$studentId}/grade",
                    $payload,
                );

            if (!$response->successful()) {
                // API บอกเหตุผลมาตรง ๆ เช่นคะแนนเกินคะแนนเต็ม
                // ส่งต่อให้ครูเห็น ดีกว่าบอกว่าบันทึกไม่สำเร็จเฉย ๆ
                return back()->withErrors([
                    'grade' => $response->json('message') ?? 'บันทึกคะแนนไม่สำเร็จ',
                ])->withInput();
            }

            return back()->with('success', 'บันทึกคะแนนและความเห็นเรียบร้อย');
        } catch (ConnectionException $e) {
            return back()->withErrors([
                'grade' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง',
            ])->withInput();
        }
    }
}
