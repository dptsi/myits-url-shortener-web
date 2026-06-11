# QR Code Generation - Solution (FINAL)

## Masalah
QR code yang ter-generate hanya menampilkan logo saja, QR pattern tidak muncul.

## Solusi yang Berhasil

### Install Imagick dan rsvg-convert di Container
```bash
docker exec myits-url-shortener-web sh -c "
apk add --no-cache imagemagick imagemagick-dev rsvg && \
pecl install imagick && \
docker-php-ext-enable imagick
"
```

### Update bulk-generate-qrcode
Script menggunakan BaconQrCode untuk generate SVG, lalu convert ke PNG dengan `rsvg-convert`, dan merge logo dengan Imagick.

**Flow:**
1. Generate QR code sebagai SVG menggunakan BaconQrCode
2. Convert SVG ke PNG menggunakan `rsvg-convert`
3. Merge logo di tengah menggunakan Imagick dengan background putih
4. Encode ke base64 dan simpan ke database

## Cara Generate QR Code

### Single QR Code
```bash
docker exec -it myits-url-shortener-web php -d "newrelic.enabled=false" generate-qr.php nusantara77
```

### Bulk Generate (semua link tanpa QR)
```bash
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode
```

### Generate Ulang (force)
```bash
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --short-url=nusantara77 --force
```

## Expected Result
- QR Code size: 500x500 pixels
- Black pixels: ~48-50% (120,000-125,000)
- White pixels: ~47-49% (115,000-120,000)
- Logo pixels: ~3-5% (7,500-12,500)
- Format: PNG base64 encoded
- Base64 length: ~25-35 KB

## Files Modified
1. `src/bulk-generate-qrcode` - Updated dengan rsvg-convert + Imagick workflow
2. `src/generate-qr.php` - Alpha blending fix untuk logo merge
3. `src/app/Console/Commands/GenerateQRCodes.php` - Alpha blending fix

## Technical Details

### Why rsvg-convert?
- BaconQrCode SvgImageBackEnd menghasilkan SVG dengan path complexes
- GD tidak bisa parse SVG path dengan benar
- Imagick butuh delegate (rsvg-convert) untuk render SVG
- rsvg-convert adalah tool yang reliable untuk SVG -> PNG conversion

### Why Imagick for logo merge?
- Imagick support alpha blending dan compositing yang lebih baik
- GD imagecopy() tidak handle transparency dengan benar
- Imagick bisa resize dan composite dengan quality tinggi

## Verification
```bash
docker exec myits-url-shortener-web php -r "
\$pdo = new PDO('sqlsrv:Server=oltp.its.ac.id,1433;Database=SHORTENER', 'shortener_app', 'password');
\$stmt = \$pdo->prepare('SELECT base64 FROM links WHERE short_url = :short_url');
\$stmt->execute([':short_url' => 'nusantara77']);
\$base64 = \$stmt->fetchColumn();
\$pngData = base64_decode(\$base64);
\$img = imagecreatefromstring(\$pngData);

echo 'Size: ' . imagesx(\$img) . 'x' . imagesy(\$img) . PHP_EOL;

\$black = 0; \$white = 0; \$other = 0;
for (\$y = 0; \$y < 500; \$y++) {
    for (\$x = 0; \$x < 500; \$x++) {
        \$rgb = imagecolorat(\$img, \$x, \$y);
        \$r = (\$rgb >> 16) & 0xFF;
        if (\$r < 50) \$black++; elseif (\$r > 200) \$white++; else \$other++;
    }
}
echo 'Black: ' . \$black . ' (' . round(\$black/250000*100,1) . '%)' . PHP_EOL;
echo 'White: ' . \$white . ' (' . round(\$white/250000*100,1) . '%)' . PHP_EOL;
echo 'Other: ' . \$other . ' (' . round(\$other/250000*100,1) . '%)' . PHP_EOL;
echo 'Valid: ' . (\$black > 50000 && \$black < 200000 ? 'YES' : 'NO') . PHP_EOL;
imagedestroy(\$img);
"
```
