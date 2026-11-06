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

    /**
     * ตั้งหรือแก้คะแนนเต็มของงานจากหน้าให้คะแนนโดยตรง
     *
     * งานเก่าที่สร้างก่อนมีช่องนี้มี max_score เป็น null ช่องคะแนนจึงถูกปิด
     * ส่งไปแค่ max_score ตัวเดียว เพราะเส้นแก้ไขงานแบบเต็มจะส่ง
     * assignment_name กับ due_date ไปด้วยเสมอ ถ้าว่างจะลบค่าเดิมทิ้ง
     */
    public function setMaxScore(Request $request, string $assignmentId)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $request->validate([
            'max_score' => 'required|integer|min:1|max:1000',
        ], [
            'max_score.required' => 'กรุณากรอกคะแนนเต็ม',
            'max_score.integer'  => 'คะแนนเต็มต้องเป็นตัวเลขจำนวนเต็ม',
            'max_score.min'      => 'คะแนนเต็มต้องมากกว่าศูนย์',
            'max_score.max'      => 'คะแนนเต็มสูงเกินไป',
        ]);

        try {
            $response = Http::withToken($token)
                ->withoutVerifying()
                ->acceptJson()
                ->connectTimeout(30)
                ->timeout(120)
                ->put($this->apiUrl . "/assignment/{$assignmentId}", [
                    'max_score' => (int) $request->input('max_score'),
                ]);

            if (!$response->successful()) {
                return back()->withErrors([
                    'max_score' => $response->json('message') ?? 'ตั้งคะแนนเต็มไม่สำเร็จ',
                ])->withInput();
            }

            return back()->with('success', 'ตั้งคะแนนเต็มเรียบร้อย ให้คะแนนได้แล้ว');
        } catch (ConnectionException $e) {
            return back()->withErrors([
                'max_score' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง',
            ])->withInput();
        }
    }

    /**
     * บันทึกคะแนนและความเห็นของหลายคนพร้อมกัน
     *
     * ส่งเฉพาะแถวที่ค่าเปลี่ยนจากเดิม ไม่ยิง API ซ้ำกับคนที่ไม่ได้แก้
     * ถ้าบางคนบันทึกไม่ผ่าน คนอื่นยังบันทึกต่อ แล้วบอกชื่อคนที่ไม่ผ่านพร้อมเหตุผล
     */
    public function storeMany(Request $request, string $assignmentId)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $request->validate([
            'rows'              => 'array',
            'rows.*.score'      => 'nullable|integer|min:0',
            'rows.*.feedback'   => 'nullable|string|max:5000',
        ], [
            'rows.*.score.integer' => 'คะแนนต้องเป็นตัวเลขจำนวนเต็ม',
            'rows.*.score.min'     => 'คะแนนต้องไม่ติดลบ',
            'rows.*.feedback.max'  => 'ข้อความยาวเกินไป',
        ]);

        $norm = fn ($v) => ($v === null || $v === '') ? null : (string) $v;

        $changed = collect($request->input('rows', []))->filter(function ($r) use ($norm) {
            return $norm($r['score'] ?? null) !== $norm($r['orig_score'] ?? null)
                || $norm($r['feedback'] ?? null) !== $norm($r['orig_feedback'] ?? null);
        });

        if ($changed->isEmpty()) {
            return back()->with('success', 'ไม่มีอะไรเปลี่ยน');
        }

        $saved = 0;
        $failed = [];

        foreach ($changed as $studentId => $r) {
            $payload = [
                'score'    => $norm($r['score'] ?? null) === null ? null : (int) $r['score'],
                'feedback' => $norm($r['feedback'] ?? null),
            ];

            try {
                $res = Http::withToken($token)
                    ->acceptJson()
                    ->connectTimeout(30)
                    ->timeout(60)
                    ->patch($this->apiUrl . "/assignment/{$assignmentId}/students/{$studentId}/grade", $payload);

                if ($res->successful()) {
                    $saved++;
                } else {
                    $failed[] = $res->json('message') ?? 'บันทึกไม่สำเร็จ';
                }
            } catch (ConnectionException $e) {
                $failed[] = 'เซิร์ฟเวอร์กำลังเริ่มทำงาน';
                break;
            }
        }

        $redirect = back();

        if ($saved) {
            $redirect = $redirect->with('success', "บันทึกคะแนนและความเห็นแล้ว {$saved} คน");
        }

        if ($failed) {
            return $redirect->withErrors([
                'grade' => 'บันทึกไม่สำเร็จ ' . count($failed) . ' คน: ' . implode(' / ', array_unique($failed)),
            ])->withInput();
        }

        return $redirect;
    }
}
