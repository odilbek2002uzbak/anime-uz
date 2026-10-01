<?php
// Telegram faylini (rasm/video) bot tokenini yashirgan holda foydalanuvchiga uzatadi.
require_once __DIR__ . '/lib.php';
error_reporting(0);
while (ob_get_level()) { ob_end_clean(); }

$token = app_env('BOT_TOKEN');
$fid = $_GET['f'] ?? '';
if ($token === '' || !preg_match('/^[A-Za-z0-9_\-]{10,300}$/', $fid)) { http_response_code(404); exit; }

$cache = sys_get_temp_dir() . '/tgfile_' . md5($fid) . '.txt';
$path = (is_file($cache) && time() - filemtime($cache) < 3000) ? trim(file_get_contents($cache)) : '';
if ($path === '') {
    $r = tg_raw('getFile', ['file_id' => $fid]);
    $path = $r['result']['file_path'] ?? '';
    if ($path === '') { http_response_code(404); exit; }
    @file_put_contents($cache, $path);
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$types = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif','mp4'=>'video/mp4'];
$ct = $types[$ext] ?? 'application/octet-stream';

$status = 200; $fwd = []; $started = false; $failed = false;
$reqHeaders = [];
if (!empty($_SERVER['HTTP_RANGE'])) { $reqHeaders[] = 'Range: ' . $_SERVER['HTTP_RANGE']; }

$ch = curl_init("https://api.telegram.org/file/bot$token/$path");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => $reqHeaders,
    CURLOPT_TIMEOUT => 300,
    CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$status, &$fwd) {
        if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) { $status = (int)$m[1]; $fwd = []; }
        elseif (preg_match('/^(Content-Length|Content-Range|Accept-Ranges):\s*(.+)$/i', trim($h), $m)) { $fwd[$m[1]] = trim($m[2]); }
        return strlen($h);
    },
    CURLOPT_WRITEFUNCTION => function ($c, $d) use (&$started, &$failed, &$status, &$fwd, $ct) {
        if (!$started) {
            if ($status >= 400) { $failed = true; return 0; }
            http_response_code($status);
            header("Content-Type: $ct");
            header('Cache-Control: public, max-age=86400');
            foreach ($fwd as $k => $v) { header("$k: $v"); }
            $started = true;
        }
        echo $d;
        flush();
        return strlen($d);
    },
]);
curl_exec($ch);
curl_close($ch);
if (!$started) { http_response_code(404); }
