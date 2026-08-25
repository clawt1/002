<?php
namespace App\Helpers;

use App\Core\Session;

class Flash {
    public static function set($type, $message) {
        Session::start();
        $flashes = Session::get('flash_messages', []);
        $flashes[$type][] = $message;
        Session::set('flash_messages', $flashes);
    }

    public static function success($message) {
        self::set('success', $message);
    }

    public static function error($message) {
        self::set('error', $message);
    }

    public static function get($type) {
        Session::start();
        $flashes = Session::get('flash_messages', []);
        $messages = $flashes[$type] ?? [];
        unset($flashes[$type]);
        Session::set('flash_messages', $flashes);
        return $messages;
    }

    public static function all() {
        Session::start();
        $flashes = Session::get('flash_messages', []);
        Session::set('flash_messages', []);
        return $flashes;
    }
}
