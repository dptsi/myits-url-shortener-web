<?php
namespace App\Helpers;

use App\Factories\UserFactory;
use App\Models\User;
use App\Helpers\CryptoHelper;
use Hash;
use Illuminate\Http\Request;

class UserHelper {
    public static $USER_ROLES = [
        'admin'    => 'admin',
        'default'  => '',
    ];

    public static function userExists($username) {
        /* XXX: used primarily with test cases */

        $user = self::getUserByUsername($username, $inactive=true);
        return ($user ? true : false);
    }

    public static function emailExists($email) {
        /* XXX: used primarily with test cases */

        $user = self::getUserByEmail($email, $inactive=true);
        return ($user ? true : false);
    }

    public static function validateUsername($username) {
        return ctype_alnum($username);
    }

    public static function userIsAdmin($username) {
        return (self::getUserByUsername($username)->role == self::$USER_ROLES['admin']);
    }

    public static function registerUser(Request $request) {
        // register the user to the database
        // for the first time logged in
        $username = session('username');
        $user_id  = session('user_id');
        $email    = session('email');
        // $email    = 'dummy@mail.com';
        $ip = $request->ip();

        $user = UserFactory::createUserWithSub(
            $username,
            $user_id,
            $email,
            1,
            $ip
        );
    }

    public static function loginUser($sub, $username, $email){
        $user = User::where('user_id', $sub)
                    ->where('username', $username)
                    ->update(['email' => $email]);
    }

    public static function isUserExist($sub, $username) {
        $user = User::where('user_id', $sub)->where('username', $username)->first();

        if (!$user) {
            return false;
        }
        else {
            return true;
        }
    }

    public static function checkCredentials($username, $password) {
        $user = User::where('active', 1)
            ->where('username', $username)
            ->first();

        if ($user == null) {
            return false;
        }

        $correct_password = Hash::check($password, $user->password);

        if (!$correct_password) {
            return false;
        }
        else {
            return ['username' => $username, 'role' => $user->role];
        }
    }

    public static function resetRecoveryKey($username) {
        $recovery_key = CryptoHelper::generateRandomHex(50);
        $user = self::getUserByUsername($username);

        if (!$user) {
            return false;
        }

        $user->recovery_key = $recovery_key;
        $user->save();

        return $recovery_key;
    }

    public static function userResetKeyCorrect($username, $recovery_key, $inactive=false) {
        // Given a username and a recovery key, return true if they match.
        $user = self::getUserByUsername($username, $inactive);

        if ($user) {
            if ($recovery_key != $user->recovery_key) {
                return false;
            }
        }
        else {
            return false;
        }
        return true;
    }

    public static function getUserBy($attr, $value, $inactive=false) {
        $user = User::where($attr, $value);

        if (!$inactive) {
            // if user must be active
            $user = $user
                ->where('active', 1);
        }

        return $user->first();
    }

    public static function getUserById($user_id, $inactive=false) {
        return self::getUserBy('id', $user_id, $inactive);
    }

    public static function getUserByUsername($username, $inactive=false) {
        return self::getUserBy('username', $username, $inactive);
    }

    public static function getUserByEmail($email, $inactive=false) {
        return self::getUserBy('email', $email, $inactive);
    }
}
