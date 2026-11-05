<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ฝั่ง API มีเส้นยืนยันอีเมลและอนุมัติให้ admin อยู่แล้ว
 * แต่หน้าเว็บไม่มีปุ่มเรียกเลย ด่านอนุมัติจึงใช้งานจริงไม่ได้
 */
class AdminApprovalTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    public function test_pending_page_lists_accounts_from_the_api(): void
    {
        Http::fake([
            '*/user/pending' => Http::response([
                [
                    '_id' => 'abc123',
                    'name' => 'Waiting Student',
                    'email' => 'waiting@mail.kmutt.ac.th',
                    'role' => 'USER',
                    'isVerified' => false,
                ],
            ], 200),
        ]);

        $this->asAdmin()
            ->withoutVite()
            ->get('/admin/pending')
            ->assertOk()
            ->assertSee('waiting@mail.kmutt.ac.th', false)
            ->assertSee('ยังไม่ยืนยัน', false);
    }

    public function test_pending_page_shows_the_buttons_for_each_account(): void
    {
        Http::fake([
            '*/user/pending' => Http::response([
                ['_id' => 'abc123', 'name' => 'A', 'email' => 'a@mail.kmutt.ac.th', 'role' => 'USER', 'isVerified' => false],
            ], 200),
        ]);

        $response = $this->asAdmin()->withoutVite()->get('/admin/pending');

        $response->assertSee('/admin/pending/abc123/verify-email', false);
        $response->assertSee('/admin/pending/abc123/approve', false);
        $response->assertSee('/admin/pending/abc123/resend-verify', false);
    }

    public function test_verify_email_button_calls_the_api(): void
    {
        Http::fake(['*' => Http::response(['isVerified' => true], 200)]);

        $this->asAdmin()
            ->patch('/admin/pending/abc123/verify-email')
            ->assertSessionHas('success');

        Http::assertSent(fn($request) => str_contains($request->url(), '/user/abc123/verify-email')
            && $request->method() === 'PATCH');
    }

    public function test_approve_button_calls_the_api(): void
    {
        Http::fake(['*' => Http::response(['status' => 'APPROVED'], 200)]);

        $this->asAdmin()
            ->patch('/admin/pending/abc123/approve')
            ->assertSessionHas('success');

        Http::assertSent(fn($request) => str_contains($request->url(), '/user/abc123/approve'));
    }

    public function test_resend_verify_shows_the_new_link_on_the_page(): void
    {
        Http::fake([
            '*' => Http::response(['verifyLink' => 'https://example.test/verify-email?token=new'], 201),
        ]);

        $this->asAdmin()
            ->post('/admin/pending/abc123/resend-verify')
            ->assertSessionHas('verify_link', 'https://example.test/verify-email?token=new');
    }

    public function test_api_failure_does_not_crash_the_page(): void
    {
        Http::fake(['*/user/pending' => Http::response('boom', 500)]);

        $this->asAdmin()
            ->withoutVite()
            ->get('/admin/pending')
            ->assertOk()
            ->assertSee('ไม่มีบัญชีรออนุมัติ', false);
    }

    public function test_a_student_cannot_open_the_pending_page(): void
    {
        $this->withSession(['api_token' => 'fake-token', 'user' => ['role' => 'USER']])
            ->get('/admin/pending')
            ->assertForbidden();
    }

    public function test_a_visitor_is_sent_to_the_admin_login(): void
    {
        $this->get('/admin/pending')->assertRedirect('/login/admin');
    }
}
