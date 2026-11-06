<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * หน้าแรกพาไปหน้า login ของนักศึกษา
 * เทสเดิมจาก Laravel คาดว่าได้ 200 ซึ่งไม่ตรงกับแอปนี้ จึง fail มาตลอด
 */
class ExampleTest extends TestCase
{
    public function test_the_home_page_sends_visitors_to_the_student_login(): void
    {
        $this->get('/')->assertRedirect('/login/student');
    }

    public function test_the_student_login_page_opens(): void
    {
        $this->withoutVite()->get('/login/student')->assertOk();
    }
}
