#!/bin/bash
set -e

# Apache: faqat bitta MPM (prefork) yoqilgan bo'lsin
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork >/dev/null 2>&1 || true
APP=/var/www/html
DATA_DIR="${DATA_DIR:-/data}"
PORT="${PORT:-80}"

# 1) Doimiy ma'lumotlar (admin sozlamalari, step fayllar...) Volume ga ulanadi
mkdir -p "$DATA_DIR"
for d in admin step steps tizim tugma matn images; do
  mkdir -p "$DATA_DIR/$d"
  if [ -e "$APP/$d" ] && [ ! -L "$APP/$d" ]; then rm -rf "$APP/$d"; fi
  ln -sfn "$DATA_DIR/$d" "$APP/$d"
done
chown -R www-data:www-data "$DATA_DIR"
if ! mountpoint -q "$DATA_DIR" 2>/dev/null; then
  echo "[warn] $DATA_DIR Volume emas! Redeploy qilsangiz admin sozlamalari/step fayllar o'chadi. Railway'da Volume ulang (mount path: $DATA_DIR)."
fi

# 2) Apache Railway PORT ida tinglasin
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# 3) Jadvallar va webhook
php "$APP/migrate.php" || echo "[warn] migrate muvaffaqiyatsiz"
php "$APP/set_webhook.php" || true

# 4) VIP kunlarini kamaytirish (eski cron o'rniga): har soatda, kuniga bir marta ta'sir qiladi
( while true; do sleep 3600; curl -fsS "http://127.0.0.1:${PORT}/bot.php?update=vip" >/dev/null 2>&1 || true; done ) &

exec apache2-foreground
