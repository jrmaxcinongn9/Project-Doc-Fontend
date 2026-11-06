<?php

namespace App\Http\Controllers;

use App\Support\XlsxWriter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * ส่งออกคะแนนทั้งห้องเป็นไฟล์ Excel
 *
 * หนึ่งแถวต่อนักศึกษา หนึ่งคอลัมน์ต่องาน ท้ายตารางเป็นคะแนนรวมและจำนวนงานที่ส่ง
 * ครูเอาไปกรอกระบบของมหาวิทยาลัยต่อได้เลย ไม่ต้องลอกทีละคน
 */
class ScoreExportController extends Controller
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = (string) config('services.api_url');
    }

    public function classroom(string $id)
    {
        $token = session('api_token');

        if (!$token) {
            return redirect('/login/teacher')->withErrors('กรุณาเข้าสู่ระบบใหม่');
        }

        try {
            $get = fn (string $path) => Http::withToken($token)->acceptJson()->timeout(120)->get($this->apiUrl . $path);

            $roomRes = $get('/classroom/' . $id);
            // ห้องของครูคนอื่น API ตอบ 403 ให้เหมือนไม่มีห้องนี้
            abort_unless($roomRes->successful(), 404);

            $room = $roomRes->json();
            $assigns = collect($get('/assignment')->json() ?? []);
            $filesRes = $get('/files')->json() ?? [];
        } catch (ConnectionException $e) {
            return back()->withErrors('เซิร์ฟเวอร์กำลังเริ่มทำงาน กรุณาลองอีกครั้ง');
        }

        $roomId = self::idOf($room['_id'] ?? null);

        $assignments = $assigns
            ->filter(fn ($a) => self::idOf($a['classroom'] ?? null) === $roomId)
            ->sortBy(fn ($a) => $a['due_date'] ?? '9999')
            ->values();

        $files = collect($filesRes['files'] ?? $filesRes);

        $students = collect($room['students'] ?? [])
            ->filter(fn ($s) => is_array($s))
            ->sortBy(fn ($s) => $s['student_id'] ?? $s['name'] ?? '')
            ->values();

        $header = ['ลำดับ', 'รหัสนักศึกษา', 'ชื่อ - นามสกุล', 'อีเมล'];
        foreach ($assignments as $a) {
            $header[] = ($a['assignment_name'] ?? 'งาน') . (!empty($a['max_score']) ? ' (เต็ม ' . $a['max_score'] . ')' : '');
        }
        array_push($header, 'คะแนนรวม', 'คะแนนเต็มรวม', 'ส่งงานแล้ว');

        $rows = [$header];

        foreach ($students as $i => $s) {
            $sid = self::idOf($s['_id'] ?? null);
            $line = [$i + 1, (string) ($s['student_id'] ?? ''), $s['name'] ?? '', $s['email'] ?? ''];
            $total = 0;
            $max = 0;
            $sent = 0;

            foreach ($assignments as $a) {
                $aid = self::idOf($a['_id'] ?? null);
                $row = collect($a['students'] ?? [])->first(fn ($r) => self::idOf($r['student_id'] ?? null) === $sid);
                $submitted = $files->contains(fn ($f) => (string) ($f['assignment_id'] ?? '') === $aid
                    && (string) ($f['uploaded_by_id'] ?? '') === $sid);

                if ($submitted) $sent++;

                $score = $row['score'] ?? null;
                if ($score !== null) {
                    $line[] = (float) $score == (int) $score ? (int) $score : (float) $score;
                    $total += $score;
                    $max += (int) ($a['max_score'] ?? 0);
                } else {
                    $line[] = $submitted ? 'ยังไม่ตรวจ' : 'ไม่ส่ง';
                }
            }

            array_push($line, $total, $max, $sent . '/' . $assignments->count());
            $rows[] = $line;
        }

        $name = 'คะแนน-' . preg_replace('/[\\\\\/:*?"<>|]+/u', '-', $room['name'] ?? 'ห้องเรียน') . '-' . now('Asia/Bangkok')->format('Y-m-d');

        if (XlsxWriter::available()) {
            $path = XlsxWriter::write('คะแนน', $rows);

            return response()->download($path, $name . '.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        }

        // เครื่องที่ไม่มี ZipArchive ส่งเป็น CSV ที่ Excel เปิดภาษาไทยได้ (มี BOM)
        $csv = "\xEF\xBB\xBF";
        foreach ($rows as $r) {
            $csv .= implode(',', array_map(fn ($v) => '"' . str_replace('"', '""', (string) $v) . '"', $r)) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename*=UTF-8''" . rawurlencode($name . '.csv'),
        ]);
    }

    private static function idOf($v): ?string
    {
        if (is_array($v)) {
            $v = $v['_id'] ?? ($v['$oid'] ?? null);
            if (is_array($v)) $v = $v['$oid'] ?? null;
        }
        return $v === null ? null : (string) $v;
    }
}
