<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Project;

class DashboardController extends Controller {
    public function index() {
        $db = Database::getInstance();
        $userId = Auth::id();

        $activeProjects = $db->query("SELECT COUNT(*) AS count FROM projects WHERE statut = 'En cours'")->fetch()['count'] ?? 0;

        $overdueTasks = $db->query("
            SELECT COUNT(*) AS count
            FROM tasks t
            JOIN steps s ON t.step_id = s.id
            WHERE t.statut != 'Terminé' AND s.deadline < CURRENT_DATE()
        ")->fetch()['count'] ?? 0;

        $projectModel = new Project();
        $projects = $projectModel->all();
        $totalProgress = 0;
        foreach ($projects as $p) {
            $totalProgress += $p['progress_pct'];
        }
        $avgProgress = count($projects) > 0 ? round($totalProgress / count($projects)) : 0;

        $assignedTasks = $db->query("
            SELECT t.*, s.titre AS step_titre, s.deadline, p.titre AS project_titre
            FROM tasks t
            JOIN steps s ON t.step_id = s.id
            JOIN projects p ON s.project_id = p.id
            WHERE t.assigne_id = ? AND t.statut != 'Terminé'
            ORDER BY s.deadline ASC
        ", [$userId])->fetchAll();

        $this->view('dashboard/index', [
            'activeProjects' => $activeProjects,
            'overdueTasks' => $overdueTasks,
            'avgProgress' => $avgProgress,
            'assignedTasks' => $assignedTasks,
            'projects' => $projects
        ]);
    }

    public function focus() {
        $db = Database::getInstance();
        $userId = Auth::id();

        $tasks = $db->query("
            SELECT t.*, s.titre AS step_titre, p.titre AS project_titre
            FROM tasks t
            JOIN steps s ON t.step_id = s.id
            JOIN projects p ON s.project_id = p.id
            WHERE t.assigne_id = ? AND t.statut != 'Terminé'
            ORDER BY t.cree_le DESC
        ", [$userId])->fetchAll();

        $this->view('dashboard/focus', [
            'tasks' => $tasks
        ]);
    }
}
