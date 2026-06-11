#!/usr/bin/env bash
#
# Bulk QR Code Generator — via curl ke endpoint /generate-qr
#
# Menembak endpoint GET /generate-qr?short_link=<short_url> untuk setiap link
# yang belum punya QR code (kolom base64 kosong). Tidak butuh `php artisan`.
#
# Daftar short_url diambil dari database, lalu curl ditembak ke localhost:8080
# supaya tidak terkendala TLS / nginx-proxy. Script otomatis mendeteksi apakah
# dijalankan DI DALAM container (langsung) atau DI HOST (lewat docker exec).
#
# Penggunaan:
#   ./bulk-generate-qrcode-curl.sh                       # Generate semua yg kosong
#   ./bulk-generate-qrcode-curl.sh --force               # Generate ulang semua link
#   ./bulk-generate-qrcode-curl.sh --limit=50            # Maksimal 50 link
#   ./bulk-generate-qrcode-curl.sh --short-url=abc123    # Satu link spesifik
#   ./bulk-generate-qrcode-curl.sh --delay=0.2           # Jeda antar request (detik)
#   ./bulk-generate-qrcode-curl.sh --container=NAMA      # Nama container (default: myits-url-shortener-web)
#   ./bulk-generate-qrcode-curl.sh --base-url=URL        # Base URL endpoint (default: http://localhost:8080)
#   ./bulk-generate-qrcode-curl.sh --help
#
set -euo pipefail

# ─── Default ──────────────────────────────────────────────────────────────────
CONTAINER="myits-url-shortener-web"
BASE_URL="http://localhost:8080"
FORCE=0
LIMIT=0
DELAY=0
SHORT_URL=""

# ─── Parse argumen ────────────────────────────────────────────────────────────
for arg in "$@"; do
    case "$arg" in
        --force)          FORCE=1 ;;
        --limit=*)        LIMIT="${arg#*=}" ;;
        --delay=*)        DELAY="${arg#*=}" ;;
        --short-url=*)    SHORT_URL="${arg#*=}" ;;
        --container=*)    CONTAINER="${arg#*=}" ;;
        --base-url=*)     BASE_URL="${arg#*=}" ;;
        --help|-h)
            sed -n '2,30p' "$0" | sed 's/^# \{0,1\}//'
            exit 0 ;;
        *)
            echo "[ERROR] Argumen tidak dikenal: $arg" >&2
            exit 1 ;;
    esac
done

# ─── Deteksi lingkungan: di dalam container atau di host ───────────────────────
# Di dalam container: jalankan php/curl langsung. Di host: bungkus dgn docker exec.
if [[ -f /.dockerenv ]]; then
    IN_CONTAINER=1
else
    IN_CONTAINER=0
    if ! command -v docker >/dev/null 2>&1; then
        echo "[ERROR] Perintah 'docker' tidak ada. Jalankan script ini dari dalam container, atau install docker di host." >&2
        exit 1
    fi
    if ! docker ps --format '{{.Names}}' | grep -qx "$CONTAINER"; then
        echo "[ERROR] Container '$CONTAINER' tidak berjalan." >&2
        exit 1
    fi
fi

# Jalankan sebuah perintah di lingkungan aplikasi (lokal kalau di container,
# lewat docker exec kalau di host). Env QR_* diteruskan ke proses anak.
run_in_app() {
    if [[ "$IN_CONTAINER" -eq 1 ]]; then
        env QR_SHORT="$SHORT_URL" QR_FORCE="$FORCE" QR_LIMIT="$LIMIT" sh -c "$1"
    else
        docker exec -e QR_SHORT="$SHORT_URL" -e QR_FORCE="$FORCE" -e QR_LIMIT="$LIMIT" \
            "$CONTAINER" sh -c "$1"
    fi
}

# Jalankan curl di lingkungan aplikasi (tanpa env QR_*).
run_curl() {
    if [[ "$IN_CONTAINER" -eq 1 ]]; then
        curl "$@"
    else
        docker exec "$CONTAINER" curl "$@"
    fi
}

# ─── Ambil daftar short_url dari DB ───────────────────────────────────────────
# Dijalankan memakai bootstrap Lumen + DB facade (tanpa artisan).
PHP_QUERY='cd /var/www/html && php -r '\''
    require "vendor/autoload.php";
    $app = require "bootstrap/app.php";
    $q = Illuminate\Support\Facades\DB::table("links");
    $short = getenv("QR_SHORT");
    $force = getenv("QR_FORCE") === "1";
    $limit = (int) getenv("QR_LIMIT");
    if ($short !== "") {
        $q->where("short_url", $short);
    } elseif (!$force) {
        $q->where(function($w){ $w->whereNull("base64")->orWhereRaw("CAST(base64 AS NVARCHAR(1)) = \x27\x27"); });
    }
    $q->orderBy("id", "ASC");
    if ($limit > 0) { $q->limit($limit); }
    foreach ($q->get() as $l) { echo $l->short_url, PHP_EOL; }
'\'''

echo "[INFO] Mengambil daftar link dari database..."
mapfile -t LINKS < <(run_in_app "$PHP_QUERY" 2>/dev/null)

TOTAL=${#LINKS[@]}
if [[ "$TOTAL" -eq 0 ]]; then
    echo "[INFO] Tidak ada link yang perlu QR code."
    exit 0
fi

echo "[INFO] $TOTAL link akan diproses via $BASE_URL/generate-qr"
echo

# ─── Loop curl ────────────────────────────────────────────────────────────────
SUCCESS=0
FAILED=0
N=0

for SU in "${LINKS[@]}"; do
    [[ -z "$SU" ]] && continue
    N=$((N + 1))
    printf '[%d/%d] %s ' "$N" "$TOTAL" "$SU"

    # -G + --data-urlencode aman utk karakter khusus pada short_link
    HTTP_CODE=$(run_curl -s -o /dev/null -w '%{http_code}' \
        -G "$BASE_URL/generate-qr" --data-urlencode "short_link=$SU" 2>/dev/null || echo "000")

    if [[ "$HTTP_CODE" == "200" ]]; then
        SUCCESS=$((SUCCESS + 1))
        echo "OK"
    else
        FAILED=$((FAILED + 1))
        echo "GAGAL (HTTP $HTTP_CODE)"
    fi

    # Jeda antar request bila diminta
    if [[ "$DELAY" != "0" ]]; then
        sleep "$DELAY"
    fi
done

# ─── Ringkasan ────────────────────────────────────────────────────────────────
echo
echo "Selesai! $SUCCESS berhasil, $FAILED gagal dari $TOTAL total."
[[ "$FAILED" -gt 0 ]] && exit 1 || exit 0
