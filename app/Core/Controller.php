<?php
namespace App\Core;

class Controller {
    protected function view($name, $data = []) {
        extract($data);

        $viewFile = __DIR__ . '/../Views/' . $name . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            die("La vue {$name} n'existe pas.");
        }
    }

    protected function redirect($url) {
        $config = require __DIR__ . '/../Config/config.php';
        $appUrl = rtrim($config['app']['url'], '/');
        $url = '/' . ltrim($url, '/');
        header("Location: " . $appUrl . $url);
        exit;
    }

    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
