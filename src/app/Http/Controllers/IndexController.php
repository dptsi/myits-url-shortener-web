<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Helpers\CryptoHelper;

class IndexController extends Controller {
    /**
     * Show the index page and perform authentication checking
     *
     * @return Response
     */
    public function showIndexPage(Request $request) {

        //Adetiya Bagus Nusantara
        //session_start();
        return view('dinonaktifkan');

        // Authenticate here
        // redirect to MyITS SSO if not logged in
        if ( self::isLoggedIn() ) {
            return view('index', ['large' => true]);
        }
        else {
            return redirect()->route('login', $request->all());
        }
        
        if (env('POLR_SETUP_RAN') != true) {
            return redirect(route('setup'));
        }

        if (!env('SETTING_PUBLIC_INTERFACE') && !self::isLoggedIn()) {
            if (env('SETTING_INDEX_REDIRECT')) {
                return redirect()->to(env('SETTING_INDEX_REDIRECT'));
            }
            else {
                return redirect()->to(route('login'));
            }
        }

        return view('index', ['large' => true]);
    }
}
