<?php
require_once __DIR__ . '/lib.php';
include __DIR__ . '/sql.php';

$bot_username = cached_bot_username();
?><!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Anime Ro'yxati</title>
  <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      box-sizing: border-box;
      font-family: 'Rubik', sans-serif;
    }
    body {
      margin: 0;
      background: linear-gradient(to right, #0f172a, #1e3a8a);
    }
    .card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background: linear-gradient(145deg, #1e293b, #334155);
    }
    .card:hover {
      transform: translateY(-8px);
      box-shadow: 0 16px 32px rgba(0, 0, 0, 0.5);
    }
    .watch-btn {
      transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
      box-shadow: 0 4px 12px rgba(59, 130, 246, 0.5);
    }
    .watch-btn:hover {
      background-color: #1d4ed8;
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(59, 130, 246, 0.7);
    }
    .header {
      animation: fadeIn 1s ease-in-out;
    }
    @keyframes fadeIn {
      0% { opacity: 0; transform: translateY(-20px); }
      100% { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="min-h-screen text-white p-4 md:p-8">
  <div class="max-w-7xl mx-auto">
    <h2 class="header text-3xl md:text-4xl font-bold text-center mb-10 text-blue-300 drop-shadow-lg">🎬 Anime Ro'yxati</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <?php
      $sql = mysqli_query($connect, "SELECT * FROM animelar ORDER BY id DESC");
      while ($sql && ($row = mysqli_fetch_assoc($sql))) {
        $id     = (int)$row['id'];
        $nom    = htmlspecialchars($row['nom'], ENT_QUOTES);
        $qismi  = htmlspecialchars($row['qismi'], ENT_QUOTES);
        $davlat = htmlspecialchars($row['davlat'], ENT_QUOTES);
        $tili   = htmlspecialchars($row['tili'], ENT_QUOTES);
        $yili   = htmlspecialchars($row['yili'], ENT_QUOTES);
        $janr   = htmlspecialchars($row['janri'], ENT_QUOTES);

        $file_id = $row['rams'];
        $telegram_link = "https://t.me/" . $bot_username . "?start=$id";

        // Bot tokeni sahifada ko'rinmasligi uchun fayl media.php orqali uzatiladi
        $media_url = 'media.php?f=' . urlencode($file_id);
        $is_video = strtoupper(substr($file_id, 0, 1)) === 'B';   // bot.php dagi qoida bilan bir xil
        if ($file_id === '') {
          $media_html = "<div class='w-full h-48 flex items-center justify-center bg-red-950 rounded-t-xl text-red-300'>❌ Fayl yuklanmadi</div>";
        } elseif ($is_video) {
          $media_html = "<video controls preload='metadata' class='w-full h-48 object-cover rounded-t-xl' onerror='mediaFail(this)'><source src='$media_url' type='video/mp4'>Video ko‘rsatilmayapti.</video>";
        } else {
          $media_html = "<img src='$media_url' alt='$nom' loading='lazy' class='w-full h-48 object-cover rounded-t-xl' onerror='mediaFail(this)'>";
        }

        echo "
          <a href='$telegram_link' class='card rounded-xl overflow-hidden cursor-pointer'>
            $media_html
            <div class='p-5'>
              <h3 class='text-lg font-semibold text-blue-200 mb-3'>$nom</h3>
              <p class='text-sm text-blue-300 mb-1'>🎞 Qismlar: $qismi</p>
              <p class='text-sm text-blue-300 mb-1'>🌍 Davlat: $davlat</p>
              <p class='text-sm text-blue-300 mb-1'>🗣 Til: $tili</p>
              <p class='text-sm text-blue-300 mb-1'>📅 Yili: $yili</p>
              <p class='text-sm text-blue-300 mb-4'>🏷 Janr: $janr</p>
              <button onclick='window.location.href=\"$telegram_link\"' class='watch-btn w-full bg-blue-500 text-white font-medium py-2 rounded-lg text-center'>Tomosha qilish</button>
            </div>
          </a>
        ";
      }
      ?>
    </div>
  </div>
  <script>
    function mediaFail(el) {
      var d = document.createElement('div');
      d.className = 'w-full h-48 flex items-center justify-center bg-red-950 rounded-t-xl text-red-300';
      d.textContent = '❌ Fayl yuklanmadi';
      el.replaceWith(d);
    }
  </script>
</body>
</html>
