<?php
namespace App\Middlewares;

use App\Core\Auth;

class RoleMiddleware {
    public function handle() {
        if (!Auth::check()) {
            $config = require __DIR__ . '/../Config/config.php';
            header("Location: " . rtrim($config['app']['url'], '/') . "/login");
            exit;
        }

        $user = Auth::user();
        $role = $user['role_nom'] ?? '';

        if (($_SERVER['REQUEST_METHOD'] === 'POST') && ($role === 'Client/Observateur' || $role === 'Direction')) {
            http_response_code(403);
            if (file_exists(__DIR__ . '/../Views/errors/403.php')) {
                require __DIR__ . '/../Views/errors/403.php';
            } else {
                echo "<h1>403 - Accès Interdit</h1><p>Votre rôle ne vous permet pas d'effectuer cette action.</p>";
            }
            exit;
        }
    }
}
