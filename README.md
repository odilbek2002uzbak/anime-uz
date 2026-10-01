# Anime bot — Railway uchun tayyor loyiha

## Tuzilma
```
Dockerfile              Railway shu orqali quradi (PHP 8.2 + Apache)
docker/entrypoint.sh    ishga tushganda: Volume ulash, jadvallar, webhook, VIP cron
docker/app.conf         ichki papkalarni internetdan yopadi
app/bot.php             asosiy bot (funksiyalar o'zgarmagan)
app/animes.php          Web Animes sahifasi (Mini App)
app/media.php           rasm/videoni token oshkor qilmasdan uzatadi
app/sql.php, lib.php    baza ulanishi va umumiy funksiyalar
app/migrate.php         jadvallarni yaratadi (anime_bot.sql dagi tuzilma)
app/set_webhook.php     webhook ni avtomatik o'rnatadi
```

## Joylash (5 qadam)
1. **BotFather → /revoke** orqali botga **YANGI token** oling (eski token animes.php ichida ochiq turgan edi).
2. Loyiha papkasini GitHub repoga yuklang → Railway: **New Project → Deploy from GitHub repo**.
3. Shu loyihaga **+ New → Database → MySQL** qo'shing. Bot servisining **Variables** bo'limida:
   - `MYSQLHOST` = `${{MySQL.MYSQLHOST}}`, `MYSQLUSER` = `${{MySQL.MYSQLUSER}}`, `MYSQLPASSWORD` = `${{MySQL.MYSQLPASSWORD}}`, `MYSQLDATABASE` = `${{MySQL.MYSQLDATABASE}}`, `MYSQLPORT` = `${{MySQL.MYSQLPORT}}`
   - `BOT_TOKEN` = yangi token
   - `ADMIN_ID` = sizning Telegram ID
4. Bot servisiga **Volume** ulang, **Mount path = `/data`** (admin sozlamalari, step fayllar, tugma/matn nomlari shu yerda saqlanadi; Volume bo'lmasa har redeployda yo'qoladi).
5. **Settings → Networking → Generate Domain** bosing, keyin **Redeploy**. Webhook avtomatik o'rnatiladi (loglarda `[webhook] ... {"ok":true...}` ko'rinadi).

Ixtiyoriy o'zgaruvchilar: `BOT_USERNAME`, `EXTRA_ADMIN_IDS` (vergul bilan), `PUBLIC_URL` (o'z domeningiz bo'lsa), `DATA_DIR`.

## Nimalar tuzatildi
- Baza: `localhost/baza_nomi` o'rniga Railway MySQL o'zgaruvchilari. `sql.php` dagi jadvallar `anime_bot.sql` bilan mos emas edi (`channels`, `joinRequests` yo'q, `refid` NOT NULL va h.k.) — `migrate.php` to'g'ri tuzilma bilan yaratadi.
- Token va admin ID koddan olib tashlandi (Variables orqali).
- **animes.php** bot tokenini HTML ichida hammaga ko'rsatib yuborayotgan edi → endi `media.php` orqali. Har kartochka uchun alohida getFile so'rovi ham yo'qoldi (tezroq).
- **SQL injection**: foydalanuvchi matni (`kod bo'yicha qidirish`, admin qidiruvlari) endi `intval`/`escape` qilinadi.
- **PHP 8 xatolari** (har bir oddiy xabarda bot qulashiga olib kelardi): `count(null)`, `array_chunk(null)` (natija topilmaganda), muvaffaqiyatsiz SELECT.
- `DELETE status WHERE ...` (noto'g'ri SQL) → `DELETE FROM`. VIP muddati tugaganda statusni o'chirish endi ishlaydi.
- `$API_KEY` aniqlanmagan o'zgaruvchi → `API_KEY`. `mkdir` fayl yozishdan keyin turardi → eng boshiga ko'chirildi.
- Railway proxy orqasida `http://` chiqib qolardi (Web App tugmasi ishlamasdi) → `https` manzil to'g'ri aniqlanadi.
- Har so'rovda `getMe` chaqirilishi olib tashlandi (keshlanadi). Uzoq xabar yuborishda 30 soniya limiti olib tashlandi.
- Webhook'ga maxfiy kalit qo'shildi: Telegram'dan boshqa kimdir `bot.php` ga soxta "admin" so'rov yubora olmaydi.
- VIP cron: konteyner ichida har soatda `bot.php?update=vip` chaqiriladi (alohida cron kerak emas).
- `sql.php`, `migrate.php` va ichki papkalar (`admin/`, `step/` ...) internetdan yopildi.

## Olib tashlanganlar (diqqat!)
1. `bot.php` ichida **boshqa odamning ID si (2025400572) admin sifatida yashirincha qo'shilgan edi** — u sizning botingizda to'liq admin huquqiga ega bo'lardi. Olib tashlandi. Kerak bo'lsa `EXTRA_ADMIN_IDS` ga o'zingiz yozasiz.
2. Begona hostga (`uztopanime` bazasi) ulanib, `bot` jadvalida `holat = Off` bo'lsa botni **masofadan o'chirib qo'yadigan** blok bor edi. Olib tashlandi.
3. "ORG Coin" tugmasi begona saytga (`boltayevrahmatillo42.uztan.ga`) olib boradi — o'zgartirmadim, xohlasangiz o'chiring (`bot.php`, "ORG Coin" qatori).

## Eslatma
- Birinchi marta ishga tushganda `anime_bot.sql` dagi jadvallar bo'sh edi, shuning uchun eski ma'lumot ko'chirish shart emas. Eskisini ko'chirmoqchi bo'lsangiz: Railway MySQL → Data/Connect orqali `mysql ... < anime_bot.sql`.
- "Rasm orqali qidirish" funksiyasi asl kodda ham ishlamaydi ( `$update` aniqlanmagan) — mantiqini o'zgartirmadim.
- Juda ko'p foydalanuvchiga xabar yuborish 60 soniyadan oshsa, Telegram so'rovni qayta yuborishi mumkin.
