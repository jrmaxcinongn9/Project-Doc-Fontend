<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ครูแต่ละคนดูได้เฉพาะห้องของตัวเอง
 * API คืนเฉพาะของครูคนนั้น หน้าเว็บต้องตอบ 404 เมื่อเปิดห้องหรืองานของคนอื่น
 */
class TeacherOwnRoomsOnlyTest extends TestCase
{
    private function asTeacher(): self
    {
        return $this->withSession([
            'api_token' => 'fake-token',
            'user' => ['role' => 'TEACHER', 'email' => 't@mail.kmutt.ac.th'],
        ]);
    }

    public function test_another_teachers_classroom_is_not_found(): void
    {
        Http::fake([
            '*/classroom/*' => Http::response(['message' => 'ห้องเรียนนี้ไม่ใช่ของคุณ แก้ไขไม่ได้'], 403),
            '*' => Http::response([], 200),
        ]);

        $this->asTeacher()->withoutVite()->get('/teacher/classroom/other-room')->assertNotFound();
    }

    public function test_an_assignment_outside_the_teachers_rooms_is_not_found(): void
    {
        // API กรองแล้ว งานของห้องคนอื่นจึงไม่อยู่ในรายการ
        Http::fake(['*' => Http::response([], 200)]);

        $this->asTeacher()->withoutVite()->get('/teacher/assignment-view/someone-elses')->assertNotFound();
    }
}
