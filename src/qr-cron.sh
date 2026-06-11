#!/bin/sh
#
# qr-cron.sh — dijalankan oleh crond untuk men-generate QR code otomatis.
#
# Menjalankan bulk-generate-qrcode-curl.sh di dalam tmux session 'qrgen'
# (detached), dibatasi LIMIT link per jalan. Anti-overlap: kalau sesi sebelumnya
# masih jalan, langsung skip supaya tidak menumpuk dua proses sekaligus.
#
# Atur jumlah per jalan lewat env QR_LIMIT (default 5000), mis. di crontab:
#   5 * * * * QR_LIMIT=10000 /var/www/html/qr-cron.sh
#
# Pantau progres dari dalam container:
#   tmux attach -t qrgen
# Lihat log:
#   tail -f /var/log/qrgen.log
#
SESSION="qrgen"
SCRIPT="/var/www/html/bulk-generate-qrcode-curl.sh"
LOG="/var/log/qrgen.log"
# Jumlah link yang di-generate tiap jalan (override lewat env QR_LIMIT)
LIMIT="${QR_LIMIT:-5000}"

# Skip kalau sesi generate sebelumnya masih berjalan
if tmux has-session -t "$SESSION" 2>/dev/null; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] sesi '$SESSION' masih jalan — skip" >> "$LOG"
    exit 0
fi

echo "[$(date '+%Y-%m-%d %H:%M:%S')] memulai generate QR (limit $LIMIT) di sesi '$SESSION'" >> "$LOG"

# Jalankan script di tmux session detached; output diteruskan ke log
tmux new-session -d -s "$SESSION" \
    "bash '$SCRIPT' --limit=$LIMIT >> '$LOG' 2>&1; echo \"[\$(date '+%Y-%m-%d %H:%M:%S')] selesai\" >> '$LOG'"
