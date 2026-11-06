<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * token ของ API หมดอายุใน 1 ชั่วโมง แต่ session ของ Laravel อยู่นานกว่า
 * เดิมพ้นชั่วโมงแล้วหน้าเว็บขึ้นว่าง เพราะ API ตอบ 401 แต่ไม่มีใครพาไป login ใหม่
 */
class ExpiredTokenTest extends TestCase
{
    private function jwt(int $exp): string
    {
        $enc = fn (array $a) => rtrim(strtr(base64_encode(json_encode($a)), '+/', '-_'), '=');

        return $enc(['alg' => 'HS256', 'typ' => 'JWT']) . '.'
            . $enc(['sub' => 'u1', 'exp' => $exp]) . '.signature';
    }

    private function as(string $role, string $token): self
    {
        return $this->withSession([
            'api_token' => $token,
            'user' => ['role' => $role, 'email' => 'x@mail.kmutt.ac.th'],
        ]);
    }

    public static function roles(): array
    {
        return [
            'ครู' => ['TEACHER', '/teacher/classroom', '/login/teacher'],
            'admin' => ['ADMIN', '/admin/users', '/login/admin'],
            'นักศึกษา' => ['USER', '/student/scores', '/login/student'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_an_expired_token_sends_the_user_to_their_login_page(string $role, string $page, string $login): void
    {
        Http::fake();

        $this->as($role, $this->jwt(time() - 60))
            ->get($page)
            ->assertRedirect($login)
            ->assertSessionHasErrors('email');

        Http::assertNothingSent();
    }

    public function test_the_session_is_cleared_so_the_old_token_is_gone(): void
    {
        $this->as('TEACHER', $this->jwt(time() - 60))->get('/teacher/classroom');

        $this->assertNull(session('api_token'));
        $this->assertNull(session('user'));
    }

    public function test_a_token_that_is_still_valid_is_left_alone(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $this->as('ADMIN', $this->jwt(time() + 3600))
            ->withoutVite()
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_a_token_about_to_expire_counts_as_expired(): void
    {
        $this->as('ADMIN', $this->jwt(time() + 10))
            ->get('/admin/users')
            ->assertRedirect('/login/admin');
    }

    public function test_the_login_page_itself_still_opens_without_a_session(): void
    {
        $this->withoutVite()->get('/login/teacher')->assertOk();
    }
}
