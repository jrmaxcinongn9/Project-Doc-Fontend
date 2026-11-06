<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * หน้ารายละเอียดนักศึกษาเดิมเป็นข้อมูลตัวอย่าง ทุกคนเห็น "ทดสอบ นักศึกษา" กับงาน Week 1-3
 * ตอนนี้ดึงนักศึกษาจริง ห้องที่อยู่ งานทุกชิ้น สถานะ ไฟล์ และคะแนน
 */
class TeacherStudentDetailTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 't@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeApi(): void
    {
        $past = now()->subDays(3)->toIso8601String();
        $future = now()->addDays(5)->toIso8601String();

        Http::fake([
            '*/user' => Http::response([
                ['_id' => 'stu1', 'name' => 'Somchai Jaidee', 'email' => 'somchai@mail.kmutt.ac.th', 'student_id' => '66070500001',
                 'major' => 'ครุศาสตร์ไฟฟ้า', 'category' => 'SOFTWARE', 'role' => 'USER'],
                ['_id' => 't1', 'name' => 'Teacher One', 'role' => 'TEACHER'],
            ], 200),
            '*/classroom' => Http::response([
                ['_id' => 'r1', 'name' => 'ห้องฝึกงาน A', 'major' => 'ครุศาสตร์ไฟฟ้า', 'createdBy' => 't1', 'students' => ['stu1']],
                ['_id' => 'r2', 'name' => 'ห้องที่ไม่เกี่ยว', 'major' => 'อื่น', 'createdBy' => 't1', 'students' => ['someone']],
            ], 200),
            '*/assignment' => Http::response([
                ['_id' => 'a1', 'assignment_name' => 'งานส่งแล้ว', 'classroom' => ['_id' => 'r1'], 'due_date' => $future, 'max_score' => 10,
                 'students' => [['student_id' => 'stu1', 'score' => 8, 'feedback' => 'ดีมาก']]],
                ['_id' => 'a2', 'assignment_name' => 'งานเลยกำหนด', 'classroom' => 'r1', 'due_date' => $past, 'max_score' => 10, 'students' => []],
                ['_id' => 'a3', 'assignment_name' => 'งานยังไม่ถึงกำหนด', 'classroom' => 'r1', 'due_date' => $future, 'students' => []],
                ['_id' => 'a9', 'assignment_name' => 'งานห้องอื่น', 'classroom' => 'r2', 'students' => []],
            ], 200),
            '*/files' => Http::response([
                ['_id' => 'f1', 'assignment_id' => 'a1', 'uploaded_by_id' => 'stu1', 'original_name' => 'รายงาน.pdf',
                 'mimetype' => 'application/pdf', 'createdAt' => now()->subDay()->toIso8601String()],
                ['_id' => 'f9', 'assignment_id' => 'a1', 'uploaded_by_id' => 'other', 'original_name' => 'ของคนอื่น.pdf'],
            ], 200),
        ]);
    }

    public function test_the_list_links_each_student_to_their_own_page(): void
    {
        Http::fake(['*/teacher/my-students' => Http::response([
            'students' => [['_id' => 'stu1', 'name' => 'Somchai']], 'category' => 'SOFTWARE',
        ], 200)]);

        $this->asTeacher()->withoutVite()->get('/teacher/students')
            ->assertSee('href="/teacher/students/stu1"', false)
            ->assertDontSee('href="/teacher/detail"', false);
    }

    public function test_the_page_shows_the_real_student(): void
    {
        $this->fakeApi();

        $this->asTeacher()->withoutVite()->get('/teacher/students/stu1')
            ->assertOk()
            ->assertSee('Somchai Jaidee', false)
            ->assertSee('66070500001', false)
            ->assertSee('somchai@mail.kmutt.ac.th', false)
            ->assertDontSee('ทดสอบ นักศึกษา', false)
            ->assertDontSee('Week 1', false);
    }

    public function test_only_work_from_the_students_rooms_is_listed(): void
    {
        $this->fakeApi();

        $this->asTeacher()->withoutVite()->get('/teacher/students/stu1')
            ->assertSee('ห้องฝึกงาน A', false)
            ->assertSee('งานส่งแล้ว', false)
            ->assertDontSee('งานห้องอื่น', false)
            ->assertDontSee('ห้องที่ไม่เกี่ยว', false);
    }

    public function test_each_assignment_gets_the_right_status(): void
    {
        $this->fakeApi();

        $html = $this->asTeacher()->withoutVite()->get('/teacher/students/stu1')->getContent();

        $this->assertStringContainsString('ส่งแล้ว', $html);
        $this->assertStringContainsString('เลยกำหนด', $html);
        $this->assertStringContainsString('ยังไม่ส่ง', $html);
        $this->assertStringContainsString('เลยกำหนดมา 3 วัน', $html);
    }

    public function test_only_this_students_files_and_score_are_shown(): void
    {
        $this->fakeApi();

        $this->asTeacher()->withoutVite()->get('/teacher/students/stu1')
            ->assertSee('/teacher/view/f1', false)
            ->assertDontSee('ของคนอื่น.pdf', false)
            ->assertSee('ดีมาก', false)
            ->assertSee('แก้คะแนน', false);
    }

    public function test_dates_are_thai_buddhist_years(): void
    {
        $this->fakeApi();

        $year = now()->timezone('Asia/Bangkok')->year + 543;

        $this->asTeacher()->withoutVite()->get('/teacher/students/stu1')
            ->assertSee((string) $year, false);
    }

    public function test_an_unknown_student_is_a_404(): void
    {
        $this->fakeApi();

        $this->asTeacher()->withoutVite()->get('/teacher/students/nobody')->assertNotFound();
    }

    public function test_the_old_mock_url_goes_back_to_the_list(): void
    {
        $this->asTeacher()->get('/teacher/detail')->assertRedirect('/teacher/students');
    }
}
