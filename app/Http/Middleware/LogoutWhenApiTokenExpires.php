<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ออกจากระบบให้เองเมื่อ token ของ API หมดอายุ
 *
 * token จาก API มีอายุสั้น (ค่าปริยาย 1 ชั่วโมง) แต่ session ของ Laravel อยู่นานกว่า
 * พ้นชั่วโมงไปแล้ว session ยังบอกว่า login อยู่ ด่านของแต่ละกลุ่มหน้าจึงปล่อยผ่าน
 * แต่ API ตอบ 401 ทุกคำขอ หน้าเว็บขึ้นว่างหรือพังโดยไม่บอกอะไร
 * ผู้ใช้ไม่มีทางรู้ว่าต้อง login ใหม่
 *
 * อ่านเวลาหมดอายุ (exp) จาก payload ของ JWT ตรง ๆ ไม่ต้องยิง API
 * ไม่ได้ตรวจลายเซ็น เพราะแค่ใช้ตัดสินว่าจะพาไปหน้า login หรือไม่
 * ส่วนการตรวจสิทธิ์จริงยังอยู่ที่ API เหมือนเดิม
 */
class LogoutWhenApiTokenExpires
{
    // เผื่อเวลาไว้นิดหน่อย กันกรณีหมดอายุระหว่างที่หน้ากำลังเรียก API
    private const LEEWAY_SECONDS = 30;

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->session()->get('api_token');

        if (!$token || !$this->isExpired($token)) {
            return $next($request);
        }

        $role = $request->session()->get('user.role');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // หน้าที่ไม่ต้อง login เช่น ลิงก์ยืนยันอีเมลหรือลิงก์เข้าสู่ระบบจากเมล ให้ทำงานต่อด้วย session ที่ล้างแล้ว
        // เดิมพาไปหน้า login ทุกหน้า คนที่เคยเข้าระบบไว้ในเบราว์เซอร์นั้นจึงกดลิงก์ในเมลไม่ได้
        if (!$this->needsLogin($request)) {
            return $next($request);
        }

        return redirect($this->loginPathFor($role))
            ->withErrors(['email' => 'หมดเวลาการใช้งาน กรุณาเข้าสู่ระบบใหม่']);
    }

    private function isExpired(string $token): bool
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        $payload = json_decode(
            base64_decode(strtr($parts[1], '-_', '+/')),
            true,
        );

        $exp = $payload['exp'] ?? null;

        // token ที่ไม่มี exp ถือว่าไม่หมดอายุ ปล่อยให้ API ตัดสินเอง
        if (!is_numeric($exp)) {
            return false;
        }

        return (int) $exp <= time() + self::LEEWAY_SECONDS;
    }

    private function needsLogin(Request $request): bool
    {
        return $request->is('student/*', 'teacher/*', 'admin/*', 'profile/*', 'notifications', 'notifications/*', 'assignments/*');
    }

    private function loginPathFor(?string $role): string
    {
        return match ($role) {
            'TEACHER' => '/login/teacher',
            'ADMIN'   => '/login/admin',
            default   => '/login/student',
        };
    }
}
