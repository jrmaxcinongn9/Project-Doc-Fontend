<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * กดลิงก์ในเมลแล้วเข้าระบบได้เลย
 *   ลิงก์ยืนยันอีเมลของบัญชีที่อนุมัติแล้ว
 *   ลิงก์ในเมลแจ้งว่า admin อนุมัติแล้ว
 */
class MagicLoginTest extends TestCase
{
    private function apiLogin(string $role = 'USER'): array
    {
        return [
            'access_token' => 'jwt-token',
            'user' => ['id' => 'u1', 'email' => 'a@mail.kmutt.ac.th', 'name' => 'A', 'role' => $role],
        ];
    }

    public function test_the_approval_link_logs_the_student_in(): void
    {
        Http::fake(['*/auth/magic' => Http::response($this->apiLogin(), 200)]);

        $this->get('/login/magic?token=abc')->assertRedirect('/student/dashboard');

        $this->assertSame('jwt-token', session('api_token'));
        $this->assertSame('USER', session('user.role'));
        Http::assertSent(fn ($r) => $r['token'] === 'abc');
    }

    public function test_a_teacher_link_goes_to_the_teacher_pages(): void
    {
        Http::fake(['*/auth/magic' => Http::response($this->apiLogin('TEACHER'), 200)]);

        $this->get('/login/magic?token=abc')->assertRedirect('/teacher/classroom');
    }

    public function test_a_used_or_expired_link_goes_to_login_with_the_reason(): void
    {
        Http::fake(['*/auth/magic' => Http::response(['message' => 'ลิงก์เข้าสู่ระบบไม่ถูกต้องหรือถูกใช้ไปแล้ว'], 400)]);

        $this->get('/login/magic?token=old')
            ->assertRedirect('/login/student')
            ->assertSessionHasErrors(['email' => 'ลิงก์เข้าสู่ระบบไม่ถูกต้องหรือถูกใช้ไปแล้ว']);

        $this->assertNull(session('api_token'));
    }

    public function test_an_old_session_is_replaced(): void
    {
        Http::fake(['*/auth/magic' => Http::response($this->apiLogin(), 200)]);

        $this->withSession(['api_token' => 'someone-else', 'user' => ['role' => 'ADMIN']])
            ->get('/login/magic?token=abc');

        $this->assertSame('jwt-token', session('api_token'));
        $this->assertSame('USER', session('user.role'));
    }

    public function test_verifying_an_approved_account_logs_in_straight_away(): void
    {
        Http::fake([
            '*/user/verify-email*' => Http::response(['message' => 'ยืนยันอีเมลเรียบร้อย', 'login_token' => 'lt'], 200),
            '*/auth/magic' => Http::response($this->apiLogin(), 200),
        ]);

        $this->get('/verify-email?token=v1')->assertRedirect('/student/dashboard');

        $this->assertSame('jwt-token', session('api_token'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/auth/magic') && $r['token'] === 'lt');
    }

    public function test_verifying_a_pending_account_says_to_wait_for_approval(): void
    {
        Http::fake(['*/user/verify-email*' => Http::response([
            'message' => 'ยืนยันอีเมลเรียบร้อย บัญชีอยู่ระหว่างรอผู้ดูแลระบบอนุมัติ เมื่ออนุมัติแล้วจะได้รับอีเมลพร้อมลิงก์เข้าสู่ระบบ',
        ], 200)]);

        $this->withoutVite()->get('/verify-email?token=v1')
            ->assertOk()
            ->assertSee('รอผู้ดูแลระบบอนุมัติ', false)
            ->assertSee('ลิงก์เข้าสู่ระบบ', false);

        $this->assertNull(session('api_token'));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/auth/magic'));
    }
}
