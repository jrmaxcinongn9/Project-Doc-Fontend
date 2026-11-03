<?php

return [

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ⭐ เพิ่มตรงนี้
    // คอนโทรลเลอร์ 11 ตัวต่อ API ผ่านค่านี้ ถ้าไม่มีค่า ทุกตัวจะยิงไปที่
    // path ว่างแล้วล้มด้วย ConnectionException ซึ่งอ่านเหมือนระบบล่ม
    // จึงใส่ที่อยู่ production ไว้เป็นค่าปริยาย ตั้ง API_URL เพื่อทับได้
    'api_url' => rtrim(
        env('API_URL') ?: 'https://project-doc-backend.onrender.com',
        '/',
    ),

];
