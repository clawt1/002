<?php
if (php_sapi_name() !== 'cli' && !defined('RUNNING_FROM_WEB')) {
    die("Accès direct non autorisé.");
}

if (!class_exists('App\Core\Database')) {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/../../app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
}

use App\Core\Database;

try {
    $db = Database::getInstance();
    $today = date('Y-m-d');
    $threeDaysFromNow = date('Y-m-d', strtotime('+3 days'));

    $stmt = $db->query("
        SELECT s.id, s.titre, s.deadline, s.project_id, p.titre AS project_titre, p.owner_id
        FROM steps s
        JOIN projects p ON s.project_id = p.id
        WHERE s.statut != 'Terminé' AND s.deadline BETWEEN ? AND ?
    ", [$today, $threeDaysFromNow]);

    $steps = $stmt->fetchAll();
    foreach ($steps as $step) {
        if ($step['owner_id']) {
            $msg = "Échéance proche ! L'étape '{$step['titre']}' du projet '{$step['project_titre']}' arrive à échéance le {$step['deadline']}.";

            $check = $db->query("SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND type = 'deadline_step' AND cible_id = ?", [
                $step['owner_id'], $step['id']
            ])->fetch();

            if ($check && $check['count'] == 0) {
                $db->query("INSERT INTO notifications (user_id, type, cible_type, cible_id, message) VALUES (?, 'deadline_step', 'steps', ?, ?)", [
                    $step['owner_id'], $step['id'], $msg
                ]);
            }
        }
    }

    $stmt = $db->query("
        SELECT t.id, t.titre, t.assigne_id, s.deadline, p.titre AS project_titre
        FROM tasks t
        JOIN steps s ON t.step_id = s.id
        JOIN projects p ON s.project_id = p.id
        WHERE t.statut != 'Terminé' AND s.deadline BETWEEN ? AND ?
    ", [$today, $threeDaysFromNow]);

    $tasks = $stmt->fetchAll();
    foreach ($tasks as $task) {
        if ($task['assigne_id']) {
            $msg = "Échéance proche ! La tâche '{$task['titre']}' (Projet '{$task['project_titre']}') arrive à échéance le {$task['deadline']}.";

            $check = $db->query("SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND type = 'deadline_task' AND cible_id = ?", [
                $task['assigne_id'], $task['id']
            ])->fetch();

            if ($check && $check['count'] == 0) {
                $db->query("INSERT INTO notifications (user_id, type, cible_type, cible_id, message) VALUES (?, 'deadline_task', 'tasks', ?, ?)", [
                    $task['assigne_id'], $task['id'], $msg
                ]);
            }
        }
    }

    echo "Vérification des échéances terminée avec succès.\n";
} catch (\Exception $e) {
    echo "Erreur lors de la vérification des échéances : " . $e->getMessage() . "\n";
}
