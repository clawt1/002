<?php
if (!function_exists('loadEnv')) {
    function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') === false) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match('/^\'(.*)\'$/', $value, $matches)) {
                $value = $matches[1];
            }
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

loadEnv(__DIR__ . '/../../.env');

return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'roadmap_manager',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4'
    ],
    'app' => [
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'env' => getenv('APP_ENV') ?: 'production',
        'name' => 'Roadmap Manager',
        'edition' => getenv('APP_EDITION') ?: 'enterprise'
    ],
    'mail' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.mailtrap.io',
        'port' => getenv('MAIL_PORT') ?: 2525,
        'user' => getenv('MAIL_USER') ?: '',
        'pass' => getenv('MAIL_PASS') ?: '',
        'from_email' => getenv('MAIL_FROM') ?: 'no-reply@roadmap.local',
        'from_name' => 'Roadmap Manager'
    ],
    'ldap' => [
        'enabled' => getenv('LDAP_ENABLED') === 'true',
        'host' => getenv('LDAP_HOST') ?: 'ldap.forumsys.com',
        'port' => getenv('LDAP_PORT') ?: 389,
        'dn' => getenv('LDAP_DN') ?: 'cn=read-only-admin,dc=example,dc=com',
        'pass' => getenv('LDAP_PASS') ?: 'password',
        'base_dn' => getenv('LDAP_BASE_DN') ?: 'dc=example,dc=com'
    ]
];
