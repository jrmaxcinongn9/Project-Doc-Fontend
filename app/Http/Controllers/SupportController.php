<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * ช่วยเหลือ: คำถามที่พบบ่อย และแจ้งปัญหาถึงผู้ดูแลระบบ
 * เปิดได้โดยไม่ต้อง login เพราะปัญหาที่พบบ่อยที่สุดคือเข้าระบบไม่ได้
 */
class SupportController extends Controller
{
    public const TOPICS = [
        'ACCOUNT'    => 'บัญชี สมัคร ยืนยันอีเมล หรือรหัสผ่าน',
        'CLASSROOM'  => 'ห้องเรียนหรือสาขาวิชา',
        'SUBMISSION' => 'การส่งงานและไฟล์',
        'GRADE'      => 'คะแนนและความเห็น',
        'OTHER'      => 'อื่น ๆ',
    ];

    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function show()
    {
        return view('support.index', ['topics' => self::TOPICS]);
    }

    public function store(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:200',
            'topic'   => 'required|in:' . implode(',', array_keys(self::TOPICS)),
            'message' => 'required|string|min:10|max:3000',
        ], [
            'name.required'    => 'กรุณากรอกชื่อ',
            'email.required'   => 'กรุณากรอกอีเมลที่ใช้ติดต่อกลับ',
            'email.email'      => 'รูปแบบอีเมลไม่ถูกต้อง',
            'topic.required'   => 'กรุณาเลือกหัวข้อ',
            'message.required' => 'กรุณาเล่าปัญหาที่พบ',
            'message.min'      => 'กรุณาเล่ารายละเอียดอย่างน้อย 10 ตัวอักษร',
            'message.max'      => 'รายละเอียดยาวเกินไป',
        ]);

        try {
            $client = Http::acceptJson()->connectTimeout(30)->timeout(60);
            // login อยู่ ส่ง token ไปด้วย เรื่องที่แจ้งจะผูกกับบัญชี
            if (session('api_token')) {
                $client = $client->withToken(session('api_token'));
            }
            $res = $client->post($this->apiUrl . '/support', $data);
        } catch (ConnectionException $e) {
            return back()->withErrors(['support' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง'])->withInput();
        }

        if (!$res->successful()) {
            return back()->withErrors(['support' => $this->apiError($res, 'ส่งเรื่องไม่สำเร็จ')])->withInput();
        }

        return redirect('/support#form')->with('support_sent', 'ได้รับเรื่องแล้ว ผู้ดูแลระบบจะตอบกลับทางอีเมล ' . $data['email']);
    }

    // ---------------------------- admin ----------------------------

    public function adminIndex(Request $request)
    {
        $status = in_array($request->query('status'), ['OPEN', 'RESOLVED', 'ALL'], true) ? $request->query('status') : 'OPEN';

        try {
            $res = Http::withToken(session('api_token'))->acceptJson()->timeout(60)
                ->get($this->apiUrl . '/support', $status === 'ALL' ? [] : ['status' => $status]);
            $tickets = $res->successful() ? collect($res->json()) : collect();
            $error = $res->successful() ? null : $this->apiError($res, 'โหลดรายการไม่สำเร็จ');
        } catch (ConnectionException $e) {
            $tickets = collect();
            $error = 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารีเฟรชอีกครั้ง';
        }

        return view('admin.support', [
            'tickets' => $tickets,
            'status'  => $status,
            'topics'  => self::TOPICS,
            'loadError' => $error,
        ]);
    }

    public function adminReply(Request $request, string $id)
    {
        $request->validate(['message' => 'required|string|min:2|max:3000'], [
            'message.required' => 'กรุณาพิมพ์คำตอบ',
        ]);

        try {
            $res = Http::withToken(session('api_token'))->acceptJson()->timeout(60)
                ->post($this->apiUrl . "/support/{$id}/reply", [
                    'message' => $request->input('message'),
                    'resolve' => $request->boolean('resolve'),
                ]);
        } catch (ConnectionException $e) {
            return back()->withErrors(['support' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง'])->withInput();
        }

        if (!$res->successful()) {
            return back()->withErrors(['support' => $this->apiError($res, 'ตอบไม่สำเร็จ')])->withInput();
        }

        return back()->with('success', $res->json('mailed')
            ? 'ตอบกลับทางอีเมลแล้ว'
            : 'บันทึกคำตอบแล้ว แต่ส่งอีเมลไม่สำเร็จ กรุณาติดต่อผู้แจ้งทางอื่น');
    }

    public function adminStatus(Request $request, string $id)
    {
        $request->validate(['status' => 'required|in:OPEN,RESOLVED']);

        try {
            $res = Http::withToken(session('api_token'))->acceptJson()->timeout(60)
                ->patch($this->apiUrl . "/support/{$id}", ['status' => $request->input('status')]);
        } catch (ConnectionException $e) {
            return back()->withErrors(['support' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }

        if (!$res->successful()) {
            return back()->withErrors(['support' => $this->apiError($res, 'เปลี่ยนสถานะไม่สำเร็จ')]);
        }

        return back()->with('success', $request->input('status') === 'RESOLVED' ? 'ปิดเรื่องแล้ว' : 'เปิดเรื่องอีกครั้งแล้ว');
    }
}
