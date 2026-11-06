<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ใช้งานได้บนมือถือ iPad และ PC
 * เดิมเมนูด้านข้างกว้าง 256px ตายตัว บนมือถือเนื้อหาเหลือที่ราว 120px
 * และตารางงานของนักศึกษากว้าง 950px ปุ่มส่งงานหลุดขอบขวาบน iPad
 */
class ResponsiveLayoutTest extends TestCase
{
    private function as(string $role): self
    {
        return $this->withSession(['api_token' => 'x', 'user' => ['role' => $role, 'name' => 'A', 'email' => 'a@mail.kmutt.ac.th']]);
    }

    public static function pages(): array
    {
        return [
            'นักศึกษา' => ['USER', '/student/scores'],
            'อาจารย์' => ['TEACHER', '/teacher/profile'],
            'admin' => ['ADMIN', '/admin/profile'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_every_role_gets_a_phone_top_bar_and_a_sliding_menu(string $role, string $url): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as($role)->withoutVite()->get($url)
            ->assertOk()
            ->assertSee('id="sidebarOpen"', false)
            ->assertSee('id="appSidebar"', false)
            ->assertSee('-translate-x-full', false)
            ->assertSee('lg:translate-x-0', false)
            ->assertSee('data-sidebar-close', false);
    }

    public function test_teacher_and_admin_tables_stack_on_phones(): void
    {
        Http::fake(['*/teacher/my-students' => Http::response(['students' => [['_id' => 's1', 'name' => 'A']], 'category' => 'DATA'], 200)]);

        $this->as('TEACHER')->withoutVite()->get('/teacher/students')
            ->assertSee('class="stack-on-mobile', false)
            ->assertSee('table.stack-on-mobile thead { display: none; }', false);
    }

    public function test_student_work_shows_as_cards_with_a_submit_button_below_xl(): void
    {
        Http::fake([
            '*/classroom/my' => Http::response([[
                '_id' => 'r1', 'name' => 'ห้อง', 'teacher' => ['name' => 'T'],
                'assignments' => [['_id' => 'a1', 'assignment_name' => 'งาน 1', 'due_date' => now()->addDay()->toIso8601String()]],
            ]], 200),
            '*/files/my' => Http::response([], 200),
        ]);

        $html = $this->as('USER')->withoutVite()->get('/student/classrooms/r1')->getContent();

        $this->assertStringContainsString('class="xl:hidden divide-y divide-slate-100"', $html);
        $this->assertStringContainsString('class="hidden xl:block overflow-x-auto"', $html);
        // ปุ่มส่งงานมีทั้งในการ์ดและในตาราง
        $this->assertSame(2, substr_count($html, 'data-assignment-id="a1"'));
        $this->assertStringContainsString('สูงสุดไฟล์ละ 5 MB', $html);
    }

    public function test_login_header_does_not_wrap_on_phones(): void
    {
        $this->withoutVite()->get('/login/student')
            ->assertSee('hidden sm:inline text-lg', false)
            ->assertSee('whitespace-nowrap', false);
    }
}
