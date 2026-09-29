<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClassroomController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        // ตรวจสอบว่าใน config/services.php มี api_url หรือใช้ env โดยตรง
        $this->apiUrl = config('services.api_url', env('API_URL')) . '/classroom';
    }

    // ฟังก์ชันอัปเดตข้อมูล (PATCH ไปที่ API)
    public function update(Request $request, $id)
    {
        $token = session('api_token');

        $request->validate([
            'name'         => 'required|string',
            'major'        => 'required|string',
            'academicYear' => 'required',
            'level'        => 'required',
            'isActive'     => 'required' 
        ]);

        try {
            // ส่งค่าไปยัง API
            $response = Http::withToken($token)
                ->acceptJson()
                ->patch($this->apiUrl . '/' . $id, [
                    'name'         => (string) $request->name,
                    'major'        => (string) $request->major,
                    'academicYear' => (string) $request->academicYear,
                    'level'        => (int) $request->level,
                    'isActive'     => $request->isActive == "1" ? true : false,
                ]);

            if ($response->successful()) {
                return back()->with('success', 'อัปเดตข้อมูลห้องเรียนสำเร็จ');
            }

            return back()->withErrors(['api_error' => 'แก้ไขไม่สำเร็จ: ' . ($response->json()['message'] ?? 'Status ' . $response->status())])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['api_error' => 'การเชื่อมต่อ API ขัดข้อง: ' . $e->getMessage()])->withInput();
        }
    }

    // ฟังก์ชันสร้าง (Store) - เพิ่มไว้ให้เผื่อยังไม่สมบูรณ์
    public function store(Request $request)
    {
        $token = session('api_token');
        try {
            $response = Http::withToken($token)->post($this->apiUrl, $request->all());
            if ($response->successful()) return back()->with('success', 'สร้างห้องเรียนสำเร็จ');
            return back()->withErrors($response->json()['message'] ?? 'สร้างไม่สำเร็จ');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}