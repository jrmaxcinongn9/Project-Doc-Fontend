<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // บังคับ https เฉพาะบน Vercel (APP_ENV=production)
        // ถ้าบังคับในเครื่องด้วย ลิงก์และ redirect จะชี้ไป https://localhost
        // ซึ่ง php -S ตอบไม่ได้ เว็บจึงเปิดไม่ขึ้น
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
