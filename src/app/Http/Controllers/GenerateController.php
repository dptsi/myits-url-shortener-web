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
        (new GenerateQRCode($link))->handle();
        return "ok";
    }
}

