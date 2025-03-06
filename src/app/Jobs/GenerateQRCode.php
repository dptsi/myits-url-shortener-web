<?php

// app/Jobs/GenerateQRCode.php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQRCode
{
    protected $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    public function handle()
    {
        // URL yang akan diencode dalam QR code
        $url = 'https://its.id/' . $this->item;

        // Membuat QR code
        $qrCode = QrCode::format('png')
            ->size(500)
            ->margin(0)
            ->errorCorrection('H')
            ->merge('https://i.imgur.com/BHhJgMH.png', .3, true)
            ->generate($url);

        // Mengonversi QR code ke Base64
        $qrCodeBase64 = base64_encode($qrCode);

        // Memperbarui database dengan nilai base64 dari QR code
        DB::table('links')->where('short_url', $this->item)->update(['base64' => $qrCodeBase64]);
    }
}
