<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * คอนโทรลเลอร์ทุกตัวต่อ API ผ่าน config('services.api_url')
 * ถ้าค่านี้ว่าง ทั้งแอปจะยิงไปที่ path ว่างแล้วล้มทันที
 * โดยขึ้นข้อความที่อ่านเหมือนระบบล่ม ทั้งที่ API ปกติดี
 */
class ApiUrlConfigTest extends TestCase
{
    public function test_api_url_is_never_empty(): void
    {
        $this->assertNotEmpty(config('services.api_url'));
    }

    public function test_api_url_is_an_absolute_url(): void
    {
        $url = config('services.api_url');

        $this->assertNotFalse(
            filter_var($url, FILTER_VALIDATE_URL),
            "api_url ต้องเป็น URL เต็ม ได้มาเป็น: {$url}",
        );
    }

    public function test_api_url_has_no_trailing_slash(): void
    {
        // คอนโทรลเลอร์ต่อ path ด้วยการเชื่อมสตริง เช่น api_url . "/user/register"
        // ถ้าค่าลงท้ายด้วย / จะได้ // ซึ่งบาง API ตอบ 404
        $this->assertStringEndsNotWith('/', config('services.api_url'));
    }
}
