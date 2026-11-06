<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ช่วยเหลือ: คำถามที่พบบ่อย แจ้งปัญหาถึง admin และ admin ตอบกลับทางเมล
 */
class SupportTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession(['api_token' => 'fake', 'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th']]);
    }

    private function form(array $over = []): array
    {
        return array_merge([
            'name' => 'สมชาย', 'email' => 'Somchai@MAIL.kmutt.ac.th', 'topic' => 'ACCOUNT',
            'message' => 'ไม่ได้รับเมลยืนยันเลยครับ ดูในสแปมแล้ว',
        ], $over);
    }

    public function test_the_help_page_opens_without_logging_in(): void
    {
        $this->withoutVite()->get('/support')
            ->assertOk()
            ->assertSee('ไม่ได้รับเมลยืนยันตอนสมัคร', false)
            ->assertSee('action="/support"', false);
    }

    public function test_the_student_login_support_button_goes_to_the_help_page(): void
    {
        $this->withoutVite()->get('/login/student')
            ->assertSee('href="/support"', false)
            ->assertDontSee('openSupport()', false);
    }

    public function test_the_student_login_has_a_sign_up_link_under_the_form(): void
    {
        $html = $this->withoutVite()->get('/login/student')->getContent();

        $this->assertStringContainsString("Don't have an account?", $html);
        // ปุ่มมุมขวาบนกับลิงก์ใต้ฟอร์ม
        $this->assertSame(2, substr_count($html, 'href="/register"'));
        // ปุ่มส้มทึบเหลือแค่ Login ปุ่มเดียว
        $this->assertStringNotContainsString('from-[#F58220] to-[#E06B00]\n                      shadow-lg shadow-orange-500/30\n                      hover:shadow-orange-500/60', $html);
    }

    public function test_teacher_and_admin_login_link_to_help(): void
    {
        $this->withoutVite()->get('/login/teacher')->assertSee('href="/support"', false);
        $this->withoutVite()->get('/login/admin')->assertSee('href="/support"', false);
    }

    public function test_a_logged_in_user_gets_name_and_email_filled_in(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER', 'name' => 'สมหญิง', 'email' => 'somying@mail.kmutt.ac.th']])
            ->withoutVite()->get('/support')
            ->assertSee('value="สมหญิง"', false)
            ->assertSee('value="somying@mail.kmutt.ac.th"', false);
    }

    public function test_sending_a_report_reaches_the_api_with_a_lowercase_email(): void
    {
        Http::fake(['*/support' => Http::response(['_id' => 't1'], 201)]);

        $this->post('/support', $this->form())
            ->assertRedirect('/support#form')
            ->assertSessionHas('support_sent');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/support')
            && $r['email'] === 'somchai@mail.kmutt.ac.th'
            && $r['topic'] === 'ACCOUNT'
            && !$r->hasHeader('Authorization'));
    }

    public function test_a_logged_in_report_carries_the_token(): void
    {
        Http::fake(['*/support' => Http::response(['_id' => 't1'], 201)]);

        $this->withSession(['api_token' => 'jwt', 'user' => ['role' => 'USER']])->post('/support', $this->form());

        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer jwt'));
    }

    public function test_a_too_short_report_is_refused_before_sending(): void
    {
        Http::fake();

        $this->post('/support', $this->form(['message' => 'สั้น']))->assertSessionHasErrors('message');

        Http::assertNothingSent();
    }

    public function test_too_many_reports_show_the_api_reason(): void
    {
        Http::fake(['*/support' => Http::response(['message' => 'แจ้งปัญหาบ่อยเกินไป กรุณารอสักครู่แล้วลองใหม่'], 429)]);

        $this->post('/support', $this->form())->assertSessionHasErrors('support');

        $this->assertStringContainsString('บ่อยเกินไป', session('errors')->first('support'));
    }

    public function test_admin_sees_open_reports(): void
    {
        Http::fake(['*/support*' => Http::response([[
            '_id' => 't1', 'name' => 'สมชาย', 'email' => 'a@mail.kmutt.ac.th', 'topic' => 'ACCOUNT',
            'message' => 'ไม่ได้รับเมล', 'status' => 'OPEN', 'replies' => [], 'createdAt' => now()->toIso8601String(),
        ]], 200)]);

        $this->asAdmin()->withoutVite()->get('/admin/support')
            ->assertOk()
            ->assertSee('ไม่ได้รับเมล', false)
            ->assertSee('/admin/support/t1/reply', false);

        Http::assertSent(fn ($r) => str_contains($r->url(), 'status=OPEN'));
    }

    public function test_admin_reply_goes_to_the_api_and_reports_mail_status(): void
    {
        Http::fake(['*/support/t1/reply' => Http::response(['mailed' => true], 201)]);

        $this->asAdmin()->post('/admin/support/t1/reply', ['message' => 'กด Login อีกครั้งครับ', 'resolve' => '1'])
            ->assertSessionHas('success', 'ตอบกลับทางอีเมลแล้ว');

        Http::assertSent(fn ($r) => $r['message'] === 'กด Login อีกครั้งครับ' && $r['resolve'] === true);
    }

    public function test_admin_is_told_when_the_reply_email_failed(): void
    {
        Http::fake(['*' => Http::response(['mailed' => false], 201)]);

        $this->asAdmin()->post('/admin/support/t1/reply', ['message' => 'คำตอบ'])
            ->assertSessionHas('success', fn ($m) => str_contains($m, 'ส่งอีเมลไม่สำเร็จ'));
    }

    public function test_admin_can_close_a_report(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->asAdmin()->patch('/admin/support/t1', ['status' => 'RESOLVED'])->assertSessionHas('success', 'ปิดเรื่องแล้ว');

        Http::assertSent(fn ($r) => $r->method() === 'PATCH' && $r['status'] === 'RESOLVED');
    }

    public function test_a_teacher_cannot_open_the_admin_support_page(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'TEACHER']])->get('/admin/support')->assertForbidden();
    }

    public function test_the_admin_bell_includes_open_reports(): void
    {
        Http::fake([
            '*/user/pending' => Http::response([], 200),
            '*/support*' => Http::response([['name' => 'สมชาย', 'message' => 'ไม่ได้รับเมล', 'createdAt' => now()->toIso8601String()]], 200),
        ]);

        $items = $this->asAdmin()->getJson('/notifications')->json('items');

        $this->assertSame('สมชาย แจ้งปัญหา: ไม่ได้รับเมล', $items[0]['text']);
        $this->assertSame('/admin/support', $items[0]['url']);
    }
}
