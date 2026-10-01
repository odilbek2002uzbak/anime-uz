<?php
// Konteyner ishga tushganda webhook ni avtomatik o'rnatadi (faqat CLI).
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once __DIR__ . '/lib.php';
if (app_env('BOT_TOKEN') === '') { fwrite(STDERR, "[webhook] BOT_TOKEN yo'q\n"); exit(0); }
$base = app_env('PUBLIC_URL') !== '' ? rtrim(app_env('PUBLIC_URL'), '/')
      : (app_env('RAILWAY_PUBLIC_DOMAIN') !== '' ? 'https://' . app_env('RAILWAY_PUBLIC_DOMAIN') : '');
if ($base === '') {
    fwrite(STDERR, "[webhook] Public domain yo'q. Railway > Settings > Networking > Generate Domain, so'ng Redeploy qiling.\n");
    exit(0);
}
$params = ['url' => $base . '/bot.php'];
if (app_env('DISABLE_WEBHOOK_SECRET') !== '1') { $params['secret_token'] = webhook_secret(); }
$r = tg_raw('setWebhook', $params);
echo "[webhook] " . $base . "/bot.php -> " . json_encode($r) . "\n";
