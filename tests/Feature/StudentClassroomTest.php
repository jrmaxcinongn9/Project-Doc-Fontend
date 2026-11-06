<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * นักศึกษาเห็นห้องเรียนและงานแบบเดียวกับครู
 * แต่ทำได้แค่ส่งงาน เพิ่มไฟล์ และลบไฟล์ที่ตัวเองส่ง
 */
class StudentClassroomTest extends TestCase
{
    private function asStudent(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['_id' => 'stu1', 'role' => 'USER', 'name' => 'Somchai', 'email' => 's@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeApi(array $files = []): void
    {
        Http::fake([
            '*/classroom/my' => Http::response([
                [
                    '_id' => 'r1',
                    'name' => 'ห้องฝึกงาน A',
                    'major' => 'ครุศาสตร์ไฟฟ้า',
                    'academicYear' => '2569',
                    'teacher' => ['name' => 'Teacher One', 'email' => 't@mail.kmutt.ac.th'],
                    'student_count' => 30,
                    'assignments' => [
                        ['_id' => 'a1', 'assignment_name' => 'รายงานสัปดาห์ 1', 'due_date' => '2099-01-01T00:00:00Z', 'max_score' => 10],
                        ['_id' => 'a2', 'assignment_name' => 'รายงานสัปดาห์ 2', 'due_date' => null, 'max_score' => null],
                    ],
                ],
                [
                    '_id' => 'r2',
                    'name' => 'ห้องฝึกงาน B',
                    'major' => 'ครุศาสตร์ไฟฟ้า',
                    'academicYear' => '2569',
                    'teacher' => ['name' => 'Teacher Two'],
                    'assignments' => [],
                ],
            ], 200),
            '*/files/my' => Http::response($files, 200),
            '*' => Http::response([], 200),
        ]);
    }

    public function test_the_dashboard_lists_every_classroom_the_student_is_in(): void
    {
        $this->fakeApi();

        $this->asStudent()->withoutVite()->get('/student/dashboard')
            ->assertOk()
            ->assertSee('ห้องฝึกงาน A', false)
            ->assertSee('ห้องฝึกงาน B', false)
            ->assertSee('/student/classrooms/r1', false)
            ->assertSee('อาจารย์ Teacher One', false);
    }

    public function test_the_card_counts_submitted_and_pending_work(): void
    {
        $this->fakeApi([['_id' => 'f1', 'assignment_id' => 'a1', 'original_name' => 'x.pdf']]);

        $this->asStudent()->withoutVite()->get('/student/dashboard')
            ->assertSee('ส่งแล้ว 1', false)
            ->assertSee('ยังไม่ส่ง 1', false);
    }

    public function test_it_asks_only_for_the_students_own_data(): void
    {
        $this->fakeApi();

        $this->asStudent()->withoutVite()->get('/student/dashboard');

        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/classroom/my'));
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/files/my'));
        // เดิมดึงห้อง ผู้ใช้ งาน และไฟล์ของทั้งระบบมากรองเอง
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/user'));
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/files'));
    }

    public function test_the_classroom_page_lists_its_assignments_with_a_submit_button(): void
    {
        $this->fakeApi();

        $this->asStudent()->withoutVite()->get('/student/classrooms/r1')
            ->assertOk()
            ->assertSee('ห้องเรียน: ห้องฝึกงาน A', false)
            ->assertSee('รายงานสัปดาห์ 1', false)
            ->assertSee('รายงานสัปดาห์ 2', false)
            ->assertSee('data-assignment-id="a1"', false)
            ->assertSee(route('student.assignment.submit'), false);
    }

    public function test_own_files_show_up_on_the_assignment_with_download_links(): void
    {
        $this->fakeApi([['_id' => 'f1', 'assignment_id' => 'a1', 'original_name' => 'งานของฉัน.pdf', 'createdAt' => '2026-11-01T00:00:00Z']]);

        $this->asStudent()->withoutVite()->get('/student/classrooms/r1')
            // รายการไฟล์ฝังไว้ใน data-files เป็น JSON ให้หน้าต่างส่งงานอ่าน
            ->assertSee('"id":"f1"', false)
            ->assertSee(trim(json_encode('งานของฉัน.pdf'), '"'), false)
            ->assertSee(trim(json_encode('/student/files/download/f1'), '"'), false);
    }

    public function test_the_page_has_no_teacher_only_controls(): void
    {
        $this->fakeApi();

        $this->asStudent()->withoutVite()->get('/student/classrooms/r1')
            ->assertDontSee('/grade', false)
            ->assertDontSee('max-score', false)
            ->assertDontSee('/teacher/', false);
    }

    public function test_a_classroom_the_student_is_not_in_is_not_found(): void
    {
        $this->fakeApi();

        $this->asStudent()->withoutVite()->get('/student/classrooms/someone-elses-room')->assertNotFound();
    }

    public function test_a_teacher_cannot_open_the_student_pages(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'TEACHER']])
            ->get('/student/classrooms/r1')
            ->assertForbidden();
    }

    public function test_deleting_a_file_now_requires_a_student_session(): void
    {
        Http::fake();

        $this->delete('/student/files/delete/f1')->assertRedirect('/login/student');

        Http::assertNothingSent();
    }

    public function test_deleting_sends_the_request_to_the_api_which_checks_ownership(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ok'], 200)]);

        $this->asStudent()->deleteJson('/student/files/delete/f1')->assertJson(['success' => true]);

        Http::assertSent(fn ($r) => $r->method() === 'DELETE' && str_ends_with($r->url(), '/files/f1'));
    }

    public function test_an_upload_refused_by_the_api_shows_why(): void
    {
        Http::fake(['*/files/upload' => Http::response(['message' => 'คุณไม่ได้อยู่ในห้องเรียนของงานชิ้นนี้'], 403)]);

        $this->asStudent()
            ->from('/student/classrooms/r1')
            ->post('/student/assignment/submit', [
                'assignment_id' => 'a1',
                'files' => [\Illuminate\Http\UploadedFile::fake()->create('งาน.pdf', 10)],
            ])
            ->assertRedirect('/student/classrooms/r1')
            ->assertSessionHasErrors();

        $this->assertStringContainsString('คุณไม่ได้อยู่ในห้องเรียนของงานชิ้นนี้', session('errors')->first());
    }
}
