<?php
namespace App\Core;

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../Config/config.php';
            $isSecure = strpos($config['app']['url'], 'https://') === 0;

            session_start([
                'cookie_lifetime' => 86400,
                'cookie_path' => '/',
                'cookie_secure' => $isSecure,
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
                'use_strict_mode' => true,
            ]);
        }
    }

    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get($key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function regenerate() {
        self::start();
        session_regenerate_id(true);
    }
}
