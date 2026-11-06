<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * admin อนุมัติหรือปฏิเสธหลายบัญชีพร้อมกัน
 */
class AdminBulkApproveTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    public function test_the_page_has_checkboxes_and_bulk_buttons(): void
    {
        Http::fake(['*' => Http::response([
            ['_id' => 'u1', 'name' => 'A', 'email' => 'a@mail.kmutt.ac.th', 'role' => 'USER', 'status' => 'PENDING'],
            ['_id' => 'u2', 'name' => 'B', 'email' => 'b@mail.kmutt.ac.th', 'role' => 'USER', 'status' => 'PENDING'],
        ], 200)]);

        $html = $this->asAdmin()->withoutVite()->get('/admin/pending')->assertOk()->getContent();

        $this->assertStringContainsString('action="/admin/pending/bulk"', $html);
        $this->assertSame(2, substr_count($html, 'form="bulkForm"'));
        $this->assertStringContainsString('อนุมัติที่เลือก', $html);
    }

    public function test_approving_verifies_email_then_approves_each_one(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asAdmin()->post('/admin/pending/bulk', ['ids' => ['u1', 'u2'], 'action' => 'approve', 'verify' => '1'])
            ->assertSessionHas('success', 'อนุมัติแล้ว 2 บัญชี');

        Http::assertSentCount(4);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/user/u1/verify-email'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/user/u2/approve'));
    }

    public function test_approving_without_verify_only_approves(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asAdmin()->post('/admin/pending/bulk', ['ids' => ['u1'], 'action' => 'approve']);

        Http::assertSentCount(1);
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'verify-email'));
    }

    public function test_rejecting_never_verifies(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->asAdmin()->post('/admin/pending/bulk', ['ids' => ['u1', 'u2'], 'action' => 'reject', 'verify' => '1'])
            ->assertSessionHas('success', 'ปฏิเสธแล้ว 2 บัญชี');

        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'verify-email'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/user/u1/reject'));
    }

    public function test_a_failure_is_counted_and_the_rest_continue(): void
    {
        Http::fake([
            '*/user/u1/approve' => Http::response(['message' => 'User not found'], 404),
            '*' => Http::response(['ok' => true], 200),
        ]);

        $this->asAdmin()->post('/admin/pending/bulk', ['ids' => ['u1', 'u2'], 'action' => 'approve'])
            ->assertSessionHas('success', 'อนุมัติแล้ว 1 บัญชี')
            ->assertSessionHasErrors('api');
    }

    public function test_nothing_selected_is_refused(): void
    {
        Http::fake();

        $this->asAdmin()->post('/admin/pending/bulk', ['action' => 'approve'])->assertSessionHasErrors('ids');

        Http::assertNothingSent();
    }

    public function test_a_teacher_cannot_bulk_approve(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'TEACHER']])
            ->post('/admin/pending/bulk', ['ids' => ['u1'], 'action' => 'approve'])
            ->assertForbidden();
    }
}
