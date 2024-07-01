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


}

