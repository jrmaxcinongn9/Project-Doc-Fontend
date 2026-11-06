<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูเปิดดูไฟล์ที่นักศึกษาส่งได้เลยจากหน้างาน ไม่ต้องดาวน์โหลดก่อน
 * เปิดได้เฉพาะ PDF รูปภาพ และข้อความ ชนิดอื่นยังเป็นปุ่มดาวน์โหลด
 */
class TeacherViewFileTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 'teacher@mail.kmutt.ac.th'],
        ]);
    }

    private function fakePage(): void
    {
        Http::fake([
            '*/files' => Http::response([
                ['_id' => 'pdf1', 'assignment_id' => 'a1', 'original_name' => 'รายงาน.pdf', 'file_name' => 'x.pdf',
                 'mimetype' => 'application/pdf', 'uploaded_by_email' => 'somchai@mail.kmutt.ac.th', 'uploaded_by_name' => 'Somchai'],
                ['_id' => 'doc1', 'assignment_id' => 'a1', 'original_name' => 'สไลด์.pptx', 'file_name' => 'y.pptx',
                 'mimetype' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                 'uploaded_by_email' => 'anucha@mail.kmutt.ac.th', 'uploaded_by_name' => 'Anucha'],
            ], 200),
            '*/assignment' => Http::response([[
                '_id' => 'a1', 'assignment_name' => 'งาน 1', 'classroom' => 'c1', 'max_score' => 10,
                'students' => [
                    ['student_id' => 'stu1', 'name' => 'Somchai'],
                    ['student_id' => 'stu2', 'name' => 'Anucha'],
                ],
            ]], 200),
            '*/classroom' => Http::response([['_id' => 'c1', 'students' => ['stu1', 'stu2']]], 200),
            '*/user' => Http::response([
                ['_id' => 'stu1', 'name' => 'Somchai', 'email' => 'somchai@mail.kmutt.ac.th', 'role' => 'USER'],
                ['_id' => 'stu2', 'name' => 'Anucha', 'email' => 'anucha@mail.kmutt.ac.th', 'role' => 'USER'],
            ], 200),
        ]);
    }

    public function test_a_pdf_gets_an_open_button_that_opens_in_a_new_tab(): void
    {
        $this->fakePage();

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertOk()
            ->assertSee('href="/teacher/view/pdf1" target="_blank"', false)
            ->assertSee('เปิดดู', false);
    }

    public function test_a_powerpoint_file_only_gets_a_download_link(): void
    {
        $this->fakePage();

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')
            ->assertDontSee('/teacher/view/doc1', false)
            ->assertSee('/teacher/download/doc1', false);
    }

    public function test_the_grading_row_links_to_that_students_file(): void
    {
        $this->fakePage();

        $html = $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/a1')->getContent();

        // ลิงก์เปิดดูของ Somchai อยู่ในตารางบนและในแถวให้คะแนน
        $this->assertGreaterThanOrEqual(3, substr_count($html, '/teacher/view/pdf1'));
        $this->assertStringContainsString('ดาวน์โหลด:', $html);
    }

    public function test_the_view_route_asks_the_api_for_an_inline_copy(): void
    {
        Http::fake(['*' => Http::response('%PDF-1.4', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="x.pdf"',
        ])]);

        $this->asTeacher()->get('/teacher/view/pdf1/raw')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline; filename="x.pdf"')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        Http::assertSent(fn ($r) => str_contains($r->url(), '/files/download/pdf1')
            && str_contains($r->url(), 'inline=1'));
    }

    public function test_a_missing_file_is_a_404(): void
    {
        Http::fake(['*' => Http::response(['message' => 'ไฟล์ไม่พบหรือถูกลบไปแล้ว'], 404)]);

        $this->asTeacher()->get('/teacher/view/gone/raw')->assertNotFound();
    }

    public function test_the_view_page_renders_pdfs_with_pdfjs_from_the_raw_route(): void
    {
        $this->asTeacher()->get('/teacher/view/pdf1')
            ->assertOk()
            ->assertSee('pdf.js/3.11.174/pdf.min.js', false)
            ->assertSee("fetch('/teacher/view/pdf1/raw')", false)
            ->assertSee('/teacher/download/pdf1', false);
    }

    public function test_a_student_cannot_use_the_teacher_view_route(): void
    {
        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->get('/teacher/view/pdf1')
            ->assertForbidden();

        $this->withSession(['api_token' => 'x', 'user' => ['role' => 'USER']])
            ->get('/teacher/view/pdf1/raw')
            ->assertForbidden();
    }
}
