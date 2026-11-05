<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * สาขาคือค่าที่ใช้จับคู่ครูกับนักศึกษา
 * เดิมตั้งจากหน้าเว็บไม่ได้เลย ต้องยิง API เอง
 * ครูจึงไม่มีสาขาและเห็นรายชื่อว่างเปล่า
 */
class AdminSetCategoryTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeList(array $users = []): void
    {
        Http::fake([
            '*/user' => Http::response($users, 200),
            '*' => Http::response(['ok' => true], 200),
        ]);
    }

    public function test_the_update_sends_the_category_to_the_api(): void
    {
        $this->fakeList();

        $this->asAdmin()->patch('/admin/users/u1', [
            'name' => 'Teacher One',
            'email' => 'teacher@mail.kmutt.ac.th',
            'role' => 'TEACHER',
            'category' => 'SOFTWARE',
        ]);

        Http::assertSent(fn($r) => str_contains($r->url(), '/user/u1')
            && ($r->data()['category'] ?? null) === 'SOFTWARE');
    }

    public function test_an_unknown_category_is_rejected(): void
    {
        $this->fakeList();

        $this->asAdmin()->patch('/admin/users/u1', [
            'name' => 'Teacher One',
            'email' => 'teacher@mail.kmutt.ac.th',
            'role' => 'TEACHER',
            'category' => 'ANYTHING',
        ])->assertSessionHasErrors('category');
    }

    public function test_leaving_the_category_empty_still_works(): void
    {
        $this->fakeList();

        $this->asAdmin()->patch('/admin/users/u1', [
            'name' => 'Student One',
            'email' => 'student@mail.kmutt.ac.th',
            'role' => 'USER',
        ])->assertSessionHasNoErrors();
    }

    public function test_the_table_shows_a_teachers_category(): void
    {
        $this->fakeList([[
            '_id' => 't1',
            'name' => 'Teacher One',
            'email' => 'teacher@mail.kmutt.ac.th',
            'role' => 'TEACHER',
            'category' => 'SOFTWARE',
        ]]);

        $this->asAdmin()->withoutVite()->get('/admin/users')
            ->assertOk()
            ->assertSee('ซอฟต์แวร์ / พัฒนาโปรแกรม', false);
    }

    public function test_a_teacher_without_a_category_is_flagged_in_the_table(): void
    {
        $this->fakeList([[
            '_id' => 't2',
            'name' => 'Teacher Two',
            'email' => 'teacher2@mail.kmutt.ac.th',
            'role' => 'TEACHER',
        ]]);

        $this->asAdmin()->withoutVite()->get('/admin/users')
            ->assertOk()
            ->assertSee('ยังไม่ได้ตั้งสาขา', false);
    }

    public function test_the_edit_form_offers_every_category(): void
    {
        $this->fakeList();

        $response = $this->asAdmin()->withoutVite()->get('/admin/users');

        foreach (['SOFTWARE', 'NETWORK', 'DATA', 'HARDWARE', 'DESIGN', 'BUSINESS', 'OTHER'] as $c) {
            $response->assertSee('value="' . $c . '"', false);
        }
    }
}
