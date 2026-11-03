<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AccountGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // หน้า verify-email ใช้ layout ที่โหลด asset ผ่าน Vite
        // เทสต์ไม่ได้ build asset จึงต้องปิดไว้ ไม่งั้นจะพังเพราะ manifest ไม่มี
        $this->withoutVite();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name'                  => 'Somchai Jai',
            'student_id'            => '65130500001',
            'email'                 => 'somchai@mail.kmutt.ac.th',
            'password'              => 'secret1',
            'password_confirmation' => 'secret1',
            'category'              => 'SOFTWARE',
            'major'                 => 'CPE',
        ], $override);
    }

    public function test_academic_year_is_no_longer_required_or_sent(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload())
            ->assertSessionHasNoErrors()
            ->assertRedirect('/login/student');

        Http::assertSent(function ($request) {
            return ! array_key_exists('academicYear', $request->data());
        });
    }

    public function test_category_is_required(): void
    {
        Http::fake();

        $this->post('/register', $this->payload(['category' => '']))
            ->assertSessionHasErrors('category');

        Http::assertNothingSent();
    }

    public function test_unknown_category_is_rejected(): void
    {
        Http::fake();

        $this->post('/register', $this->payload(['category' => 'ROCKET']))
            ->assertSessionHasErrors('category');

        Http::assertNothingSent();
    }

    public function test_category_reaches_the_api(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload(['category' => 'NETWORK']));

        Http::assertSent(function ($request) {
            return $request['category'] === 'NETWORK';
        });
    }

    public function test_success_message_tells_the_user_to_verify_and_wait(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $response = $this->post('/register', $this->payload());

        $response->assertRedirect('/login/student');
        $this->assertStringContainsString('ยืนยัน', session('success'));
        $this->assertStringContainsString('อนุมัติ', session('success'));
    }

    public function test_verify_email_page_reports_success(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ยืนยันอีเมลเรียบร้อย'], 200)]);

        $this->get('/verify-email?token=abc123')
            ->assertOk()
            ->assertSee('ยืนยันอีเมลสำเร็จ', false);
    }

    public function test_verify_email_page_reports_failure(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ลิงก์ยืนยันหมดอายุแล้ว'], 400)]);

        $this->get('/verify-email?token=expired')
            ->assertOk()
            ->assertSee('ลิงก์ยืนยันหมดอายุแล้ว', false);
    }

    public function test_verify_email_without_token_does_not_call_the_api(): void
    {
        Http::fake();

        $this->get('/verify-email')
            ->assertOk()
            ->assertSee('ไม่พบรหัสยืนยันใน URL', false);

        Http::assertNothingSent();
    }
}
