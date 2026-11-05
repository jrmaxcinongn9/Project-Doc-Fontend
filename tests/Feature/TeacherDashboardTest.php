<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * อาจารย์ขอตัวเลขต่อชิ้นงาน ส่งกี่คน ยังไม่ส่งกี่คน เป็นกี่เปอร์เซ็นต์
 * ให้คะแนนแล้วกี่คน และบอกว่าเมนูครูควรมีสองเมนู
 */
class TeacherDashboardTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 'teacher@mail.kmutt.ac.th'],
        ]);
    }

    private function fake(array $assignments, array $files = []): void
    {
        Http::fake([
            '*/assignment/me' => Http::response($assignments, 200),
            '*/files' => Http::response($files, 200),
        ]);
    }

    private function assignment(array $over = []): array
    {
        return array_merge([
            '_id' => 'a1',
            'assignment_name' => 'รายงานสัปดาห์ที่ 1',
            'max_score' => 10,
            'students' => [
                ['student_id' => 'stu1', 'score' => 8],
                ['student_id' => 'stu2', 'score' => null],
                ['student_id' => 'stu3', 'score' => null],
                ['student_id' => 'stu4', 'score' => null],
            ],
        ], $over);
    }

    public function test_it_counts_submissions_from_files_not_from_status(): void
    {
        // students[].status ตั้งเป็น PENDING ตอนสร้างงานแล้วไม่เคยถูกเขียนทับ
        // ใช้เป็นตัวนับไม่ได้ ต้องนับจากไฟล์ที่ส่งเข้ามาจริง
        $this->fake([$this->assignment()], [
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1'],
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu2'],
        ]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')
            ->assertOk()->viewData('rows');

        $this->assertSame(2, $rows[0]->submitted);
        $this->assertSame(2, $rows[0]->notSubmitted);
    }

    public function test_several_files_from_one_person_count_once(): void
    {
        $this->fake([$this->assignment()], [
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1'],
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1'],
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1'],
        ]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')->viewData('rows');

        $this->assertSame(1, $rows[0]->submitted);
    }

    public function test_files_from_another_assignment_are_not_counted(): void
    {
        $this->fake([$this->assignment()], [
            ['assignment_id' => 'other', 'uploaded_by_id' => 'stu1'],
        ]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')->viewData('rows');

        $this->assertSame(0, $rows[0]->submitted);
    }

    public function test_it_reports_the_submitted_percentage(): void
    {
        $this->fake([$this->assignment()], [
            ['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1'],
        ]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')->viewData('rows');

        $this->assertSame(25, $rows[0]->submittedPercent);
    }

    public function test_it_counts_how_many_have_been_graded(): void
    {
        $this->fake([$this->assignment()]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')->viewData('rows');

        $this->assertSame(1, $rows[0]->graded);
        $this->assertSame(25, $rows[0]->gradedPercent);
    }

    public function test_a_zero_counts_as_graded(): void
    {
        $this->fake([$this->assignment(['students' => [['student_id' => 'stu1', 'score' => 0]]])]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')->viewData('rows');

        $this->assertSame(1, $rows[0]->graded);
    }

    public function test_an_assignment_with_no_students_does_not_divide_by_zero(): void
    {
        $this->fake([$this->assignment(['students' => []])]);

        $rows = $this->asTeacher()->withoutVite()->get('/teacher/dashboard')
            ->assertOk()->viewData('rows');

        $this->assertSame(0, $rows[0]->submittedPercent);
        $this->assertSame(0, $rows[0]->gradedPercent);
    }

    public function test_the_page_shows_the_numbers(): void
    {
        $this->fake([$this->assignment()], [['assignment_id' => 'a1', 'uploaded_by_id' => 'stu1']]);

        $this->asTeacher()->withoutVite()->get('/teacher/dashboard')
            ->assertSee('รายงานสัปดาห์ที่ 1', false)
            ->assertSee('1 / 4 คน', false);
    }

    public function test_the_menu_now_has_both_items(): void
    {
        $this->fake([]);

        $this->asTeacher()->withoutVite()->get('/teacher/dashboard')
            ->assertSee('ภาพรวมงาน', false)
            ->assertSee('จัดการห้องเรียน', false);
    }

    public function test_an_api_failure_does_not_crash_the_page(): void
    {
        Http::fake(['*/assignment/me' => Http::response('boom', 500)]);

        $this->asTeacher()->withoutVite()->get('/teacher/dashboard')
            ->assertOk()
            ->assertSee('ดึงรายการงานไม่สำเร็จ', false);
    }

    public function test_a_student_cannot_open_it(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->get('/teacher/dashboard')
            ->assertForbidden();
    }
}
