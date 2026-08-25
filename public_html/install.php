<?php
// /public_html/install.php - Installateur automatique en 1 clic (Solo & Mutualisé)

$envPath = __DIR__ . '/../.env';

// Sécurité renforcée : bloquer toute exécution si le fichier .env existe sur le serveur
if (file_exists($envPath)) {
    http_response_code(403);
    die("<h1>403 - Accès Refusé</h1><p>L'application Roadmap Manager est déjà installée (fichier .env présent). Veuillez supprimer le fichier <code>public_html/install.php</code> de votre serveur FTP par sécurité.</p>");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['db_host'] ?? 'localhost';
    $name = $_POST['db_name'] ?? '';
    $user = $_POST['db_user'] ?? '';
    $pass = $_POST['db_pass'] ?? '';
    $appUrl = $_POST['app_url'] ?? 'http://localhost';
    $edition = $_POST['app_edition'] ?? 'enterprise';

    if (empty($name) || empty($user)) {
        $error = "Veuillez remplir au moins le nom de la base et l'utilisateur.";
    } else {
        try {
            $dsn = "mysql:host={$host};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");

            $envData = "# Configuration de la base de données\n";
            $envData .= "DB_HOST={$host}\n";
            $envData .= "DB_NAME={$name}\n";
            $envData .= "DB_USER={$user}\n";
            $envData .= "DB_PASS={$pass}\n\n";
            $envData .= "# Configuration de l'application\n";
            $envData .= "APP_URL={$appUrl}\n";
            $envData .= "APP_ENV=development\n";
            $envData .= "APP_EDITION={$edition}\n\n";
            $envData .= "# Configuration Mail\n";
            $envData .= "MAIL_HOST=smtp.mailtrap.io\n";
            $envData .= "MAIL_PORT=2525\n";
            $envData .= "MAIL_USER=\n";
            $envData .= "MAIL_PASS=\n";
            $envData .= "MAIL_FROM=no-reply@roadmap.local\n";

            file_put_contents($envPath, $envData);

            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $schemaSql = file_get_contents($schemaFile);
                $queries = explode(';', $schemaSql);
                foreach ($queries as $query) {
                    $trimmed = trim($query);
                    if (!empty($trimmed)) {
                        $pdo->exec($trimmed);
                    }
                }
            } else {
                throw new Exception("Fichier de schéma database/schema.sql introuvable.");
            }

            $seedFile = __DIR__ . '/../database/seed.sql';
            if (file_exists($seedFile)) {
                $seedSql = file_get_contents($seedFile);
                $queries = explode(';', $seedSql);
                foreach ($queries as $query) {
                    $trimmed = trim($query);
                    if (!empty($trimmed)) {
                        $pdo->exec($trimmed);
                    }
                }
            }

            $success = "Installation réussie ! Le fichier .env a été créé et la base de données a été initialisée.<br><br><strong>IMPORTANT : Veuillez supprimer le fichier public_html/install.php de votre serveur FTP pour des raisons de sécurité.</strong>";
        } catch (Exception $e) {
            $error = "Erreur d'installation : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation - Roadmap Manager</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-center items-center p-6">
    <div class="max-w-xl w-full bg-slate-900 border border-slate-800 rounded-2xl p-8 shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">Roadmap Manager</h1>
            <p class="text-slate-400 mt-2">Assistant d'installation en un clic (FTP & Mutualisé)</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-950/40 border border-red-800 text-red-400 p-4 rounded-xl mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-950/40 border border-emerald-800 text-emerald-400 p-6 rounded-xl mb-6">
                <?php echo $success; ?>
                <div class="mt-6 flex justify-center">
                    <a href="/" class="bg-gradient-to-r from-blue-500 to-cyan-500 text-slate-950 font-black p-3 px-6 rounded-xl shadow-lg hover:brightness-110 transition">Accéder à l'application</a>
                </div>
            </div>
        <?php else: ?>
            <form action="" method="POST" class="space-y-4">
                <h2 class="text-lg font-bold border-b border-slate-800 pb-2 mb-4">Configuration de la Base de Données</h2>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-400">Hôte de la base de données (Database Host)</label>
                    <input type="text" name="db_host" value="localhost" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-400">Nom de la base de données (Database Name)</label>
                    <input type="text" name="db_name" placeholder="roadmap_manager" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500" required>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-400">Utilisateur BDD</label>
                        <input type="text" name="db_user" placeholder="root" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1 text-slate-400">Mot de passe BDD</label>
                        <input type="password" name="db_pass" placeholder="laisser vide si aucun" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500">
                    </div>
                </div>

                <h2 class="text-lg font-bold border-b border-slate-800 pb-2 pt-4 mb-4">Paramètres de l'Application</h2>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-400">URL racine du site (ex. sur votre hébergeur)</label>
                    <input type="url" name="app_url" value="<?php echo 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'); ?>" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1 text-slate-400">Édition du produit à activer</label>
                    <select name="app_edition" class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-slate-100 focus:outline-none focus:border-cyan-500">
                        <option value="enterprise">Édition Enterprise (Toutes options)</option>
                        <option value="solo">Édition Solo / Freelance</option>
                        <option value="agency">Édition Agence Web / Studio</option>
                        <option value="pme">Édition PME / DSI</option>
                        <option value="academy">Édition École / Bootcamp</option>
                        <option value="time">Édition ESN / Régie</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-slate-950 font-black p-4 rounded-xl shadow-lg hover:brightness-110 transition mt-6">Lancer l'installation et l'import SQL</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
