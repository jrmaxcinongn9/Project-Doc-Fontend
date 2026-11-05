<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * คะแนนเต็มคือค่าที่ปลดล็อกการให้คะแนนรายคน
 * API บังคับว่าต้องมีก่อนจึงจะใส่คะแนนได้
 *
 * แต่ไม่มีฟอร์มไหนในระบบส่งค่านี้เลย ทุกงานจึงไม่มีคะแนนเต็ม
 * ช่องกรอกคะแนนถูกปิดตลอด ครูให้คะแนนไม่ได้จริงสักงาน
 */
class MaxScoreFromFormsTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 'teacher@mail.kmutt.ac.th'],
        ]);
    }

    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    public function test_a_teacher_creating_an_assignment_sends_the_maximum(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'a1'], 201)]);

        $this->asTeacher()->post('/teacher/classroom/c1/assignment', [
            'assignment_name' => 'รายงานสัปดาห์ที่ 1',
            'due_date' => '2026-12-01T23:59',
            'max_score' => 10,
        ]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/assignment')
            && ($r->data()['max_score'] ?? null) === 10);
    }

    public function test_leaving_it_blank_sends_nothing_rather_than_zero(): void
    {
        // ศูนย์จะถูก API ปฏิเสธ เพราะคะแนนเต็มต้องมากกว่าศูนย์
        Http::fake(['*' => Http::response(['_id' => 'a1'], 201)]);

        $this->asTeacher()->post('/teacher/classroom/c1/assignment', [
            'assignment_name' => 'งานไม่คิดคะแนน',
            'due_date' => '2026-12-01T23:59',
            'max_score' => '',
        ]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/assignment')
            && !array_key_exists('max_score', $r->data()));
    }

    public function test_a_teacher_can_set_the_maximum_afterwards(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asTeacher()->put('/teacher/assignment/a1', [
            'assignment_name' => 'รายงาน',
            'due_date' => '2026-12-01T23:59',
            'max_score' => 20,
        ]);

        Http::assertSent(fn($r) => $r->method() === 'PUT'
            && ($r->data()['max_score'] ?? null) === 20);
    }

    public function test_an_admin_creating_an_assignment_sends_the_maximum(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'a1'], 201)]);

        $this->asAdmin()->post('/admin/classroom/assignment', [
            'assignment_name' => 'รายงาน',
            'classroom_id' => 'c1',
            'max_score' => 15,
        ]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/assignment')
            && ($r->data()['max_score'] ?? null) === 15);
    }

    public function test_an_admin_can_set_the_maximum_afterwards(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asAdmin()->put('/admin/assignment/a1', [
            'assignment_name' => 'รายงาน',
            'max_score' => 25,
        ]);

        Http::assertSent(fn($r) => $r->method() === 'PUT'
            && ($r->data()['max_score'] ?? null) === 25);
    }

    public function test_editing_an_assignment_without_touching_the_maximum_leaves_it_alone(): void
    {
        // ไม่ส่งคีย์ไปเลย ดีกว่าส่ง null ซึ่งจะล้างคะแนนเต็มทิ้ง
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asAdmin()->put('/admin/assignment/a1', ['assignment_name' => 'ชื่อใหม่']);

        Http::assertSent(fn($r) => !array_key_exists('max_score', $r->data()));
    }
}
