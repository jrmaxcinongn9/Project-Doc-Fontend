<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูทุกคนเคยเห็นนักศึกษาทุกคน และหน้านี้เคยเป็นข้อมูลปลอม
 * ที่เขียนไว้ใน view ตรง ๆ ครูทุกคนจึงเห็นชุดเดียวกันหมด
 */
class TeacherMyStudentsTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 'teacher@mail.kmutt.ac.th'],
        ]);
    }

    public function test_the_page_lists_the_students_the_api_returns(): void
    {
        Http::fake([
            '*/teacher/my-students' => Http::response([
                'category' => 'SOFTWARE',
                'total' => 1,
                'students' => [[
                    '_id' => 'u1',
                    'name' => 'Somchai Jaidee',
                    'email' => 'somchai@mail.kmutt.ac.th',
                    'student_id' => '66070500011',
                    'major' => 'CPE',
                ]],
            ], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertOk()
            ->assertSee('somchai@mail.kmutt.ac.th', false)
            ->assertSee('66070500011', false);
    }

    public function test_the_page_says_which_category_the_list_came_from(): void
    {
        Http::fake([
            '*/teacher/my-students' => Http::response([
                'category' => 'SOFTWARE',
                'students' => [],
            ], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertSee('ซอฟต์แวร์ / พัฒนาโปรแกรม', false);
    }

    public function test_no_fake_students_are_left_in_the_page(): void
    {
        Http::fake([
            '*/teacher/my-students' => Http::response(['category' => 'SOFTWARE', 'students' => []], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertDontSee('somchai@demo.com', false)
            ->assertDontSee('65309010101', false);
    }

    public function test_a_teacher_without_a_category_is_told_why_the_list_is_empty(): void
    {
        Http::fake([
            '*/teacher/my-students' => Http::response([
                'category' => null,
                'students' => [],
                'message' => 'บัญชีครูนี้ยังไม่ได้ตั้งสาขาความเชี่ยวชาญ',
            ], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertOk()
            ->assertSee('ยังไม่ได้ตั้งสาขา', false);
    }

    public function test_an_empty_category_reads_differently_from_an_empty_list(): void
    {
        Http::fake([
            '*/teacher/my-students' => Http::response(['category' => 'DATA', 'students' => []], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertSee('ยังไม่มีนักศึกษาในสาขานี้ที่ผ่านการอนุมัติ', false)
            ->assertDontSee('ยังไม่ได้ตั้งสาขา', false);
    }

    public function test_an_api_failure_does_not_crash_the_page(): void
    {
        Http::fake(['*/teacher/my-students' => Http::response('boom', 500)]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertOk()
            ->assertSee('ดึงรายชื่อนักศึกษาไม่สำเร็จ', false);
    }

    public function test_the_menu_links_to_the_page(): void
    {
        Http::fake(['*/teacher/my-students' => Http::response(['category' => 'DATA', 'students' => []], 200)]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertSee('นักศึกษาของฉัน', false)
            ->assertSee('/teacher/students', false);
    }

    public function test_a_student_cannot_open_the_page(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->get('/teacher/students')
            ->assertForbidden();
    }

    public function test_a_visitor_is_sent_to_the_teacher_login(): void
    {
        $this->get('/teacher/students')->assertRedirect('/login/teacher');
    }
}
