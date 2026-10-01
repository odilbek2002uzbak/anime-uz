<?php
// Umumiy yordamchi funksiyalar (bot.php, animes.php, media.php, migrate.php ishlatadi)

function app_env($key, $default = '') {
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

// Botning tashqi (https) manzili: Railway proxy orqasida ham to'g'ri https qaytaradi
function public_base_url() {
    $u = app_env('PUBLIC_URL');
    if ($u !== '') return rtrim($u, '/');
    $d = app_env('RAILWAY_PUBLIC_DOMAIN');
    if ($d !== '') return 'https://' . $d;
    $proto = $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '';
    if ($proto !== '') {
        $proto = trim(explode(',', $proto)[0]);
    } else {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    }
    $host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return $proto . '://' . $host;
}

// Telegram API ga oddiy so'rov (massiv qaytaradi)
function tg_raw($method, $params = []) {
    $token = app_env('BOT_TOKEN');
    if ($token === '') return null;
    $ch = curl_init("https://api.telegram.org/bot$token/$method");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_TIMEOUT => 30,
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    return $res ? json_decode($res, true) : null;
}

// Bot username (getMe ni har updatede chaqirmaslik uchun keshlanadi)
function cached_bot_username() {
    $env = ltrim(app_env('BOT_USERNAME'), '@');
    if ($env !== '') return $env;
    $f = sys_get_temp_dir() . '/botname_' . md5(app_env('BOT_TOKEN')) . '.txt';
    $n = @file_get_contents($f);
    if ($n) return trim($n);
    $r = tg_raw('getMe');
    $n = $r['result']['username'] ?? '';
    if ($n !== '') @file_put_contents($f, $n);
    return $n;
}

// Webhook uchun maxfiy kalit (BOT_TOKEN dan hosil qilinadi, alohida saqlash shart emas)
function webhook_secret() {
    return substr(hash_hmac('sha256', 'tg-webhook', app_env('BOT_TOKEN')), 0, 48);
}
