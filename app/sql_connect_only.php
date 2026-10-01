<?php
// migrate.php uchun: ulanish bo'lmasa exit qilmaydi
$connect = null;
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
$connect = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, (int)$db_port) ?: null;
if ($connect) { mysqli_set_charset($connect, 'utf8mb4'); }
