<?php
namespace App\Core;

class Router {
    private $routes = [];
    private $currentGroupMiddlewares = [];

    public function group($middlewares, $callback) {
        $previousGroupMiddlewares = $this->currentGroupMiddlewares;
        $this->currentGroupMiddlewares = array_merge($this->currentGroupMiddlewares, (array)$middlewares);
        $callback($this);
        $this->currentGroupMiddlewares = $previousGroupMiddlewares;
    }

    public function add($method, $uri, $action) {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $uri);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'uri' => $uri,
            'pattern' => $pattern,
            'action' => $action,
            'middlewares' => $this->currentGroupMiddlewares
        ];
    }

    public function get($uri, $action) {
        $this->add('GET', $uri, $action);
    }

    public function post($uri, $action) {
        $this->add('POST', $uri, $action);
    }

    public function dispatch($requestUri, $requestMethod) {
        $path = parse_url($requestUri, PHP_URL_PATH);

        $config = require __DIR__ . '/../Config/config.php';
        $appUrl = $config['app']['url'];
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?: '';
        if ($basePath && $basePath !== '/' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = '/' . trim($path, '/');

        $requestMethod = strtoupper($requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, function($key) {
                    return !is_numeric($key);
                }, ARRAY_FILTER_USE_KEY);

                foreach ($route['middlewares'] as $middlewareClass) {
                    if (class_exists($middlewareClass)) {
                        $middleware = new $middlewareClass();
                        $middleware->handle();
                    }
                }

                $actionParts = explode('@', $route['action']);
                $controllerClass = $actionParts[0];
                $method = $actionParts[1] ?? 'index';

                if (class_exists($controllerClass)) {
                    $controller = new $controllerClass();
                    if (method_exists($controller, $method)) {
                        call_user_func_array([$controller, $method], $params);
                        return;
                    }
                }
                $this->sendNotFound("Méthode {$method} introuvable dans le contrôleur {$controllerClass}.");
                return;
            }
        }

        $this->sendNotFound("La route {$path} [{$requestMethod}] n'existe pas.");
    }

    private function sendNotFound($debugMsg = '') {
        http_response_code(404);
        $config = require __DIR__ . '/../Config/config.php';
        if ($config['app']['env'] === 'development') {
            echo "<h1>404 Not Found</h1><p>" . htmlspecialchars($debugMsg, ENT_QUOTES, 'UTF-8') . "</p>";
        } else {
            if (file_exists(__DIR__ . '/../Views/errors/404.php')) {
                require __DIR__ . '/../Views/errors/404.php';
            } else {
                echo "<h1>404 - Page non trouvée</h1>";
            }
        }
        exit;
    }
}
