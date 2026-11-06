<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูต้องให้ feedback กลับได้ ไม่ใช่รับงานเฉย ๆ และต้องให้คะแนนได้
 * ฟิลด์มีในฐานข้อมูลแล้ว แต่ไม่เคยมีหน้าจอไหนเขียนลงไปได้
 */
class TeacherGradingUiTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 'teacher@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeAssignment(array $over = [], array $classStudents = ['stu1', 'stu2'], bool $populated = false): void
    {
        // API จริง populate classroom มาเป็น object ทั้งก้อน
        $classroom = ['_id' => 'c1', 'name' => 'ห้อง A', 'students' => $classStudents];

        $assignment = array_merge([
            '_id' => 'a1',
            'assignment_name' => 'รายงานสัปดาห์ที่ 1',
            'classroom' => $populated ? $classroom : 'c1',
            'max_score' => 10,
            'students' => [
                ['student_id' => 'stu1', 'name' => 'Somchai', 'score' => 7, 'feedback' => 'ดีขึ้นมาก'],
                ['student_id' => 'stu2', 'name' => 'Anucha', 'score' => null, 'feedback' => null],
            ],
        ], $over);

        Http::fake([
            '*/files' => Http::response([], 200),
            '*/assignment' => Http::response([$assignment], 200),
            '*/classroom' => Http::response([$classroom], 200),
            '*/user' => Http::response([
                ['_id' => 'stu1', 'name' => 'Somchai', 'email' => 'somchai@mail.kmutt.ac.th', 'role' => 'USER'],
                ['_id' => 'stu2', 'name' => 'Anucha', 'email' => 'anucha@mail.kmutt.ac.th', 'role' => 'USER'],
                ['_id' => 'stu3', 'name' => 'Latecomer', 'email' => 'late@mail.kmutt.ac.th', 'role' => 'USER'],
            ], 200),
            '*' => Http::response(['ok' => true], 200),
        ]);
    }

    public function test_the_page_lists_a_row_for_each_student(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertOk()
            ->assertSee('Somchai', false)
            ->assertSee('Anucha', false);
    }

    public function test_an_existing_score_and_comment_are_filled_in(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertSee('value="7"', false)
            ->assertSee('ดีขึ้นมาก', false);
    }

    public function test_the_page_states_the_maximum_score(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertSee('คะแนนเต็มของงานชิ้นนี้ 10 คะแนน', false)
            ->assertSee('max="10"', false);
    }

    public function test_without_a_maximum_the_score_box_is_disabled_and_explained(): void
    {
        $this->fakeAssignment(['max_score' => null]);

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertSee('ยังไม่ได้ตั้งคะแนนเต็ม', false)
            ->assertSee('disabled', false);
    }

    public function test_saving_sends_the_score_and_comment_to_the_api(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->patch('/teacher/assignment/a1/students/stu1/grade', [
            'score' => 9,
            'feedback' => 'เพิ่มบทสรุปอีกนิด',
        ])->assertSessionHas('success');

        Http::assertSent(fn($r) => str_contains($r->url(), '/assignment/a1/students/stu1/grade')
            && $r->method() === 'PATCH'
            && $r->data()['score'] === 9
            && $r->data()['feedback'] === 'เพิ่มบทสรุปอีกนิด');
    }

    public function test_an_empty_score_box_clears_the_mark(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->patch('/teacher/assignment/a1/students/stu1/grade', [
            'score' => '',
            'feedback' => 'ยังไม่ตรวจ',
        ]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/grade')
            && $r->data()['score'] === null);
    }

    public function test_zero_is_a_real_mark_not_an_empty_box(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()->patch('/teacher/assignment/a1/students/stu1/grade', ['score' => 0]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/grade')
            && $r->data()['score'] === 0);
    }

    public function test_a_negative_score_is_refused_before_it_is_sent(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()
            ->patch('/teacher/assignment/a1/students/stu1/grade', ['score' => -5])
            ->assertSessionHasErrors('score');

        Http::assertNotSent(fn($r) => str_contains($r->url(), '/grade'));
    }

    public function test_the_api_refusal_is_shown_to_the_teacher(): void
    {
        // ตั้ง fake ใหม่ทั้งชุด ไม่เรียก fakeAssignment
        // เพราะ Http::fake รอบหลังจะต่อท้ายของเดิม ตัว '*' ที่กว้างกว่าจะชนะก่อน
        Http::fake([
            '*/grade' => Http::response(['message' => 'คะแนนต้องไม่เกินคะแนนเต็มของงานชิ้นนี้ ซึ่งคือ 10'], 400),
        ]);

        $this->asTeacher()
            ->patch('/teacher/assignment/a1/students/stu1/grade', ['score' => 50])
            ->assertSessionHasErrors('grade');
    }

    public function test_a_student_cannot_reach_the_grading_route(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->patch('/teacher/assignment/a1/students/stu1/grade', ['score' => 10])
            ->assertForbidden();
    }

    public function test_a_student_added_to_the_room_after_the_assignment_still_gets_a_row(): void
    {
        $this->fakeAssignment([], ['stu1', 'stu2', 'stu3']);

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertOk()
            ->assertSee('Latecomer', false)
            ->assertSee('name="rows[stu3][score]"', false);
    }

    public function test_a_populated_classroom_is_still_found(): void
    {
        $this->fakeAssignment([], ['stu1', 'stu2', 'stu3'], true);

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertSee('Latecomer', false)
            ->assertSee('ทั้งหมด 3 คน', false);
    }

    public function test_a_student_is_not_listed_twice(): void
    {
        $this->fakeAssignment();

        $html = $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')->getContent();

        $this->assertSame(1, substr_count($html, 'name="rows[stu1][score]"'));
    }

    public function test_the_page_offers_to_set_the_maximum_when_it_is_missing(): void
    {
        $this->fakeAssignment(['max_score' => null]);

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertSee('/teacher/assignment/a1/max-score', false)
            ->assertSee('ตั้งคะแนนเต็ม', false);
    }

    public function test_setting_the_maximum_sends_only_that_field(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()
            ->patch('/teacher/assignment/a1/max-score', ['max_score' => 20])
            ->assertSessionHas('success');

        // ส่งแค่ max_score ถ้าส่ง assignment_name หรือ due_date เป็น null ไปด้วย
        // API จะลบชื่องานและวันส่งทิ้ง
        Http::assertSent(fn($r) => str_ends_with($r->url(), '/assignment/a1')
            && $r->method() === 'PUT'
            && $r->data() === ['max_score' => 20]);
    }

    public function test_a_zero_or_missing_maximum_is_refused(): void
    {
        $this->fakeAssignment();

        $this->asTeacher()
            ->patch('/teacher/assignment/a1/max-score', ['max_score' => 0])
            ->assertSessionHasErrors('max_score');

        Http::assertNotSent(fn($r) => $r->method() === 'PUT');
    }

    public function test_a_student_cannot_set_the_maximum(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->patch('/teacher/assignment/a1/max-score', ['max_score' => 10])
            ->assertForbidden();
    }
}
