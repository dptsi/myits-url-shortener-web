<?php

namespace App\Console\Commands;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Writer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qrcode:generate
                            {--short-url= : Generate QR only for a specific short URL}
                            {--limit= : Max number of QR codes to generate (default: all)}
                            {--force : Regenerate QR codes even if base64 already exists}
                            {--no-progress : Hide the progress bar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bulk generate HD QR codes for all links that are missing them';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $query = DB::table('links');

        // Target specific short URL
        if ($shortUrl = $this->option('short-url')) {
            $query->where('short_url', $shortUrl);
        } else {
            // Default: only links with no QR code yet
            $query->whereNull('base64');

            // Force: regenerate even if already exists
            if ($this->option('force')) {
                $query->whereNotNull('base64');
                $this->info('Mode --force: QR codes akan digenerate ulang walau sudah ada.');
            } else {
                $this->info('Hanya link tanpa base64 QR code yang akan diproses.');
            }
        }

        // Apply limit
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $links = $query->get(['id', 'short_url', 'long_url']);

        if ($links->isEmpty()) {
            $this->warn('Tidak ada link yang perlu digenerate QR code-nya.');
            return;
        }

        $total = count($links);
        $success = 0;
        $failed = 0;

        $bar = $this->option('no-progress') ? null
            : $this->output->createProgressBar($total);

        if ($bar) {
            $bar->setFormat(" %current%/%max% [%bar%] %percent:3s%% -- %message%");
            $bar->setMessage('Memulai...');
            $bar->start();
        }

        $logoPath = __DIR__ . '/../../../public/img/logo.png';

        foreach ($links as $i => $link) {
            $msg = "{$link->short_url}";
            if ($bar) {
                $bar->setMessage($msg);
            } else {
                $this->line("  [$i/$total] $msg");
            }

            try {
                $this->generateQRCode($link->short_url, $logoPath);
                $success++;
            } catch (\Exception $e) {
                $failed++;
                if ($bar) {
                    $bar->clear();
                }
                $this->error("  Gagal: {$link->short_url} — {$e->getMessage()}");
                if ($bar) {
                    $bar->display();
                }
            }

            if ($bar) {
                $bar->advance();
            }
        }

        if ($bar) {
            $bar->finish();
            $this->line('');
        }

        $this->info("Selesai! {$success} berhasil, {$failed} gagal dari {$total} total.");
    }

    /**
     * Generate QR code for a short URL using BaconQrCode (SVG) + GD (PNG conversion)
     */
    private function generateQRCode($shortUrl, $logoPath)
    {
        $url = 'https://its.id/' . $shortUrl;

        // Step 1: Generate QR code as SVG
        $renderer = new ImageRenderer(
            new RendererStyle(500, 0),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svgContent = $writer->writeString($url);

        // Step 2: Parse SVG and render to PNG via GD
        $svgXml = simplexml_load_string($svgContent);
        $svgAttrs = $svgXml->attributes();
        $width = (int) $svgAttrs['width'];
        $height = (int) $svgAttrs['height'];

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $transparent);

        // Draw QR modules from SVG
        foreach ($svgXml->rect as $rect) {
            $attrs = $rect->attributes();
            $fill = (string) $attrs['fill'];
            if ($fill === '#000000') {
                $x = (int) $attrs['x'];
                $y = (int) $attrs['y'];
                $w = (int) $attrs['width'];
                $h = (int) $attrs['height'];
                imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $black);
            }
        }

        // Step 3: Merge logo dengan alpha blending
        $logoData = @file_get_contents($logoPath);
        if ($logoData) {
            $logoImg = @imagecreatefromstring($logoData);
            if ($logoImg) {
                $logoW = imagesx($logoImg);
                $logoH = imagesy($logoImg);
                $logoSize = (int) ($width * 0.25);
                $resizedLogo = imagescale($logoImg, $logoSize, (int)($logoSize * $logoH / $logoW));
                imagesavealpha($resizedLogo, true);
                $resizedLogoW = imagesx($resizedLogo);
                $resizedLogoH = imagesy($resizedLogo);
                $dstX = (int)(($width - $resizedLogoW) / 2);
                $dstY = (int)(($height - $resizedLogoH) / 2);

                // Buat background putih untuk logo (agar QR tidak terlihat tembus di tengah)
                $logoBg = imagecreatetruecolor($resizedLogoW, $resizedLogoH);
                $white = imagecolorallocate($logoBg, 255, 255, 255);
                imagefill($logoBg, 0, 0, $white);
                imagealphablending($logoBg, true);
                imagesavealpha($logoBg, false);

                // Copy resized logo ke background putih
                imagecopy($logoBg, $resizedLogo, 0, 0, 0, 0, $resizedLogoW, $resizedLogoH);

                // Paste logo ke QR dengan alpha blending
                imagealphablending($img, true);
                imagesavealpha($img, true);
                imagecopy($img, $logoBg, $dstX, $dstY, 0, 0, $resizedLogoW, $resizedLogoH);

                imagedestroy($logoBg);
                imagedestroy($logoImg);
                imagedestroy($resizedLogo);
            }
        }

        // Step 4: Output as PNG base64
        ob_start();
        imagepng($img);
        $pngData = ob_get_clean();
        $qrCodeBase64 = base64_encode($pngData);
        imagedestroy($img);

        // Step 5: Save to database
        DB::table('links')
            ->where('short_url', $shortUrl)
            ->update(['base64' => $qrCodeBase64]);
    }
}
