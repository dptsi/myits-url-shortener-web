<?php
namespace App\Factories;

use Hash;
use App\Models\User;
use App\Helpers\CryptoHelper;
use App\Helpers\UserHelper;

class UserFactory {
    public static function createUser($username, $email, $password, $active=0, $ip='127.0.0.1', $api_key=false, $api_active=0, $role=false) {
        if (!$role) {
            $role = UserHelper::$USER_ROLES['default'];
        }

        $hashed_password = Hash::make($password);
        $recovery_key = CryptoHelper::generateRandomHex(50);

        $user = new User;
        $user->username = $username;
        $user->password = $hashed_password;
        $user->email = $email;
        $user->recovery_key = $recovery_key;
        $user->active = $active;
        $user->ip = $ip;
        $user->role = $role;
        $user->api_key = $api_key;
        $user->api_active = $api_active;

        $user->save();
        return $user;
    }

    public static function createUserWithSub(
        $sso_id,
        $username,
        $email,
        $role,
        $active=0,
        $api_key=false,
        $api_active=0
    )
    {

        // create new user
        $user = new User;
        $user->username = $username;
        $user->sso_id = $sso_id;
        $user->email = $email;
        $user->active = $active;
        $user->role = $role;
        $user->api_key = $api_key;
        $user->api_active = $api_active;

        $user->save();
        return $user;
    }
}
