<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * หน้าจัดการบัญชีที่รออนุมัติ
 *
 * ก่อนหน้านี้ฝั่ง API มีเส้นยืนยันอีเมลและอนุมัติให้ admin อยู่แล้ว
 * แต่หน้าเว็บไม่มีปุ่มเรียกเลย ทางเดียวคือยิงคำสั่งจาก terminal เอง
 * ซึ่งทำให้ด่านอนุมัติที่สร้างไว้ใช้งานจริงไม่ได้
 */
class AdminApprovalController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    /** Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกจึงต้องรอให้ตื่นก่อน */
    private function client(string $token)
    {
        return Http::withToken($token)
            ->withoutVerifying()
            ->acceptJson()
            ->connectTimeout(30)
            ->timeout(120);
    }

    public function index(Request $request)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $response = $this->client($token)->get($this->apiUrl . '/user/pending');

            if (!$response->successful()) {
                return view('admin.pending-users', ['users' => collect()])
                    ->withErrors(['api' => 'ดึงรายการรออนุมัติไม่สำเร็จ']);
            }

            $users = collect($response->json())->map(function ($u) {
                $u['id'] = $u['_id'] ?? ($u['id'] ?? null);
                return $u;
            })->values();

            return view('admin.pending-users', ['users' => $users]);
        } catch (ConnectionException $e) {
            return view('admin.pending-users', ['users' => collect()])
                ->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วโหลดหน้านี้อีกครั้ง']);
        }
    }

    /** admin กดยืนยันอีเมลแทนผู้ใช้ ใช้เมื่อเมลส่งไปไม่ถึง */
    public function verifyEmail($id)
    {
        return $this->call('patch', "/user/{$id}/verify-email", 'ยืนยันอีเมลเรียบร้อย');
    }

    public function approve($id)
    {
        return $this->call('patch', "/user/{$id}/approve", 'อนุมัติบัญชีเรียบร้อย');
    }

    public function reject($id)
    {
        return $this->call('patch', "/user/{$id}/reject", 'ปฏิเสธบัญชีเรียบร้อย');
    }

    /**
     * อนุมัติหรือปฏิเสธหลายบัญชีพร้อมกัน
     *
     * ตอนอนุมัติ ยืนยันอีเมลให้ด้วยได้ (ค่าปริยาย) เพราะบัญชีที่อนุมัติแต่ยังไม่ยืนยันอีเมล
     * ยัง login ไม่ได้ และระบบส่งเมลยังใช้งานไม่ได้
     * ถ้าบางบัญชีไม่ผ่าน บัญชีอื่นยังทำต่อ แล้วบอกว่าไม่ผ่านกี่บัญชี
     */
    public function bulk(Request $request)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'string',
            'action' => 'required|in:approve,reject',
        ], [
            'ids.required' => 'กรุณาเลือกบัญชีอย่างน้อย 1 บัญชี',
        ]);

        $approve = $request->input('action') === 'approve';
        $verify = $approve && $request->boolean('verify');
        $done = 0;
        $failed = 0;

        try {
            foreach (array_unique($request->input('ids')) as $id) {
                $id = rawurlencode($id);

                if ($verify && !$this->client($token)->patch($this->apiUrl . "/user/{$id}/verify-email")->successful()) {
                    $failed++;
                    continue;
                }

                $res = $this->client($token)->patch($this->apiUrl . "/user/{$id}/" . ($approve ? 'approve' : 'reject'));
                $res->successful() ? $done++ : $failed++;
            }
        } catch (ConnectionException $e) {
            return back()->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน ทำไปแล้ว ' . $done . ' บัญชี กรุณาลองอีกครั้ง']);
        }

        $redirect = back()->with('success', ($approve ? 'อนุมัติ' : 'ปฏิเสธ') . "แล้ว {$done} บัญชี");

        return $failed
            ? $redirect->withErrors(['api' => "ทำรายการไม่สำเร็จ {$failed} บัญชี"])
            : $redirect;
    }

    /**
     * ขอลิงก์ยืนยันใหม่ แล้วเอาลิงก์มาแสดงบนหน้าเว็บ
     * ระบบส่งเมลยังใช้งานไม่ได้ ถ้าไม่โชว์ลิงก์ admin ก็ไม่มีอะไรส่งต่อให้ผู้ใช้
     */
    public function resendVerify($id)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $response = $this->client($token)
                ->post($this->apiUrl . "/user/{$id}/resend-verify");

            if (!$response->successful()) {
                return back()->withErrors(['api' => 'ขอลิงก์ยืนยันใหม่ไม่สำเร็จ']);
            }

            return back()
                ->with('success', 'สร้างลิงก์ยืนยันใหม่แล้ว ลิงก์เดิมใช้ไม่ได้อีก')
                ->with('verify_link', $response->json('verifyLink'));
        } catch (ConnectionException $e) {
            return back()->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }
    }

    private function call(string $method, string $path, string $success)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/admin')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $response = $this->client($token)->{$method}($this->apiUrl . $path);

            if (!$response->successful()) {
                return back()->withErrors(['api' => $this->apiError($response)]);
            }

            return back()->with('success', $success);
        } catch (ConnectionException $e) {
            return back()->withErrors(['api' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }
    }
}
