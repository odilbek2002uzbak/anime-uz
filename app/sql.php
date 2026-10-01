<?php
// Baza ulanishi. Railway MySQL o'zgaruvchilaridan avtomatik o'qiydi.
// Jadvallar bu yerda emas, migrate.php da yaratiladi (konteyner ishga tushganda).
mysqli_report(MYSQLI_REPORT_OFF);

$db_host = getenv('DB_HOST') ?: getenv('MYSQLHOST') ?: '';
$db_user = getenv('DB_USER') ?: getenv('MYSQLUSER') ?: '';
$db_pass = getenv('DB_PASS') ?: getenv('MYSQLPASSWORD') ?: '';
$db_name = getenv('DB_NAME') ?: getenv('MYSQLDATABASE') ?: '';
$db_port = getenv('DB_PORT') ?: getenv('MYSQLPORT') ?: '3306';

if ($db_host === '' && ($__url = getenv('MYSQL_URL') ?: getenv('DATABASE_URL'))) {
    $__p = parse_url($__url);
    $db_host = $__p['host'] ?? '';
    $db_user = urldecode($__p['user'] ?? '');
    $db_pass = urldecode($__p['pass'] ?? '');
    $db_name = ltrim($__p['path'] ?? '', '/');
    $db_port = $__p['port'] ?? 3306;
}
if ($db_host === '') { $db_host = 'localhost'; }

$connect = mysqli_connect($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
if (!$connect) {
    error_log('DB ulanish xatosi: ' . mysqli_connect_error());
    http_response_code(503);   // Telegram keyinroq qayta yuboradi
    exit;
}
mysqli_set_charset($connect, 'utf8mb4');
