<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * หน้าเพิ่มผู้ใช้ของ admin เดิมกดบันทึกแล้วไม่เกิดอะไร
 * ฟอร์มไม่มี category ส่ง academicYear ที่ API ไม่รับ
 * ส่งครูไปเส้นของนักศึกษา และไม่แสดง error จาก API เลย
 */
class AdminCreateUserTest extends TestCase
{
    private function asAdmin(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'ADMIN', 'email' => 'admin@mail.kmutt.ac.th'],
        ]);
    }

    private function student(array $over = []): array
    {
        return array_merge([
            'role' => 'USER',
            'name' => 'Somchai Jaidee',
            'student_id' => '66070500001',
            'major' => 'ครุศาสตร์ไฟฟ้า',
            'category' => 'SOFTWARE',
            'email' => 'somchai.j@mail.kmutt.ac.th',
            'password' => 'secret123',
        ], $over);
    }

    private function teacher(array $over = []): array
    {
        return array_merge([
            'role' => 'TEACHER',
            'name' => 'Teacher One',
            'category' => 'NETWORK',
            'email' => 'teacher.one@mail.kmutt.ac.th',
            'password' => 'secret123',
        ], $over);
    }

    public function test_a_student_goes_to_the_user_register_route_with_every_required_field(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'u1'], 201)]);

        $this->asAdmin()->post('/admin/users', $this->student())->assertSessionHas('success');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/user/register')
            && $r['student_id'] === '66070500001'
            && $r['major'] === 'ครุศาสตร์ไฟฟ้า'
            && $r['category'] === 'SOFTWARE'
            && $r['role'] === 'USER');
    }

    public function test_academic_year_is_never_sent_because_the_api_rejects_it(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'u1'], 201)]);

        $this->asAdmin()->post('/admin/users', $this->student(['academicYear' => '2569']));

        Http::assertSent(fn ($r) => !array_key_exists('academicYear', $r->data()));
    }

    public function test_a_teacher_goes_to_the_teacher_route_without_student_fields(): void
    {
        Http::fake(['*' => Http::response(['_id' => 't1'], 201)]);

        $this->asAdmin()
            ->post('/admin/users', $this->teacher(['student_id' => '123', 'major' => 'x']))
            ->assertSessionHas('success');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/teacher/register')
            && $r->data() === [
                'name' => 'Teacher One',
                'email' => 'teacher.one@mail.kmutt.ac.th',
                'password' => 'secret123',
                'category' => 'NETWORK',
            ]);
    }

    public static function badStudentIds(): array
    {
        return [
            'สั้นไป' => ['6607050000'],
            'ยาวไป' => ['660705000012'],
            'ว่าง' => [''],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badStudentIds')]
    public function test_a_student_id_must_be_exactly_eleven_digits(string $id): void
    {
        Http::fake();

        $this->asAdmin()
            ->post('/admin/users', $this->student(['student_id' => $id]))
            ->assertSessionHasErrorsIn('create', 'student_id');

        Http::assertNothingSent();
    }

    public function test_spaces_and_dashes_in_the_student_id_are_dropped(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'u1'], 201)]);

        $this->asAdmin()->post('/admin/users', $this->student(['student_id' => '66070-50000 1']));

        Http::assertSent(fn ($r) => $r['student_id'] === '66070500001');
    }

    public function test_the_email_must_be_a_kmutt_student_mail_address(): void
    {
        Http::fake();

        $this->asAdmin()
            ->post('/admin/users', $this->student(['email' => 'someone@gmail.com']))
            ->assertSessionHasErrorsIn('create', 'email');

        Http::assertNothingSent();
    }

    public function test_an_uppercase_email_is_accepted_and_lowercased(): void
    {
        Http::fake(['*' => Http::response(['_id' => 'u1'], 201)]);

        $this->asAdmin()->post('/admin/users', $this->student(['email' => ' Somchai.J@MAIL.KMUTT.AC.TH ']));

        Http::assertSent(fn ($r) => $r['email'] === 'somchai.j@mail.kmutt.ac.th');
    }

    public function test_a_category_is_required(): void
    {
        Http::fake();

        $this->asAdmin()
            ->post('/admin/users', $this->teacher(['category' => '']))
            ->assertSessionHasErrorsIn('create', 'category');
    }

    public function test_the_api_refusal_is_shown_in_thai(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Email already exists'], 400)]);

        $this->asAdmin()
            ->post('/admin/users', $this->student())
            ->assertSessionHasErrorsIn('create', ['api' => 'อีเมลนี้ถูกใช้งานแล้ว']);
    }

    public function test_the_form_shows_the_error_and_reopens(): void
    {
        Http::fake([
            '*/user' => Http::response([], 200),
            '*' => Http::response(['message' => 'Student ID already exists'], 400),
        ]);

        $this->asAdmin()->from('/admin/users')->post('/admin/users', $this->student());

        $this->asAdmin()->withoutVite()->get('/admin/users')
            ->assertSee('รหัสนักศึกษานี้ถูกใช้งานแล้ว', false)
            ->assertSee('openCreateModal(), 100', false);
    }

    public function test_the_form_limits_the_student_id_to_eleven_digits(): void
    {
        Http::fake(['*/user' => Http::response([], 200)]);

        $this->asAdmin()->withoutVite()->get('/admin/users')
            ->assertSee('maxlength="11" pattern="[0-9]{11}"', false)
            ->assertSee('name="category"', false)
            ->assertDontSee('name="academicYear" class="w-full border border-slate-200 px-4 py-2.5 rounded-xl focus:ring-2 focus:ring-[#F26522]', false);
    }
}
