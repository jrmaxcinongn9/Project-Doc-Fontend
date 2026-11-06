<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * ปุ่มรูปตาดูรหัสผ่าน ต้องมีทุกหน้าที่มีช่องรหัสผ่าน ทั้งตอนสมัครและเข้าสู่ระบบ
 */
class PasswordToggleTest extends TestCase
{
    public static function pages(): array
    {
        return [
            'สมัคร' => ['/register'],
            'login นักศึกษา' => ['/login/student'],
            'login อาจารย์' => ['/login/teacher'],
            'login admin' => ['/login/admin'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_the_page_has_a_password_field_and_the_toggle_script(string $url): void
    {
        $this->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('type="password"', false)
            ->assertSee("btn.className = 'password-toggle'", false)
            ->assertSee('แสดงรหัสผ่าน', false);
    }
}
