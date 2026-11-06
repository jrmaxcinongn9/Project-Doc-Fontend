<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูกรอกคะแนนหลายคนแล้วกดบันทึกครั้งเดียว
 * เดิมกดบันทึกแถวหนึ่งแล้วหน้าโหลดใหม่ คะแนนที่กรอกในแถวอื่นหายหมด
 */
class TeacherSaveAllGradesTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 't@mail.kmutt.ac.th'],
        ]);
    }

    private function row(?string $score, ?string $fb = '', ?string $origScore = '', ?string $origFb = ''): array
    {
        return ['score' => $score, 'feedback' => $fb, 'orig_score' => $origScore, 'orig_feedback' => $origFb];
    }

    public function test_the_page_has_one_form_and_a_save_all_button(): void
    {
        Http::fake([
            '*/files' => Http::response([], 200),
            '*/assignment' => Http::response([[
                '_id' => 'a1', 'assignment_name' => 'งาน', 'classroom' => 'c1', 'max_score' => 10,
                'students' => [['student_id' => 's1', 'name' => 'A'], ['student_id' => 's2', 'name' => 'B']],
            ]], 200),
            '*/classroom' => Http::response([['_id' => 'c1', 'students' => ['s1', 's2']]], 200),
            '*/user' => Http::response([], 200),
        ]);

        $html = $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')->getContent();

        $this->assertSame(1, substr_count($html, 'action="/teacher/assignment/a1/grades"'));
        $this->assertStringContainsString('บันทึกทั้งหมด', $html);
        $this->assertStringContainsString('name="rows[s1][score]"', $html);
        $this->assertStringContainsString('name="rows[s2][score]"', $html);
        $this->assertStringContainsString('beforeunload', $html);
    }

    public function test_only_changed_rows_are_sent(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asTeacher()->patch('/teacher/assignment/a1/grades', ['rows' => [
            's1' => $this->row('8', 'ดี', '', ''),
            's2' => $this->row('5', 'เดิม', '5', 'เดิม'),
            's3' => $this->row('', 'เพิ่มความเห็น', '', ''),
        ]])->assertSessionHas('success', 'บันทึกคะแนนและความเห็นแล้ว 2 คน');

        Http::assertSentCount(2);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/students/s1/grade') && $r['score'] === 8 && $r['feedback'] === 'ดี');
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/students/s3/grade') && $r['score'] === null);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '/students/s2/'));
    }

    public function test_clearing_a_score_sends_null(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asTeacher()->patch('/teacher/assignment/a1/grades', ['rows' => [
            's1' => $this->row('', '', '7', ''),
        ]]);

        Http::assertSent(fn ($r) => $r['score'] === null);
    }

    public function test_nothing_changed_sends_nothing(): void
    {
        Http::fake();

        $this->asTeacher()->patch('/teacher/assignment/a1/grades', ['rows' => [
            's1' => $this->row('5', 'x', '5', 'x'),
        ]])->assertSessionHas('success', 'ไม่มีอะไรเปลี่ยน');

        Http::assertNothingSent();
    }

    public function test_one_failure_does_not_stop_the_others_and_is_reported(): void
    {
        Http::fake([
            '*/students/s1/grade' => Http::response(['message' => 'คะแนนต้องไม่เกินคะแนนเต็มของงานชิ้นนี้ ซึ่งคือ 10'], 400),
            '*' => Http::response(['ok' => true], 200),
        ]);

        $this->asTeacher()->patch('/teacher/assignment/a1/grades', ['rows' => [
            's1' => $this->row('50'),
            's2' => $this->row('9'),
        ]])
            ->assertSessionHas('success', 'บันทึกคะแนนและความเห็นแล้ว 1 คน')
            ->assertSessionHasErrors('grade');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/students/s2/grade'));
    }

    public function test_a_negative_score_is_refused_before_anything_is_sent(): void
    {
        Http::fake();

        $this->asTeacher()->patch('/teacher/assignment/a1/grades', ['rows' => ['s1' => $this->row('-1')]])
            ->assertSessionHasErrors('rows.s1.score');

        Http::assertNothingSent();
    }

    public function test_a_student_cannot_use_it(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->patch('/teacher/assignment/a1/grades', ['rows' => []])
            ->assertForbidden();
    }
}
