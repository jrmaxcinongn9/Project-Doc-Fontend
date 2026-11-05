<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * คะแนนและความเห็นของนักศึกษาคนที่ login
 *
 * อาจารย์บอกว่านักเรียนต้อง View Feedback และ View Score ได้
 * เดิมไม่มีหน้านี้เลย คะแนนที่ครูให้ไปไม่มีใครเห็น
 */
class StudentScoreController extends Controller
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
            return redirect('/login/student')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $empty = ['scores' => collect(), 'graded' => 0, 'totalScore' => 0, 'totalMax' => 0];

        try {
            // Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกจึงต้องรอให้ตื่น
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->get($this->apiUrl . '/assignment/my-scores');

            if (!$response->successful()) {
                return view('student.scores', $empty)
                    ->withErrors(['api' => 'ดึงคะแนนไม่สำเร็จ']);
            }

            $scores = collect($response->json() ?? []);

            // รวมเฉพาะงานที่ตรวจแล้ว งานที่ยังไม่ตรวจไม่ควรถูกนับเป็นศูนย์
            // ไม่อย่างนั้นยอดรวมจะดูแย่กว่าความจริงตั้งแต่ต้นเทอม
            $gradedRows = $scores->filter(fn($s) => ($s['score'] ?? null) !== null);

            return view('student.scores', [
                'scores'     => $scores,
                'graded'     => $gradedRows->count(),
                'totalScore' => $gradedRows->sum(fn($s) => (int) $s['score']),
                'totalMax'   => $gradedRows->sum(fn($s) => (int) ($s['max_score'] ?? 0)),
            ]);
        } catch (ConnectionException $e) {
            return view('student.scores', $empty)
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }
}
