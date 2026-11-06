<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

/**
 * รูปโปรไฟล์ของผู้ใช้ที่ login ทุกบทบาท
 * ตัวรูปเก็บที่ API (GridFS) ที่นี่แค่ส่งต่อ
 */
class ProfileAvatarController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    /** รูปของตัวเอง ไม่มีรูปตอบ 404 ให้หน้าเว็บแสดงตัวอักษรแทน */
    public function show()
    {
        $token = session('api_token');
        abort_unless($token, 404);

        try {
            $res = Http::withToken($token)->timeout(30)->get($this->apiUrl . '/user/me/avatar');
        } catch (ConnectionException $e) {
            abort(404);
        }

        abort_unless($res->successful(), 404);

        return response($res->body(), 200, [
            'Content-Type'           => $res->header('Content-Type') ?: 'image/png',
            'Cache-Control'          => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request)
    {
        $token = session('api_token');
        if (!$token) return redirect('/');

        $request->validate([
            'avatar' => 'required|file|mimetypes:image/png,image/jpeg,image/webp,image/gif|max:2048',
        ], [
            'avatar.required'  => 'กรุณาเลือกรูป',
            'avatar.mimetypes' => 'รูปโปรไฟล์ต้องเป็น PNG, JPG, WebP หรือ GIF',
            'avatar.max'       => 'รูปใหญ่เกิน 2 MB',
        ]);

        $file = $request->file('avatar');

        try {
            $res = Http::withToken($token)
                ->acceptJson()
                ->timeout(60)
                ->attach('avatar', file_get_contents($file->getRealPath()), $file->getClientOriginalName(), [
                    'Content-Type' => $file->getMimeType(),
                ])
                ->post($this->apiUrl . '/user/me/avatar');
        } catch (ConnectionException $e) {
            return back()->withErrors(['avatar' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }

        if (!$res->successful()) {
            $msg = $res->json('message');
            return back()->withErrors(['avatar' => is_string($msg) ? $msg : 'อัปโหลดรูปไม่สำเร็จ']);
        }

        // เปลี่ยนเลขเวอร์ชันให้ทุกหน้าโหลดรูปใหม่ ไม่ใช้รูปเก่าที่ cache ไว้
        session(['avatar_v' => time()]);

        return back()->with('avatar_success', 'เปลี่ยนรูปโปรไฟล์แล้ว');
    }

    public function destroy()
    {
        $token = session('api_token');
        if (!$token) return redirect('/');

        try {
            Http::withToken($token)->acceptJson()->timeout(30)->delete($this->apiUrl . '/user/me/avatar');
        } catch (ConnectionException $e) {
            return back()->withErrors(['avatar' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง']);
        }

        session(['avatar_v' => time()]);

        return back()->with('avatar_success', 'ลบรูปโปรไฟล์แล้ว');
    }
}
