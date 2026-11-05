<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * APP_KEY คือกุญแจที่ Laravel ใช้เซ็นและถอดรหัส session cookie
 * โปรเจกต์นี้ตั้ง SESSION_DRIVER เป็น cookie ข้อมูล session จึงอยู่ในคุกกี้
 * ใครมีกุญแจก็ปลอมคุกกี้ให้ตัวเองเป็น ADMIN ได้
 * ค่านี้จึงต้องไม่อยู่ในไฟล์ที่ commit ขึ้น repo
 */
class AppKeyNotInRepoTest extends TestCase
{
    public function test_vercel_config_does_not_carry_the_app_key(): void
    {
        $config = json_decode(file_get_contents(base_path('vercel.json')), true);

        $this->assertArrayNotHasKey('APP_KEY', $config['env'] ?? []);
    }

    public function test_vercel_config_is_still_valid_json(): void
    {
        $config = json_decode(file_get_contents(base_path('vercel.json')), true);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('routes', $config);
    }

    public function test_the_other_deployment_settings_are_untouched(): void
    {
        $config = json_decode(file_get_contents(base_path('vercel.json')), true);

        $this->assertSame('production', $config['env']['APP_ENV']);
        $this->assertSame('cookie', $config['env']['SESSION_DRIVER']);
    }

    public function test_no_tracked_file_holds_a_base64_laravel_key(): void
    {
        // กันไว้ไม่ให้มีใครเผลอ commit กุญแจกลับเข้ามาอีกทางไฟล์อื่น
        $files = ['vercel.json', '.env.example'];

        foreach ($files as $file) {
            $path = base_path($file);

            if (!file_exists($path)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression(
                '/base64:[A-Za-z0-9+\/]{40,}=*/',
                file_get_contents($path),
                $file . ' ยังมีกุญแจอยู่'
            );
        }
    }
}
