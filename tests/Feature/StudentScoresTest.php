<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * อาจารย์บอกว่านักเรียนต้อง View Score และ View Feedback ได้
 * เดิมไม่มีหน้านี้เลย คะแนนที่ครูให้ไปไม่มีใครเห็น
 */
class StudentScoresTest extends TestCase
{
    private function asStudent(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'USER', 'email' => 'student@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeScores(array $rows): void
    {
        Http::fake(['*/assignment/my-scores' => Http::response($rows, 200)]);
    }

    public function test_the_page_shows_a_score_and_its_maximum(): void
    {
        $this->fakeScores([[
            'assignment_name' => 'รายงานสัปดาห์ที่ 1',
            'score' => 8,
            'max_score' => 10,
            'feedback' => null,
        ]]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertOk()
            ->assertSee('รายงานสัปดาห์ที่ 1', false)
            ->assertSee('8', false)
            ->assertSee('10', false);
    }

    public function test_the_page_shows_the_teachers_comment(): void
    {
        $this->fakeScores([[
            'assignment_name' => 'รายงาน',
            'score' => 8,
            'max_score' => 10,
            'feedback' => 'เขียนสรุปได้ดี เพิ่มตัวอย่างอีกหน่อย',
            'feedback_by' => 'teacher@mail.kmutt.ac.th',
        ]]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertSee('เขียนสรุปได้ดี เพิ่มตัวอย่างอีกหน่อย', false)
            ->assertSee('teacher@mail.kmutt.ac.th', false);
    }

    public function test_ungraded_work_says_so_instead_of_showing_zero(): void
    {
        $this->fakeScores([[
            'assignment_name' => 'รายงาน',
            'score' => null,
            'max_score' => 10,
            'feedback' => null,
        ]]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertSee('ยังไม่ตรวจ', false);
    }

    public function test_the_total_counts_only_graded_work(): void
    {
        // งานที่ครูยังไม่ตรวจ ไม่ควรถูกนับเป็นศูนย์
        // ไม่อย่างนั้นยอดรวมจะดูแย่กว่าความจริงตั้งแต่ต้นเทอม
        $this->fakeScores([
            ['assignment_name' => 'ก', 'score' => 8, 'max_score' => 10],
            ['assignment_name' => 'ข', 'score' => null, 'max_score' => 10],
        ]);

        $response = $this->asStudent()->withoutVite()->get('/student/scores');

        $response->assertViewHas('totalScore', 8);
        $response->assertViewHas('totalMax', 10);
        $response->assertViewHas('graded', 1);
    }

    public function test_a_zero_counts_as_graded(): void
    {
        $this->fakeScores([['assignment_name' => 'ก', 'score' => 0, 'max_score' => 10]]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertViewHas('graded', 1)
            ->assertViewHas('totalMax', 10);
    }

    public function test_no_work_at_all_reads_clearly(): void
    {
        $this->fakeScores([]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertOk()
            ->assertSee('ยังไม่มีงานที่ต้องส่ง', false);
    }

    public function test_an_api_failure_does_not_crash_the_page(): void
    {
        Http::fake(['*/assignment/my-scores' => Http::response('boom', 500)]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertOk()
            ->assertSee('ดึงคะแนนไม่สำเร็จ', false);
    }

    public function test_the_menu_links_to_the_page(): void
    {
        $this->fakeScores([]);

        $this->asStudent()->withoutVite()->get('/student/scores')
            ->assertSee('คะแนนและความเห็น', false)
            ->assertSee('/student/scores', false);
    }

    public function test_a_visitor_is_sent_to_the_student_login(): void
    {
        $this->get('/student/scores')->assertRedirect('/login/student');
    }
}
