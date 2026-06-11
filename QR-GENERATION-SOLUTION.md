# QR Code Generation - Troubleshooting & Solutions

## Masalah yang Ditemukan dan Solusi

### 1. QR Code hanya menampilkan logo (tanpa QR pattern)
**Penyebab:** Logo di-paste ke QR code tanpa alpha blending yang benar.

**Solusi:** Tambahkan `imagealphablending()` dan `imagesavealpha()` sebelum paste logo.

**File yang diperbaiki:**
- `src/generate-qr.php` - Step 3: Merge logo
- `src/app/Console/Commands/GenerateQRCodes.php` - generateQRCode() method

### 2. bulk-generate-qrcode error di SQL Server (syntax backtick)
**Penyebab:** Script menggunakan backtick (\`) untuk table name yang tidak support di SQL Server.

**Solusi:** Gunakan square bracket (`[tableName]`) untuk SQL Server.

**File yang diperbaiki:**
- `src/bulk-generate-qrcode` - Line 118-126

### 3. QR Code kosong (hanya background putih) - CRITICAL
**Penyebab:** SVG dari SimpleSoftwareIO\QrCode menggunakan `<path>` element untuk QR modules, tapi script hanya parse `<rect>` elements.

**Solusi:** Ganti dari SimpleSoftwareIO\QrCode ke BaconQrCode langsung dengan SVG renderer yang menghasilkan `<rect>` elements (bukan `<path>`).

**File yang diperbaiki:**
- `src/bulk-generate-qrcode` - Menggunakan BaconQrCode ImageRenderer dengan SvgImageBackEnd

## Cara Generate QR Code

### Opsi 1: Via bulk-generate-qrcode (RECOMMENDED)
```bash
# Generate untuk satu link
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --short-url=nusantara77

# Generate semua link yang belum punya QR code
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode

# Generate ulang semua link (force regenerate)
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --force

# Dengan limit
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --limit=50

# Quiet mode (minimal output)
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --quiet
```

### Opsi 2: Via Artisan Command (di dalam container)
```bash
docker exec -it myits-url-shortener-web php artisan qrcode:generate --short-url=nusantara77
```

## Technical Details

### QR Code Generation Flow
1. Generate QR code sebagai SVG menggunakan BaconQrCode ImageRenderer dengan SvgImageBackEnd
2. Parse SVG XML untuk mendapatkan `<rect>` elements (QR modules)
3. Render modules ke GD image (PNG) dengan `imagefilledrectangle()`
4. Resize dan paste logo di tengah dengan alpha blending
5. Encode ke base64
6. Save ke database table `links.base64`

### QR Code Specifications
- Size: 500x500 pixels
- Error correction: H (High) - ~30% data dapat dikoreksi
- Logo size: 25% dari QR code width
- Format: PNG base64 encoded

### Alpha Blending Fix
```php
// BEFORE (wrong - logo background solid menimpa QR)
imagecopy($img, $logoBg, $dstX, $dstY, 0, 0, $resizedLogoW, $resizedLogoH);

// AFTER (correct - dengan alpha blending)
imagealphablending($img, true);
imagesavealpha($img, true);
imagecopy($img, $logoBg, $dstX, $dstY, 0, 0, $resizedLogoW, $resizedLogoH);
```

### SQL Server Syntax Fix
```php
// BEFORE (MySQL style - ERROR di SQL Server)
$sql = "SELECT id FROM `{$tableName}` WHERE ...";

// AFTER (SQL Server compatible)
$sql = "SELECT id FROM [{$tableName}] WHERE ...";
```

## Testing & Verification

```bash
# Verify QR code berhasil di-generate
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --short-url=nusantara77 --force --quiet

# Check base64 content dan validitas QR code
docker exec -it myits-url-shortener-web php -r "
\$pdo = new PDO('sqlsrv:Server=oltp.its.ac.id,1433;Database=SHORTENER', 'shortener_app', 's1ngk4tj3lasp4dat!');
\$stmt = \$pdo->prepare('SELECT short_url, DATALENGTH(base64) as len FROM links WHERE short_url = :short_url');
\$stmt->execute([':short_url' => 'nusantara77']);
\$link = \$stmt->fetch(PDO::FETCH_OBJ);
echo 'Base64 length: ' . \$link->len . ' bytes' . PHP_EOL;
// Expected: ~20000-30000 bytes untuk QR 500x500 dengan logo
"

# Verify 3 position detection patterns
docker exec -it myits-url-shortener-web php -r "
\$pdo = new PDO('sqlsrv:Server=oltp.its.ac.id,1433;Database=SHORTENER', 'shortener_app', 's1ngk4tj3lasp4dat!');
\$stmt = \$pdo->prepare('SELECT base64 FROM links WHERE short_url = :short_url');
\$stmt->execute([':short_url' => 'nusantara77']);
\$base64 = \$stmt->fetchColumn();
\$pngData = base64_decode(\$base64);
\$img = imagecreatefromstring(\$pngData);

// Check 3 corners (harus hitam)
\$corners = [[20,20], [480,20], [20,480]];
\$valid = true;
foreach (['TL','TR','BL'] as \$i => \$label) {
    \$rgb = imagecolorat(\$img, \$corners[\$i][0], \$corners[\$i][1]);
    \$r = (\$rgb >> 16) & 0xFF;
    if (\$r > 50) \$valid = false;
    echo \"\$label: \" . (\$r < 50 ? 'BLACK ✓' : 'NOT BLACK ✗') . PHP_EOL;
}
echo 'QR Valid: ' . (\$valid ? 'YES' : 'NO') . PHP_EOL;
imagedestroy(\$img);
"
```

## Expected Results

Untuk QR code 500x500 dengan logo:
- Base64 length: ~20.000-30.000 bytes
- Decoded PNG: ~7.000-15.000 bytes
- Pixel distribution:
  - Black pixels (QR modules): ~200.000+
  - White pixels (background): ~10.000+
  - Color pixels (logo): ~2.000-5.000 (tergantung logo)
- 3 position detection patterns (kotak di pojok TL, TR, BL) harus hitam

## Files Modified

1. **`src/bulk-generate-qrcode`** - Complete rewrite:
   - SQL Server syntax fix (backtick → square bracket)
   - Ganti SimpleSoftwareIO\QrCode → BaconQrCode ImageRenderer
   - SVG rect parsing untuk QR modules
   - Logo merging dengan alpha blending
   - Size 500x500 pixels

2. **`src/generate-qr.php`** - Alpha blending fix untuk logo

3. **`src/app/Console/Commands/GenerateQRCodes.php`** - Alpha blending fix untuk logo

## Contoh QR Code Valid

QR code yang valid harus memiliki:
- 3 position detection patterns (kotak besar) di pojok: Top-Left, Top-Right, Bottom-Left
- Logo di tengah dengan background putih
- Pattern QR hitam-putih yang jelas
- Size 500x500 pixels
