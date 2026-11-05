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

    private function fakeAssignment(array $over = []): void
    {
        $assignment = array_merge([
            '_id' => 'a1',
            'assignment_name' => 'รายงานสัปดาห์ที่ 1',
            'classroom' => 'c1',
            'max_score' => 10,
            'students' => [
                ['student_id' => 'stu1', 'name' => 'Somchai', 'score' => 7, 'feedback' => 'ดีขึ้นมาก'],
                ['student_id' => 'stu2', 'name' => 'Anucha', 'score' => null, 'feedback' => null],
            ],
        ], $over);

        Http::fake([
            '*/files' => Http::response([], 200),
            '*/assignment' => Http::response([$assignment], 200),
            '*/classroom' => Http::response([['_id' => 'c1', 'name' => 'ห้อง A', 'students' => ['stu1', 'stu2']]], 200),
            '*/user' => Http::response([
                ['_id' => 'stu1', 'name' => 'Somchai', 'email' => 'somchai@mail.kmutt.ac.th', 'role' => 'USER'],
                ['_id' => 'stu2', 'name' => 'Anucha', 'email' => 'anucha@mail.kmutt.ac.th', 'role' => 'USER'],
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
}
