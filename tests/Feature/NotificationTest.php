<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * แจ้งเตือนในเว็บ คำนวณจากข้อมูลจริง แยกตามบทบาท
 */
class NotificationTest extends TestCase
{
    private function as(string $role): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => $role, 'email' => 'x@mail.kmutt.ac.th', '_id' => 'me'],
        ]);
    }

    public function test_a_student_sees_new_grades_and_work_due_soon(): void
    {
        Http::fake([
            '*/assignment/my-scores' => Http::response([
                ['assignment_name' => 'งาน 1', 'score' => 8, 'max_score' => 10, 'feedback' => 'ดี', 'feedback_at' => now()->subDay()->toIso8601String()],
                ['assignment_name' => 'งานเก่า', 'score' => 5, 'feedback_at' => now()->subDays(60)->toIso8601String()],
                ['assignment_name' => 'ยังไม่ตรวจ', 'score' => null, 'feedback' => null, 'feedback_at' => null],
            ], 200),
            '*/files/my' => Http::response([['assignment_id' => 'sent']], 200),
            '*/classroom/my' => Http::response([[
                '_id' => 'r1',
                'assignments' => [
                    ['_id' => 'soon', 'assignment_name' => 'ใกล้ส่ง', 'due_date' => now()->addDays(2)->toIso8601String()],
                    ['_id' => 'sent', 'assignment_name' => 'ส่งแล้ว', 'due_date' => now()->addDay()->toIso8601String()],
                    ['_id' => 'far', 'assignment_name' => 'อีกนาน', 'due_date' => now()->addDays(20)->toIso8601String()],
                ],
            ]], 200),
        ]);

        $json = $this->as('USER')->getJson('/notifications')->assertOk()->json();
        $texts = collect($json['items'])->pluck('text')->implode('|');

        $this->assertStringContainsString('อาจารย์ตรวจ "งาน 1" แล้ว ได้ 8/10 คะแนน', $texts);
        $this->assertStringContainsString('"ใกล้ส่ง" ครบกำหนดส่ง', $texts);
        $this->assertStringNotContainsString('งานเก่า', $texts);
        $this->assertStringNotContainsString('ส่งแล้ว', $texts);
        $this->assertStringNotContainsString('อีกนาน', $texts);
        $this->assertSame(2, $json['unread']);
    }

    public function test_a_teacher_sees_new_ungraded_submissions_once_per_student(): void
    {
        Http::fake([
            '*/assignment' => Http::response([
                ['_id' => 'a1', 'assignment_name' => 'งาน 1', 'students' => [['student_id' => 's1', 'score' => null], ['student_id' => 's2', 'score' => 9]]],
            ], 200),
            '*/files' => Http::response([
                ['assignment_id' => 'a1', 'uploaded_by_id' => 's1', 'uploaded_by_name' => 'เอ', 'createdAt' => now()->subHour()->toIso8601String()],
                ['assignment_id' => 'a1', 'uploaded_by_id' => 's1', 'uploaded_by_name' => 'เอ', 'createdAt' => now()->subHours(2)->toIso8601String()],
                ['assignment_id' => 'a1', 'uploaded_by_id' => 's2', 'uploaded_by_name' => 'บี', 'createdAt' => now()->subHour()->toIso8601String()],
            ], 200),
        ]);

        $items = $this->as('TEACHER')->getJson('/notifications')->json('items');

        $this->assertCount(1, $items);
        $this->assertSame('เอ ส่ง "งาน 1" รอให้คะแนน', $items[0]['text']);
        $this->assertSame('/teacher/assignment-view/a1', $items[0]['url']);
    }

    public function test_an_admin_sees_pending_accounts(): void
    {
        Http::fake(['*/user/pending' => Http::response([
            ['name' => 'นักศึกษาใหม่', 'createdAt' => now()->subMinutes(5)->toIso8601String()],
        ], 200)]);

        $items = $this->as('ADMIN')->getJson('/notifications')->json('items');

        $this->assertSame('บัญชี นักศึกษาใหม่ รออนุมัติ', $items[0]['text']);
        $this->assertSame('/admin/pending', $items[0]['url']);
    }

    public function test_opening_the_bell_marks_everything_read(): void
    {
        Http::fake(['*/user/pending' => Http::response([
            ['name' => 'A', 'createdAt' => now()->subMinutes(5)->toIso8601String()],
        ], 200)]);

        $this->as('ADMIN')->postJson('/notifications/seen')->assertOk();

        $this->assertSame(0, $this->getJson('/notifications')->json('unread'));
    }

    public function test_not_logged_in_gets_nothing(): void
    {
        $this->getJson('/notifications')->assertStatus(401);
    }

    public function test_every_sidebar_shows_the_bell(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as('USER')->withoutVite()->get('/student/dashboard')->assertSee('id="notifBtn"', false);
        $this->as('TEACHER')->withoutVite()->get('/teacher/students')->assertSee('id="notifBtn"', false);
        $this->as('ADMIN')->withoutVite()->get('/admin/users')->assertSee('id="notifBtn"', false);
    }
}
