<?php
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Database;

echo "=== SMOKE TEST: ROADMAP MANAGER ===\n\n";

echo "[PHP] Version installée : " . PHP_VERSION . " (Requis: >= 8.5)\n";
if (version_compare(PHP_VERSION, '8.5.0', '<')) {
    echo "[INFO] Version PHP inférieure à 8.5, mais compatible pour l'exécution locale.\n";
} else {
    echo "[OK] Version PHP valide.\n";
}

$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "[OK] Extension '{$ext}' chargée.\n";
    } else {
        echo "[ERREUR] Extension '{$ext}' manquante !\n";
    }
}

try {
    $db = Database::getInstance();
    echo "[OK] Connexion à la base de données réussie via PDO.\n";

    $expected_tables = ['users', 'roles', 'projects', 'steps', 'tasks', 'notifications', 'activity_logs', 'attachments', 'clients', 'risks', 'time_entries', 'tech_stacks', 'project_permissions'];
    foreach ($expected_tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE ?", [$table]);
        if ($stmt->fetch()) {
            echo "[OK] Table '{$table}' présente.\n";
        } else {
            echo "[ERREUR] Table '{$table}' MANQUANTE !\n";
        }
    }
} catch (\Exception $e) {
    echo "[ERREUR BASE DE DONNEES] " . $e->getMessage() . "\n";
}

$writable_dirs = [
    __DIR__ . '/../logs',
    __DIR__ . '/../../public_html/uploads',
];
foreach ($writable_dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (is_writable($dir)) {
        echo "[OK] Dossier '" . basename($dir) . "' accessible en écriture.\n";
    } else {
        echo "[ERREUR] Dossier '" . basename($dir) . "' non accessible en écriture !\n";
    }
}

echo "\nSmoke test terminé.\n";
