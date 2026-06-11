---
name: qrcode-generation-fix
description: Fix QR code generation that produces empty/white images or logo-only output using rsvg-convert + Imagick or SVG path parsing with GD
source: auto-skill
extracted_at: '2026-06-09T11:38:00.000Z'
---

# QR Code Generation Fix - Production Solutions

## Problem

When generating QR codes in PHP using SimpleSoftwareIO/QrCode or BaconQrCode with SVG-to-PNG conversion, the resulting image may be:
1. **Completely black/white/empty** - No QR pattern visible
2. **Logo only** - Only the center logo appears, no QR modules
3. **Base64 decodes to empty/invalid image**
4. **Only 1-2 corners visible** - QR pattern partially rendered

## RECOMMENDED SOLUTION: rsvg-convert + Imagick (PRODUCTION-READY)

This is the most reliable approach that works consistently:

```php
<?php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

// Generate QR code as SVG
$renderer = new ImageRenderer(
    new RendererStyle(500, 0),
    new SvgImageBackEnd()
);
$writer = new Writer($renderer);
$svgContent = $writer->writeString($url);

// Save SVG to temporary file
$tmpSvg = sys_get_temp_dir() . '/qr_' . uniqid() . '.svg';
$tmpPng = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
file_put_contents($tmpSvg, $svgContent);

// Convert SVG to PNG using rsvg-convert (RELIABLE!)
exec("rsvg-convert -w 500 -h 500 {$tmpSvg} -o {$tmpPng} 2>&1", $output, $ret);
@unlink($tmpSvg);

if ($ret !== 0) {
    throw new Exception('rsvg-convert failed: ' . implode("\n", $output));
}

$pngContent = file_get_contents($tmpPng);
@unlink($tmpPng);

// Merge logo using Imagick (better alpha handling than GD)
$logoPath = '/path/to/logo.png';
if (file_exists($logoPath) && $pngContent) {
    $qr = new Imagick();
    $qr->readImageBlob($pngContent);
    
    $logo = new Imagick();
    $logo->readImage($logoPath);
    
    // Resize logo to 25% of QR size
    $logoSize = (int) (500 * 0.25);
    $logo->thumbnailImage($logoSize, $logoSize);
    
    // Center position
    $x = (500 - $logo->getImageWidth()) / 2;
    $y = (500 - $logo->getImageHeight()) / 2;
    
    // Create white background for logo
    $logoBg = new Imagick();
    $logoBg->newImage($logo->getImageWidth(), $logo->getImageHeight(), 'white');
    $logoBg->compositeImage($logo, Imagick::COMPOSITE_DEFAULT, 0, 0);
    
    // Composite logo with white background onto QR code
    $qr->compositeImage($logoBg, Imagick::COMPOSITE_DEFAULT, $x, $y);
    
    $pngContent = $qr->getImageBlob();
    
    $logo->destroy();
    $logoBg->destroy();
    $qr->destroy();
}

// Encode to base64
$qrCodeBase64 = base64_encode($pngContent);
```

### Prerequisites

Install required packages in Docker container:

```bash
# For Alpine-based images
docker exec myits-url-shortener-web sh -c "
apk add --no-cache imagemagick imagemagick-dev rsvg && \
pecl install imagick && \
docker-php-ext-enable imagick
"

# For Debian/Ubuntu-based images
docker exec myits-url-shortener-web sh -c "
apt-get update && apt-get install -y imagemagick librsvg2-bin php-imagick && \
docker-php-ext-enable imagick
"
```

### Why This Works

1. **rsvg-convert**: Native SVG renderer that correctly handles all SVG features including paths, transforms, and nested elements
2. **Imagick**: Superior image compositing with proper alpha blending, better than GD
3. **No manual parsing**: Avoids error-prone SVG path parsing with regex

---

## ALTERNATIVE: SVG Path Parsing with GD (if rsvg-convert unavailable)

```php
<?php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;

// Generate QR code as SVG
$renderer = new ImageRenderer(
    new RendererStyle(500, 0),
    new SvgImageBackEnd()
);
$writer = new Writer($renderer);
$svgContent = $writer->writeString($url);

// Parse SVG
$svgXml = simplexml_load_string($svgContent);
$svgAttrs = $svgXml->attributes();
$width = (int) $svgAttrs['width'];
$height = (int) $svgAttrs['height'];

// Create base image
$img = imagecreatetruecolor($width, $height);
imagesavealpha($img, true);
$white = imagecolorallocate($img, 255, 255, 255);
imagefill($img, 0, 0, $white);
$black = imagecolorallocate($img, 0, 0, 0);

// Calculate scale factor from SVG transform
// SVG uses transform="scale(N)" where N = width / qr_module_size
// For 500px SVG with ~25x25 modules: scale = 500/25 = 20
$scaleFactor = (int) ($width / 25);

// Render rect elements (background)
foreach ($svgXml->rect as $rect) {
    $attrs = $rect->attributes();
    $fill = (string) $attrs['fill'];
    if ($fill !== '#ffffff' && $fill !== '#fefefe') {
        $x = (int) $attrs['x'];
        $y = (int) $attrs['y'];
        $w = (int) $attrs['width'];
        $h = (int) $attrs['height'];
        imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $black);
    }
}

// Render path elements (QR modules) - TRAVERSE NESTED <g> ELEMENTS
// BaconQrCode structure: <svg><g><g><path>
foreach ($svgXml->children() as $child) {
    if ($child->getName() === 'g') {
        foreach ($child->children() as $g2) {
            if ($g2->getName() === 'g') {
                foreach ($g2->children() as $path) {
                    if ($path->getName() === 'path') {
                        $attrs = $path->attributes();
                        $fill = (string) $attrs['fill'];
                        if ($fill === '#000000') {
                            $d = (string) $attrs['d'];
                            
                            // Parse path commands: M=move, L=line, H=horizontal, V=vertical, Z=close
                            preg_match_all('/([MLHVZ])([^MLHVZ]*)/i', $d, $matches, PREG_SET_ORDER);
                            
                            $points = [];
                            $startX = 0; $startY = 0;
                            $currentX = 0; $currentY = 0;
                            
                            foreach ($matches as $match) {
                                $cmd = strtoupper($match[1]);
                                $params = trim($match[2]);
                                $nums = [];
                                preg_match_all('/-?\d+\.?\d*/', $params, $nums);
                                $nums = array_map('floatval', $nums[0] ?? []);
                                
                                switch ($cmd) {
                                    case 'M': // Move to - start new sub-path
                                        $currentX = $nums[0];
                                        $currentY = $nums[1];
                                        $startX = $currentX;
                                        $startY = $currentY;
                                        $points = [[$currentX, $currentY]];
                                        break;
                                    case 'L': // Line to
                                        $currentX = $nums[0];
                                        $currentY = $nums[1];
                                        $points[] = [$currentX, $currentY];
                                        break;
                                    case 'H': // Horizontal line
                                        $currentX = $nums[0];
                                        $points[] = [$currentX, $currentY];
                                        break;
                                    case 'V': // Vertical line
                                        $currentY = $nums[0];
                                        $points[] = [$currentX, $currentY];
                                        break;
                                    case 'Z': // Close path - draw module
                                        $points[] = [$startX, $startY];
                                        if (count($points) >= 2) {
                                            $xs = array_column($points, 0);
                                            $ys = array_column($points, 1);
                                            $minX = (int) round(min($xs));
                                            $minY = (int) round(min($ys));
                                            $maxX = (int) round(max($xs));
                                            $maxY = (int) round(max($ys));
                                            // APPLY SCALE FACTOR - THIS IS CRITICAL!
                                            imagefilledrectangle($img, 
                                                $minX * $scaleFactor, $minY * $scaleFactor, 
                                                ($maxX - 1) * $scaleFactor, ($maxY - 1) * $scaleFactor, 
                                                $black);
                                        }
                                        $points = [];
                                        break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

// Merge logo with alpha blending
$logoPath = '/path/to/logo.png';
if (file_exists($logoPath)) {
    $logoData = @file_get_contents($logoPath);
    if ($logoData) {
        $logoImg = @imagecreatefromstring($logoData);
        if ($logoImg) {
            $logoSize = (int) ($width * 0.25); // 25% of QR size
            $resizedLogo = imagescale($logoImg, $logoSize, $logoSize);
            $resizedLogoW = imagesx($resizedLogo);
            $resizedLogoH = imagesy($resizedLogo);
            $dstX = (int)(($width - $resizedLogoW) / 2);
            $dstY = (int)(($height - $resizedLogoH) / 2);

            // Create white background for logo
            $logoBg = imagecreatetruecolor($resizedLogoW, $resizedLogoH);
            $white = imagecolorallocate($logoBg, 255, 255, 255);
            imagefill($logoBg, 0, 0, $white);
            imagealphablending($logoBg, true);
            imagesavealpha($logoBg, false);

            // Copy logo to background
            imagecopy($logoBg, $resizedLogo, 0, 0, 0, 0, $resizedLogoW, $resizedLogoH);

            // Paste to QR with alpha blending ENABLED
            imagealphablending($img, true);
            imagesavealpha($img, true);
            imagecopy($img, $logoBg, $dstX, $dstY, 0, 0, $resizedLogoW, $resizedLogoH);

            imagedestroy($logoBg);
            imagedestroy($logoImg);
            imagedestroy($resizedLogo);
        }
    }
}

// Output as PNG base64
ob_start();
imagepng($img);
$pngData = ob_get_clean();
imagedestroy($img);
$base64 = base64_encode($pngData);
```

## Verification

After generating, verify the QR code is valid:

```php
// Check base64 decodes to valid PNG
$pngData = base64_decode($base64);
$img = imagecreatefromstring($pngData);

if ($img) {
    echo "Dimensions: " . imagesx($img) . "x" . imagesy($img) . PHP_EOL;
    
    // Count black pixels (QR modules should exist)
    $blackCount = 0;
    for ($y = 0; $y < 500; $y++) {
        for ($x = 0; $x < 500; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            if ($r < 50) $blackCount++;
        }
    }
    echo "Black pixels: $blackCount" . PHP_EOL;
    // Expected: 1000-2000 for valid QR code
    imagedestroy($img);
} else {
    echo "INVALID PNG" . PHP_EOL;
}
```

## Expected Results

For a 500x500 QR code with logo:
- Base64 length: ~20000-30000 bytes (with logo)
- PNG size: ~15-20 KB
- Black pixels (QR modules): 50000-150000 (~20-60% of image)
- White pixels (background): 80000-180000
- Color pixels (logo): 2000-8000
- Valid PNG header: `89504e470d0a1a0a`
- 3 position detection patterns visible at corners (top-left, top-right, bottom-left)

## Important Limitations & Recommendations

### GD + SVG Parsing Limitations
SVG path parsing with PHP GD has significant limitations:
- Complex path data may not parse correctly
- Scale factor must be calculated and applied manually
- Nested `<g>` elements must be traversed explicitly
- Path commands with adjacent characters (e.g., `ZM`) require special regex handling

### Recommended: Use Imagick Extension
For production use, install the Imagick extension and use `ImagickImageBackEnd`:

```php
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Writer;

$renderer = new ImageRenderer(
    new RendererStyle(500, 0),
    new ImagickImageBackEnd()  // Direct PNG output, no SVG parsing needed
);
$writer = new Writer($renderer);
$qrCode = $writer->writeString($url);
$qrCodeBase64 = base64_encode($qrCode);
```

**Benefits:**
- No SVG parsing required
- Direct PNG output
- More reliable and accurate QR rendering
- Better performance

### Install Imagick in Docker
```dockerfile
RUN docker-php-ext-install imagick
```

Or for Alpine-based images:
```dockerfile
RUN apk add --no-cache imagemagick php-imagick
```

## SQL Server Specific Fix

If using SQL Server, change table name syntax:

```php
// WRONG (MySQL style)
$sql = "SELECT id FROM `{$tableName}` WHERE ...";

// CORRECT (SQL Server)
$sql = "SELECT id FROM [{$tableName}] WHERE ...";
```

## Files to Check/Modify

1. `src/bulk-generate-qrcode` - Standalone script with full fix
2. `src/generate-qr.php` - Single QR generator
3. `src/app/Console/Commands/GenerateQRCodes.php` - Artisan command

## Usage Example

```bash
# Generate QR for specific short URL
docker exec -it myits-url-shortener-web \
  php /var/www/html/bulk-generate-qrcode --short-url=nusantara77

# Generate all missing QR codes
docker exec -it myits-url-shortener-web \
  php /var/www/html/bulk-generate-qrcode

# Force regenerate all
docker exec -it myits-url-shortener-web \
  php /var/www/html/bulk-generate-qrcode --force
```
