# QR Code Generation - Solution

## Masalah
QR code yang ter-generate hanya menampilkan logo saja, QR pattern tidak muncul.

## Root Cause
1. BaconQrCode SvgImageBackEnd menghasilkan SVG dengan `<path>` elements yang kompleks
2. SVG path parsing dengan GD tidak bekerja dengan benar - banyak modules yang tidak ter-render
3. Imagick extension tidak tersedia di container untuk PNG rendering langsung

## Solusi yang Bekerja

### Opsi 1: Install Imagick di Container (RECOMMENDED)
Tambahkan Imagick extension ke Docker container:

```dockerfile
RUN docker-php-ext-install imagick
```

Kemudian gunakan code ini:
```php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(500, 0),
    new ImagickImageBackEnd()
);
$writer = new Writer($renderer);
$qrCode = $writer->writeString($url);
$qrCodeBase64 = base64_encode($qrCode);
```

### Opsi 2: Gunakan generate-qr.php yang Ada
Script `generate-qr.php` sudah bekerja dengan SVG parsing manual. Gunakan dari dalam container:

```bash
docker exec -it myits-url-shortener-web php -d "newrelic.enabled=false" generate-qr.php nusantara77
```

### Opsi 3: Update bulk-generate-qrcode
Script `bulk-generate-qrcode` sudah di-update untuk menggunakan SVG parsing, tapi hasilnya tidak optimal (94% black pixels).

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
docker exec -it myits-url-shortener-web php /var/www/html/bulk-generate-qrcode --force
```

## Expected Result
- QR Code size: 500x500 pixels
- Format: PNG base64 encoded
- Base64 length: ~20-30 KB
- 3 position detection patterns di pojok (TL, TR, BL)

## Files Modified
1. `src/bulk-generate-qrcode` - Updated dengan SVG parsing logic
2. `src/generate-qr.php` - Alpha blending fix untuk logo merge
3. `src/app/Console/Commands/GenerateQRCodes.php` - Alpha blending fix

## Catatan Penting
- SVG parsing dengan GD memiliki limitasi - tidak semua QR modules ter-render dengan sempurna
- Untuk production, disarankan install Imagick extension
- Logo merge menggunakan alpha blending untuk menghindari QR pattern tertutup
