<?php
namespace App\Core;

class Csrf {
    public static function token() {
        Session::start();
        if (!Session::has('csrf_token')) {
            $token = bin2hex(random_bytes(32));
            Session::set('csrf_token', $token);
        }
        return Session::get('csrf_token');
    }

    public static function verify($token) {
        if (!$token) {
            return false;
        }
        Session::start();
        $stored = Session::get('csrf_token');
        if (!$stored || !hash_equals($stored, $token)) {
            return false;
        }
        return true;
    }

    public static function field() {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}
