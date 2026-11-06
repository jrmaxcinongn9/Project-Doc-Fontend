<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * บั๊กที่แก้ในรอบเก็บกวาด กันไม่ให้กลับมา
 */
class BugFixRegressionTest extends TestCase
{
    private function as(string $role): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => $role, 'email' => 'x@mail.kmutt.ac.th', 'name' => 'X'],
        ]);
    }

    public function test_editing_only_the_max_score_does_not_wipe_name_or_due_date(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->as('TEACHER')->put('/teacher/assignment/a1', ['max_score' => 20]);

        Http::assertSent(fn ($r) => $r->method() === 'PUT' && $r->data() === ['max_score' => 20]);
    }

    public function test_editing_the_name_and_date_still_sends_them(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->as('TEACHER')->put('/teacher/assignment/a1', ['assignment_name' => 'ใหม่', 'due_date' => '2026-12-01T10:00']);

        Http::assertSent(fn ($r) => $r['assignment_name'] === 'ใหม่' && str_starts_with($r['due_date'], '2026-12-01T03:00'));
    }

    public function test_an_api_refusal_is_shown_as_text_not_raw_json(): void
    {
        Http::fake(['*' => Http::response(['message' => 'งานชิ้นนี้ไม่ได้อยู่ในห้องเรียนของคุณ', 'statusCode' => 403], 403)]);

        $this->as('TEACHER')->put('/teacher/assignment/a1', ['max_score' => 20]);

        $error = session('errors')->first();
        $this->assertSame('แก้ไขไม่สำเร็จ: งานชิ้นนี้ไม่ได้อยู่ในห้องเรียนของคุณ', $error);
        $this->assertStringNotContainsString('statusCode', $error);
    }

    public function test_an_admin_account_can_be_saved_from_the_edit_form(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->as('ADMIN')->patch('/admin/users/u1', [
            'name' => 'Admin Two', 'email' => 'admin2@mail.kmutt.ac.th', 'role' => 'ADMIN',
        ])->assertSessionHas('success');

        Http::assertSent(fn ($r) => $r['role'] === 'ADMIN');
    }

    public function test_the_student_profile_shows_the_role_in_thai(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as('USER')->withoutVite()->get('/student/profile')->assertSee('นักศึกษา', false)->assertDontSee('>STUDENT<', false);
    }

    public function test_an_admin_opening_an_assignment_gets_the_full_grading_page(): void
    {
        Http::fake([
            '*/assignment' => Http::response([['_id' => 'a1', 'assignment_name' => 'งาน', 'classroom' => 'c1', 'students' => []]], 200),
            '*' => Http::response([], 200),
        ]);

        $this->as('ADMIN')->withoutVite()->get('/admin/assignment/a1/documents')
            ->assertOk()
            ->assertSee('ให้คะแนนและความเห็น', false);
    }
}
