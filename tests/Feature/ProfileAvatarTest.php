<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ผู้ใช้ทุกบทบาทเลือกรูปมาใส่เป็นรูปโปรไฟล์ได้
 */
class ProfileAvatarTest extends TestCase
{
    private function as(string $role): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => $role, 'name' => 'สมชาย', 'email' => 'x@mail.kmutt.ac.th'],
        ]);
    }

    public static function profilePages(): array
    {
        return [
            'นักศึกษา' => ['USER', '/student/profile'],
            'ครู' => ['TEACHER', '/teacher/profile'],
            'admin' => ['ADMIN', '/admin/profile'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('profilePages')]
    public function test_every_profile_page_has_the_picture_picker(string $role, string $page): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as($role)->withoutVite()->get($page)
            ->assertOk()
            ->assertSee('name="avatar"', false)
            ->assertSee('action="/profile/avatar"', false)
            ->assertSee('id="avatarPreview"', false);
    }

    public function test_the_topbar_shows_the_picture_with_a_letter_fallback(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as('TEACHER')->withoutVite()->get('/teacher/profile')
            ->assertSee('src="/profile/avatar?v=', false)
            ->assertSee('onerror="this.remove()"', false)
            ->assertSee('ส', false);
    }

    public function test_uploading_sends_the_image_to_the_api_and_busts_the_cache(): void
    {
        Http::fake(['*/user/me/avatar' => Http::response(['avatar_id' => 'g1'], 201)]);

        $this->as('USER')->from('/student/profile')
            ->post('/profile/avatar', ['avatar' => UploadedFile::fake()->image('me.png', 100, 100)])
            ->assertRedirect('/student/profile')
            ->assertSessionHas('avatar_success');

        $this->assertNotNull(session('avatar_v'));
        Http::assertSent(fn ($r) => $r->method() === 'POST'
            && str_ends_with($r->url(), '/user/me/avatar')
            && $r->isMultipart());
    }

    public function test_a_non_image_is_refused_before_it_is_sent(): void
    {
        Http::fake();

        $this->as('USER')->post('/profile/avatar', ['avatar' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors('avatar');

        Http::assertNothingSent();
    }

    public function test_an_image_over_2mb_is_refused(): void
    {
        Http::fake();

        $this->as('USER')->post('/profile/avatar', ['avatar' => UploadedFile::fake()->image('big.jpg')->size(3000)])
            ->assertSessionHasErrors('avatar');

        Http::assertNothingSent();
    }

    public function test_the_picture_route_streams_the_image_or_404s(): void
    {
        Http::fake(['*/user/me/avatar' => Http::response('PNGDATA', 200, ['Content-Type' => 'image/png'])]);

        $this->as('USER')->get('/profile/avatar')->assertOk()->assertHeader('Content-Type', 'image/png');
    }

    public function test_no_picture_yet_is_404_so_the_letter_shows(): void
    {
        Http::fake(['*/user/me/avatar' => Http::response(['message' => 'ยังไม่มีรูปโปรไฟล์'], 404)]);

        $this->as('USER')->get('/profile/avatar')->assertNotFound();
    }

    public function test_removing_the_picture_calls_the_api(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 200)]);

        $this->as('USER')->delete('/profile/avatar')->assertSessionHas('avatar_success', 'ลบรูปโปรไฟล์แล้ว');

        Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_ends_with($r->url(), '/user/me/avatar'));
    }

    public function test_not_logged_in_gets_no_picture(): void
    {
        $this->get('/profile/avatar')->assertNotFound();
    }
}
