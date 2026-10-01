<?php
// Konteyner ishga tushganda bir marta ishlaydi: jadvallarni yaratadi (mavjud bo'lsa tegmaydi).
mysqli_report(MYSQLI_REPORT_OFF);
$c = null;
for ($i = 1; $i <= 30; $i++) {
    ob_start(); include __DIR__ . '/sql_connect_only.php'; $out = ob_get_clean();
    if (!empty($connect)) { $c = $connect; break; }
    fwrite(STDERR, "[migrate] baza hali tayyor emas ($i/30)...\n");
    sleep(2);
}
if (!$c) { fwrite(STDERR, "[migrate] bazaga ulanib bo'lmadi\n"); exit(1); }

$cs = "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
$tables = [
"anime_datas" => "CREATE TABLE IF NOT EXISTS `anime_datas` (
  `data_id` int NOT NULL AUTO_INCREMENT,
  `id` text NOT NULL,
  `file_id` text NOT NULL,
  `qism` text NOT NULL,
  `sana` text,
  PRIMARY KEY (`data_id`),
  KEY `idx_anime_id` (`id`(32))
) $cs",
"animelar" => "CREATE TABLE IF NOT EXISTS `animelar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` text NOT NULL,
  `rams` text NOT NULL,
  `qismi` text NOT NULL,
  `davlat` text NOT NULL,
  `tili` text NOT NULL,
  `yili` text NOT NULL,
  `janri` text NOT NULL,
  `qidiruv` int NOT NULL,
  `sana` text NOT NULL,
  `aniType` text,
  `like` int DEFAULT '0',
  `deslike` int DEFAULT '0',
  PRIMARY KEY (`id`)
) $cs",
"channels" => "CREATE TABLE IF NOT EXISTS `channels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `channelId` varchar(32) NOT NULL,
  `channelType` varchar(255) NOT NULL,
  `channelLink` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) $cs",
"joinRequests" => "CREATE TABLE IF NOT EXISTS `joinRequests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `channelId` varchar(32) NOT NULL,
  `userId` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_join` (`channelId`,`userId`(64))
) $cs",
"kabinet" => "CREATE TABLE IF NOT EXISTS `kabinet` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(250) NOT NULL,
  `pul` varchar(250) NOT NULL,
  `pul2` varchar(250) NOT NULL,
  `odam` varchar(250) NOT NULL,
  `ban` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_kabinet_user` (`user_id`)
) $cs",
"send" => "CREATE TABLE IF NOT EXISTS `send` (
  `send_id` int NOT NULL AUTO_INCREMENT,
  `time1` text NOT NULL,
  `time2` text NOT NULL,
  `start_id` text NOT NULL,
  `stop_id` text NOT NULL,
  `admin_id` text NOT NULL,
  `message_id` text NOT NULL,
  `reply_markup` text NOT NULL,
  `step` text NOT NULL,
  `time3` text NOT NULL,
  `time4` text NOT NULL,
  `time5` text NOT NULL,
  PRIMARY KEY (`send_id`)
) $cs",
"status" => "CREATE TABLE IF NOT EXISTS `status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(250) NOT NULL,
  `kun` varchar(250) NOT NULL,
  `date` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status_user` (`user_id`)
) $cs",
"user_id" => "CREATE TABLE IF NOT EXISTS `user_id` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` varchar(250) NOT NULL,
  `status` text NOT NULL,
  `refid` varchar(11) DEFAULT NULL,
  `sana` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid_user` (`user_id`)
) $cs",
];
foreach ($tables as $name => $sql) {
    if (mysqli_query($c, $sql)) { echo "[migrate] $name OK\n"; }
    else { fwrite(STDERR, "[migrate] $name XATO: " . mysqli_error($c) . "\n"); }
}
