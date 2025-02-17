<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\CryptoHelper;

class IndexController extends Controller
{
    /**
     * Show the index page and perform authentication checking
     *
     * @return Response
     */
    public function showIndexPage(Request $request)
    {

        // return view('dinonaktifkan');

        // Authenticate here
        // redirect to MyITS SSO if not logged in
        if (self::isLoggedIn()) {
            return view('index', ['large' => true]);
        } else {
            return redirect()->route('login', $request->all());
        }

        return view('index', ['large' => true]);
    }
}
