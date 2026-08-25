<?php
namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Session;

class AuthMiddleware {
    public function handle() {
        if (!Auth::check()) {
            Session::set('redirect_after_login', $_SERVER['REQUEST_URI']);

            $config = require __DIR__ . '/../Config/config.php';
            $appUrl = rtrim($config['app']['url'], '/');
            header("Location: " . $appUrl . "/login");
            exit;
        }
    }
}
