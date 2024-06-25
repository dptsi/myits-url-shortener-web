<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Jobs\GenerateQRCode;

class GenerateController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil 1 baris dari tabel links
        $links = DB::table('links')->where('base64',null)->limit(1)->get();
        
        foreach ($links as $item) {
            // URL yang akan dienkode dalam QR code
            (new GenerateQRCode($item))->handle();
            // Membuat QR code
            // $qrCode = QrCode::format('png')
            //     ->size(100)
            //     ->margin(0)
            //     ->errorCorrection('H')
            //     ->merge('https://i.imgur.com/BHhJgMH.png', .3, true)
            //     ->generate($url);

            // // Mengonversi QR code ke Base64
            // $qrCodeBase64 = base64_encode($qrCode);

            // // Memperbarui database dengan nilai base64 dari QR code
            // DB::table('links')->where('short_url', $item->short_url)->update(['base64' => $qrCodeBase64]);
        }
        return "ok";
    }
}

