<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //

    /**
     * ข้อความ error จาก API ที่ผู้ใช้อ่านเข้าใจ
     *
     * เดิมหลายหน้าแสดง $response->body() ตรง ๆ ผู้ใช้เห็น JSON ดิบ เช่น {"message":...,"statusCode":403}
     * Nest ส่ง message มาเป็นข้อความเดียวหรือเป็นอาเรย์ก็ได้
     */
    protected function apiError($response, string $fallback = 'ทำรายการไม่สำเร็จ'): string
    {
        $msg = null;
        try {
            $msg = $response->json('message');
        } catch (\Throwable $e) {
            // ไม่ใช่ JSON ใช้ข้อความสำรอง
        }

        if (is_array($msg)) {
            $msg = implode(' / ', array_filter($msg, 'is_string'));
        }

        $known = [
            'Email already exists'      => 'อีเมลนี้ถูกใช้งานแล้ว',
            'Student ID already exists' => 'รหัสนักศึกษานี้ถูกใช้งานแล้ว',
            'Forbidden resource'        => 'ไม่มีสิทธิ์ทำรายการนี้',
            'Unauthorized'              => 'กรุณาเข้าสู่ระบบใหม่',
        ];

        if (is_string($msg) && $msg !== '') {
            return $fallback . ': ' . ($known[$msg] ?? $msg);
        }

        return $fallback;
    }
}
