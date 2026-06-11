<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\GenerateQRCode;

class GenerateController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil 1 baris dari tabel links
        $link = DB::table('links')->where('base64',null)->orderBy('id','DESC')->limit(1)->first();
        (new GenerateQRCode($link->short_url))->handle();
        return "ok";
    }

    public function show($short_url)
    {
        // Mengambil 1 baris dari tabel links
        $link = DB::table('links')->where('short_url',$short_url)->first();
        return $link->base64;
        return ' <img src="data:image/png;base64,' . $link->base64 . '" alt="QR Code">';
    }

    public function generate(Request $request)
    {
        // Menjalankan Job GenerateQRCode untuk short_link tertentu via URL
        // Contoh: /generate-qr?short_link=abc123
        $short_link = $request->query('short_link');
        if (empty($short_link)) {
            return response()->json(['status' => 'error', 'message' => 'Parameter short_link wajib diisi'], 400);
        }

        // Pastikan short_link ada di tabel links
        $link = DB::table('links')->where('short_url', $short_link)->first();

        if (!$link) {
            return response()->json(['status' => 'error', 'message' => 'short_link tidak ditemukan'], 404);
        }

        // Jalankan Job secara sinkron
        (new GenerateQRCode($short_link))->handle();

        return response()->json([
            'status'     => 'ok',
            'message'    => 'QR code berhasil dibuat',
            'short_link' => $short_link,
        ]);
    }


}

