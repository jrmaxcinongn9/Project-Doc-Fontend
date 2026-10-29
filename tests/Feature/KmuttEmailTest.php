<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KmuttEmailTest extends TestCase
{
    private function payload(string $email): array
    {
        return [
            'name'         => 'Somchai Jai',
            'student_id'   => '65130500001',
            'email'        => $email,
            'password'     => 'secret1',
            'academicYear' => '2569',
            'major'        => 'CPE',
        ];
    }

    public static function acceptedEmails(): array
    {
        return [['65130500001@mail.kmutt.ac.th'], ['Somchai@MAIL.KMUTT.AC.TH'], ['  somchai@mail.kmutt.ac.th  ']];
    }

    public static function rejectedEmails(): array
    {
        return [['somchai@gmail.com'], ['somchai@kmutt.ac.th'], ['a@mail.kmutt.ac.th.evil.com']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('acceptedEmails')]
    public function test_kmutt_email_passes_validation(string $email): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload($email))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/login/student');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rejectedEmails')]
    public function test_other_domains_are_rejected(string $email): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload($email))
            ->assertSessionHasErrors('email');

        Http::assertNothingSent();
    }

    public function test_email_is_lowercased_before_it_reaches_the_api(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 201)]);

        $this->post('/register', $this->payload('Somchai@MAIL.KMUTT.AC.TH'));

        Http::assertSent(fn ($request) => $request['email'] === 'somchai@mail.kmutt.ac.th');
    }
}
