<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ระบบส่งเมลยังใช้งานไม่ได้ API จึงคืนลิงก์ยืนยันมากับ response ตอนสมัคร
 * เมื่อตั้ง EXPOSE_VERIFY_LINK ไว้ แต่หน้าเว็บทิ้งค่านั้นไปทั้งก้อน
 * ผู้สมัครจึงไม่มีทางยืนยันอีเมลเลย ทั้งที่ลิงก์ส่งมาถึงแล้ว
 */
class VerifyLinkOnLoginTest extends TestCase
{
    private array $payload = [
        'name' => 'Test Student',
        'student_id' => '66070500099',
        'email' => 'student@mail.kmutt.ac.th',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'category' => 'SOFTWARE',
        'major' => 'CPE',
    ];

    public function test_register_puts_the_verify_link_in_the_session(): void
    {
        Http::fake([
            '*/user/register' => Http::response([
                'email' => 'student@mail.kmutt.ac.th',
                'verifyLink' => 'https://example.test/verify-email?token=abc123',
            ], 201),
        ]);

        $this->post('/register', $this->payload)
            ->assertRedirect('/login/student')
            ->assertSessionHas('verify_link', 'https://example.test/verify-email?token=abc123');
    }

    public function test_register_still_works_when_the_api_sends_no_link(): void
    {
        Http::fake([
            '*/user/register' => Http::response(['email' => 'student@mail.kmutt.ac.th'], 201),
        ]);

        $this->post('/register', $this->payload)
            ->assertRedirect('/login/student')
            ->assertSessionHas('success')
            ->assertSessionMissing('verify_link');
    }

    public function test_login_page_shows_the_verify_button_when_a_link_is_present(): void
    {
        $response = $this->withoutVite()
            ->withSession(['verify_link' => 'https://example.test/verify-email?token=abc123'])
            ->get('/login/student');

        $response->assertOk();
        $response->assertSee('ยืนยันอีเมลของฉัน', false);
        $response->assertSee('https://example.test/verify-email?token=abc123', false);
    }

    public function test_login_page_hides_the_button_when_there_is_no_link(): void
    {
        $this->withoutVite()
            ->get('/login/student')
            ->assertOk()
            ->assertDontSee('ยืนยันอีเมลของฉัน', false);
    }

    public function test_login_page_shows_the_success_message_after_registering(): void
    {
        // หน้านี้เคยอ่านแต่ errors ข้อความว่าสมัครสำเร็จจึงหายไปเงียบ ๆ
        $this->withoutVite()
            ->withSession(['success' => 'สมัครสำเร็จแล้ว'])
            ->get('/login/student')
            ->assertOk()
            ->assertSee('สมัครสำเร็จแล้ว', false);
    }
}
