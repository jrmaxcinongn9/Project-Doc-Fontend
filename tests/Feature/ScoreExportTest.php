<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูส่งออกคะแนนทั้งห้องเป็น Excel เอาไปกรอกระบบมหาวิทยาลัยต่อได้
 */
class ScoreExportTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 't@mail.kmutt.ac.th'],
        ]);
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/classroom/r1' => Http::response([
                '_id' => 'r1', 'name' => 'ห้อง A',
                'students' => [
                    ['_id' => 's2', 'name' => 'บี', 'student_id' => '66000000002', 'email' => 'b@mail.kmutt.ac.th'],
                    ['_id' => 's1', 'name' => 'เอ', 'student_id' => '66000000001', 'email' => 'a@mail.kmutt.ac.th'],
                ],
            ], 200),
            '*/classroom/*' => Http::response(['message' => 'ห้องเรียนนี้ไม่ใช่ของคุณ'], 403),
            '*/assignment' => Http::response([
                ['_id' => 'a1', 'assignment_name' => 'งาน 1', 'classroom' => ['_id' => 'r1'], 'max_score' => 10, 'due_date' => '2026-01-01',
                 'students' => [['student_id' => 's1', 'score' => 8]]],
                ['_id' => 'a2', 'assignment_name' => 'งาน 2', 'classroom' => 'r1', 'max_score' => 20, 'due_date' => '2026-02-01', 'students' => []],
                ['_id' => 'x', 'assignment_name' => 'งานห้องอื่น', 'classroom' => 'r9', 'students' => []],
            ], 200),
            '*/files' => Http::response([
                ['assignment_id' => 'a2', 'uploaded_by_id' => 's1'],
            ], 200),
        ]);
    }

    /** อ่านแผ่นงานจากไฟล์ที่ดาวน์โหลด คืนเป็นอาเรย์ของแถว */
    private function sheet($response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive();
        $zip->open($path);
        $xml = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
        $zip->close();

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $line = [];
            foreach ($row->c as $c) {
                $line[] = isset($c->is) ? (string) $c->is->t : (string) $c->v;
            }
            $rows[] = $line;
        }
        return $rows;
    }

    public function test_it_downloads_an_xlsx_named_after_the_room(): void
    {
        $this->fakeApi();

        $res = $this->asTeacher()->get('/teacher/classroom/r1/export')->assertOk();

        $this->assertStringContainsString('spreadsheetml', $res->headers->get('Content-Type'));
        $this->assertStringContainsString(rawurlencode('ห้อง A'), $res->headers->get('Content-Disposition'));
    }

    public function test_one_row_per_student_with_one_column_per_assignment(): void
    {
        $this->fakeApi();

        $rows = $this->sheet($this->asTeacher()->get('/teacher/classroom/r1/export'));

        $this->assertSame(['ลำดับ', 'รหัสนักศึกษา', 'ชื่อ - นามสกุล', 'อีเมล', 'งาน 1 (เต็ม 10)', 'งาน 2 (เต็ม 20)', 'คะแนนรวม', 'คะแนนเต็มรวม', 'ส่งงานแล้ว'], $rows[0]);
        // เรียงตามรหัสนักศึกษา
        $this->assertSame(['1', '66000000001', 'เอ', 'a@mail.kmutt.ac.th', '8', 'ยังไม่ตรวจ', '8', '10', '1/2'], $rows[1]);
        $this->assertSame(['2', '66000000002', 'บี', 'b@mail.kmutt.ac.th', 'ไม่ส่ง', 'ไม่ส่ง', '0', '0', '0/2'], $rows[2]);
        $this->assertCount(3, $rows);
    }

    public function test_work_from_other_rooms_is_left_out(): void
    {
        $this->fakeApi();

        $rows = $this->sheet($this->asTeacher()->get('/teacher/classroom/r1/export'));

        $this->assertNotContains('งานห้องอื่น', $rows[0]);
    }

    public function test_another_teachers_room_is_not_found(): void
    {
        $this->fakeApi();

        $this->asTeacher()->get('/teacher/classroom/r9/export')->assertNotFound();
    }

    public function test_the_classroom_page_has_the_export_button(): void
    {
        $this->fakeApi();
        Http::fake(['*/classroom/r1' => Http::response(['_id' => 'r1', 'name' => 'ห้อง A', 'students' => []], 200), '*' => Http::response([], 200)]);

        $this->asTeacher()->withoutVite()->get('/teacher/classroom/r1')
            ->assertSee('/teacher/classroom/r1/export', false);
    }
}
