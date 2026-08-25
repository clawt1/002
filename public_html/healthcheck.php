<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

use App\Core\Session;
use App\Core\Auth;
use App\Core\Database;

Session::start();

// Exiger que l'utilisateur soit connecté et possède la permission Administrateur
if (!Auth::check() || !Auth::hasPermission('all')) {
    http_response_code(403);
    die("<h1>403 - Accès Refusé</h1><p>Cette page de diagnostic nécessite une session Administrateur active. Veuillez d'abord vous connecter sur <a href='/login'>/login</a>.</p>");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Healthcheck - Diagnostics Système</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 p-8 min-h-screen flex flex-col justify-center items-center">
    <div class="max-w-2xl w-full bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-2xl">
        <h1 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-500 mb-6">🩺 Diagnostics Santé Applicative</h1>

        <div class="space-y-4">
            <div class="flex justify-between items-center bg-slate-950/50 p-3 rounded-lg border border-slate-880">
                <span>Version PHP :</span>
                <span class="font-bold <?php echo version_compare(PHP_VERSION, '8.5.0', '<') ? 'text-amber-400' : 'text-emerald-400'; ?>">
                    <?php echo PHP_VERSION; ?>
                </span>
            </div>

            <div class="bg-slate-950/50 p-3 rounded-lg border border-slate-880">
                <span class="block mb-2 font-medium">Extensions requises :</span>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <?php
                    $extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
                    foreach ($extensions as $ext) {
                        $loaded = extension_loaded($ext);
                        echo "<div class='flex justify-between items-center p-1 px-2 rounded " . ($loaded ? 'bg-emerald-950/20 text-emerald-400' : 'bg-red-950/20 text-red-400') . "'>";
                        echo "<span>{$ext}</span>";
                        echo "<span>" . ($loaded ? 'Actif' : 'Manquant') . "</span>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>

            <div class="flex justify-between items-center bg-slate-950/50 p-3 rounded-lg border border-slate-880">
                <span>Connexion Base de Données :</span>
                <?php
                try {
                    $db = Database::getInstance();
                    $stmt = $db->query("SELECT VERSION() AS ver");
                    $res = $stmt->fetch();
                    echo "<span class='font-bold text-emerald-400'>Opérationnelle (MySQL " . htmlspecialchars($res['ver'], ENT_QUOTES, 'UTF-8') . ")</span>";
                } catch (\Exception $e) {
                    echo "<span class='font-bold text-red-500'>Échec de connexion : " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</span>";
                }
                ?>
            </div>

            <div class="flex justify-between items-center bg-slate-950/50 p-3 rounded-lg border border-slate-880">
                <span>Droits d'écriture /uploads :</span>
                <?php
                $uploadsDir = __DIR__ . '/uploads';
                if (!is_dir($uploadsDir)) {
                    @mkdir($uploadsDir, 0755, true);
                }
                $writable = is_writable($uploadsDir);
                echo $writable
                    ? "<span class='font-bold text-emerald-400'>OK (Écriture permise)</span>"
                    : "<span class='font-bold text-red-500'>Verrouillé (Non accessible)</span>";
                ?>
            </div>

            <div class="flex justify-between items-center bg-slate-950/50 p-3 rounded-lg border border-slate-880">
                <span>Espace disque libre :</span>
                <span class="font-bold text-cyan-400">
                    <?php
                    $free = @disk_free_space(__DIR__);
                    $total = @disk_total_space(__DIR__);
                    if ($free !== false && $total !== false) {
                        $freeGb = round($free / (1024 * 1024 * 1024), 2);
                        $totalGb = round($total / (1024 * 1024 * 1024), 2);
                        echo "{$freeGb} Go libres sur {$totalGb} Go";
                    } else {
                        echo "Non disponible (Restriction hébergeur)";
                    }
                    ?>
                </span>
            </div>
        </div>
    </div>
</body>
</html>
