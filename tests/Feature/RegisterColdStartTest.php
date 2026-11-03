<?php

namespace Tests\Feature;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Render free tier พักเครื่องเมื่อไม่มีคนใช้ คำขอแรกหลังพักจึงช้ามาก
 * เทสต์ชุดนี้กันไม่ให้หน้าสมัครไปตัดการรอทิ้งเร็วเกินไป
 * และกันไม่ให้ข้อความที่ผู้ใช้เห็นกลับไปโทษฐานข้อมูลเหมือนเดิม
 */
class RegisterColdStartTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function payload(): array
    {
        return [
            'name'                  => 'Somchai Jai',
            'student_id'            => '65130500001',
            'email'                 => 'somchai@mail.kmutt.ac.th',
            'password'              => 'secret1',
            'password_confirmation' => 'secret1',
            'category'              => 'SOFTWARE',
            'major'                 => 'CPE',
        ];
    }

    public function test_connection_failure_asks_the_user_to_try_again(): void
    {
        Http::fake(function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $response = $this->post('/register', $this->payload());

        $response->assertSessionHasErrors('register');

        $errors = session('errors')->get('register');
        $this->assertSame(
            'เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณารอสักครู่แล้วกดสมัครอีกครั้ง',
            $errors[0],
        );
    }

    public function test_connection_failure_keeps_what_the_user_typed(): void
    {
        Http::fake(function () {
            throw new ConnectionException('cURL error 28: Operation timed out');
        });

        $this->post('/register', $this->payload());

        // ต้องไม่ให้ผู้ใช้กรอกฟอร์มใหม่ทั้งใบเพียงเพราะเซิร์ฟเวอร์ตื่นช้า
        $this->assertSame('somchai@mail.kmutt.ac.th', session('_old_input.email'));
        $this->assertSame('65130500001', session('_old_input.student_id'));
    }

    public function test_waiting_window_is_long_enough_for_a_cold_start(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload())
            ->assertRedirect('/login/student');

        // ค่าปริยายของ Laravel คือ 30 วินาที ซึ่งสั้นกว่าเวลาที่ Render
        // ใช้ปลุกเครื่อง จึงต้องยืดออกไปในคอนโทรลเลอร์
        $source = file_get_contents(app_path('Http/Controllers/RegisterController.php'));
        $this->assertStringContainsString('->timeout(120)', $source);
        $this->assertStringContainsString('->connectTimeout(30)', $source);
    }
}
