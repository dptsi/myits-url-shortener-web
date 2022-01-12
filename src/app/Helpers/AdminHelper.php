<?php
namespace App\Helpers;

use DateTime;

class AdminHelper {
    public static function pass() {
        
    }

    public static function isDateExpired($dateExpired) {
        $now = new DateTime('now');
        $expire = new DateTime($dateExpired);

        return $now >= $expire;
    }
}
