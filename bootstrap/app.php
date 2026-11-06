<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        // token ของ API หมดอายุก่อน session ต้องพาไป login ใหม่
        // ไม่อย่างนั้นหน้าเว็บขึ้นว่างเพราะ API ตอบ 401 ทุกคำขอ
        $middleware->web(append: [
            \App\Http\Middleware\LogoutWhenApiTokenExpires::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if ($storagePath = getenv('APP_STORAGE')) {
    $app->useStoragePath($storagePath);
}

return $app;
