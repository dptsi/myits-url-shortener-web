<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\LinkHelper;
use App\Helpers\CryptoHelper;
use App\Helpers\UserHelper;
use App\Models\User;
use App\Factories\UserFactory;
use DB;

class AjaxController extends Controller
{
    /**
     * Process AJAX requests.
     *
     * @return Response
     */
    public function checkLinkAvailability(Request $request)
    {
        $link_ending = $request->input('link_ending');
        $ending_conforms = LinkHelper::validateEnding($link_ending);

        if (session('role_group') == UserHelper::$ROLE_GROUP['mahasiswa']) {
            $link_ending = 'm/' . $link_ending;
        }

        if (!$ending_conforms) {
            return "invalid";
        } else if (LinkHelper::linkExists($link_ending)) {
            // if ending already exists
            return "unavailable";
        } else {
            return "available";
        }
    }

    public function toggleAPIActive(Request $request)
    {
        self::ensureAdmin();

        $user_id = $request->input('user_id');
        $user = UserHelper::getUserById($user_id);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }
        $current_status = $user->api_active;

        if ($current_status == 1) {
            $new_status = 0;
        } else {
            $new_status = 1;
        }

        $user->api_active = $new_status;
        $user->save();

        return $user->api_active;
    }

    public function generateNewAPIKey(Request $request)
    {
        /**
         * If user is an admin, allow resetting of any API key
         *
         * If user is not an admin, allow resetting of own key only, and only if
         * API is enabled for the account.
         * @return string; new API key
         */


        $user_id = $request->input('user_id');
        $user = UserHelper::getUserById($user_id);

        $username_user_requesting = session('username');
        $user_requesting = UserHelper::getUserByUsername($username_user_requesting);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }

        if ($user != $user_requesting) {
            // if user is attempting to reset another user's API key,
            // ensure they are an admin
            self::ensureAdmin();
        } else {
            // user is attempting to reset own key
            // ensure that user is permitted to access the API
            $user_api_enabled = $user->api_active;
            if (!$user_api_enabled) {
                // if the user does not have API access toggled on,
                // allow only if user is an admin
                self::ensureAdmin();
            }
        }

        $new_api_key = CryptoHelper::generateRandomHex(env('_API_KEY_LENGTH'));
        $user->api_key = $new_api_key;
        $user->save();

        return $user->api_key;
    }

    public function editAPIQuota(Request $request)
    {
        /**
         * If user is an admin, allow the user to edit the per minute API quota of
         * any user.
         */

        self::ensureAdmin();

        $user_id = $request->input('user_id');
        $new_quota = $request->input('new_quota');
        $user = UserHelper::getUserById($user_id);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }
        $user->api_quota = $new_quota;
        $user->save();
        return "OK";
    }

    public function toggleUserActive(Request $request)
    {
        self::ensureAdmin();

        $user_id = $request->input('user_id');
        $user = UserHelper::getUserById($user_id, true);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }
        $current_status = $user->active;

        if ($current_status == 1) {
            $new_status = 0;
        } else {
            $new_status = 1;
        }

        $user->active = $new_status;
        $user->save();

        return $user->active;
    }

    public function changeUserRole(Request $request)
    {
        self::ensureAdmin();

        $user_id = $request->input('user_id');
        $role = $request->input('role');
        $user = UserHelper::getUserById($user_id, true);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }

        $user->role = $role;
        $user->save();

        return "OK";
    }

    public function addNewUser(Request $request)
    {
        self::ensureAdmin();

        $ip = $request->ip();
        $username = $request->input('username');
        $user_password = $request->input('user_password');
        $user_email = $request->input('user_email');
        $user_role = $request->input('user_role');

        UserFactory::createUser($username, $user_email, $user_password, 1, $ip, false, 0, $user_role);

        return "OK";
    }

    public function deleteUser(Request $request)
    {
        self::ensureAdmin();

        $user_id = $request->input('user_id');
        $user = UserHelper::getUserById($user_id, true);

        if (!$user) {
            // abort(404, 'User not found.');
            return view('errors.404');
        }

        $user->delete();
        return "OK";
    }

    public function deleteLink(Request $request)
    {
        $allowedReferer = 'https://shortener.its.ac.id';
        $referer = $request->headers->get('referer');

        if (!$referer || strpos($referer, $allowedReferer) !== 0) {
            return response("Unauthorized request source", 403);
        }

        $user_id  = session('user_id');
        
        // Cek role user
        $check = DB::table('users')->where('id', $user_id)->select('role')->first();
        $isAdmin = ($check->role == 'admin');

        $link_ending = $request->input('link_ending');
        $link = LinkHelper::linkExists($link_ending);
        if (!$isAdmin && $link->user_id != $user_id) {
            return response("You are not authorized to edit this link", 403);
        }
        if (!$link) {
            // abort(404, 'Link not found.');
            return view('errors.404');
        }

        $link->delete();
        return "OK";
    }

    public function toggleLink(Request $request)
    {
        self::ensureAdmin();

        $link_ending = $request->input('link_ending');
        $link = LinkHelper::linkExists($link_ending);

        if (!$link) {
            // abort(404, 'Link not found.');
            return view('errors.404');
        }

        $current_status = $link->is_disabled;

        $new_status = 1;

        if ($current_status == 1) {
            // if currently disabled, then enable
            $new_status = 0;
        }

        $link->is_disabled = $new_status;

        $link->save();

        return ($new_status ? "Enable" : "Disable");
    }

    public function editLinkLongUrl(Request $request)
    {

        $allowedReferer = 'https://shortener.its.ac.id';
        $referer = $request->headers->get('referer');

        if (!$referer || strpos($referer, $allowedReferer) !== 0) {
            return response("Unauthorized request source", 403);
        }

        $user_id  = session('user_id');
        // Cek role user
        $check = DB::table('users')->where('id', $user_id)->select('role')->first();
        $isAdmin = ($check->role == 'admin');

        $link_ending = $request->input('link_ending');
        $link = DB::table('links')->where('short_url', $link_ending)->first();

        if (!$link) {
            return view('errors.404');
        }

        // Jika bukan admin, cek apakah user adalah pemilik link
        if (!$isAdmin && $link->user_id != $user_id) {
            return response("You are not authorized to edit this link", 403);
        }

        // Validasi input URL baru
        // $request->validate([
        //     'new_long_url' => 'required|url',
        // ]);

        // Update URL
        DB::table('links')->where('short_url', $link_ending)->update([
            'long_url' => $request->input('new_long_url')
        ]);

        return response("OK", 200);
    }
}
