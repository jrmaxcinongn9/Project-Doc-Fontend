<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ลิงก์ยืนยันที่ได้ตอนสมัครโชว์รอบเดียว รีเฟรชแล้วหายถาวร
 * ผู้ใช้จึงติดอยู่ที่ข้อความว่ากรุณายืนยันอีเมล โดยไม่มีลิงก์ให้กด
 */
class LoginResendLinkTest extends TestCase
{
    private array $creds = [
        'email' => 'student@mail.kmutt.ac.th',
        'password' => '123456',
    ];

    private function loginBlocked(): array
    {
        return [
            '*/auth/login' => Http::response([
                'message' => 'กรุณายืนยันอีเมลก่อนเข้าใช้งาน ตรวจสอบลิงก์ยืนยันในกล่องจดหมายของคุณ',
            ], 403),
        ];
    }

    public function test_a_blocked_login_asks_for_a_new_link(): void
    {
        Http::fake($this->loginBlocked() + [
            '*/user/resend-verify-mine' => Http::response([
                'verifyLink' => 'https://app.test/verify-email?token=fresh',
            ], 200),
        ]);

        $this->post('/login', $this->creds)
            ->assertSessionHas('verify_link', 'https://app.test/verify-email?token=fresh');
    }

    public function test_the_error_message_is_still_shown(): void
    {
        Http::fake($this->loginBlocked() + [
            '*/user/resend-verify-mine' => Http::response(['verifyLink' => 'x'], 200),
        ]);

        $this->post('/login', $this->creds)->assertSessionHasErrors('email');
    }

    public function test_the_password_is_passed_through_so_the_api_can_check_it(): void
    {
        Http::fake($this->loginBlocked() + [
            '*/user/resend-verify-mine' => Http::response(['verifyLink' => 'x'], 200),
        ]);

        $this->post('/login', $this->creds);

        Http::assertSent(fn($r) => str_contains($r->url(), 'resend-verify-mine')
            && $r->data()['email'] === 'student@mail.kmutt.ac.th'
            && $r->data()['password'] === '123456');
    }

    public function test_a_wrong_password_does_not_trigger_a_link_request(): void
    {
        Http::fake([
            '*/auth/login' => Http::response(['message' => 'Invalid email or password'], 401),
            '*' => Http::response([], 200),
        ]);

        $this->post('/login', $this->creds);

        Http::assertNotSent(fn($r) => str_contains($r->url(), 'resend-verify-mine'));
    }

    public function test_an_account_awaiting_approval_does_not_trigger_one_either(): void
    {
        Http::fake([
            '*/auth/login' => Http::response(['message' => 'บัญชีนี้อยู่ระหว่างรอผู้ดูแลระบบอนุมัติ'], 403),
            '*' => Http::response([], 200),
        ]);

        $this->post('/login', $this->creds);

        Http::assertNotSent(fn($r) => str_contains($r->url(), 'resend-verify-mine'));
    }

    public function test_the_login_page_still_works_when_the_link_request_fails(): void
    {
        Http::fake($this->loginBlocked() + [
            '*/user/resend-verify-mine' => Http::response('boom', 500),
        ]);

        $this->post('/login', $this->creds)
            ->assertRedirect()
            ->assertSessionHasErrors('email')
            ->assertSessionMissing('verify_link');
    }

    public function test_the_button_appears_on_the_login_page_afterwards(): void
    {
        Http::fake($this->loginBlocked() + [
            '*/user/resend-verify-mine' => Http::response([
                'verifyLink' => 'https://app.test/verify-email?token=fresh',
            ], 200),
        ]);

        $this->post('/login', $this->creds);

        $this->withoutVite()->get('/login/student')
            ->assertSee('ยืนยันอีเมลของฉัน', false)
            ->assertSee('token=fresh', false);
    }
}
