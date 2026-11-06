<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * แจ้งเตือนในเว็บ ใช้แทนอีเมลที่ยังส่งไม่ได้
 *
 * ไม่ได้เก็บแจ้งเตือนแยกในฐานข้อมูล คำนวณจากข้อมูลจริงทุกครั้งที่เปิด
 *   นักศึกษา: อาจารย์ให้คะแนนหรือความเห็นแล้ว, งานใกล้ครบกำหนดที่ยังไม่ส่ง
 *   ครู: นักศึกษาส่งงานใหม่ในห้องของตัวเองที่ยังไม่ได้ให้คะแนน
 *   admin: บัญชีที่รออนุมัติ
 *
 * อ่านแล้วหรือยังเก็บเป็นเวลาที่เปิดดูล่าสุดไว้ใน session
 */
class NotificationController extends Controller
{
    private const LOOKBACK_DAYS = 14;
    private const DUE_SOON_DAYS = 3;

    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function index(Request $request)
    {
        $token = session('api_token');
        $role = session('user.role');

        if (!$token || !$role) {
            return response()->json(['items' => [], 'unread' => 0], 401);
        }

        try {
            $items = match ($role) {
                'USER'    => $this->forStudent($token),
                'TEACHER' => $this->forTeacher($token),
                'ADMIN'   => $this->forAdmin($token),
                default   => collect(),
            };
        } catch (ConnectionException $e) {
            return response()->json(['items' => [], 'unread' => 0, 'error' => 'เซิร์ฟเวอร์กำลังเริ่มทำงาน']);
        }

        $seen = session('notifications_seen_at');
        $seenAt = $seen ? Carbon::parse($seen) : null;

        $items = $items
            ->filter(fn ($i) => $i['at'] !== null)
            ->sortByDesc(fn ($i) => $i['at']->timestamp)
            ->take(20)
            ->values()
            ->map(fn ($i) => [
                'text'   => $i['text'],
                'url'    => $i['url'],
                'at'     => $i['at']->toIso8601String(),
                'ago'    => $i['at']->locale('th')->diffForHumans(),
                'unread' => !$seenAt || $i['at']->greaterThan($seenAt),
            ]);

        return response()->json([
            'items'  => $items,
            'unread' => $items->where('unread', true)->count(),
        ]);
    }

    public function seen()
    {
        session(['notifications_seen_at' => now()->toIso8601String()]);

        return response()->json(['ok' => true]);
    }

    private function get(string $token, string $path): array
    {
        $res = Http::withToken($token)->acceptJson()->connectTimeout(10)->timeout(30)->get($this->apiUrl . $path);

        return $res->successful() ? ($res->json() ?? []) : [];
    }

    private function forStudent(string $token): Collection
    {
        $since = now()->subDays(self::LOOKBACK_DAYS);
        $items = collect();

        foreach ($this->get($token, '/assignment/my-scores') as $s) {
            if (empty($s['feedback_at']) || ($s['score'] === null && empty($s['feedback']))) continue;
            $at = Carbon::parse($s['feedback_at']);
            if ($at->lessThan($since)) continue;

            $score = $s['score'] !== null ? ' ได้ ' . $s['score'] . (isset($s['max_score']) ? '/' . $s['max_score'] : '') . ' คะแนน' : '';
            $items->push([
                'text' => 'อาจารย์ตรวจ "' . ($s['assignment_name'] ?? 'งาน') . '" แล้ว' . $score,
                'url'  => '/student/scores',
                'at'   => $at,
            ]);
        }

        $sent = collect($this->get($token, '/files/my'))->pluck('assignment_id')->map(fn ($v) => (string) $v);

        foreach ($this->get($token, '/classroom/my') as $room) {
            foreach ($room['assignments'] ?? [] as $a) {
                if (empty($a['due_date']) || $sent->contains((string) $a['_id'])) continue;
                $due = Carbon::parse($a['due_date']);
                if ($due->isPast() || $due->greaterThan(now()->addDays(self::DUE_SOON_DAYS))) continue;

                $items->push([
                    'text' => '"' . ($a['assignment_name'] ?? 'งาน') . '" ครบกำหนดส่ง '
                        . $due->copy()->timezone('Asia/Bangkok')->locale('th')->translatedFormat('j M H:i') . ' ยังไม่ได้ส่ง',
                    'url'  => '/student/classrooms/' . $room['_id'],
                    // นับเป็นแจ้งเตือนใหม่ตั้งแต่ตอนที่งานเข้าช่วงใกล้กำหนด
                    'at'   => $due->copy()->subDays(self::DUE_SOON_DAYS),
                ]);
            }
        }

        return $items;
    }

    private function forTeacher(string $token): Collection
    {
        $since = now()->subDays(self::LOOKBACK_DAYS);

        // API คืนเฉพาะงานและไฟล์ในห้องของครูคนนี้
        $assigns = collect($this->get($token, '/assignment'))->keyBy(fn ($a) => (string) ($a['_id'] ?? ''));
        $files = $this->get($token, '/files');
        $files = collect($files['files'] ?? $files);

        return $files
            ->filter(fn ($f) => !empty($f['createdAt']) && Carbon::parse($f['createdAt'])->greaterThan($since))
            // หลายไฟล์ของคนเดียวในงานเดียวนับเป็นแจ้งเตือนเดียว ใช้ไฟล์ล่าสุด
            ->sortByDesc('createdAt')
            ->unique(fn ($f) => ($f['assignment_id'] ?? '') . '|' . ($f['uploaded_by_id'] ?? ''))
            ->filter(function ($f) use ($assigns) {
                $a = $assigns->get((string) ($f['assignment_id'] ?? ''));
                if (!$a) return false;
                $row = collect($a['students'] ?? [])->first(fn ($s) => (string) ($s['student_id'] ?? '') === (string) ($f['uploaded_by_id'] ?? ''));
                // ให้คะแนนแล้วไม่ต้องเตือน
                return ($row['score'] ?? null) === null;
            })
            ->map(fn ($f) => [
                'text' => ($f['uploaded_by_name'] ?? $f['uploaded_by_email'] ?? 'นักศึกษา') . ' ส่ง "'
                    . ($assigns->get((string) $f['assignment_id'])['assignment_name'] ?? 'งาน') . '" รอให้คะแนน',
                'url'  => '/teacher/assignment-view/' . $f['assignment_id'],
                'at'   => Carbon::parse($f['createdAt']),
            ])
            ->values();
    }

    private function forAdmin(string $token): Collection
    {
        return collect($this->get($token, '/user/pending'))->map(fn ($u) => [
            'text' => 'บัญชี ' . ($u['name'] ?? $u['email'] ?? '-') . ' รออนุมัติ',
            'url'  => '/admin/pending',
            'at'   => !empty($u['createdAt']) ? Carbon::parse($u['createdAt']) : null,
        ]);
    }
}
