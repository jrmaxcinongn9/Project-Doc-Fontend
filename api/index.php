<?php

// Force HTTPS in serverless environment so all asset() and @vite generate https:// URLs
$_SERVER['HTTPS'] = 'on';
$_SERVER['SERVER_PORT'] = 443;

// Create tmp storage directories for serverless environment
$storagePath = '/tmp/storage';
foreach ([
    $storagePath,
    $storagePath . '/app',
    $storagePath . '/app/public',
    $storagePath . '/framework',
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward to public/index.php
require __DIR__ . '/../public/index.php';
