<?php
namespace App\Core;

class Auth {
    public static function checkRateLimit($email) {
        Session::start();
        $attempts = Session::get('login_attempts_' . md5($email), []);
        $now = time();
        $attempts = array_filter($attempts, function($time) use ($now) {
            return ($now - $time) < 900;
        });
        Session::set('login_attempts_' . md5($email), $attempts);
        return count($attempts) < 5;
    }

    public static function registerAttempt($email) {
        Session::start();
        $attempts = Session::get('login_attempts_' . md5($email), []);
        $attempts[] = time();
        Session::set('login_attempts_' . md5($email), $attempts);
    }

    public static function clearAttempts($email) {
        Session::remove('login_attempts_' . md5($email));
    }

    public static function attempt($email, $password) {
        if (!self::checkRateLimit($email)) {
            return 'rate_limit';
        }

        $config = require __DIR__ . '/../Config/config.php';
        $ldapSuccess = false;

        if ($config['ldap']['enabled'] && function_exists('ldap_connect')) {
            $ldapSuccess = self::attemptLdap($email, $password, $config['ldap']);
        }

        $db = Database::getInstance();
        $stmt = $db->query("SELECT u.*, r.nom AS role_nom, r.permissions FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ? AND u.actif = 1 LIMIT 1", [$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($ldapSuccess || password_verify($password, $user['mot_de_passe_hash'])) {
                self::clearAttempts($email);
                Session::regenerate();
                Session::set('user_id', $user['id']);
                Session::set('user_name', $user['nom']);
                Session::set('user_email', $user['email']);
                Session::set('user_role_id', $user['role_id']);
                Session::set('user_role', $user['role_nom']);

                $perms = json_decode($user['permissions'] ?? '[]', true) ?: [];
                Session::set('user_permissions', $perms);

                self::logAudit($user['id'], 'CONNEXION', 'users', $user['id'], "L'utilisateur {$user['email']} s'est connecté.");

                return true;
            }
        }

        self::registerAttempt($email);
        return false;
    }

    private static function attemptLdap($email, $password, $ldapConfig) {
        $conn = @ldap_connect($ldapConfig['host'], $ldapConfig['port']);
        if (!$conn) return false;
        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        if (@ldap_bind($conn, $ldapConfig['dn'], $ldapConfig['pass'])) {
            $search = @ldap_search($conn, $ldapConfig['base_dn'], "(mail=" . ldap_escape($email, "", LDAP_ESCAPE_FILTER) . ")");
            if ($search) {
                $entries = @ldap_get_entries($conn, $search);
                if ($entries && $entries['count'] > 0) {
                    $userDn = $entries[0]['dn'];
                    if (@ldap_bind($conn, $userDn, $password)) {
                        @ldap_unbind($conn);
                        return true;
                    }
                }
            }
        }
        @ldap_unbind($conn);
        return false;
    }

    public static function check() {
        return Session::has('user_id');
    }

    public static function id() {
        return Session::get('user_id');
    }

    public static function user() {
        if (!self::check()) return null;
        $db = Database::getInstance();
        $stmt = $db->query("SELECT u.*, r.nom AS role_nom FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?", [self::id()]);
        return $stmt->fetch();
    }

    public static function logout() {
        if (self::check()) {
            self::logAudit(self::id(), 'DECONNEXION', 'users', self::id(), "L'utilisateur s'est déconnecté.");
        }
        Session::destroy();
    }

    public static function hasPermission($permission) {
        $perms = Session::get('user_permissions', []);
        if (isset($perms['all']) && $perms['all'] === true) {
            return true;
        }
        return isset($perms[$permission]) && $perms[$permission] === true;
    }

    public static function logAudit($userId, $action, $entityType, $entityId, $detail) {
        try {
            $db = Database::getInstance();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $db->query("INSERT INTO activity_logs (user_id, action, entite_type, entite_id, detail, ip_address) VALUES (?, ?, ?, ?, ?, ?)", [
                $userId, $action, $entityType, $entityId, $detail, $ip
            ]);
        } catch (\Exception $e) {
            // Ignorer silencieusement pour éviter d'interrompre l'exécution principale
        }
    }
}
