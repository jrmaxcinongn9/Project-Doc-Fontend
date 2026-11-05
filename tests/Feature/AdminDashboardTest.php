<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * อาจารย์ขอให้ admin เห็นทั้งระบบและแยกต่อสาขา
 * ว่าสาขานี้มีงานกี่งาน ครูกี่คน นักเรียนกี่คน
 *
 * ของเดิมนับแค่นักศึกษากับครู ส่วนเอกสารและงานฝึกเขียนศูนย์ตายตัวไว้ในโค้ด
 */
class AdminDashboardTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    private function users(): array
    {
        return [
            ['_id' => 's1', 'role' => 'USER', 'category' => 'SOFTWARE'],
            ['_id' => 's2', 'role' => 'USER', 'category' => 'SOFTWARE'],
            ['_id' => 's3', 'role' => 'USER', 'category' => 'NETWORK'],
            ['_id' => 't1', 'role' => 'TEACHER', 'category' => 'SOFTWARE'],
            ['_id' => 'a1', 'role' => 'ADMIN'],
        ];
    }

    private function fake(array $over = []): void
    {
        Http::fake(array_merge([
            '*/user' => Http::response($this->users(), 200),
            '*/assignment' => Http::response([
                ['_id' => 'as1', 'students' => [['student_id' => 's1'], ['student_id' => 's2']]],
                ['_id' => 'as2', 'students' => [['student_id' => 's3']]],
            ], 200),
            '*/files' => Http::response([
                ['assignment_id' => 'as1', 'uploaded_by_id' => 's1'],
                ['assignment_id' => 'as1', 'uploaded_by_id' => 's1'],
                ['assignment_id' => 'as2', 'uploaded_by_id' => 's3'],
            ], 200),
        ], $over));
    }

    public function test_it_counts_students_and_teachers_without_counting_admins(): void
    {
        $this->fake();

        $stats = $this->asAdmin()->withoutVite()->get('/admin/dashboard')
            ->assertOk()->viewData('stats');

        $this->assertSame(3, $stats['students']);
        $this->assertSame(1, $stats['teachers']);
    }

    public function test_it_counts_assignments_for_real(): void
    {
        // ช่องนี้เคยเขียนเลขศูนย์ตายตัวไว้ในโค้ด
        $this->fake();

        $stats = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('stats');

        $this->assertSame(2, $stats['assignments']);
    }

    public function test_several_files_from_one_person_count_as_one_submission(): void
    {
        $this->fake();

        $stats = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('stats');

        $this->assertSame(2, $stats['submissions']);
    }

    public function test_it_breaks_students_down_by_category(): void
    {
        $this->fake();

        $rows = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('byCategory');
        $software = $rows->firstWhere('key', 'SOFTWARE');

        $this->assertSame(2, $software->students);
        $this->assertSame(1, $software->teachers);
    }

    public function test_it_counts_the_assignments_touching_each_category(): void
    {
        $this->fake();

        $rows = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('byCategory');

        $this->assertSame(1, $rows->firstWhere('key', 'SOFTWARE')->assignments);
        $this->assertSame(1, $rows->firstWhere('key', 'NETWORK')->assignments);
        $this->assertSame(0, $rows->firstWhere('key', 'DATA')->assignments);
    }

    public function test_a_category_with_students_but_no_teacher_is_flagged(): void
    {
        $this->fake();

        $rows = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('byCategory');

        $this->assertTrue($rows->firstWhere('key', 'NETWORK')->uncovered);
        $this->assertFalse($rows->firstWhere('key', 'SOFTWARE')->uncovered);
    }

    public function test_an_empty_category_is_not_flagged(): void
    {
        $this->fake();

        $rows = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('byCategory');

        $this->assertFalse($rows->firstWhere('key', 'DESIGN')->uncovered);
    }

    public function test_every_category_appears_even_when_empty(): void
    {
        $this->fake();

        $rows = $this->asAdmin()->withoutVite()->get('/admin/dashboard')->viewData('byCategory');

        $this->assertCount(7, $rows);
    }

    public function test_the_page_shows_the_breakdown(): void
    {
        $this->fake();

        $this->asAdmin()->withoutVite()->get('/admin/dashboard')
            ->assertSee('แยกตามสาขา', false)
            ->assertSee('ซอฟต์แวร์ / พัฒนาโปรแกรม', false)
            ->assertSee('ยังไม่มีครูในสาขานี้', false);
    }

    public function test_the_invented_trend_badges_are_gone(): void
    {
        $this->fake();

        $this->asAdmin()->withoutVite()->get('/admin/dashboard')
            ->assertDontSee('+12%', false)
            ->assertDontSee('+4%', false);
    }

    public function test_a_failure_fetching_users_does_not_crash_the_page(): void
    {
        Http::fake(['*/user' => Http::response('boom', 500)]);

        $this->asAdmin()->withoutVite()->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('ดึงข้อมูลภาพรวมไม่สำเร็จ', false);
    }

    public function test_missing_assignments_do_not_break_the_user_counts(): void
    {
        $this->fake(['*/assignment' => Http::response('boom', 500)]);

        $stats = $this->asAdmin()->withoutVite()->get('/admin/dashboard')
            ->assertOk()->viewData('stats');

        $this->assertSame(3, $stats['students']);
        $this->assertSame(0, $stats['assignments']);
    }

    public function test_a_student_cannot_open_it(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->get('/admin/dashboard')
            ->assertForbidden();
    }
}
